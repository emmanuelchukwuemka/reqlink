<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResponderController;
use App\Http\Controllers\HospitalController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blogPost:slug}', [BlogController::class, 'show'])->name('blog.show');

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

// Language switcher (public)
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'yo', 'ha', 'ig'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

Route::middleware(['auth'])->group(function () {
    Route::post('/wallet/fund', [\App\Http\Controllers\WalletController::class, 'initiate'])->name('wallet.fund');
    Route::get('/wallet/callback', [\App\Http\Controllers\WalletController::class, 'callback'])->name('wallet.callback');
    Route::get('/map/live-data', [\App\Http\Controllers\DashboardController::class, 'liveMapData'])->name('map.live-data');
    Route::get('/admin/live-data', [\App\Http\Controllers\DashboardController::class, 'liveAdminData'])->name('admin.live-data');
    Route::post('/admin/dispatch', [\App\Http\Controllers\DashboardController::class, 'adminDispatch'])->name('admin.dispatch');
    Route::get('/admin/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('admin.analytics');

    // Chat
    Route::get('/chat/{uuid}/messages', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/{uuid}/send', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/{uuid}/unread', [\App\Http\Controllers\ChatController::class, 'unreadCount'])->name('chat.unread');

    // Bed reservations
    Route::post('/bed/reserve/{uuid}', [\App\Http\Controllers\BedReservationController::class, 'reserve'])->name('bed.reserve');
    Route::post('/bed/respond/{id}', [\App\Http\Controllers\BedReservationController::class, 'respond'])->name('bed.respond');
    Route::post('/bed/arrived/{id}', [\App\Http\Controllers\BedReservationController::class, 'arrived'])->name('bed.arrived');
    Route::get('/bed/pending', [\App\Http\Controllers\BedReservationController::class, 'pending'])->name('bed.pending');

    // Push subscriptions
    Route::post('/push/subscribe', [\App\Http\Controllers\PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::get('/push/vapid-public-key', [\App\Http\Controllers\PushController::class, 'vapidPublicKey'])->name('push.vapid-key');

    Route::post('/responder/toggle-duty', [ResponderController::class, 'toggleDuty'])->name('responder.toggle-duty');
    Route::post('/responder/update-location', [ResponderController::class, 'updateLocation'])->name('responder.update-location');
    Route::post('/responder/update-profile', [ResponderController::class, 'updateProfile'])->name('responder.update-profile');
    Route::post('/backup-requests', [\App\Http\Controllers\BackupRequestController::class, 'store'])->name('backup-requests.store');
    Route::get('/backup-requests', [\App\Http\Controllers\BackupRequestController::class, 'index'])->name('backup-requests.index');
    Route::get('/backup-requests/mine', [\App\Http\Controllers\BackupRequestController::class, 'mine'])->name('backup-requests.mine');
    Route::post('/backup-requests/{id}/acknowledge', [\App\Http\Controllers\BackupRequestController::class, 'acknowledge'])->name('backup-requests.acknowledge');
    Route::post('/backup-requests/{id}/resolve', [\App\Http\Controllers\BackupRequestController::class, 'resolve'])->name('backup-requests.resolve');
    Route::post('/hospital/update', [HospitalController::class, 'update'])->name('hospital.update');
    Route::post('/hospital/accept/{uuid}', [HospitalController::class, 'acceptPatient'])->name('hospital.accept');
    Route::post('/hospital/decline/{uuid}', [HospitalController::class, 'declinePatient'])->name('hospital.decline');
    Route::post('/hospital/discharge/{uuid}', [HospitalController::class, 'dischargePatient'])->name('hospital.discharge');
    Route::get('/hospital/incoming-locations', [HospitalController::class, 'incomingLocations'])->name('hospital.incoming-locations');
    Route::get('/hospital/export-admissions', [HospitalController::class, 'exportAdmissions'])->name('hospital.export-admissions');
    Route::post('/hospital/patients', [HospitalController::class, 'storePatient'])->name('hospital.patients.store');
    Route::post('/hospital/patients/{id}/discharge', [HospitalController::class, 'dischargeManualPatient'])->name('hospital.patients.discharge');
    Route::delete('/hospital/patients/{id}', [HospitalController::class, 'destroyPatient'])->name('hospital.patients.destroy');
    Route::post('/hospital/reservations', [HospitalController::class, 'storeReservation'])->name('hospital.reservations.store');
    Route::post('/hospital/reservations/{id}/cancel', [HospitalController::class, 'cancelReservation'])->name('hospital.reservations.cancel');
    Route::post('/hospital/reservations/{id}/admit', [HospitalController::class, 'admitReservation'])->name('hospital.reservations.admit');
    Route::post('/emergency/responder-notes/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'saveResponderNotes'])->name('emergency.responder-notes');
    Route::get('/emergency/status/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'getStatus'])->name('emergency.status');
    Route::post('/emergency/update-location/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'updateUserLocation'])->name('emergency.update-location');
    Route::post('/emergency/accept/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'acceptMission'])->name('emergency.accept');
    Route::post('/emergency/decline/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'declineMission'])->name('emergency.decline');
    Route::post('/emergency/arrived/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'markArrived'])->name('emergency.arrived');
    Route::post('/emergency/evidence/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'uploadEvidence'])->name('emergency.evidence');
    Route::post('/emergency/triage/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'updateTriage'])->name('emergency.triage');
    Route::post('/emergency/resolve/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'resolveEmergency'])->name('emergency.resolve');
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings')->middleware('auth');
Route::get('/admin/command-center', [DashboardController::class, 'commandCenter'])->name('admin.command-center')->middleware('auth');
Route::post('/admin/user/{id}/toggle-status', [DashboardController::class, 'toggleUserStatus'])->name('admin.user.toggle-status')->middleware('auth');
Route::post('/admin/user/{id}/role', [DashboardController::class, 'updateUserRole'])->name('admin.user.role')->middleware('auth');
Route::get('/admin/incidents', [DashboardController::class, 'globalIncidents'])->name('admin.incidents')->middleware('auth');
Route::post('/admin/incident/{id}/status', [DashboardController::class, 'updateIncidentStatus'])->name('admin.incident.status')->middleware('auth');
Route::get('/admin/agencies', [DashboardController::class, 'agencyOversight'])->name('admin.agencies')->middleware('auth');
Route::post('/admin/responder/{id}/toggle-duty', [DashboardController::class, 'adminToggleResponderDuty'])->name('admin.responder.toggle-duty')->middleware('auth');
Route::get('/admin/blog', [BlogController::class, 'adminIndex'])->name('admin.blog.index')->middleware('auth');
Route::post('/admin/blog', [BlogController::class, 'store'])->name('admin.blog.store')->middleware('auth');
Route::post('/admin/blog/media-upload', [BlogController::class, 'uploadMedia'])->name('admin.blog.media-upload')->middleware('auth');
Route::put('/admin/blog/{blogPost}', [BlogController::class, 'update'])->name('admin.blog.update')->middleware('auth');
Route::delete('/admin/blog/{blogPost}', [BlogController::class, 'destroy'])->name('admin.blog.destroy')->middleware('auth');

Route::get('/admin/verifications', [\App\Http\Controllers\AdminVerificationController::class, 'index'])->name('admin.verifications.index')->middleware('auth');
Route::post('/admin/verifications/{id}/approve', [\App\Http\Controllers\AdminVerificationController::class, 'approve'])->name('admin.verifications.approve')->middleware('auth');
Route::post('/admin/verifications/{id}/reject', [\App\Http\Controllers\AdminVerificationController::class, 'reject'])->name('admin.verifications.reject')->middleware('auth');

Route::get('/admin/tools', [\App\Http\Controllers\AdminToolsController::class, 'index'])->name('admin.tools')->middleware('auth');

Route::post('/admin/support/{id}/read', [\App\Http\Controllers\AdminSupportController::class, 'markRead'])->name('admin.support.read')->middleware('auth');
Route::post('/admin/support/{id}/reply', [\App\Http\Controllers\AdminSupportController::class, 'reply'])->name('admin.support.reply')->middleware('auth');
Route::delete('/admin/support/{id}', [\App\Http\Controllers\AdminSupportController::class, 'destroy'])->name('admin.support.destroy')->middleware('auth');

Route::post('/admin/finance/{id}/flag', [\App\Http\Controllers\AdminFinanceController::class, 'flag'])->name('admin.finance.flag')->middleware('auth');
Route::post('/admin/finance/{id}/unflag', [\App\Http\Controllers\AdminFinanceController::class, 'unflag'])->name('admin.finance.unflag')->middleware('auth');
Route::get('/admin/finance/export', [\App\Http\Controllers\AdminFinanceController::class, 'export'])->name('admin.finance.export')->middleware('auth');

Route::delete('/admin/reviews/{id}', [\App\Http\Controllers\AdminReviewController::class, 'destroy'])->name('admin.reviews.destroy')->middleware('auth');

Route::post('/admin/announcements', [\App\Http\Controllers\AdminAnnouncementController::class, 'store'])->name('admin.announcements.store')->middleware('auth');
Route::delete('/admin/announcements/{id}', [\App\Http\Controllers\AdminAnnouncementController::class, 'destroy'])->name('admin.announcements.destroy')->middleware('auth');

Route::post('/admin/emergency-types', [\App\Http\Controllers\AdminEmergencyTypeController::class, 'store'])->name('admin.emergency-types.store')->middleware('auth');
Route::put('/admin/emergency-types/{id}', [\App\Http\Controllers\AdminEmergencyTypeController::class, 'update'])->name('admin.emergency-types.update')->middleware('auth');
Route::delete('/admin/emergency-types/{id}', [\App\Http\Controllers\AdminEmergencyTypeController::class, 'destroy'])->name('admin.emergency-types.destroy')->middleware('auth');

Route::get('/admin/users/export', [DashboardController::class, 'exportUsers'])->name('admin.users.export')->middleware('auth');
Route::post('/admin/users/bulk-action', [DashboardController::class, 'bulkUserAction'])->name('admin.users.bulk-action')->middleware('auth');
Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update')->middleware('auth');
Route::post('/settings/delete-account', [\App\Http\Controllers\SettingsController::class, 'deleteAccount'])->name('settings.delete-account')->middleware('auth');
Route::post('/user/toggle-samaritan', [DashboardController::class, 'toggleSamaritan'])->middleware('auth');
Route::post('/user/toggle-mamacare', [DashboardController::class, 'toggleMamaCare'])->middleware('auth');
Route::post('/user/update-mamacare-profile', [DashboardController::class, 'updateMamaCareProfile'])->middleware('auth');
Route::post('/user/update-specialty', [DashboardController::class, 'updateSpecialty'])->middleware('auth');
Route::post('/emergency/request-doctor-consult/{uuid}', [\App\Http\Controllers\EmergencyController::class, 'requestDoctorConsult'])->name('emergency.request-doctor-consult')->middleware('auth');
Route::post('/emergency/doctor-notes/{uuid}', [DashboardController::class, 'saveDoctorNotes'])->name('doctor.notes.save')->middleware('auth');
Route::post('/emergency/complete-consult/{uuid}', [DashboardController::class, 'completeConsult'])->name('doctor.consult.complete')->middleware('auth');
// Public so the landing-page AI widget works for visitors who aren't logged in yet
Route::post('/api/chat/openai', [\App\Http\Controllers\OpenAiController::class, 'chat'])
    ->name('api.chat.openai')
    ->middleware('throttle:20,1');

Route::post('/emergency/trigger', [\App\Http\Controllers\EmergencyController::class, 'trigger'])
    ->name('emergency.trigger')
    ->middleware('auth');

Route::get('/responder/alerts', [\App\Http\Controllers\EmergencyController::class, 'fetchAlerts'])
    ->name('responder.alerts')
    ->middleware('auth');
