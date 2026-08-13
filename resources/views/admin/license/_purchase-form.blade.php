{{-- Shared by admin/license/index.blade.php (inline, unlicensed installs) and
     the "Buy This Software" topbar modal in layouts/admin.blade.php (every
     page, so it's reachable even on an already-activated demo install). --}}
<div class="rounded-xl border border-gray-200 bg-white p-4">
    <p class="text-sm font-semibold text-gray-800 mb-1">Don't have a license key?</p>
    <p class="text-sm text-gray-500 mb-3">Get in touch to purchase one, or pay by bank transfer and submit the form below.</p>
    <div class="flex flex-wrap gap-2">
        <a href="https://wa.me/923006163221" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            <i class="fab fa-whatsapp text-green-600"></i> +92 300 6163221
        </a>
        <a href="mailto:izmadts@gmail.com"
           class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            <i class="fas fa-envelope text-gray-500"></i> izmadts@gmail.com
        </a>
    </div>
</div>

@php $bank = config('services.license_purchase'); @endphp
<div class="rounded-xl border border-gray-200 bg-white p-4">
    <p class="text-sm font-semibold text-gray-800 mb-1">
        <i class="fas fa-university text-gray-400 mr-1"></i> Bank Transfer Details
    </p>
    <p class="text-sm text-gray-500 mb-3">
        Pay Rs. {{ number_format($bank['amount']) }} to the account below, then fill in the form
        underneath with your details and a screenshot/photo of the payment slip - we'll email you a
        license key once the payment is confirmed.
    </p>
    <div class="grid grid-cols-2 gap-3 text-sm bg-gray-50 rounded-lg p-3 mb-4">
        <div>
            <p class="text-xs text-gray-400 uppercase mb-0.5">Bank</p>
            <p class="font-medium text-gray-800">{{ $bank['bank_name'] }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase mb-0.5">Account Title</p>
            <p class="font-medium text-gray-800">{{ $bank['account_title'] }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase mb-0.5">Account Number</p>
            <p class="font-mono text-gray-800">{{ $bank['account_number'] }}</p>
        </div>
        @if ($bank['iban'])
        <div>
            <p class="text-xs text-gray-400 uppercase mb-0.5">IBAN</p>
            <p class="font-mono text-gray-800">{{ $bank['iban'] }}</p>
        </div>
        @endif
        @if ($bank['branch'])
        <div>
            <p class="text-xs text-gray-400 uppercase mb-0.5">Branch</p>
            <p class="font-medium text-gray-800">{{ $bank['branch'] }}</p>
        </div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.license.purchase-request') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Your Name *</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Business / Shop Name</label>
                <input type="text" name="business_name" value="{{ old('business_name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Phone / WhatsApp *</label>
                <input type="text" name="phone" required value="{{ old('phone') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Domain You're Deploying On</label>
                <input type="text" name="domain" placeholder="e.g. myshop.com" value="{{ old('domain') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Amount Paid (Rs.) *</label>
                <input type="number" name="amount_paid" step="0.01" min="0" required value="{{ old('amount_paid', $bank['amount']) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Payment Slip (screenshot or photo) *</label>
            <input type="file" name="payment_slip" required accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full text-sm text-gray-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-gray-900 file:text-white file:text-xs file:cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, or PDF - max 5MB.</p>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Notes (optional)</label>
            <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes') }}</textarea>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">
            <i class="fas fa-paper-plane mr-1"></i> Send Purchase Request
        </button>
    </form>
</div>
