<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the WooCommerce REST API v3
 * (https://woocommerce.github.io/woocommerce-rest-api-docs/). Auth is
 * Basic Auth with the consumer key as username / consumer secret as
 * password - WooCommerce only accepts that over HTTPS, so a plain-HTTP
 * site_url is rejected up front rather than silently failing every call.
 */
class WooCommerceClient
{
    public function __construct(
        private string $siteUrl,
        private string $consumerKey,
        private string $consumerSecret,
    ) {
    }

    public function testConnection(): array
    {
        if (!str_starts_with($this->siteUrl, 'https://')) {
            return ['ok' => false, 'message' => 'Site URL must start with https:// - WooCommerce rejects Basic Auth credentials over plain HTTP.'];
        }

        $response = $this->request('products', ['per_page' => 1]);

        if (!$response['ok']) {
            return $response;
        }

        return ['ok' => true, 'message' => 'Connected successfully.'];
    }

    /** @return array{ok: bool, data?: array, message?: string} */
    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        return $this->request('products', ['page' => $page, 'per_page' => $perPage]);
    }

    public function fetchProductVariations(int $productId): array
    {
        return $this->request("products/{$productId}/variations", ['per_page' => 100]);
    }

    public function fetchCustomers(int $page = 1, int $perPage = 100): array
    {
        return $this->request('customers', ['page' => $page, 'per_page' => $perPage]);
    }

    public function fetchOrders(int $page = 1, int $perPage = 100): array
    {
        return $this->request('orders', ['page' => $page, 'per_page' => $perPage, 'status' => 'any']);
    }

    /** General store settings (address, currency, etc.) - GET /settings/general. */
    public function fetchGeneralSettings(): array
    {
        return $this->request('settings/general');
    }

    public function fetchPaymentGateways(): array
    {
        return $this->request('payment_gateways');
    }

    public function fetchShippingZones(): array
    {
        return $this->request('shipping/zones');
    }

    public function fetchShippingZoneMethods(int $zoneId): array
    {
        return $this->request("shipping/zones/{$zoneId}/methods");
    }

    private function request(string $endpoint, array $query = []): array
    {
        $baseUrl = rtrim($this->siteUrl, '/') . '/wp-json/wc/v3/';

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->timeout(20)
                ->acceptJson()
                ->get($baseUrl . $endpoint, $query);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach the WooCommerce site: ' . $e->getMessage()];
        }

        if (!$response->successful()) {
            $body = $response->json();

            return [
                'ok' => false,
                'message' => $body['message'] ?? ('WooCommerce returned HTTP ' . $response->status() . '.'),
            ];
        }

        return ['ok' => true, 'data' => $response->json() ?? []];
    }
}
