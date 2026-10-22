<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LandscapeController;
use App\Http\Controllers\EvacuationMapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SensorNodeController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\HazardReportController;


// Otomasi n8n: pemeriksaan peringatan berkala (dilindungi header X-LARAS-Token = N8N_TOKEN)
Route::post('/api/n8n/alert-tick', [AutomationController::class, 'alertTick'])
    ->middleware('throttle:30,1')
    ->name('n8n.alert-tick');
    
// --- RUTE PUBLIK (Warga / Guest boleh LIHAT saja) ---
Route::get('/', [DashboardController::class, 'index']);
Route::get('/api/sensor-now', [DashboardController::class, 'getLatestData']);
Route::get('/api/sensor-since', [DashboardController::class, 'getSince']);
Route::get('/api/status-history', [DashboardController::class, 'getStatusHistory']);
Route::get('/api/forecast-history', [DashboardController::class, 'getForecastHistory']);
Route::get('/api/weather-bmkg', [DashboardController::class, 'getWeather']);
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])->middleware('throttle:20,1');

Route::get('/lanskap', [LandscapeController::class, 'index'])->name('lanskap.index');
Route::get('/peta-evakuasi', [EvacuationMapController::class, 'index'])->name('peta-evakuasi.index');
Route::get('/peta-evakuasi/checkins', [EvacuationMapController::class, 'getCheckins'])->name('peta-evakuasi.checkins-list');

// Laporan hambatan jalan: warga boleh melihat & melapor tanpa login (dibatasi 5 laporan / 10 menit)
Route::get('/peta-evakuasi/laporan-jalan', [HazardReportController::class, 'index'])->name('laporan-jalan.index');
Route::post('/peta-evakuasi/laporan-jalan', [HazardReportController::class, 'store'])->middleware('throttle:5,10')->name('laporan-jalan.store');

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

    // Laporan hambatan jalan: verifikasi / hapus
    Route::post('/peta-evakuasi/laporan-jalan/{report}/verifikasi', [HazardReportController::class, 'verify'])->name('laporan-jalan.verify');
    Route::delete('/peta-evakuasi/laporan-jalan/{report}', [HazardReportController::class, 'destroy'])->name('laporan-jalan.destroy');

    // Node sensor: tambah / ubah koordinat GPS
    Route::post('/nodes', [SensorNodeController::class, 'store'])->name('nodes.store');
    Route::put('/nodes/{node}', [SensorNodeController::class, 'update'])->name('nodes.update');

    // Verifikasi peringatan oleh kader + kelola nomor WA warga (berisi nomor HP: wajib login)
    Route::get('/verifikasi-peringatan', [VerifikasiController::class, 'index'])->name('verifikasi.index');
    Route::post('/verifikasi-peringatan/{verification}/kirim', [VerifikasiController::class, 'approve'])->name('verifikasi.approve');
    Route::post('/verifikasi-peringatan/{verification}/tolak', [VerifikasiController::class, 'reject'])->name('verifikasi.reject');
    Route::post('/verifikasi-peringatan/warga', [VerifikasiController::class, 'storeWarga'])->name('verifikasi.warga.store');
    Route::delete('/verifikasi-peringatan/warga/{contact}', [VerifikasiController::class, 'destroyWarga'])->name('verifikasi.warga.destroy');
});