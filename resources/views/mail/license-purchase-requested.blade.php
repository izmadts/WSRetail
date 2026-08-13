<x-mail::message>
# New License Purchase Request

Someone activated the "Buy a license" form on a self-hosted WSRetail install.

<x-mail::panel>
**Name:** {{ $details['name'] }}<br>
**Business/Shop:** {{ $details['business_name'] ?: '-' }}<br>
**Phone / WhatsApp:** {{ $details['phone'] }}<br>
**Email:** {{ $details['email'] ?: '-' }}<br>
**Domain they're deploying on:** {{ $details['domain'] ?: '-' }}<br>
**Amount paid:** Rs. {{ number_format((float) $details['amount_paid'], 2) }}<br>
**Payment slip attached:** {{ $details['has_slip'] ? 'Yes - see attachment' : 'No' }}
</x-mail::panel>

@if ($details['notes'])
**Notes from them:**

{{ $details['notes'] }}
@endif

Reply to this email (or WhatsApp/call them directly) to send a license key
once the payment is confirmed - see the License Server admin panel to
generate one.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
