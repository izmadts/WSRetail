<x-khata-pdf-layout title="Payable Report (Supplier Outstanding)">
    <x-slot:meta>
        <div class="khata-meta">
            <strong>As of:</strong> {{ now()->format('d-M-Y') }}
            &nbsp;|&nbsp; <strong>Suppliers with balance owed:</strong> {{ $totalSuppliers }}
        </div>
    </x-slot:meta>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:80px;">Code</th>
                <th>Supplier</th>
                <th style="width:90px;">Phone</th>
                <th class="num" style="width:85px;">Total Purchases</th>
                <th class="num" style="width:85px;">Total Paid</th>
                <th class="num" style="width:90px;">Balance Owed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliersWithBalance as $supplier)
            <tr>
                <td>{{ $supplier->code }}</td>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->phone }}</td>
                <td class="num">{{ number_format($supplier->total_purchases, 2) }}</td>
                <td class="num">{{ number_format($supplier->total_paid, 2) }}</td>
                <td class="num">{{ number_format($supplier->balance, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; color:#777;">No outstanding payables.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5">Total Payable</td>
                <td class="num">{{ number_format($totalPayable, 2) }}</td>
            </tr>
        </tbody>
    </table>
</x-khata-pdf-layout>
