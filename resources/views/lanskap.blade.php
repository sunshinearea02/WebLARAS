<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lanskap — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <!-- NAVBAR CONTROL CENTER -->
    <nav class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
        <div class="flex items-center space-x-3">
            <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-10 h-10 rounded-xl object-cover shadow-md" onerror="this.src='https://via.placeholder.com/40'">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 leading-none">LARAS</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mt-0.5">Control Center</p>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <a href="/" class="text-xs font-bold text-slate-600 hover:text-blue-600 transition">Edukasi</a>
            <a href="{{ route('lanskap.index') }}" class="text-xs font-bold text-blue-600 border-b-2 border-blue-600 pb-0.5">Data Lanskap</a>
            <a href="{{ route('peta-evakuasi.index') }}" class="text-xs font-bold text-slate-600 hover:text-blue-600 transition">Peta Evakuasi</a>

            <!-- STATUS AUTENTIKASI -->
            @auth
                <div class="flex items-center space-x-3 border-l pl-4 border-slate-200">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-800 leading-none">{{ Auth::user()->name }}</p>
                        <span class="text-[10px] font-mono uppercase font-semibold text-blue-600">{{ Auth::user()->role }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs bg-rose-50 text-rose-600 px-3 py-1.5 rounded-lg font-bold hover:bg-rose-100 transition">Logout</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-xs bg-blue-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-blue-700 transition">Login Kader / Admin</a>
            @endauth
        </div>
    </nav>

    <!-- CONTENT CONTAINER -->
    <div class="max-w-5xl mx-auto px-6 py-8 space-y-8">

        <!-- HEADER BANNER -->
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Karakterisasi Lokasi</span>
            <h2 class="text-3xl font-black text-slate-900 mt-1">Data Lanskap &amp; Kondisi Lapangan</h2>
            <p class="text-slate-500 mt-1.5 text-sm max-w-2xl">Isi data kualitatif kondisi lereng di titik pemasangan sensor. Data ini melengkapi telemetri sensor untuk konteks penilaian risiko.</p>
        </div>

        <!-- NOTIFIKASI SUCCESS / ERROR -->
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold px-4 py-3 rounded-xl shadow-sm">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl shadow-sm">
            <p class="font-bold mb-1">Periksa kembali isian form:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- FORM INPUT DATA (HANYA BISA DIAKSES OLEH ADMIN & KADER) -->
        @auth
            @if(in_array(Auth::user()->role, ['admin', 'kader']))
            <form method="POST" action="{{ route('lanskap.store') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-8">
                @csrf

                <!-- SECTION 1: IDENTITAS LOKASI -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 font-mono">Identitas Lokasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Nama / ID Lokasi <span class="text-rose-500">*</span></label>
                            <input type="text" name="location_name" value="{{ old('location_name') }}" required
                                   placeholder="Contoh: Titik Sensor A - Lereng Utara"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Catatan Kondisi Lapangan</label>
                            <textarea name="field_notes" rows="3" placeholder="Catatan bebas, observasi tambahan dari lapangan..."
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('field_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: KONDISI FISIK LERENG -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 font-mono">Kondisi Fisik Lereng</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        @php
                        $selectFields = [
                            'dominant_vegetation'  => 'Jenis Vegetasi Dominan',
                            'slope_classification' => 'Klasifikasi Kemiringan Lereng',
                            'drainage_condition'   => 'Kondisi Drainase',
                            'soil_cracking'        => 'Keretakan Tanah',
                            'soil_type'            => 'Jenis Tanah',
                            'landslide_history'    => 'Riwayat Kejadian Longsor',
                        ];
                        @endphp

                        @foreach($selectFields as $field => $label)
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">{{ $label }}</label>
                            <select name="{{ $field }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="">— Pilih {{ $label }} —</option>
                                @if(isset($options[$field]))
                                    @foreach($options[$field] as $opt)
                                    <option value="{{ $opt }}" @selected(old($field) === $opt)>{{ $opt }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @endforeach

                    </div>
                </div>

                <!-- SECTION 3: INTERVENSI MANUSIA -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 font-mono">Konteks Area &amp; Intervensi Manusia</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Pemotongan Lereng Buatan Manusia</label>
                            <div class="flex gap-3 mt-1.5">
                                <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-sm cursor-pointer flex-1 justify-center hover:bg-slate-50">
                                    <input type="radio" name="artificial_slope_cutting" value="ada" @checked(old('artificial_slope_cutting') === 'ada') class="accent-blue-600">
                                    Ada
                                </label>
                                <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-sm cursor-pointer flex-1 justify-center hover:bg-slate-50">
                                    <input type="radio" name="artificial_slope_cutting" value="tidak_ada" @checked(old('artificial_slope_cutting') === 'tidak_ada') class="accent-blue-600">
                                    Tidak Ada
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Terdapat Perkuatan Geosintetik</label>
                            <select name="geosynthetic_reinforcement"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="">— Pilih Jenis Perkuatan —</option>
                                @if(isset($options['geosynthetic_reinforcement']))
                                    @foreach($options['geosynthetic_reinforcement'] as $opt)
                                    <option value="{{ $opt }}" @selected(old('geosynthetic_reinforcement') === $opt)>{{ $opt }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-7 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all">
                        Simpan Data Lanskap
                    </button>
                </div>
            </form>
            @endif
        @else
            <!-- INFORMASI UNTUK WARGA / GUEST -->
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-xs p-4 rounded-2xl flex justify-between items-center shadow-sm">
                <span>⚠️ Anda mengakses halaman ini sebagai <strong>Warga (Guest)</strong>. Pengisian data kualitatif lanskap hanya dikelola oleh Kader &amp; Admin.</span>
                <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:underline">Login Petugas →</a>
            </div>
        @endauth

        <!-- RIWAYAT DATA LANSKAP (DAPAT DILIHAT OLEH SEMUA ORANG) -->
        <div class="mt-10 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 font-mono">Riwayat Data Lanskap Tersimpan ({{ $entries->count() }})</h3>

            @if($entries->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-400">
                Belum ada data lanskap yang tersimpan.
            </div>
            @else
            <div class="space-y-4">
                @foreach($entries as $entry)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $entry->location_name }}</h4>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $entry->created_at->format('d/m/Y H:i') }} WIB</p>
                        </div>

                        <!-- TOMBOL HAPUS (HANYA DITAMPILKAN KE ADMIN & KADER) -->
                        @auth
                            @if(in_array(Auth::user()->role, ['admin', 'kader']))
                            <form method="POST" action="{{ route('lanskap.destroy', $entry->id) }}" onsubmit="return confirm('Hapus data lanskap ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold hover:underline">Hapus</button>
                            </form>
                            @endif
                        @endauth
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2 text-xs">
                        <p><span class="text-slate-400">Vegetasi:</span> <span class="font-medium text-slate-700">{{ $entry->dominant_vegetation ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Kemiringan:</span> <span class="font-medium text-slate-700">{{ $entry->slope_classification ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Drainase:</span> <span class="font-medium text-slate-700">{{ $entry->drainage_condition ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Keretakan:</span> <span class="font-medium text-slate-700">{{ $entry->soil_cracking ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Jenis Tanah:</span> <span class="font-medium text-slate-700">{{ $entry->soil_type ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Riwayat Longsor:</span> <span class="font-medium text-slate-700">{{ $entry->landslide_history ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Potongan Lereng:</span> <span class="font-medium text-slate-700">{{ $entry->artificial_slope_cutting === null ? '-' : ($entry->artificial_slope_cutting ? 'Ada' : 'Tidak Ada') }}</span></p>
                        <p><span class="text-slate-400">Geosintetik:</span> <span class="font-medium text-slate-700">{{ $entry->geosynthetic_reinforcement ?? '-' }}</span></p>
                    </div>

                    @if($entry->field_notes)
                    <p class="pt-2 border-t border-slate-100 text-xs text-slate-500 leading-relaxed">{{ $entry->field_notes }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</body>
</html>