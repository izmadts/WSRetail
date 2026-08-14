<dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm max-w-2xl">
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Name</dt><dd class="text-gray-800">{{ $m['name'] }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Email</dt><dd class="text-gray-800">{{ $m['email'] ?? '—' }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Phone</dt><dd class="text-gray-800">{{ $m['phone'] ?? '—' }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Address</dt><dd class="text-gray-800">{{ $m['address'] ?? '—' }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Address 2</dt><dd class="text-gray-800">{{ $m['address_2'] ?? '—' }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">City</dt><dd class="text-gray-800">{{ $m['city'] ?? '—' }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">State</dt><dd class="text-gray-800">{{ $m['state'] ?? '—' }}</dd></div>
    <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Country</dt><dd class="text-gray-800">{{ $m['country'] ?? '—' }}</dd></div>
</dl>
