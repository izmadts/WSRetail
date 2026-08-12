<x-khata-pdf-layout title="Day Book (General Journal)">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($from_date)->format('d-M-Y') }}
            to {{ \Carbon\Carbon::parse($to_date)->format('d-M-Y') }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:60px;">Date</th>
                <th style="width:100px;">Voucher</th>
                <th>Account</th>
                <th>Description</th>
                <th class="num" style="width:75px;">Debit</th>
                <th class="num" style="width:75px;">Credit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
            <tr>
                <td>{{ \Carbon\Carbon::parse($entry->entry_date)->format('d-M-Y') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $entry->reference_type)) }} #{{ $entry->reference_id }}</td>
                <td>{{ $entry->account->code }} - {{ $entry->account->name }}</td>
                <td>{{ $entry->description }}</td>
                <td class="num">{{ $entry->type === 'debit' ? number_format($entry->amount, 2) : '' }}</td>
                <td class="num">{{ $entry->type === 'credit' ? number_format($entry->amount, 2) : '' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; color:#777;">No transactions posted in this period.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">Totals (must match - every voucher balances)</td>
                <td class="num">{{ number_format($total_debit, 2) }}</td>
                <td class="num">{{ number_format($total_credit, 2) }}</td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
