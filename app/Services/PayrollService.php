<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\SalaryPayment;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Payroll accrual (Run Payroll) posts Dr Salary Expense / Cr Salary
 * Payable, payment posts the reverse against Cash/Bank. Deliberately no
 * multi-log FIFO payment allocation, since a payslip is one discrete
 * monthly obligation, not a running tab (see SalaryPayment). Deductions
 * (tax/other) are netted straight out of the expense/payable pair rather
 * than routed to a separate liability - there's no tax-authority-remittance
 * tracking in this module, so modeling a Tax Payable account would be a
 * liability with no corresponding "pay the tax authority" flow to clear it.
 */
class PayrollService
{
    use AccountingTrait;

    /**
     * Process payroll for every employee with an active salary structure
     * as of the 1st of the given month. Idempotent per year/month via the
     * payroll_runs unique constraint - a second call for an already-run
     * month throws rather than silently double-posting.
     *
     * @param array<int,float> $overtimeByEmployeeId employee_id => overtime amount for this run
     */
    public function runPayroll(int $year, int $month, array $overtimeByEmployeeId, int $processedByUserId): PayrollRun
    {
        if (PayrollRun::where('year', $year)->where('month', $month)->exists()) {
            throw new \Exception("Payroll for {$month}/{$year} has already been processed.");
        }

        return DB::transaction(function () use ($year, $month, $overtimeByEmployeeId, $processedByUserId) {
            $periodStart = \Carbon\Carbon::create($year, $month, 1);

            $payrollRun = PayrollRun::create([
                'year' => $year,
                'month' => $month,
                'status' => 'processed',
                'processed_by' => $processedByUserId,
                'processed_at' => now(),
            ]);

            $totalAmount = 0;

            $employees = Employee::where('is_active', true)
                ->whereIn('employment_status', ['active', 'on_leave'])
                ->get();

            foreach ($employees as $employee) {
                $component = SalaryComponent::activeFor($employee, $periodStart);
                if (!$component) {
                    continue;
                }

                $overtime = round((float) ($overtimeByEmployeeId[$employee->id] ?? 0), 2);
                $totalAllowances = $component->total_allowances;
                $totalDeductions = $component->total_deductions;
                $grossPay = round((float) $component->basic_pay + $totalAllowances + $overtime, 2);
                $netPay = round($grossPay - $totalDeductions, 2);

                if ($netPay <= 0) {
                    continue;
                }

                $payslip = Payslip::create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $employee->id,
                    'basic_pay' => $component->basic_pay,
                    'total_allowances' => $totalAllowances,
                    'overtime_amount' => $overtime,
                    'total_deductions' => $totalDeductions,
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'is_paid' => false,
                ]);

                $this->postAccrualLedger($netPay, $payslip->id, "Salary accrued - {$employee->name} - {$payrollRun->month_name} {$year}", $periodStart);

                $totalAmount += $netPay;
            }

            $payrollRun->update(['total_amount' => $totalAmount]);

            return $payrollRun;
        });
    }

    /**
     * Records a payment against a payslip. Allows partial payments (summed
     * against net_pay) rather than requiring exact settlement in one shot,
     * but never allocates across multiple payslips - each payslip is paid
     * on its own.
     */
    public function payPayslip(Payslip $payslip, float $amount, $paymentDate, string $method, ?string $referenceNo, int $paidByUserId): SalaryPayment
    {
        if ($payslip->is_paid) {
            throw new \Exception('This payslip has already been fully paid.');
        }

        return DB::transaction(function () use ($payslip, $amount, $paymentDate, $method, $referenceNo, $paidByUserId) {
            $payment = SalaryPayment::create([
                'payslip_id' => $payslip->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'paid_by' => $paidByUserId,
            ]);

            $totalPaid = round((float) $payslip->payments()->sum('amount'), 2);

            if ($totalPaid >= round($payslip->net_pay, 2)) {
                $payslip->update([
                    'is_paid' => true,
                    'paid_date' => $paymentDate,
                    'paid_by' => $paidByUserId,
                ]);
                $payslip->payrollRun?->refreshPaidStatus();
            }

            $this->postPaymentLedger($amount, $payslip, $method, $paymentDate);

            return $payment;
        });
    }

    private function postAccrualLedger(float $amount, int $payslipId, string $description, $date): void
    {
        if (round($amount, 2) == 0) {
            return;
        }

        $expenseAccount = Account::where('code', '5050')->first();
        $payableAccount = Account::where('code', '2030')->first();

        if (!$expenseAccount || !$payableAccount) {
            Log::warning('Salary accounts not found, skipping ledger post', ['payslip_id' => $payslipId]);
            return;
        }

        $this->postDoubleEntry([
            ['account_id' => $expenseAccount->id, 'type' => 'debit', 'amount' => $amount, 'description' => $description],
            ['account_id' => $payableAccount->id, 'type' => 'credit', 'amount' => $amount, 'description' => $description],
        ], 'payroll_accrual', $payslipId, $date);
    }

    private function postPaymentLedger(float $amount, Payslip $payslip, string $method, $date): void
    {
        $payableAccount = Account::where('code', '2030')->first();
        $cashAccount = Account::where('code', '1010')->first();
        $bankAccount = Account::where('code', '1020')->first();
        $offsetAccount = $method === 'cash' ? $cashAccount : $bankAccount;

        if (!$payableAccount || !$offsetAccount) {
            Log::warning('Salary payment accounts not found, skipping ledger post', ['payslip_id' => $payslip->id]);
            return;
        }

        $employeeName = $payslip->employee->name ?? 'employee';

        $this->postDoubleEntry([
            ['account_id' => $payableAccount->id, 'type' => 'debit', 'amount' => $amount, 'description' => "Salary payment to {$employeeName}"],
            ['account_id' => $offsetAccount->id, 'type' => 'credit', 'amount' => $amount, 'description' => "Salary payment to {$employeeName} via {$method}"],
        ], 'salary_payment', $payslip->id, $date);
    }
}
