<?php

/*
|--------------------------------------------------------------------------
| Wilayah pantau LARAS
|--------------------------------------------------------------------------
| Peta Evakuasi difokuskan ke satu desa. Node yang kolom `desa`-nya sama
| dengan LARAS_DESA ditampilkan di peta; posko evakuasi desa itu diatur di
| bawah. Koordinat saat ini adalah data contoh (dummy) untuk demo.
*/
return [
    'desa'      => env('LARAS_DESA', 'Padusan'),
    'kecamatan' => env('LARAS_KECAMATAN', 'Pacet'),
    'kabupaten' => env('LARAS_KABUPATEN', 'Mojokerto'),

    // Titik tengah & zoom awal peta (dipakai bila belum ada node)
    'center' => [-7.6815, 112.5405],
    'zoom'   => 15,

    'posko' => [
        [
            'id'        => 'Posko Balai Desa Padusan',
            'nama'      => 'Posko Balai Desa Padusan',
            'lokasi'    => 'Balai Desa Padusan, Kec. Pacet',
            'lat'       => -7.67480,
            'lng'       => 112.54420,
            'kapasitas' => 150,
        ],
        [
            'id'        => 'Posko SDN Padusan',
            'nama'      => 'Posko Lapangan SDN Padusan',
            'lokasi'    => 'Lapangan SDN Padusan, Kec. Pacet',
            'lat'       => -7.67760,
            'lng'       => 112.54790,
            'kapasitas' => 100,
        ],
    ],
];