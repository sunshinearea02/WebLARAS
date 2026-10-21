<?php

/*
|--------------------------------------------------------------------------
| Wilayah pantau LARAS
|--------------------------------------------------------------------------
| Peta Evakuasi menampilkan semua node di Kecamatan Pacet (lintas desa).
| LARAS_DESA = desa utama (pilot) untuk pesan WhatsApp & chatbot.
| Posko memakai koordinat kantor desa/kecamatan dari OpenStreetMap; posko
| SDN Padusan masih perkiraan. Sesuaikan dengan posko resmi BPBD/desa.
*/
return [
    'desa'      => env('LARAS_DESA', 'Padusan'),
    'kecamatan' => env('LARAS_KECAMATAN', 'Pacet'),
    'kabupaten' => env('LARAS_KABUPATEN', 'Mojokerto'),

    // Titik tengah & zoom awal peta (dipakai bila belum ada node)
    'center' => [-7.6680, 112.5480],
    'zoom'   => 14,

    'posko' => [
        [
            'id'        => 'Posko Kecamatan Pacet',
            'nama'      => 'Posko Utama Kecamatan Pacet',
            'lokasi'    => 'Kantor Kecamatan Pacet, Jl. Raya Pandanarum-Pacet, Desa Petak',
            'lat'       => -7.65052,
            'lng'       => 112.53732,
            'kapasitas' => 300,
        ],
        [
            'id'        => 'Posko Balai Desa Padusan',
            'nama'      => 'Posko Balai Desa Padusan',
            'lokasi'    => 'Balai Desa Padusan, Kec. Pacet',
            'lat'       => -7.68309,
            'lng'       => 112.54250,
            'kapasitas' => 150,
        ],
        [
            'id'        => 'Posko SDN Padusan',
            'nama'      => 'Posko Lapangan SDN Padusan',
            'lokasi'    => 'Lapangan SDN Padusan, Kec. Pacet',
            'lat'       => -7.68309,
            'lng'       => 112.53978,
            'kapasitas' => 100,
        ],
        [
            'id'        => 'Posko Balai Desa Claket',
            'nama'      => 'Posko Balai Desa Claket',
            'lokasi'    => 'Balai Desa Claket, Jl. Raya Trawas-Pacet',
            'lat'       => -7.67557,
            'lng'       => 112.56724,
            'kapasitas' => 120,
        ],
        [
            'id'        => 'Posko Balai Desa Cembor',
            'nama'      => 'Posko Balai Desa Cembor',
            'lokasi'    => 'Balai Desa Cembor, Jl. Raya Trawas-Pacet',
            'lat'       => -7.66939,
            'lng'       => 112.57768,
            'kapasitas' => 100,
        ],
        [
            'id'        => 'Posko Balai Desa Sajen',
            'nama'      => 'Posko Balai Desa Sajen',
            'lokasi'    => 'Balai Desa Sajen, Jl. Raya Gondang-Pacet',
            'lat'       => -7.66578,
            'lng'       => 112.53051,
            'kapasitas' => 120,
        ],
        [
            'id'        => 'Posko Balai Desa Kemiri',
            'nama'      => 'Posko Balai Desa Kemiri',
            'lokasi'    => 'Balai Desa Kemiri, Jl. Raya Gondang-Pacet',
            'lat'       => -7.65343,
            'lng'       => 112.52299,
            'kapasitas' => 100,
        ],
    ],
];
