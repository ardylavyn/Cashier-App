<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductCategoryImageController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Sanctum itu gerbang, jadi butuh kunci (token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/product-categories/options', [ProductCategoryController::class, 'options']);
        Route::post('/product-categories/{id}/image', [ProductCategoryImageController::class, 'store']);
        Route::delete('/product-categories/{id}/image', [ProductCategoryImageController::class, 'destroy']);
        Route::apiResource('product-categories', ProductCategoryController::class);

        Route::get('/products/options', [ProductsController::class, 'options']);
        Route::post('/products/{id}/image', [ProductImageController::class, 'store']);
        Route::delete('/products/{id}/image', [ProductImageController::class, 'destroy']);
        Route::apiResource('products', ProductsController::class);

        Route::get('/customers/options', [CustomerController::class, 'options']);
        Route::apiResource('customers', CustomerController::class);

        Route::get('/transactions/options', [TransactionController::class, 'options']);
        Route::get('/transactions/refunds', [TransactionController::class, 'refunds']);
        Route::apiResource('transactions', TransactionController::class);
        Route::post('/transactions/{id}/refund', [TransactionController::class, 'refund']);
    });
});
