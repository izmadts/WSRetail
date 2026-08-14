# WSRetail Changelog

Running record of what's been built/fixed, kept for whoever (human or AI)
picks this project up next. Newest entries first. This is a work log, not
user-facing release notes.

## 2026-08-14 (7)

**Demo Mode credentials can no longer drift out of sync with reality.**
Triggered directly by a live incident on demo.izmadts.com: the login-page
hint showed `admin@demo.com` / `demoAdmin321`, but no account with those
credentials actually existed - `demo_credentials_note` was always just a
free-text `Setting` string an admin typed by hand, with nothing tying it to
a real `User` row.

- Replaced the single free-text field with two structured ones,
  `demo_email` / `demo_password` (Settings > General > Demo Mode). Both
  required together or both left blank (`required_with` each way) - a
  half-filled pair would recreate the exact same confusion for a different
  reason.
- **Saving them now provisions the account, not just the display text**:
  `SettingsController::updateGeneral()` calls
  `User::updateOrCreate(['email' => $demo_email], [..., 'role' => 'admin',
  'is_active' => true, 'approved_at' => now()])` whenever both fields are
  present. Both `is_active` AND `approved_at` matter -
  `User::isActive()` requires both; setting only one would silently leave
  the account locked even with the exact right password, which is likely
  how the original mismatch happened in the first place.
  Changing the demo email later does not delete/deactivate the previous
  account - deliberate, since it may have real demo activity tied to it as
  `created_by` elsewhere.
- Login page now also **pre-fills both fields** with the real values
  whenever Demo Mode is active with both set, so a visitor can click Sign
  In with no typing at all - not just see a hint and copy it manually.
- `AppServiceProvider`'s view composer and `auth/login.blade.php` updated
  to match (`demoEmail`/`demoPassword` instead of `demoCredentialsNote`).
  No migration needed - both old and new are just `Setting` key/value rows.
- Live-tested end-to-end over real HTTP: saved both fields via the actual
  settings form, confirmed the account was created active/approved with a
  matching password hash, confirmed the login page rendered both fields
  pre-filled with the correct values, then submitted exactly that pre-filled
  form and confirmed it reached `/admin/dashboard`. Fixtures removed and
  demo_mode reset to off afterward.

## 2026-08-14 (6)

**Two real bugs found via the user's own live import** (against a real
WooCommerce store, not a fake), both pre-existing patterns from earlier in
the session that this surfaced for the first time:

- **Misleading "0 created, 0 updated, 0 skipped" success message after a
  real, successful commit.** `IntegrationController::commitImport()` called
  `$service->commit($batch->fresh('items'))` - `fresh()` returns a
  SEPARATE cloned model instance; `commit()` correctly wrote the real
  counts onto that clone (confirmed in the DB: a real settings import had
  actually created 1, updated 3, skipped 2), but the success-message string
  right after read `$batch->created_count` etc from the ORIGINAL,
  never-updated variable, which still held its just-created defaults
  (0/0/0). Every commit of every import type (products/customers/orders/
  settings) showed this wrong message - the data was always fine, the
  message never was. Fixed with `$batch->refresh()` before building the
  message. Caught only because the user reported the confusing message and
  it was checked against the actual DB state, which told a different story.
- **Review screen's expand/collapse arrow did nothing.** Each row's detail
  panel was a second `<tr x-show="open">` as a SIBLING of the summary
  `<tr x-data="{ open: false }">` - Alpine.js scope only flows to
  descendants, so the detail row had no access to the summary row's `open`
  state at all; clicking the chevron toggled a variable nothing was
  listening to. Fixed by wrapping each item's pair of `<tr>`s in its own
  `<tbody x-data="{ open: false }">` (a table can validly have multiple
  `<tbody>` elements) so both rows share one scope. Verified via real HTTP
  that the rendered markup nests correctly.

## 2026-08-14 (5)

**Full-fidelity WooCommerce import: variants, settings, and a real review
screen.** Follow-up to the WooCommerce integration work above, addressing
three gaps the user flagged directly: form validation/placeholders,
incomplete review-screen data, and no way to bring over the old store's own
settings.

- **Variants/attributes are no longer flattened.** A WooCommerce "variable"
  product's variations are now exploded into WSRetail's real
  ProductAttribute/ProductAttributeValue/ProductVariant system - the same
  structure the admin's own variant builder produces - instead of
  collapsing into one simple-product row. Each variation keeps its own SKU,
  price, stock, and image (downloaded the same way product gallery images
  are). Only attributes marked "Used for variations" generate variants;
  purely descriptive WooCommerce attributes (e.g. "Material: Cotton") have
  no equivalent field in WSRetail today and are surfaced on the review
  screen as "not imported" rather than silently dropped or faked into the
  variant system.
- **Real bug caught by testing**: order line items reference a *variation's*
  SKU for a variable product (e.g. "TSHIRT-RED"), not the parent product's
  own code ("TSHIRT"). `IntegrationImportService::commitOrder()` only ever
  matched against `Product::code`, so every line from a variable product
  would silently fail to match and the whole order would be rejected
  ("No line item matched an existing product by SKU"). Fixed by checking
  `ProductVariant::sku` first and resolving to its parent product, writing
  both `product_id` and `product_variant_id` on the SaleItem - caught by
  actually placing a variant-product order through the import pipeline in
  a live test, not by inspection.
- **Old store settings import** (Settings > Ecommerce): a new "Import
  Settings" button pulls the old WooCommerce store's general settings
  (address, currency), payment gateways, and shipping - staged and reviewed
  through the exact same stage > review > commit flow as products/
  customers/orders, not a bespoke screen. Store address/currency map into
  `Setting`; each payment gateway maps into a `PaymentMethod` row
  (pre-unchecked on the review screen if it was disabled on the old store);
  shipping is approximated as WSRetail's single flat-rate + free-threshold
  model, using the first enabled `flat_rate`/`free_shipping` method found
  across the old store's shipping zones - explicitly flagged on the review
  screen as an approximation, since WooCommerce supports full per-zone
  rates and WSRetail (deliberately, see the Ecommerce Settings entry above)
  does not. WooCommerce has no store phone/support-email field, so those
  are left for the admin to fill in directly rather than being overwritten
  with nothing.
- **Review screen now shows everything that will be written**, not a
  summary row: each row expands (click to toggle) into the full mapped
  data - for products: description, short description, brand, weight/
  dimensions, the old URL, every gallery image as a thumbnail, and (for
  variable products) a full variant table with per-variant image/SKU/
  price/stock; for customers: every address field; for orders: full
  customer contact info, payment method, coupon, both billing and shipping
  address snapshots, and every line item with a red-flagged
  will-definitely-be-skipped indicator for SKU-less lines; for settings:
  the specific fields/values about to be written, per item.
- **Form validation/placeholder audit** across the Integrations connect
  form and all three Ecommerce Settings tabs: added placeholders showing
  expected formats (phone, WooCommerce key prefixes, currency amounts),
  tightened validation (`store_phone` now regex-checked against a
  digits/+/-/()/. pattern instead of accepting any string, shipping
  rate/threshold capped at sane upper bounds against fat-finger typos,
  payment method name requires a minimum length, WooCommerce API keys
  require a minimum length as a basic sanity check). Deliberately did NOT
  add DNS-checking email validation (`email:dns`) - inconsistent with every
  other email field in the codebase and prone to false rejections on flaky
  outbound DNS, so kept to the same plain `email` rule used everywhere else.
- Live-tested via `Http::fake()`: staged and committed a variable product
  with 2 variations (one with an image, one without - confirmed the
  no-image one is skipped without failing the variant), confirmed
  `ProductAttribute`/`ProductAttributeValue`/`ProductVariant` rows and the
  downloaded variant image landed correctly; staged and committed a full
  settings batch (general/payment/shipping) and confirmed `Setting` and
  `PaymentMethod` rows updated correctly, including the disabled-gateway
  pre-unchecked behavior; re-tested order commit after the SKU-matching
  fix and confirmed both `product_id` and `product_variant_id` landed on
  the SaleItem. Loaded the review screen for all four batch types
  (products/customers/orders/settings) over real HTTP and grep-verified
  the expanded detail actually renders the right data. All fixtures
  (products, variants, attributes, customers, sales, batches, settings,
  payment methods) removed afterward; a stray "Cash on Delivery" ->
  "Cash on delivery" casing change from the settings-import test was
  reverted to its original default.

## 2026-08-14 (4)

**Ecommerce Settings** - new tabbed settings area (Settings > Ecommerce),
mirroring WooCommerce's own Settings tab shape for a familiar landing point
to a store owner migrating from there. Deliberately scoped down from a full
WooCommerce-parity settings suite (confirmed with the user):

- **Store** - `storefront_url` (moved here from where it was first added
  under Integrations > SEO Redirects, which now shows it read-only with a
  link back to this page instead of its own duplicate form), plus
  `store_address` / `store_phone` / `store_email`. App name/currency/
  logo/theme stay in the existing General settings - this tab only holds
  what's specific to the storefront-facing side.
- **Payment** - method toggles, not a gateway integration (WSRetail has no
  online payment processor wired up). New `payment_methods` table
  (code/name/description/is_enabled/sort_order), seeded with Cash on
  Delivery (enabled) and Bank Transfer (disabled) as defaults; admin can
  rename, edit the checkout-facing description, enable/disable, add, or
  remove methods. A shopper's pick still lands as free text on
  `Sale::payment_method`, unchanged - this only controls what's *offered*.
  New `GET /api/v1/customer/payment-methods` (auth required, same group as
  `/products`/`/categories`) returns only enabled methods, documented in the
  API docs under a new "Payment Methods" section.
- **Shipping** - a single flat rate (`shipping_flat_rate`) plus an optional
  free-shipping threshold (`shipping_free_threshold`), not per-zone/
  per-city rates - a real multi-zone shipping engine is a separate, much
  larger feature. **Actually wired into checkout**, not a dead setting:
  `OrderController::store()` now computes `shipping_cost` server-side
  (waived once subtotal reaches the threshold) instead of always writing 0,
  and folds it into `total_amount`/`due_amount`. Admin-created sales (POS/
  manual) are untouched - shipping only applies to storefront checkout.
- Sidebar: new "Ecommerce Settings" entry; added as a tab in the shared
  Settings tab strip (`admin.settings.partials.tabs`) alongside General/
  Credit/etc., with its own Store/Payment/Shipping sub-tab strip on each of
  the three pages.
- Bug caught before it shipped: the "add payment method" form and each
  existing method's "edit" form share one page, and the add-form originally
  used the same field names (`name`/`description`) as the edit forms - a
  failed add submission would flash `old('name') = ''` globally and blank
  every row's displayed name. Fixed by namespacing the add-form's fields
  (`new_name`/`new_description`); also guarded `PaymentMethod::code`
  (unique) against a collision when two methods slugify to the same value.
- Live-tested end-to-end as a throwaway admin over real HTTP: saved Store/
  Shipping settings and confirmed the `Setting` rows landed correctly;
  added a payment method via the admin form and confirmed only enabled
  methods appear from the Customer API; placed two real orders through
  `POST /api/v1/customer/orders` against a throwaway product (₨1000 each) -
  one below the ₨3000 threshold correctly charged the ₨150 flat rate
  (total 2150), one at/above it correctly waived shipping (total 4000).
  Regression-checked 12 admin pages afterward; all fixtures removed.

## 2026-08-14 (3)

**SEO-safe URL redirects for CMS product imports.** A store migrating from
WooCommerce (or another CMS later) may already have search ranking / indexed
URLs / customer bookmarks pointing at its old product pages. Losing those on
cutover is a real, avoidable cost - so every product import now captures a
redirect from the old URL to the new one automatically, no extra admin step.

- Migrations: `products.legacy_slug` (the product's slug on the *source*
  platform - kept distinct from WSRetail's own auto-generated `slug`, which
  `Product::boot()` always overwrites from the current name); new
  `url_redirects` table (`old_path` unique, `new_path`, `product_id`,
  `source`).
- `IntegrationImportService::mapProduct()` now carries WooCommerce's `slug`
  and full `permalink` through to `commitProduct()`, which calls the new
  `recordRedirect()`: prefers the real permalink path (so it respects
  whatever custom permalink structure the old store used, e.g.
  `/shop/%postname%/`), falls back to WooCommerce's default
  `/product/{slug}/` shape when no permalink is present. Append-only by
  design - re-importing after a slug rename on the source store adds a new
  `old_path` rather than overwriting a previously captured one, since the
  older URL may still be indexed/bookmarked too.
- New public (no customer token, throttled) endpoint
  `GET /api/v1/customer/redirect?path=...` resolves an old path to
  `{old_path, new_path, product_id}` - has to be public since it must
  resolve for an anonymous visitor who followed an old link before any
  `/connect` has happened. Documented in the API docs (new "SEO Redirects"
  section).
- Storefront wiring: `wsretail-storefront/app/product/[...slug]/page.tsx`
  catches WooCommerce's default permalink shape, calls the lookup endpoint
  server-side, and issues a real `permanentRedirect()` (HTTP 308) - not a
  client-side redirect, which search engines don't credit the same way.
- Admin UI: Settings > Integrations > SEO Redirects - lists every captured
  redirect, lets the admin set a `storefront_url` setting (used to build
  full URLs in exports), and exports a ready-to-paste `.htaccess` or
  `nginx.conf` rule file for stores that will point their *old* domain's web
  server/CDN straight at the new one rather than routing traffic through the
  Next.js app at all.
- Live-tested via `Http::fake()` (staged + committed a fake WooCommerce
  product with a custom permalink, confirmed `legacy_slug` and the
  `url_redirects` row landed correctly) and via real HTTP (the public lookup
  endpoint through the license-gated API, the admin redirects page, and both
  export formats) as a throwaway admin; fixtures removed afterward.

> **Note:** WSRetail was cloned from WSERP with the Golden Club (loyalty/
> rewards/referral) module intentionally left out — this is a POS/retail
> build, not a loyalty platform. Entries below that came from the WSERP
> history and covered Golden Club (and the Golden-Club-only parts of the
> Sale Agent/Vendor mobile apps) have been removed since none of that code
> exists in this codebase. Everything else — reporting, credit/stock
> settings, bug fixes, UI fixes — applies here too and is kept as-is.

## 2026-08-14 (2)

**Standard e-commerce field audit**, prompted by "make sure standard
product/customer/order fields exist in our database" before more CMS
connectors get built on top of gaps. Compared WSRetail's schema against
what WooCommerce/Shopify/Magento all commonly expose and closed the real
gaps - not a redesign, just making sure the data has somewhere to go.

**New product fields**: `short_description`, `brand` (nullable - no core
WooCommerce field maps to it, but Shopify's `vendor` will), `weight`,
`length`, `width`, `height`. New `product_images` table + `Product::images()`
- the single `image` column stays the primary/thumbnail everywhere it's
already used, `images` is the rest of the gallery every platform actually
has (WSRetail only ever had room for one).

**New customer field**: `address_2` (apartment/suite line - every platform
splits this from the main address line; WSRetail only had one).

**New sale fields**: `payment_method` (the actual checkout method/gateway
name, e.g. "Cash on Delivery" - distinct from `payment_term`'s cash/credit
accounting classification), `coupon_code`, `billing_address` +
`shipping_address` (JSON, a point-in-time snapshot of the order's own
address - deliberately NOT a live reference to the customer's saved
address, since a customer's profile address can change later without
rewriting what was actually shipped where on old orders, and a guest/one-
off order's ship-to may never have matched any saved customer address).

**Deliberately NOT added**, and why: a per-order **currency** column
(WSRetail's accounting is single-currency throughout - a stored-but-unused
field would be actively misleading); **multiple categories per product**
or a full **multi-address address-book per customer** (both are real
things WooCommerce/Shopify/Magento support, but both are structural
features - many-to-many category assignment, a proper address book with
default billing/shipping flags - not a missing-column fix, and would touch
most of the existing product/customer UI). Both are flagged here as known,
deliberate v1 simplifications rather than silently unhandled.

**Wired all the way through, not just added as empty columns** (per
explicit ask - "I want all the data mapped correctly"):
- `IntegrationImportService`'s WooCommerce product mapper now populates
  description (HTML-stripped AND entity-decoded - caught `&amp;` staying
  literal after just stripping tags, fixed with `html_entity_decode`),
  short_description, weight, and dimensions, all natively present on a
  core WooCommerce product.
- **Product images are now actually downloaded**, not just schema-ready:
  fetched per URL (capped at 8/product), stored via the exact same
  `public_path('uploads/products')` convention `ProductController`'s own
  manual upload already uses (so imported and manually-uuploaded images
  render through the identical `asset()` path), one bad image logged and
  skipped rather than failing the whole product. Found and fixed a real
  bug here too: `Response::header('Content-Type', '')` - Laravel's HTTP
  client `header()` only takes one argument, so the fallback default was
  silently ignored, and a response with no Content-Type header would have
  fed `null` into `str_starts_with()` (deprecated/unsafe in PHP 8.1+).
  Fixed to `header('Content-Type') ?? ''`.
- Order mapper now captures `payment_method_title`, the first
  `coupon_lines[].code`, and full `billing`/`shipping` address blocks.
- **Customer/Storefront API updated to match**: `ProductResource` exposes
  short_description/brand/weight/dimensions/images; `SaleResource` exposes
  payment_method/coupon_code/billing_address/shipping_address/
  external_order_id; `CustomerProfileResource` exposes address_2.
  `AuthController::connect()`/`updateProfile()` now accept address_2/state/
  country (previously in the resource output but not actually settable).
  `OrderController::store()` now accepts an optional `payment_method` and a
  `shipping_address` snapshot at checkout time, matching the same shape
  used for imported orders. API Documentation page updated to match (stale
  docs would just recreate the exact mismatch risk this whole audit was
  about).
- Live-tested the full chain end to end via `Http::fake()`: product with 2
  real (fake-byte) images actually downloaded to disk and readable,
  description entity-decoding, customer address_2, order payment_method/
  coupon/both addresses - then via *real* HTTP against the running app:
  `/connect` with address_2, `/products` showing every new field, and a
  full `/orders` POST with `payment_method` + `shipping_address` returning
  correctly. All fixtures (test images on disk included) deleted after.

## 2026-08-14

**WooCommerce integration - one-time product/customer/order import.** New
Settings > Integrations hub (`admin.integrations.*`, role:admin, own
top-level route prefix rather than nested under Settings since it has a
multi-step flow, not a simple settings form). WooCommerce is the only
working connector in v1 (Shopify/Magento/WhatsApp/SMS show as "Coming soon"
cards, same grid, so the hub's shape doesn't change when they're built).
Scope was deliberately narrowed from the original idea (raw CMS import for
Woo/Shopify/Magento + WhatsApp/SMS) to a one-time import, WooCommerce first,
after weighing it with the user - live two-way sync is a fundamentally
different, much larger build (webhook/polling infra, conflict resolution
when both sides can change stock) and not what was needed first.

- **New tables**: `integrations` (one row per platform, credentials
  encrypted at rest via `Crypt::encryptString` in `Integration`'s
  accessor/mutator, confirmed via raw DB read that the column holds
  ciphertext, not plaintext), `import_batches` + `import_batch_items` (a
  staging area - fetched WooCommerce rows land here first, mapped to
  WSRetail's field shape and matched against existing records, and nothing
  touches real `Product`/`Customer`/`Sale` rows until an admin reviews and
  confirms). `sales.external_order_id` (nullable, unique per `source`) so a
  re-import recognizes an already-imported order instead of duplicating it.
- **`WooCommerceClient`**: thin wrapper on the WooCommerce REST API v3,
  Basic Auth (consumer key/secret), rejects a non-`https://` site URL up
  front since WooCommerce silently rejects Basic Auth over plain HTTP.
- **`IntegrationImportService`**: fetch+stage+match for all three types,
  plus commit. Products match on SKU→`code` (falls back to `WC-{id}` when a
  WooCommerce product has no SKU, since `products.code` is unique/required
  and can't be blank); customers match on email-then-phone; orders dedup by
  `(source, external_order_id)` and are never "updated" on re-import (once
  a sale exists, further changes go through the admin UI, not a re-run
  import silently touching a possibly already-confirmed/posted sale).
  Deliberate v1 scope cuts, called out in the code: variable WooCommerce
  products import as simple products using their own price/stock (not
  exploded into WSRetail's Size/Color variant system); product images
  aren't downloaded, only SKU/name/price/stock/category.
- **Review screen**: every staged row, checkbox to include/exclude,
  new-vs-update-vs-already-imported badges. Caught and fixed a real bug
  before it shipped - the first version paginated this screen, but the
  commit form only submits checkboxes for whatever page is currently in the
  DOM, so any item on a page the admin never visited would've been silently
  excluded on commit. Removed pagination there entirely; a large catalog is
  now a long scroll instead of a data-loss bug.
- **"E-commerce Store Orders"** under Sales (`admin.sales.ecommerce`) -
  same `Sale`/`SaleItem` records as everything else (not a parallel order
  system), just filtered to `source NOT IN ('web','pos','customer_app')`,
  with an added Store Order # and platform-badge column. Route registered
  before the `/{sale}` wildcard in `routes/web.php` (same reason `/create`
  and `/pos` already are) so `/sales/ecommerce` doesn't get swallowed as a
  `{sale}` id.
- **Bug found and fixed via live testing**: `Sale::$fillable` was missing
  `external_order_id` - the exact same mass-assignment class of bug as the
  `StockMovement::$fillable` miss earlier this session. First end-to-end
  test run showed the sale, customer, and items all committing correctly
  but `external_order_id` silently landing as `NULL`, which would have
  broken re-import dedup for every customer using this feature. Added it to
  `$fillable`, re-ran the full pipeline to confirm the fix.
- **Live-tested end to end** against `Http::fake()`-simulated WooCommerce
  responses (no real WooCommerce store available in this environment): full
  connect → stage → commit for all three types; re-running the import
  afterward correctly matched existing products for update and skipped the
  already-imported order rather than duplicating anything; verified the
  connect/disconnect controller actions and credential encryption directly;
  verified every new page (hub, review, connected-state, E-commerce Orders
  empty state) over real HTTP as a logged-in admin. All throwaway
  fixtures (test user, integration row, products/customers/sales, import
  batches) deleted afterward.
- Also rebuilt `public/build/` twice more (`npm run build`) for new
  Tailwind classes this UI introduced (`opacity-60`, `capitalize`, plus the
  purple/gray badge colors) not yet in the compiled bundle - same recurring
  gotcha noted in the two previous entries.

## 2026-08-13 (7)

**Urdu "welcome to the demo" intro popup**, Demo Mode only (same
`Setting::get('demo_mode')` flag as the login-page credentials hint and the
"Buy This Software" button - all three ship dark/off by default on every
install, including fresh clones of the public repo). A small round "?"
button now sits right next to the logo/site name in the sidebar
(`layouts/admin.blade.php`) - only rendered when Demo Mode is on - that
opens an RTL Urdu popup pitching what the software is and which businesses
it fits (restaurant/cafe, clothing, mobile shop, general retail, wholesale),
plus a quick features list (POS, inventory/variants, accounting, storefront,
staff/customer records).

It also auto-opens exactly once, right after logging in: `AuthenticatedSessionController::store()`
flashes a `show_demo_intro` session value only when Demo Mode is on, and the
Alpine store seeds `demoIntroOpen` from that flash value on the very next
page render (`Alpine.store('wserpUi', { ..., demoIntroOpen: @json((bool) session('show_demo_intro')) })`).
Flash data is one-request-only by design, so it auto-opens on the page the
user lands on right after login and stays closed on every subsequent page
load - closing it or clicking the "?" button afterward both just toggle the
same store flag.

Live-tested end to end: enabled Demo Mode, logged in as a throwaway admin,
confirmed `demoIntroOpen: true` (and the Urdu text) on the very first
dashboard load, confirmed `demoIntroOpen: false` on a second load of the
same page, then confirmed with Demo Mode off that neither the "?" button nor
any Urdu content renders at all (only the harmless, always-present
`demoIntroOpen` store key used by the existing keyboard-shortcut guard).
Also had to rebuild `public/build/` again (`npm run build`) since this
introduced more brand-new Tailwind classes (`bg-gradient-to-l`,
`from-amber-500`, `to-orange-500`) not yet in the compiled bundle - see the
note in the previous entry about this being a recurring step, not a one-off.

## 2026-08-13 (6)

**Pre-public-push security pass**, prompted by "make sure this source code
is unbreakable... before pushing." One real enforcement gap found and fixed,
plus a general audit of the fixable stuff. See the chat transcript for the
honest caveat on what "unbreakable" can't mean for self-hosted, source-
visible software - this entry only covers what's genuinely fixable.

**Fixed: Customer/storefront API wasn't behind the license check at all.**
`EnsureLicensed` only ever wrapped the `admin.*` route group. A storefront
talks directly to `/api/v1/customer/*` and never touches `/admin` - so an
unlicensed, expired, or revoked install would keep silently taking orders
through the storefront forever, with zero enforcement, even though the admin
panel itself was correctly locked. Now `routes/api.php` wraps the whole
customer API group in the same `license` middleware, and `EnsureLicensed`
returns a JSON 403 (instead of an HTML redirect) for API/JSON requests when
locked. Live-tested: deactivated the license, confirmed both `/admin/*`
(redirects to the license page) and `/api/v1/customer/connect` (403 JSON)
are blocked; reactivated and confirmed both work again.

**Fixed: `.env.example` shipped `APP_DEBUG=true`.** The web install wizard
(`InstallController`) already force-writes `APP_ENV=production` /
`APP_DEBUG=false` into the real `.env` regardless, so the documented
"go through /install" path was already safe - but the example template
itself defaulting to debug-on (full stack traces/paths on error) is an
unnecessary risk for anyone who skips the wizard. Changed the default to
`false`.

**Audited, no changes needed:** no model uses `$guarded = []` (the mass-
assignment-everything footgun); every `DB::raw`/`selectRaw`/`orderByRaw`
usage is a hardcoded aggregate expression (SUM/CAST/COALESCE/CASE), never
user input concatenated into SQL; no `exec`/`shell_exec`/`system`/`eval`
calls anywhere in `app/`; no unescaped `{!! !!}` Blade output fed by
request/user input; `composer.json`'s production `require` has no debug-
tooling packages (debugbar/telescope/etc.) that could leak internals if
accidentally left enabled. The license-purchase payment slip upload
(previous entries) is attached to the outgoing email directly from PHP's
upload temp path and is never written into any web-accessible directory, so
there's no file-upload-to-RCE path there either.

**Noted, not changed (a genuine judgment call for the deployer, not a bug):**
Laravel's default CORS config allows `*` on `api/*` (no `config/cors.php` is
published/customized in this repo). Left as-is because the customer API is
Bearer-token authenticated, not cookie-based, so this isn't a CSRF-style
hole - and locking `allowed_origins` down to a specific storefront domain is
a real per-deployment value only the person deploying it knows, not
something to hardcode into the public template.

## 2026-08-13 (5)

**Fixed: "Buy This Software" button rendered invisible (white on white).**
Root cause: `public/build/assets/*.css` was a pre-built Tailwind bundle from
earlier in the day, built before the `bg-amber-500`/`hover:bg-amber-600`
classes were added to the topbar button. Tailwind only emits CSS for classes
it sees in source files *at build time* - since nothing else in the codebase
used `bg-amber-500`, that specific utility didn't exist in the already-built
CSS, so the button had no background (fell through to the header's white)
while `text-white` (used elsewhere already, so already compiled) did apply -
literally white text on white. Ran `npm run build` to recompile; verified
`.bg-amber-500{...}` is now present in the new hashed CSS file and that the
served page references it.

**Reminder for future work in this repo:** any time a brand-new Tailwind
color/utility is introduced that nothing else uses yet, `public/build/`
needs a rebuild (`npm run build`, or run `npm run dev` while iterating)
before it'll actually render - the class existing in a `.blade.php` file is
not enough by itself.

## 2026-08-13 (4)

**"Buy This Software" topbar button + Demo Mode setting.** The bank-transfer
purchase form from the previous entry only lived on the locked/unactivated
license page, so it was unreachable on an already-licensed install like the
owner's own public demo. Extracted the bank details + form markup into a
shared partial (`resources/views/admin/license/_purchase-form.blade.php`,
included from both the license page and the new modal) and added an amber
"Buy This Software" button to the main admin topbar (`layouts/admin.blade.php`,
admin-role only, matching the existing "Reconcile All Accounts" gating) that
opens it in a modal via the same `$store.wserpUi` Alpine pattern already used
for quick search/keyboard shortcuts. Live-tested on a normal page (dashboard,
license already active) - button, modal, real bank details, and a full form
submission all confirmed working outside the license page.

Also added a **Demo Mode** setting (Settings > General, DB-backed via the
existing `Setting` key/value store - same pattern as `dark_mode_enabled`, not
an env var) so the owner can turn on a credentials hint on the login page
from the UI, no server access needed. Off by default on every install,
including fresh clones of the public repo - only meant for the owner's own
public demo (e.g. demo.izmadts.com). When on, an admin-authored free-text
note (`demo_credentials_note`, e.g. "Email: admin@demo.com   Password:
admin") renders on the login page. Deliberately did **not** relax the login
form's email validation to accept a bare "admin" as a username - it stays a
real (if simple) email/password pair, just displayed openly for the demo.
Wired into the existing `AppServiceProvider` view composer that already
shares `siteName`/`darkModeEnabled`/etc. with `auth.login`. Live-tested:
enabled the setting + note directly, confirmed the banner renders on
`/login`, confirmed the toggle+note round-trip through Settings > General,
then reset both back off.

## 2026-08-13 (3)

**"Buy a license" bank-transfer form on the locked/unactivated license page.**
Previously that page only had WhatsApp/email contact chips. Added a bank
transfer details panel (bank name/account title/account number, from
`config('services.license_purchase')` — hardcoded per-install like the
WhatsApp/email chips next to it, since every self-hosted copy of this public
repo should point at the same owner, not be per-install configurable) plus a
form (name, business name, phone, email, domain, amount paid, notes, payment
slip upload) that emails the submission — with the slip attached straight
from the upload, never written to permanent storage — to
`LICENSE_PURCHASE_NOTIFY_EMAIL` (defaults to izmadts@gmail.com). New
`App\Mail\LicensePurchaseRequested` + `resources/views/mail/license-purchase-requested.blade.php`,
new `LicenseController::purchaseRequest()` (admin-only, throttled 5/min),
route stays inside the `admin.license.*` name group so `EnsureLicensed`
lets it through even on a fully locked install. No local DB table — this is
intentionally a stateless mail-out, not a purchase-tracking system (that's
the license server's job).

Live-tested end to end: logged in as a throwaway admin, temporarily
deactivated the local license to reach the unactivated-state UI, submitted
the form with a real multipart file upload, confirmed the flash success
message, and inspected the logged email (`MAIL_MAILER=log`) — correct `To`,
`Reply-To` set to the submitter's email, correct subject, and the PDF
attachment present with the right content type. Restored the original
license activation and deleted the throwaway admin afterward.

Also fixed a real, unrelated bug hit while restoring the test license state:
`license.last_error` was a `VARCHAR(255)`, and a normal connection-refused
cURL error message (e.g. license server briefly unreachable) is long enough
to exceed that, which threw a SQL truncation error instead of just recording
the real one — turning a harmless "couldn't reach the license server" into a
500. Widened it to `TEXT` via a new migration
(`2026_08_13_145134_widen_last_error_column_on_license_table.php`).

## 2026-08-13 (2)

**Customer API: retail channel, not wholesale/Mandi.** The Customer API
(`/api/v1/customer/*`) — the one the new Next.js storefront connects to —
was inherited from WSERP's "Mandi" seller-app feature and was hardcoded as
a **wholesale-only** channel: every listing/order priced off
`wholesale_price`/`is_wholesale`, and every new customer defaulted into the
Wholesale customer group. That's backwards for a direct-to-consumer
storefront (clothing/mobile/general retail), so it's now a **retail-only**
channel instead:

- `Api\Customer\ProductController`/`CategoryController` now filter on
  `is_retail` and price off `sale_price` (was `is_wholesale`/`wholesale_price`).
- `Api\Customer\OrderController::resolveChannel()` now resolves to
  `sale_price`/`is_retail`/`"retail"` — orders are rejected with a clear
  422 if a product isn't marked retail-eligible.
- `AuthController::connect()` now defaults new storefront customers to the
  **Retail** customer group instead of Wholesale.
- `CustomerProfileResource::order_channel` now reports `"retail"`.
- Live-tested end to end: connect → browse (retail item shown, wholesale-only
  item correctly excluded) → categories → place order (priced at
  `sale_price`) → attempted order against a wholesale-only product correctly
  rejected with 422. Fixtures cleaned up after.

Also removed the leftover "Mandi"/"seller-app"/izmafood/Flutter framing this
carried over from WSERP throughout comments, `.env.example`,
`config/services.php`, the admin **API Documentation** page, and
`api-tester.blade.php` — all now describe this as the general
customer/storefront API. The **API Documentation** page's Integration Guide
section (previously a Flutter/Dart migration guide for a different,
unrelated mobile app) was replaced with a Next.js storefront integration
guide matching how `wsretail-storefront` actually calls this API (server-side
`/connect` proxy holding the integration key, browser-direct calls after
that).

`DEPLOYMENT.md` and `deploy.sh` also had this same legacy content — a table
of two Flutter apps (`izmafood-vendors`, `izmafood_saleagent`) with
hardcoded API URLs to update by hand, and "WSERP" branding. Rewrote both:
`DEPLOYMENT.md` now points to the cPanel guide in README.md for non-SSH
hosts and has a short "connect a storefront" section instead of the Flutter
table; `deploy.sh`'s post-install message reflects the same.

## 2026-08-13

### Next.js storefront starter + Customer API variant-visibility fix + public-launch prep

Sibling project `wsretail-storefront` (new Laravel-adjacent repo, not part
of this codebase) - a Next.js 15 App Router app that shops against
WSRetail's existing Customer API (`/api/v1/customer/*`), for anyone who
wants their own online store connected to a WSRetail backend rather than
using the admin/POS screens for every sale.

- **The integration key never reaches the browser.** WSRetail's `/connect`
  endpoint requires `CUSTOMER_API_INTEGRATION_KEY` - a secret that
  authenticates the *storefront itself*, not one shopper. The storefront's
  own `app/api/connect/route.ts` is the only place that key is read (a
  Next.js server route, `WSRETAIL_INTEGRATION_KEY` - no `NEXT_PUBLIC_`
  prefix, so Next.js never bundles it into client JS); the browser calls
  that route, which proxies to WSRetail server-side. Every other call
  (products, orders, profile) uses the per-customer Bearer token
  `/connect` returns instead, which is safe client-side the same as any
  SPA session token.
- **Pages**: connect (name+phone, no password - matches `/connect`'s
  actual design), product catalog with search/category filter, product
  detail, a localStorage-backed cart, checkout (cash/credit, submits a
  draft order same as a phoned-in one), order history, order detail with
  cancel. Ships with `output: 'standalone'` in `next.config.ts` so it can
  run on a cPanel host's "Setup Node.js App" feature, not only Vercel.
- **Two real gaps documented rather than papered over** in the
  storefront's own README: WSRetail's Customer API has no
  unauthenticated/public product listing (every visitor must connect
  first - there's no anonymous browse-then-signup path today), and
  `OrderController@store` doesn't yet accept `product_variant_id` (a
  variant product is visible and priced correctly in the catalog now, but
  checking out a *specific* variant through this API isn't wired up yet).
- **Bug caught while building the storefront**: the Customer API's
  `ProductController`/`CategoryController` had the exact same
  `current_stock > 0` filter bug fixed elsewhere this session for
  Sale/Purchase/Barcode - a variant product's own `current_stock` is
  always 0 (real stock lives on its variants), so every variant product
  was silently invisible in the storefront catalog. Fixed with the same
  `Product::scopeInStock()` already built for that purpose;
  `ProductResource`'s price/stock fields and the wholesale-price override
  in `ProductController::index()` now read from the variants when
  `has_variants` is true instead of the always-zero/always-blank base
  columns.
- **License locked-out screen** (`admin.license.index`) gained a "Don't
  have a license key?" contact block linking WhatsApp and email
  (izmadts@gmail.com) - held back from the UI/README until the address
  was actually confirmed rather than publishing a guess.
- **Public-repo prep for `github.com/izmadts/WSRetail`**: full secrets
  audit (no committed `.env`, no hardcoded API/AWS/Stripe-style keys, DB
  config uses only `env()` defaults, seeder data is clearly placeholder) -
  found and fixed one real issue: `.gitignore` had `.env.example` itself
  ignored, which would have shipped the public repo with no install
  template at all. `README.md` (still the stock Laravel skeleton file)
  rewritten with real WSRetail docs, features, and a cPanel-first install
  guide (the wizard at `/install` already exists; the README explains
  getting code onto shared hosting, setting the document root to
  `public/`, and the workaround for hosts that don't allow that). Per
  explicit instruction, nothing was pushed or committed - audited and
  prepared only.

Verified live end-to-end: `npm run build` (clean TypeScript compile),
`app/api/connect/route.ts` proxying a real connect call against a running
WSRetail dev instance with the integration key confirmed absent from any
client-visible response, product catalog correctly showing a variant
product's aggregated stock/from-price where it would previously have been
silently missing, a full order placed and independently verified via the
Customer API's own order-list/detail endpoints, and every storefront page
(`/`, `/login`, `/products`, `/cart`, `/checkout`, `/orders`,
`/orders/{id}`, `/account`) rendering without a server error - then all
test fixtures (2 products, 1 customer, 1 draft order) removed and the
temporary integration key reverted.

### Product variants (Size/Color/Storage/...) - clothing, mobile shops, restaurants, general retail

A product can now be sold in more than one option - each with its own
price, stock, and barcode - covering the businesses this is meant to fit
beyond plain single-SKU retail: clothing (Size x Color), mobile shops
(Color x Storage), restaurants (portion sizes), or any product line with
real variation. Designed around "few clicks" - reusable attributes,
inline quick-add, and one button that generates the whole combination
matrix instead of creating each variant by hand.

- **Schema**: `product_attributes`/`product_attribute_values` (store-wide,
  reusable - "Size" defined once, reused across every product that has
  sizes), `product_variants` (own sku/barcode/label/purchase_price/
  sale_price/wholesale_price/current_stock/min-max stock/is_active - a
  full peer of Product's own pricing/stock fields, just scoped to one
  combination), `product_variant_attribute_value` pivot, `products.has_variants`
  flag, and a nullable `product_variant_id` added to `sale_items`/
  `purchase_items`/`stock_movements`.
- **Create/edit UI**: a toggle reveals an Alpine-driven builder - pick
  existing attributes or create a new one inline (e.g. type "Flavor",
  hit Create, no page navigation), pick which values apply to *this*
  product, click Generate and every combination appears as an editable
  row (SKU/barcode/purchase/sale/wholesale price/stock), with a bulk-set
  toolbar to fill price/stock across every row at once instead of
  editing 20 fields by hand. Editing an existing variant product can add
  more variants later (e.g. a new color) without touching the ones
  already selling - existing rows edit in place, current_stock on them
  stays read-only same as the base product's own edit-time rule.
  New Settings > Attributes page for managing them outside the product
  form too.
- **Stock/pricing correctness**: a variant product's own price/stock
  columns are zeroed and unused - `Product::isLowStock()`/`isOverStock()`/
  `scopeLowStock`/`scopeOverStock`/`scopeInStock`/`totalStock()` all became
  variant-aware (check every variant instead of the always-zero parent
  row) so low-stock alerts, the products list, and "can this be sold"
  filters keep working correctly for both kinds of product side by side.
- **Wired through the whole sale/purchase/stock path**, not just
  create/display: POS gets a variant picker popup when a variant
  product's tile is clicked (grid tiles show a price range and combined
  stock across variants); the regular Sale and Purchase create/edit forms
  grow a second dropdown once a variant product is selected. `SaleService`/
  `PurchaseService`'s stock update/reverse/movement-logging all resolve
  against the chosen `ProductVariant` instead of the parent `Product` when
  one was selected - confirmed live that overselling a specific
  low-stock variant is still blocked while a sibling variant with plenty
  of stock sells fine. Barcode label printing lists variants alongside
  plain products (one row per SKU) rather than only the parent product.
- **Opening stock/ledger posted per variant** on creation (and for any
  variant added later via edit), same double-entry treatment
  `Product::postOpeningStock()` already gave the base product - under a
  deliberately distinct `opening_variant` reference_type rather than
  reusing `opening`, since `AccountReconciliationService` hardcodes
  `opening`'s reference_id as a Product primary key; reusing it with a
  variant's id could collide with an unrelated product sharing that id
  and corrupt that tool's scan.
- Two real bugs caught by live testing before they'd have hit a real
  store: `StockMovement` was missing `product_variant_id` from
  `$fillable`, so every variant-aware stock movement silently saved with
  a null variant reference despite the code passing it in (mass
  assignment drops unlisted keys with no error) - stock quantities were
  still correct, but which variant moved wasn't recorded. And editing a
  variant product to add more variants re-posted opening-stock ledger
  entries for every existing variant too, not just the new one, silently
  doubling their inventory/equity journal entries on each subsequent
  edit - fixed by having variant-creation return exactly the rows it
  created instead of the caller re-scanning the product's full variant
  list afterward.

Verified live end-to-end (not template rendering): created a 6-variant
clothing product (3 sizes x 2 colors) with inline attribute/value
creation, confirmed correct opening stock + 12 balanced journal lines;
sold specific variants through both POS (picker popup) and the regular
Sale form with correct per-variant stock deduction and stock movement
records; confirmed the insufficient-stock guard blocks overselling a
depleted variant; received stock against a specific variant through
Purchase; printed barcode labels for a mix of variants and a plain
product; edited the product to bump one variant's price and add a
brand-new variant, confirming existing variants' ledger entries were
untouched (catching and fixing the double-posting bug above) - then all
test fixtures (product, variants, attributes, sales, purchase, supplier,
category, admin account) removed afterward.

### Commercial licensing: standalone license server + remote activation on every install

The other half of "remove Sale Agent, make this sellable": a real
license-key system so a sold copy of WSRetail requires activation
against a server the seller controls, rather than working the moment
someone has the source. Honest framing kept throughout: this is a
licensing gate, not source-code tamper-proofing - nothing server-side
in PHP makes a codebase truly unhackable/uneditable by whoever has a
copy of it. What this *does* buy is a real commercial control point: no
key, no working install; a suspended/revoked/expired key stops a live
install within one check-in; a key is locked to one domain and a seat
count, both enforced server-side.

- **New standalone app, `wsretail-license-server`** (separate Laravel 11
  project/repo/deploy, its own MySQL database) - the thing "a remote
  server you control" from the earlier decision actually refers to.
  `licenses` (key, customer, status active/suspended/revoked,
  max_activations, expires_at nullable = perpetual) and
  `license_activations` (one row per domain a key has been activated on,
  with its own secret `activation_token`, fingerprint, last_seen_at).
  Keys are Crockford-base32 (`WSR-XXXXX-XXXXX-XXXXX-XXXXX`, no 0/O/1/I/L)
  so a misread character can't resolve to a different valid key.
  Session-auth admin panel (`/admin/licenses`) to generate keys per
  customer, see every activation (domain/IP/app version/last check-in),
  suspend/reactivate/revoke a license, or force-deactivate a single
  activation to free a seat. Public throttled API
  (`/api/v1/activate|validate|deactivate`) - the key alone only ever
  creates/refreshes an activation for a domain; validating requires the
  activation_token that only that specific install has, so a leaked key
  alone can't impersonate an already-activated install (though it can
  still consume a fresh seat up to max_activations, same as any license
  key).
- **WSRetail side**: new `License` singleton model/table + `LicenseService`
  (activate/validate/deactivate, all calling the server above) +
  `EnsureLicensed` middleware on the entire `/admin` route group. An
  explicit no from the server (suspended/revoked/expired) locks
  immediately; a network failure instead starts a grace window
  (`LICENSE_GRACE_DAYS`, default 7 days) so a temporary outage on either
  side doesn't brick a paying customer - only sustained unreachability
  past the grace window locks. Settings > License is reachable regardless
  of lock state (or a locked-out admin could never fix it) - shows
  status/expiry/last-check-in, lets an admin activate/re-check/deactivate;
  every other role sees the same status read-only with a "contact your
  administrator" message.
- **Checked at two points, not just once**: the install wizard
  (`/install/step2`) now requires and validates a license key as part of
  finishing installation - migrate runs, then activation is attempted,
  and only on success do seeding/admin-account-creation/`.env` writing
  proceed, so a bad key aborts cleanly and step2 can just be resubmitted.
  After that, ongoing enforcement is two-layered: `php artisan
  app:check-license` is scheduled daily (needs the server's cron running
  `schedule:run`, standard Laravel deployment requirement) as the "real"
  check-in, and the `EnsureLicensed` middleware also fires an
  opportunistic check itself (rate-limited to once per
  `LICENSE_RECHECK_INTERVAL_HOURS`, default 6h) so an install still
  self-heals its status even on a host where cron was never configured.
  An existing install upgrading into this feature (never ran the wizard's
  license step) locks immediately on next request until an admin visits
  Settings > License and activates - confirmed live rather than assumed.

Verified live end-to-end against real dev databases on both sides (not
template rendering): activation through the WSRetail-side form against a
real key from the license server, `EnsureLicensed` correctly locking the
entire admin group with zero key activated, suspend on the license server
propagating to a full admin-panel lock on next re-check, reactivate
un-locking it again, deactivating on the WSRetail side freeing the seat
back on the server, the grace-period math (backdated last-successful-check
past the 7-day window locks; a stale check-attempt timestamp lets the
middleware's opportunistic check self-heal it), and the scheduled
`app:check-license` command running standalone - then all test licenses/
activations/admin accounts cleaned up, keeping one clearly-labeled
"WSRetail - Local Dev Instance" license active so this dev copy's admin
panel keeps working day to day.

### Sale Agent module removed entirely - lean POS/retail product

Full removal, not just hiding: the `sales_agent` role, the whole `/agent/*`
self-service portal, the Sale Agent mobile API (`/api/v1/agent/*`),
agent registration/approval, and the entire commission-calculation
system. This was the last piece of WSERP's field-sales-agent model left
in a codebase that's now scoped as a POS product - there's no seller
role left that isn't staff using the POS/admin directly.

- **Deleted outright**: `Agent`/`AgentController`/`AgentManagementController`
  and the Api\Agent controllers, the entire `resources/views/agent/*` and
  `admin/agents/*` view trees, `AgentRegistrationController` +
  `agent-register-*` auth views, `EnsureAgentActive` middleware,
  `CommissionService` (~770 lines), `AgentCommissionLog`/
  `AgentCommissionPayment`/`AgentMonthlyTarget` models and their three
  tables, `CloseCommissionMonth` console command, agent report views
  (`admin.reports.agents`/`agent-detail`), and `routes/api/agent.php`.
- **Schema**: two new migrations drop `sales.agent_id`/`commission_*`/
  `recovery_percentage`/`is_commission_held`/`commission_hold_reason`,
  `customers.created_by_agent_id`/`is_agent_customer`, and
  `users.channel`/`commission_rate_*`/`commission_slabs`/`sales_target`/
  `payout_account_*` (kept `basic_salary`/`fuel_allowance` - shared with
  the generic Payroll module - and `approved_at`/`approved_by`, a generic
  staff-approval concept); `users.role` enum narrowed to
  `admin,manager,accountant,pos_manager`. A stray `sales.commission_paid_at`
  column missed by the first pass was caught by re-diffing the schema
  after migrating and dropped in a small follow-up migration.
- **Customer Credit Limit/hold gate preserved**: `CommissionService` had
  quietly mixed two unrelated concerns - agent commission math and the
  general customer credit-hold check used by every sale regardless of
  agent. Extracted the latter into a new `CustomerCreditService` *before*
  deleting `CommissionService`, keeping the legacy `commission.*` setting-key
  prefix on purpose so an admin's already-configured credit-hold settings
  aren't silently reset. Settings > "Commission & Bonus" is now
  Settings > "Credit".
- **Customer mobile app**: dropped the agent-picker/attribution path
  entirely rather than replacing it - `/connect` no longer takes
  `agent_id`/`direct`, `Customer.createdByAgent()`/`is_agent_customer` are
  gone, and order confirmation messaging always just says "the admin" now.
- **Runtime dependency caught by a full-codebase grep sweep** (not just the
  initial file inventory): `AccountReconciliationService` - the "Reconcile
  All Accounts" ledger-integrity scanner - had a hard, functional
  dependency on `AgentCommissionLog`/`AgentCommissionPayment` and an
  injected `CommissionService`. Deleting `CommissionService` without
  fixing this would have thrown a fatal "Class not found" the next time
  anyone opened that admin tool. Cleaned out its agent-specific scan/fix
  branches instead of leaving a dangling dependency.
- **Historical data left alone on purpose**: already-seeded Chart-of-Accounts
  rows (Agent Commission Payable/Expense) and `ActivityLog`'s "agent"
  category label/icon are untouched - only removed from the *seeder* for
  future installs - so existing journal entries and old audit-log rows
  still render correctly. No historical migration files were edited, only
  new drop-migrations added.
- API docs/tester rewritten to drop the Agent API tab entirely (the
  Customer API was always the only thing worth documenting there) and the
  SOP guide's Agent Management/Commission sections replaced with a POS
  Setup section.

Verified live end-to-end against the real dev database: admin/manager/
accountant/pos_manager logins all still work; a regular sale and a POS
checkout (with the location-lock spoofing test repeated) both post
correctly with zero agent/commission fields; customer create/edit works
without an agent field; the Customer mobile API `/connect` → `/products`
→ `/orders` → `/orders/{id}/cancel` flow works with no `agent_id`
anywhere in the request or response; Settings > Credit loads and saves;
Reconcile All Accounts runs without a fatal error; API Docs/Tester and
the SOP guide render with zero agent/commission mentions left - then all
test fixtures (4 users, 2 locations, 1 product, 2 customers, 3 sales)
removed afterward, `.env` integration-key override reverted.

### Full-fledged POS: dedicated screen, POS Manager role, settings, receipts, barcodes

Ideas pulled from a reference commercial POS suite (SalePro) reviewed for
this: single-location-per-user locking via a hidden field vs. dropdown,
a dedicated chrome-free layout, and a centralized POS settings row.
Deliberately **not** ported: cash register open/close till, held/parked
sales, a customer-facing display screen, restaurant/table mode, SMS,
Stripe/PayPal - none of that was asked for; add on request.

- **`pos_manager` role** (new value in `users.role`'s enum) - logs straight
  into `/admin/sales/pos` on login (`AuthenticatedSessionController`) and
  can reach *only* that screen: `RolePermission::ROLES`/the seeder give
  them `sales` view+create and nothing else, and the outer
  `role:admin,manager,accountant,pos_manager` route gate now lets them
  into the `/admin` group at all only for the fine-grained
  `permission:module,ability` middleware on every other route to block
  them - Settings stays behind its own `role:admin` gate regardless, so a
  POS Manager can never reach it. `SaleController::index()`/`create()`
  additionally redirect a POS Manager straight back to `/admin/sales/pos`
  so they can't even browse the sales list.
- **Per-user location lock** - `users.location_id` (nullable). When set
  (typically for a POS Manager), the POS screen shows that location as a
  fixed, read-only field instead of a dropdown, and `SaleController::store()`
  force-overwrites `location_id` server-side to the user's own location
  regardless of what the form posts - verified live by spoofing a
  different `location_id` in the raw POST and confirming the sale still
  landed against the user's real locked location. Settings > Users gained
  a "POS Location" field and a `pos_manager` role option.
- **Dedicated POS screen/URL** - new `layouts.pos` (no sidebar, no admin
  nav - compact top bar with location/user/clock/fullscreen/dark-mode
  only) replaces `layouts.admin` on the POS view. The topbar's POS button
  now opens it with `target="_blank"` - its own tab, own URL
  (`admin.sales.pos`), not nested inside the regular dashboard chrome.
- **POS Settings** (Settings > POS Settings, admin-only, new `pos_settings`
  singleton table/`PosSetting` model): default location/customer, products
  shown per page (client-side "Load more" pagination on the POS grid),
  receipt paper size (Thermal 58mm/80mm/A4) + auto-print-after-checkout
  toggle, which payment methods appear at POS checkout, and barcode label
  format/size/columns/what-to-show. All consumed live by the POS screen
  and the new barcode print page below.
- **Receipt printing** - didn't exist at all before (`show.blade.php` only
  displayed the invoice number as text). New standalone print view
  (`admin.sales.receipt`, its own bare HTML document sized to the
  configured paper width) is what POS checkout now redirects to instead
  of the sales list; also linked from the regular Sale detail page for
  reprints. Honors the auto-print setting via `window.print()` on load.
- **Barcode labels** - new Settings-configurable format
  (CODE128/EAN13/CODE39), label size, and columns-per-row. New "Print
  Barcodes" page off the Products list: pick quantities per product,
  generates a print sheet rendered client-side via `jsbarcode` (added to
  `resources/js/app.js`, no server-side image generation needed) - falls
  back to CODE128 per-label if a product's code isn't valid under a
  stricter format like EAN13, rather than failing the whole sheet.

Verified live end-to-end against the real dev database (not just template
rendering): a location-locked `pos_manager` test account logging in and
landing on POS, blocked with 403 from `/admin/dashboard` and
`/admin/settings/*`, a spoofed `location_id` on a raw POST still landing
against the locked location, receipt page rendering and correctly hiding
the admin-only "View Full Details" link for that role, POS Settings
saving and taking effect on the POS screen, and a barcode label sheet
actually generating - then all test fixtures (users, location, product,
customer, sale) removed afterward.

## 2026-08-12

### Per-location POS type (Retail / Wholesale / Both)

New **Locations** entity (Settings > Locations, admin-only) — this app had
no concept of a physical location/branch at all before now. Each location
has a **POS Use** setting: `Retail`, `Wholesale`, or `Both`.

- New `locations` table and a nullable `sales.location_id` — nullable and
  optional everywhere, so any install that doesn't set up locations at all
  keeps working exactly as before (the field doesn't even render on the
  sale form if no location has been created yet).
- The sale create/edit screens gained an optional Location field. Picking
  a Retail-only or Wholesale-only location overrides the customer group's
  price field (the existing `sale_price`/`wholesale_price` logic added
  2026-08-04) for that sale, forcing retail- or wholesale-flagged products
  and pricing regardless of the customer's own group; "Both" (or no
  location selected) leaves it exactly as it worked before — driven by the
  customer's group.
- Sale detail page shows the location (with its POS type) when one is set.
- A location with sales recorded against it can't be deleted (same pattern
  as Customer Groups).

### Dedicated POS checkout screen

New cashier-style screen at `admin/sales/pos` (route `admin.sales.pos`,
`SaleController::pos()`), reachable via a blue **POS** button always
visible in the topbar (next to Quick Search, gated on the same
`sales,create` permission as New Sale). Click-to-add product grid
(search + category chips) with a running cart on the right - customer,
location (POS Use), and Cash/Credit selectable up top; agent/discount/
tax/shipping tucked behind a "More options" toggle to keep the fast path
uncluttered.

Deliberately **not** a separate sale-creation code path: the page's form
posts to the exact same `admin.sales.store` endpoint the regular New Sale
form uses, so every existing rule (credit-limit/credit-hold gate,
commission calculation, stock + ledger posting, below-cost/stock-warning
confirmations) applies identically - this is just a faster front end onto
the same backend, not a parallel implementation to keep in sync. Every
sale from this screen is submitted `status=confirmed` (no Draft option
here - use the regular New Sale form for quotes) and, for Cash, forces
`amount_received` to the full total (same "cash must be paid in full"
rule the regular form already enforces).

## 2026-08-06

### Previously-inert settings made real

Found via live testing that several fields were stored in the database but
never actually enforced anywhere — fixed all of them, each behind a
default-off toggle so no existing install's behavior changes until an
admin deliberately opts in:

- **Timezone** (Settings > General): now actually applied to `now()`/date
  calculations and scheduled jobs — previously stored but ignored.
- **Customer Credit Limit**: new setting *"Block new credit sales that
  would exceed a customer's Credit Limit"* (Settings > Commission & Bonus).
- **Customer Credit Days**: now a real per-customer override of the global
  credit-hold grace period (flat default + optional per-customer
  override). Also discovered and fixed: the existing "Block new credit
  sales to customers overdue past the grace period" checkbox was already
  in the UI but wired to nothing — it now actually blocks.
- **Product Max Stock Level**: products now show an "Overstocked" badge
  (product list + detail page) once Current Stock exceeds it, mirroring
  the existing Low Stock badge.

All four checks live in one shared `CommissionService::creditGateMessage()`
called from every place a credit sale can be created (Admin, Agent web,
Agent API) — not duplicated per controller.

### Bugs found and fixed during live testing

Found by actually exercising the app end-to-end (real HTTP requests against
a running server, not just reading code), not from bug reports:

- **`Supplier::code` collisions**: the auto-generated code had no random
  component (`date('dmy-Hi-s')`), so two suppliers created within the same
  second failed on the unique constraint. Fixed to match the
  date+random-suffix pattern already used for Product/Customer/Expense
  codes.
- **Overdue-days math broken under Carbon 3**: `checkCreditHoldStatus()`
  (dead code until the Credit Limit work above activated it) showed things
  like "-60.43 days overdue" instead of "60" — Carbon 3 changed
  `diffInDays()` to default to signed, sub-day-precision output.
- **Sale `due_amount` could go negative**: a return against an
  already-fully-paid sale drove `due_amount` to e.g. `-960` instead of
  flooring at `0`.

### Reporting — standard "bahi khata" ledger reports

Every report now shares one letterhead (company logo + name, report title,
generated-on timestamp, repeating page-number footer) via a new
`<x-khata-pdf-layout>` component, and a new `LedgerHelper` computes
running-balance ledger rows in one place instead of once per report.

New reports (Date | Particulars | Reference | Debit | Credit | running
Balance, Dr/Cr style — screen view + PDF export, date-range filterable):

- **Customer Ledger** — every sale/payment/return for one customer.
- **Supplier Ledger** — every purchase/payment/return for one supplier
  (credit-term only, matching how the supplier's own balance is computed
  elsewhere).
- **Account Ledger** — works for any Chart of Accounts account; doubles as
  the Cash Book/Bank Book (open the Cash or Bank account, no separate
  feature needed).
- **Day Book** — every voucher posted in a date range, flat journal style,
  with a debit=credit balance check.
- **Payable** — the supplier-side mirror of the existing Receivable report
  (didn't exist before).

Existing reports (Receivable, Trial Balance, Profit & Loss) upgraded to
the same letterhead + PDF button.

Navigation: "Ledger" links added to Customer/Supplier detail pages, the
Customers/Suppliers report list pages, and the Chart of Accounts list.

### Export/report cleanup

Found while auditing the above: Trial Balance, Profit & Loss, and Tax
Report all had CSV/Excel/PDF export buttons that **silently downloaded
empty files** — their `type` was never registered in `ExportController`.
Rather than wire three statement-shaped reports into a flat-row CSV/Excel
system built for list-shaped data, replaced all three with the new
khata-style PDF (which the underlying data actually suits). Receivable's
CSV/Excel were confirmed genuinely working and kept; its old generic PDF
button was removed in favor of the one khata-style PDF button, so no page
shows two different "PDF" buttons anymore.

### UI: filter-row button alignment

Every "Filter"/"Reset" (and PDF/Print) button sitting next to a labeled
date/text field was misaligned — the `<button>` (browser-default
`inline-block`) and the `<a>` styled as a button (browser-default `inline`)
don't box the same way even with identical Tailwind classes, so `flex
items-end` couldn't align them reliably. Fixed everywhere this pattern
appears (13 files: every report filter, Activity Log, Bank Reconciliation)
by making every button-styled `<a>` explicitly `inline-flex`, and giving
the button/link wrapper a `pt-6` spacer matching the sibling label's
height instead of relying on content-height coincidence.

## Environment notes for whoever deploys this next

- 9 migrations that had been written earlier were never actually run
  against the dev database until this session (`php artisan migrate`) —
  worth double-checking migration status on any other environment this
  code has touched.
- This session's live testing used its own throwaway fixtures (a test
  admin `live.test.owner@wserp.local`, a test agent, 2 suppliers, 6 spice
  products, plus test sales/purchases/returns) — all confined to the local
  dev database, not part of the deployment package, and safe to wipe.
- Git history does not reflect this work — none of it has been committed.
