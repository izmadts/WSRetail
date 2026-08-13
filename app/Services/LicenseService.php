<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Facades\Http;

/**
 * Client side of the WSRetail licensing system - activates/validates this
 * install against the remote license server (see the separate
 * wsretail-license-server app). The License::current() singleton row is
 * the local cache of whatever the server last said; isLocked() decides
 * whether that cache still counts as "good" (see License::isLocked() for
 * the grace-period rule).
 */
class LicenseService
{
    private const APP_VERSION = '1.0.0';

    public function graceDays(): int
    {
        return (int) config('services.license_server.grace_days', 7);
    }

    public function isLocked(): bool
    {
        return License::current()->isLocked($this->graceDays());
    }

    public function lockReason(): ?string
    {
        return License::current()->lockReason($this->graceDays());
    }

    public function activate(string $licenseKey): array
    {
        $license = License::current();
        $domain = request()->getHost();

        $response = $this->post('/api/v1/activate', [
            'license_key' => trim($licenseKey),
            'domain' => $domain,
            'fingerprint' => $license->fingerprint,
            'app_version' => self::APP_VERSION,
        ]);

        if (!$response['ok']) {
            $license->update(['last_error' => $response['message']]);

            return $response;
        }

        $data = $response['data'];
        $license->update([
            'license_key' => trim($licenseKey),
            'activation_token' => $data['activation_token'],
            'domain' => $domain,
            'remote_status' => $data['status'] ?? 'active',
            'expires_at' => $data['expires_at'] ?? null,
            'activated_at' => now(),
            'last_validated_at' => now(),
            'last_check_attempt_at' => now(),
            'last_error' => null,
        ]);

        return $response;
    }

    public function validate(): array
    {
        $license = License::current();

        if (!$license->isActivated()) {
            return ['ok' => false, 'message' => 'Not activated.'];
        }

        $response = $this->post('/api/v1/validate', [
            'license_key' => $license->license_key,
            'activation_token' => $license->activation_token,
            'domain' => $license->domain ?? request()->getHost(),
        ]);

        $update = ['last_check_attempt_at' => now()];

        if ($response['ok']) {
            $data = $response['data'];
            $update['remote_status'] = $data['status'] ?? 'active';
            $update['expires_at'] = $data['expires_at'] ?? null;
            $update['last_validated_at'] = now();
            $update['last_error'] = null;
        } else {
            // A definite "no" from the server (suspended/revoked/expired)
            // locks immediately; a network failure just leaves
            // last_validated_at where it was and lets the grace period do
            // its job - see License::isLocked().
            $update['last_error'] = $response['message'];
            if (!empty($response['data']['status'])) {
                $update['remote_status'] = $response['data']['status'];
            }
        }

        $license->update($update);

        return $response;
    }

    public function deactivate(): array
    {
        $license = License::current();

        if ($license->isActivated()) {
            $this->post('/api/v1/deactivate', [
                'license_key' => $license->license_key,
                'activation_token' => $license->activation_token,
            ]);
        }

        $license->update([
            'license_key' => null,
            'activation_token' => null,
            'domain' => null,
            'remote_status' => null,
            'expires_at' => null,
            'activated_at' => null,
            'last_validated_at' => null,
            'last_error' => null,
        ]);

        return ['ok' => true, 'message' => 'Deactivated.'];
    }

    /**
     * Fires a validate() at most once per recheck_interval_hours - called
     * opportunistically from the middleware so a self-hosted install still
     * self-heals even if no cron/scheduler is configured. Always swallows
     * failures; this must never break a page load.
     */
    public function maybeOpportunisticCheck(): void
    {
        $license = License::current();

        if (!$license->isActivated()) {
            return;
        }

        $interval = (int) config('services.license_server.recheck_interval_hours', 6);
        if ($license->last_check_attempt_at && $license->last_check_attempt_at->gt(now()->subHours($interval))) {
            return;
        }

        try {
            $this->validate();
        } catch (\Throwable $e) {
            // Best-effort only.
        }
    }

    private function post(string $path, array $payload): array
    {
        $baseUrl = rtrim(config('services.license_server.url'), '/');

        try {
            $response = Http::timeout((int) config('services.license_server.timeout', 5))
                ->acceptJson()
                ->post($baseUrl . $path, $payload);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach the license server: ' . $e->getMessage()];
        }

        $body = $response->json() ?? [];

        if (!$response->successful() || empty($body['success'])) {
            return [
                'ok' => false,
                'message' => $body['message'] ?? 'License server returned an error.',
                'data' => $body['data'] ?? null,
            ];
        }

        return ['ok' => true, 'message' => $body['message'] ?? 'OK', 'data' => $body['data'] ?? []];
    }
}
