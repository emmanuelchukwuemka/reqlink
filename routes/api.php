<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\SosController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/sos', [SosController::class, 'trigger']);
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);
});

// USSD & SMS Bridge (External Gateways)
Route::post('/bridge/sms', [\App\Http\Controllers\UssdBridgeController::class, 'handleSms']);
Route::post('/bridge/ussd', [\App\Http\Controllers\UssdBridgeController::class, 'handleUssd']);
