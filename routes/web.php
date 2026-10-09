<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LandscapeController;
use App\Http\Controllers\EvacuationMapController;


Route::get('/', [DashboardController::class, 'index']);
Route::get('/api/sensor-now', [DashboardController::class, 'getLatestData']);
Route::get('/api/sensor-since', [DashboardController::class, 'getSince']);
Route::get('/api/status-history', [DashboardController::class, 'getStatusHistory']);
Route::get('/api/forecast-history', [DashboardController::class, 'getForecastHistory']);
Route::get('/api/weather-bmkg', [DashboardController::class, 'getWeather']);
Route::post('/api/chatbot', [ChatbotController::class, 'chat']);
Route::get('/lanskap', [LandscapeController::class, 'index'])->name('lanskap.index');
Route::post('/lanskap', [LandscapeController::class, 'store'])->name('lanskap.store');
Route::delete('/lanskap/{id}', [LandscapeController::class, 'destroy'])->name('lanskap.destroy');
Route::get('/peta-evakuasi', [EvacuationMapController::class, 'index'])->name('peta-evakuasi.index');
Route::post('/peta-evakuasi/checkin', [EvacuationMapController::class, 'storeCheckin'])->name('peta-evakuasi.checkin');
Route::get('/peta-evakuasi/checkins', [EvacuationMapController::class, 'getCheckins'])->name('peta-evakuasi.checkins-list');