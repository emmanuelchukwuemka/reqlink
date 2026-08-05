<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupRequestController;
use App\Http\Controllers\Api\BedReservationController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\EmergencyController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\ResponderController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SupportMessageController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\CheckSuspensionApi;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware(['auth:sanctum', CheckSuspensionApi::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/mine', [ReviewController::class, 'mine']);
    Route::get('/responders/{id}/reviews', [ReviewController::class, 'forResponder']);

    Route::get('/emergency-types', [EmergencyController::class, 'types']);
    Route::get('/hospitals', [HospitalController::class, 'index']);

    Route::post('/emergency/trigger', [EmergencyController::class, 'trigger']);
    Route::get('/emergency/status/{uuid}', [EmergencyController::class, 'getStatus']);
    Route::post('/emergency/update-location/{uuid}', [EmergencyController::class, 'updateUserLocation']);
    Route::post('/emergency/{uuid}/choose-hospital', [EmergencyController::class, 'chooseHospital']);
    Route::post('/emergency/accept/{uuid}', [EmergencyController::class, 'acceptMission']);
    Route::post('/emergency/decline/{uuid}', [EmergencyController::class, 'declineMission']);
    Route::post('/emergency/arrived/{uuid}', [EmergencyController::class, 'markArrived']);
    Route::post('/emergency/responder-notes/{uuid}', [EmergencyController::class, 'saveResponderNotes']);
    Route::post('/emergency/request-doctor-consult/{uuid}', [EmergencyController::class, 'requestDoctorConsult']);
    Route::post('/emergency/resolve/{uuid}', [EmergencyController::class, 'resolveEmergency']);
    Route::post('/emergency/evidence/{uuid}', [EmergencyController::class, 'uploadEvidence']);
    Route::post('/emergency/triage/{uuid}', [EmergencyController::class, 'updateTriage']);
    Route::get('/responder/alerts', [EmergencyController::class, 'fetchAlerts']);

    Route::post('/responder/toggle-duty', [ResponderController::class, 'toggleDuty']);
    Route::post('/responder/update-location', [ResponderController::class, 'updateLocation']);
    Route::post('/responder/update-profile', [ResponderController::class, 'updateProfile']);

    Route::post('/backup-requests', [BackupRequestController::class, 'store']);
    Route::get('/backup-requests', [BackupRequestController::class, 'index']);
    Route::get('/backup-requests/mine', [BackupRequestController::class, 'mine']);
    Route::post('/backup-requests/{id}/acknowledge', [BackupRequestController::class, 'acknowledge']);
    Route::post('/backup-requests/{id}/resolve', [BackupRequestController::class, 'resolve']);

    Route::post('/bed/reserve/{hospitalId}', [BedReservationController::class, 'reserve']);
    Route::post('/bed/respond/{id}', [BedReservationController::class, 'respond']);
    Route::post('/bed/arrived/{id}', [BedReservationController::class, 'arrived']);
    Route::get('/bed/pending', [BedReservationController::class, 'pending']);

    Route::post('/hospital/update', [HospitalController::class, 'update']);
    Route::get('/hospital/incoming', [HospitalController::class, 'incoming']);
    Route::post('/hospital/accept/{uuid}', [HospitalController::class, 'acceptPatient']);
    Route::post('/hospital/decline/{uuid}', [HospitalController::class, 'declinePatient']);
    Route::post('/hospital/discharge/{uuid}', [HospitalController::class, 'dischargePatient']);
    Route::get('/hospital/incoming-locations', [HospitalController::class, 'incomingLocations']);
    Route::get('/hospital/admissions', [HospitalController::class, 'admissions']);
    Route::post('/hospital/patients', [HospitalController::class, 'storePatient']);
    Route::post('/hospital/patients/{id}/discharge', [HospitalController::class, 'dischargeManualPatient']);
    Route::delete('/hospital/patients/{id}', [HospitalController::class, 'destroyPatient']);
    Route::post('/hospital/reservations', [HospitalController::class, 'storeReservation']);
    Route::post('/hospital/reservations/{id}/cancel', [HospitalController::class, 'cancelReservation']);
    Route::post('/hospital/reservations/{id}/admit', [HospitalController::class, 'admitReservation']);

    Route::get('/doctor/consult-queue', [DoctorController::class, 'consultQueue']);
    Route::post('/emergency/doctor-notes/{uuid}', [DoctorController::class, 'saveNotes']);
    Route::post('/emergency/complete-consult/{uuid}', [DoctorController::class, 'completeConsult']);

    Route::post('/wallet/fund', [WalletController::class, 'initiate']);
    Route::get('/wallet/banks', [WalletController::class, 'banks']);
    Route::post('/wallet/resolve-account', [WalletController::class, 'resolveAccount']);
    Route::post('/wallet/bank-account', [WalletController::class, 'saveBankAccount']);
    Route::post('/wallet/withdraw', [WalletController::class, 'requestWithdrawal']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::get('/wallet/transactions/{reference}', [WalletController::class, 'transactionStatus']);

    Route::get('/chat/{uuid}/messages', [ChatController::class, 'messages']);
    Route::post('/chat/{uuid}/send', [ChatController::class, 'send']);
    Route::get('/chat/{uuid}/unread', [ChatController::class, 'unreadCount']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/map/live-data', [DashboardController::class, 'liveMapData']);

    Route::post('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/delete-account', [SettingsController::class, 'deleteAccount']);

    Route::post('/user/toggle-samaritan', [ProfileController::class, 'toggleSamaritan']);
    Route::post('/user/toggle-mamacare', [ProfileController::class, 'toggleMamaCare']);
    Route::post('/user/update-mamacare-profile', [ProfileController::class, 'updateMamaCareProfile']);
    Route::post('/user/update-specialty', [ProfileController::class, 'updateSpecialty']);

    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/support-messages/mine', [SupportMessageController::class, 'mine']);

    Route::post('/push/register', [PushController::class, 'register']);
    Route::post('/push/unregister', [PushController::class, 'unregister']);
});

// Public, read-only content — no auth required
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);

// Guest-friendly like its web counterpart (auto-fills from the bearer token
// if one is present, but doesn't require it)
Route::post('/support-message', [SupportMessageController::class, 'store']);

// USSD & SMS Bridge (External Gateways)
Route::post('/bridge/sms', [\App\Http\Controllers\UssdBridgeController::class, 'handleSms']);
Route::post('/bridge/ussd', [\App\Http\Controllers\UssdBridgeController::class, 'handleUssd']);
