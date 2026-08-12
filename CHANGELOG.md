# WSERP Changelog

Running record of what's been built/fixed, kept for whoever (human or AI)
picks this project up next. Newest entries first. This is a work log, not
user-facing release notes.

## 2026-08-06

### Golden Club — feature completion

- **Referral program**: was previously dead code (nothing could ever create
  a referral). Added an optional referral-code field to the customer
  connect flow; a new admin setting (Settings > Golden Club) lets the
  referral program be turned off entirely if not wanted.
- **Reward tier-gating**: replaced the old single "minimum tier" dropdown
  with a free multi-select (`eligible_membership_levels`, JSON column) so a
  reward can be restricted to any combination of Silver/Gold/Platinum, or
  left open to everyone.
- **Points expiry (FIFO)**: points now expire a configurable number of
  months after being earned (flat default + optional per-tier override, so
  e.g. Platinum members can be given longer-lasting points). A new
  scheduled command (`golden-club:expire-points`, daily) replays each
  customer's transaction history in strict earn-order (oldest points spent
  first) to expire exactly the right remaining amount, and sends an
  advance-warning notification for points expiring soon.
- **OTP verification — clarified, not changed**: confirmed via full
  codebase read that `otp_verified` is **not** a real OTP/SMS flow — no
  SMS gateway is integrated anywhere in this project. It's set to `true`
  only when an admin manually clicks "Verify" on a customer's Golden Club
  profile (Admin > Golden Club > Pending Verification). A customer created
  straight through the admin panel does **not** earn Golden Club points
  until someone does this manually. Documented here since it's easy to
  mistake for a bug the first time you notice it.
- **Help tooltips**: a reusable `<x-help-tooltip>` component (pure CSS,
  hover-only — no click handling, so it can never intercept a click meant
  for a nearby control) was rolled out across Golden Club settings, general
  Settings, Commission & Bonus settings, and the Sale/Purchase/Product/
  Customer/Sales-Return/Purchase-Return/Expense/Income create+edit forms —
  explaining what each non-obvious field actually does, verified against
  the real backend behavior before being written (several early drafts
  were caught and corrected against what the code actually does).

### Mobile apps (Sale Agent + Mandi/Vendor)

- Sale Agent app (`izmafood_saleagent`): reward model now carries
  `reward_type`/instant-delivery flag; `redeemReward()` shows the server's
  real success message instead of hardcoded text.
- Vendor app (`izmafood-vendors`): added a full Golden Club screen (points
  summary, referral code with copy-to-clipboard, reward store, redemption
  history) reachable from a new button on the Mandi home page; added the
  optional referral-code field to the Mandi connect flow.
- Confirmed neither change touches the main vendor app's connection to the
  real izmafood.com marketplace — all Golden Club/Mandi work is isolated to
  its own screens and its own WSERP API client.

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
  credit-hold grace period (same "flat default + override" shape as the
  points-expiry tiers above). Also discovered and fixed: the existing
  "Block new credit sales to customers overdue past the grace period"
  checkbox was already in the UI but wired to nothing — it now actually
  blocks.
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
  floreing at `0`.

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
