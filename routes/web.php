<?php

use App\Http\Controllers\WebAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::get('/register-partner', [WebAuthController::class, 'showPartnerRegister'])->name('register.partner');
Route::post('/register', [WebAuthController::class, 'register']);

// Password Reset Routes
Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings')->middleware('auth');
Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update')->middleware('auth');

Route::post('/emergency/trigger', [\App\Http\Controllers\EmergencyController::class, 'trigger'])
    ->name('emergency.trigger')
    ->middleware('auth');

Route::get('/responder/alerts', [\App\Http\Controllers\EmergencyController::class, 'fetchAlerts'])
    ->name('responder.alerts')
    ->middleware('auth');
