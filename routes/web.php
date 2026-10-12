<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LandscapeController;
use App\Http\Controllers\EvacuationMapController;
use App\Http\Controllers\AuthController;

// --- RUTE PUBLIK ---
Route::get('/', [DashboardController::class, 'index']);
Route::get('/api/sensor-now', [DashboardController::class, 'getLatestData']);
Route::get('/api/sensor-since', [DashboardController::class, 'getSince']);
Route::get('/api/status-history', [DashboardController::class, 'getStatusHistory']);
Route::get('/api/forecast-history', [DashboardController::class, 'getForecastHistory']);
Route::get('/api/weather-bmkg', [DashboardController::class, 'getWeather']);
Route::post('/api/chatbot', [ChatbotController::class, 'chat']);

// Halaman Lanskap & Peta Evakuasi (Dapat diakses Publik/Guest)
Route::get('/lanskap', [LandscapeController::class, 'index'])->name('lanskap.index');
Route::get('/peta-evakuasi', [EvacuationMapController::class, 'index'])->name('peta-evakuasi.index');
Route::post('/peta-evakuasi/checkin', [EvacuationMapController::class, 'storeCheckin'])->name('peta-evakuasi.checkin');
Route::get('/peta-evakuasi/checkins', [EvacuationMapController::class, 'getCheckins'])->name('peta-evakuasi.checkins-list');

// Otentikasi
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- RUTE PROTEKSI (Hanya Admin / Kader) ---
Route::middleware(['auth'])->group(function () {
    Route::post('/lanskap', [LandscapeController::class, 'store'])->name('lanskap.store');
    Route::delete('/lanskap/{id}', [LandscapeController::class, 'destroy'])->name('lanskap.destroy');
});