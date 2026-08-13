# Deploying WSRetail to a live server

This covers the SSH/CLI path. If your host only gives you cPanel with no
shell access, use the step-by-step cPanel guide in [README.md](README.md)
instead - both end up at the same `/install` wizard.

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
to `/install` - a short wizard:

1. **Database** - host/port/name/username/password. It tests the connection
   before letting you continue.
2. **Admin, company & license** - your real admin login (name/email/password),
   company name/logo/phone/address, and your license key (see
   [Licensing](README.md#licensing) in the README if you don't have one yet).

Submitting the final step runs the migrations, seeds structural defaults
(chart of accounts, product/expense/income categories, customer groups, role
permissions - **never** demo/test data), creates your admin account, saves
your company settings, activates your license, and writes `storage/installed`
so the wizard can't be run again by accident. From there you're redirected to
a completion page and can log in.

To re-run the wizard later (e.g. a full reset), delete `storage/installed`
on the server - this does **not** touch any existing data by itself, it just
unlocks the wizard again.

## 4. Connect a storefront (optional)

If you're also deploying the companion Next.js storefront
(`wsretail-storefront`) so customers can shop online:

- Deploy the storefront separately (its own subdomain or the main domain -
  see that repo's own README for its cPanel "Setup Node.js App" steps).
- In WSRetail, go to **System > API Documentation** to get the Customer API
  base URL and generate/copy `CUSTOMER_API_INTEGRATION_KEY` from `.env`.
- In the storefront's environment, set `WSRETAIL_API_URL` and
  `WSRETAIL_INTEGRATION_KEY` to match - see the storefront's
  `.env.local.example`.

The two apps talk to each other purely over HTTPS via that API; no shared
filesystem or database access is needed, so they can live on completely
different domains/servers.

## 5. Recommended follow-ups (not automated by the wizard)

- Set up HTTPS (Let's Encrypt or your host's SSL) if not already on.
- Change `QUEUE_CONNECTION`/mail settings in `.env` if you need background
  jobs or outgoing email beyond the defaults.
- Take a database backup before ever deleting `storage/installed`.
