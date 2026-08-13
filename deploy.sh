#!/usr/bin/env bash
#
# WSRetail deploy bootstrap - run this ONCE via SSH after uploading/cloning the
# codebase to a live server, before visiting the site in a browser.
#
# What this does NOT do (deliberately): touch the database, create the admin
# account, or write app settings - that's the job of the web setup wizard at
# /install, which this script's final step points you to. This script only
# gets the CODE ready to run (composer/npm dependencies, .env bootstrap,
# folder permissions) - none of which the wizard itself can do, since it
# needs the Composer autoloader to exist before any Laravel code (including
# the wizard) can execute at all.
#
# Usage:
#   chmod +x deploy.sh
#   ./deploy.sh                       # uses default web user (www-data)
#   WEB_USER=nginx WEB_GROUP=nginx ./deploy.sh   # override for your host
#
set -euo pipefail

WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"
SKIP_NPM="${SKIP_NPM:-0}"

cd "$(dirname "$0")"

info()  { printf '\n\033[1;34m==>\033[0m %s\n' "$1"; }
warn()  { printf '\033[1;33m! %s\033[0m\n' "$1"; }
fail()  { printf '\033[1;31mERROR: %s\033[0m\n' "$1"; exit 1; }

# ------------------------------------------------------------------
# 1. Sanity checks
# ------------------------------------------------------------------
info "Checking requirements..."
command -v php >/dev/null 2>&1 || fail "PHP is not on PATH."
command -v composer >/dev/null 2>&1 || fail "Composer is not on PATH."

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
info "PHP version: $PHP_VERSION"
php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' \
    || fail "PHP 8.2+ is required (found $PHP_VERSION)."

for ext in pdo_mysql mbstring openssl tokenizer xml ctype json bcmath fileinfo; do
    php -m | grep -qi "^${ext}$" || warn "PHP extension '$ext' does not appear to be enabled - the app may not boot without it."
done

# ------------------------------------------------------------------
# 2. .env bootstrap (the web wizard fills in DB/APP_URL/APP_KEY - this
#    just makes sure a file exists for it to write into)
# ------------------------------------------------------------------
if [ ! -f .env ]; then
    info "No .env found - copying from .env.example"
    cp .env.example .env
else
    info ".env already exists - leaving it alone"
fi

# ------------------------------------------------------------------
# 3. PHP dependencies
# ------------------------------------------------------------------
info "Installing PHP dependencies (composer install --no-dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

# ------------------------------------------------------------------
# 4. Frontend build (Tailwind/Alpine/Chart.js via Vite - see package.json).
#    Skippable with SKIP_NPM=1 if assets are pre-built elsewhere (e.g. a
#    CI pipeline that ships public/build already populated).
# ------------------------------------------------------------------
if [ "$SKIP_NPM" = "1" ]; then
    info "SKIP_NPM=1 - skipping frontend build."
elif command -v npm >/dev/null 2>&1; then
    info "Building frontend assets (npm ci && npm run build)..."
    npm ci
    npm run build
else
    warn "npm not found - skipping frontend build. Admin pages need public/build/manifest.json to render correctly; build it separately (e.g. on your machine, then upload public/build/) before going live."
fi

# ------------------------------------------------------------------
# 5. Permissions - storage/ and bootstrap/cache/ must be writable by
#    whatever user PHP-FPM/Apache runs as.
# ------------------------------------------------------------------
info "Setting storage/bootstrap permissions (web user: $WEB_USER:$WEB_GROUP)..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs bootstrap/cache
if command -v chown >/dev/null 2>&1 && [ "$(id -u)" = "0" ]; then
    chown -R "$WEB_USER":"$WEB_GROUP" storage bootstrap/cache
else
    warn "Not running as root (or chown unavailable) - skipping chown. Run 'sudo chown -R $WEB_USER:$WEB_GROUP storage bootstrap/cache' yourself if the app can't write to these folders."
fi
chmod -R 775 storage bootstrap/cache

# ------------------------------------------------------------------
# 6. Done - point at the web wizard (or note it's already installed)
# ------------------------------------------------------------------
if [ -f storage/installed ]; then
    info "storage/installed already exists - this looks like a redeploy of an existing install."
    echo "Code and dependencies are up to date. No further setup needed."
else
    info "Code is ready. Point your web server's document root at: $(pwd)/public"
    echo ""
    echo "Then visit your domain in a browser to finish setup - it will redirect"
    echo "you straight into the install wizard (database credentials, admin"
    echo "account, company name/logo, license key). Nothing else to run by hand."
    echo ""
    warn "Reminder: this only prepares the WSRetail backend. If you're also"
    warn "deploying the companion Next.js storefront, see DEPLOYMENT.md step 4"
    warn "for how to connect it."
fi
