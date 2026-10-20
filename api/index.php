<?php

/**
 * Pintu masuk Laravel untuk Vercel (runtime vercel-php).
 * Semua request yang bukan file statis diarahkan ke sini oleh vercel.json.
 *
 * Di Vercel file ini berada di /api/index.php. Tanpa koreksi di bawah, Laravel
 * mengira aplikasi terpasang di sub-folder "/api" dan memotong awalan itu,
 * sehingga route seperti /api/sensor-now terbaca /sensor-now (404).
 */
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';

require __DIR__ . '/../public/index.php';
