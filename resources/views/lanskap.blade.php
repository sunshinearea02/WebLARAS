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
<body class="bg-slate-50 min-h-screen">

    <nav class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
        <div class="flex items-center space-x-3">
            <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-10 h-10 rounded-xl object-cover shadow-md">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 leading-none">LARAS</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mt-0.5">Data Lanskap Lokasi</p>
            </div>
        </div>
        <a href="/" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Kembali ke Dashboard
        </a>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <div class="mb-8">
            <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Karakterisasi Lokasi</span>
            <h2 class="text-3xl font-black text-slate-900 mt-2">Data Lanskap &amp; Kondisi Lapangan</h2>
            <p class="text-slate-500 mt-2 max-w-2xl">Isi data kualitatif kondisi lereng di titik pemasangan sensor. Data ini melengkapi telemetri sensor untuk konteks penilaian risiko.</p>
        </div>

        @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 rounded-xl">
            <p class="font-bold mb-1">Periksa kembali isian form:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('lanskap.store') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-8">
            @csrf

            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Identitas Lokasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama / ID Lokasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="location_name" value="{{ old('location_name') }}" required
                               placeholder="Contoh: Titik Sensor A - Lereng Utara"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Kondisi Lapangan</label>
                        <textarea name="field_notes" rows="3" placeholder="Catatan bebas, observasi tambahan dari lapangan..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('field_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Kondisi Fisik Lereng</h3>
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
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $label }}</label>
                        <select name="{{ $field }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">— Pilih {{ $label }} —</option>
                            @foreach($options[$field] as $opt)
                            <option value="{{ $opt }}" @selected(old($field) === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach

                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Konteks Area &amp; Intervensi Manusia</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pemotongan Lereng Buatan Manusia di Sekitar Titik</label>
                        <div class="flex gap-3 mt-2">
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
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Terdapat Perkuatan Geosintetik</label>
                        <select name="geosynthetic_reinforcement"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">— Pilih Jenis Perkuatan —</option>
                            @foreach($options['geosynthetic_reinforcement'] as $opt)
                            <option value="{{ $opt }}" @selected(old('geosynthetic_reinforcement') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-7 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-200 transition-all">
                    Simpan Data Lanskap
                </button>
            </div>
        </form>

        <div class="mt-12">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Riwayat Data Lanskap Tersimpan ({{ $entries->count() }})</h3>

            @if($entries->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400">
                Belum ada data lanskap yang tersimpan.
            </div>
            @else
            <div class="space-y-3">
                @foreach($entries as $entry)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $entry->location_name }}</h4>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $entry->created_at->format('d/m/Y H:i') }} WIB</p>
                        </div>
                        <form method="POST" action="{{ route('lanskap.destroy', $entry->id) }}" onsubmit="return confirm('Hapus data lanskap ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-semibold">Hapus</button>
                        </form>
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
                    <p class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 leading-relaxed">{{ $entry->field_notes }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</body>
</html>
