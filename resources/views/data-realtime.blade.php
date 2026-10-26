<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sensor Realtime — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <style>
        /* baris baru: muncul dari atas dengan sorot hijau yang memudar */
        @keyframes masuk { from { opacity: 0; transform: translateY(-8px); background: #bbf7d0; } 40% { opacity: 1; transform: none; background: #bbf7d0; } to { background: transparent; } }
        @keyframes berubah { from { background: #fde68a; } to { background: transparent; } }
        tr.baru td { animation: masuk 2.4s ease-out; }
        tr.berubah td { animation: berubah 2s ease-out; }
        @keyframes kedip { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }
        .titik-live { animation: kedip 1.4s ease-in-out infinite; }
        .tabel-wrap { max-height: calc(100vh - 260px); }
        thead th { position: sticky; top: 0; z-index: 1; }
    </style>
</head>
<body class="bg-air-50 text-deep-900 min-h-screen">
    @include('partials.navbar', ['active' => 'realtime'])

    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 py-6 space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="section-tag">Telemetri langsung</span>
                <h2 class="text-[1.75rem] sm:text-[2rem] font-bold text-deep-900 mt-2">Data Sensor Masuk (Realtime)</h2>
                <p class="text-deep-500 mt-1 text-[14px] max-w-2xl">Setiap data baru dari node yang masuk ke database tampil otomatis di baris paling atas, tanpa perlu refresh. Status ikut diperbarui begitu dianalisis AI.</p>
            </div>
            <div class="flex items-center gap-3 text-[13px]">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-air-300">
                    <span id="indikator" class="titik-live w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span id="infoLive" class="font-semibold text-deep-700">Menyambung...</span>
                </span>
                <span class="px-3 py-1.5 rounded-full bg-white border border-air-300 font-mono text-deep-600">Data baru: <b id="jumlahBaru" class="text-teal-700">0</b></span>
            </div>
        </div>

        <div class="card bg-white border border-air-300 rounded-2xl shadow-card overflow-hidden">
            <div class="tabel-wrap overflow-auto">
                <table class="w-full text-[12.5px] whitespace-nowrap">
                    <thead class="bg-deep-900 text-white text-left">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold">ID</th>
                            <th class="px-3 py-2.5 font-semibold">Waktu (WIB)</th>
                            <th class="px-3 py-2.5 font-semibold">Node</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Tanah dangkal</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Tanah dalam</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Hujan</th>
                            <th class="px-3 py-2.5 font-semibold text-right">ax</th>
                            <th class="px-3 py-2.5 font-semibold text-right">ay</th>
                            <th class="px-3 py-2.5 font-semibold text-right">az</th>
                            <th class="px-3 py-2.5 font-semibold text-right">gx</th>
                            <th class="px-3 py-2.5 font-semibold text-right">gy</th>
                            <th class="px-3 py-2.5 font-semibold text-right">gz</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Pitch</th>
                            <th class="px-3 py-2.5 font-semibold text-right">Roll</th>
                            <th class="px-3 py-2.5 font-semibold">Status</th>
                            <th class="px-3 py-2.5 font-semibold">Prakiraan 1 jam</th>
                        </tr>
                    </thead>
                    <tbody id="isiTabel" class="divide-y divide-air-200 font-mono text-deep-800">
                        <tr><td colspan="16" class="px-3 py-8 text-center text-deep-400 font-sans">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="text-[12px] text-deep-400">ax..gz dalam satuan mentah MPU6050 (1 g = 16384, 1 derajat/detik = 131). Data diperiksa tiap 2 detik; menampilkan 40 data terbaru.</p>
    </div>

    <script>
        const NODES = @json($nodes);
        const URL_DATA = @json(url('/api/sensor-terbaru')) + '?limit=40';
        const tbody = document.getElementById('isiTabel');
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const angka = (v, d = 1, u = '') => (v === null || v === undefined || v === '') ? '<span class="text-deep-300">-</span>' : Number(v).toFixed(d) + u;
        const bulat = v => (v === null || v === undefined || v === '') ? '<span class="text-deep-300">-</span>' : Math.round(Number(v));
        const WARNA = { BAHAYA: 'bg-rose-600 text-white', WASPADA: 'bg-amber-400 text-deep-900', AMAN: 'bg-emerald-500 text-white' };
        const lencana = s => {
            const k = String(s || '').toUpperCase();
            return WARNA[k] ? `<span class="px-2 py-0.5 rounded-full text-[11px] font-bold font-sans ${WARNA[k]}">${k}</span>`
                            : `<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold font-sans bg-air-100 text-deep-500">menunggu AI</span>`;
        };
        const jam = t => { const s = String(t || ''); const m = s.match(/(\d{4})-(\d{2})-(\d{2})[ T](\d{2}:\d{2}:\d{2})/); return m ? `${m[3]}/${m[2]} ${m[4]}` : esc(s); };

        function baris(r) {
            const n = NODES[r.node_id] || {};
            return `<td class="px-3 py-2 text-deep-500">${r.id}</td>
                <td class="px-3 py-2">${jam(r.timestamp)}</td>
                <td class="px-3 py-2 font-sans"><b>${esc(n.nama || ('Node ' + r.node_id))}</b> <span class="text-deep-400 text-[11px]">${esc(n.kode || '')}</span></td>
                <td class="px-3 py-2 text-right">${angka(r.dangkal, 1, '%')}</td>
                <td class="px-3 py-2 text-right">${angka(r.dalam, 1, '%')}</td>
                <td class="px-3 py-2 text-right">${angka(r.hujan, 1, ' mm')}</td>
                <td class="px-3 py-2 text-right">${bulat(r.ax)}</td>
                <td class="px-3 py-2 text-right">${bulat(r.ay)}</td>
                <td class="px-3 py-2 text-right">${bulat(r.az)}</td>
                <td class="px-3 py-2 text-right">${bulat(r.gx)}</td>
                <td class="px-3 py-2 text-right">${bulat(r.gy)}</td>
                <td class="px-3 py-2 text-right">${bulat(r.gz)}</td>
                <td class="px-3 py-2 text-right">${angka(r.pitch, 2, '&deg;')}</td>
                <td class="px-3 py-2 text-right">${angka(r.roll, 2, '&deg;')}</td>
                <td class="px-3 py-2">${lencana(r.status)}</td>
                <td class="px-3 py-2">${r.forecast ? lencana(r.forecast) : '<span class="text-deep-300">-</span>'}</td>`;
        }

        const tampil = new Map();   // id -> status terakhir yang ditampilkan
        let pertama = true, jumlahBaru = 0;

        async function muat() {
            try {
                const res = await fetch(URL_DATA, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const rows = await res.json();                 // terbaru dulu
                if (pertama) tbody.innerHTML = rows.length ? '' : '<tr><td colspan="16" class="px-3 py-8 text-center text-deep-400 font-sans">Belum ada data.</td></tr>';
                // sisipkan dari yang paling lama supaya urutan tetap terbaru di atas
                for (const r of rows.slice().reverse()) {
                    const st = (r.status || '') + '|' + (r.forecast || '');
                    const ada = tbody.querySelector(`tr[data-id="${r.id}"]`);
                    if (!ada) {
                        if (!tampil.size && tbody.firstElementChild && !tbody.firstElementChild.dataset.id) tbody.innerHTML = '';
                        const tr = document.createElement('tr');
                        tr.dataset.id = r.id; tr.innerHTML = baris(r);
                        if (!pertama) { tr.className = 'baru'; jumlahBaru++; }
                        tbody.prepend(tr);
                    } else if (tampil.get(r.id) !== st) {
                        ada.innerHTML = baris(r); ada.className = ''; void ada.offsetWidth; ada.className = 'berubah';
                    }
                    tampil.set(r.id, st);
                }
                while (tbody.children.length > 40) { tampil.delete(Number(tbody.lastElementChild.dataset.id)); tbody.lastElementChild.remove(); }
                pertama = false;
                document.getElementById('jumlahBaru').textContent = jumlahBaru;
                document.getElementById('infoLive').textContent = 'Live, diperbarui ' + new Date().toLocaleTimeString('id-ID');
                document.getElementById('indikator').className = 'titik-live w-2.5 h-2.5 rounded-full bg-emerald-500';
            } catch (e) {
                document.getElementById('infoLive').textContent = 'Koneksi terputus, mencoba lagi...';
                document.getElementById('indikator').className = 'w-2.5 h-2.5 rounded-full bg-rose-500';
            } finally {
                setTimeout(muat, 2000);
            }
        }
        muat();
    </script>
</body>
</html>
