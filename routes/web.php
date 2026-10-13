<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LandscapeController;
use App\Http\Controllers\EvacuationMapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SensorNodeController;

// --- RUTE PUBLIK (Warga / Guest boleh LIHAT saja) ---
Route::get('/', [DashboardController::class, 'index']);
Route::get('/api/sensor-now', [DashboardController::class, 'getLatestData']);
Route::get('/api/sensor-since', [DashboardController::class, 'getSince']);
Route::get('/api/status-history', [DashboardController::class, 'getStatusHistory']);
Route::get('/api/forecast-history', [DashboardController::class, 'getForecastHistory']);
Route::get('/api/weather-bmkg', [DashboardController::class, 'getWeather']);
Route::post('/api/chatbot', [ChatbotController::class, 'chat']);

Route::get('/lanskap', [LandscapeController::class, 'index'])->name('lanskap.index');
Route::get('/peta-evakuasi', [EvacuationMapController::class, 'index'])->name('peta-evakuasi.index');
Route::get('/peta-evakuasi/checkins', [EvacuationMapController::class, 'getCheckins'])->name('peta-evakuasi.checkins-list');
Route::post('/nodes', [SensorNodeController::class, 'store'])->name('nodes.store');
Route::put('/nodes/{node}', [SensorNodeController::class, 'update'])->name('nodes.update');
// --- OTENTIKASI ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // Pendaftaran mandiri kader: akun berstatus PENDING sampai disetujui admin
    Route::get('/daftar', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/daftar', [AuthController::class, 'signup'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Admin bisa langsung membuat akun petugas (kader/admin) dan menyetujui pendaftar baru.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Persetujuan pendaftar baru
    Route::post('/petugas/{user}/setujui', [AuthController::class, 'approve'])->name('petugas.approve');
    Route::delete('/petugas/{user}/tolak', [AuthController::class, 'reject'])->name('petugas.reject');
});

// --- RUTE PROTEKSI (Hanya Admin / Kader yang boleh EDIT) ---
Route::middleware(['auth', 'role:admin,kader'])->group(function () {
    // Data Lanskap
    Route::post('/lanskap', [LandscapeController::class, 'store'])->name('lanskap.store');
    Route::put('/lanskap/{id}', [LandscapeController::class, 'update'])->name('lanskap.update');
    Route::delete('/lanskap/{id}', [LandscapeController::class, 'destroy'])->name('lanskap.destroy');

    // Check-in Warga di Posko
    Route::post('/peta-evakuasi/checkin', [EvacuationMapController::class, 'storeCheckin'])->name('peta-evakuasi.checkin');
    Route::delete('/peta-evakuasi/checkin/{id}', [EvacuationMapController::class, 'destroyCheckin'])->name('peta-evakuasi.checkin.destroy');
});