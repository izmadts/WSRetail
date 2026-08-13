<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $sale->invoice_no }}</title>
    @php
        $isThermal = $posSettings->invoice_paper_size !== 'a4';
        $widthMm = $posSettings->invoice_paper_size === 'thermal_58' ? 58 : ($posSettings->invoice_paper_size === 'thermal_80' ? 80 : 210);
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: {{ $isThermal ? "'Courier New', monospace" : "Arial, sans-serif" }};
            font-size: {{ $isThermal ? '11px' : '13px' }};
            color: #111;
            margin: 0;
            padding: {{ $isThermal ? '4mm' : '10mm' }};
            width: {{ $widthMm }}mm;
        }
        h1 { font-size: {{ $isThermal ? '13px' : '20px' }}; margin: 0 0 4px; text-align: center; }
        .center { text-align: center; }
        .muted { color: #555; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        hr { border: none; border-top: 1px dashed #999; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { text-align: left; padding: 2px 0; font-size: {{ $isThermal ? '10px' : '12px' }}; }
        th:last-child, td:last-child { text-align: right; }
        .totals .row { padding: 1px 0; }
        .totals .grand { font-weight: bold; font-size: {{ $isThermal ? '13px' : '15px' }}; border-top: 1px solid #333; margin-top: 4px; padding-top: 4px; }
        .actions { text-align: center; margin-top: 12px; }
        .actions button, .actions a {
            display: inline-block; margin: 0 4px; padding: 6px 14px; border-radius: 6px;
            border: 1px solid #ccc; background: #fff; color: #333; font-size: 13px; text-decoration: none; cursor: pointer;
        }
        @media print {
            .actions { display: none; }
            body { padding: 0; }
        }
        @page { size: {{ $isThermal ? $widthMm . 'mm auto' : 'A4' }}; margin: {{ $isThermal ? '0' : '10mm' }}; }
    </style>
</head>
<body>
    <h1>{{ \App\Models\Setting::get('app_name') ?: config('app.name') }}</h1>
    @if(\App\Models\Setting::get('company_address'))
    <p class="center muted">{{ \App\Models\Setting::get('company_address') }}</p>
    @endif
    @if(\App\Models\Setting::get('company_phone'))
    <p class="center muted">{{ \App\Models\Setting::get('company_phone') }}</p>
    @endif

    <hr>

    <div class="row"><span>Invoice</span><span>{{ $sale->invoice_no }}</span></div>
    <div class="row"><span>Date</span><span>{{ $sale->sale_date->format('d-m-Y') }}</span></div>
    @if($sale->location)
    <div class="row"><span>Location</span><span>{{ $sale->location->name }}</span></div>
    @endif
    <div class="row"><span>Cashier</span><span>{{ $sale->createdBy->name ?? '-' }}</span></div>
    <div class="row"><span>Customer</span><span>{{ $sale->customer->name ?? 'Walk-in' }}</span></div>

    <hr>

    <table>
        <thead>
            <tr><th>Item</th><th>Qty</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'N/A' }}{{ $item->variant ? ' (' . $item->variant->label . ')' : '' }}<br><span class="muted">{{ number_format($item->unit_price, 2) }} x {{ number_format($item->quantity, 2) }}</span></td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <div class="totals">
        <div class="row"><span>Sub Total</span><span>Rs. {{ number_format($sale->sub_total, 2) }}</span></div>
        @if($sale->discount > 0)
        <div class="row"><span>Discount</span><span>- Rs. {{ number_format($sale->discount_type === 'percentage' ? $sale->sub_total * $sale->discount / 100 : $sale->discount, 2) }}</span></div>
        @endif
        @if($sale->tax > 0)
        <div class="row"><span>Tax</span><span>Rs. {{ number_format($sale->tax, 2) }}</span></div>
        @endif
        @if($sale->shipping_cost > 0)
        <div class="row"><span>Shipping</span><span>Rs. {{ number_format($sale->shipping_cost, 2) }}</span></div>
        @endif
        <div class="row grand"><span>Total</span><span>Rs. {{ number_format($sale->total_amount, 2) }}</span></div>
        <div class="row"><span>Paid</span><span>Rs. {{ number_format($sale->paid_amount, 2) }}</span></div>
        @if($sale->due_amount > 0)
        <div class="row"><span>Due</span><span>Rs. {{ number_format($sale->due_amount, 2) }}</span></div>
        @endif
        @if($sale->payments->isNotEmpty())
        <div class="row muted"><span>Payment Method</span><span>{{ ucfirst(str_replace('_', ' ', $sale->payments->last()->payment_method)) }}</span></div>
        @endif
    </div>

    <hr>
    <p class="center muted">Thank you for your business!</p>

    <div class="actions">
        <button type="button" onclick="window.print()">🖶 Print</button>
        @if(auth()->user() && !auth()->user()->isPosManager())
        <a href="{{ route('admin.sales.show', $sale) }}">View Full Details</a>
        @endif
        <a href="{{ route('admin.sales.pos') }}">New Sale</a>
    </div>

    @if($posSettings->auto_print_receipt)
    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
    @endif
</body>
</html>
