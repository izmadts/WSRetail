@extends('layouts.agent')

@section('title', 'Target Report')
@section('page-title', 'Target vs Achievement')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-bullseye text-gray-400 mr-2"></i> Target Report
        </h4>
        <a href="{{ route('agent.reports.index') }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Back</a>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-blue-50 rounded-lg text-center">
                <p class="text-sm text-gray-500">This Month Target</p>
                <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($targetAmount ?? 0, 2) }}</p>
            </div>
            <div class="p-4 bg-green-50 rounded-lg text-center">
                <p class="text-sm text-gray-500">Achieved</p>
                <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($currentMonthSales ?? 0, 2) }}</p>
            </div>
            <div class="p-4 bg-purple-50 rounded-lg text-center">
                <p class="text-sm text-gray-500">Achievement</p>
                <p class="text-2xl font-bold {{ ($achievement ?? 0) >= 100 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($achievement ?? 0, 2) }}%
                </p>
                @if(($bonus ?? 0) > 0)
                    <p class="text-sm text-green-600">Bonus: Rs. {{ number_format($bonus, 2) }}</p>
                @endif
            </div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 px-2">Month</th>
                    <th class="text-right py-2 px-2">Target</th>
                    <th class="text-right py-2 px-2">Achieved</th>
                    <th class="text-right py-2 px-2">Achievement %</th>
                    <th class="text-right py-2 px-2">Bonus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($monthlyData as $data)
                <tr>
                    <td class="py-2 px-2">{{ $data['month'] }}</td>
                    <td class="py-2 px-2 text-right">Rs. {{ number_format($data['target'], 2) }}</td>
                    <td class="py-2 px-2 text-right">Rs. {{ number_format($data['achieved'], 2) }}</td>
                    <td class="py-2 px-2 text-right font-medium {{ $data['achievement'] >= 100 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($data['achievement'], 2) }}%
                    </td>
                    <td class="py-2 px-2 text-right text-purple-600">Rs. {{ number_format($data['bonus'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection