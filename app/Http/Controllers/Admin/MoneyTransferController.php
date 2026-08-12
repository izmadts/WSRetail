<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoneyTransfer;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoneyTransferController extends Controller
{
    public function index()
    {
        $transfers = MoneyTransfer::with('fromAccount', 'toAccount', 'createdBy')
            ->orderBy('transfer_date', 'desc')
            ->get();

        // Summary stats
        $totalTransferred = MoneyTransfer::where('status', 'completed')->sum('amount');
        $pendingTransfers = MoneyTransfer::where('status', 'pending')->sum('amount');

        return view('admin.money-transfers.index', compact('transfers', 'totalTransferred', 'pendingTransfers'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('admin.money-transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            // MoneyTransfer::created() posts accounting automatically when
            // status is completed - no need to call it again here.
            MoneyTransfer::create([
                'from_account_id' => $validated['from_account_id'],
                'to_account_id' => $validated['to_account_id'],
                'amount' => $validated['amount'],
                'transfer_date' => $validated['transfer_date'],
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('admin.money-transfers.index')
            ->with('success', 'Money transfer created successfully!');
    }

    public function show(MoneyTransfer $moneyTransfer)
    {
        $moneyTransfer->load('fromAccount', 'toAccount', 'createdBy');
        return view('admin.money-transfers.show', compact('moneyTransfer'));
    }

    public function edit(MoneyTransfer $moneyTransfer)
    {
        if ($moneyTransfer->status == 'completed') {
            return back()->with('error', 'Cannot edit a completed transfer!');
        }

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('admin.money-transfers.edit', compact('moneyTransfer', 'accounts'));
    }

    public function update(Request $request, MoneyTransfer $moneyTransfer)
    {
        if ($moneyTransfer->status == 'completed') {
            return back()->with('error', 'Cannot update a completed transfer!');
        }

        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated, $moneyTransfer) {
            // MoneyTransfer::updated() reverses+reposts accounting
            // automatically whenever status/amount/accounts actually changed.
            $moneyTransfer->update([
                'from_account_id' => $validated['from_account_id'],
                'to_account_id' => $validated['to_account_id'],
                'amount' => $validated['amount'],
                'transfer_date' => $validated['transfer_date'],
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
            ]);
        });

        return redirect()->route('admin.money-transfers.index')
            ->with('success', 'Money transfer updated successfully!');
    }

    public function destroy(MoneyTransfer $moneyTransfer)
    {
        if ($moneyTransfer->status == 'completed') {
            return back()->with('error', 'Cannot delete a completed transfer!');
        }

        $moneyTransfer->delete();
        return redirect()->route('admin.money-transfers.index')
            ->with('success', 'Money transfer deleted successfully!');
    }

    public function complete(MoneyTransfer $moneyTransfer)
    {
        if ($moneyTransfer->status == 'completed') {
            return back()->with('error', 'Transfer already completed!');
        }

        $moneyTransfer->update(['status' => 'completed']);

        return back()->with('success', 'Money transfer completed successfully!');
    }

    public function cancel(MoneyTransfer $moneyTransfer)
    {
        if ($moneyTransfer->status == 'completed') {
            return back()->with('error', 'Cannot cancel a completed transfer!');
        }

        $moneyTransfer->update(['status' => 'cancelled']);
        return back()->with('success', 'Money transfer cancelled!');
    }
}
