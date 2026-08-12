<x-khata-pdf-layout title="Receivable Report (Customer Outstanding)">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>As of:</strong> {{ now()->format('d-M-Y') }}
            &nbsp;|&nbsp; <strong>Customers with balance owed:</strong> {{ $totalCustomers }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:80px;">Code</th>
                <th>Customer</th>
                <th style="width:90px;">Phone</th>
                <th class="num" style="width:85px;">Total Sales</th>
                <th class="num" style="width:85px;">Total Paid</th>
                <th class="num" style="width:90px;">Balance Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customersWithBalance as $customer)
            <tr>
                <td>{{ $customer->code }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td class="num">{{ number_format($customer->total_sales, 2) }}</td>
                <td class="num">{{ number_format($customer->total_paid, 2) }}</td>
                <td class="num">{{ number_format($customer->balance, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; color:#777;">No outstanding receivables.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5">Total Receivable</td>
                <td class="num">{{ number_format($totalReceivable, 2) }}</td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
