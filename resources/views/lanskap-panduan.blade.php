<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pengisian Data Lanskap — LARAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <style>
        html { scroll-behavior: smooth; }
        section[id] { scroll-margin-top: 96px; }
    </style>
</head>
<body class="bg-air-50 text-deep-900 min-h-screen">

    @include('partials.navbar', ['active' => 'lanskap'])

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        <!-- HEADER -->
        <div>
            <a href="{{ route('lanskap.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-600 hover:underline">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Data Lanskap
            </a>
            <span class="block mt-4 text-xs font-bold uppercase tracking-widest text-teal-600 font-mono">Panduan Kader</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Panduan Pengisian Data Lanskap</h2>
            <p class="text-slate-500 mt-2 text-sm max-w-2xl leading-relaxed">
                Panduan ini menjelaskan cara menilai setiap kolom di form Data Lanskap langsung di lapangan:
                apa yang diamati, cara mengukurnya, ciri setiap pilihan, dan sumber rujukannya.
            </p>
        </div>

        <!-- AREA PENGAMATAN -->
        <div class="flex items-start gap-3 bg-teal-50 border border-teal-200 rounded-2xl p-4 sm:p-5">
            <span class="material-symbols-outlined text-teal-600 text-[28px] shrink-0">radar</span>
            <div class="text-sm text-slate-700 leading-relaxed">
                <p class="font-bold text-slate-900">Semua kolom dinilai dalam radius 50 meter dari setiap node.</p>
                <p class="mt-1">Vegetasi, kemiringan, drainase, retakan, jenis tanah, riwayat longsor, pemotongan lereng, dan perkuatan diamati di area lingkaran berjari-jari 50 m dengan node sebagai titik tengahnya. Satu survei diisi untuk satu node.</p>
            </div>
        </div>

        <!-- DAFTAR ISI -->
        <nav class="flex flex-wrap gap-2">
            <a href="#persiapan" class="px-3 py-1.5 rounded-full bg-white border border-air-300 text-xs font-semibold text-deep-700 hover:bg-air-100">Persiapan</a>
            @foreach($fields as $key => $field)
                <a href="#{{ $key }}" class="px-3 py-1.5 rounded-full bg-white border border-air-300 text-xs font-semibold text-deep-700 hover:bg-air-100">{{ $field['label'] }}</a>
            @endforeach
            <a href="#catatan" class="px-3 py-1.5 rounded-full bg-white border border-air-300 text-xs font-semibold text-deep-700 hover:bg-air-100">Catatan Lapangan</a>
            <a href="#sumber" class="px-3 py-1.5 rounded-full bg-white border border-air-300 text-xs font-semibold text-deep-700 hover:bg-air-100">Sumber</a>
        </nav>

        <!-- PERSIAPAN -->
        <section id="persiapan" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 space-y-5">
            <h3 class="text-lg font-bold text-slate-900">Sebelum ke Lapangan</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm text-slate-600 leading-relaxed">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kapan survei dilakukan</h4>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Saat node pertama kali dipasang.</li>
                        <li>Setiap 3 bulan, terutama di awal dan akhir musim hujan.</li>
                        <li>Setelah hujan lebat, gempa, atau ada galian dan pembukaan lahan baru di sekitar node.</li>
                    </ul>
                    <p class="mt-2">Bila kondisi berubah, ubah data node tersebut dengan tombol <strong>Edit</strong> supaya sistem memakai kondisi terbaru.</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Area yang diamati</h4>
                    <p>Lingkaran berjari-jari <strong>50 m</strong> dengan node sebagai titik tengahnya. Perhatikan terutama bagian <strong>atas lereng</strong>, karena retakan dan aliran air dari atas paling memengaruhi titik node.</p>
                    <p class="mt-2">Untuk memperkirakan 50 m di lapangan, bentangkan meteran atau hitung ±65 langkah orang dewasa dari node. Karena node dipasang berdekatan, area dua node bisa tumpang tindih. Kondisi yang sama boleh dicatat di kedua node.</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Alat yang dibawa</h4>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($tools as $tool)
                            <li>{{ $tool }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-rose-800">
                    <h4 class="text-xs font-bold uppercase tracking-wider mb-2">Keselamatan</h4>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Jangan survei saat hujan deras atau saat status node Waspada/Bahaya.</li>
                        <li>Survei minimal berdua, jangan sendirian.</li>
                        <li>Jangan berdiri di tepi tebing atau di atas retakan.</li>
                    </ul>
                </div>
            </div>

            <!-- BAGAIMANA DATA DIPAKAI -->
            <div class="border-t border-slate-100 pt-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Bagaimana data ini dipakai</h4>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Isian form diubah menjadi <strong>skor lereng</strong> (0–1). Makin tinggi skornya, ambang peringatan diturunkan
                    hingga 30% sehingga peringatan muncul lebih awal. Contoh: pada skor 0,55, batas kebasahan kritis turun dari 75 menjadi 62,6.
                    Bobot tiap kolom:
                </p>
                <div class="mt-3 space-y-2">
                    @foreach($weights as $w)
                        <div class="flex items-center gap-3 text-xs">
                            <span class="w-36 shrink-0 text-slate-600">{{ $w['label'] }}</span>
                            <div class="flex-1 h-2.5 rounded-full bg-air-100 overflow-hidden">
                                <div class="h-full bg-teal-500 rounded-full" style="width: {{ $w['weight'] * 4 }}%"></div>
                            </div>
                            <span class="w-10 text-right font-mono font-bold text-slate-700">{{ $w['weight'] }}%</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-500 leading-relaxed">
                    Pemotongan lereng buatan menaikkan skor, sedangkan perkuatan lereng menurunkannya. Bobot dan nilai ini rancangan tim LARAS
                    dan akan dikalibrasi bersama ahli geoteknik. Karena itu isilah sesuai kondisi sebenarnya, bukan perkiraan yang paling aman.
                </p>
            </div>
        </section>

        <!-- PANDUAN PER KOLOM -->
        @foreach($fields as $key => $field)
        <section id="{{ $key }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 space-y-5">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-teal-600 bg-air-100 rounded-xl p-2">{{ $field['icon'] }}</span>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $field['label'] }}</h3>
                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-teal-50 border border-teal-200 text-[11px] font-semibold text-teal-700">
                        <span class="material-symbols-outlined text-[14px]">radar</span> Area: radius 50 m dari node
                    </span>
                    <p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ $field['what'] }}</p>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Cara menilai di lapangan</h4>
                <ol class="list-decimal pl-5 space-y-1.5 text-sm text-slate-600 leading-relaxed">
                    @foreach($field['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Ciri setiap pilihan</h4>
                <div class="rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                    @foreach($field['options'] as $option => $desc)
                        <div class="grid grid-cols-1 sm:grid-cols-[200px_1fr] gap-1 sm:gap-4 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-800">{{ $option }}</span>
                            <span class="text-slate-600 leading-relaxed">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if(!empty($field['basis']))
            <div class="bg-air-50 border border-air-300 rounded-xl p-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-teal-700 mb-2">Dasar dari Sumber Resmi</h4>
                <ul class="space-y-2.5 text-sm text-slate-700 leading-relaxed">
                    @foreach($field['basis'] as $b)
                        <li class="flex gap-2">
                            <span class="material-symbols-outlined text-[18px] text-teal-500 shrink-0">bookmark</span>
                            <span>{{ $b['text'] }}
                                <a href="#sumber-{{ $b['source'] }}" class="whitespace-nowrap text-xs font-semibold text-teal-600 hover:underline">({{ $sources[$b['source']]['short'] }})</a>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($field['tips']))
            <div class="bg-cream-50 border border-cream-200 rounded-xl p-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-cream-800 mb-2">Perhatikan</h4>
                <ul class="list-disc pl-5 space-y-1.5 text-sm text-slate-700 leading-relaxed">
                    @foreach($field['tips'] as $tip)
                        <li>{{ $tip }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <p class="text-xs text-slate-500">
                Sumber:
                @foreach($field['sources'] as $s)
                    <a href="#sumber-{{ $s }}" class="text-teal-600 hover:underline">{{ $sources[$s]['short'] }}</a>@if(!$loop->last); @endif
                @endforeach
            </p>
        </section>
        @endforeach

        <!-- CATATAN LAPANGAN -->
        <section id="catatan" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 space-y-3">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-teal-600 bg-air-100 rounded-xl p-2">edit_note</span>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Catatan Kondisi Lapangan</h3>
                    <p class="text-sm text-slate-600 mt-1">Tulis hal berikut di kolom Catatan, lalu simpan fotonya. Catatan ini membantu petugas BPBD membaca perubahan lereng dari waktu ke waktu.</p>
                </div>
            </div>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm text-slate-600">
                @foreach($notesChecklist as $item)
                    <li class="flex gap-2"><span class="material-symbols-outlined text-[18px] text-teal-500">check_circle</span>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="text-sm text-rose-700 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3">
                Retakan baru atau melebar, rembesan atau mata air baru, serta pohon atau tiang yang mulai miring adalah tanda tanah bergerak.
                Segera laporkan ke BPBD tanpa menunggu survei berikutnya.
            </p>
        </section>

        <!-- SUMBER -->
        <section id="sumber" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-3">Sumber Rujukan</h3>
            <ol class="list-decimal pl-5 space-y-3 text-sm">
                @foreach($sources as $key => $source)
                    <li id="sumber-{{ $key }}" class="scroll-mt-24">
                        @if($source['url'])
                            <a href="{{ $source['url'] }}" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:underline">{{ $source['title'] }}</a>
                        @else
                            <span class="font-semibold text-slate-800">{{ $source['title'] }}</span>
                        @endif
                        <p class="text-slate-500 text-xs mt-0.5">{{ $source['note'] }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="text-center pb-4">
            <a href="{{ route('lanskap.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md">
                <span class="material-symbols-outlined text-[18px]">edit_document</span> Isi Data Lanskap
            </a>
        </div>
    </div>

</body>
</html>
