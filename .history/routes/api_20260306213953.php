<?php

use App\Http\Controllers\Inventory\AdjustmentController;
use App\Http\Controllers\Inventory\ItemUOMController;
use App\Http\Controllers\Main\CompanyController;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Purchasing\SupPayController;
use App\Http\Controllers\Reporting\InventoryController;
use App\Http\Controllers\Reporting\OperationalController;
use App\Http\Controllers\Sales\AddoneController;
use App\Http\Controllers\Sales\ExpencesController;
use App\Http\Controllers\Sales\StewardController;
use App\Http\Controllers\Sync\SyncClientDataController;
use App\Http\Controllers\Sync\SyncMasterDataController;
use App\Models\default\TableMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemClassController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\KitchenController;
use App\Http\Controllers\Inventory\BOMController;
use App\Http\Controllers\Purchasing\VendorController;
use App\Http\Controllers\Purchasing\GRNController;
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Http\Controllers\Main\TableMasterController;
use App\Http\Controllers\Inventory\TransferController;
use App\Http\Controllers\Sales\KDSController;
use App\Http\Controllers\Reporting\SalesController;
use App\Http\Controllers\Reporting\PurchasingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [UserController::class, 'logout']);
});

Route::middleware('auth:sanctum')
    ->prefix('products')
    ->group(function () {
        Route::get('/', [ItemController::class, 'index']);
        Route::get('/default', [ItemController::class, 'getDefault']);
        Route::post('/', [ItemController::class, 'create']);
        Route::get('/{id}', [ItemController::class, 'getById']);
        Route::put('/{id}', [ItemController::class, 'update']);
        Route::delete('/{id}', [ItemController::class, 'destroy']);
    });

Route::middleware('auth:sanctum')
    ->prefix('categories')
    ->group(function () {
        Route::get('/', [ItemCategoryController::class, 'index']);
        Route::post('/', [ItemCategoryController::class, 'create']);
        Route::get('/{id}', [ItemCategoryController::class, 'show']);
        Route::put('/{id}', [ItemCategoryController::class, 'update']);
        Route::delete('/{id}', [ItemCategoryController::class, 'delete']);
    });



Route::middleware('auth:sanctum')
    ->prefix('class')
    ->group(function () {
        Route::get('/', [ItemClassController::class, 'index']);
        Route::post('/', [ItemClassController::class, 'create']);
        Route::get('/{id}', [ItemClassController::class, 'show']);
        Route::put('/{id}', [ItemClassController::class, 'update']);
        Route::delete('/{id}', [ItemClassController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('uom')
    ->group(function () {
        Route::get('/', [ItemUOMController::class, 'index']);
        Route::post('/', [ItemUOMController::class, 'create']);
        Route::get('/{id}', [ItemUOMController::class, 'show']);
        Route::put('/{id}', [ItemUOMController::class, 'update']);
        Route::delete('/{id}', [ItemUOMController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('table')
    ->group(function () {
        Route::get('/', [TableMasterController::class, 'index']);
        Route::post('/', [TableMasterController::class, 'create']);
        Route::get('/{id}', [TableMasterController::class, 'show']);
        Route::put('/{id}', [TableMasterController::class, 'update']);
        Route::delete('/{id}', [TableMasterController::class, 'delete']);
    });


Route::middleware('auth:sanctum')
    ->prefix('warehouse')
    ->group(function () {
        Route::get('/', [WarehouseController::class, 'index']);
        Route::post('/', [WarehouseController::class, 'create']);
        Route::get('/{id}', [WarehouseController::class, 'show']);
        Route::put('/{id}', [WarehouseController::class, 'update']);
        Route::delete('/{id}', [WarehouseController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('kitchen')
    ->group(function () {
        Route::get('/', [KitchenController::class, 'index']);
        Route::post('/', [KitchenController::class, 'create']);
        Route::get('/{id}', [KitchenController::class, 'show']);
        Route::put('/{id}', [KitchenController::class, 'update']);
        Route::delete('/{id}', [KitchenController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('customer')
    ->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'create']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('vendor')
    ->group(function () {
        Route::get('/', [VendorController::class, 'index']);
        Route::post('/', [VendorController::class, 'create']);
        Route::get('/{id}', [VendorController::class, 'show']);
        Route::put('/{id}', [VendorController::class, 'update']);
        Route::delete('/{id}', [VendorController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('grn')
    ->group(function () {
        Route::get('/', [GRNController::class, 'index']);
        Route::get('/default', [GRNController::class, 'getDefault']);
        Route::post('/', [GRNController::class, 'create']);
        Route::get('/{id}', [GRNController::class, 'getById']);
        Route::put('/{id}', [GRNController::class, 'update']);
        Route::delete('/{id}', [GRNController::class, 'destroy']);
    });

Route::middleware('auth:sanctum')
    ->prefix('bom')
    ->group(function () {
        Route::get('/', [BOMController::class, 'index']);
        Route::get('/default', [BOMController::class, 'getDefault']);
        Route::post('/', [BOMController::class, 'createOrUpdate']);
        Route::get('/{id}', [BOMController::class, 'getById']);
        Route::get('/{id}/{active}', [BOMController::class, 'setActive']);
        Route::delete('/{id}', [BOMController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('steward')
    ->group(function () {
        Route::get('/', [StewardController::class, 'index']);
        Route::get('/default', [StewardController::class, 'getDefault']);
        Route::post('/', [StewardController::class, 'create']);
        Route::get('/{id}', [StewardController::class, 'getById']);
        Route::put('/{id}', [StewardController::class, 'update']);
        Route::delete('/{id}', [StewardController::class, 'delete']);
    });


Route::middleware('auth:sanctum')
    ->prefix('invoice')
    ->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/invoice_data', [InvoiceController::class, 'getAllData']);
        Route::get('/get_tables', [InvoiceController::class, 'get_tables']);
        Route::post('/save_order', [InvoiceController::class, 'SaveOrder']);
        Route::get('/get_order/{id}', [InvoiceController::class, 'get_order']);
        Route::post('/void_order', [InvoiceController::class, 'VoidOrder']);

        Route::post('/SaveInvoice', [InvoiceController::class, 'SaveInvoice']);

        Route::post('/payments/{id}', [InvoiceController::class, 'saveInvoicePayments']);
        Route::get('/check_session', [InvoiceController::class, 'check_cashier_session']);

        Route::post('/saveCashierSession', [InvoiceController::class, 'saveCashierSession']);
        Route::post('/closeCashierSession', [InvoiceController::class, 'closeCashierSession']);
    });



Route::middleware('auth:sanctum')
    ->prefix('ajs')
    ->group(function () {
        Route::get('/', [AdjustmentController::class, 'index']);
        Route::get('/default', [AdjustmentController::class, 'getDefault']);
        Route::post('/', [AdjustmentController::class, 'create']);
        Route::get('/{id}', [AdjustmentController::class, 'getById']);
        Route::put('/{id}', [AdjustmentController::class, 'update']);
        Route::delete('/{id}', [AdjustmentController::class, 'destroy']);
    });


Route::middleware('auth:sanctum')
    ->prefix('trn')
    ->group(function () {
        Route::get('/', [TransferController::class, 'index']);
        Route::get('/default', [TransferController::class, 'getDefault']);
        Route::post('/', [TransferController::class, 'create']);
        Route::get('/{id}', [TransferController::class, 'getById']);
        Route::put('/{id}', [TransferController::class, 'update']);
        Route::delete('/{id}', [TransferController::class, 'destroy']);
    });


Route::middleware('auth:sanctum')
    ->prefix('pay')
    ->group(function () {
        Route::get('/', [SupPayController::class, 'index']);
        Route::get('/default', [SupPayController::class, 'getDefault']);
        Route::post('/', [SupPayController::class, 'create']);
        Route::get('/{id}', [SupPayController::class, 'getById']);
        Route::put('/{id}', [SupPayController::class, 'update']);
        Route::delete('/{id}', [SupPayController::class, 'destroy']);
        Route::get('void/{id}', [SupPayController::class, 'void']);
    });



Route::middleware('auth:sanctum')
    ->prefix('kds')
    ->group(function () {
        Route::get('/', [KdsController::class, 'getOrders']);
        Route::get('/default', [KdsController::class, 'getDefault']);
        Route::post('/update_order', [KdsController::class, 'updateStatus'])->withoutMiddleware('throttle:api');
        Route::get('/{id}', [KdsController::class, 'getById']);
        Route::put('/{id}', [KdsController::class, 'update']);
        Route::delete('/{id}', [KdsController::class, 'destroy']);
    });



Route::middleware('auth:sanctum')
    ->prefix('reports')
    ->group(function () {
        Route::post('/', [KdsController::class, 'getOrders']);
        Route::get('/default', [KdsController::class, 'getDefault']);
        Route::post('/update_order', [KdsController::class, 'updateStatus'])->withoutMiddleware('throttle:api');
        Route::get('/{id}', [KdsController::class, 'getById']);
        Route::put('/{id}', [KdsController::class, 'update']);
        Route::delete('/{id}', [KdsController::class, 'destroy']);
    });




Route::middleware('auth:sanctum')
    ->prefix('auth')
    ->group(function () {
        Route::post('/save_role', [UserController::class, 'createOrUpdateRole']);
        Route::post('/save_auth', [UserController::class, 'saveRolePermissions']);
        Route::get('/get_role', [UserController::class, 'UserRoles']);
        Route::get('/get_default', [UserController::class, 'getDefaultPermissions']);
        Route::delete('/delete_role/{id}', [UserController::class, 'deleteUserRoles']);
        Route::post('/save_user', [UserController::class, 'register']);
        Route::delete('/delete_user/{id}', [UserController::class, 'deleteUser']);
        Route::get('/get_users', [UserController::class, 'Users']);
    });


Route::middleware('auth:sanctum')
    ->prefix('company')
    ->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
    });


Route::middleware('auth:sanctum')
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'avgDailyRevenueWithLunchDinner']);
    });


Route::middleware('auth:sanctum')
    ->prefix('report')
    ->group(function () {
        Route::get('/daily-sales-summary', [SalesController::class, 'dailySalesSummary']);
        Route::get('/daily-sales-detail', [SalesController::class, 'dailySalesDetails']);
        Route::get('/sales-by-item', [SalesController::class, 'salesByItem']);
        Route::get('/fast-moving-item', [SalesController::class, 'fastNonMovingReport']);
        Route::get('/sales-by-category', [SalesController::class, 'salesByCategory']);
        Route::get('/sales-by-channel', [SalesController::class, 'salesByChannel']);

        Route::get('/stock-status', [InventoryController::class, 'stockStatus']);
        Route::get('/food-costing', [InventoryController::class, 'foodCostingNoBOM']);
        Route::get('/reorderReport', [InventoryController::class, 'reorderReport']);
        Route::get('/stockMovement', [InventoryController::class, 'stockMovement']);

        Route::get('/table-turnover', [OperationalController::class, 'tableTurnover']);

        Route::get('/steward-performance', [OperationalController::class, 'performance']);


        Route::get('/outstanding', [PurchasingController::class, 'supplierOutstanding']);
        Route::get('/grnHistory', [PurchasingController::class, 'grnHistory']);

        Route::get('/allSessionsReport', [SalesController::class, 'allSessionsReport']);

        Route::get('/kot-void-report', [SalesController::class, 'getVoidOrders']);
         Route::get('/pnl-report', [SalesController::class, 'getPNLReport']);
         Route::get('/crm-report', [SalesController::class, 'dashboard']);
    });

Route::middleware('auth:sanctum')->group(function () {
    // Expenses routes
    Route::prefix('expenses')->group(function () {
        Route::get('/default', [ExpencesController::class, 'getDefault']);
        Route::post('/', [ExpencesController::class, 'createExpense']);
        Route::put('/{id}', [ExpencesController::class, 'updateExpense']);
        Route::delete('/{id}', [ExpencesController::class, 'deleteExpense']);
        Route::get('/{id}', [ExpencesController::class, 'getExpense']);
        Route::get('/', [ExpencesController::class, 'indexExpenses']);
        Route::get('/summary/report', [ExpencesController::class, 'getExpenseSummary']);
    });

    // Expense Categories routes (separate from expenses)
    Route::prefix('expense-categories')->group(function () {
        Route::post('/', [ExpencesController::class, 'createCategory']);
        Route::delete('/{id}', [ExpencesController::class, 'deleteCategory']);
        Route::get('/', [ExpencesController::class, 'indexCategory']);
    });
});





Route::middleware('auth:sanctum')
    ->prefix('addone')
    ->group(function () {
        Route::get('/', [AddoneController::class, 'index']);
        Route::post('/', [AddoneController::class, 'create']);
        Route::get('/{id}', [AddoneController::class, 'show']);
        Route::put('/{id}', [AddoneController::class, 'update']);
        Route::delete('/{id}', [AddoneController::class, 'delete']);
    });


Route::middleware('apikey')
    ->prefix('sync-master')
    ->group(function () {
        Route::get('getItemCategory', [SyncMasterDataController::class, 'getItemCategory']);
        Route::post('markItemCategorySynced', [SyncMasterDataController::class, 'markItemCategorySynced']);

        Route::get('getItems', [SyncMasterDataController::class, 'getItems']);
        Route::post('markItemsSynced', [SyncMasterDataController::class, 'markItemsSynced']);

        Route::get('getTableMaster', [SyncMasterDataController::class, 'getTableMaster']);
        Route::post('markTableMasterSynced', [SyncMasterDataController::class, 'markTableMasterSynced']);

        Route::get('getSteward', [SyncMasterDataController::class, 'getSteward']);
        Route::post('markStewardSynced', [SyncMasterDataController::class, 'markStewardSynced']);

        Route::get('getAddone', [SyncMasterDataController::class, 'getAddone']);
        Route::post('markAddoneSynced', [SyncMasterDataController::class, 'markAddoneSynced']);


        Route::get('getKitchen', [SyncMasterDataController::class, 'getKitchen']);
        Route::post('markKitchenSynced', [SyncMasterDataController::class, 'markKitchenSynced']);


        Route::get('getPaymentMethod', [SyncMasterDataController::class, 'getPaymentMethod']);
        Route::post('markPaymentMethodSynced', [SyncMasterDataController::class, 'markPaymentMethodSynced']);


        Route::get('getUsers/{wh}', [SyncMasterDataController::class, 'getUsers']);
        Route::post('markUsersSynced', [SyncMasterDataController::class, 'markUsersSynced']);

        Route::get('getCompany', [SyncMasterDataController::class, 'getCompany']);
        Route::post('markCompanySynced', [SyncMasterDataController::class, 'markCompanySynced']);

        Route::get('verify', [SyncMasterDataController::class, 'verify']);

        Route::post('customerSync', [SyncMasterDataController::class, 'customerSync']);
        Route::post('orderSync', [SyncClientDataController::class, 'SaveOrderSync']);
        Route::post('orderVoidSync', [SyncClientDataController::class, 'orderVoidSync']);

        Route::post('invoiceSync', [SyncClientDataController::class, 'SaveInvoiceSync']);
        Route::post('invoicePaymentSync', [SyncClientDataController::class, 'invoicePaymentSync']);

        Route::post('voidInvoice', [SyncClientDataController::class, 'voidInvoiceByReference']);

        Route::post('sessionSync', [SyncClientDataController::class, 'sessionSync']);

        Route::get('kot', [SyncClientDataController::class, 'getOrders']);
    });


