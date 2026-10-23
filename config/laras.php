<?php

/*
|--------------------------------------------------------------------------
| Wilayah pantau LARAS
|--------------------------------------------------------------------------
| Lokasi pilot: Desa Kemiri, Kec. Pacet, Kab. Mojokerto. Peta Evakuasi
| menampilkan semua node di tabel sensor_nodes, stasiun utama (Raspberry Pi
| gateway) dan posko evakuasi di bawah ini.
| LARAS_DESA = desa utama untuk pesan WhatsApp & chatbot.
| Posko memakai koordinat kantor desa/kecamatan dari OpenStreetMap.
| Sesuaikan dengan posko resmi BPBD/desa.
*/
return [
    'desa'      => env('LARAS_DESA', 'Kemiri'),
    'kecamatan' => env('LARAS_KECAMATAN', 'Pacet'),
    'kabupaten' => env('LARAS_KABUPATEN', 'Mojokerto'),

    // Titik tengah & zoom awal peta (dipakai bila belum ada node)
    'center' => [-7.661646, 112.516094],
    'zoom'   => 16,

    // Stasiun utama: Raspberry Pi gateway LoRa + analisis AI (penanda ungu di peta)
    'stasiun' => [
        'nama'   => 'Stasiun Utama LARAS',
        'lokasi' => 'Desa Kemiri, Kec. Pacet',
        'ket'    => 'Raspberry Pi: gateway LoRa, analisis AI, dan pengirim data ke server',
        'lat'    => -7.661646,
        'lng'    => 112.516094,
    ],

    // Posko evakuasi di sisi barat-utara lereng (bekas titik NODE-09 & NODE-10).
    'posko' => [
        [
            'id'        => 'Posko Utara Barat Kemiri',
            'nama'      => 'Posko Utara Barat Kemiri',
            'lokasi'    => 'Sisi utara-barat lereng Desa Kemiri, 506 mdpl, +-270 m dari Stasiun Utama',
            'lat'       => -7.659701,
            'lng'       => 112.514685,
            'kapasitas' => 100,
        ],
        [
            'id'        => 'Posko Barat Laut Kemiri',
            'nama'      => 'Posko Barat Laut Kemiri',
            'lokasi'    => 'Sisi barat laut Desa Kemiri, area relatif datar, 536 mdpl, +-390 m dari Stasiun Utama',
            'lat'       => -7.660199,
            'lng'       => 112.512923,
            'kapasitas' => 100,
        ],
    ],
];
