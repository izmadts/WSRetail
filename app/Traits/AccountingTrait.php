<?php

namespace App\Traits;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

trait AccountingTrait
{
    /**
     * Post a balanced set of journal entries in one transaction.
     *
     * Each entry must carry its own account_id/type/amount/description, since
     * the two sides of a real transaction almost always describe themselves
     * differently (e.g. "Cash Sale #X" debit vs "Sale Revenue #X" credit).
     * Entries missing a required key, or with $entries that don't balance
     * (debits != credits), abort the whole batch - this is the one place in
     * the app that actually asserts the books stay balanced.
     */
    public function postDoubleEntry($entries, $referenceType, $referenceId, $entryDate = null)
    {
        if (empty($entries)) {
            return;
        }

        DB::transaction(function () use ($entries, $referenceType, $referenceId, $entryDate) {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($entries as $entry) {
                if (empty($entry['account_id']) || !isset($entry['type'], $entry['amount'], $entry['description'])) {
                    throw new \Exception('Invalid journal entry: missing account_id, type, amount, or description.');
                }

                if ($entry['type'] == 'debit') {
                    $totalDebit += $entry['amount'];
                } elseif ($entry['type'] == 'credit') {
                    $totalCredit += $entry['amount'];
                } else {
                    throw new \Exception("Invalid journal entry type: {$entry['type']}");
                }

                JournalEntry::create([
                    'account_id' => $entry['account_id'],
                    'type' => $entry['type'],
                    'amount' => $entry['amount'],
                    'description' => $entry['description'],
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'entry_date' => $entryDate ? \Carbon\Carbon::parse($entryDate)->toDateString() : now()->toDateString(),
                ]);
            }

            if (round($totalDebit, 2) != round($totalCredit, 2)) {
                throw new \Exception("Accounting equation violated for {$referenceType} #{$referenceId}: Debits ({$totalDebit}) do not equal Credits ({$totalCredit}).");
            }
        });
    }
}
