<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SalesReturnController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\IncomeCategoryController;
use App\Http\Controllers\Admin\MoneyTransferController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\BankReconciliationController;
use App\Http\Controllers\Admin\AccountReconciliationController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ApiSystemController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PosSettingController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\StaffUserController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\MyLeaveController;
use App\Http\Controllers\Admin\SalaryComponentController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\MyPayslipController;
use App\Http\Controllers\Admin\GuideController;
use App\Http\Controllers\Admin\QuickSearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES
// ============================================

// Web setup wizard - see routes/install.php. Locked shut by the
// not.installed middleware once storage/installed exists (i.e. on every
// real install after the first), so this require is always safe.
require __DIR__ . '/install.php';

Route::get('/', function () {
    if (!\Illuminate\Support\Facades\File::exists(storage_path('installed'))) {
        return redirect()->route('install.index');
    }
    return redirect()->route('login');
});

// Auth routes (Laravel Breeze)
require __DIR__ . '/auth.php';

// ============================================
// ADMIN ROUTES - Protected with Auth
// ============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,manager,accountant,pos_manager', 'log.activity', 'license'])->group(function () {

    // ==========================================
    // 1. DASHBOARD
    // ==========================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard,view')->name('dashboard');

    // License management - reachable regardless of lock state (see the
    // `license` middleware / EnsureLicensed), so a locked-out admin can
    // always fix it. Viewing status needs no role check; activating/
    // deactivating/rechecking is gated to role=admin inside the controller.
    Route::prefix('license')->name('license.')->group(function () {
        Route::get('/', [LicenseController::class, 'index'])->name('index');
        Route::post('/activate', [LicenseController::class, 'activate'])->name('activate');
        Route::post('/recheck', [LicenseController::class, 'recheck'])->name('recheck');
        Route::post('/deactivate', [LicenseController::class, 'deactivate'])->name('deactivate');
        Route::post('/purchase-request', [LicenseController::class, 'purchaseRequest'])
            ->middleware('throttle:5,1')
            ->name('purchase-request');
    });

    // Ctrl/Cmd+K quick search palette - no permission: middleware here since
    // it aggregates several modules at once; QuickSearchController filters
    // each group internally by hasPermission() instead.
    Route::get('/quick-search', [QuickSearchController::class, 'search'])->name('quick-search');

    // ==========================================
    // 2. ACCOUNTS MANAGEMENT
    // ==========================================
    Route::prefix('accounts')->name('accounts.')->middleware('permission:accounts,view')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/create', [AccountController::class, 'create'])->middleware('permission:accounts,create')->name('create');
        Route::post('/', [AccountController::class, 'store'])->middleware('permission:accounts,create')->name('store');
        Route::get('/{account}', [AccountController::class, 'show'])->name('show');
        Route::get('/{account}/edit', [AccountController::class, 'edit'])->middleware('permission:accounts,edit')->name('edit');
        Route::put('/{account}', [AccountController::class, 'update'])->middleware('permission:accounts,edit')->name('update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->middleware('permission:accounts,delete')->name('destroy');
        Route::post('/{account}/toggle-status', [AccountController::class, 'toggleStatus'])->middleware('permission:accounts,edit')->name('toggle-status');
    });

    // ==========================================
    // 3. CATEGORIES MANAGEMENT
    // ==========================================
    Route::prefix('categories')->name('categories.')->middleware('permission:categories,view')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->middleware('permission:categories,create')->name('create');
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:categories,create')->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->middleware('permission:categories,edit')->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('permission:categories,edit')->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories,delete')->name('destroy');
        Route::post('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->middleware('permission:categories,edit')->name('toggle-status');
    });

    // ==========================================
    // 4. PRODUCTS MANAGEMENT
    // ==========================================
    Route::prefix('products')->name('products.')->middleware('permission:products,view')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->middleware('permission:products,create')->name('create');
        Route::post('/', [ProductController::class, 'store'])->middleware('permission:products,create')->name('store');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('low-stock');
        Route::prefix('barcode')->name('barcode.')->group(function () {
            Route::get('/', [BarcodeController::class, 'index'])->name('index');
            Route::post('/print', [BarcodeController::class, 'print'])->name('print');
        });
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->middleware('permission:products,edit')->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware('permission:products,edit')->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware('permission:products,delete')->name('destroy');
        Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->middleware('permission:products,edit')->name('toggle-status');
    });

    // Product variant attributes (Size, Color, ...) - reusable across
    // products, managed here or quick-added inline from the product form.
    Route::prefix('product-attributes')->name('product-attributes.')->middleware('permission:products,view')->group(function () {
        Route::get('/', [ProductAttributeController::class, 'index'])->name('index');
        Route::get('/data', [ProductAttributeController::class, 'data'])->name('data');
        Route::post('/', [ProductAttributeController::class, 'store'])->middleware('permission:products,create')->name('store');
        Route::post('/{productAttribute}/values', [ProductAttributeController::class, 'storeValue'])->middleware('permission:products,create')->name('values.store');
    });

    // ==========================================
    // 5. SUPPLIERS MANAGEMENT
    // ==========================================
    Route::prefix('suppliers')->name('suppliers.')->middleware('permission:suppliers,view')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->middleware('permission:suppliers,create')->name('create');
        Route::post('/', [SupplierController::class, 'store'])->middleware('permission:suppliers,create')->name('store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('permission:suppliers,edit')->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers,edit')->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers,delete')->name('destroy');
        Route::post('/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->middleware('permission:suppliers,edit')->name('toggle-status');
        Route::post('/{supplier}/payments', [SupplierController::class, 'makePayment'])->middleware('permission:suppliers,edit')->name('payments.store');
        Route::put('/{supplier}/payments/{payment}', [SupplierController::class, 'updatePayment'])->middleware('permission:suppliers,edit')->name('payments.update');
        Route::delete('/{supplier}/payments/{payment}', [SupplierController::class, 'deletePayment'])->middleware('permission:suppliers,delete')->name('payments.destroy');
    });

    // ==========================================
    // 6. PURCHASES MANAGEMENT
    // ==========================================
    Route::prefix('purchases')->name('purchases.')->middleware('permission:purchases,view')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->middleware('permission:purchases,create')->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->middleware('permission:purchases,create')->name('store');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->middleware('permission:purchases,edit')->name('edit');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->middleware('permission:purchases,edit')->name('update');
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->middleware('permission:purchases,delete')->name('destroy');
        Route::post('/{purchase}/add-payment', [PurchaseController::class, 'addPayment'])->middleware('permission:purchases,edit')->name('add-payment');
        Route::get('/{purchase}/return', [PurchaseController::class, 'returnCreate'])->name('return-create');
    });

    // ==========================================
    // 7. PURCHASE RETURNS
    // ==========================================
    Route::prefix('purchase-returns')->name('purchase-returns.')->middleware('permission:purchase-returns,view')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseReturnController::class, 'create'])->middleware('permission:purchase-returns,create')->name('create');
        Route::post('/', [PurchaseReturnController::class, 'store'])->middleware('permission:purchase-returns,create')->name('store');
        Route::get('/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('show');
        Route::delete('/{purchaseReturn}', [PurchaseReturnController::class, 'destroy'])->middleware('permission:purchase-returns,delete')->name('destroy');
        Route::get('/get-purchase-details/{purchaseId}', [PurchaseReturnController::class, 'getPurchaseDetails'])->name('get-purchase-details');
    });

    // ==========================================
    // 8. INVENTORY MANAGEMENT
    // ==========================================
    Route::prefix('inventory')->name('inventory.')->middleware('permission:inventory,view')->group(function () {
        Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [InventoryController::class, 'history'])->name('history');

        Route::prefix('adjustments')->name('adjustments.')->group(function () {
            Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
            Route::get('/create', [StockAdjustmentController::class, 'create'])->middleware('permission:inventory,create')->name('create');
            Route::post('/', [StockAdjustmentController::class, 'store'])->middleware('permission:inventory,create')->name('store');
            Route::delete('/{adjustment}', [StockAdjustmentController::class, 'destroy'])->middleware('permission:inventory,delete')->name('destroy');
        });
    });

    // ==========================================
    // 9. CUSTOMER MANAGEMENT
    // ==========================================
    Route::prefix('customers')->name('customers.')->middleware('permission:customers,view')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->middleware('permission:customers,create')->name('create');
        Route::post('/', [CustomerController::class, 'store'])->middleware('permission:customers,create')->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:customers,edit')->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers,edit')->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers,delete')->name('destroy');
        Route::post('/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->middleware('permission:customers,edit')->name('toggle-status');
        Route::post('/{customer}/payments', [CustomerController::class, 'makePayment'])->middleware('permission:customers,edit')->name('payments.store');
        Route::put('/{customer}/payments/{payment}', [CustomerController::class, 'updatePayment'])->middleware('permission:customers,edit')->name('payments.update');
        Route::delete('/{customer}/payments/{payment}', [CustomerController::class, 'deletePayment'])->middleware('permission:customers,delete')->name('payments.destroy');
    });

    // ==========================================
    // 10b. HR - EMPLOYEES (Admin) - Leave/Payroll land here in later
    // phases; this is Phase 1 (employee records only).
    // ==========================================
    Route::prefix('employees')->name('employees.')->middleware('permission:employees,view')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->middleware('permission:employees,create')->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->middleware('permission:employees,create')->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:employees,edit')->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees,edit')->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees,delete')->name('destroy');
        // role:admin, not permission:employees,edit - granting a login (with
        // a choice of admin/manager/accountant role) is exactly as sensitive
        // as creating one via Settings -> Users, which is hard admin-only.
        // The default seeded permissions give Manager employees:edit=true,
        // which would otherwise let a Manager mint themselves/anyone a new
        // admin-role login straight from HR.
        Route::post('/{employee}/grant-access', [EmployeeController::class, 'grantAccess'])->middleware('role:admin')->name('grant-access');
    });

    Route::prefix('departments')->name('departments.')->middleware('permission:employees,view')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::post('/', [DepartmentController::class, 'store'])->middleware('permission:employees,create')->name('store');
        Route::put('/{department}', [DepartmentController::class, 'update'])->middleware('permission:employees,edit')->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:employees,delete')->name('destroy');
    });

    // ==========================================
    // 10c. HR - LEAVE (Admin) - Phase 2. Payroll (Phase 3) not built yet.
    // ==========================================
    Route::prefix('leave-types')->name('leave-types.')->middleware('permission:leaves,view')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::post('/', [LeaveTypeController::class, 'store'])->middleware('permission:leaves,create')->name('store');
        Route::put('/{leaveType}', [LeaveTypeController::class, 'update'])->middleware('permission:leaves,edit')->name('update');
        Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])->middleware('permission:leaves,delete')->name('destroy');
    });

    Route::prefix('leave-requests')->name('leave-requests.')->middleware('permission:leaves,view')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->middleware('permission:leaves,edit')->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->middleware('permission:leaves,edit')->name('reject');
    });

    // Self-service - intentionally not permission-gated, only ever touches
    // the logged-in user's own Employee record.
    Route::prefix('my-leave')->name('my-leave.')->group(function () {
        Route::get('/', [MyLeaveController::class, 'index'])->name('index');
        Route::post('/', [MyLeaveController::class, 'store'])->name('store');
        Route::post('/{leaveRequest}/cancel', [MyLeaveController::class, 'cancel'])->name('cancel');
    });

    // ==========================================
    // 10d. HR - PAYROLL (Admin) - Phase 3
    // ==========================================
    Route::prefix('salary-components')->name('salary-components.')->middleware('permission:payroll,view')->group(function () {
        Route::get('/', [SalaryComponentController::class, 'index'])->name('index');
        Route::get('/{employee}', [SalaryComponentController::class, 'show'])->name('show');
        Route::post('/{employee}', [SalaryComponentController::class, 'store'])->middleware('permission:payroll,create')->name('store');
    });

    Route::prefix('payroll-runs')->name('payroll-runs.')->middleware('permission:payroll,view')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/create', [PayrollController::class, 'create'])->middleware('permission:payroll,create')->name('create');
        Route::post('/', [PayrollController::class, 'store'])->middleware('permission:payroll,create')->name('store');
        Route::get('/{payrollRun}', [PayrollController::class, 'show'])->name('show');
        Route::post('/payslips/{payslip}/pay', [PayrollController::class, 'payPayslip'])->middleware('permission:payroll,edit')->name('payslips.pay');
    });

    // Self-service - not permission-gated, only ever shows the logged-in
    // user's own payslips.
    Route::prefix('my-payslips')->name('my-payslips.')->group(function () {
        Route::get('/', [MyPayslipController::class, 'index'])->name('index');
    });

    // ==========================================
    // 11. SALES MANAGEMENT (Admin)
    // ==========================================
    Route::prefix('sales')->name('sales.')->middleware('permission:sales,view')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->middleware('permission:sales,create')->name('create');
        Route::get('/pos', [SaleController::class, 'pos'])->middleware('permission:sales,create')->name('pos');
        Route::post('/', [SaleController::class, 'store'])->middleware('permission:sales,create')->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/receipt', [SaleController::class, 'receipt'])->name('receipt');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->middleware('permission:sales,edit')->name('edit');
        Route::put('/{sale}', [SaleController::class, 'update'])->middleware('permission:sales,edit')->name('update');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->middleware('permission:sales,delete')->name('destroy');
        Route::post('/{sale}/add-payment', [SaleController::class, 'addPayment'])->middleware('permission:sales,edit')->name('add-payment');
        // Confirms/rejects a still-draft sale - how a customer-placed order
        // (source=customer_app), which only an admin can act on, becomes a
        // real, ledger-posted sale.
        Route::post('/{sale}/confirm', [SaleController::class, 'confirm'])->middleware('permission:sales,edit')->name('confirm');
        Route::post('/{sale}/reject', [SaleController::class, 'reject'])->middleware('permission:sales,edit')->name('reject');
    });

    // ==========================================
    // 12. SALES RETURNS (Admin)
    // ==========================================
    Route::prefix('sales-returns')->name('sales-returns.')->middleware('permission:sales-returns,view')->group(function () {
        Route::get('/', [SalesReturnController::class, 'index'])->name('index');
        Route::get('/create', [SalesReturnController::class, 'create'])->middleware('permission:sales-returns,create')->name('create');
        Route::post('/', [SalesReturnController::class, 'store'])->middleware('permission:sales-returns,create')->name('store');
        Route::get('/{salesReturn}', [SalesReturnController::class, 'show'])->name('show');
        Route::delete('/{salesReturn}', [SalesReturnController::class, 'destroy'])->middleware('permission:sales-returns,delete')->name('destroy');
        Route::get('/get-sale-details/{saleId}', [SalesReturnController::class, 'getSaleDetails'])->name('get-sale-details');
    });

    // ==========================================
    // 13. ADMIN PROFILE
    // ==========================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ==========================================
    // 14. EXPENSES MANAGEMENT
    // ==========================================
    Route::prefix('expenses')->name('expenses.')->middleware('permission:expenses,view')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->middleware('permission:expenses,create')->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->middleware('permission:expenses,create')->name('store');
        Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('permission:expenses,edit')->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses,edit')->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses,delete')->name('destroy');
        Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->middleware('permission:expenses,edit')->name('approve');
        Route::post('/{expense}/mark-paid', [ExpenseController::class, 'markAsPaid'])->middleware('permission:expenses,edit')->name('mark-paid');
        Route::post('/{expense}/cancel', [ExpenseController::class, 'cancel'])->middleware('permission:expenses,edit')->name('cancel');
    });

    // Expense Categories - folded into the 'expenses' permission module,
    // same as the route prefix being a sub-resource of expense management.
    Route::prefix('expense-categories')->name('expense-categories.')->middleware('permission:expenses,view')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index');
        Route::post('/', [ExpenseCategoryController::class, 'store'])->middleware('permission:expenses,create')->name('store');
        Route::put('/{category}', [ExpenseCategoryController::class, 'update'])->middleware('permission:expenses,edit')->name('update');
        Route::delete('/{category}', [ExpenseCategoryController::class, 'destroy'])->middleware('permission:expenses,delete')->name('destroy');
    });

    // ==========================================
    // 15. INCOME MANAGEMENT
    // ==========================================
    Route::prefix('incomes')->name('incomes.')->middleware('permission:incomes,view')->group(function () {
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        Route::get('/create', [IncomeController::class, 'create'])->middleware('permission:incomes,create')->name('create');
        Route::post('/', [IncomeController::class, 'store'])->middleware('permission:incomes,create')->name('store');
        Route::get('/{income}', [IncomeController::class, 'show'])->name('show');
        Route::get('/{income}/edit', [IncomeController::class, 'edit'])->middleware('permission:incomes,edit')->name('edit');
        Route::put('/{income}', [IncomeController::class, 'update'])->middleware('permission:incomes,edit')->name('update');
        Route::delete('/{income}', [IncomeController::class, 'destroy'])->middleware('permission:incomes,delete')->name('destroy');
    });

    // Income Categories - folded into the 'incomes' permission module.
    Route::prefix('income-categories')->name('income-categories.')->middleware('permission:incomes,view')->group(function () {
        Route::get('/', [IncomeCategoryController::class, 'index'])->name('index');
        Route::post('/', [IncomeCategoryController::class, 'store'])->middleware('permission:incomes,create')->name('store');
        Route::put('/{category}', [IncomeCategoryController::class, 'update'])->middleware('permission:incomes,edit')->name('update');
        Route::delete('/{category}', [IncomeCategoryController::class, 'destroy'])->middleware('permission:incomes,delete')->name('destroy');
    });

    // ==========================================
    // 16. MONEY TRANSFER MANAGEMENT
    // ==========================================
    Route::prefix('money-transfers')->name('money-transfers.')->middleware('permission:money-transfers,view')->group(function () {
        Route::get('/', [MoneyTransferController::class, 'index'])->name('index');
        Route::get('/create', [MoneyTransferController::class, 'create'])->middleware('permission:money-transfers,create')->name('create');
        Route::post('/', [MoneyTransferController::class, 'store'])->middleware('permission:money-transfers,create')->name('store');
        // Bound as {moneyTransfer}, not {transfer} - every controller method
        // below type-hints `MoneyTransfer $moneyTransfer`, and implicit
        // route-model-binding only works when the URI segment name matches
        // the controller's parameter name exactly. With the mismatched
        // {transfer} this used to have, Laravel silently fell through to
        // constructing a brand new, empty MoneyTransfer (id=null, no
        // attributes) instead of fetching the real record or 404ing -
        // confirmed live: show() rendered with a blank model and 500'd on
        // ->transfer_date->format(), and edit/update/destroy/complete/
        // cancel would all have silently operated on that same empty
        // instance instead of the real one.
        Route::get('/{moneyTransfer}', [MoneyTransferController::class, 'show'])->name('show');
        Route::get('/{moneyTransfer}/edit', [MoneyTransferController::class, 'edit'])->middleware('permission:money-transfers,edit')->name('edit');
        Route::put('/{moneyTransfer}', [MoneyTransferController::class, 'update'])->middleware('permission:money-transfers,edit')->name('update');
        Route::delete('/{moneyTransfer}', [MoneyTransferController::class, 'destroy'])->middleware('permission:money-transfers,delete')->name('destroy');
        Route::post('/{moneyTransfer}/complete', [MoneyTransferController::class, 'complete'])->middleware('permission:money-transfers,edit')->name('complete');
        Route::post('/{moneyTransfer}/cancel', [MoneyTransferController::class, 'cancel'])->middleware('permission:money-transfers,edit')->name('cancel');
    });

    // ==========================================
    // 17. BUSINESS REPORTS
    // ==========================================
    Route::prefix('reports')->name('reports.')->middleware('permission:reports,view')->group(function () {
        // Financial Reports
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/pdf', [ReportController::class, 'profitLossPdf'])->name('profit-loss-pdf');
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/trial-balance/pdf', [ReportController::class, 'trialBalancePdf'])->name('trial-balance-pdf');

        // Customer Reports
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/customer/{customer}', [ReportController::class, 'customerDetail'])->name('customer-detail');
        Route::get('/customer-ledger/{customer}', [ReportController::class, 'customerLedger'])->name('customer-ledger');
        Route::get('/customer-ledger/{customer}/pdf', [ReportController::class, 'customerLedgerPdf'])->name('customer-ledger-pdf');

        // Supplier Reports
        Route::get('/suppliers', [ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('/supplier/{supplier}', [ReportController::class, 'supplierDetail'])->name('supplier-detail');
        Route::get('/supplier-ledger/{supplier}', [ReportController::class, 'supplierLedger'])->name('supplier-ledger');
        Route::get('/supplier-ledger/{supplier}/pdf', [ReportController::class, 'supplierLedgerPdf'])->name('supplier-ledger-pdf');

        // Receivable / Payable Reports
        Route::get('/receivable', [ReportController::class, 'receivable'])->name('receivable');
        Route::get('/receivable/pdf', [ReportController::class, 'receivablePdf'])->name('receivable-pdf');
        Route::get('/payable', [ReportController::class, 'payable'])->name('payable');
        Route::get('/payable/pdf', [ReportController::class, 'payablePdf'])->name('payable-pdf');

        // Account Ledger / Cash Book / Bank Book (same report, any account)
        Route::get('/account-ledger/{account}', [ReportController::class, 'accountLedger'])->name('account-ledger');
        Route::get('/account-ledger/{account}/pdf', [ReportController::class, 'accountLedgerPdf'])->name('account-ledger-pdf');

        // Day Book (General Journal)
        Route::get('/day-book', [ReportController::class, 'dayBook'])->name('day-book');
        Route::get('/day-book/pdf', [ReportController::class, 'dayBookPdf'])->name('day-book-pdf');

        // Expense & Income Reports
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/incomes', [ReportController::class, 'incomes'])->name('incomes');

        // Daily Summary
        Route::get('/daily-summary', [ReportController::class, 'dailySummary'])->name('daily-summary');

        // Tax Report
        Route::get('/tax', [ReportController::class, 'taxReport'])->name('tax');
        Route::get('/tax/pdf', [ReportController::class, 'taxReportPdf'])->name('tax-pdf');
    });

    // ==========================================
    // 18. BANK RECONCILIATION
    // ==========================================
    Route::prefix('bank-reconciliations')->name('bank-reconciliations.')->middleware('permission:bank-reconciliations,view')->group(function () {
        Route::get('/', [BankReconciliationController::class, 'index'])->name('index');
        Route::get('/create', [BankReconciliationController::class, 'create'])->middleware('permission:bank-reconciliations,create')->name('create');
        Route::post('/', [BankReconciliationController::class, 'store'])->middleware('permission:bank-reconciliations,create')->name('store');
        Route::get('/{bankReconciliation}', [BankReconciliationController::class, 'show'])->name('show');
        Route::get('/{bankReconciliation}/edit', [BankReconciliationController::class, 'edit'])->middleware('permission:bank-reconciliations,edit')->name('edit');
        Route::put('/{bankReconciliation}', [BankReconciliationController::class, 'update'])->middleware('permission:bank-reconciliations,edit')->name('update');
        Route::delete('/{bankReconciliation}', [BankReconciliationController::class, 'destroy'])->middleware('permission:bank-reconciliations,delete')->name('destroy');
        Route::post('/{bankReconciliation}/reconcile', [BankReconciliationController::class, 'reconcile'])->middleware('permission:bank-reconciliations,edit')->name('reconcile');
    });

    // ==========================================
    // 19. EXPORTS
    // ==========================================
    Route::prefix('exports')->name('exports.')->middleware('permission:exports,view')->group(function () {
        Route::get('/csv', [ExportController::class, 'exportCSV'])->name('csv');
        Route::get('/excel', [ExportController::class, 'exportExcel'])->name('excel');
        Route::get('/pdf', [ExportController::class, 'exportPDF'])->name('pdf');
    });

    // ==========================================
    // 20. ACTIVITY LOGS
    // ==========================================
    Route::prefix('activity-logs')->name('activity-logs.')->middleware('permission:activity-logs,view')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
        Route::post('/clear', [ActivityLogController::class, 'clear'])->middleware('permission:activity-logs,delete')->name('clear');
        Route::post('/clear-all', [ActivityLogController::class, 'clearAll'])->middleware('permission:activity-logs,delete')->name('clear-all');
        Route::get('/export/{format}', [ActivityLogController::class, 'export'])->name('export');
    });

    // ==========================================
    // 21. BACKUP & RESTORE
    // ==========================================
    Route::prefix('backups')->name('backups.')->middleware('permission:backups,view')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->middleware('permission:backups,create')->name('create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::post('/restore/{filename}', [BackupController::class, 'restore'])
            ->middleware(['password.confirm', 'permission:backups,delete'])->name('restore');
        Route::delete('/delete-all', [BackupController::class, 'deleteAll'])->middleware('permission:backups,delete')->name('delete-all');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->middleware('permission:backups,delete')->name('destroy');
    });

    // ==========================================
    // 21b. API - Docs & Tester (admin-only: the tester can perform real
    // create/update/delete calls against live data)
    // ==========================================
    Route::prefix('system/api')->name('system.api.')->middleware('role:admin')->group(function () {
        Route::get('/docs', [ApiSystemController::class, 'documentation'])->name('docs');
        Route::get('/tester', [ApiSystemController::class, 'tester'])->name('tester');
    });

    // Software Management Guide Book - the full module-by-module SOP
    // manual. Not admin-only: the whole point is for manager/accountant
    // users to be able to read it too.
    Route::get('/guide-book', [GuideController::class, 'index'])->name('guide.index');

    // ==========================================
    // 23. SETTINGS (admin-only)
    // ==========================================
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [SettingsController::class, 'general'])->name('index');

        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');

        Route::get('/credit', [SettingsController::class, 'credit'])->name('credit');
        Route::post('/credit', [SettingsController::class, 'updateCredit'])->name('credit.update');

        Route::prefix('customer-groups')->name('customer-groups.')->group(function () {
            Route::get('/', [CustomerGroupController::class, 'index'])->name('index');
            Route::get('/create', [CustomerGroupController::class, 'create'])->name('create');
            Route::post('/', [CustomerGroupController::class, 'store'])->name('store');
            Route::get('/{customerGroup}/edit', [CustomerGroupController::class, 'edit'])->name('edit');
            Route::put('/{customerGroup}', [CustomerGroupController::class, 'update'])->name('update');
            Route::delete('/{customerGroup}', [CustomerGroupController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/', [LocationController::class, 'index'])->name('index');
            Route::get('/create', [LocationController::class, 'create'])->name('create');
            Route::post('/', [LocationController::class, 'store'])->name('store');
            Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('edit');
            Route::put('/{location}', [LocationController::class, 'update'])->name('update');
            Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [StaffUserController::class, 'index'])->name('index');
            Route::get('/create', [StaffUserController::class, 'create'])->name('create');
            Route::post('/', [StaffUserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [StaffUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [StaffUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [StaffUserController::class, 'destroy'])->name('destroy');
        });

        Route::get('/permissions', [RolePermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [RolePermissionController::class, 'update'])->name('permissions.update');

        Route::get('/pos', [PosSettingController::class, 'edit'])->name('pos.edit');
        Route::post('/pos', [PosSettingController::class, 'update'])->name('pos.update');
    });

    // ==========================================
    // 24. RECONCILE ALL ACCOUNTS (admin-only) - ledger integrity scan/fix
    // across every transaction type. Not the same as Bank Reconciliation
    // above (statement-vs-ledger matching for one account) - this checks
    // the journal entries themselves for missing/duplicate/orphaned/
    // unbalanced/mismatched rows. Deliberately not added to any sidebar
    // menu group (especially not "accounting", where Bank Reconciliation
    // lives) - reached only via the admin-only Dashboard quick-action card.
    // ==========================================
    Route::prefix('ledger-integrity')->name('ledger-integrity.')->middleware('role:admin')->group(function () {
        Route::get('/', [AccountReconciliationController::class, 'index'])->name('index');
        Route::post('/scan', [AccountReconciliationController::class, 'scan'])->name('scan');
        Route::post('/fix', [AccountReconciliationController::class, 'fix'])->name('fix');
    });
});

// ============================================
// FALLBACK ROUTES (Redirects)
// ============================================
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth']);
