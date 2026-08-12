<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use App\Helpers\ExportHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    // =============================================
    // 1. EXPORT TO CSV
    // =============================================

    public function exportCSV(Request $request)
    {
        $type = $request->type;
        $data = $this->getExportData($type, $request);

        $filename = $type . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data, $type) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ExportHelper::getHeaders($type));

            foreach ($data as $row) {
                fputcsv($file, ExportHelper::formatRow($row, $type));
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    // =============================================
    // 2. EXPORT TO EXCEL (via Maatwebsite)
    // =============================================

    public function exportExcel(Request $request)
    {
        $type = $request->type;
        $data = $this->getExportData($type, $request);

        return Excel::download(new class($data, $type) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            private $type;

            public function __construct($data, $type)
            {
                $this->data = $data;
                $this->type = $type;
            }

            public function array(): array
            {
                $rows = [];
                foreach ($this->data as $row) {
                    $rows[] = ExportHelper::formatRow($row, $this->type);
                }
                return $rows;
            }

            public function headings(): array
            {
                return ExportHelper::getHeaders($this->type);
            }
        }, $type . '_' . date('Y-m-d') . '.xlsx');
    }

    // =============================================
    // 3. EXPORT TO PDF
    // =============================================

    public function exportPDF(Request $request)
    {
        $type = $request->type;
        $data = $this->getExportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Report';

        $pdf = Pdf::loadView('admin.exports.pdf', [
            'data' => $data,
            'type' => $type,
            'title' => $title,
            'controller' => $this
        ]);

        return $pdf->download($type . '_' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    // 4. GET EXPORT DATA
    // =============================================

    public function getExportData($type, $request)
    {
        switch ($type) {
            case 'sales':
                return Sale::with('customer')->orderBy('created_at', 'desc')->get();

            case 'purchases':
                return Purchase::with('supplier')->orderBy('created_at', 'desc')->get();

            case 'customers':
                return Customer::orderBy('name')->get();

            case 'suppliers':
                return Supplier::orderBy('name')->get();

            case 'expenses':
                return Expense::with('category')->orderBy('expense_date', 'desc')->get();

            case 'incomes':
                return Income::with('category')->orderBy('income_date', 'desc')->get();

            case 'agents':
                return User::where('role', 'sales_agent')->orderBy('name')->get();

            case 'receivable':
                // balance is a computed accessor (opening_balance + sales - payments),
                // not a column - can't filter in SQL, so filter/sort in PHP instead.
                return Customer::with(['sales', 'salePayments'])
                    ->get()
                    ->filter(fn ($customer) => $customer->balance > 0)
                    ->sortByDesc('balance')
                    ->values();

            default:
                return [];
        }
    }

    // =============================================
    // 5. GET HEADERS
    // =============================================

    public function getHeaders($type)
    {
        return ExportHelper::getHeaders($type);
    }

    // =============================================
    // 6. FORMAT ROW
    // =============================================

    public function formatRow($row, $type)
    {
        return ExportHelper::formatRow($row, $type);
    }
}
