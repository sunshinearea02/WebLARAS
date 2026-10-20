<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Peringatan — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    @if($pending->isNotEmpty())
        <meta http-equiv="refresh" content="30">
    @endif
</head>
<body class="bg-air-50 text-deep-900 min-h-screen pb-12">

    @include('partials.navbar', ['active' => 'verifikasi'])

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-8">

        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Kader</span>
            <h2 class="text-3xl font-black text-slate-900 mt-1">Verifikasi Peringatan</h2>
            <p class="text-slate-500 mt-1.5 text-sm max-w-2xl">
                Peringatan dari AI dikirim ke warga desa terdampak setelah <b>satu kader</b> menyetujui.
                BAHAYA tanpa keputusan <b>{{ $autoMinutes }} menit</b> otomatis terkirim; WASPADA kedaluwarsa setelah {{ $waspadaMinutes }} menit.
            </p>
            <p class="text-xs text-slate-400 mt-2 font-mono">{{ $kaderCount }} nomor kader menerima pemberitahuan</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 rounded-xl">{{ $errors->first() }}</div>
        @endif
        @if($kaderCount === 0)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-xl">
                Belum ada nomor WhatsApp kader. Isi kolom <b>phone</b> akun admin/kader yang aktif. Selama kosong, peringatan langsung dikirim ke warga tanpa verifikasi.
            </div>
        @endif

        {{-- MENUNGGU KEPUTUSAN --}}
        <section class="space-y-4">
            <h3 class="text-lg font-bold">Menunggu keputusan</h3>
            @forelse($pending as $v)
                @php
                    $nodes = $v->snapshot['nodes'] ?? [];
                    $bahaya = $v->level === 'BAHAYA';
                    $deadline = $v->created_at->copy()->addMinutes($bahaya ? $autoMinutes : $waspadaMinutes);
                    $wargaN = count($wa->wargaTargets($v->desa));
                    $badge = fn ($s) => ['BAHAYA' => 'bg-rose-100 text-rose-700', 'WASPADA' => 'bg-amber-100 text-amber-800'][$s] ?? 'bg-emerald-100 text-emerald-700';
                    $num = fn ($x, $u = '', $dec = 1) => $x === null || $x === '' ? '-' : number_format((float) $x, $dec, '.', '') . $u;
                @endphp
                <div id="v{{ $v->id }}" class="bg-white rounded-2xl border-2 {{ $bahaya ? 'border-rose-300' : 'border-amber-300' }} {{ $highlight === $v->id ? 'ring-4 ring-blue-200' : '' }} shadow-sm p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $badge($v->level) }}">{{ $v->level }}</span>
                            <span class="ml-2 font-mono text-sm text-slate-500">#{{ $v->id }}</span>
                            <h4 class="text-xl font-black mt-2">Desa {{ $v->desa }}</h4>
                            <p class="text-sm text-slate-500">Terdeteksi {{ $v->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB &middot; {{ $wargaN }} nomor warga akan menerima peringatan</p>
                        </div>
                        <p class="text-sm font-semibold {{ $bahaya ? 'text-rose-600' : 'text-amber-700' }}">
                            {{ $bahaya ? 'Otomatis terkirim' : 'Kedaluwarsa' }} {{ $deadline->diffForHumans() }}
                        </p>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Hasil pemantauan sensor</p>
                        <table class="w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr><th class="py-2 pr-3">Node</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Tanah dangkal</th><th class="py-2 pr-3">Tanah dalam</th><th class="py-2 pr-3">Hujan</th><th class="py-2 pr-3">Pitch</th><th class="py-2 pr-3">Perubahan</th><th class="py-2 pr-3">Getaran</th><th class="py-2">Lokasi</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($nodes as $n)
                                    @php
                                        $d = $n['data'] ?? []; $ai = $n['ai'] ?? [];
                                        $hujan = isset($ai['rain_1h_mm']) ? $num($ai['rain_1h_mm'], ' mm/jam') : $num($d['rainfall_mm'] ?? null, ' mm');
                                        $dev = isset($ai['tilt_dev_deg']) ? (((float) $ai['tilt_dev_deg'] > 0 ? '+' : '') . number_format((float) $ai['tilt_dev_deg'], 1) . '°') : '-';
                                    @endphp
                                    <tr>
                                        <td class="py-2 pr-3"><b>{{ $n['code'] }}</b>@if(($n['name'] ?? '') && $n['name'] !== $n['code'])<span class="block text-xs text-slate-400">{{ $n['name'] }}</span>@endif</td>
                                        <td class="py-2 pr-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badge($n['status']) }}">{{ $n['status'] }}</span></td>
                                        <td class="py-2 pr-3">{{ $num($d['moisture_percent'] ?? null, '%') }}</td>
                                        <td class="py-2 pr-3">{{ $num($d['moisture_percent_deep'] ?? null, '%') }}</td>
                                        <td class="py-2 pr-3 whitespace-nowrap">{{ $hujan }}</td>
                                        <td class="py-2 pr-3">{{ $num($d['pitch'] ?? null, '°') }}</td>
                                        <td class="py-2 pr-3">{{ $dev }}</td>
                                        <td class="py-2 pr-3">{{ $num($ai['vibration_g'] ?? null, ' g', 2) }}</td>
                                        <td class="py-2">
                                            @if(isset($n['lat'], $n['lng']))
                                                <a class="text-blue-600 underline" target="_blank" rel="noopener" href="https://maps.google.com/?q={{ $n['lat'] }},{{ $n['lng'] }}">Maps</a>
                                            @else - @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-4 mb-1">Hasil analisis AI</p>
                        <table class="w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr><th class="py-2 pr-3">Node</th><th class="py-2 pr-3">Prediksi 1 jam</th><th class="py-2 pr-3">Confidence LSTM</th><th class="py-2 pr-3">Confidence RF</th><th class="py-2">Indeks risiko</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($nodes as $n)
                                    @php $d = $n['data'] ?? []; $ai = $n['ai'] ?? []; $pred = $ai['rf_status'] ?? $d['forecast_status'] ?? null; @endphp
                                    <tr>
                                        <td class="py-2 pr-3"><b>{{ $n['code'] }}</b></td>
                                        <td class="py-2 pr-3">@if($pred)<span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badge(strtoupper($pred)) }}">{{ strtoupper($pred) }}</span>@else - @endif</td>
                                        <td class="py-2 pr-3">{{ $num($ai['lstm_confidence'] ?? null, '%') }}</td>
                                        <td class="py-2 pr-3">{{ $num($ai['rf_confidence'] ?? null, '%') }}</td>
                                        <td class="py-2">{{ $num($ai['risk_index'] ?? null, ' / 100') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="text-xs text-slate-400 mt-2">Grafik lengkap: <a href="/#live" class="underline">Live Monitoring</a> &middot; <a href="{{ route('peta-evakuasi.index') }}" class="underline">Peta Evakuasi</a></p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 mt-5">
                        <form method="POST" action="{{ route('verifikasi.approve', $v) }}" class="flex-1"
                              onsubmit="return confirm('Kirim peringatan {{ $v->level }} ke warga Desa {{ $v->desa }} sekarang?')">
                            @csrf
                            <button class="w-full px-4 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold">Setujui &amp; kirim ke warga</button>
                        </form>
                        <form method="POST" action="{{ route('verifikasi.reject', $v) }}" class="flex-1 flex gap-2"
                              onsubmit="return confirm('Nyatakan alarm palsu? Peringatan tidak dikirim.')">
                            @csrf
                            <input name="note" maxlength="300" placeholder="Alasan (opsional)" class="flex-1 min-w-0 px-3 py-3 rounded-xl border border-slate-300 text-sm">
                            <button class="px-4 py-3 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 font-bold">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-6 text-sm text-slate-500">Tidak ada peringatan yang menunggu verifikasi.</div>
            @endforelse
        </section>

        {{-- NOMOR WARGA PER DESA --}}
        <section class="space-y-3">
            <h3 class="text-lg font-bold">Nomor WA warga</h3>
            <p class="text-sm text-slate-500">Warga hanya menerima peringatan untuk desanya. Pilih "Semua desa" untuk penerima umum (mis. grup BPBD).
                @if($globalCount) Ditambah {{ $globalCount }} nomor dari <span class="font-mono">WA_ALERT_TARGETS</span>. @endif</p>

            <form method="POST" action="{{ route('verifikasi.warga.store') }}" class="bg-white rounded-2xl border border-slate-200 p-5 grid sm:grid-cols-[180px_1fr_auto] gap-3 items-start">
                @csrf
                <select name="desa" class="px-3 py-2.5 rounded-xl border border-slate-300 text-sm">
                    @foreach($desaList as $d)<option value="{{ $d }}">Desa {{ $d }}</option>@endforeach
                    <option value="">Semua desa</option>
                </select>
                <div class="space-y-2">
                    <textarea name="phones" rows="2" required placeholder="081234567890, 081298765432 (bisa banyak, pisahkan koma atau baris baru) atau id grup 1203630xxxx@g.us" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
                    <input name="name" maxlength="100" placeholder="Nama (opsional, bila 1 nomor)" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <button class="px-4 py-2.5 rounded-xl bg-teal-500 hover:bg-teal-600 text-white font-bold">Tambah</button>
            </form>

            <div class="grid sm:grid-cols-2 gap-3">
                @forelse($contacts as $desa => $list)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4">
                        <p class="font-bold mb-2">{{ $desa === '' ? 'Semua desa' : "Desa {$desa}" }} <span class="text-xs font-normal text-slate-400">({{ $list->count() }})</span></p>
                        <ul class="divide-y divide-slate-100 text-sm max-h-56 overflow-y-auto">
                            @foreach($list as $c)
                                <li class="flex items-center justify-between py-1.5 gap-2">
                                    <span class="font-mono truncate">{{ $c->phone }} @if($c->name)<span class="font-sans text-slate-400">&middot; {{ $c->name }}</span>@endif</span>
                                    <form method="POST" action="{{ route('verifikasi.warga.destroy', $c) }}" onsubmit="return confirm('Hapus {{ $c->phone }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 text-xs font-semibold">Hapus</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada nomor warga.</p>
                @endforelse
            </div>
        </section>

        {{-- RIWAYAT --}}
        <section class="space-y-3">
            <h3 class="text-lg font-bold">Riwayat</h3>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-air-50 text-left text-xs uppercase text-slate-500">
                        <tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Desa</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Hasil</th><th class="px-4 py-3">Oleh</th><th class="px-4 py-3">Warga</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($history as $v)
                            <tr>
                                <td class="px-4 py-3 font-mono">{{ $v->id }}</td>
                                <td class="px-4 py-3">{{ $v->desa ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold {{ $v->level === 'BAHAYA' ? 'text-rose-600' : 'text-amber-700' }}">{{ $v->level }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $v->created_at->timezone('Asia/Jakarta')->format('d/m H:i') }}</td>
                                <td class="px-4 py-3">{{ $v->stateLabel() }}@if($v->note)<span class="block text-xs text-slate-400">{{ $v->note }}</span>@endif</td>
                                <td class="px-4 py-3">{{ $v->deciderName() ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $v->sent_to_warga_at ? "{$v->warga_sent}/{$v->warga_total}" : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">Belum ada riwayat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>