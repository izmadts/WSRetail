# WSRetail

A Laravel-based Point-of-Sale and retail management system — sales, purchases,
inventory (including product variants like Size/Color/Storage), customer
accounts, double-entry accounting, and a mobile-friendly Customer API for
your own storefront app to connect to. Built for retail shops, clothing
stores, mobile/electronics shops, restaurants, and general small/medium
businesses that need POS + inventory + accounting in one place.

**This is commercial software.** A license key is required to activate an
installation — see [Licensing](#licensing) below.

## Features

- **Point of Sale** — a dedicated, full-screen checkout screen with a
  product grid, barcode scanning, and receipt printing, independent from
  the regular admin sales flow.
- **Multi-location support** — set each location to Retail, Wholesale, or
  Both; a location-locked POS Manager role can be confined to a single
  location.
- **Product variants** — Size, Color, Storage, or any custom attribute
  combination, each with its own price, stock, and barcode. Fits clothing,
  mobile shops, restaurants, and general retail alike.
- **Inventory** — stock movements, low-stock/overstock alerts, barcode
  label printing, stock adjustments.
- **Sales & purchases** — full line-item sales/purchases with returns,
  credit terms, and customer credit-limit/hold policies.
- **Double-entry accounting** — every transaction posts to a real
  chart-of-accounts ledger; a built-in "Reconcile All Accounts" tool
  scans for missing/unbalanced entries.
- **Customer mobile API** — a token-authenticated REST API (`/api/v1/customer/*`)
  for a companion seller/storefront app to connect, browse the catalog, and
  place orders. Full docs are built into the admin panel under
  **System > API Documentation**, with a live **API Testing** console.
- **Roles & permissions** — Admin, Manager, Accountant, and POS Manager,
  with a fine-grained module/action permission matrix.

## Requirements

- PHP 8.2 or later
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- A standard Apache/Nginx web server with the document root pointed at
  the `public/` folder (this is the normal Laravel deployment shape —
  see the cPanel notes below if your host doesn't let you set that
  directly)

## Installation

### Option A — Local development

```bash
git clone https://github.com/izmadts/WSRetail.git
cd WSRetail
composer install
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`, then visit the app in your
browser — if `storage/installed` doesn't exist yet, you'll land on the
built-in setup wizard at `/install`, which walks through database
connection, admin account/company details, and license activation, and
writes `.env` for you at the end. You don't need to hand-run
`php artisan migrate` — the wizard does that as part of installation.

### Option B — Shared hosting / cPanel

Most small retail businesses run this on ordinary cPanel shared hosting
rather than a VPS, so that's the primary path documented here:

1. **Get the code onto the server.** Either use cPanel's Git Version
   Control feature (Git™ Version Control → Create → this repo's URL), or
   download a ZIP from GitHub and upload/extract it via File Manager.
2. **Point the domain/subdomain's document root at `public/`.** In cPanel
   this is usually under Domains → your domain → Document Root, or when
   creating a subdomain, set its document root to
   `yoursubdomain.com/public` instead of the subdomain's default folder.
   If your host doesn't let you choose a custom document root at all, see
   the note below.
3. **Install PHP dependencies.** If cPanel gives you SSH/Terminal access,
   run `composer install --optimize-autoloader --no-dev` from the app's
   root folder. If not, run `composer install` locally and upload the
   resulting `vendor/` folder alongside the rest of the code (it isn't
   committed to the repo).
4. **Create a MySQL database** via cPanel's MySQL Databases tool, and note
   the database name, username, and password (cPanel usually prefixes
   both with your account name, e.g. `cpaneluser_wsretail`).
5. **Set PHP version to 8.2+** under cPanel's "Select PHP Version" (MultiPHP
   Manager), and make sure the `pdo_mysql`, `mbstring`, `bcmath`, `gd`,
   and `zip` extensions are enabled there.
6. **Visit your domain.** You'll land on `/install` automatically. Enter
   the database details from step 4, then your admin account, company
   details, and your license key (see below). The wizard writes `.env`
   itself — you don't need to create one by hand.

**If your host won't let you set a custom document root** (some very
basic shared hosting only serves from the account's top-level `public_html`):
upload the app to a folder *above* `public_html` (e.g. `wsretail_app/`),
then either move the contents of `wsretail_app/public/` into `public_html/`
and edit the two path references at the top of `public_html/index.php` to
point up to `../wsretail_app/`, or ask your host to enable a custom
document root — most cPanel resellers can turn this on. A fuller
step-by-step version of this workaround, plus screenshots, belongs in
your own deployment notes once you've picked a specific host.

### After installing

- Log in with the admin account you created and open the built-in **Guide
  Book (SOPs)** from the sidebar — it documents day-to-day usage: sales,
  purchases, inventory, POS setup, and settings.
- **Settings → License** shows your activation status and lets you
  re-check-in or switch keys if you ever need to move the install to a
  new domain.

## Licensing

WSRetail requires a license key to activate. A locked installation still
lets an admin reach **Settings → License** to enter a key — nothing else
in the admin panel is reachable until it's activated.

To purchase a license:

- **WhatsApp:** +92 300 6163221
- **Email:** izmadts@gmail.com

## Support

This repository does not include a public issue tracker for end-user
support — use the contact details above. Bug reports and pull requests
against the codebase itself are welcome via GitHub Issues/PRs.

## License (software)

This is proprietary, commercially-licensed software — the source being
publicly viewable does not grant a license to run it. See
[Licensing](#licensing) above.
