# Deploying WSERP to a live server

## 1. Get the code onto the server

Upload/clone the repository to your hosting (via Git, SFTP, or your host's
deploy panel). Don't do anything else yet.

## 2. Run the deploy script once, via SSH

```bash
chmod +x deploy.sh
./deploy.sh
```

This installs Composer/npm dependencies, builds the frontend assets, creates
`.env` from `.env.example` if it doesn't exist yet, and fixes `storage`/
`bootstrap/cache` permissions. It does **not** touch the database or create
any accounts - that happens in the browser next.

If your web server runs as a different user than `www-data` (check your
host's docs - common alternatives are `nginx`, `apache`, or your own cPanel
username), pass it explicitly:

```bash
WEB_USER=nginx WEB_GROUP=nginx ./deploy.sh
```

Point your web server's document root at the `public/` folder (not the repo
root) - same as any Laravel app.

## 3. Finish setup in the browser

Visit your domain. Since nothing is installed yet, it redirects you straight
to `/install` - a 2-step wizard:

1. **Database** - host/port/name/username/password. It tests the connection
   before letting you continue.
2. **Admin & Company** - your real admin login (name/email/password) and
   company name/logo/phone/address.

Submitting step 2 runs the migrations, seeds structural defaults (chart of
accounts, product/expense/income categories, customer groups, and
commission policy defaults, role permissions - **never** demo/test data),
creates your admin account, saves your company settings, and writes
`storage/installed` so the wizard can't be run again by accident. From there
you're redirected to a completion page and can log in.

To re-run the wizard later (e.g. a full reset), delete `storage/installed`
on the server - this does **not** touch any existing data by itself, it just
unlocks the wizard again.

## 4. Update the mobile apps' API base URLs - not automatic

WSERP's own `APP_URL` is set automatically during step 2 (detected from
whatever domain you visited to run the wizard) - nothing to do there. The
Flutter apps are separate codebases with their own hardcoded dev URLs that
**do not update themselves** and must be changed by hand before a release
build:

| App | File | What to change |
|---|---|---|
| `izmafood-vendors` (Mandi/seller app) | `lib/config/wserp_constants.dart` | `kWserpBaseUrlFallback` -> your real domain + `/api/v1/customer`; `kWserpIntegrationKeyFallback` -> must match `CUSTOMER_API_INTEGRATION_KEY` in WSERP's `.env` on the live server (rotate it there for production, don't ship the dev value) |
| `izmafood_saleagent` (sales agent app) | `lib/services/api_client.dart` | `ApiClient.baseUrl` -> your real domain + `/api/v1/agent` |
| `izmafood-vendors` (existing izmafood.com integration) | `lib/config/constants.dart` | `kBaseApiUrl` - already production (`https://izmafood.com/api/`), no change needed |

Both apps also support overriding via a bundled `.env` (`flutter_dotenv`) if
you'd rather not hardcode the production values into source - see each
constants file's fallback pattern.

## 5. Recommended follow-ups (not automated by the wizard)

- Set up HTTPS (Let's Encrypt or your host's SSL) if not already on.
- Change `QUEUE_CONNECTION`/mail settings in `.env` if you need background
  jobs or outgoing email beyond the defaults.
- Take a database backup before ever deleting `storage/installed`.
