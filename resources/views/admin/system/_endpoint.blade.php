@php
    $methodColors = [
        'GET' => 'bg-blue-100 text-blue-800',
        'POST' => 'bg-green-100 text-green-800',
        'PUT' => 'bg-yellow-100 text-yellow-800',
        'PATCH' => 'bg-yellow-100 text-yellow-800',
        'DELETE' => 'bg-red-100 text-red-800',
    ];
    $auth = $auth ?? true;
    $base = $base ?? '/api/v1/agent';
@endphp
<div class="border border-gray-200 rounded-lg p-4 mb-3">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold font-mono {{ $methodColors[$method] ?? 'bg-gray-100 text-gray-800' }}">{{ $method }}</span>
        <code class="text-sm font-mono text-gray-800">{{ $base }}{{ $path }}</code>
        @if($auth)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-lock mr-1"></i>Auth required</span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"><i class="fas fa-lock-open mr-1"></i>Public</span>
        @endif
    </div>

    @if(!empty($description))
    <p class="text-sm text-gray-600 mb-2">{{ $description }}</p>
    @endif

    @if(!empty($query))
    <p class="text-xs font-semibold text-gray-500 uppercase mt-3 mb-1">Query Parameters</p>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <tbody class="divide-y divide-gray-100">
                @foreach($query as $field => $rule)
                <tr><td class="py-1 pr-3 font-mono text-gray-700 whitespace-nowrap">{{ $field }}</td><td class="py-1 text-gray-500">{{ $rule }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($body))
    <p class="text-xs font-semibold text-gray-500 uppercase mt-3 mb-1">Request Body</p>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <tbody class="divide-y divide-gray-100">
                @foreach($body as $field => $rule)
                <tr><td class="py-1 pr-3 font-mono text-gray-700 whitespace-nowrap align-top">{{ $field }}</td><td class="py-1 text-gray-500">{{ $rule }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($response))
    <p class="text-xs font-semibold text-gray-500 uppercase mt-3 mb-1">Example Response</p>
    <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>{{ $response }}</code></pre>
    @endif
</div>
