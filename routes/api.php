<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\RoomApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Rapi Sekolah
|--------------------------------------------------------------------------
| Auth  : Laravel Sanctum (Bearer token)
| Prefix: /api
|--------------------------------------------------------------------------
*/

// ── Public ──────────────────────────────────────────────────────────────
Route::post('/login', [AuthApiController::class, 'login']);

// ── Authenticated ────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::put('/me', [AuthApiController::class, 'updateMe']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Rooms
    Route::get('/rooms', [RoomApiController::class, 'index']);
    Route::get('/rooms/{id}', [RoomApiController::class, 'show'])->whereNumber('id');
    Route::post('/rooms', [RoomApiController::class, 'store']);       // Admin only
    Route::put('/rooms/{id}', [RoomApiController::class, 'update'])->whereNumber('id'); // Admin only
    Route::delete('/rooms/{id}', [RoomApiController::class, 'destroy'])->whereNumber('id'); // Admin only

    // Events
    Route::get('/events/slots', [EventApiController::class, 'slots']);
    Route::get('/events', [EventApiController::class, 'index']);
    Route::post('/events', [EventApiController::class, 'store']);
    Route::get('/events/{id}', [EventApiController::class, 'show'])->whereNumber('id');
    Route::post('/events/{id}/review', [EventApiController::class, 'review'])->whereNumber('id'); // Admin/Waka
    Route::delete('/events/{id}', [EventApiController::class, 'destroy'])->whereNumber('id');

    // Complaints
    Route::get('/complaints', [ComplaintApiController::class, 'index']);
    Route::post('/complaints', [ComplaintApiController::class, 'store']);
    Route::get('/complaints/{id}', [ComplaintApiController::class, 'show'])->whereNumber('id');
    Route::patch('/complaints/{id}/status', [ComplaintApiController::class, 'updateStatus'])->whereNumber('id'); // PIC only
    Route::delete('/complaints/{id}', [ComplaintApiController::class, 'destroy'])->whereNumber('id');

    // Reports (Admin/Waka/PIC)
    Route::get('/reports', [ReportApiController::class, 'index']);
});
