<?php

use App\Http\Controllers\CaseController;
use App\Http\Controllers\LookupAdminController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ImpactController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\OutreachController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PulseSurveyController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceEncounterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MediationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated Routes (hub-scoped + write-protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'hub.scope', 'can.write'])->group(function () {

    // ── Dashboards ──────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'commandCenter'])->name('dashboard');
    Route::get('/dashboard/litigation-adr', [DashboardController::class, 'litigationAdr'])->name('dashboard.litigation-adr');

    // ── Cases ────────────────────────────────────────────────────────
    Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/{case}/slip', [CaseController::class, 'slip'])->name('cases.slip');
    Route::get('/cases/{case}', [CaseController::class, 'show'])->name('cases.show');
    Route::post('/cases/{case}/approve', [CaseController::class, 'approve'])->name('cases.approve');
    Route::post('/cases/{case}/reject', [CaseController::class, 'reject'])->name('cases.reject');
    Route::post('/cases/{case}/resolve', [CaseController::class, 'resolve'])->name('cases.resolve');
    Route::post('/cases/{case}/reassign', [CaseController::class, 'reassign'])->name('cases.reassign');
    Route::post('/cases/{case}/messages', [CaseController::class, 'storeMessage'])->name('cases.message.store');
    Route::post('/cases/{case}/transfers/{transfer}/approve', [CaseController::class, 'approveTransfer'])->name('cases.transfer.approve');
    Route::post('/cases/{case}/transfers/{transfer}/reject',  [CaseController::class, 'rejectTransfer'])->name('cases.transfer.reject');

    // ── Intake ───────────────────────────────────────────────────────
    Route::get('/intake', [IntakeController::class, 'create'])->name('intake.create');
    Route::post('/intake', [IntakeController::class, 'store'])->name('intake.store');

    // ── Service Encounters ───────────────────────────────────────────
    Route::post('/cases/{case}/encounters', [ServiceEncounterController::class, 'store'])->name('encounters.store');

    // ── Documents ────────────────────────────────────────────────────
    Route::post('/cases/{case}/documents', [CaseController::class, 'storeDocument'])->name('documents.store');
    Route::post('/documents/{document}/verify', [CaseController::class, 'verifyDocument'])->name('documents.verify');

    // ── Service Scorecards & Calendars ───────────────────────────────
    Route::get('/services/adr-scorecard', [ServiceController::class, 'adrScorecard'])->name('services.adr');
    Route::post('/services/adr-referral', [ServiceController::class, 'storeAdrReferral'])->name('services.adr.referral');
    Route::post('/services/encounters', [ServiceEncounterController::class, 'logFromScorecard'])->name('encounters.log');
    Route::get('/services/adr-calendar', [ServiceController::class, 'adrCalendar'])->name('services.adr-calendar');
    Route::get('/services/litigation-scorecard', [ServiceController::class, 'litigationScorecard'])->name('services.litigation');
    Route::get('/services/litigation-calendar', [ServiceController::class, 'litigationCalendar'])->name('services.litigation-calendar');
    Route::post('/cases/{case}/litigation-stage', [ServiceController::class, 'updateLitigationStage'])->name('cases.litigation-stage');
    Route::post('/cases/{case}/adr-stage', [ServiceController::class, 'updateAdrStage'])->name('cases.adr-stage');
    Route::post('/cases/{case}/set-outcome', [CaseController::class, 'setOutcome'])->name('cases.set-outcome');

    // ── Case Referral Tracking ───────────────────────────────────────
    Route::post('/cases/{case}/referrals',                              [CaseController::class, 'storeReferral'])->name('cases.referral.store');
    Route::patch('/cases/{case}/referrals/{referral}/focal',            [CaseController::class, 'updateReferralFocal'])->name('cases.referral.focal');
    Route::post('/cases/{case}/referrals/{referral}/letters',           [CaseController::class, 'storeReferralLetter'])->name('cases.referral.letter');
    Route::post('/cases/{case}/referrals/{referral}/threads',           [CaseController::class, 'storeReferralThread'])->name('cases.referral.thread');
    Route::post('/cases/{case}/referrals/{referral}/close',             [CaseController::class, 'closeReferral'])->name('cases.referral.close');
    Route::delete('/cases/{case}/referrals/{referral}',                 [CaseController::class, 'destroyReferral'])->name('cases.referral.destroy');

    // ── Mediation Workflow ───────────────────────────────────────────
    Route::post('/cases/{case}/mediation/parties',              [MediationController::class, 'storeParty'])->name('mediation.party.store');
    Route::delete('/cases/{case}/mediation/parties/{party}',    [MediationController::class, 'destroyParty'])->name('mediation.party.destroy');
    Route::post('/cases/{case}/mediation/consent',              [MediationController::class, 'updateConsent'])->name('mediation.consent.update');
    Route::post('/cases/{case}/mediation/diary',                [MediationController::class, 'storeDiary'])->name('mediation.diary.store');

    // ── Referrals ────────────────────────────────────────────────────
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referrals', [ReferralController::class, 'store'])->name('referrals.store');

    // ── Outreach ─────────────────────────────────────────────────────
    Route::get('/outreach', [OutreachController::class, 'index'])->name('outreach.index');
    Route::post('/outreach', [OutreachController::class, 'store'])->name('outreach.store');
    Route::post('/outreach/{outreach}/pulse', [PulseSurveyController::class, 'store'])->name('pulse.store');

    // ── Complaints ───────────────────────────────────────────────────
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::post('/complaints/{complaint}/actions', [ComplaintController::class, 'addAction'])->name('complaints.action');

    // ── Indicators ───────────────────────────────────────────────────
    Route::get('/indicators', [IndicatorController::class, 'index'])->name('indicators.index');
    Route::get('/indicators/{indicator}', [IndicatorController::class, 'show'])->name('indicators.show');

    // ── Evidence ─────────────────────────────────────────────────────
    Route::get('/evidence', [EvidenceController::class, 'index'])->name('evidence.index');
    Route::post('/evidence', [EvidenceController::class, 'store'])->name('evidence.store');
    Route::post('/evidence/{evidence}/verify', [EvidenceController::class, 'verify'])->name('evidence.verify');

    // ── Feedback ─────────────────────────────────────────────────────
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // ── Staff & Training ─────────────────────────────────────────────
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
    Route::post('/staff/{staff}/training', [StaffController::class, 'logTraining'])->name('staff.training');
    Route::post('/staff/user/{user}/training', [StaffController::class, 'logUserTraining'])->name('staff.user-training');

    // ── Learning & VfM ───────────────────────────────────────────────
    Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
    Route::post('/learning/reflections', [LearningController::class, 'storeReflection'])->name('learning.reflection');
    Route::post('/learning/case-studies', [LearningController::class, 'storeCaseStudy'])->name('learning.case-study');
    Route::post('/learning/finance-inputs', [LearningController::class, 'updateFinanceInputs'])->name('learning.finance-inputs');

    // ── Impact Reports ───────────────────────────────────────────────
    Route::get('/impact', [ImpactController::class, 'index'])->name('impact.index');
    Route::post('/impact/export', [ImpactController::class, 'export'])->name('impact.export');

    // ── Settings ─────────────────────────────────────────────────────
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/hub', [SettingsController::class, 'setHub'])->name('settings.hub');
    Route::post('/settings/theme', [SettingsController::class, 'setTheme'])->name('settings.theme');
    Route::post('/settings/finance', [SettingsController::class, 'updateFinance'])->name('settings.finance');

    // ── User Management (Head / users.manage only) ───────────────
    Route::get('/settings/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/settings/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/settings/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/settings/users/{user}/toggle', [UserManagementController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/settings/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // ── Lookup Admin (Head / lookups.manage only) ────────────────
    Route::post('/settings/lookups/groups', [LookupAdminController::class, 'storeGroup'])->name('lookups.group.store');
    Route::post('/settings/lookups/options', [LookupAdminController::class, 'storeOption'])->name('lookups.option.store');
    Route::patch('/settings/lookups/options/{lookup}', [LookupAdminController::class, 'updateOption'])->name('lookups.option.update');
    Route::post('/settings/lookups/options/{lookup}/toggle', [LookupAdminController::class, 'toggleOption'])->name('lookups.option.toggle');
    Route::delete('/settings/lookups/options/{lookup}', [LookupAdminController::class, 'destroyOption'])->name('lookups.option.destroy');
    Route::post('/settings/lookups/reorder', [LookupAdminController::class, 'reorderGroup'])->name('lookups.reorder');

    // ── Training Course Management ──────────────────────────────
    Route::post('/settings/training', [SettingsController::class, 'storeTraining'])->name('settings.training.store');
    Route::delete('/settings/training/{training}', [SettingsController::class, 'deleteTraining'])->name('settings.training.delete');

    // ── Module Toggle ────────────────────────────────────────────────
    Route::post('/settings/modules/{key}/toggle', [SettingsController::class, 'toggleModule'])->name('settings.module.toggle');

    // ── Partner Organisations ────────────────────────────────────────
    Route::post('/settings/partners/category',    [SettingsController::class, 'storePartnerCategory'])->name('settings.partner.category.store');
    Route::post('/settings/partners',             [SettingsController::class, 'storePartner'])->name('settings.partner.store');
    Route::patch('/settings/partners/{partner}',  [SettingsController::class, 'updatePartner'])->name('settings.partner.update');
    Route::delete('/settings/partners/{partner}', [SettingsController::class, 'destroyPartner'])->name('settings.partner.destroy');

    // ── Location Management ─────────────────────────────────────
    Route::get('/settings/locations/details', [SettingsController::class, 'locationDetails'])->name('locations.details');
    Route::post('/settings/locations', [SettingsController::class, 'storeLocation'])->name('locations.store');
    Route::delete('/settings/locations/{id}', [SettingsController::class, 'deleteLocation'])->name('locations.delete');
    Route::delete('/settings/locations-bulk', [SettingsController::class, 'bulkDeleteLocations'])->name('locations.bulk-delete');
});

// ── Notifications (auth only — no write guard needed for reads) ──────
Route::middleware('auth')->group(function () {
    Route::get('/notifications',           [\App\Http\Controllers\NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationsController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read',[\App\Http\Controllers\NotificationsController::class, 'markRead'])->name('notifications.read');
});

// ── Profile (Breeze) ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Public LCD Dashboard (no login required — for TV / kiosk display) ───
Route::get('/lcd', [DashboardController::class, 'lcd'])->name('dashboard.lcd');

require __DIR__.'/auth.php';
