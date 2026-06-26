<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResponderController;
use App\Http\Controllers\HospitalController;

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
Route::get('/verify-code', [WebAuthController::class, 'showVerifyCode'])->name('password.verify.code');
Route::post('/verify-code', [WebAuthController::class, 'verifyCode'])->name('password.verify.code.post');
Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');

Route::get('/logout', [WebAuthController::class, 'logout'])->name('logout.get');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::post('/support-message', [\App\Http\Controllers\SupportMessageController::class, 'store'])->name('support.message');

// Paystack webhook — must be outside auth + CSRF
Route::post('/wallet/webhook', [\App\Http\Controllers\WalletController::class, 'webhook'])
    ->name('wallet.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::middleware(['auth'])->group(function () {
    Route::post('/wallet/fund', [\App\Http\Controllers\WalletController::class, 'initiate'])->name('wallet.fund');
    Route::get('/wallet/callback', [\App\Http\Controllers\WalletController::class, 'callback'])->name('wallet.callback');
    Route::get('/map/live-data', [\App\Http\Controllers\DashboardController::class, 'liveMapData'])->name('map.live-data');
    Route::get('/admin/live-data', [\App\Http\Controllers\DashboardController::class, 'liveAdminData'])->name('admin.live-data');
    Route::post('/admin/dispatch', [\App\Http\Controllers\DashboardController::class, 'adminDispatch'])->name('admin.dispatch');

    Route::post('/responder/toggle-duty', [ResponderController::class, 'toggleDuty'])->name('responder.toggle-duty');
    Route::post('/responder/update-location', [ResponderController::class, 'updateLocation'])->name('responder.update-location');
    Route::post('/hospital/update', [HospitalController::class, 'update'])->name('hospital.update');
    Route::post('/hospital/accept/{uuid}', [HospitalController::class, 'acceptPatient'])->name('hospital.accept');
    Route::get('/emergency/status/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'getStatus'])->name('emergency.status');
    Route::post('/emergency/update-location/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'updateUserLocation'])->name('emergency.update-location');
    Route::post('/emergency/accept/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'acceptMission'])->name('emergency.accept');
    Route::post('/emergency/evidence/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'uploadEvidence'])->name('emergency.evidence');
    Route::post('/emergency/triage/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'updateTriage'])->name('emergency.triage');
    Route::post('/emergency/resolve/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'resolveEmergency'])->name('emergency.resolve');
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
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

Route::get('/responder/alerts', [\App\Http\Controllers\EmergencyController::class, 'fetchAlerts'])
    ->name('responder.alerts')
    ->middleware('auth');
