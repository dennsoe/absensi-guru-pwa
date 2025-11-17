<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Notifications API
    Route::prefix('notifications')->name('api.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });

    // Absensi Status API
    Route::prefix('absensi')->name('api.absensi.')->group(function () {
        Route::post('/check-status', [AbsensiController::class, 'checkStatus'])->name('check-status');
        Route::get('/today', [AbsensiController::class, 'today'])->name('today');
        Route::get('/history', [AbsensiController::class, 'history'])->name('history');
    });

    // Settings API
    Route::prefix('settings')->name('api.settings.')->group(function () {
        Route::get('/{category?}', [SettingsController::class, 'index'])->name('index');
        Route::get('/{category}/{key}', [SettingsController::class, 'show'])->name('show');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
    });
});
