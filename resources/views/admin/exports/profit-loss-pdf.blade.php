<x-khata-pdf-layout title="Profit &amp; Loss Statement">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}
            to {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr>
                <th>Particulars</th>
                <th class="num" style="width:110px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr class="total-row"><td colspan="2">Income</td></tr>
            <tr><td>&nbsp;&nbsp;Sales Revenue (net of returns)</td><td class="num">{{ number_format($salesRevenue, 2) }}</td></tr>
            <tr><td>&nbsp;&nbsp;Other Income</td><td class="num">{{ number_format($otherIncome, 2) }}</td></tr>
            <tr><td><strong>Total Income</strong></td><td class="num"><strong>{{ number_format($totalIncome, 2) }}</strong></td></tr>

            <tr class="total-row"><td colspan="2">Cost of Goods Sold</td></tr>
            <tr><td>&nbsp;&nbsp;COGS (net of returns)</td><td class="num">{{ number_format($cogs, 2) }}</td></tr>
            <tr><td><strong>Gross Profit</strong></td><td class="num"><strong>{{ number_format($grossProfit, 2) }}</strong></td></tr>

            <tr class="total-row"><td colspan="2">Operating Expenses</td></tr>
            @forelse($expensesByCategory as $cat)
            <tr><td>&nbsp;&nbsp;{{ $cat['category'] ?? 'Uncategorized' }}</td><td class="num">{{ number_format($cat['total'], 2) }}</td></tr>
            @empty
            <tr><td colspan="2" style="color:#777;">&nbsp;&nbsp;No operating expenses in this period.</td></tr>
            @endforelse
            <tr><td><strong>Total Operating Expenses</strong></td><td class="num"><strong>{{ number_format($operatingExpenses, 2) }}</strong></td></tr>

            <tr class="closing-row">
                <td>{{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}</td>
                <td class="num">{{ number_format(abs($netProfit), 2) }}</td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
