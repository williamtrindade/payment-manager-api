<?php

use App\Enums\UserRole;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/buy', [CheckoutController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

    Route::middleware('role:ADMIN,FINANCE')->group(function () {
        Route::post('/transactions/{transaction}/refund', [RefundController::class, 'refund']);
    });

    // ADMIN, MANAGER e FINANCE (Gerenciar Produtos)
    Route::middleware('role:ADMIN,MANAGER,FINANCE')->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    // Apenas ADMIN (Configurações de Gateway)
    Route::middleware('role:ADMIN')->group(function () {
        Route::patch('/gateways/{gateway}/toggle', [GatewayController::class, 'toggle']);
        Route::patch('/gateways/{gateway}/priority', [GatewayController::class, 'updatePriority']);
        Route::apiResource('users', UserController::class);
    });
});
