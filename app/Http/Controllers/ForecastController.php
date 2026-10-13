<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;

class ForecastController extends Controller
{
    public function latest()
    {
        $data = SensorLog::latest('timestamp')->first();

        if (! $data) {
            return response()->json(['error' => 'Tidak ada data sensor.'], 404);
        }

        return response()->json([
            'status' => $data->status,
            'forecast_status' => $data->forecast_status,
            'rf_confidence' => $data->rf_confidence,
            'lstm_confidence' => $data->lstm_confidence,
            'forecast_confidence' => $data->forecast_confidence,
            'vibration' => $data->vibration,
            'moisture_percent' => $data->moisture_percent,
            'pitch' => $data->pitch,
            'roll' => $data->roll,
            'timestamp' => $data->timestamp,
        ]);
    }
}
