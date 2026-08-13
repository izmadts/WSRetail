@extends('layouts.admin')

@section('title', 'Guide Book')
@section('page-title', 'Software Management Guide Book')

@section('content')
<div x-data="{ q: '' }" class="flex flex-col lg:flex-row gap-6">

    <!-- Table of Contents -->
    <div class="lg:w-72 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-card p-4 lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
            <div class="mb-3">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
                    <input type="text" x-model="q" placeholder="Search topics..."
                        class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            @php
                $toc = [
                    'Getting Started' => [
                        'about' => ['fa-info-circle', 'About This System'],
                        'how-money-flows' => ['fa-exchange-alt', 'How Money Flows (Double-Entry Basics)'],
                        'roles-permissions' => ['fa-user-shield', 'Roles & Permissions'],
                        'top-bar-tools' => ['fa-toolbox', 'Top Bar Tools'],
                    ],
                    'Inventory' => [
                        'categories' => ['fa-tags', 'Categories'],
                        'products' => ['fa-box', 'Products'],
                        'stock-adjustments' => ['fa-sliders-h', 'Stock Adjustments'],
                    ],
                    'Purchases' => [
                        'suppliers' => ['fa-truck', 'Suppliers'],
                        'purchases' => ['fa-shopping-cart', 'Purchases'],
                        'purchase-returns' => ['fa-undo-alt', 'Purchase Returns'],
                        'supplier-payments' => ['fa-money-bill', 'Supplier Payments'],
                    ],
                    'Sales' => [
                        'customers' => ['fa-users', 'Customers'],
                        'sales' => ['fa-shopping-bag', 'Sales'],
                        'sales-returns' => ['fa-undo-alt', 'Sales Returns'],
                        'customer-payments' => ['fa-money-bill', 'Customer Payments'],
                        'pos' => ['fa-cash-register', 'POS Setup'],
                    ],
                    'HR & Payroll' => [
                        'employees' => ['fa-id-badge', 'Employees & Departments'],
                        'leave' => ['fa-calendar-check', 'Leave Management'],
                        'payroll' => ['fa-money-check-alt', 'Salary & Payroll'],
                    ],
                    'Finance' => [
                        'income' => ['fa-arrow-up', 'Income'],
                        'expenses' => ['fa-arrow-down', 'Expenses'],
                        'money-transfers' => ['fa-exchange-alt', 'Money Transfers'],
                    ],
                    'Accounting' => [
                        'chart-of-accounts' => ['fa-book', 'Chart of Accounts & Ledger'],
                        'bank-reconciliation' => ['fa-university', 'Bank Reconciliation'],
                        'reconcile-all' => ['fa-magic', 'Reconcile All Accounts'],
                    ],
                    'Reports' => [
                        'reports' => ['fa-chart-bar', 'Reports Overview'],
                    ],
                    'Settings & Access' => [
                        'settings' => ['fa-sliders-h', 'General & Business Settings'],
                        'users-permissions' => ['fa-shield-alt', 'Users & Permissions'],
                    ],
                    'System Tools' => [
                        'activity-logs' => ['fa-history', 'Activity Logs'],
                        'backups' => ['fa-cloud-upload-alt', 'Backup & Restore'],
                        'exports' => ['fa-file-export', 'Exports'],
                    ],
                    'Golden Rules' => [
                        'golden-rules' => ['fa-star', 'Data Entry Golden Rules'],
                    ],
                ];
            @endphp

            <nav class="space-y-3 text-sm">
                @foreach($toc as $group => $items)
                <div>
                    <p class="sidebar-section-title mb-1">{{ $group }}</p>
                    <div class="space-y-0.5">
                        @foreach($items as $id => [$icon, $label])
                        <a href="#{{ $id }}" x-show="q === '' || '{{ strtolower($label) }}'.includes(q.toLowerCase())"
                            class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                            <i class="fas {{ $icon }} w-4 text-gray-400"></i> {{ $label }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0 space-y-6">

        <section id="about" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-info-circle text-blue-500 mr-2"></i>About This System</h2>
            <p class="text-gray-700 leading-7 mb-3">
                WSRetail is a single connected system for the whole business - inventory, purchasing, sales,
                HR & payroll, and full double-entry accounting all live in one place and feed the same ledger. This
                Guide Book explains, module by module, what each screen is for, exactly what to fill in, and the
                Standard Operating Procedure (SOP) for keeping data clean and the accounts accurate.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-900 font-semibold mb-1"><i class="fas fa-lightbulb mr-1"></i> The one rule that matters most</p>
                <p class="text-blue-800 text-sm leading-6">
                    Every real transaction (a sale, a purchase, an expense, a payroll run, a payment) automatically
                    updates the Chart of Accounts behind the scenes - you never manually "post to accounts." Because
                    of that, the single most important discipline in this system is: <strong>enter every transaction
                    through its own module, using real dates and real amounts, at the time it actually happens.</strong>
                    Skipping this, or fixing mistakes by editing numbers directly instead of using the correct
                    reversing action (a return, a cancellation, a reversing payment), is what causes the books to
                    stop matching reality.
                </p>
            </div>
        </section>

        <section id="how-money-flows" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-exchange-alt text-blue-500 mr-2"></i>How Money Flows (Double-Entry Basics)</h2>
            <p class="text-gray-700 leading-7 mb-3">
                Every account in <a href="#chart-of-accounts" class="text-blue-600 hover:underline">Chart of Accounts</a>
                belongs to one of five types: <strong>Asset</strong>, <strong>Liability</strong>, <strong>Equity</strong>,
                <strong>Revenue</strong>, or <strong>Expense</strong>. Every real transaction touches at least two
                accounts - one side is <span class="text-red-600 font-semibold">Debit</span>, the other is
                <span class="text-green-600 font-semibold">Credit</span> - and the two sides always add up to the same
                total. Throughout this system, <span class="text-red-600 font-semibold">Debit figures are shown in
                red</span> and <span class="text-green-600 font-semibold">Credit figures in green</span> (Ledger,
                Trial Balance, Day Book, Chart of Accounts).
            </p>
            <p class="text-gray-700 leading-7 mb-3">A few real examples from this system, so the idea is concrete:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="font-semibold text-gray-800 text-sm mb-1">A cash Sale</p>
                    <p class="text-xs text-gray-500"><span class="text-red-600">Dr</span> Cash (1010) &middot; <span class="text-green-600">Cr</span> Sales Revenue (4010)</p>
                    <p class="text-xs text-gray-500"><span class="text-red-600">Dr</span> Purchase Cost / COGS (5010) &middot; <span class="text-green-600">Cr</span> Inventory Stock (1030)</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="font-semibold text-gray-800 text-sm mb-1">A credit Purchase</p>
                    <p class="text-xs text-gray-500"><span class="text-red-600">Dr</span> Inventory Stock (1030) &middot; <span class="text-green-600">Cr</span> Accounts Payable (2010)</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="font-semibold text-gray-800 text-sm mb-1">Paying a Supplier</p>
                    <p class="text-xs text-gray-500"><span class="text-red-600">Dr</span> Accounts Payable (2010) &middot; <span class="text-green-600">Cr</span> Cash/Bank</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="font-semibold text-gray-800 text-sm mb-1">An Expense</p>
                    <p class="text-xs text-gray-500"><span class="text-red-600">Dr</span> General Expenses (5030) &middot; <span class="text-green-600">Cr</span> Cash/Bank</p>
                </div>
            </div>
            <p class="text-gray-700 leading-7">
                Because the system enforces this automatically, it is <strong>never necessary or correct to manually
                add or edit a Journal Entry</strong> to "fix" a number. If a figure looks wrong, the correct fix is
                always one of: edit the original transaction (if nothing downstream depends on it yet), enter a
                proper Return/Reversal, or run <a href="#reconcile-all" class="text-blue-600 hover:underline">Reconcile
                All Accounts</a> to find and repair the actual discrepancy.
            </p>
        </section>

        <section id="roles-permissions" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-user-shield text-blue-500 mr-2"></i>Roles & Permissions</h2>
            <p class="text-gray-700 leading-7 mb-3">There are four roles in the system:</p>
            <ul class="list-disc pl-6 space-y-1.5 text-gray-700 leading-6 mb-3">
                <li><strong>Admin</strong> - full access to everything, always. The only role that can reach Settings, Permissions, User accounts, Backups, and this Guide Book's neighboring System tools.</li>
                <li><strong>Manager</strong> and <strong>Accountant</strong> - log into the same admin panel as Admin, but only see and can act on the modules an Admin has granted them under <a href="#users-permissions" class="text-blue-600 hover:underline">Settings &rarr; Permissions</a>.</li>
                <li><strong>POS Manager</strong> - locked to the POS checkout screen only, optionally locked to one location - can never reach Settings or any other admin module.</li>
            </ul>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-900 text-sm leading-6">
                    Permissions are per-module and per-ability: each of the ~25 modules (Accounts, Products, Sales,
                    Expenses, Employees, Payroll, and so on) has its own <strong>View / Create / Edit / Delete</strong>
                    toggles for Manager and Accountant. A user who can View Employees but not Payroll genuinely cannot
                    open payroll pages - it isn't just a hidden menu link, it's enforced on every request. Set this up
                    under Settings &rarr; Permissions before handing an account to a new staff member.
                </p>
            </div>
        </section>

        <section id="top-bar-tools" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-toolbox text-blue-500 mr-2"></i>Top Bar Tools</h2>
            <p class="text-gray-700 leading-7 mb-3">A row of quick tools sits in the header, to the left of the profile menu, on every admin page:</p>
            <ul class="list-disc pl-6 space-y-1.5 text-gray-700 text-sm leading-6 mb-3">
                <li><strong>Quick Search</strong> (<kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">Ctrl</kbd>/<kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">Cmd</kbd>+<kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">K</kbd>, magnifying-glass icon) - jump straight to a Customer, Supplier, Product, or Sale/Purchase invoice by typing a few characters, from anywhere. Only searches modules you have View access to.</li>
                <li><strong>Quick Add</strong> (+ icon) - one click to New Sale/Purchase/Product/Customer/Supplier/Expense/Income. Only lists what you have Create access to, and disappears entirely if that's nothing.</li>
                <li><strong>Notifications</strong> (bell icon) - live counts for Low Stock products and Pending Leave requests, each gated the same way as its module. Click a row to go straight to that list, already filtered.</li>
                <li><strong>Fullscreen</strong> (expand icon, desktop only) - toggles the browser's fullscreen mode for distraction-free data entry.</li>
                <li><strong>Reconcile All Accounts</strong> (red heartbeat icon, admin only) - shortcut straight to the <a href="#reconcile-all" class="text-blue-600 hover:underline">ledger integrity tool</a>.</li>
                <li><strong>Keyboard Shortcuts</strong> (keyboard icon, desktop only, or press <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">?</kbd>) - opens a cheat-sheet of every <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">G</kbd> then <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">letter</kbd> navigation shortcut (e.g. <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">G</kbd> <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">S</kbd> for Sales, <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">G</kbd> <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">P</kbd> for Purchases), filtered to modules you can view. Shortcuts never fire while typing in a text field.</li>
                <li><strong>Dark Mode</strong> (moon/sun icon) - if enabled in Settings &rarr; General.</li>
            </ul>
            <p class="text-gray-700 text-sm leading-6">Every one of these respects the same permission matrix as the rest of the app - nothing here bypasses <a href="#roles-permissions" class="text-blue-600 hover:underline">Roles & Permissions</a>, it's just a faster way to reach what you already have access to.</p>
        </section>

        <!-- ===================== INVENTORY ===================== -->

        <section id="categories" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-tags text-yellow-600 mr-2"></i>Categories</h2>
            <p class="text-gray-700 leading-7 mb-3">Groups products for browsing, reporting, and filtering. Supports one level of sub-categories via a parent category.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Name, Description, Parent Category (optional), Active/Inactive.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Create categories before adding products - every product must belong to one.</li>
                <li>Keep names short and consistent (e.g. "Beverages", not "beverages" and "Beverage" as two separate entries).</li>
                <li>Deactivating a category does not delete or hide its existing products - reassign products to another category first if you're retiring one.</li>
            </ol>
        </section>

        <section id="products" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-box text-yellow-600 mr-2"></i>Products</h2>
            <p class="text-gray-700 leading-7 mb-3">The master item list - every Purchase and Sale line references a Product here.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Code, Name, Category, Unit (pcs/kg/box...), Purchase Price, Sale Price, Wholesale Price, Min &amp; Max Stock Level, Barcode, Image, Description, Active/Inactive.</p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                <p class="text-blue-900 text-sm leading-6">
                    <strong>Current Stock is never typed in directly on the edit form</strong> once a product has
                    history - it only changes as a side-effect of real transactions: Purchases add stock, Sales
                    subtract it, Returns reverse it, and Stock Adjustments correct it. This is deliberate: it keeps
                    the stock figure traceable to an actual audit trail instead of a number someone typed once and
                    forgot about.
                </p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Set <strong>Min Stock Level</strong> honestly for every product - it drives the sidebar's Low Stock Alert badge, which is only useful if the threshold is realistic.</li>
                <li>Sale Price and Wholesale Price are starting defaults for new Sales - they can still be overridden per line on an actual sale when needed.</li>
                <li>Don't delete a product that has purchase/sale history - deactivate it instead, so past invoices keep displaying correctly.</li>
            </ol>
        </section>

        <section id="stock-adjustments" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-sliders-h text-yellow-600 mr-2"></i>Stock Adjustments</h2>
            <p class="text-gray-700 leading-7 mb-3">The only correct way to change a product's stock outside of a Purchase or Sale - physical stock counts, damage, theft, expiry, or entering true opening stock for a brand-new product.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Product, Type (In / Out), Quantity, Reason, Notes. Stock Before/After is captured automatically for the audit trail.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> an <strong>Out</strong> adjustment (loss/shrinkage) posts <span class="text-red-600">Dr</span> Inventory Shrinkage &amp; Adjustments (5040) / <span class="text-green-600">Cr</span> Inventory Stock (1030). An <strong>In</strong> adjustment (found stock/correction) reverses that.</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-900 text-sm leading-6">
                    <strong>Do not</strong> use a Stock Adjustment to paper over a wrong Purchase or Sale entry (e.g.
                    the wrong quantity was typed on an invoice). Fix the original Purchase/Sale instead - an
                    adjustment used that way hides the real mistake and quietly mis-states Cost of Goods Sold.
                </p>
            </div>
        </section>

        <!-- ===================== PURCHASES ===================== -->

        <section id="suppliers" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-truck text-purple-700 mr-2"></i>Suppliers</h2>
            <p class="text-gray-700 leading-7 mb-3">Every vendor the business buys from. A Supplier's balance (Accounts Payable) is entirely driven by its Purchases, Purchase Returns, and Payments - it is never edited directly.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Code, Name, Email, Phone/Mobile, Address (City/State/Country), CNIC, NTN/STRN (tax numbers), Opening Balance, Credit Limit, Credit Days, Notes, Active/Inactive.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li><strong>Opening Balance</strong> is a one-time figure entered when a supplier is first added to the system (what you already owed them before going digital) - it posts straight to the ledger the moment the supplier is created, so get it right the first time.</li>
                <li>Set a realistic Credit Limit and Credit Days if this supplier extends you credit - some reports and dashboards use these.</li>
                <li>Never delete a supplier with purchase history - deactivate instead.</li>
            </ol>
        </section>

        <section id="purchases" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-shopping-cart text-purple-700 mr-2"></i>Purchases</h2>
            <p class="text-gray-700 leading-7 mb-3">Every stock-in transaction from a supplier.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Invoice No. (auto), Supplier, Purchase Date, Due Date, Payment Term (Cash/Credit), line items (Product, Qty, Price), Discount, Tax, Shipping Cost.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Status flow</p>
            <p class="text-gray-600 text-sm mb-3"><code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Draft</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Ordered</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Received</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Partial</code> / <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Paid</code>, or <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Cancelled</code> at any point.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact (on receiving stock):</strong> <span class="text-red-600">Dr</span> Inventory Stock (1030) / <span class="text-green-600">Cr</span> Accounts Payable (2010) for a credit purchase, or <span class="text-green-600">Cr</span> Cash/Bank for a cash purchase.</p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Use <strong>Draft</strong> while an order is still being negotiated - nothing hits stock or the ledger until it moves to Received.</li>
                <li>Enter the real purchase date, not today's date, if the goods actually arrived earlier - Day Book and reports are date-driven.</li>
                <li>If part of the cost is paid immediately, record it as a payment rather than marking the whole purchase Paid without a matching payment - see <a href="#supplier-payments" class="text-blue-600 hover:underline">Supplier Payments</a>.</li>
            </ol>
        </section>

        <section id="purchase-returns" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-undo-alt text-purple-700 mr-2"></i>Purchase Returns</h2>
            <p class="text-gray-700 leading-7 mb-3">Goods sent back to a supplier - damaged, wrong item, over-ordered, etc. Always created against an existing Purchase.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> Accounts Payable (2010) (or <span class="text-red-600">Dr</span> Cash/Bank if already paid and refunded) / <span class="text-green-600">Cr</span> Inventory Stock (1030) - stock and the amount owed both go down together.</p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <p class="text-gray-700 text-sm leading-6">Always return through this module, referencing the original Purchase - never adjust the Purchase's own quantities after the fact once it's been Received, since other reports and the supplier's running balance already reflect it.</p>
        </section>

        <section id="supplier-payments" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-money-bill text-purple-700 mr-2"></i>Supplier Payments</h2>
            <p class="text-gray-700 leading-7 mb-3">Recorded from a Supplier's own detail page - every payment you make against what you owe a supplier, whether against one purchase or as a general running-balance payment.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Amount, Payment Date, Method (Cash/Bank Transfer/Cheque/Credit Card), Reference No., optional Bank Service Charge, Notes.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> Accounts Payable (2010) / <span class="text-green-600">Cr</span> Cash or Bank Account. A Bank Service Charge, if entered, posts as its own small expense line.</p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Payments can be edited or deleted from the same page if a mistake was made - both actions correctly reverse and repost the ledger, so use them instead of a new offsetting entry.</li>
                <li>Use the real payment date, especially for Bank Reconciliation to line up correctly later.</li>
            </ol>
        </section>

        <!-- ===================== SALES ===================== -->

        <section id="customers" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-users text-emerald-600 mr-2"></i>Customers</h2>
            <p class="text-gray-700 leading-7 mb-3">Everyone the business sells to - added directly by staff, or via the Customer API (seller/vendor app integration).</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Code, Name, Email, Phone/Mobile, Address, CNIC/NTN, Opening Balance, Credit Limit, Credit Days, Notes, Active/Inactive.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Same rule as Suppliers: Opening Balance is entered once, at creation, and posts to Accounts Receivable immediately.</li>
                <li>Set Credit Limit / Credit Days for any customer who regularly buys on credit - Settings &rarr; Credit can optionally block new credit sales once a customer is overdue past this or over their limit.</li>
            </ol>
        </section>

        <section id="sales" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-shopping-bag text-emerald-600 mr-2"></i>Sales</h2>
            <p class="text-gray-700 leading-7 mb-3">Every sale, whether entered through the regular admin sale form or the POS checkout screen.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Invoice No. (auto), Customer, Location (optional), Sale Date, Due Date, Payment Term (Cash/Credit), line items, Discount, Tax, Shipping Cost.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Status flow</p>
            <p class="text-gray-600 text-sm mb-3"><code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Draft</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Confirmed</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Partial</code> / <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Paid</code>, or <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Cancelled</code>.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700 mb-1"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> Cash or Accounts Receivable (1040) / <span class="text-green-600">Cr</span> Sales Revenue (4010).</p>
                <p class="text-sm text-gray-700">Plus the cost side: <span class="text-red-600">Dr</span> Purchase Cost / COGS (5010) / <span class="text-green-600">Cr</span> Inventory Stock (1030), so gross profit is always correct without any manual step.</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-3">
                <p class="text-red-900 text-sm leading-6">
                    <strong>Critical rule:</strong> a sale only becomes truly "Paid" when a real
                    <strong>payment is recorded</strong> against it
                    (in full or via "Pay in Full"). Simply changing the Status dropdown to Paid without recording a
                    payment does not move any money in the ledger and will under-count what the customer actually owes.
                    Always create the sale as Draft/Confirmed, then record the payment when it's actually received.
                </p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Confirm a sale (move off Draft) only once it's real and stock should actually be committed.</li>
                <li>For credit sales, always set a sensible Due Date - it drives overdue tracking and the credit-hold policy.</li>
            </ol>
        </section>

        <section id="sales-returns" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-undo-alt text-emerald-600 mr-2"></i>Sales Returns</h2>
            <p class="text-gray-700 leading-7 mb-3">Goods a customer sends back. Always created against an existing Sale.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> Sales Revenue (4010) / <span class="text-green-600">Cr</span> Accounts Receivable or Cash-Bank (refund) - <em>and</em> the cost side reverses too: <span class="text-red-600">Dr</span> Inventory Stock (1030) back in / <span class="text-green-600">Cr</span> Purchase Cost (5010).</p>
            </div>
        </section>

        <section id="customer-payments" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-money-bill text-emerald-600 mr-2"></i>Customer Payments</h2>
            <p class="text-gray-700 leading-7 mb-3">Recorded from a Customer's own detail page, exactly mirroring Supplier Payments.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> Cash or Bank Account / <span class="text-green-600">Cr</span> Accounts Receivable (1040).</p>
            </div>
        </section>

        <section id="pos" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-cash-register text-teal-500 mr-2"></i>POS Setup</h2>
            <p class="text-gray-700 leading-7 mb-3">The POS checkout screen (topbar "POS" button, opens in its own tab) is a faster, cashier-style front end onto the same sale-creation endpoint the regular New Sale form uses - same stock/accounting/credit-gate rules apply, it's just a click-to-add product grid instead of manual rows.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Set up each physical location under Settings &rarr; Locations, choosing its POS Use (Retail only, Wholesale only, or Both) - this locks which products/pricing a sale from that location can use.</li>
                <li>Configure defaults (default location/customer, receipt paper size, enabled payment methods, barcode label format) under Settings &rarr; POS Settings.</li>
                <li>For a cashier who should only ever use the POS - never the rest of the admin panel - create them as a <strong>POS Manager</strong> under Settings &rarr; Users, with a fixed Location. Their POS screen shows that location as locked (no switcher), and the server re-enforces it on every sale regardless of what the browser sends.</li>
            </ol>
        </section>

        <!-- ===================== HR ===================== -->

        <section id="employees" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-id-badge text-indigo-500 mr-2"></i>Employees & Departments</h2>
            <p class="text-gray-700 leading-7 mb-3">The HR employee register. Two ways an Employee record can exist:</p>
            <ul class="list-disc pl-6 space-y-1.5 text-gray-700 text-sm leading-6 mb-3">
                <li><strong>Auto-linked</strong> - the instant anyone gets a system login (admin/manager/accountant/pos_manager), an Employee record is created for them automatically. Nothing to do here.</li>
                <li><strong>Standalone</strong> - added manually here, for staff who never log into the software at all (warehouse, delivery, other operational/supply-chain roles). Can later be given a login via "Grant System Access" on their profile, without creating a duplicate. <strong>Admin-only</strong> - deliberately not covered by the Employees permission, since it can create an admin/manager/accountant login, the same sensitivity as adding one under Settings &rarr; Users.</li>
            </ul>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Employee Code (auto, e.g. EMP-0001), Name, Contact & Emergency Contact, CNIC, Department, Designation, Employment Type (Full-time/Part-time/Contract/Intern), Date of Joining/Leaving, Employment Status (Active/On Leave/Suspended/Terminated/Resigned), Reporting Manager.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Set up Departments first if you want to organize staff by team.</li>
                <li>Keep Employment Status current - Payroll only pays employees who are Active or On Leave.</li>
                <li>Never delete an employee with leave/payroll history - the record is soft-deleted (recoverable) but historical payslips must stay intact for records.</li>
            </ol>
        </section>

        <section id="leave" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-calendar-check text-indigo-500 mr-2"></i>Leave Management</h2>
            <p class="text-gray-700 leading-7 mb-3">Simple request &rarr; approve/reject workflow via the admin panel's self-service "My Leave" page.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Set up Leave Types first (e.g. Annual, Sick, Casual, Unpaid), each with its own default days/year and whether it's paid.</li>
                <li>Any staff member with a login requests their own leave from "My Leave"; an admin/manager with the <strong>leaves</strong> permission approves or rejects it under Leave Requests.</li>
                <li>Remaining balance is always calculated live from approved requests for the current year - it is never a number you edit directly.</li>
                <li>Only the employee who submitted a still-pending request can cancel it themselves.</li>
            </ol>
        </section>

        <section id="payroll" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-money-check-alt text-indigo-500 mr-2"></i>Salary & Payroll</h2>
            <p class="text-gray-700 leading-7 mb-3">Detailed, effective-dated pay structures per employee, processed into a real monthly payroll run that posts to the ledger.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Step 1 - Salary Structure</p>
            <p class="text-gray-700 text-sm leading-6 mb-3">Under Salary Structures, set each employee's Basic Pay + itemized Allowances (House Rent, Medical, Fuel, Other) + Deductions (Tax, Other), with an Effective From date. A raise or change is always a <strong>new row</strong>, never an edit of an old one - this keeps a full history of every past salary automatically.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Step 2 - Run Payroll</p>
            <p class="text-gray-700 text-sm leading-6 mb-3">Under Payroll Runs &rarr; Run Payroll, pick a month. The system previews every active employee with a salary structure, lets you add any Overtime amount per person, then processes it - generating one Payslip per employee. A month can only be processed once.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
                <p class="text-sm text-gray-700"><strong>Accounting impact (processing):</strong> <span class="text-red-600">Dr</span> Salary Expense (5050) / <span class="text-green-600">Cr</span> Salary Payable (2030) per payslip.</p>
                <p class="text-sm text-gray-700"><strong>Accounting impact (paying a payslip):</strong> <span class="text-red-600">Dr</span> Salary Payable (2030) / <span class="text-green-600">Cr</span> Cash/Bank.</p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">Step 3 - Pay Payslips</p>
            <p class="text-gray-700 text-sm leading-6 mb-3">Open a processed run and use "Pay" on each payslip once salary is actually disbursed - a discrete payment per payslip, not a running tab.</p>
            <p class="text-gray-700 text-sm leading-6">Employees see their own payslip history under "My Payslips" in the admin panel.</p>
        </section>

        <!-- ===================== FINANCE ===================== -->

        <section id="income" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-arrow-up text-green-600 mr-2"></i>Income</h2>
            <p class="text-gray-700 leading-7 mb-3">Any money in that isn't a Sale - investment, loan received, or other miscellaneous income.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Title, Category, Amount, Income Date, Payment Method, Reference No., Source (Sale/Investment/Loan/Other), Receipt attachment, Notes.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> Cash/Bank / <span class="text-green-600">Cr</span> the category's mapped income account (default: Other Income, 4020) - posted automatically the moment it's saved.</p>
            </div>
        </section>

        <section id="expenses" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-arrow-down text-red-600 mr-2"></i>Expenses</h2>
            <p class="text-gray-700 leading-7 mb-3">Every business cost that isn't a Purchase - rent, utilities, salaries paid outside Payroll, fuel, office supplies, etc.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">Title, Category, Amount, Expense Date, Payment Method, Reference No., Receipt attachment, Notes.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Status flow</p>
            <p class="text-gray-600 text-sm mb-3"><code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Pending</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Approved</code> &rarr; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Paid</code>, or <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Cancelled</code>.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> General Expenses (5030) / <span class="text-green-600">Cr</span> Cash or Bank Account.</p>
            </div>
        </section>

        <section id="money-transfers" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-exchange-alt text-blue-600 mr-2"></i>Money Transfers</h2>
            <p class="text-gray-700 leading-7 mb-3">Moving money between the business's own accounts - e.g. depositing cash into the bank, or moving funds between two bank accounts. This is <strong>not</strong> for paying suppliers, customers, or expenses - those each have their own module.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">Fields</p>
            <p class="text-gray-600 text-sm mb-3">From Account, To Account, Amount, Transfer Date, Reference No., Description.</p>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-700"><strong>Accounting impact:</strong> <span class="text-red-600">Dr</span> To Account / <span class="text-green-600">Cr</span> From Account - a same-side (Asset-to-Asset) movement, so it never touches Revenue or Expense.</p>
            </div>
        </section>

        <!-- ===================== ACCOUNTING ===================== -->

        <section id="chart-of-accounts" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-book text-cyan-500 mr-2"></i>Chart of Accounts & Ledger</h2>
            <p class="text-gray-700 leading-7 mb-3">The complete list of accounts every module posts to, organized by numeric block:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-gray-700 mb-3">
                <p><strong>1000s Assets</strong> - Cash (1010), Bank (1020), Inventory Stock (1030), Accounts Receivable (1040)</p>
                <p><strong>2000s Liabilities</strong> - Accounts Payable (2010), Salary Payable (2030)</p>
                <p><strong>3000s Equity</strong> - Owner's Capital (3010), Opening Balance Equity (3020)</p>
                <p><strong>4000s Revenue</strong> - Sales Revenue (4010), Other Income (4020)</p>
                <p><strong>5000s Expense</strong> - Purchase Cost / COGS (5010), General Expenses (5030), Inventory Shrinkage (5040), Salary Expense (5050)</p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Stick to the numeric blocks above when adding a new account (e.g. a new bank account belongs in the 1000s) - it keeps reports grouped sensibly.</li>
                <li>Click into any account to see its full Ledger - every Journal Entry ever posted to it, in date order, with a running balance.</li>
                <li>Deactivating an account hides it from new transactions but keeps its history intact - never delete an account that has any Journal Entries.</li>
            </ol>
        </section>

        <section id="bank-reconciliation" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-university text-cyan-500 mr-2"></i>Bank Reconciliation</h2>
            <p class="text-gray-700 leading-7 mb-3">Matches this system's balance for a Cash/Bank account against the real bank statement for a given date, line by line.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Start a new reconciliation for the account and statement date, enter the statement's closing balance.</li>
                <li>Add each statement line (deposit, withdrawal, transfer, fee, interest, other) and mark it Cleared once it matches a transaction already in the system.</li>
                <li>Any difference left over after matching everything is the number to investigate before marking the reconciliation Reconciled.</li>
            </ol>
        </section>

        <section id="reconcile-all" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-magic text-cyan-500 mr-2"></i>Reconcile All Accounts</h2>
            <p class="text-gray-700 leading-7 mb-3">An admin-only Dashboard button that scans <em>every</em> transaction type in the system (purchases, sales, returns, payments, payroll, adjustments...) and checks that each one's ledger entries actually exist, are balanced, and match its own stored amount.</p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                <p class="text-blue-900 text-sm leading-6">
                    This is the tool to reach for whenever a balance looks wrong and you can't tell why - it doesn't
                    just report problems, it can also repair missing or broken ledger entries directly (each finding
                    shows exactly what it will fix before you confirm).
                </p>
            </div>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <p class="text-gray-700 text-sm leading-6">Run it periodically (e.g. monthly, or after any bulk data work) as a health check, not only when something already looks broken - it's the fastest way to catch a data problem before it compounds across months of reports.</p>
        </section>

        <!-- ===================== REPORTS ===================== -->

        <section id="reports" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-chart-bar text-gray-600 mr-2"></i>Reports Overview</h2>
            <p class="text-gray-700 leading-7 mb-3">Every report reads live from the ledger and transaction tables - there is nothing to "generate" or "close" first, figures are always current as of the moment you open the page.</p>
            <ul class="list-disc pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li><strong>Profit &amp; Loss</strong> - Revenue minus COGS minus Expenses over a date range.</li>
                <li><strong>Trial Balance</strong> - every account's total debits/credits, to confirm the books balance.</li>
                <li><strong>Day Book</strong> - every Journal Entry for a single day, the rawest possible view of what happened.</li>
                <li><strong>Receivable / Payable</strong> - who owes the business, and who the business owes, right now.</li>
                <li><strong>Customers / Suppliers</strong> - performance and balance summaries per party.</li>
                <li><strong>Expenses / Income</strong> - breakdowns by category and date range.</li>
                <li><strong>Daily Summary</strong> - a single day's sales, purchases, expenses and cash movement at a glance.</li>
                <li><strong>Tax Report</strong> - tax collected/paid across sales and purchases.</li>
            </ul>
        </section>

        <!-- ===================== SETTINGS ===================== -->

        <section id="settings" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-sliders-h text-gray-500 mr-2"></i>General & Business Settings</h2>
            <p class="text-gray-700 leading-7 mb-3">Admin-only. Company identity and system-wide behavior:</p>
            <ul class="list-disc pl-6 space-y-1 text-gray-700 text-sm leading-6 mb-3">
                <li><strong>General</strong> - business name, logo, favicon, currency code/symbol, timezone, date format, theme color, dark mode.</li>
                <li><strong>Customer Groups</strong> - pricing/segmentation groups for customers.</li>
                <li><strong>Locations</strong> and <strong>POS Settings</strong> - see <a href="#pos" class="text-blue-600 hover:underline">POS Setup</a>.</li>
                <li><strong>Credit</strong> - the credit-hold/credit-limit policy for blocking new credit sales.</li>
            </ul>
            <p class="text-gray-700 text-sm leading-6">Changing Currency or Timezone here affects how every figure and date displays app-wide - double-check before saving, ideally outside business hours.</p>
        </section>

        <section id="users-permissions" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-shield-alt text-gray-500 mr-2"></i>Users & Permissions</h2>
            <p class="text-gray-700 leading-7 mb-3">Admin-only. Create Manager/Accountant logins under Settings &rarr; Users, then control exactly what each role can see and do under Settings &rarr; Permissions.</p>
            <p class="font-semibold text-gray-800 text-sm mb-1">SOP</p>
            <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm leading-6">
                <li>Create the login first, then immediately review its module permissions - a brand-new Manager/Accountant starts with whatever the default matrix grants, not a blank slate.</li>
                <li>Keep <strong>Payroll</strong> and <strong>Employees</strong> access separate and deliberate - salary data is sensitive; grant Payroll only to whoever actually processes it.</li>
                <li>Deactivate a departing staff member's login rather than deleting it, so their name still displays correctly on old records they created or approved.</li>
            </ol>
        </section>

        <!-- ===================== SYSTEM TOOLS ===================== -->

        <section id="activity-logs" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-history text-gray-600 mr-2"></i>Activity Logs</h2>
            <p class="text-gray-700 leading-7">An automatic audit trail of who created, updated, deleted, approved, or fixed what, and when - every meaningful action in the system logs itself here with no extra effort. Use it to answer "who changed this?" questions, or to review what a Reconcile All Accounts run actually fixed.</p>
        </section>

        <section id="backups" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-cloud-upload-alt text-gray-600 mr-2"></i>Backup & Restore</h2>
            <p class="text-gray-700 leading-7 mb-3">Full database backups you can create, download, and restore from, all from the admin panel.</p>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-900 text-sm leading-6">
                    <strong>Restoring a backup replaces all current data</strong> with the backup's data - it requires
                    re-confirming your password precisely because it's destructive to anything entered since that
                    backup was taken. Take a fresh backup before restoring an old one, and only restore when you're
                    certain it's the right call.
                </p>
            </div>
        </section>

        <section id="exports" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-file-export text-gray-600 mr-2"></i>Exports</h2>
            <p class="text-gray-700 leading-7">Sales, Purchases, Customers, Suppliers, Expenses, Income, and Receivable data can each be exported to CSV, Excel, or PDF from their respective list pages - useful for sharing with an accountant or importing into another tool, without giving that person system access.</p>
        </section>

        <!-- ===================== GOLDEN RULES ===================== -->

        <section id="golden-rules" class="bg-white rounded-xl shadow-card p-6 border-2 border-yellow-200">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-star text-yellow-500 mr-2"></i>Data Entry Golden Rules</h2>
            <p class="text-gray-700 leading-7 mb-3">A short list every user of this system should keep in mind, pulled together from the module SOPs above:</p>
            <ol class="list-decimal pl-6 space-y-2 text-gray-700 text-sm leading-6">
                <li><strong>Enter transactions when they happen, with real dates.</strong> Backdating or batching entries days later breaks Day Book and any report that's date-sensitive.</li>
                <li><strong>Status changes are not payments.</strong> Marking a Sale/Purchase "Paid" does nothing to the ledger by itself - always record the actual payment.</li>
                <li><strong>Fix mistakes at the source, or with a proper reversal</strong> (Return, cancelled payment, Stock Adjustment for a genuine physical count) - never by hand-editing a total or a Journal Entry.</li>
                <li><strong>Never delete a record that has financial history</strong> - deactivate/soft-delete instead, so old invoices, payslips, and reports keep displaying correctly.</li>
                <li><strong>Opening balances are one-time and immediate</strong> - get Suppliers'/Customers' opening balances right the moment you create them, since they post to the ledger straight away.</li>
                <li><strong>When a balance looks wrong, run <a href="#reconcile-all" class="text-blue-600 hover:underline">Reconcile All Accounts</a> before assuming it's a bug</strong> - most "the numbers don't match" situations are a missed transaction or a skipped payment step, and the tool finds those directly.</li>
                <li><strong>Grant only the permissions a role actually needs</strong>, especially for Payroll and Settings - review Settings &rarr; Permissions whenever someone's responsibilities change.</li>
            </ol>
        </section>

    </div>
</div>
@endsection
