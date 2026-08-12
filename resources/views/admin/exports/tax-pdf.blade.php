<x-khata-pdf-layout title="Tax Report">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}
            to {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr><th>Particulars</th><th class="num" style="width:110px;">Amount</th></tr>
        </thead>
        <tbody>
            <tr><td>Sales Tax Collected</td><td class="num">{{ number_format($salesTax, 2) }}</td></tr>
            <tr><td>Purchase Tax Paid</td><td class="num">{{ number_format($purchaseTax, 2) }}</td></tr>
            <tr class="closing-row">
                <td>{{ $netTax >= 0 ? 'Net Tax Payable' : 'Net Tax Refundable' }}</td>
                <td class="num">{{ number_format(abs($netTax), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size:12px; font-weight:bold; margin-top:14px; color:#16326b;">Sales with Tax</p>
    <table class="ledger">
        <thead>
            <tr>
                <th style="width:80px;">Invoice</th>
                <th>Customer</th>
                <th style="width:70px;">Date</th>
                <th class="num" style="width:85px;">Total</th>
                <th class="num" style="width:75px;">Tax</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                <td>{{ $sale->sale_date->format('d-M-Y') }}</td>
                <td class="num">{{ number_format($sale->total_amount, 2) }}</td>
                <td class="num">{{ number_format($sale->tax, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#777;">No sales with tax in this period.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">Total Sales Tax</td>
                <td class="num">{{ number_format($salesTax, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size:12px; font-weight:bold; margin-top:14px; color:#16326b;">Purchases with Tax</p>
    <table class="ledger">
        <thead>
            <tr>
                <th style="width:80px;">Invoice</th>
                <th>Supplier</th>
                <th style="width:70px;">Date</th>
                <th class="num" style="width:85px;">Total</th>
                <th class="num" style="width:75px;">Tax</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $purchase)
            <tr>
                <td>{{ $purchase->invoice_no }}</td>
                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                <td>{{ $purchase->purchase_date->format('d-M-Y') }}</td>
                <td class="num">{{ number_format($purchase->total_amount, 2) }}</td>
                <td class="num">{{ number_format($purchase->tax, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#777;">No purchases with tax in this period.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">Total Purchase Tax</td>
                <td class="num">{{ number_format($purchaseTax, 2) }}</td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
