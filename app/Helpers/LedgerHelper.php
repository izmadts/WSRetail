<?php

namespace App\Helpers;

/**
 * Turns a list of dated {date, particulars, reference, debit, credit} rows
 * into a standard running-balance ledger ("khata") - each row gets the
 * cumulative balance after applying its own debit/credit, starting from an
 * opening balance. Shared by every ledger-style report (Customer, Supplier,
 * Account/Cash Book/Bank Book) so the running-balance math is written once
 * instead of once per report.
 */
class LedgerHelper
{
    /**
     * @param array $rows Each: ['date' => string, 'particulars' => string, 'reference' => ?string, 'debit' => float, 'credit' => float]
     * @param float $openingBalance Signed - positive means a debit-side opening balance.
     * @return array{rows: array, opening_balance: float, closing_balance: float}
     */
    public static function withRunningBalance(array $rows, float $openingBalance = 0): array
    {
        // Stable since PHP 8.0 - same-date rows keep their original (already
        // chronological-ish, e.g. sale-then-its-own-payment) relative order
        // instead of shuffling on ties.
        usort($rows, fn ($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));

        $balance = $openingBalance;
        $out = [];

        foreach ($rows as $row) {
            $balance += (float) ($row['debit'] ?? 0) - (float) ($row['credit'] ?? 0);
            $row['debit'] = (float) ($row['debit'] ?? 0);
            $row['credit'] = (float) ($row['credit'] ?? 0);
            // Traditional khata rows never show a bare negative number - the
            // balance is always displayed as an absolute amount plus a Dr/Cr
            // suffix, so a credit-normal running balance (e.g. a growing
            // payable) still reads naturally instead of as "-45,000".
            $row['balance'] = $balance;
            $row['balance_abs'] = abs($balance);
            $row['balance_side'] = $balance >= 0 ? 'Dr' : 'Cr';
            $out[] = $row;
        }

        return [
            'rows' => $out,
            'opening_balance' => $openingBalance,
            'closing_balance' => $balance,
        ];
    }
}
