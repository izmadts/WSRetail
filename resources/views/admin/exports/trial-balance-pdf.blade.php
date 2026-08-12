<x-khata-pdf-layout title="Trial Balance">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>As of:</strong> {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:60px;">Code</th>
                <th>Account</th>
                <th style="width:90px;">Type</th>
                <th class="num" style="width:90px;">Debit</th>
                <th class="num" style="width:90px;">Credit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trialBalance as $row)
            <tr>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['type'] }}</td>
                <td class="num">{{ $row['balance_type'] === 'debit' ? number_format($row['balance'], 2) : '' }}</td>
                <td class="num">{{ $row['balance_type'] === 'credit' ? number_format($row['balance'], 2) : '' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#777;">No account balances to show.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3">Totals</td>
                <td class="num">{{ number_format($totalDebitBalance, 2) }}</td>
                <td class="num">{{ number_format($totalCreditBalance, 2) }}</td>
            </tr>
            <tr class="closing-row">
                <td colspan="5">
                    {{ abs($totalDebitBalance - $totalCreditBalance) < 0.01 ? 'Books balance - total debits equal total credits.' : 'OUT OF BALANCE by ' . number_format(abs($totalDebitBalance - $totalCreditBalance), 2) }}
                </td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
