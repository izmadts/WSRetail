{{--
    Shared running-balance ("khata") table for the on-screen Customer/Supplier/
    Account Ledger reports - all three pass the exact same shape of data
    (rows/opening_balance/closing_balance/total_debit/total_credit) via
    ReportController::buildPeriodLedger(), so the table markup lives once.
--}}
<div class="overflow-x-auto">
    <table class="w-full" id="ledgerTable">
        <thead>
            <tr class="border-b">
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Date</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Particulars</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Reference</th>
                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Debit</th>
                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Credit</th>
                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-gray-50 font-medium">
                <td colspan="4" class="py-2 px-2 text-gray-600">Balance Brought Forward</td>
                <td></td>
                <td class="py-2 px-2 text-right">Rs. {{ number_format(abs($opening_balance), 2) }} {{ $opening_balance_side }}</td>
            </tr>
            @forelse($rows as $row)
            <tr class="border-b border-gray-100">
                <td class="py-2 px-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                <td class="py-2 px-2">{{ $row['particulars'] }}</td>
                <td class="py-2 px-2 text-gray-500">{{ $row['reference'] }}</td>
                <td class="py-2 px-2 text-right text-red-600 font-medium">{{ $row['debit'] > 0 ? 'Rs. ' . number_format($row['debit'], 2) : '' }}</td>
                <td class="py-2 px-2 text-right text-green-600 font-medium">{{ $row['credit'] > 0 ? 'Rs. ' . number_format($row['credit'], 2) : '' }}</td>
                <td class="py-2 px-2 text-right font-medium">Rs. {{ number_format($row['balance_abs'], 2) }} {{ $row['balance_side'] }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-6 text-center text-gray-400">No transactions in this period.</td></tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50 font-bold">
            <tr>
                <td colspan="3" class="text-right py-2 px-2">Period Totals:</td>
                <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($total_debit, 2) }}</td>
                <td class="text-right py-2 px-2 text-green-600">Rs. {{ number_format($total_credit, 2) }}</td>
                <td></td>
            </tr>
            <tr class="border-t-2 border-gray-800">
                <td colspan="5" class="text-right py-2 px-2">Closing Balance:</td>
                <td class="text-right py-2 px-2">Rs. {{ number_format(abs($closing_balance), 2) }} {{ $closing_balance_side }}</td>
            </tr>
        </tfoot>
    </table>
</div>
