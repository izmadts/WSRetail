<?php

namespace App\Helpers;

class ExportHelper
{
    /**
     * Neutralize CSV/spreadsheet formula injection: if a free-text cell
     * starts with a character Excel/Sheets treats as a formula trigger
     * (=, +, -, @) prefix it with a single quote so it's imported as text.
     */
    public static function sanitizeCell($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        if (preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    public static function getHeaders($type)
    {
        $headers = [
            'sales' => ['Invoice No', 'Customer', 'Date', 'Total Amount', 'Paid Amount', 'Due Amount', 'Status'],
            'purchases' => ['Invoice No', 'Supplier', 'Date', 'Total Amount', 'Paid Amount', 'Due Amount', 'Status'],
            'customers' => ['Code', 'Name', 'Email', 'Phone', 'City', 'Total Sales', 'Balance', 'Status'],
            'suppliers' => ['Code', 'Name', 'Email', 'Phone', 'City', 'Total Purchases', 'Balance', 'Status'],
            'expenses' => ['Expense No', 'Title', 'Category', 'Amount', 'Date', 'Payment Method', 'Status'],
            'incomes' => ['Income No', 'Title', 'Category', 'Amount', 'Date', 'Payment Method', 'Source'],
            'agents' => ['Name', 'Email', 'Phone', 'City', 'Total Customers', 'Total Sales', 'Total Commission', 'Status'],
            'receivable' => ['Code', 'Customer Name', 'Phone', 'City', 'Total Sales', 'Total Paid', 'Balance'],
        ];

        return $headers[$type] ?? ['Data'];
    }

    public static function formatRow($row, $type)
    {
        $sanitize = fn ($v) => self::sanitizeCell($v);

        switch ($type) {
            case 'sales':
                return [
                    $row->invoice_no,
                    $sanitize($row->customer->name ?? 'N/A'),
                    $row->sale_date->format('d-m-Y'),
                    number_format($row->total_amount, 2),
                    number_format($row->paid_amount, 2),
                    number_format($row->due_amount, 2),
                    $row->status_label,
                ];

            case 'purchases':
                return [
                    $row->invoice_no,
                    $sanitize($row->supplier->name ?? 'N/A'),
                    $row->purchase_date->format('d-m-Y'),
                    number_format($row->total_amount, 2),
                    number_format($row->paid_amount, 2),
                    number_format($row->due_amount, 2),
                    ucfirst($row->status),
                ];

            case 'customers':
                return [
                    $row->code,
                    $sanitize($row->name),
                    $sanitize($row->email ?? ''),
                    $sanitize($row->phone ?? ''),
                    $sanitize($row->city ?? ''),
                    number_format($row->total_sales, 2),
                    number_format($row->balance, 2),
                    $row->is_active ? 'Active' : 'Inactive',
                ];

            case 'suppliers':
                return [
                    $row->code,
                    $sanitize($row->name),
                    $sanitize($row->email ?? ''),
                    $sanitize($row->phone ?? ''),
                    $sanitize($row->city ?? ''),
                    number_format($row->total_purchases, 2),
                    number_format($row->balance, 2),
                    $row->is_active ? 'Active' : 'Inactive',
                ];

            case 'expenses':
                return [
                    $row->expense_no,
                    $sanitize($row->title),
                    $sanitize($row->category->name ?? 'Uncategorized'),
                    number_format($row->amount, 2),
                    $row->expense_date->format('d-m-Y'),
                    $row->payment_method_label,
                    $row->status_label,
                ];

            case 'incomes':
                return [
                    $row->income_no,
                    $sanitize($row->title),
                    $sanitize($row->category->name ?? 'Uncategorized'),
                    number_format($row->amount, 2),
                    $row->income_date->format('d-m-Y'),
                    $row->payment_method_label,
                    $row->source_label,
                ];

            case 'agents':
                return [
                    $sanitize($row->name),
                    $sanitize($row->email),
                    $sanitize($row->phone ?? ''),
                    $sanitize($row->city ?? ''),
                    $row->customers()->count(),
                    number_format($row->sales()->whereIn('status', ['confirmed', 'partial', 'paid'])->sum('total_amount'), 2),
                    number_format($row->commissionLogs()->sum('amount'), 2),
                    $row->is_active ? 'Active' : 'Inactive',
                ];

            case 'receivable':
                return [
                    $row->code,
                    $sanitize($row->name),
                    $sanitize($row->phone ?? ''),
                    $sanitize($row->city ?? ''),
                    number_format($row->total_sales, 2),
                    number_format($row->total_paid, 2),
                    number_format($row->balance, 2),
                ];

            default:
                return (array) $row;
        }
    }
}
