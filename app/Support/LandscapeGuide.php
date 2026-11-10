<?php

namespace App\Support;

/**
 * Isi halaman "Panduan Pengisian Data Lanskap" untuk kader.
 *
 * Setiap kolom form lanskap punya penjelasan cara menilai di lapangan,
 * ciri tiap pilihan, dasar dari sumber resmi, dan daftar sumbernya.
 * Kunci 'options' HARUS sama persis dengan LandscapeAssessment::options()
 * supaya panduan selalu sesuai dengan pilihan di form (dicek di LandscapeGuideTest).
 */
class LandscapeGuide
{
    /** Bobot tiap parameter pada skor lereng (static_risk.py di Raspberry Pi). */
    public static function weights(): array
    {
        return [
            ['label' => 'Keretakan tanah', 'weight' => 25],
            ['label' => 'Kemiringan lereng', 'weight' => 20],
            ['label' => 'Vegetasi dominan', 'weight' => 15],
            ['label' => 'Kondisi drainase', 'weight' => 15],
            ['label' => 'Riwayat longsor', 'weight' => 15],
            ['label' => 'Jenis tanah', 'weight' => 10],
        ];
    }

    /** Panduan per kolom form. */
    public static function fields(): array
    {
        return [
            'dominant_vegetation' => [
                'label' => 'Jenis Vegetasi Dominan',
                'what' => 'Penutup lahan yang paling luas (lebih dari separuh area) dalam radius 50 m dari node. Akar pohon yang dalam dan menyebar mengikat tanah, sedangkan rumput dan semak berakar serabut dangkal sehingga kurang mampu menahan lereng.',
                'steps' => [
                    'Berdiri di samping node, lalu foto ke empat arah: atas lereng, bawah lereng, kiri, dan kanan.',
                    'Perkirakan jenis penutup lahan yang menutupi lebih dari separuh area di foto.',
                    'Cari tanda campur tangan manusia: tunggul pohon, jalur bekas angkut kayu, bekas bakaran, tanaman yang ditanam berbaris, atau tanah yang dicangkul.',
                    'Cocokkan dengan ciri pada tabel di bawah, lalu tulis nama tanaman utamanya di Catatan.',
                ],
                'options' => [
                    'Hutan lebat/primer' => 'Hutan yang masih utuh dan belum pernah ditebang. Tajuk pohon rapat dan bertingkat sehingga langit sulit terlihat dari bawah. Pohon besar dari banyak jenis tumbuh tidak beraturan, serasah daun tebal, dan tumbuhan bawah jarang karena teduh. Tidak ada tunggul, jalur bekas angkut kayu, bekas bakaran, atau tanaman budidaya. Di Jawa umumnya hanya tersisa di kawasan konservasi atau hutan lindung.',
                    'Hutan sekunder/semak belukar' => 'Hutan yang sudah pernah ditebang lalu tumbuh kembali. Masih terlihat tunggul atau jalur bekas tebangan, pohonnya lebih kecil dan umurnya hampir seragam, tajuknya berlubang-lubang, dan banyak tumbuhan perintis. Termasuk semak belukar: tumbuhan berkayu rendah yang rapat dan sulit ditembus, biasanya di bekas hutan atau bekas ladang yang ditinggalkan.',
                    'Perkebunan/tanaman keras' => 'Pohon yang sengaja ditanam dan tidak diganti minimal 2 tahun, dipanen buah, bunga, getah, atau daunnya tanpa menebang pohon: kopi, cengkeh, alpukat, durian, kelapa, atau bambu. Biasanya ditanam berbaris dengan jarak teratur dan tanah di bawahnya sering dibersihkan. Hutan tanaman pinus atau mahoni milik Perhutani juga dipilih di sini (lihat bagian Perhatikan).',
                    'Lahan pertanian/tanaman semusim' => 'Ladang atau tegalan berisi tanaman yang dipanen kurang dari setahun dan tanahnya rutin dicangkul: sayuran (kubis, wortel, kentang, cabai, tomat), jagung, singkong, ubi, atau padi gogo. Biasanya ada bedengan atau guludan, dan di antara musim tanam tanahnya bisa terbuka sementara. Sawah di lereng juga dipilih di sini.',
                    'Rumput/lahan terbuka' => 'Didominasi rumput tinggi atau rendah dengan pohon yang sangat jarang: alang-alang, padang penggembalaan, atau kebun rumput pakan ternak.',
                    'Tanpa vegetasi/gundul' => 'Hampir tidak ada tumbuhan (tutupan kurang dari ±4%) dan kondisi itu bertahan berbulan-bulan: tanah atau batu terlihat jelas, misalnya bekas longsor, bekas galian tanah atau batu, atau lahan yang baru dibuka atau dibakar. Ladang yang hanya beberapa minggu tidak ditanami tidak termasuk di sini.',
                ],
                'basis' => [
                    ['text' => 'Hutan lahan kering primer adalah hutan yang masih kompak dan belum menampakkan bekas penebangan, sedangkan hutan sekunder sudah menampakkan alur dan bercak bekas tebang. Semak belukar didominasi vegetasi rendah alami dan biasanya merupakan bekas hutan. Perkebunan adalah lahan tanpa pergantian tanaman selama 2 tahun. Daerah tak bervegetasi memiliki tutupan vegetasi kurang dari 4% selama lebih dari 10 bulan.', 'source' => 'sni7645'],
                    ['text' => 'Tingkat kerawanan dari vegetasi: tinggi untuk alang-alang, rumput, semak, dan perdu; sedang untuk tumbuhan berdaun jarum seperti cemara dan pinus; rendah untuk pohon berakar tunjang yang menyebar seperti jati, kemiri, mahoni, dan asam jawa. Lereng yang dijadikan sawah, ladang, atau hutan pinus termasuk pola tanam yang tidak tepat.', 'source' => 'pu22'],
                    ['text' => 'Tanah longsor banyak terjadi di persawahan dan perladangan di lereng terjal, karena akar tanamannya kurang kuat mengikat tanah dan tanahnya menjadi jenuh air. Tanah longsor juga banyak terjadi di daerah yang gundul.', 'source' => 'esdm'],
                ],
                'tips' => [
                    'Kalau dua jenis sama luasnya, pilih yang lebih terbuka (lebih berisiko), lalu tulis keduanya di Catatan.',
                    'Hutan pinus atau cemara: pilih "Perkebunan/tanaman keras", lalu tulis "pinus" di Catatan. Menurut Permen PU 22/2007, tumbuhan berdaun jarum kurang kuat menahan lereng dibanding pohon berakar tunjang.',
                    'Jangan memilih "Perkebunan" hanya karena ada beberapa pohon di pinggir kebun sayur. Yang dinilai adalah yang paling luas.',
                ],
                'sources' => ['sni7645', 'pu22', 'esdm'],
            ],

            'slope_classification' => [
                'label' => 'Klasifikasi Kemiringan Lereng',
                'what' => 'Seberapa curam lereng tempat node dipasang, dalam persen. Persen berarti berapa cm tanah naik untuk setiap 1 m jarak mendatar. Makin curam, makin besar gaya yang mendorong tanah turun.',
                'steps' => [
                    'Cara HP: letakkan papan atau tongkat lurus 1–2 m searah turunan lereng di samping node (hindari gundukan atau lubang).',
                    'Letakkan HP di atas papan, buka aplikasi klinometer atau waterpas, lalu baca sudutnya dalam derajat.',
                    'Ulangi di tiga titik: sedikit di atas node, di samping node, dan sedikit di bawah node. Pakai nilai tengahnya.',
                    'Ubah derajat ke persen dengan tabel di bawah, atau dengan rumus persen = tan(derajat) × 100.',
                    'Cara tanpa HP: ikat tali di titik atas lereng, tarik mendatar sejauh 1 m (cek datar dengan waterpas atau selang berisi air), lalu ukur jarak tegak dari tali ke tanah dalam cm. Angka cm itu sama dengan kemiringan dalam persen.',
                ],
                'options' => [
                    'Datar (0-8%)' => 'Naik 0–8 cm per 1 m mendatar (0–4,6°). Tanah hampir rata dan air hujan menggenang atau mengalir pelan.',
                    'Landai (8-15%)' => 'Naik 8–15 cm per 1 m (4,6–8,5°). Masih nyaman dilalui sambil berjalan biasa.',
                    'Sedang (15–25%)' => 'Naik 15–25 cm per 1 m (8,5–14°). Terasa menanjak saat berjalan. Mulai 15%, lereng sudah termasuk kriteria kawasan rawan longsor menurut Permen PU 22/2007.',
                    'Curam (25–45%)' => 'Naik 25–45 cm per 1 m (14–24°). Perlu berhati-hati saat turun, dan air hujan mengalir deras di permukaan.',
                    'Sangat curam (> 45%)' => 'Naik lebih dari 45 cm per 1 m (lebih dari 24°). Sulit didaki tanpa berpegangan pada tanaman atau tali. Lereng di atas 40% masuk Zona Tipe A, zona berpotensi longsor dengan kerawanan tertinggi menurut Permen PU 22/2007.',
                ],
                'basis' => [
                    ['text' => 'Kawasan rawan longsor ditetapkan antara lain pada kemiringan lereng 15% hingga 70%. Zona Tipe A berada pada lereng di atas 40%, Tipe B pada 21–40%, dan Tipe C pada 0–20%. Kemiringan lereng adalah indikator dengan bobot terbesar (30%) pada aspek fisik alami.', 'source' => 'pu22'],
                    ['text' => 'Lima kelas kemiringan (0–8%, 8–15%, 15–25%, 25–45%, lebih dari 45%) mengikuti pembagian kelas lereng yang umum dipakai dalam penetapan kawasan lindung. Sebagian pustaka memakai batas 25–40% dan lebih dari 40% untuk dua kelas teratas.', 'source' => 'sk837'],
                    ['text' => 'Lereng terjal memperbesar gaya pendorong. Tanah lempung tebal (lebih dari 2,5 m) pada lereng lebih dari 22° berpotensi longsor, terutama saat hujan.', 'source' => 'esdm'],
                ],
                'tips' => [
                    'Hati-hati: 45% BUKAN 45°. Lereng 45° sama dengan 100%. Batas "sangat curam" di form adalah 45% ≈ 24°.',
                    'Ukur searah turunan lereng yang paling curam, bukan miring menyamping.',
                    'Kalau hasil ukur tepat di batas kelas, pilih kelas yang lebih curam.',
                ],
                'sources' => ['pu22', 'sk837', 'esdm'],
            ],

            'drainage_condition' => [
                'label' => 'Kondisi Drainase',
                'what' => 'Ke mana air hujan dan air buangan mengalir di sekitar dan di atas node. Air yang masuk dan menumpuk di dalam lereng menambah berat tanah dan membuat bidang gelincir licin, penyebab utama longsor saat hujan.',
                'steps' => [
                    'Telusuri selokan, parit, saluran teras, talang, dan pipa buangan rumah di sekitar node, terutama di bagian atas lereng.',
                    'Ikuti alirannya sampai ujung: apakah air dibuang keluar lereng (ke sungai atau saluran besar), atau berhenti di tengah lereng.',
                    'Periksa isi dan dinding saluran: lancar, tertutup sampah atau lumpur, retak, atau bocor.',
                    'Cari jejak aliran air: alur gerusan kecil, endapan lumpur di kaki lereng, genangan, atau rembesan dan mata air di lereng.',
                    'Bila aman, amati lagi saat atau sesaat setelah hujan untuk melihat ke mana air mengalir.',
                ],
                'options' => [
                    'Baik, berfungsi lancar' => 'Saluran ada, dindingnya utuh, bersih, dan dipelihara. Air dialirkan sampai keluar lereng. Sehari setelah hujan tidak ada genangan, tidak ada alur gerusan baru, dan tidak ada rembesan di bawah lereng.',
                    'Tidak ada saluran drainase' => 'Tidak ada saluran buatan, sehingga air hujan mengalir bebas di permukaan atau meresap. Biasanya terlihat alur gerusan kecil, endapan lumpur di kaki lereng, atau genangan di cekungan.',
                    'Ada tapi tersumbat/rusak' => 'Saluran ada tetapi penuh sampah, lumpur, atau rumput; dindingnya retak, jebol, atau bocor; atau ujungnya berhenti di tengah lereng sehingga air meluap dan masuk ke lereng. Termasuk talang atau pipa buangan rumah yang diarahkan ke lereng, serta kolam atau sawah di lereng atas yang airnya merembes. Ini kondisi paling berbahaya karena air terkumpul lalu masuk di satu titik.',
                ],
                'basis' => [
                    ['text' => 'Tingkat kerawanan drainase: tinggi bila sistem drainase tidak memadai dan tidak ada usaha perbaikan; sedang bila agak memadai dan ada usaha perbaikan; rendah bila memadai dan salurannya dipelihara. Pencetakan kolam yang airnya merembes ke dalam lereng juga meningkatkan kerawanan.', 'source' => 'pu22'],
                    ['text' => 'Tata air lereng berkerawanan tinggi bila sering muncul rembesan atau mata air pada lereng.', 'source' => 'pu22'],
                    ['text' => 'Munculnya mata air baru secara tiba-tiba adalah gejala umum tanah longsor. Jangan mencetak sawah atau membuat kolam di lereng bagian atas dekat permukiman.', 'source' => 'esdm'],
                    ['text' => 'Air yang mengalir di lereng atau menggenang di tempat yang sebelumnya tidak pernah tergenang, serta pipa air atau saluran buangan yang bocor, termasuk tanda potensi longsor.', 'source' => 'usgs_signs'],
                ],
                'tips' => [
                    'Rembesan air atau mata air baru di lereng adalah tanda bahaya. Catat di Catatan dan laporkan ke BPBD.',
                    'Saluran yang ujungnya berhenti di tengah lereng sama berbahayanya dengan saluran tersumbat.',
                ],
                'sources' => ['pu22', 'esdm', 'usgs_signs'],
            ],

            'soil_cracking' => [
                'label' => 'Keretakan Tanah',
                'what' => 'Lebar retakan tanah terbesar dalam radius 50 m dari node, terutama retakan yang memanjang sejajar arah tebing. Retakan seperti ini sering menjadi tanda awal tanah mulai bergerak, dan juga menjadi jalan masuk air hujan ke dalam lereng.',
                'steps' => [
                    'Telusuri area sekitar node, terutama bagian atas lereng dan tepi tebing.',
                    'Ukur lebar retakan terbesar dengan penggaris, lalu ukur panjangnya dengan meteran.',
                    'Masukkan lidi atau kawat ke dalam retakan untuk memperkirakan kedalamannya.',
                    'Perhatikan apakah tanah di salah satu sisi retakan lebih rendah dari sisi lainnya.',
                    'Foto retakan dengan penggaris di sampingnya, lalu tandai kedua ujungnya dengan patok atau cat supaya perubahannya bisa dibandingkan di survei berikutnya.',
                ],
                'options' => [
                    'Tidak ada' => 'Tidak ditemukan retakan memanjang di tanah, tebing, jalan, atau dinding penahan di sekitar node.',
                    'Ringan (< 2 cm)' => 'Retakan selebar retak rambut sampai kurang dari 2 cm (kira-kira kurang dari lebar dua jari), tanpa beda tinggi antara kedua sisinya.',
                    'Sedang (2 - 5 cm)' => 'Retakan selebar 2–5 cm (kira-kira muat dua sampai tiga jari), biasanya memanjang beberapa meter.',
                    'Parah (> 5 cm)' => 'Retakan lebih dari 5 cm; atau tanah di satu sisi retakan sudah turun lebih rendah; atau beberapa retakan bersambung membentuk lengkung seperti tapal kuda. Segera laporkan ke BPBD.',
                ],
                'basis' => [
                    ['text' => 'Gejala umum tanah longsor: munculnya retakan di lereng yang sejajar dengan arah tebing, biasanya setelah hujan. Retakan tanah sebaiknya segera ditutup dan dipadatkan agar air tidak masuk.', 'source' => 'esdm'],
                    ['text' => 'Musim kering yang panjang membuat tanah permukaan retak dan merekah. Saat hujan pertama yang deras, air masuk lewat retakan, terkumpul di dasar lereng, dan dapat memicu longsor.', 'source' => 'esdm'],
                    ['text' => 'Retakan baru, tanah yang menggembung, atau perubahan bentuk di tanah, jalan, dan permukaan lain adalah tanda potensi longsor. Retakan baru pada dinding atau pondasi bangunan juga termasuk.', 'source' => 'usgs_signs'],
                ],
                'tips' => [
                    'Retak kering musiman berpola seperti sarang lebah, dangkal, dan muncul di tanah liat saat kemarau. Kolom ini diisi berdasarkan retakan yang memanjang searah tebing. Retak kering tetap ditulis di Catatan dan sebaiknya ditutup sebelum musim hujan.',
                    'Retakan baru, atau retakan yang memanjang atau melebar dibanding survei sebelumnya, segera laporkan ke BPBD tanpa menunggu survei berikutnya.',
                    'Catat juga retakan pada rumah, jalan, atau dinding penahan, serta pintu dan jendela yang tiba-tiba sulit ditutup.',
                ],
                'sources' => ['esdm', 'usgs_signs'],
            ],

            'soil_type' => [
                'label' => 'Jenis Tanah',
                'what' => 'Jenis tanah di kedalaman sensor (25–75 cm). Tanah liat menahan air lama dan menjadi lembek serta licin saat jenuh. Tanah gembur dan mudah lolos air yang menumpang di atas lapisan kedap juga rawan, karena air berhenti di batas kedua lapisan dan menjadi bidang gelincir.',
                'steps' => [
                    'Ambil segenggam tanah (±25 gram, seukuran bola pingpong) dari lubang pemasangan probe di kedalaman 25–75 cm, bukan dari permukaan.',
                    'Perhatikan dulu tanah keringnya: berapa banyak batu dan kerikil, apa warnanya, dan apakah terasa ringan dan gembur.',
                    'Buang batu dan akar, lalu basahi sedikit demi sedikit sambil diremas sampai lentur seperti plastisin. Kalau terlalu basah, tambahkan tanah kering.',
                    'Bentuk bola berdiameter ±3 cm. Kalau tidak bisa membentuk bola, berarti tanah berpasir.',
                    'Letakkan bola di antara ibu jari dan telunjuk, lalu dorong keluar dengan ibu jari sampai membentuk pita yang menggantung. Ukur panjang pita sebelum patah: kurang dari 2,5 cm, 2,5–5 cm, atau lebih dari 5 cm.',
                    'Basahi sedikit tanah di telapak tangan, lalu gosok dengan jari: kasar berpasir, licin seperti bedak, atau lengket.',
                ],
                'options' => [
                    'Tanah berbatu/berkerikil' => 'Lebih dari separuh isinya batu atau kerikil (butiran lebih besar dari ±2 mm) sehingga sulit dicangkul dan sulit dibentuk bola.',
                    'Tanah berpasir' => 'Terasa kasar dan butirannya terlihat. Tidak bisa dibentuk bola, atau bisa dibentuk bola tetapi tidak bisa dibentuk pita. Cepat kering dan cepat meloloskan air.',
                    'Tanah vulkanik (andosol)' => 'Hitam atau cokelat sangat gelap, gembur dan remah, terasa ringan saat digenggam, licin seperti berlemak saat dipilin tetapi tidak lengket, dan cepat menyerap air. Terbentuk dari abu gunung api, umum di lereng gunung seperti kawasan Pacet. Bila ciri ini jelas, pilih andosol walaupun hasil uji pitanya sedang.',
                    'Tanah lanau' => 'Saat basah terasa licin dan halus seperti bedak, tidak kasar dan tidak lengket. Saat kering terasa seperti tepung. Pitanya pendek sampai sedang (kurang dari 5 cm).',
                    'Tanah liat/lempung' => 'Lengket dan mudah dibentuk, pita lebih dari 5 cm (atau 2,5–5 cm tetapi jelas lengket), dan permukaannya mengilap saat digosok. Saat kering keras dan retak-retak, saat basah lembek dan sangat licin.',
                    'Tidak diketahui' => 'Pilih ini bila ragu. Skor yang dipakai berada di tengah, jadi lebih aman daripada menebak.',
                ],
                'basis' => [
                    ['text' => 'Uji pita: tanah ±25 gram dibasahi sampai bisa dibentuk, lalu dibuat bola ±3 cm. Tidak bisa dibentuk bola berarti pasir; bisa dibentuk bola tetapi tidak bisa dibentuk pita berarti pasir berlempung. Pita patah di 2,5 cm atau kurang berarti lempung (loam), 2,5–5 cm berarti lempung berliat, dan lebih dari 5 cm berarti liat. Rasa kasar menandakan pasir, licin menandakan debu (lanau).', 'source' => 'fao'],
                    ['text' => 'Tanah lempung atau tanah liat tebal (lebih dari 2,5 m) pada lereng lebih dari 22° berpotensi longsor, karena lembek saat terkena air dan pecah saat panas. Tanah pelapukan hasil letusan gunung api umumnya berupa lempung dengan sedikit pasir dan subur.', 'source' => 'esdm'],
                    ['text' => 'Kondisi tanah berkerawanan tinggi bila lereng tersusun dari tanah penutup tebal (lebih dari 2 m) yang gembur dan mudah lolos air, menumpang di atas batuan yang lebih padat dan kedap seperti andesit, breksi andesit, tuf, napal, dan batu lempung.', 'source' => 'pu22'],
                ],
                'tips' => [
                    'Urutan memutuskan: cek batu dan kerikil dulu, lalu ciri andosol (gelap, gembur, ringan), baru uji pita dan uji gosok.',
                    'Bila ragu, tanyakan ke penyuluh pertanian (PPL) desa atau Dinas Pertanian.',
                    'Tulis di Catatan bila di bawah tanah gembur ditemukan lapisan yang jauh lebih keras atau padat, karena batas lapisan seperti itu bisa menjadi bidang gelincir.',
                ],
                'sources' => ['fao', 'nrcs', 'esdm', 'pu22'],
            ],

            'landslide_history' => [
                'label' => 'Riwayat Kejadian Longsor',
                'what' => 'Apakah lereng yang sama (di area node, atau lereng di atas dan bawahnya yang bersambung) pernah longsor, kapan pun dan sebesar apa pun. Bekas longsoran lama mudah bergerak lagi.',
                'steps' => [
                    'Tanyakan ke warga yang paling lama tinggal di sekitar lokasi, pemilik lahan, dan perangkat desa.',
                    'Cek catatan kejadian di BPBD Kabupaten Mojokerto atau di kantor desa.',
                    'Cek Data Informasi Bencana Indonesia (DIBI) BNPB untuk kecamatan setempat.',
                    'Cari ciri bekas longsoran lama di lapangan (lihat Dasar dari Sumber Resmi di bawah).',
                ],
                'options' => [
                    'Ada' => 'Ada keterangan warga atau catatan resmi tentang longsor di lereng yang sama, atau ditemukan ciri bekas longsoran lama di lapangan.',
                    'Tidak Ada' => 'Tidak ada keterangan warga, tidak ada catatan resmi, dan tidak ditemukan ciri bekas longsoran.',
                ],
                'basis' => [
                    ['text' => 'Ciri bekas longsoran lama: tebing terjal yang panjang dan melengkung membentuk tapal kuda; sering dijumpai mata air dan pepohonan yang relatif lebat karena tanahnya gembur dan subur; bagian atas badan longsoran relatif landai; dijumpai longsoran-longsoran kecil, terutama di tebing lembah; dan alur lembah yang tebingnya retak-retak.', 'source' => 'esdm'],
                    ['text' => 'Pohon, tiang listrik, atau pagar yang miring dan berubah bentuk di lereng adalah tanda tanah bergerak.', 'source' => 'usgs_signs'],
                    ['text' => 'Adanya gerakan tanah termasuk kriteria penetapan kawasan rawan bencana longsor.', 'source' => 'pu22'],
                ],
                'tips' => [
                    'Tulis di Catatan: tahun kejadian, perkiraan ukuran, dan sumber informasinya (nama warga atau instansi).',
                    'Bila hanya menemukan ciri di lapangan tanpa keterangan warga, tetap pilih "Ada" dan jelaskan cirinya di Catatan.',
                ],
                'sources' => ['esdm', 'usgs_signs', 'dibi', 'pu22'],
            ],

            'artificial_slope_cutting' => [
                'label' => 'Pemotongan Lereng Buatan Manusia',
                'what' => 'Apakah ada tebing hasil galian manusia di sekitar node. Memotong kaki lereng menghilangkan penahan alami tanah di atasnya, apalagi bila dipotong tegak.',
                'steps' => [
                    'Telusuri lereng di atas dan bawah node dalam radius 50 m.',
                    'Cari dinding tanah yang tegak atau hampir tegak dengan sisi rata, bekas cangkul atau alat berat, rumah yang menempel ke tebing, jalan yang memotong lereng, atau bekas galian tanah dan batu.',
                    'Perkirakan tinggi tebing galian dan sudutnya, lalu periksa apakah sudah diberi dinding penahan.',
                    'Perhatikan juga timbunan tanah di lembah atau di bawah lereng untuk memperluas lahan.',
                ],
                'options' => [
                    'Ada' => 'Ada tebing hasil galian manusia di area pengamatan, untuk jalan, rumah, kolam, saluran, atau tambang tanah dan batu, baik sudah diberi dinding penahan maupun belum.',
                    'Tidak Ada' => 'Lereng masih berbentuk alami, atau hanya ada teras pertanian rendah yang tidak memotong lereng secara tegak.',
                ],
                'basis' => [
                    ['text' => 'Tingkat kerawanan dari penggalian dan pemotongan lereng: tinggi bila intensitasnya tinggi (untuk jalan, bangunan, atau penambangan) tanpa memperhatikan struktur lapisan tanah dan tanpa analisis kestabilan lereng; sedang bila intensitasnya rendah dan sudah memperhatikan keduanya; rendah bila tidak ada penggalian. Bobot indikator ini 20%, salah satu yang terbesar pada aspek aktivitas manusia.', 'source' => 'pu22'],
                    ['text' => 'Jangan memotong tebing jalan menjadi tegak dan jangan menggali di bawah lereng terjal. Tanah timbunan di lembah yang belum padat sempurna dapat turun dan retak saat hujan.', 'source' => 'esdm'],
                ],
                'tips' => [
                    'Tulis di Catatan: tujuan galian, perkiraan tinggi tebing, ada atau tidaknya dinding penahan, dan timbunan tanah bila ada.',
                ],
                'sources' => ['pu22', 'esdm'],
            ],

            'geosynthetic_reinforcement' => [
                'label' => 'Perkuatan Geosintetik',
                'what' => 'Bahan perkuatan buatan (lembaran atau jaring sintetis, atau batang baja) yang dipasang di dalam lereng untuk menambah kekuatan tanah. Biasanya berasal dari proyek pemerintah atau kontraktor, misalnya di tebing jalan atau lereng yang pernah longsor.',
                'steps' => [
                    'Amati permukaan dan tepi lereng, terutama tebing jalan, timbunan jalan, atau lereng bekas longsor yang sudah diperbaiki.',
                    'Cari bahan yang menyembul di tepi lapisan timbunan atau tersingkap karena erosi, atau kepala baut baja yang berbaris di permukaan dinding.',
                    'Karena perkuatan geosintetik wajib ditutup lapisan penutup, bahannya sering tidak terlihat. Tanyakan ke pemerintah desa atau Dinas PU, atau baca papan nama proyek.',
                ],
                'options' => [
                    'Tidak ada' => 'Tidak terlihat bahan perkuatan dan tidak ada informasi proyek perkuatan lereng di lokasi.',
                    'Geotekstil' => 'Lembaran kain sintetis, berupa anyaman atau seperti kain kempa, biasanya putih, hitam, atau abu-abu. Dipasang berlapis di dalam timbunan, dan ujungnya kadang terlihat terlipat di tepi lapisan.',
                    'Geogrid' => 'Jaring plastik kaku yang tertanam berlapis di dalam timbunan tanah, biasanya hitam. Lubangnya memanjang (geogrid satu arah atau uniaksial) atau persegi.',
                    'Geocell' => 'Sel plastik berbentuk sarang lebah yang dibentangkan di permukaan lereng, lalu diisi tanah, kerikil, atau rumput untuk menahan erosi.',
                    'Soil nailing/angkur tanah' => 'Batang baja yang ditanam miring ke dalam lereng dengan jarak teratur. Permukaan lereng biasanya dilapisi beton semprot, dengan kepala angkur (pelat dan mur baja) tersusun berbaris.',
                    'Kombinasi lebih dari satu' => 'Terlihat atau diketahui ada dua jenis perkuatan atau lebih dari daftar di atas.',
                ],
                'basis' => [
                    ['text' => 'Lereng tanah yang diperkuat geosintetik dipakai untuk timbunan jalan baru, pelebaran timbunan jalan, perbaikan keruntuhan lereng, dan alternatif dinding penahan tanah. Jenis perkuatannya geotekstil perkuatan dan geogrid uniaksial, dan perkuatan harus ditutup lapisan penutup muka (lunak atau keras) agar tidak rusak oleh sinar matahari.', 'source' => 'bm2022'],
                    ['text' => 'Dinding soil nail terdiri dari batang baja yang ditanam ke dalam tanah dan lapisan muka, umumnya beton semprot, yang terhubung ke kepala angkur.', 'source' => 'fhwa'],
                ],
                'tips' => [
                    'Bronjong (kawat berisi batu), talud batu, dan tembok penahan bukan geosintetik. Pilih "Tidak ada", lalu tulis jenisnya di Catatan.',
                ],
                'sources' => ['bm2022', 'fhwa'],
            ],
        ];
    }

    /** Daftar hal yang ditulis di kolom Catatan Kondisi Lapangan. */
    public static function notesChecklist(): array
    {
        return [
            'Tanggal survei, nama kader, dan cuaca 3 hari terakhir.',
            'Nama tanaman utama di sekitar node.',
            'Ukuran dan lokasi retakan, beserta perubahannya sejak survei lalu.',
            'Rembesan, mata air baru, atau genangan.',
            'Pohon, tiang listrik, pagar, atau dinding yang miring.',
            'Retakan pada rumah, jalan, atau dinding penahan; pintu atau jendela yang tiba-tiba sulit ditutup.',
            'Galian, timbunan, kolam, bangunan, atau pembukaan lahan baru di sekitar node.',
            'Sumber informasi riwayat longsor.',
        ];
    }

    /** Sumber rujukan. Kunci dipakai di fields()[...]['sources'] dan ['basis']. */
    public static function sources(): array
    {
        return [
            'sni7645' => [
                'short' => 'SNI 7645:2010',
                'title' => 'Badan Standardisasi Nasional (2010). SNI 7645:2010 Klasifikasi Penutup Lahan',
                'note' => 'Definisi hutan lahan kering primer dan sekunder, semak belukar, perkebunan, ladang, padang rumput, dan lahan terbuka (hlm. 8–9 dari 28). Versi terbaru: SNI 7645-1:2014.',
                'url' => 'https://repository.unikom.ac.id/44316/1/15.%20SNI%207645-2010%20Klasifikasi%20penutup%20lahan.pdf',
            ],
            'pu22' => [
                'short' => 'Permen PU 22/PRT/M/2007',
                'title' => 'Peraturan Menteri Pekerjaan Umum No. 22/PRT/M/2007 tentang Pedoman Penataan Ruang Kawasan Rawan Bencana Longsor',
                'note' => 'Kriteria kawasan rawan dan zona Tipe A/B/C (hlm. 16–19), serta tabel indikator kerawanan beserta bobotnya: kemiringan, tanah, tata air lereng, vegetasi, pola tanam, pemotongan lereng, kolam, dan drainase (hlm. 25–34).',
                'url' => 'https://bpbd.sukabumikota.go.id/wp-content/uploads/2021/10/PERATURAN-MENTERI-PEKERJAAN-UMUM-NOMOR-22-TAHUN-2007.pdf',
            ],
            'esdm' => [
                'short' => 'ESDM: Pengenalan Gerakan Tanah',
                'title' => 'Departemen Energi dan Sumber Daya Mineral. Pengenalan Gerakan Tanah',
                'note' => 'Gejala umum longsor, faktor penyebab (lereng terjal, tanah lempung, tata lahan, timbunan, penggundulan), ciri bekas longsoran lama, dan langkah pencegahan.',
                'url' => 'https://www.esdm.go.id/assets/media/content/Pengenalan_Gerakan_Tanah.pdf',
            ],
            'sk837' => [
                'short' => 'SK Mentan 837/1980',
                'title' => 'SK Menteri Pertanian No. 837/Kpts/Um/11/1980 tentang Kriteria dan Tata Cara Penetapan Hutan Lindung',
                'note' => 'Rujukan kelas kemiringan lereng yang umum dipakai. Naskah resminya tidak tersedia online; batas kelas dikutip dari pustaka turunan.',
                'url' => null,
            ],
            'usgs_signs' => [
                'short' => 'USGS: tanda-tanda longsor',
                'title' => 'U.S. Geological Survey. What are the signs of landslide development? What do I do if a landslide occurs?',
                'note' => 'Daftar tanda potensi longsor: retakan baru, air mengalir atau menggenang di lereng, pohon/tiang/pagar miring, retakan bangunan, pipa bocor.',
                'url' => 'https://www.usgs.gov/programs/landslide-hazards/what-are-signs-landslide-development-what-do-i-do-if-a-landslide-occurs',
            ],
            'fao' => [
                'short' => 'FAO: uji pita tanah',
                'title' => 'FAO Global Soil Partnership. Physical soil properties, Exercise P01b: Texture, ribbon method',
                'note' => 'Langkah uji bola dan pita tanah di lapangan beserta batas 2,5 cm dan 5 cm, mengacu pada panduan USDA-NRCS.',
                'url' => 'https://www.fao.org/fileadmin/user_upload/GSP/GSDP/Field_exercises/New_format_EN/P01b-Texture-Ribbon-EN.pdf',
            ],
            'nrcs' => [
                'short' => 'USDA-NRCS Texture by Feel',
                'title' => 'USDA Natural Resources Conservation Service. Guide to Texture by Feel',
                'note' => 'Diagram alir penentuan tekstur tanah dengan perabaan, rujukan utama panduan FAO.',
                'url' => 'https://www.nrcs.usda.gov/sites/default/files/2022-11/texture-by-feel.pdf',
            ],
            'dibi' => [
                'short' => 'DIBI BNPB',
                'title' => 'BNPB. Data Informasi Bencana Indonesia (DIBI)',
                'note' => 'Catatan kejadian bencana per kabupaten dan kecamatan.',
                'url' => 'https://dibi.bnpb.go.id/',
            ],
            'bm2022' => [
                'short' => 'Bina Marga SKh-1.3.19',
                'title' => 'Ditjen Bina Marga, Kementerian PUPR (2022). Spesifikasi Khusus Interim SKh-1.3.19: Lereng Tanah yang Diperkuat dengan Geosintetik',
                'note' => 'Penggunaan geotekstil dan geogrid untuk perkuatan lereng, serta kewajiban lapisan penutup muka.',
                'url' => 'https://binamarga.pu.go.id/uploads/files/1856/preview_1856-1-5.pdf',
            ],
            'fhwa' => [
                'short' => 'FHWA-NHI-14-007',
                'title' => 'Federal Highway Administration (2015). Soil Nail Walls Reference Manual, FHWA-NHI-14-007 (GEC No. 7)',
                'note' => 'Komponen dinding soil nailing: batang baja, lapisan muka beton semprot, dan kepala angkur.',
                'url' => 'https://highways.dot.gov/sites/fhwa.dot.gov/files/FHWA-NHI-14-007.pdf',
            ],
        ];
    }
}
