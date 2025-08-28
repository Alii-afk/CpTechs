<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::apiResource('users', App\Http\Controllers\Api\UserController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

// Business Locations (public for registration)
Route::get('business-locations/active', [App\Http\Controllers\Api\BusinessLocationController::class, 'active']);

// Public routes for frontend dropdowns (active items only)
Route::get('business-locations/active', [App\Http\Controllers\Api\BusinessLocationController::class, 'active']);
Route::get('brands/active', [App\Http\Controllers\Api\BrandController::class, 'active']);
Route::get('flavors/active', [App\Http\Controllers\Api\FlavorController::class, 'active']);
Route::get('brands/{brand}/flavors/active', [App\Http\Controllers\Api\FlavorController::class, 'activeByBrand']);
Route::get('units/active', [App\Http\Controllers\Api\UnitController::class, 'active']);
Route::get('product-categories/active', [App\Http\Controllers\Api\ProductCategoryController::class, 'active']);
Route::get('suppliers/active', [App\Http\Controllers\Api\SupplierController::class, 'active']);

// Public routes for management (all items)
Route::get('business-locations', [App\Http\Controllers\Api\BusinessLocationController::class, 'index']);
Route::get('business-locations/{businessLocation}', [App\Http\Controllers\Api\BusinessLocationController::class, 'show']);
Route::get('brands', [App\Http\Controllers\Api\BrandController::class, 'index']);
Route::get('brands/{brand}', [App\Http\Controllers\Api\BrandController::class, 'show']);
Route::get('flavors', [App\Http\Controllers\Api\FlavorController::class, 'index']);
Route::get('flavors/{flavor}', [App\Http\Controllers\Api\FlavorController::class, 'show']);
Route::get('brands/{brand}/flavors', [App\Http\Controllers\Api\FlavorController::class, 'byBrand']);
Route::get('units', [App\Http\Controllers\Api\UnitController::class, 'index']);
Route::get('units/{unit}', [App\Http\Controllers\Api\UnitController::class, 'show']);
Route::get('product-categories', [App\Http\Controllers\Api\ProductCategoryController::class, 'index']);
Route::get('product-categories/{productCategory}', [App\Http\Controllers\Api\ProductCategoryController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User roles and permissions
    Route::apiResource('user-roles', App\Http\Controllers\Api\UserRoleController::class);
    Route::post('user-roles/{userRole}/permissions', [App\Http\Controllers\Api\UserRoleController::class, 'assignPermissions']);
    
    // Permissions
    Route::get('permissions/tabs', [App\Http\Controllers\Api\PermissionController::class, 'tabs']);
    Route::get('permissions/types', [App\Http\Controllers\Api\PermissionController::class, 'types']);
    Route::get('permissions/tab-permissions', [App\Http\Controllers\Api\PermissionController::class, 'tabPermissions']);
    Route::post('permissions/tabs', [App\Http\Controllers\Api\PermissionController::class, 'storeTab']);
    Route::post('permissions/types', [App\Http\Controllers\Api\PermissionController::class, 'storeType']);
    Route::post('permissions/tab-permissions', [App\Http\Controllers\Api\PermissionController::class, 'storeTabPermission']);
    
    // Users
    Route::get('users/for-commission', [App\Http\Controllers\Api\UserController::class, 'getUsersForCommission']);
    
    // Business Locations (protected for management)
    Route::post('business-locations', [App\Http\Controllers\Api\BusinessLocationController::class, 'store']);
    Route::put('business-locations/{businessLocation}', [App\Http\Controllers\Api\BusinessLocationController::class, 'update']);
    Route::delete('business-locations/{businessLocation}', [App\Http\Controllers\Api\BusinessLocationController::class, 'destroy']);
    
    // Suppliers
    Route::apiResource('suppliers', App\Http\Controllers\Api\SupplierController::class);
    Route::get('suppliers/with-dues', [App\Http\Controllers\Api\SupplierController::class, 'withDues']);
    Route::get('suppliers/{supplier}/dues', [App\Http\Controllers\Api\SupplierController::class, 'getDues']);
    Route::post('suppliers/{supplier}/dues', [App\Http\Controllers\Api\SupplierController::class, 'updateDues']);
    
    // Customers
    Route::apiResource('customers', App\Http\Controllers\Api\CustomerController::class);
    Route::get('customers/list', [App\Http\Controllers\Api\CustomerController::class, 'list']);
    Route::get('customers/outstanding-balance', [App\Http\Controllers\Api\CustomerController::class, 'withOutstandingBalance']);
    Route::post('customers/{customer}/balance', [App\Http\Controllers\Api\CustomerController::class, 'updateBalance']);
    
    // Brands and Flavors (protected for management)
    Route::post('brands', [App\Http\Controllers\Api\BrandController::class, 'store']);
    Route::put('brands/{brand}', [App\Http\Controllers\Api\BrandController::class, 'update']);
    Route::delete('brands/{brand}', [App\Http\Controllers\Api\BrandController::class, 'destroy']);
    Route::get('brands/with-flavor-count', [App\Http\Controllers\Api\BrandController::class, 'withFlavorCount']);
    
    // Flavors (protected for management)
    Route::post('flavors', [App\Http\Controllers\Api\FlavorController::class, 'store']);
    Route::put('flavors/{flavor}', [App\Http\Controllers\Api\FlavorController::class, 'update']);
    Route::delete('flavors/{flavor}', [App\Http\Controllers\Api\FlavorController::class, 'destroy']);
    Route::post('brands/{brand}/flavors', [App\Http\Controllers\Api\FlavorController::class, 'store']);
}); 

// Product Categories
Route::apiResource('product-categories', App\Http\Controllers\Api\ProductCategoryController::class);
Route::get('product-categories/active', [App\Http\Controllers\Api\ProductCategoryController::class, 'active']);

// Products
Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
Route::get('products/active', [App\Http\Controllers\Api\ProductController::class, 'active']);

// Units
Route::apiResource('units', App\Http\Controllers\Api\UnitController::class);
Route::get('units/active', [App\Http\Controllers\Api\UnitController::class, 'active']);

// Products
Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
Route::get('products/options', [App\Http\Controllers\Api\ProductController::class, 'getOptions']);
Route::get('products/low-stock', [App\Http\Controllers\Api\ProductController::class, 'getLowStock']);
Route::get('products/out-of-stock', [App\Http\Controllers\Api\ProductController::class, 'getOutOfStock']);

// Purchases
Route::apiResource('purchases', App\Http\Controllers\Api\PurchaseController::class);
Route::post('purchases/supplier-dues', [App\Http\Controllers\Api\PurchaseController::class, 'getSupplierDues']);
Route::post('purchases/{id}/receive-stock', [App\Http\Controllers\Api\PurchaseController::class, 'receiveStock']);
Route::get('purchases/{id}/audit-logs', [App\Http\Controllers\Api\PurchaseController::class, 'getAuditLogs']);

// Product Inventory
Route::get('product-inventory/stock-levels', [App\Http\Controllers\Api\ProductInventoryController::class, 'getStockLevels']);
Route::get('product-inventory/low-stock', [App\Http\Controllers\Api\ProductInventoryController::class, 'getLowStock']);
Route::get('product-inventory/out-of-stock', [App\Http\Controllers\Api\ProductInventoryController::class, 'getOutOfStock']);
Route::get('product-inventory/latest-pricing/{product}', [App\Http\Controllers\Api\ProductInventoryController::class, 'getLatestPricing']);
Route::apiResource('product-inventory', App\Http\Controllers\Api\ProductInventoryController::class); 