<?php

use App\Http\Controllers\Admin\OfficeSettingController;
use App\Http\Controllers\Admin\PresenceApprovalController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\ChatMediaController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Web\KaryawanChatController;
use App\Http\Controllers\Web\KaryawanController;
use App\Http\Controllers\Web\InternalGroupController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Response::file(Storage::disk('public')->path($path));
})->where('path', '.*');

Broadcast::routes(['middleware' => ['auth']]);

Route::get('/dashboard', function () {
    $user = Auth::user();
    if (in_array($user->role, ['kepala', 'admin'], true)) {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todayJobs = \App\Models\Job::with(['cs', 'technician.division'])
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $yesterdayJobs = \App\Models\Job::with(['cs', 'technician.division'])
            ->whereDate('created_at', $yesterday)
            ->latest()
            ->get();

        $activeJobs = \App\Models\Job::with(['cs', 'technician.division'])
            ->whereIn('status', ['pending', 'process'])
            ->latest()
            ->limit(8)
            ->get();

        $allJobs = \App\Models\Job::all();
        $todayPresences = \App\Models\Presence::with('user')
            ->whereDate('date', $today)
            ->get();
        $pendingPresenceApprovals = \App\Models\Presence::where(function ($query) {
                $query->where('is_approved', 'pending')
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('check_out')
                            ->where(function ($approvalQuery) {
                                $approvalQuery->where('is_approved_out', 'pending')
                                    ->orWhereNull('is_approved_out');
                            });
                    });
            })
            ->count();

        $pendingLeaves = \App\Models\Leave::where('status', 'pending')->count();
        $employeesCount = \App\Models\User::where('role', 'karyawan')->count();

        $summary = [
            'today_jobs' => $todayJobs->count(),
            'yesterday_jobs' => $yesterdayJobs->count(),
            'active_jobs' => $activeJobs->count(),
            'completed_jobs' => $allJobs->where('status', 'completed')->count(),
            'pending_jobs' => $allJobs->where('status', 'pending')->count(),
            'process_jobs' => $allJobs->where('status', 'process')->count(),
            'overdue_jobs' => $allJobs->filter->is_overdue->count(),
            'today_attendance' => $todayPresences->count(),
            'pending_presence_approvals' => $pendingPresenceApprovals,
            'pending_leaves' => $pendingLeaves,
            'employees' => $employeesCount,
        ];

        return view('dashboard', compact(
            'summary',
            'todayJobs',
            'yesterdayJobs',
            'activeJobs',
            'todayPresences'
        ));
    }
    if ($user->role === 'karyawan') {
        return redirect()->route('karyawan.dashboard');
    }

    return redirect()->route('technician.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/messages', [\App\Http\Controllers\Web\ChatWebController::class, 'index'])->name('admin.messages');
    Route::get('/notifications/poll', [\App\Http\Controllers\Web\NotificationWebController::class, 'poll'])->name('web.notifications.poll');
    Route::get('/messages/poll', [\App\Http\Controllers\Web\ChatWebController::class, 'poll'])->name('web.chats.poll');
    Route::post('/messages/send', [\App\Http\Controllers\Api\ChatController::class, 'store'])->name('web.chats.store');
    Route::post('/messages/chunks', [\App\Http\Controllers\Api\ChatController::class, 'uploadChunk'])->name('web.chats.chunks.store');
    Route::post('/messages/chunks/complete', [\App\Http\Controllers\Api\ChatController::class, 'completeChunkUpload'])->name('web.chats.chunks.complete');
    Route::post('/messages/{id}/pin', [\App\Http\Controllers\Api\ChatController::class, 'pin'])->name('web.chats.pin');
    Route::post('/messages/{id}/unpin', [\App\Http\Controllers\Api\ChatController::class, 'unpin'])->name('web.chats.unpin');
    Route::post('/messages/{id}/seen', [\App\Http\Controllers\Api\ChatController::class, 'markSeen'])->name('web.chats.seen.mark');
    Route::get('/messages/{id}/seen', [\App\Http\Controllers\Api\ChatController::class, 'seenBy'])->name('web.chats.seen');
    Route::put('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'update'])->name('web.chats.update');
    Route::delete('/messages/{id}', [\App\Http\Controllers\Api\ChatController::class, 'destroy'])->name('web.chats.destroy');
    Route::get('/internal-group/status', [InternalGroupController::class, 'status'])->name('web.internal-group.status');

    Route::middleware(['role:karyawan'])->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', [KaryawanController::class, 'dashboard'])->name('dashboard');

        Route::get('/attendance', [KaryawanController::class, 'attendanceIndex'])->name('attendance.index');
        Route::get('/attendance/check-in', [KaryawanController::class, 'attendanceCheckIn'])->name('attendance.checkin');
        Route::post('/attendance/check-in', [KaryawanController::class, 'attendanceStoreCheckIn'])->name('attendance.store-checkin');
        Route::post('/attendance/check-out', [KaryawanController::class, 'attendanceStoreCheckOut'])->name('attendance.store-checkout');
        Route::get('/attendance/request-permit', [KaryawanController::class, 'attendanceRequestPermit'])->name('attendance.request-permit');
        Route::post('/attendance/request-permit', [KaryawanController::class, 'attendanceSubmitPermit'])->name('attendance.submit-permit');

        Route::get('/notifications', [KaryawanController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/{id}', [KaryawanController::class, 'notificationShow'])->name('notifications.show');

        Route::get('/agenda', [KaryawanController::class, 'agendaIndex'])->name('agenda.index');
        Route::get('/profile', [KaryawanController::class, 'profile'])->name('profile');

        Route::get('/chat', [KaryawanChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/send/{chat?}', [KaryawanChatController::class, 'send'])->name('chat.send');
        Route::get('/chat/messages/{chat?}', [KaryawanChatController::class, 'messages'])->name('chat.messages');
    });

    Route::middleware('role:kepala')->group(function () {
        Route::get('/admin/attendance/approval', [PresenceApprovalController::class, 'index'])->name('admin.presence.index');
        Route::get('/admin/attendance/perizinan', [PresenceApprovalController::class, 'perizinan'])->name('admin.perizinan');
        Route::patch('/admin/attendance/approve/{id}', [PresenceApprovalController::class, 'leaveApprove'])->name('admin.presence.approve');
        Route::patch('/admin/attendance/reject/{id}', [PresenceApprovalController::class, 'leaveReject'])->name('admin.presence.reject');
        Route::post('/admin/attendance/approval/{id}/{status}', [PresenceApprovalController::class, 'updateStatus'])->name('admin.presence.updateStatus');
        Route::get('/admin/attendance/schedule', [PresenceApprovalController::class, 'schedule'])->name('admin.presence.schedule');
        Route::post('/admin/attendance/schedule/toggle', [PresenceApprovalController::class, 'toggleHoliday'])->name('admin.presence.toggle');
        Route::post('/admin/attendance/schedule/update', [PresenceApprovalController::class, 'updateSchedule'])->name('admin.presence.updateSchedule');
        Route::get('/admin/attendance/history', [PresenceApprovalController::class, 'history'])->name('admin.presence.history');
        Route::get('/admin/attendance/settings', [OfficeSettingController::class, 'index'])->name('admin.presence.settings');
        Route::post('/admin/attendance/settings/update', [OfficeSettingController::class, 'update'])->name('admin.presence.updateSettings');
    });

    Route::resource('divisions', DivisionController::class);
    Route::post('/users-management/{id}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users-management.reset-password');

    Route::resource('users-management', UserController::class);
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/history', [JobController::class, 'history'])->name('jobs.history');
    Route::get('/jobs/timeline', [JobController::class, 'timeline'])->name('jobs.timeline');
    Route::post('/jobs/{job}/feedback', [JobController::class, 'storeFeedback'])->name('jobs.feedback');
    Route::post('/jobs/{job}/comment', [JobController::class, 'storeComment'])->name('jobs.comment');
    Route::delete('/job-comments/{comment}', [JobController::class, 'destroyComment'])->name('jobs.comment.destroy');

    Route::get('/kpi/formulir', [KpiController::class, 'form'])->name('kpi.formulir');
    Route::post('/kpi/formulir', [KpiController::class, 'storeIndicator'])->name('kpi.indicators.store');
    Route::post('/kpi/formulir/lock', [KpiController::class, 'lockIndicators'])->name('kpi.indicators.lock');
    Route::put('/kpi/formulir/{indicator}', [KpiController::class, 'updateIndicator'])->name('kpi.indicators.update');
    Route::delete('/kpi/formulir/{indicator}', [KpiController::class, 'destroyIndicator'])->name('kpi.indicators.destroy');
    Route::get('/kpi/jadwal', [KpiController::class, 'schedules'])->name('kpi.jadwal');
    Route::post('/kpi/jadwal', [KpiController::class, 'storeSchedule'])->name('kpi.schedules.store');
    Route::put('/kpi/jadwal/{schedule}', [KpiController::class, 'updateSchedule'])->name('kpi.schedules.update');
    Route::delete('/kpi/jadwal/{schedule}', [KpiController::class, 'destroySchedule'])->name('kpi.schedules.destroy');
    Route::get('/kpi/evaluasi', [KpiController::class, 'evaluations'])->name('kpi.evaluasi');
    Route::post('/kpi/evaluasi', [KpiController::class, 'storeEvaluation'])->name('kpi.evaluations.store');
    Route::post('/kpi/evaluasi/{evaluation}/final', [KpiController::class, 'finalizeEvaluation'])->name('kpi.evaluations.final');
    Route::put('/kpi/evaluasi/{evaluation}', [KpiController::class, 'updateEvaluation'])->name('kpi.evaluations.update');
    Route::delete('/kpi/evaluasi/{evaluation}', [KpiController::class, 'destroyEvaluation'])->name('kpi.evaluations.destroy');

    Route::get('/recruitment/profil', [RecruitmentController::class, 'profile'])->name('recruitment.profil');
    Route::get('/recruitment/manajemen', [RecruitmentController::class, 'index'])->name('recruitment.index');
    Route::get('/recruitment/lowongan', [RecruitmentController::class, 'openings'])->name('recruitment.lowongan');
    Route::post('/recruitment/lowongan', [RecruitmentController::class, 'storeOpening'])->name('recruitment.openings.store');
    Route::get('/recruitment/kandidat', [RecruitmentController::class, 'candidates'])->name('recruitment.kandidat');
    Route::post('/recruitment/kandidat', [RecruitmentController::class, 'storeCandidate'])->name('recruitment.candidates.store');
    Route::delete('/recruitment/kandidat/{candidate}', [RecruitmentController::class, 'destroyCandidate'])->name('recruitment.candidates.destroy');

    Route::get('/technician/dashboard', [JobController::class, 'technicianDashboard'])->name('technician.dashboard');
    Route::post('/jobs/{job}/accept', [JobController::class, 'acceptJob'])->name('jobs.accept');
    Route::post('/jobs/{job}/progress', [JobController::class, 'updateProgress'])->name('jobs.progress');

    Route::get('/admin/checklists/create', [ChecklistController::class, 'createTemplate'])->name('admin.createTemplate');
    Route::post('/admin/checklists/store', [ChecklistController::class, 'storeTemplate'])->name('admin.storeTemplate');
    Route::get('/admin/checklists', [ChecklistController::class, 'indexTemplate'])->name('admin.indexTemplate');
    Route::get('/checklists', [ChecklistController::class, 'index'])->name('checklists.index');
    Route::get('/checklists/fill/{type}/{date}', [ChecklistController::class, 'create'])->name('checklists.create');
    Route::post('/checklists/submit', [ChecklistController::class, 'storeAnswer'])->name('checklists.submit');

    Route::resource('/admin/clients', ClientController::class)
        ->names('admin.clients')
        ->only(['index', 'store', 'update', 'destroy']);
});

// Media endpoint for chat attachments (keeps cookie-based auth working for <img>/<video>).
Route::get('/chat-media/{chat}', [ChatMediaController::class, 'show'])
    ->middleware('auth')
    ->name('chat.media.show');

require __DIR__.'/auth.php';
