<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Halaman Verifikasi Petugas</h1>
                <p class="text-sm text-slate-500 mt-1">Daftar node sensor yang mendeteksi indikasi Waspada / Bahaya dan memerlukan tindakan.</p>
            </div>
            <a href="{{ url('/peta-evakuasi') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-teal-600 hover:text-teal-700">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Peta
            </a>
        </div>

        <!-- Tabel Data / List Notifikasi -->
        @if(isset($pendingData) && count($pendingData) > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] tracking-wider">
                        <tr>
                            <th class="p-4">Node Sensor</th>
                            <th class="p-4">Lokasi</th>
                            <th class="p-4">Status Peringatan</th>
                            <th class="p-4">Kelembapan / Curah Hujan</th>
                            <th class="p-4">Waktu</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingData as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 font-bold text-slate-900">{{ $item->node_code }} ({{ $item->node_name }})</td>
                            <td class="p-4 text-slate-600">{{ $item->desa }}</td>
                            <td class="p-4">
                                @if($item->status == 'BAHAYA')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span> BAHAYA
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> WASPADA
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-600">
                                {{ $item->moisture_percent }}% / {{ $item->rainfall_mm }} mm
                            </td>
                            <td class="p-4 text-slate-500 text-xs">{{ $item->timestamp }}</td>
                            <td class="p-4 text-center">
                                <form action="{{ route('verifikasi.verify', $item->id) }}" method="POST" onsubmit="return confirm('Verifikasi dan ubah status node ini ke AMAN?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">check_circle</span> Verifikasi
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- State Kosong (Aman) -->
            <div class="text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                <span class="material-symbols-outlined text-4xl text-emerald-500 mb-2">verified</span>
                <h3 class="text-base font-bold text-slate-800">Semua Kondisi Aman</h3>
                <p class="text-sm text-slate-500 mt-1">Tidak ada data peringatan Waspada atau Bahaya yang perlu diverifikasi saat ini.</p>
            </div>
        @endif

    </div>
</body>
</html>