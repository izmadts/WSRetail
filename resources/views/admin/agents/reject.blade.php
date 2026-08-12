@extends('layouts.admin')

@section('title', 'Reject Agent')
@section('page-title', 'Reject Agent: ' . $user->name)

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-times-circle text-red-600 mr-2"></i> Reject Agent Application
        </h3>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.agents.do-reject', $user) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection <span class="text-red-500">*</span></label>
                <textarea name="admin_note" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('admin_note') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Please provide a clear reason for rejection</p>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-times mr-1"></i> Reject Application
                </button>
                <a href="{{ route('admin.agents.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection