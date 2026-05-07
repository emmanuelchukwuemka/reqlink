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

Route::get('/logout', [WebAuthController::class, 'logout'])->name('logout.get');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::post('/responder/toggle-duty', [ResponderController::class, 'toggleDuty'])->name('responder.toggle-duty');
    Route::post('/responder/update-location', [ResponderController::class, 'updateLocation'])->name('responder.update-location');
    Route::post('/hospital/update', [HospitalController::class, 'update'])->name('hospital.update');
    Route::get('/emergency/status/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'getStatus'])->name('emergency.status');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings')->middleware('auth');
Route::get('/admin/command-center', [DashboardController::class, 'commandCenter'])->name('admin.command-center')->middleware('auth');
Route::post('/admin/user/{id}/toggle-status', [DashboardController::class, 'toggleUserStatus'])->name('admin.user.toggle-status')->middleware('auth');
Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update')->middleware('auth');
Route::post('/user/toggle-samaritan', [DashboardController::class, 'toggleSamaritan'])->middleware('auth');
Route::post('/api/chat/openai', [\App\Http\Controllers\OpenAiController::class, 'chat'])->middleware('auth');

Route::post('/emergency/trigger', [\App\Http\Controllers\EmergencyController::class, 'trigger'])
    ->name('emergency.trigger')
    ->middleware('auth');

Route::get('/debug-mail', function () {
    return [
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'username' => config('mail.mailers.smtp.username'),
        'password' => config('mail.mailers.smtp.password'),
        'encryption' => config('mail.mailers.smtp.encryption'),
        'scheme' => config('mail.mailers.smtp.scheme'),
    ];
});

Route::get('/responder/alerts', [\App\Http\Controllers\EmergencyController::class, 'fetchAlerts'])
    ->name('responder.alerts')
    ->middleware('auth');
