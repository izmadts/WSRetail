<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\ImportBatch;
use App\Models\ImportBatchItem;
use App\Models\Integration;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\UrlRedirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches from the connected platform, stages rows into ImportBatch/
 * ImportBatchItem for review (nothing touches real data yet), then commits
 * selected rows into Product/Customer/Sale/PaymentMethod/Setting. v1
 * (WooCommerce only, one-time import - see CHANGELOG): a WooCommerce
 * "variable" product's variations ARE exploded into WSRetail's real
 * Size/Color-style variant system (ProductAttribute/ProductAttributeValue/
 * ProductVariant), not flattened into one row - each variation's own SKU/
 * price/stock/image is preserved. Store settings (general/payment/shipping)
 * are staged and reviewed the same way as products/customers/orders - see
 * stageSettings().
 */
class IntegrationImportService
{
    public function client(Integration $integration): WooCommerceClient
    {
        $creds = $integration->credentials ?? [];

        return new WooCommerceClient(
            $creds['site_url'] ?? '',
            $creds['consumer_key'] ?? '',
            $creds['consumer_secret'] ?? '',
        );
    }

    public function stageProducts(Integration $integration, ?int $userId): ImportBatch
    {
        $client = $this->client($integration);
        $batch = ImportBatch::create(['integration_id' => $integration->id, 'type' => 'products', 'created_by' => $userId]);

        $page = 1;
        $fetched = 0;

        do {
            $result = $client->fetchProducts($page, 100);
            if (!$result['ok']) {
                break;
            }
            $rows = $result['data'];

            foreach ($rows as $row) {
                $variations = [];
                if (($row['type'] ?? 'simple') === 'variable') {
                    $vResult = $client->fetchProductVariations($row['id']);
                    if ($vResult['ok']) {
                        $variations = $vResult['data'];
                    }
                }

                $mapped = $this->mapProduct($row, $variations);
                $existing = Product::where('code', $mapped['code'])->first();

                ImportBatchItem::create([
                    'import_batch_id' => $batch->id,
                    'external_id' => (string) $row['id'],
                    'raw_payload' => $row,
                    'mapped_payload' => $mapped,
                    'action' => $existing ? 'update' : 'create',
                    'matched_model' => $existing ? Product::class : null,
                    'matched_id' => $existing?->id,
                ]);
                $fetched++;
            }

            $page++;
        } while (count($rows ?? []) === 100);

        $batch->update(['fetched_count' => $fetched]);

        return $batch;
    }

    public function stageCustomers(Integration $integration, ?int $userId): ImportBatch
    {
        $client = $this->client($integration);
        $batch = ImportBatch::create(['integration_id' => $integration->id, 'type' => 'customers', 'created_by' => $userId]);

        $page = 1;
        $fetched = 0;

        do {
            $result = $client->fetchCustomers($page, 100);
            if (!$result['ok']) {
                break;
            }
            $rows = $result['data'];

            foreach ($rows as $row) {
                $mapped = $this->mapCustomer($row);

                if (empty($mapped['email']) && empty($mapped['phone'])) {
                    continue; // no usable contact info to match/import against
                }

                $existing = $this->findCustomer($mapped['email'] ?? null, $mapped['phone'] ?? null);

                ImportBatchItem::create([
                    'import_batch_id' => $batch->id,
                    'external_id' => (string) $row['id'],
                    'raw_payload' => $row,
                    'mapped_payload' => $mapped,
                    'action' => $existing ? 'update' : 'create',
                    'matched_model' => $existing ? Customer::class : null,
                    'matched_id' => $existing?->id,
                ]);
                $fetched++;
            }

            $page++;
        } while (count($rows ?? []) === 100);

        $batch->update(['fetched_count' => $fetched]);

        return $batch;
    }

    public function stageOrders(Integration $integration, ?int $userId): ImportBatch
    {
        $client = $this->client($integration);
        $batch = ImportBatch::create(['integration_id' => $integration->id, 'type' => 'orders', 'created_by' => $userId]);

        $page = 1;
        $fetched = 0;

        do {
            $result = $client->fetchOrders($page, 100);
            if (!$result['ok']) {
                break;
            }
            $rows = $result['data'];

            foreach ($rows as $row) {
                $mapped = $this->mapOrder($row);
                $alreadyImported = Sale::where('source', 'woocommerce')
                    ->where('external_order_id', (string) $row['id'])
                    ->exists();

                ImportBatchItem::create([
                    'import_batch_id' => $batch->id,
                    'external_id' => (string) $row['id'],
                    'raw_payload' => $row,
                    'mapped_payload' => $mapped,
                    // Orders are never "updated" on re-import - once a sale
                    // exists in WSRetail, further changes go through the
                    // admin UI, not a re-run import overwriting a possibly
                    // already-confirmed/posted sale.
                    'action' => $alreadyImported ? 'skip' : 'create',
                    'included' => !$alreadyImported,
                ]);
                $fetched++;
            }

            $page++;
        } while (count($rows ?? []) === 100);

        $batch->update(['fetched_count' => $fetched]);

        return $batch;
    }

    /**
     * Store-level settings, not per-record data - a small, mixed batch (one
     * "store info" item, one item per payment gateway, one derived
     * "shipping" item), reviewed and confirmed through the exact same
     * stage -> review -> commit flow as products/customers/orders so it's
     * one consistent mental model, not a bespoke settings-only screen.
     */
    public function stageSettings(Integration $integration, ?int $userId): ImportBatch
    {
        $client = $this->client($integration);
        $batch = ImportBatch::create(['integration_id' => $integration->id, 'type' => 'settings', 'created_by' => $userId]);
        $fetched = 0;

        $generalResult = $client->fetchGeneralSettings();
        if ($generalResult['ok']) {
            $mapped = $this->mapStoreInfo($generalResult['data']);
            if (!empty($mapped['fields'])) {
                ImportBatchItem::create([
                    'import_batch_id' => $batch->id,
                    'external_id' => 'store_info',
                    'raw_payload' => $generalResult['data'],
                    'mapped_payload' => $mapped,
                    'action' => 'update',
                ]);
                $fetched++;
            }
        }

        $gatewaysResult = $client->fetchPaymentGateways();
        if ($gatewaysResult['ok']) {
            foreach ($gatewaysResult['data'] as $gateway) {
                $mapped = $this->mapPaymentGateway($gateway);
                $existing = PaymentMethod::where('code', $mapped['code'])->first();

                ImportBatchItem::create([
                    'import_batch_id' => $batch->id,
                    'external_id' => 'payment_' . $gateway['id'],
                    'raw_payload' => $gateway,
                    'mapped_payload' => $mapped,
                    'action' => $existing ? 'update' : 'create',
                    'matched_model' => $existing ? PaymentMethod::class : null,
                    'matched_id' => $existing?->id,
                    // Pre-checked only for gateways the old store actually
                    // had enabled - a store's full gateway list from
                    // WooCommerce includes plenty it never turned on.
                    'included' => $mapped['is_enabled'],
                ]);
                $fetched++;
            }
        }

        $zonesResult = $client->fetchShippingZones();
        if ($zonesResult['ok']) {
            $flatRate = null;
            $freeThreshold = null;
            $sourceZoneName = null;

            foreach ($zonesResult['data'] as $zone) {
                $methodsResult = $client->fetchShippingZoneMethods($zone['id']);
                if (!$methodsResult['ok']) {
                    continue;
                }

                foreach ($methodsResult['data'] as $method) {
                    if (!($method['enabled'] ?? false)) {
                        continue;
                    }

                    if ($method['method_id'] === 'flat_rate' && $flatRate === null) {
                        $flatRate = (float) ($method['settings']['cost']['value'] ?? 0);
                        $sourceZoneName = $zone['name'] ?? null;
                    }

                    if ($method['method_id'] === 'free_shipping' && $freeThreshold === null) {
                        $freeThreshold = (float) ($method['settings']['min_amount']['value'] ?? 0);
                    }
                }
            }

            if ($flatRate !== null || $freeThreshold !== null) {
                ImportBatchItem::create([
                    'import_batch_id' => $batch->id,
                    'external_id' => 'shipping',
                    'raw_payload' => $zonesResult['data'],
                    'mapped_payload' => [
                        'kind' => 'shipping',
                        'shipping_flat_rate' => $flatRate ?? 0,
                        'shipping_free_threshold' => $freeThreshold,
                        'source_zone_name' => $sourceZoneName,
                    ],
                    'action' => 'update',
                ]);
                $fetched++;
            }
        }

        $batch->update(['fetched_count' => $fetched]);

        return $batch;
    }

    public function commit(ImportBatch $batch): void
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($batch->items as $item) {
            if (!$item->included || $item->action === 'skip') {
                $item->update(['status' => 'committed']);
                $skipped++;
                continue;
            }

            try {
                $wasCreate = match ($batch->type) {
                    'products' => $this->commitProduct($item),
                    'customers' => $this->commitCustomer($item),
                    'orders' => $this->commitOrder($item),
                    'settings' => $this->commitSettingsItem($item),
                };

                $item->update(['status' => 'committed']);
                $wasCreate ? $created++ : $updated++;
            } catch (\Throwable $e) {
                $item->update(['status' => 'failed', 'error' => $e->getMessage()]);
                $skipped++;
            }
        }

        $batch->update([
            'status' => 'committed',
            'created_count' => $created,
            'updated_count' => $updated,
            'skipped_count' => $skipped,
            'committed_at' => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // Mapping: WooCommerce JSON shape -> WSRetail field shape
    // ------------------------------------------------------------------

    private function mapProduct(array $row, array $variations = []): array
    {
        $sku = trim($row['sku'] ?? '');
        $code = $sku !== '' ? $sku : 'WC-' . $row['id'];

        // WooCommerce's "regular_price"/"price" is the normal selling
        // price - that's what maps to WSRetail's "sale_price" column.
        // WooCommerce's OWN "sale_price" field means something different
        // (a temporary markdown price), so it is deliberately NOT used
        // here to avoid a same-name, different-meaning mix-up.
        $price = (float) ($row['regular_price'] !== '' ? $row['regular_price'] : ($row['price'] ?? 0));

        $dimensions = $row['dimensions'] ?? [];
        $images = [];
        foreach ($row['images'] ?? [] as $img) {
            if (!empty($img['src'])) {
                $images[] = ['url' => $img['src'], 'alt' => $img['alt'] ?? null];
            }
        }

        // Only attributes marked "Used for variations" in WooCommerce
        // generate real variants - a product can have other, purely
        // descriptive attributes (e.g. "Material: Cotton") that don't; those
        // have no equivalent field in WSRetail today (ProductAttribute/
        // ProductAttributeValue only model variant-defining attributes, not
        // free-form specs) and are intentionally not imported - flagged on
        // the review screen instead of silently dropped.
        $variationAttributeNames = collect($row['attributes'] ?? [])
            ->filter(fn ($a) => $a['variation'] ?? false)
            ->pluck('name')
            ->values()
            ->all();

        $nonVariationAttributes = collect($row['attributes'] ?? [])
            ->reject(fn ($a) => $a['variation'] ?? false)
            ->map(fn ($a) => ['name' => $a['name'] ?? '', 'values' => $a['options'] ?? []])
            ->values()
            ->all();

        $variants = [];
        foreach ($variations as $v) {
            $attrs = [];
            foreach ($v['attributes'] ?? [] as $a) {
                // WooCommerce variation attribute "option" values are
                // slugified for attributes backed by a global taxonomy
                // (e.g. "small" not "Small") - title-cased here so the
                // variant label/value matches what a shopper actually saw.
                $attrs[$a['name']] = ucwords(str_replace(['-', '_'], ' ', $a['option'] ?? ''));
            }

            $variantSku = trim($v['sku'] ?? '');
            $variantPrice = (float) ($v['regular_price'] !== '' ? $v['regular_price'] : ($v['price'] ?? 0));

            $variants[] = [
                'external_id' => (string) $v['id'],
                'sku' => $variantSku !== '' ? $variantSku : ($code . '-' . $v['id']),
                'label' => implode(' / ', array_filter(array_values($attrs), fn ($val) => $val !== '')),
                'attributes' => $attrs,
                'sale_price' => $variantPrice,
                'wholesale_price' => $variantPrice,
                'purchase_price' => 0,
                'current_stock' => (float) ($v['stock_quantity'] ?? 0),
                'image' => $v['image']['src'] ?? null,
                'is_active' => ($v['status'] ?? 'publish') === 'publish',
            ];
        }

        $isVariable = ($row['type'] ?? 'simple') === 'variable' && count($variants) > 0;

        return [
            'code' => $code,
            'name' => $row['name'] ?? ('WooCommerce Product #' . $row['id']),
            // Not a WSRetail Product column - carried through to commitProduct()
            // purely to build the old-URL -> new-URL SEO redirect (see
            // recordRedirect()). WSRetail's own `slug` column is auto-generated
            // from the name and must not be confused with this one.
            'legacy_slug' => $row['slug'] ?? null,
            'legacy_permalink' => $row['permalink'] ?? null,
            // WooCommerce ships description as raw HTML - stripped here
            // since WSRetail's product description is rendered as plain
            // text everywhere it's shown, not as HTML.
            'description' => isset($row['description']) ? trim(html_entity_decode(strip_tags($row['description']))) : null,
            'short_description' => isset($row['short_description']) ? trim(html_entity_decode(strip_tags($row['short_description']))) : null,
            // No native "brand" field on a core WooCommerce product (that's
            // a plugin-specific taxonomy, not part of the standard REST
            // response) - left null here. The column exists for Shopify's
            // "vendor" field, which IS a core, always-present field there.
            'brand' => null,
            'category_name' => $row['categories'][0]['name'] ?? null,
            'unit' => 'piece',
            'sale_price' => $price,
            'wholesale_price' => $price,
            'purchase_price' => 0,
            'current_stock' => (float) ($row['stock_quantity'] ?? 0),
            'weight' => $row['weight'] !== '' ? (float) $row['weight'] : null,
            'length' => ($dimensions['length'] ?? '') !== '' ? (float) $dimensions['length'] : null,
            'width' => ($dimensions['width'] ?? '') !== '' ? (float) $dimensions['width'] : null,
            'height' => ($dimensions['height'] ?? '') !== '' ? (float) $dimensions['height'] : null,
            'images' => $images,
            'is_active' => ($row['status'] ?? 'publish') === 'publish',
            'source_type' => $row['type'] ?? 'simple',
            'has_variants' => $isVariable,
            'variant_attribute_names' => $variationAttributeNames,
            'variants' => $variants,
            'non_variation_attributes' => array_filter($nonVariationAttributes, fn ($a) => $a['name'] !== ''),
        ];
    }

    private function mapCustomer(array $row): array
    {
        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $billing = $row['billing'] ?? [];

        return [
            'name' => $name !== '' ? $name : ($row['username'] ?? ('Customer #' . $row['id'])),
            'email' => $row['email'] ?? ($billing['email'] ?? null),
            'phone' => $billing['phone'] ?? null,
            'address' => $billing['address_1'] ?? null,
            'address_2' => $billing['address_2'] ?? null,
            'city' => $billing['city'] ?? null,
            'state' => $billing['state'] ?? null,
            'country' => $billing['country'] ?? null,
        ];
    }

    private function mapOrder(array $row): array
    {
        $billing = $row['billing'] ?? [];
        $shipping = $row['shipping'] ?? [];
        $name = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));

        $items = [];
        foreach ($row['line_items'] ?? [] as $line) {
            $items[] = [
                'sku' => trim($line['sku'] ?? ''),
                'name' => $line['name'] ?? '',
                'quantity' => (float) ($line['quantity'] ?? 1),
                'unit_price' => (float) ($line['price'] ?? 0),
                'total_price' => (float) ($line['total'] ?? 0),
            ];
        }

        $couponCode = $row['coupon_lines'][0]['code'] ?? null;

        // Full address snapshot as WooCommerce had it AT ORDER TIME - kept
        // separately from whatever ends up on the matched/created Customer
        // record, since a customer's saved address can change later
        // without rewriting the history of what was actually shipped where.
        $addressSnapshot = fn (array $a) => array_filter([
            'name' => trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?: null,
            'company' => $a['company'] ?? null,
            'address_1' => $a['address_1'] ?? null,
            'address_2' => $a['address_2'] ?? null,
            'city' => $a['city'] ?? null,
            'state' => $a['state'] ?? null,
            'postcode' => $a['postcode'] ?? null,
            'country' => $a['country'] ?? null,
            'phone' => $a['phone'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return [
            'external_order_id' => (string) $row['id'],
            'order_number' => $row['number'] ?? (string) $row['id'],
            'sale_date' => isset($row['date_created']) ? substr($row['date_created'], 0, 10) : now()->toDateString(),
            'payment_method' => $row['payment_method_title'] ?? ($row['payment_method'] ?? null),
            'coupon_code' => $couponCode,
            'customer' => [
                'name' => $name !== '' ? $name : 'WooCommerce Guest',
                'email' => $billing['email'] ?? null,
                'phone' => $billing['phone'] ?? null,
                'address' => $billing['address_1'] ?? null,
                'address_2' => $billing['address_2'] ?? null,
                'city' => $billing['city'] ?? null,
                'state' => $billing['state'] ?? null,
                'country' => $billing['country'] ?? null,
            ],
            'billing_address' => $addressSnapshot($billing) ?: null,
            'shipping_address' => $addressSnapshot($shipping) ?: null,
            'items' => $items,
            'sub_total' => (float) ($row['total'] ?? 0) - (float) ($row['shipping_total'] ?? 0) - (float) ($row['total_tax'] ?? 0),
            'shipping_cost' => (float) ($row['shipping_total'] ?? 0),
            'tax' => (float) ($row['total_tax'] ?? 0),
            'total_amount' => (float) ($row['total'] ?? 0),
        ];
    }

    /**
     * WooCommerce's /settings/general returns a flat list of {id, value}
     * objects, not the address as one blob - re-keyed by id here for easy
     * lookup, then assembled into the same shape Settings > Ecommerce >
     * Store saves. Only address/currency fields exist on WooCommerce core;
     * store phone/support email have no equivalent there (no plugin data is
     * read), so those two are left for the admin to fill in by hand.
     */
    private function mapStoreInfo(array $rawSettings): array
    {
        $byId = collect($rawSettings)->keyBy('id')->map(fn ($s) => $s['value'] ?? null);

        $addressLine1 = $byId->get('woocommerce_store_address');
        $addressLine2 = $byId->get('woocommerce_store_address_2');
        $city = $byId->get('woocommerce_store_city');
        $postcode = $byId->get('woocommerce_store_postcode');
        // WooCommerce stores this as "COUNTRY:STATE" (e.g. "PK:PB") - only
        // the country part is usable as free text here, WSRetail's
        // store_address is a single field, not country/state columns.
        $countryState = $byId->get('woocommerce_default_country');
        $country = $countryState ? strtok($countryState, ':') : null;

        $addressParts = array_filter([$addressLine1, $addressLine2, $city, $postcode, $country], fn ($v) => $v !== null && $v !== '');

        $fields = array_filter([
            'store_address' => $addressParts ? implode(', ', $addressParts) : null,
            'currency_code' => $byId->get('woocommerce_currency'),
        ], fn ($v) => $v !== null && $v !== '');

        return ['kind' => 'store_info', 'fields' => $fields];
    }

    private function mapPaymentGateway(array $gateway): array
    {
        return [
            'kind' => 'payment_gateway',
            'code' => $gateway['id'],
            'name' => $gateway['title'] ?: $gateway['method_title'] ?? $gateway['id'],
            // WooCommerce's gateway description is often admin-facing setup
            // help, not shopper-facing checkout copy - method_description is
            // the closer match to WSRetail's PaymentMethod::description,
            // falling back to description if that's all there is.
            'description' => isset($gateway['method_description'])
                ? trim(html_entity_decode(strip_tags($gateway['method_description'])))
                : (isset($gateway['description']) ? trim(html_entity_decode(strip_tags($gateway['description']))) : null),
            'is_enabled' => (bool) ($gateway['enabled'] ?? false),
        ];
    }

    // ------------------------------------------------------------------
    // Commit: mapped_payload -> real WSRetail records
    // ------------------------------------------------------------------

    private function commitProduct(ImportBatchItem $item): bool
    {
        $data = $item->mapped_payload;

        $category = null;
        if (!empty($data['category_name'])) {
            $category = Category::firstOrCreate(['name' => $data['category_name']], ['is_active' => true]);
        } else {
            $category = Category::firstOrCreate(['name' => 'Imported'], ['is_active' => true]);
        }

        $existing = Product::where('code', $data['code'])->first();
        $hasVariants = !empty($data['has_variants']) && !empty($data['variants']);

        $product = Product::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'legacy_slug' => $data['legacy_slug'] ?? null,
                'category_id' => $category->id,
                'unit' => $data['unit'],
                // A variant product's own price/stock columns are unused -
                // each variant carries its own (see ProductVariant, and the
                // same convention in ProductController::store()). Zeroed
                // here rather than left as the parent-level WooCommerce
                // values, which for a variable product are usually 0/blank
                // anyway (WooCommerce prices variable products per variation).
                'sale_price' => $hasVariants ? 0 : $data['sale_price'],
                'wholesale_price' => $hasVariants ? 0 : $data['wholesale_price'],
                'purchase_price' => $hasVariants ? 0 : $data['purchase_price'],
                'current_stock' => $hasVariants ? 0 : $data['current_stock'],
                'weight' => $data['weight'] ?? null,
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'is_active' => $data['is_active'],
                'is_retail' => true,
                'is_wholesale' => false,
                'has_variants' => $hasVariants,
            ]
        );

        if (!empty($data['images'])) {
            $this->syncProductImages($product, $data['images']);
        }

        if ($hasVariants) {
            $this->syncProductVariants($product, $data['variants']);
        }

        $this->recordRedirect($product, $data['legacy_permalink'] ?? null, $data['legacy_slug'] ?? null);

        return !$existing;
    }

    /**
     * Explodes a WooCommerce variable product's variations into real
     * ProductAttribute/ProductAttributeValue/ProductVariant rows - the same
     * structure the admin's own variant builder produces (see
     * ProductController::saveVariants()), not a flattened single price/
     * stock. Attributes are matched by name/value globally (firstOrCreate),
     * same as a store owner reusing "Color: Red" across many products.
     * Matched/updated by SKU on re-import, same convention as the parent
     * product itself.
     */
    private function syncProductVariants(Product $product, array $variants): void
    {
        foreach ($variants as $v) {
            $attributeValueIds = [];

            foreach ($v['attributes'] as $attrName => $attrValue) {
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);
                if ($attrName === '' || $attrValue === '') {
                    continue;
                }

                $attribute = ProductAttribute::firstOrCreate(['name' => $attrName]);
                $value = ProductAttributeValue::firstOrCreate([
                    'product_attribute_id' => $attribute->id,
                    'value' => $attrValue,
                ]);
                $attributeValueIds[] = $value->id;
            }

            $variant = ProductVariant::updateOrCreate(
                ['sku' => $v['sku']],
                [
                    'product_id' => $product->id,
                    'label' => $v['label'] !== '' ? $v['label'] : $v['sku'],
                    'purchase_price' => $v['purchase_price'],
                    'sale_price' => $v['sale_price'],
                    'wholesale_price' => $v['wholesale_price'],
                    'current_stock' => $v['current_stock'],
                    'is_active' => $v['is_active'],
                ]
            );

            if (!empty($attributeValueIds)) {
                $variant->attributeValues()->sync($attributeValueIds);
            }

            if (!empty($v['image'])) {
                $downloaded = $this->downloadImage($v['image'], 'wc-variant-' . $variant->id);
                if ($downloaded) {
                    $variant->update(['image' => $downloaded]);
                }
            }
        }
    }

    /**
     * Captures a redirect from the product's OLD store URL to its new
     * WSRetail storefront page, so a store that already ranks in search
     * results for its WooCommerce product URLs doesn't lose that ranking
     * the moment the old site is replaced/pointed at the new one. Prefers
     * the full permalink (respects whatever custom permalink structure the
     * old store used, e.g. "/shop/red-t-shirt/"), falling back to
     * WooCommerce's default "/product/{slug}/" shape when no permalink is
     * present. Append-only by design: re-importing after a slug rename on
     * the source store adds a NEW old_path rather than touching a
     * previously captured one, since that older URL may still be indexed
     * or bookmarked too.
     */
    private function recordRedirect(Product $product, ?string $permalink, ?string $slug): void
    {
        $oldPath = null;

        if ($permalink) {
            $path = parse_url($permalink, PHP_URL_PATH);
            if ($path) {
                $oldPath = '/' . trim($path, '/');
            }
        }

        if (!$oldPath && $slug) {
            $oldPath = '/product/' . trim($slug, '/');
        }

        if (!$oldPath || $oldPath === '/') {
            return;
        }

        $newPath = '/' . $product->slug;
        if ($oldPath === $newPath) {
            return;
        }

        UrlRedirect::updateOrCreate(
            ['old_path' => $oldPath],
            ['product_id' => $product->id, 'new_path' => $newPath, 'source' => 'woocommerce']
        );
    }

    /**
     * Downloads each WooCommerce image URL and stores it the same way
     * ProductController's own image upload does (public_path('uploads/
     * products'), not the storage disk) so every product image - manually
     * uploaded or imported - renders through the exact same asset() path.
     * One bad image (dead URL, timeout, non-image response) is skipped,
     * not treated as a reason to fail the whole product.
     */
    private function syncProductImages(Product $product, array $images): void
    {
        $product->images()->delete();
        $isFirst = true;

        foreach (array_slice($images, 0, 8) as $index => $image) {
            $relativePath = $this->downloadImage($image['url'], 'wc-' . $product->id);
            if (!$relativePath) {
                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $relativePath,
                'external_url' => $image['url'],
                'sort_order' => $index,
            ]);

            if ($isFirst) {
                $product->update(['image' => $relativePath]);
                $isFirst = false;
            }
        }
    }

    /**
     * Shared by product gallery images and variant images - downloads a
     * remote image and stores it under public_path('uploads/products'),
     * the same convention ProductController's own manual upload uses, so
     * every image (manual or imported) renders through the exact same
     * asset() path. Returns the relative path, or null if the URL didn't
     * resolve to a real image - callers skip that one image rather than
     * failing the whole product/variant.
     */
    private function downloadImage(string $url, string $prefix): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful() || !str_starts_with($response->header('Content-Type') ?? '', 'image/')) {
                return null;
            }

            $extension = match ($response->header('Content-Type')) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg',
            };
            $relativePath = 'uploads/products/' . $prefix . '-' . uniqid() . '.' . $extension;
            file_put_contents(public_path($relativePath), $response->body());

            return $relativePath;
        } catch (\Throwable $e) {
            Log::warning("Image import failed ({$url}): " . $e->getMessage());

            return null;
        }
    }

    private function commitCustomer(ImportBatchItem $item): bool
    {
        $data = $item->mapped_payload;
        $existing = $this->findCustomer($data['email'] ?? null, $data['phone'] ?? null);

        $fields = array_filter([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'address_2' => $data['address_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        if ($existing) {
            $existing->update($fields);
        } else {
            Customer::create($fields + ['is_active' => true]);
        }

        return !$existing;
    }

    private function commitOrder(ImportBatchItem $item): bool
    {
        $data = $item->mapped_payload;

        return DB::transaction(function () use ($data) {
            $customer = $this->findCustomer($data['customer']['email'] ?? null, $data['customer']['phone'] ?? null);
            if (!$customer) {
                $customer = Customer::create(array_filter([
                    'name' => $data['customer']['name'],
                    'email' => $data['customer']['email'] ?? null,
                    'phone' => $data['customer']['phone'] ?? null,
                    'address' => $data['customer']['address'] ?? null,
                    'address_2' => $data['customer']['address_2'] ?? null,
                    'city' => $data['customer']['city'] ?? null,
                    'state' => $data['customer']['state'] ?? null,
                    'country' => $data['customer']['country'] ?? null,
                    'is_active' => true,
                ], fn ($v) => $v !== null && $v !== ''));
            }

            $itemsData = [];
            foreach ($data['items'] as $line) {
                // A WooCommerce order line's SKU is a VARIATION's SKU for a
                // variable product (e.g. "TSHIRT-RED"), not the parent
                // product's own code ("TSHIRT") - checked first, since
                // matching only against Product::code would silently fail
                // every line from a variable product.
                $variant = $line['sku'] !== '' ? ProductVariant::where('sku', $line['sku'])->first() : null;
                $product = $variant ? $variant->product : ($line['sku'] !== '' ? Product::where('code', $line['sku'])->first() : null);

                $itemsData[] = [
                    'product_id' => $product?->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount' => 0,
                    'tax' => 0,
                    'total_price' => $line['total_price'],
                    '_matched' => (bool) $product,
                ];
            }

            if (!collect($itemsData)->contains('_matched', true)) {
                throw new \RuntimeException('No line item matched an existing product by SKU - import products first, then re-run the order import.');
            }

            $sale = Sale::create([
                'customer_id' => $customer->id,
                'source' => 'woocommerce',
                'external_order_id' => $data['external_order_id'],
                'sale_date' => $data['sale_date'],
                'payment_term' => 'cash',
                'payment_method' => $data['payment_method'] ?? null,
                'coupon_code' => $data['coupon_code'] ?? null,
                'status' => 'draft',
                'sub_total' => $data['sub_total'],
                'discount' => 0,
                'tax' => $data['tax'],
                'shipping_cost' => $data['shipping_cost'],
                'total_amount' => $data['total_amount'],
                'paid_amount' => 0,
                'due_amount' => $data['total_amount'],
                'notes' => 'Imported from WooCommerce - order #' . $data['order_number'],
                'billing_address' => $data['billing_address'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'created_by' => null,
            ]);

            foreach ($itemsData as $line) {
                if (!$line['_matched']) {
                    continue; // unmatched SKU - line skipped, not invented
                }
                unset($line['_matched']);
                $sale->items()->create($line);
            }

            return true;
        });
    }

    /**
     * A settings batch is heterogeneous (store info / payment gateway /
     * shipping all in one batch, unlike products/customers/orders which are
     * each one uniform type) - dispatches on the "kind" tag mapStoreInfo()/
     * mapPaymentGateway()/the inline shipping mapper stamped into
     * mapped_payload at staging time.
     */
    private function commitSettingsItem(ImportBatchItem $item): bool
    {
        $data = $item->mapped_payload;

        return match ($data['kind']) {
            'store_info' => $this->commitStoreInfo($data),
            'payment_gateway' => $this->commitPaymentGateway($data),
            'shipping' => $this->commitShipping($data),
        };
    }

    private function commitStoreInfo(array $data): bool
    {
        foreach ($data['fields'] as $key => $value) {
            Setting::set($key, $value);
        }

        return false; // always an "update" - Settings have no create/update distinction
    }

    private function commitPaymentGateway(array $data): bool
    {
        $existing = PaymentMethod::where('code', $data['code'])->first();

        PaymentMethod::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_enabled' => $data['is_enabled'],
                'sort_order' => $existing->sort_order ?? (PaymentMethod::max('sort_order') ?? 0) + 1,
            ]
        );

        return !$existing;
    }

    private function commitShipping(array $data): bool
    {
        Setting::set('shipping_flat_rate', $data['shipping_flat_rate']);
        Setting::set('shipping_free_threshold', $data['shipping_free_threshold'] ?? '');

        return false;
    }

    private function findCustomer(?string $email, ?string $phone): ?Customer
    {
        if ($email) {
            $byEmail = Customer::where('email', $email)->first();
            if ($byEmail) {
                return $byEmail;
            }
        }

        if ($phone) {
            return Customer::where('phone', $phone)->first();
        }

        return null;
    }
}
