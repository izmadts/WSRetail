<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AccountReconciliationService;
use Illuminate\Http\Request;

/**
 * "Reconcile All Accounts" - admin-only ledger integrity scanner/fixer.
 * Not to be confused with BankReconciliationController (statement-vs-
 * ledger matching for one account at a time) - this scans the whole
 * double-entry ledger for missing/duplicate/orphaned/unbalanced/mismatched
 * journal entries across every transaction type in the app. See
 * AccountReconciliationService for the actual scan/fix logic.
 */
class AccountReconciliationController extends Controller
{
    protected $service;

    public function __construct(AccountReconciliationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.account-reconciliation.index', ['results' => null]);
    }

    public function scan()
    {
        $results = $this->service->scan();

        return view('admin.account-reconciliation.index', ['results' => $results]);
    }

    public function fix(Request $request)
    {
        $validated = $request->validate([
            'selectors' => 'required|array|min:1',
            'selectors.*.reference_type' => 'required|string',
            'selectors.*.reference_id' => 'required|integer',
            'selectors.*.category' => 'required|string',
        ]);

        $outcomes = $this->service->fix($validated['selectors']);

        $fixed = count(array_filter($outcomes, fn ($o) => $o['status'] === 'fixed'));
        $skipped = count(array_filter($outcomes, fn ($o) => $o['status'] === 'skipped_already_resolved'));
        $failed = count(array_filter($outcomes, fn ($o) => in_array($o['status'], ['failed', 'not_fixable'])));

        $message = "{$fixed} issue(s) fixed.";
        if ($skipped > 0) {
            $message .= " {$skipped} were already resolved.";
        }
        if ($failed > 0) {
            $message .= " {$failed} could not be fixed - see Activity Log / server log for details.";
        }

        $results = $this->service->scan();

        return view('admin.account-reconciliation.index', [
            'results' => $results,
            'fixOutcomes' => $outcomes,
        ])->with($failed > 0 && $fixed === 0 ? 'error' : 'success', $message);
    }
}
