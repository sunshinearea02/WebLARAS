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

    'posko' => [
        [
            'id'        => 'Posko Balai Desa Kemiri',
            'nama'      => 'Posko Balai Desa Kemiri',
            'lokasi'    => 'Balai Desa Kemiri, Jl. Raya Gondang-Pacet',
            'lat'       => -7.65343,
            'lng'       => 112.52299,
            'kapasitas' => 150,
        ],
        [
            // Titik kumpul di sisi timur (samping lereng, bukan di bawahnya): jalan permukiman,
            // datar (+-0 derajat), 533 mdpl, +-1 km dari stasiun. Koordinat dari analisis medan,
            // belum bangunan resmi: ganti dengan lokasi yang disepakati desa/BPBD.
            'id'        => 'Posko Titik Kumpul Timur',
            'nama'      => 'Posko Titik Kumpul Timur',
            'lokasi'    => 'Jalan permukiman perbatasan Kemiri - Sajen, Kec. Pacet',
            'lat'       => -7.662144,
            'lng'       => 112.525401,
            'kapasitas' => 100,
        ],
        [
            'id'        => 'Posko Kecamatan Pacet',
            'nama'      => 'Posko Utama Kecamatan Pacet',
            'lokasi'    => 'Kantor Kecamatan Pacet, Jl. Raya Pandanarum-Pacet, Desa Petak',
            'lat'       => -7.65052,
            'lng'       => 112.53732,
            'kapasitas' => 300,
        ],
    ],
];
