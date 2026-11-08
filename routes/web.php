<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────────────────────
Route::get('/', fn () => view('landing'))->name('landing');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot', [AuthController::class, 'forgotForm'])->name('forgot');
Route::post('/forgot', [AuthController::class, 'forgot']);
Route::get('/verify-otp', [AuthController::class, 'verifyOtpForm'])->name('verify.otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend.otp');
Route::get('/reset', [AuthController::class, 'resetForm'])->name('reset');
Route::post('/reset', [AuthController::class, 'reset']);

Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
Route::get('/calendar/api', [CalendarController::class, 'api'])->name('calendar.api');

// Serve uploaded storage files (essential for Vercel serverless)
Route::get('/storage/{path}', function ($path) {
    $filePath = config('filesystems.disks.public.root').'/'.$path;
    if (! file_exists($filePath)) {
        abort(404);
    }
    $mime = mime_content_type($filePath) ?: 'image/jpeg';

    return response()->file($filePath, ['Content-Type' => $mime]);
})->where('path', '.*');

// ── Authenticated ────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/profile', [AuthController::class, 'profileForm'])->name('profile');
    Route::post('/profile/save', [AuthController::class, 'profileSave'])->name('profile.save');
    Route::post('/profile/password', [AuthController::class, 'passwordSave'])->name('profile.password');
    Route::post('/notifications/read', [AuthController::class, 'readNotifications'])->name('notifications.read');

    // Events (Admin, Guru, Waka)
    Route::middleware('role:admin,guru,waka')->group(function () {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::get('/events/slots', [EventController::class, 'slots'])->name('events.slots');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
    });

    // Event review + move (Admin & Waka)
    Route::middleware('role:admin,waka')->group(function () {
        Route::post('/events/review', [EventController::class, 'review'])->name('events.review');
        Route::post('/events/move', [EventController::class, 'move'])->name('events.move');

        // Complaint approve/reject (Admin & Waka)
        Route::post('/complaints/approve', [ComplaintController::class, 'approve'])->name('complaints.approve');
        Route::post('/complaints/reject', [ComplaintController::class, 'reject'])->name('complaints.reject');
    });

    // Admin & Waka Rooms Management
    Route::middleware('role:admin,waka')->group(function () {
        Route::get('/admin/rooms', [AdminController::class, 'rooms'])->name('admin.rooms');
        Route::post('/admin/rooms', [AdminController::class, 'roomSave'])->name('admin.rooms.save');
        Route::post('/admin/rooms/{id}/delete', [AdminController::class, 'roomDelete'])->name('admin.rooms.delete');

        // Laporan Sarpras: Harian, Mingguan, Bulanan
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    });

    // Admin only management (User management)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/admin/users/template', [AdminController::class, 'userTemplate'])->name('admin.users.template');
        Route::post('/admin/users/import', [AdminController::class, 'userImport'])->name('admin.users.import');
        Route::post('/admin/users', [AdminController::class, 'userSave'])->name('admin.users.save');
        Route::post('/admin/users/delete', [AdminController::class, 'userDelete'])->name('admin.users.delete');
    });

    // Complaints (all roles)
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');

    // Complaint create (Admin, Guru, Waka)
    Route::middleware('role:admin,guru,waka')->group(function () {
        Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    });

    // Complaint status update (PIC only)
    Route::middleware('role:pic')->group(function () {
        Route::post('/complaints/status', [ComplaintController::class, 'status'])->name('complaints.status');
    });

    // Delete complaint (Admin, Guru, Waka)
    Route::middleware('role:admin,guru,waka')->group(function () {
        Route::post('/complaints/delete', [ComplaintController::class, 'delete'])->name('complaints.delete');
    });
});
