<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // 1. Assets (Debit normal balance)
            ['code' => '1010', 'name' => 'Cash Account', 'type' => 'Asset', 'normal_balance' => 'Debit'],
            ['code' => '1020', 'name' => 'Bank Account', 'type' => 'Asset', 'normal_balance' => 'Debit'],
            ['code' => '1030', 'name' => 'Inventory Stock', 'type' => 'Asset', 'normal_balance' => 'Debit'],
            ['code' => '1040', 'name' => 'Accounts Receivable (Customers)', 'type' => 'Asset', 'normal_balance' => 'Debit'],

            // 2. Liabilities (Credit normal balance)
            ['code' => '2010', 'name' => 'Accounts Payable (Suppliers)', 'type' => 'Liability', 'normal_balance' => 'Credit'],
            ['code' => '2020', 'name' => 'Agent Commission Payable', 'type' => 'Liability', 'normal_balance' => 'Credit'],
            ['code' => '2030', 'name' => 'Salary Payable', 'type' => 'Liability', 'normal_balance' => 'Credit'],

            // 3. Equity (Credit normal balance)
            ['code' => '3010', 'name' => 'Owner\'s Capital', 'type' => 'Equity', 'normal_balance' => 'Credit'],
            ['code' => '3020', 'name' => 'Opening Balance Equity', 'type' => 'Equity', 'normal_balance' => 'Credit'],

            // 4. Revenue (Credit normal balance)
            ['code' => '4010', 'name' => 'Sales Revenue', 'type' => 'Revenue', 'normal_balance' => 'Credit'],
            ['code' => '4020', 'name' => 'Other Income', 'type' => 'Revenue', 'normal_balance' => 'Credit'],

            // 5. Expenses (Debit normal balance)
            ['code' => '5010', 'name' => 'Purchase Cost', 'type' => 'Expense', 'normal_balance' => 'Debit'],
            ['code' => '5020', 'name' => 'Agent Commission Expense', 'type' => 'Expense', 'normal_balance' => 'Debit'],
            ['code' => '5030', 'name' => 'General Expenses', 'type' => 'Expense', 'normal_balance' => 'Debit'],
            ['code' => '5040', 'name' => 'Inventory Shrinkage & Adjustments', 'type' => 'Expense', 'normal_balance' => 'Debit'],
            ['code' => '5050', 'name' => 'Salary Expense', 'type' => 'Expense', 'normal_balance' => 'Debit'],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account + ['is_active' => true]
            );
        }

        $this->command->info('✅ Accounts seeded successfully!');
    }
}
