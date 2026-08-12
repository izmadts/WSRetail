<x-khata-pdf-layout title="Account Ledger - {{ $account->name }}">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>Account:</strong> {{ $account->code }} - {{ $account->name }} ({{ $account->type }})
            &nbsp;|&nbsp; <strong>Normal Balance:</strong> {{ $account->normal_balance }}
            <br>
            <strong>Period:</strong> {{ $from_date ? \Carbon\Carbon::parse($from_date)->format('d-M-Y') : 'Beginning' }}
            to {{ $to_date ? \Carbon\Carbon::parse($to_date)->format('d-M-Y') : now()->format('d-M-Y') }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:70px;">Date</th>
                <th>Particulars</th>
                <th style="width:110px;">Voucher</th>
                <th class="num" style="width:75px;">Debit</th>
                <th class="num" style="width:75px;">Credit</th>
                <th class="num" style="width:95px;">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="opening-row">
                <td colspan="4">Balance Brought Forward</td>
                <td class="num"></td>
                <td class="num">{{ number_format(abs($opening_balance), 2) }} {{ $opening_balance_side }}</td>
            </tr>
            @forelse($rows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                <td>{{ $row['particulars'] }}</td>
                <td>{{ $row['reference'] }}</td>
                <td class="num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                <td class="num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
                <td class="num">{{ number_format($row['balance_abs'], 2) }} {{ $row['balance_side'] }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; color:#777;">No entries in this period.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3">Period Totals</td>
                <td class="num">{{ number_format($total_debit, 2) }}</td>
                <td class="num">{{ number_format($total_credit, 2) }}</td>
                <td class="num"></td>
            </tr>
            <tr class="closing-row">
                <td colspan="5">Closing Balance</td>
                <td class="num">{{ number_format(abs($closing_balance), 2) }} {{ $closing_balance_side }}</td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
