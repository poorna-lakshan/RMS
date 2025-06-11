<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemClassController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\KitchenController;
use App\Http\Controllers\Purchasing\VendorController;
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

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ItemController::class, 'index']);
    Route::post('/', [ItemController::class, 'create']);
    Route::get('/{id}', [ItemController::class, 'show']);
    Route::put('/{id}', [ItemController::class, 'update']);
    Route::delete('/{id}', [ItemController::class, 'destroy']);
});

Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [ItemCategoryController::class, 'index']);
    Route::post('/', [ItemCategoryController::class, 'create']);
    Route::get('/{id}', [ItemCategoryController::class, 'show']);
    Route::put('/{id}', [ItemCategoryController::class, 'update']);
    Route::delete('/{id}', [ItemCategoryController::class, 'delete']);
});

Route::group(['prefix' => 'class'], function () {
    Route::get('/', [ItemClassController::class, 'index']);
    Route::post('/', [ItemClassController::class, 'create']);
    Route::get('/{id}', [ItemClassController::class, 'show']);
    Route::put('/{id}', [ItemClassController::class, 'update']);
    Route::delete('/{id}', [ItemClassController::class, 'delete']);
});


Route::group(['prefix' => 'warehouse'], function () {
    Route::get('/', [WarehouseController::class, 'index']);
    Route::post('/', [WarehouseController::class, 'create']);
    Route::get('/{id}', [WarehouseController::class, 'show']);
    Route::put('/{id}', [WarehouseController::class, 'update']);
    Route::delete('/{id}', [WarehouseController::class, 'delete']);
});

Route::group(['prefix' => 'kitchen'], function () {
    Route::get('/', [KitchenController::class, 'index']);
    Route::post('/', [KitchenController::class, 'create']);
    Route::get('/{id}', [KitchenController::class, 'show']);
    Route::put('/{id}', [KitchenController::class, 'update']);
    Route::delete('/{id}', [KitchenController::class, 'delete']);
});

Route::group(['prefix' => 'vendor'], function () {
    Route::get('/', [VendorController::class, 'index']);
    Route::post('/', [VendorController::class, 'create']);
    Route::get('/{id}', [VendorController::class, 'show']);
    Route::put('/{id}', [VendorController::class, 'update']);
    Route::delete('/{id}', [VendorController::class, 'delete']);
});


