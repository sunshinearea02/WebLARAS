<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peta Evakuasi — LARAS Control Center</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Leaflet & Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        #map { height: 500px; width: 100%; border-radius: 1rem; z-index: 0; }
        .leaflet-popup-content-wrapper { border-radius: 12px; }
        .leaflet-routing-container { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <!-- Header Navbar -->
    <nav class="max-w-7xl mx-auto px-4 py-2.5 flex items-center justify-between bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200 gap-3">
        <div class="flex items-center gap-3 shrink-0">
            <a href="/" class="flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition border border-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>

            <div class="h-6 w-[1px] bg-slate-200"></div>

            <div class="flex items-center gap-2.5">
                <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm" onerror="this.src='https://via.placeholder.com/32'">
                <div>
                    <h1 class="text-base font-extrabold tracking-tight text-slate-900 leading-none">LARAS</h1>
                    <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mt-0.5">CONTROL CENTER</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
            <a href="/#edukasi" class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Edukasi
            </a>

            <a href="/lanskap" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Data Lanskap
            </a>

            <a href="/peta-evakuasi" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 border border-rose-200 text-rose-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                Peta Evakuasi
            </a>

            <a href="tel:112" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.79 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak Darurat (112)
            </a>

            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[11px] rounded-xl shrink-0 font-mono">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                SYNC ACTIVE
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">

        <!-- Title Section -->
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Navigasi Penyelamatan</span>
            <h2 class="text-3xl font-black text-slate-900 mt-1">Peta Evakuasi Kawasan Pacet</h2>
            <p class="text-slate-500 mt-1 max-w-2xl text-xs leading-relaxed">
                Visualisasi titik node LARAS di perbukitan Pacet beserta status risiko terkini. Rute evakuasi memandu warga mengikuti kontur jalan dari titik bahaya/lereng menuju Posko Aman terdekat.
            </p>
        </div>

        <!-- Legend Badges -->
        <div class="flex flex-wrap gap-3">
            <div class="flex items-center gap-2 bg-white border border-slate-200 px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Aman (Posko)
            </div>
            <div class="flex items-center gap-2 bg-white border border-slate-200 px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span> Waspada
            </div>
            <div class="flex items-center gap-2 bg-white border border-slate-200 px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span> Bahaya
            </div>
            <div class="flex items-center gap-2 bg-white border border-slate-200 px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 shadow-sm">
                <span class="w-4 h-0.5 bg-rose-500 border-t-2 border-dashed border-rose-500"></span> Rute Evakuasi Jalan
            </div>
        </div>

        <!-- Map Container -->
        <div id="map" class="shadow-sm border border-slate-200"></div>

        <!-- Detail Node Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($nodes as $node)
            @php
                $statusColor = match(strtoupper($node->status)) {
                    'BAHAYA' => ['bg-rose-50', 'border-rose-200', 'text-rose-700', 'bg-rose-500'],
                    'WASPADA' => ['bg-amber-50', 'border-amber-200', 'text-amber-700', 'bg-amber-500'],
                    default => ['bg-emerald-50', 'border-emerald-200', 'text-emerald-700', 'bg-emerald-500'],
                };
            @endphp
            <div class="bg-white rounded-2xl border {{ $statusColor[1] }} shadow-sm p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-slate-900 text-sm">{{ $node->node_name }}</h4>
                    <span class="flex items-center gap-1.5 text-[10px] font-bold {{ $statusColor[2] }} {{ $statusColor[0] }} px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusColor[3] }}"></span>
                        {{ strtoupper($node->status) }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 font-mono">{{ $node->desa ?? '-' }} · {{ number_format($node->lat, 5) }}, {{ number_format($node->lng, 5) }}</p>
                @if(isset($node->notes) && $node->notes)
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">{{ $node->notes }}</p>
                @endif
            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400">
                Belum ada data node tersimpan.
            </div>
            @endforelse
        </div>

        <!-- Section Sistem Check-in Warga Posko -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Form Check-in Warga (Input Manual Kader) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm lg:col-span-1">
                <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="font-bold text-slate-900 text-sm">Form Check-In Warga di Posko</h3>
                </div>

                <form id="formCheckin" class="space-y-4">

                    <!-- Pilihan Posko Tujuan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Posko Evakuasi</label>
                        <select id="selectPosko" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50">
                            @foreach($daftarPosko as $p)
                                <option value="{{ $p['id'] }}" data-lokasi="{{ $p['lokasi'] }}">
                                     {{ $p['nama'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Keterangan Lokasi Posko (Auto Fill) -->
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-xs">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Lokasi/Alamat Posko:</span>
                        <p id="teksLokasiPosko" class="font-medium text-slate-700 mt-0.5">{{ $daftarPosko[0]['lokasi'] }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap Warga</label>
                        <input type="text" id="nama_lengkap" required placeholder="Contoh: Budi Santoso"
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Asal / RT / RW</label>
                        <input type="text" id="asal" required placeholder="Contoh: Dusun Padusan RT 02 RW 01"
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Usia (Tahun)</label>
                        <input type="number" id="usia" min="0" max="120" required placeholder="Contoh: 38"
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kondisi Kesehatan</label>
                        <select id="kondisi" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="Sehat">Sehat</option>
                            <option value="Luka Ringan">Luka Ringan</option>
                            <option value="Luka Berat">Luka Berat</option>
                            <option value="Lansia / Rentan">Lansia / Rentan</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-xs transition shadow-sm">
                        + Simpan Check-In Warga
                    </button>
                </form>
            </div>

            <!-- Tabel Data Check-in Warga + FILTER POSKO -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm lg:col-span-2">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 mb-4 gap-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">
                            Rekap Warga Check-In
                        </h3>
                        <p class="text-[11px] text-slate-400">Filter data warga berdasarkan posko keberadaan saat ini</p>
                    </div>

                    <!-- Filter Dropdown Posko -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500 shrink-0">Filter Posko:</label>
                        <select id="filterPosko" class="px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 bg-slate-50 focus:outline-none">
                            <option value="SEMUA">Semua Posko</option>
                            @foreach($daftarPosko as $p)
                                <option value="{{ $p['id'] }}"> {{ $p['id'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <span id="labelTotal" class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 font-mono">
                        Total: {{ count($checkins) }} Warga
                    </span>
                </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="text-[11px] text-slate-400 uppercase bg-slate-50 border-b border-slate-100 font-mono">
                        <tr>
                            <th class="px-2 py-2.5 w-8">No</th>
                            <th class="px-3 py-2.5">Nama Warga</th>
                            <th class="px-3 py-2.5">Posko Check-in & Lokasi</th>
                            <th class="px-3 py-2.5 min-w-[120px]">Asal</th>
                            <th class="px-2 py-2.5 w-14">Usia</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Kondisi</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="bodyTabel">
                        @forelse($checkins as $key => $warga)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-2 py-2.5 font-medium font-mono text-center align-top">{{ $key + 1 }}</td>
                            <td class="px-3 py-2.5 font-bold text-slate-800 align-top">{{ $warga->nama_lengkap }}</td>
                            <td class="px-3 py-2.5 align-top">
                                <span class="font-bold text-blue-600 block">{{ $warga->posko_id }}</span>
                                <span class="text-[10px] text-slate-400 block leading-tight">{{ $warga->lokasi_posko }}</span>
                            </td>
                            <!-- Kolom Asal: Boleh melipat ke baris bawah secara rapi (Tanpa titik-titik) -->
                            <td class="px-3 py-2.5 align-top leading-relaxed text-slate-700 min-w-[120px]">{{ $warga->asal }}</td>
                            <td class="px-2 py-2.5 font-mono whitespace-nowrap align-top">{{ $warga->usia }} Thn</td>
                            <!-- Kolom Kondisi: Dikunci agar tetap 1 baris utuh -->
                            <td class="px-3 py-2.5 whitespace-nowrap align-top">
                                @if($warga->kondisi == 'Sehat')
                                    <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">🟢 Sehat</span>
                                @elseif($warga->kondisi == 'Luka Ringan')
                                    <span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Ringan</span>
                                @elseif($warga->kondisi == 'Luka Berat')
                                    <span class="inline-block bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Berat</span>
                                @else
                                    <span class="inline-block bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Lansia / Rentan</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-400 font-mono text-[11px] whitespace-nowrap align-top">{{ $warga->created_at->format('H:i') }} WIB</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-400 font-mono">Belum ada warga terdaftar di posko.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Leaflet JS & Leaflet Routing Machine -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <script>
        const nodes = @json($nodes);

        const map = L.map('map').setView([-7.66744, 112.53658], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18,
        }).addTo(map);

        const statusColor = (status) => {
            status = (status || '').toUpperCase();
            if (status === 'BAHAYA') return '#e11d48';
            if (status === 'WASPADA') return '#f59e0b';
            return '#10b981';
        };

        function makeIcon(color) {
            return L.divIcon({
                className: '',
                html: `<div style="background:${color}; width:18px; height:18px; border-radius:50%; border:3px solid white; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });
        }

        // Plot Marker Node di Peta
        nodes.forEach(node => {
            const lat = parseFloat(node.lat);
            const lng = parseFloat(node.lng);
            const color = statusColor(node.status);

            const marker = L.marker([lat, lng], { icon: makeIcon(color) }).addTo(map);
            marker.bindPopup(`
                <div style="font-family: Inter, sans-serif; min-width: 160px;">
                    <p style="font-weight:700; font-size:13px; margin-bottom:2px;">${node.node_name}</p>
                    <p style="font-size:11px; color:#64748b; margin-bottom:4px;">${node.desa || ''}</p>
                    <span style="font-size:10px; font-weight:700; color:${color}; text-transform:uppercase;">${node.status}</span>
                </div>
            `);
        });

        // Routing Realistis Mengikuti Kontur Jalan
        L.Routing.control({
            waypoints: [
                L.latLng(-7.69100, 112.54100), // Node-01 Padusan (Bahaya)
                L.latLng(-7.67500, 112.53500), // Node-02 Sajen
                L.latLng(-7.66200, 112.54000), // Node-03 Claket
                L.latLng(-7.65200, 112.52900)  // Node-04 Posko Cepokolimo (Aman)
            ],
            lineOptions: {
                styles: [{ color: '#e11d48', opacity: 0.85, weight: 5, dashArray: '8, 8' }]
            },
            createMarker: function() { return null; },
            addWaypoints: false,
            draggableWaypoints: false
        }).addTo(map);

        L.Routing.control({
            waypoints: [
                L.latLng(-7.66200, 112.54000), // Node-03 Claket
                L.latLng(-7.64400, 112.54700)  // Node-05 Kembangbelor (Aman)
            ],
            lineOptions: {
                styles: [{ color: '#10b981', opacity: 0.85, weight: 5 }]
            },
            createMarker: function() { return null; },
            addWaypoints: false,
            draggableWaypoints: false
        }).addTo(map);

        if (nodes.length > 0) {
            const bounds = L.latLngBounds(nodes.map(n => [parseFloat(n.lat), parseFloat(n.lng)]));
            map.fitBounds(bounds, { padding: [40, 40] });
        }

        // Auto-fill Teks Lokasi Posko saat Select Posko Berubah
        const selectPosko = document.getElementById('selectPosko');
        const teksLokasiPosko = document.getElementById('teksLokasiPosko');

        selectPosko.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            teksLokasiPosko.innerText = selectedOption.getAttribute('data-lokasi');
        });

        // Handling Form Check-In
        const formCheckin = document.getElementById('formCheckin');
        formCheckin.addEventListener('submit', function(e) {
            e.preventDefault();

            const selectedOption = selectPosko.options[selectPosko.selectedIndex];

            const dataPayload = {
                posko_id: selectPosko.value,
                lokasi_posko: selectedOption.getAttribute('data-lokasi'),
                nama_lengkap: document.getElementById('nama_lengkap').value,
                asal: document.getElementById('asal').value,
                usia: document.getElementById('usia').value,
                kondisi: document.getElementById('kondisi').value
            };

            fetch('{{ route("peta-evakuasi.checkin") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(dataPayload)
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    alert(res.message);
                    formCheckin.reset();
                    teksLokasiPosko.innerText = selectPosko.options[0].getAttribute('data-lokasi');
                    updateCheckinTable();
                }
            })
            .catch(err => alert("Terjadi kesalahan koneksi server."));
        });

        // Handling Filter Posko
        const filterPosko = document.getElementById('filterPosko');
        filterPosko.addEventListener('change', function() {
            updateCheckinTable(this.value);
        });

        // Update Tabel Realtime dengan Support Filter
        function updateCheckinTable(selectedFilter = 'SEMUA') {
            fetch(`{{ route("peta-evakuasi.checkins-list") }}?posko_id=${selectedFilter}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('labelTotal').innerText = `Total: ${data.length} Warga`;
                const tbody = document.getElementById('bodyTabel');

                if(data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-6 text-slate-400 font-mono">Belum ada warga terdaftar di posko ini.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.map((item, idx) => {
                    let badge = '';
                    if(item.kondisi === 'Sehat') badge = '<span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Sehat</span>';
                    else if(item.kondisi === 'Luka Ringan') badge = '<span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Ringan</span>';
                    else if(item.kondisi === 'Luka Berat') badge = '<span class="inline-block bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Berat</span>';
                    else badge = '<span class="inline-block bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold"> Lansia / Rentan</span>';

                    const time = new Date(item.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    return `
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-2 py-2.5 font-medium font-mono text-center align-top">${idx + 1}</td>
                            <td class="px-3 py-2.5 font-bold text-slate-800 align-top">${item.nama_lengkap}</td>
                            <td class="px-3 py-2.5 align-top">
                                <span class="font-bold text-blue-600 block">${item.posko_id}</span>
                                <span class="text-[10px] text-slate-400 block leading-tight">${item.lokasi_posko}</span>
                            </td>
                            <td class="px-3 py-2.5 align-top leading-relaxed text-slate-700 min-w-[120px]">${item.asal}</td>
                            <td class="px-2 py-2.5 font-mono whitespace-nowrap align-top">${item.usia} Thn</td>
                            <td class="px-3 py-2.5 whitespace-nowrap align-top">${badge}</td>
                            <td class="px-3 py-2.5 text-slate-400 font-mono text-[11px] whitespace-nowrap align-top">${time} WIB</td>
                        </tr>
                    `;
                }).join('');
            });
        }
    </script>
</body>
</html>