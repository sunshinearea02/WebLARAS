<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LARAS Control Center — Landslide Alert and Rescue Adaptive System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        @keyframes pulse-ring {
            0% { transform: scale(0.93); opacity: 1; }
            80%, 100% { transform: scale(1.15); opacity: 0; }
        }
        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes fade-slide-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-slide-up 0.6s ease forwards; }
        .animate-fade-up-delay-1 { animation: fade-slide-up 0.6s ease 0.15s forwards; opacity: 0; }
        .animate-fade-up-delay-2 { animation: fade-slide-up 0.6s ease 0.3s forwards; opacity: 0; }
        .animate-fade-up-delay-3 { animation: fade-slide-up 0.6s ease 0.45s forwards; opacity: 0; }

        #landing-page { transition: opacity 0.4s ease, transform 0.4s ease; }
        #dashboard-page { transition: opacity 0.4s ease, transform 0.4s ease; display: none; }

        .status-aman { background: #f0fdf4; border-color: #22c55e; }
        .status-waspada { background: #fefce8; border-color: #eab308; }
        .status-bahaya { background: #fff1f2; border-color: #f43f5e; }
        .status-unknown { background: #f8fafc; border-color: #94a3b8; }

        .bar-aman { width: var(--bar-w); background: #22c55e; }
        .bar-waspada { width: var(--bar-w); background: #eab308; }
        .bar-bahaya { width: var(--bar-w); background: #f43f5e; }

        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }

        details.edu-accordion summary { cursor: pointer; list-style: none; }
        details.edu-accordion summary::-webkit-details-marker { display: none; }
        details.edu-accordion[open] summary .chev { transform: rotate(180deg); }

        @keyframes float-hero {
            0%   { transform: translateY(20px); opacity: 0.7; }
            50%  { transform: translateY(-15px); opacity: 1; }
            100% { transform: translateY(20px); opacity: 0.7; }
        }
        
    </style>

    <script>
        const phpLogs    = @json($logs);
        const phpCurrent = @json($current ?? null);
        const phpWeather = @json($weatherInfo ?? 'N/A');
    </script>
</head>
<body class="bg-slate-50 antialiased text-slate-800 selection:bg-blue-200">


<div id="landing-page">

    <div class="fixed top-0 left-0 right-0 h-[600px] bg-gradient-to-b from-blue-50 via-indigo-50/30 to-transparent -z-10 pointer-events-none"></div>
    <div class="fixed top-20 right-[-10%] w-[500px] h-[500px] rounded-full bg-indigo-200/40 blur-3xl -z-10 pointer-events-none"></div>
    <div class="fixed top-[40%] left-[-10%] w-[400px] h-[400px] rounded-full bg-blue-100/50 blur-3xl -z-10 pointer-events-none"></div>

    <nav id="landingNav" class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
        <div class="flex items-center space-x-3">
            <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-10 h-10 rounded-xl object-cover shadow-md">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 leading-none">LARAS</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mt-0.5">Landslide Alert &amp; Rescue System</p>
            </div>
        </div>
        <div class="flex items-center space-x-5">
            <a href="#features" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors hidden lg:inline">Fitur</a>
            <a href="#algorithms" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors hidden lg:inline">Algoritma</a>
            <a href="#edukasi" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors hidden lg:inline">Edukasi</a>
            <a href="#kontak-darurat" class="text-sm font-medium text-rose-600 hover:text-rose-700 transition-colors hidden lg:inline">Kontak Darurat</a>
            <a href="#developers" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors hidden lg:inline">Developer</a>
            <button onclick="enterDashboard()" class="group px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center space-x-2">
                <span>Live Control Center</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="group-hover:translate-x-1 transition-transform"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 pb-24">

        <section class="pt-20 md:pt-28 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center animate-fade-up">
         
            <div class="lg:col-span-7 flex flex-col justify-center">
                <div class="inline-flex items-center space-x-2 bg-blue-50 border border-blue-100 px-3.5 py-1.5 rounded-full mb-6 w-fit">
                    <span class="flex h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-xs font-semibold text-blue-700 tracking-wide font-mono">EDGE AIOT · LSTM-ENSEMBLE FORECASTING · TRIASE BENCANA</span>
                </div>
                <h1 class="text-5xl md:text-6xl font-black tracking-tight text-slate-900 leading-tight mb-6">
                    LARAS<br>
                    <span class="text-blue-600">Peringatan Dini</span><br>
                    dan Navigasi Penyelamatan Longsor
                </h1>
                <p class="text-lg text-slate-600 leading-relaxed mb-8 max-w-lg">
                    Landslide Alert and Rescue Adaptive System memadukan sensor IoT multi-parameter, triase AI tiga lapis, dan komunikasi LoRa tanpa seluler untuk mempercepat peringatan dini sekaligus memandu penyelamatan warga di kawasan rawan longsor.
                </p>
                <div class="flex flex-wrap gap-4">
                    <button onclick="enterDashboard()" class="group px-7 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-2xl shadow-xl shadow-blue-200 transition-all flex items-center space-x-2.5">
                        <span>Buka Control Center</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="group-hover:translate-x-1 transition-transform"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                    <a href="#features" class="px-7 py-3.5 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 text-base font-bold rounded-2xl transition-all">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
                <div class="mt-6">
                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[11px] font-bold px-3 py-1.5 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        Lapisan digitalisasi EWS-L PVMBG — bukan pengganti sistem eksisting
                    </span>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if($current)
                    <div class="flex items-center gap-2 bg-white/80 backdrop-blur border border-slate-200 px-3 py-1.5 rounded-full">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-semibold text-slate-600">Live Data Active</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/80 backdrop-blur border border-slate-200 px-3 py-1.5 rounded-full">
                        <span class="text-xs font-mono text-slate-500">&#9729; {{ $weatherInfo['condition'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="lg:col-span-5 animate-fade-up-delay-1">
                <div class="relative flex justify-center items-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 200" class="w-56 md:w-64 h-auto float-hero" style="animation: float-hero 5s ease-in-out infinite;">
                        <path d="M0 170 L90 60 L150 120 L200 40 L280 170 Z" fill="#e0e7ff"/>
                        <path d="M0 170 L90 60 L150 120 L200 40 L280 170 Z" fill="none" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/>
                        <circle cx="90" cy="60" r="6" fill="#2563eb"/>
                        <circle cx="90" cy="60" r="12" fill="none" stroke="#2563eb" stroke-width="2" opacity="0.5"/>
                        <circle cx="200" cy="40" r="6" fill="#059669"/>
                        <circle cx="200" cy="40" r="12" fill="none" stroke="#059669" stroke-width="2" opacity="0.5"/>
                        <line x1="90" y1="60" x2="200" y2="40" stroke="#94a3b8" stroke-width="1.5" stroke-dasharray="4 3"/>
                        <rect x="20" y="176" width="240" height="6" rx="3" fill="#cbd5e1"/>
                    </svg>
                </div>
                <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-200/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                        <span class="text-xs font-mono text-slate-400">laras-control-center</span>
                    </div>
                    @if($current)
                    <div class="@php
                        $s = strtoupper($current->status_ai);
                        echo ($s === 'BAHAYA') ? 'bg-rose-50 border-rose-200' : (($s === 'WASPADA') ? 'bg-yellow-50 border-yellow-200' : 'bg-emerald-50 border-emerald-200');
                    @endphp rounded-2xl border p-4 mb-4">
                        <p class="text-xs font-bold uppercase tracking-widest @php echo ($s === 'BAHAYA') ? 'text-rose-400' : (($s === 'WASPADA') ? 'text-yellow-500' : 'text-emerald-500'); @endphp mb-1">System Condition</p>
                        <p class="text-2xl font-black @php echo ($s === 'BAHAYA') ? 'text-rose-700' : (($s === 'WASPADA') ? 'text-yellow-700' : 'text-emerald-700'); @endphp">
                            @if($s === 'BAHAYA') ⚠ BAHAYA : EVAKUASI
                            @elseif($s === 'WASPADA') ⚡ WASPADA : SIAGA
                            @else ✓ SISTEM AMAN
                            @endif
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide mb-1">Moisture</p>
                            <p class="text-xl font-bold font-mono text-slate-800">{{ number_format($current->soil_moisture, 1) }}<span class="text-xs font-normal text-slate-400">%</span></p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide mb-1">Vibrasi</p>
                            <p class="text-xl font-bold font-mono text-slate-800">{{ number_format($current->vibration ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wide mb-1">Pitch</p>
                            <p class="text-xl font-bold font-mono text-slate-800">{{ number_format($current->pitch ?? 0, 1) }}<span class="text-xs font-normal text-slate-400">°</span></p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-10 text-slate-400">
                        <p class="text-sm">Belum ada data sensor</p>
                    </div>
                    @endif
                </div>
            </div>
        </section>

        <section id="urgensi" class="mt-24">
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl p-8 md:p-10 text-white">
                <div class="flex items-center gap-2 mb-5">
                    <span class="flex h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-rose-300 font-mono">Urgensi</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black mb-6 max-w-2xl">Kejadian dan korban terdampak longsor meningkat tajam dalam setahun terakhir</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="stat-card bg-white/10 border border-white/10 rounded-2xl p-5">
                        <p class="text-[11px] uppercase tracking-widest text-indigo-200 font-bold mb-2">Kejadian Tanah Longsor</p>
                        <div class="flex items-end gap-3">
                            <span class="text-4xl font-black">330</span>
                            <span class="text-sm text-indigo-200 mb-1">kejadian (2025)</span>
                        </div>
                        <p class="text-xs text-indigo-300 mt-1 font-mono">vs 184 kejadian pada 2024</p>
                    </div>
                    <div class="stat-card bg-white/10 border border-white/10 rounded-2xl p-5">
                        <p class="text-[11px] uppercase tracking-widest text-indigo-200 font-bold mb-2">Korban Terdampak</p>
                        <div class="flex items-end gap-3">
                            <span class="text-4xl font-black">68.935</span>
                            <span class="text-sm text-indigo-200 mb-1">jiwa (2025)</span>
                        </div>
                        <p class="text-xs text-indigo-300 mt-1 font-mono">vs 48.344 jiwa pada 2024</p>
                    </div>
                </div>
                <p class="text-sm text-indigo-100 leading-relaxed max-w-2xl">
                    Sistem peringatan dini longsor (EWS-L) yang ada saat ini masih sangat bergantung pada pembacaan manual oleh kader di lapangan, sehingga rentan terjadi jeda waktu peringatan — terutama pada malam hari atau saat akses lapangan terganggu cuaca buruk. LARAS dikembangkan sebagai lapisan digitalisasi tambahan untuk mempersempit jeda tersebut.
                </p>
                <p class="text-[11px] text-indigo-300 mt-4 font-mono">Sumber: BNPB, 2025</p>
            </div>
        </section>

        <section id="features" class="mt-24 animate-fade-up-delay-2">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Kemampuan Sistem</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Deteksi, Triase, dan Penyelamatan</h2>
                <p class="text-slate-500 mt-3 max-w-md mx-auto">Rantai kemampuan LARAS dari sensor di lapangan sampai navigasi penyelamatan warga.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Sensor IoT Multi-Kedalaman</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Kelembapan tanah 2 kedalaman (dangkal &amp; dalam), MPU6050 dengan filter komplementer untuk pitch/roll, dan rain gauge tipping bucket.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">AI Triase 3 Lapis</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">LSTM-Ensemble Forecasting + verifikasi silang Random Forest + ambang batas fisik intensitas-durasi hujan sebagai jaring pengaman.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><circle cx="6" cy="18" r="2.2"/><circle cx="18" cy="6" r="2.2"/><circle cx="12" cy="12" r="2.2"/><path d="M8 16.5L10.5 13.5M13.5 10.5L16 7.5"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Komunikasi Tanpa Seluler</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">LoRa mesh ke gateway, dirancang untuk wilayah rawan longsor yang akses selulernya sering terputus.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2"><path d="M12 2C8 6 5 9.5 5 13a7 7 0 0014 0c0-3.5-3-7-7-11z"/><path d="M9 13l2 2 4-4" stroke="#e11d48"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2">Navigasi Penyelamatan</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Rute evakuasi tervalidasi, tombol SOS, dan fitur check-in warga di titik kumpul. <span class="text-amber-600 font-semibold">(Roadmap)</span></p>
                </div>
            </div>
        </section>

        <section id="algorithms" class="mt-24">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-purple-600 font-mono">Teknologi Inti</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Arsitektur Triase AI 3 Lapis</h2>
                <p class="text-slate-500 mt-3 max-w-lg mx-auto">Tiga lapisan yang saling mengoreksi, ditutup dengan verifikasi manusia sebelum status Bahaya disebarluaskan.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-7 text-white shadow-xl shadow-blue-200">
                    <div class="flex items-center gap-3 mb-4 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-widest text-blue-200 font-mono bg-white/10 px-2 py-1 rounded-md">Layer 1</span>
                        <h3 class="text-lg font-black">LSTM-Ensemble Forecasting</h3>
                    </div>
                    <p class="text-blue-100 text-sm leading-relaxed mb-4">Ensemble LSTM dengan Monte Carlo Dropout untuk mengestimasi ketidakpastian prediksi. Window 60 langkah (5 menit) memproyeksikan horizon 120 langkah (10 menit) ke depan.</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="text-xs text-blue-200">Window / Horizon</p>
                            <p class="text-sm font-bold font-mono">60 / 120 step</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="text-xs text-blue-200">Keluaran</p>
                            <p class="text-[11px] font-bold font-mono leading-tight">Moisture dangkal, dalam, pitch</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-7 text-white shadow-xl shadow-emerald-200">
                    <div class="flex items-center gap-3 mb-4 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-widest text-emerald-200 font-mono bg-white/10 px-2 py-1 rounded-md">Layer 2</span>
                        <h3 class="text-lg font-black">Verifikasi Silang Random Forest</h3>
                    </div>
                    <p class="text-emerald-100 text-sm leading-relaxed mb-4">Mengklasifikasikan status AMAN / WASPADA / BAHAYA secara independen. Jika hasil RF dan LSTM tidak sepakat, sistem otomatis menetapkan status sementara WASPADA.</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="text-xs text-emerald-200">N-Estimators</p>
                            <p class="text-lg font-bold font-mono">100</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="text-xs text-emerald-200">Fungsi</p>
                            <p class="text-[11px] font-bold font-mono leading-tight">Cross-check LSTM</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-amber-600 to-orange-700 rounded-2xl p-7 text-white shadow-xl shadow-amber-200">
                    <div class="flex items-center gap-3 mb-4 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-widest text-amber-200 font-mono bg-white/10 px-2 py-1 rounded-md">Layer 3</span>
                        <h3 class="text-lg font-black">Ambang Fisik Curah Hujan</h3>
                    </div>
                    <p class="text-amber-100 text-sm leading-relaxed mb-4">Kurva ambang batas intensitas-durasi hujan tervalidasi literatur (Segoni dkk., 2018), berfungsi sebagai jaring pengaman yang independen dari performa model AI.</p>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="text-xs text-amber-200">Basis</p>
                            <p class="text-[11px] font-bold font-mono leading-tight">Kurva Intensitas–Durasi (I-D)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-slate-900 rounded-2xl p-6 md:p-7 flex flex-col md:flex-row items-start md:items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-300 font-mono mb-1">Human in the Loop</p>
                    <p class="text-white text-sm leading-relaxed">Setiap status <strong>BAHAYA</strong> tidak langsung disebarluaskan ke warga. Notifikasi awal terlebih dahulu dikirim ke kader terlatih untuk verifikasi lapangan, memastikan akuntabilitas keputusan tidak diserahkan sepenuhnya kepada AI.</p>
                </div>
            </div>
        </section>

        <section id="cara-kerja" class="mt-24">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Alur Sistem</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Cara Kerja LARAS</h2>
                <p class="text-slate-500 mt-3 max-w-lg mx-auto">Dari sensor di lereng sampai notifikasi dan navigasi penyelamatan ke warga.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                $flow = [
                    ['label' => 'Sensor Unit', 'desc' => 'Moisture, IMU, rain gauge di lereng', 'color' => 'blue'],
                    ['label' => 'LoRa Mesh', 'desc' => 'Transmisi tanpa seluler', 'color' => 'indigo'],
                    ['label' => 'Gateway / MQTT', 'desc' => 'Broker penerima data', 'color' => 'purple'],
                    ['label' => 'PostgreSQL + Edge Pi', 'desc' => 'Arsip data & inferensi model', 'color' => 'violet'],
                    ['label' => 'Klasifikasi Status', 'desc' => 'AMAN / WASPADA / BAHAYA', 'color' => 'amber'],
                    ['label' => 'Notifikasi & Navigasi', 'desc' => 'Telegram/SMS/sirine + rute evakuasi', 'color' => 'rose'],
                ];
                @endphp
                @foreach($flow as $i => $f)
                <div class="relative bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
                    <div class="w-8 h-8 mx-auto rounded-full bg-{{ $f['color'] }}-100 text-{{ $f['color'] }}-700 flex items-center justify-center font-black text-xs mb-3">{{ $i + 1 }}</div>
                    <p class="font-bold text-slate-900 text-xs mb-1">{{ $f['label'] }}</p>
                    <p class="text-[10px] text-slate-500 leading-snug">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <section id="status-purwarupa" class="mt-24">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-600 font-mono">Transparansi</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Status Purwarupa</h2>
                <p class="text-slate-500 mt-3 max-w-xl mx-auto">LARAS terbuka soal komponen mana yang sudah teruji lapangan dan mana yang masih dalam rencana pengadaan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 border border-emerald-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wide">Sudah Terealisasi &amp; Teruji Lapangan</h3>
                    </div>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">✓</span>Mikrokontroler ESP32</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">✓</span>Probe kelembapan tanah dangkal (LM393)</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">✓</span>Accelerometer/gyroscope MPU6050</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">✓</span>Baterai Li-ion + BMS</li>
                        <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">✓</span>Casing hasil cetak 3D</li>
                    </ul>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-amber-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wide">Rencana Pengadaan / Belum Diuji Lapangan</h3>
                    </div>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">○</span>Probe kelembapan tanah dalam (LM393)</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">○</span>Rain gauge tipping bucket</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">○</span>Modul komunikasi LoRa</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">○</span>Panel surya</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">○</span>Sistem penjangkaran mekanis</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">○</span>Modul navigasi penyelamatan</li>
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white text-center">
                    <p class="text-xs uppercase tracking-widest text-blue-200 font-bold mb-2">MAE Moisture Turun</p>
                    <p class="text-4xl font-black">11,3%</p>
                    <p class="text-xs text-blue-200 mt-1">dibanding baseline persistence</p>
                </div>
                <div class="bg-gradient-to-br from-purple-600 to-fuchsia-700 rounded-2xl p-6 text-white text-center">
                    <p class="text-xs uppercase tracking-widest text-purple-200 font-bold mb-2">MAE Pitch Turun</p>
                    <p class="text-4xl font-black">24,8%</p>
                    <p class="text-xs text-purple-200 mt-1">dibanding baseline persistence</p>
                </div>
            </div>
            <p class="text-center text-xs text-slate-400 font-mono">Evaluasi dari 852 baris data mentah / 765 baris bersih dari 13 sesi pengamatan lapangan.</p>
        </section>

        <section id="developers" class="mt-24">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-rose-600 font-mono">Developers</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Developer Team</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                $devs = [
                    [
                        'name' => 'Devi Putri Sekar Arum',
                        'nim' => '5024241049',
                        'role' => 'Machine Learning & AI Engineer',
        
                        'letter' => 'D',
                        'gradient' => 'from-rose-400 to-pink-600'
                    ],
                    [
                        'name' => 'Athaya Khairani Adi',
                        'nim' => '5024241007',
                        'role' => 'Web Developer & Software Engineer',
                        
                        'letter' => 'A',
                        'gradient' => 'from-emerald-400 to-teal-600'
                    ],
                     [
                        'name' => 'Atika Najwa Azzahra',
                        'nim' => '5024241091',
                        'role' => 'Hardware Engineer & Prototype Designer',
                        'letter' => 'A',
                        'gradient' => 'from-sky-400 to-blue-600'
                    ],
                    [
                        'name' => 'Ir. Arta Kusuma Hernanda, S.T., M.T.',
                        'nim' => 'Dosen Pembimbing',
                        'role' => 'Pembimbing Tim Bismillah Medalis',
                        'letter' => 'A',
                        'gradient' => 'from-slate-500 to-slate-700'
                    ],
                ];
                @endphp
                @foreach($devs as $dev)
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm text-center hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $dev['gradient'] }} flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <span class="text-white font-black text-xl">{{ $dev['letter'] }}</span>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mb-1">
                        {{ $dev['name'] }}
                    </h4>

                    <p class="text-xs font-mono text-slate-400 mb-2">
                        {{ $dev['nim'] }}
                    </p>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $dev['role'] }}
                    </p>

                    @if(isset($dev['extra_role']))
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ $dev['extra_role'] }}
                        </p>
                    @endif
                </div>
                @endforeach
            </div>

           
        </section>

        <section id="edukasi" class="mt-24">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 font-mono">Edukasi Kebencanaan</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Tanda-Tanda dan Penanganan Longsor</h2>
                <p class="text-slate-500 mt-3 max-w-xl mx-auto">Konten edukasi umum kebencanaan longsor. Karakteristik tiap wilayah bisa berbeda — validasikan dengan BPBD/kader setempat.</p>
            </div>

            <div class="space-y-4 max-w-3xl mx-auto">
                <details class="edu-accordion bg-white rounded-2xl border border-slate-200 shadow-sm p-5" open>
                    <summary class="flex items-center justify-between font-bold text-slate-900">
                        <span class="flex items-center gap-2"><span class="text-lg"></span> A. Kenali Tanda-Tanda Awal</span>
                        <svg class="chev transition-transform" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600 pl-1">
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">●</span>Muncul retakan baru di tanah atau tembok, terutama sejajar lereng.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">●</span>Air rembesan atau mata air baru muncul di lereng, atau air sumur tiba-tiba keruh.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">●</span>Pohon, tiang listrik, atau pagar mulai miring ke satu arah.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">●</span>Muncul suara gemuruh dari arah lereng, terutama saat/setelah hujan deras berkepanjangan.</li>
                        <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">●</span>Pintu atau jendela rumah di lereng tiba-tiba sulit dibuka/ditutup (indikasi pergeseran struktur tanah).</li>
                    </ul>
                </details>

                <details class="edu-accordion bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <summary class="flex items-center justify-between font-bold text-slate-900">
                        <span class="flex items-center gap-2"><span class="text-lg"></span> B. Sebelum Terjadi (Kesiapsiagaan)</span>
                        <svg class="chev transition-transform" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600 pl-1">
                        <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">●</span>Kenali rute evakuasi dan titik kumpul terdekat (nanti dapat ditautkan ke modul navigasi LARAS).</li>
                        <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">●</span>Siapkan tas siaga bencana: dokumen penting, senter, obat-obatan, air minum, uang tunai secukupnya.</li>
                        <li class="flex items-start gap-2"><span class="text-blue-500 mt-0.5">●</span>Pantau status LARAS dan informasi BMKG secara berkala saat musim hujan.</li>
                    </ul>
                </details>

                <details class="edu-accordion bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <summary class="flex items-center justify-between font-bold text-slate-900">
                        <span class="flex items-center gap-2"><span class="text-lg"></span> C. Saat dan Setelah Kejadian</span>
                        <svg class="chev transition-transform" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600 pl-1">
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-0.5">●</span>Saat status Bahaya, segera menuju titik kumpul lewat rute yang sudah divalidasi, jangan mengambil jalan pintas yang belum dikonfirmasi aman.</li>
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-0.5">●</span>Bantu prioritaskan lansia, anak-anak, dan penyandang disabilitas terlebih dulu.</li>
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-0.5">●</span>Setelah tiba di titik kumpul, lakukan check-in (lewat aplikasi atau tombol fisik) supaya tim SAR tahu siapa saja yang sudah aman.</li>
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-0.5">●</span>Jangan kembali ke rumah/lokasi terdampak sebelum ada pernyataan aman resmi dari petugas BPBD/kader setempat.</li>
                    </ul>
                </details>
            </div>
        </section>

        <section id="kontak-darurat" class="mt-24">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-rose-600 font-mono">Darurat</span>
                <h2 class="text-4xl font-black text-slate-900 mt-2">Kontak Darurat</h2>
                <p class="text-slate-500 mt-3 max-w-xl mx-auto">Nomor penting per Juli 2026. LARAS adalah alat bantu, bukan pengganti prosedur resmi — tetap ikuti arahan kader/petugas setempat.</p>
            </div>

            <div class="text-center mb-8">
                <a href="tel:112" class="inline-flex items-center gap-3 px-8 py-4 bg-rose-600 hover:bg-rose-700 text-white font-black text-lg rounded-2xl shadow-xl shadow-rose-200 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    Hubungi 112
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 max-w-4xl mx-auto">
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-3xl font-black text-rose-600 font-mono mb-2">112</p>
                    <h4 class="font-bold text-slate-900 text-sm mb-1">Nomor Tunggal Panggilan Darurat</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Gratis, tersambung ke BPBD/Damkar/Polisi/Kesehatan di wilayah yang sudah terlayani, bisa dihubungi meski HP terkunci.</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-3xl font-black text-blue-600 font-mono mb-2">117</p>
                    <h4 class="font-bold text-slate-900 text-sm mb-1">BNPB</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Info dan koordinasi penanganan bencana skala nasional.</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <p class="text-3xl font-black text-emerald-600 font-mono mb-2">115</p>
                    <h4 class="font-bold text-slate-900 text-sm mb-1">Basarnas</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Kebutuhan pencarian dan evakuasi (SAR) korban.</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-dashed border-slate-300 shadow-sm">
                    <p class="text-lg font-black text-slate-400 font-mono mb-2">(031) 99860466</p>
                    <h4 class="font-bold text-slate-900 text-sm mb-1">BPBD Setempat</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Nomor BPBD kabupaten/kota tempat purwarupa/pilot LARAS dipasang.</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-dashed border-slate-300 shadow-sm">
                    <p class="text-lg font-black text-slate-400 font-mono mb-2">menyusul</p>
                    <h4 class="font-bold text-slate-900 text-sm mb-1">Kader / Posko Desa Terdekat</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Nomor kader EWS-L atau posko desa mitra.</p>
                </div>
            </div>
            <p class="text-center text-xs text-slate-400 mt-8 max-w-lg mx-auto">Catatan: saat darurat sungguhan, warga tetap harus mengikuti arahan kader/petugas resmi setempat. LARAS adalah alat bantu, bukan pengganti prosedur resmi.</p>
        </section>

        <section class="mt-24 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-12 text-center shadow-2xl shadow-blue-200">
            <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-20 h-20 rounded-2xl object-cover mx-auto mb-5 shadow-xl border-2 border-white/20">
            <h2 class="text-3xl font-black text-white mb-4">Siap Memantau?</h2>
            <p class="text-blue-100 mb-8 max-w-md mx-auto">Akses dashboard real-time LARAS untuk melihat kondisi sensor terkini dan riwayat telemetri lengkap.</p>
            <div class="flex flex-wrap justify-center gap-3">
                <button onclick="enterDashboard()" class="group px-8 py-4 bg-white text-blue-700 font-black text-base rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-3">
                    <span>Buka Live Dashboard</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="group-hover:translate-x-1 transition-transform"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
                <a href="#edukasi" class="px-8 py-4 bg-white/10 border-2 border-white/30 hover:bg-white/20 text-white font-black text-base rounded-2xl transition-all">
                    Baca Edukasi Kebencanaan
                </a>
            </div>
        </section>
    </div>

    <footer class="border-t border-slate-200 mt-20">
        <div class="max-w-7xl mx-auto px-6 py-10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-9 h-9 rounded-lg object-cover">
                <div>
                    <p class="text-sm font-bold text-slate-900 leading-none">LARAS</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Institut Teknologi Sepuluh Nopember · Departemen Teknik Komputer</p>
                </div>
            </div>
            <a href="tel:112" class="text-xs font-bold text-rose-600 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                Darurat: 112
            </a>
        </div>
    </footer>
</div>

<div id="dashboard-page" style="display:none;">

    <nav class="max-w-7xl mx-auto px-4 py-2.5 flex items-center justify-between bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200 gap-3">
        
        <!-- Sisi Kiri: Kembali + Logo + Title -->
        <div class="flex items-center gap-3 shrink-0">
            <a href="/" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition border border-slate-200">
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

        <!-- Sisi Kanan: Menu Navigasi + Status Sync -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
            <!-- Tombol Edukasi -->
            <a href="/edukasi" class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Edukasi
            </a>

            <!-- Tombol Data Lanskap -->
            <a href="/lanskap" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Data Lanskap
            </a>

            <!-- Tombol Peta Evakuasi -->
            <a href="/peta-evakuasi" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                Peta Evakuasi
            </a>

            <!-- Tombol Kontak Darurat -->
            <a href="tel:112" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs rounded-xl transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.79 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak Darurat (112)
            </a>

            <!-- Badge Sync Active -->
            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[11px] rounded-xl shrink-0 font-mono">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                SYNC ACTIVE
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-4 md:p-6 max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-8 space-y-6">

                @php
                    $status = strtoupper($current->status_ai ?? 'UNKNOWN');
                    $isBahaya = $status === 'BAHAYA';
                    $isWaspada = $status === 'WASPADA';
                    if ($isBahaya) {
                        $statusBg = 'bg-rose-50 border-rose-500';
                        $statusTextColor = 'text-rose-700';
                        $statusLabel = '⚠ BAHAYA : EVAKUASI SEGERA';
                    } elseif ($isWaspada) {
                        $statusBg = 'bg-yellow-50 border-yellow-500';
                        $statusTextColor = 'text-yellow-700';
                        $statusLabel = '⚡ WASPADA : SIAGA PENUH';
                    } else {
                        $statusBg = 'bg-white border-emerald-500';
                        $statusTextColor = 'text-emerald-700';
                        $statusLabel = '✓ SISTEM AMAN';
                    }
                @endphp

                <div id="statusCard" class="transition-all duration-300 rounded-2xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between border-l-4 gap-4 {{ $statusBg }}">
                    <div>
                        <h2 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Status Kondisi Sistem
                        </h2>
                        <p id="statusText" class="text-3xl font-extrabold tracking-tight {{ $statusTextColor }}">{{ $statusLabel }}</p>
                        <div class="flex items-center gap-3 mt-1 text-xs text-slate-500">
                            <span>Status DB: <strong class="font-mono text-slate-700">{{ $status }}</strong></span>
                        </div>
                    </div>
                    <div class="md:border-l md:border-slate-200/60 md:pl-6 flex flex-col items-start md:items-end gap-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Last Telemetry Update</p>
                        <p id="lastUpdate" class="font-mono text-sm md:text-base font-semibold text-slate-700 flex items-center md:justify-end gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-blue-500 inline-block"></span>
                            {{ $current->timestamp ?? '-' }}
                        </p>
                        @if($isBahaya || $isWaspada)
                        <a href="tel:112" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            Hubungi Darurat
                        </a>
                        @endif
                    </div>
                </div>

                @if($isBahaya)
                <div id="humanInLoopBanner" class="flex items-start gap-3 bg-rose-50 border border-rose-200 rounded-2xl p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2.5" class="flex-shrink-0 mt-0.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <p class="text-xs text-rose-700 leading-relaxed"><strong>Notifikasi telah dikirim ke kader untuk verifikasi lapangan.</strong> Status akan disebarluaskan ke warga setelah dikonfirmasi (human-in-the-loop).</p>
                </div>
                @endif

<div class="space-y-4">
    <!-- Kelembapan Tanah: Dangkal & Dalam -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Kelembapan Tanah Dangkal</h3>
                <span class="text-[9px] font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">PROBE 1</span>
            </div>
            <div class="flex items-baseline space-x-1">
                <p id="valMoisture" class="text-3xl font-light text-slate-800 font-mono">{{ number_format($current->soil_moisture ?? 0, 1) }}</p>
                <span class="text-sm font-semibold text-slate-400">%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                @php $mPct = min(100, max(0, $current->soil_moisture ?? 0)); @endphp
                <div id="valMoistureBar" class="h-1.5 rounded-full transition-all duration-300 {{ $mPct > 75 ? 'bg-rose-500' : ($mPct > 55 ? 'bg-yellow-500' : 'bg-blue-500') }}" style="width: {{ $mPct }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Kelembapan lapisan atas, indikator awal perubahan kondisi tanah</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Kelembapan Tanah Dalam</h3>
                <span class="text-[9px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">PROBE 2</span>
            </div>
            <div class="flex items-baseline space-x-1">
                <p id="valMoistureDeep" class="text-3xl font-light text-slate-800 font-mono">{{ number_format($current->moisture_percent_deep ?? 0, 1) }}</p>
                <span class="text-sm font-semibold text-slate-400">%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                @php $mdPct = min(100, max(0, $current->moisture_percent_deep ?? 0)); @endphp
                <div id="valMoistureDeepBar" class="h-1.5 rounded-full transition-all duration-300 {{ $mdPct > 75 ? 'bg-rose-500' : ($mdPct > 55 ? 'bg-yellow-500' : 'bg-emerald-500') }}" style="width: {{ $mdPct }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Saturasi lapisan bawah, indikator kestabilan lereng</p>
        </div>
    </div>

    <!-- Accelerometer & Gyroscope -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-3 border-b border-slate-100 pb-2">Accelerometer (m/s²)</h3>
            <div id="valAccel" class="grid grid-cols-3 gap-2 text-center font-mono">
                <div class="bg-red-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-red-400 mb-1">X</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->accel_x ?? 0, 1) }}</span>
                </div>
                <div class="bg-amber-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-amber-400 mb-1">Y</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->accel_y ?? 0, 1) }}</span>
                </div>
                <div class="bg-emerald-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-emerald-400 mb-1">Z</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->accel_z ?? 0, 1) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-3 border-b border-slate-100 pb-2">Gyroscope (rad/s)</h3>
            <div id="valGyro" class="grid grid-cols-3 gap-2 text-center font-mono">
                <div class="bg-purple-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-purple-400 mb-1">X</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->gyro_x ?? 0, 1) }}</span>
                </div>
                <div class="bg-fuchsia-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-fuchsia-400 mb-1">Y</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->gyro_y ?? 0, 1) }}</span>
                </div>
                <div class="bg-blue-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-blue-400 mb-1">Z</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->gyro_z ?? 0, 1) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rain Gauge (berdiri sendiri, full width) -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><path d="M16 13v8M8 13v8M12 15v8M20 16.58A5 5 0 0018 7h-1.26A8 8 0 104 15.25"/></svg>
                Curah Hujan (Rain Gauge)
            </h3>
            <span class="text-[9px] font-bold text-sky-600 bg-sky-100 px-2 py-0.5 rounded-full">AKTIF</span>
        </div>
        <div class="flex items-end justify-between flex-wrap gap-3">
            <div class="flex items-end gap-2">
                <span id="valRainfall" class="text-4xl font-black text-sky-600 font-mono">{{ number_format($current->rainfall_mm ?? 0, 1) }}</span>
                <span class="text-sm text-slate-400 font-semibold mb-1">mm/jam</span>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-slate-400 uppercase tracking-wide font-bold">Kategori Intensitas</p>
                @php
                    $r = $current->rainfall_mm ?? 0;
                    $rainCat = $r > 50 ? ['Sangat Lebat', 'text-rose-600'] : ($r > 20 ? ['Lebat', 'text-orange-600'] : ($r > 5 ? ['Sedang', 'text-amber-600'] : ($r > 0 ? ['Ringan', 'text-sky-600'] : ['Tidak Ada Hujan', 'text-slate-400'])));
                @endphp
                <p id="valRainfallCategory" class="text-sm font-bold {{ $rainCat[1] }}">{{ $rainCat[0] }}</p>
            </div>
        </div>
        <p class="text-[10px] text-slate-400 mt-3">Intensitas curah hujan dari sensor tipping bucket</p>
    </div>
</div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80 text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-2">Pitch</p>
                        <p id="valPitch" class="text-2xl font-bold font-mono text-slate-800">{{ number_format($current->pitch ?? 0, 2) }}<span class="text-sm text-slate-400">°</span></p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80 text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-2">Roll</p>
                        <p id="valRoll" class="text-2xl font-bold font-mono text-slate-800">{{ number_format($current->roll ?? 0, 2) }}<span class="text-sm text-slate-400">°</span></p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80 text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-2">Vibrasi</p>
                        <p id="valVibration" class="text-2xl font-bold font-mono text-slate-800">{{ number_format($current->vibration ?? 0, 3) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Soil Moisture History (Dangkal)</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevMoist()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592;</button>
                            <span id="moistSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextMoist()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="moistureChart" height="100"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Soil Moisture History (Dalam)</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevMoistDeep()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592;</button>
                            <span id="moistDeepSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextMoistDeep()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="moistureDeepChart" height="100"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Accelerometer History</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevAccel()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592;</button>
                            <span id="accelSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextAccel()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="accelChart" height="100"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Landslide Status History</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevSlide()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592; Prev</button>
                            <span id="slideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextSlide()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">Next &#8594;</button>
                        </div>
                    </div>
                    <canvas id="statusChart" height="100"></canvas>
                </div>

                
                <div class="flex items-center gap-3 pt-2">
                    <div class="flex-1 h-px bg-gradient-to-r from-purple-200 to-transparent"></div>
                    <div class="flex items-center gap-2 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 px-4 py-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <span class="text-xs font-bold text-purple-700 uppercase tracking-widest">Predictive Analytics</span>
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-l from-purple-200 to-transparent"></div>
                </div>

                <!-- Predictive Interval Toggle + Info -->
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 border border-purple-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-purple-900">Forecast Next Time Window</h3>
                            <p class="text-xs text-purple-600 mt-0.5">Proyeksi status prediktif berdasarkan model RF + LSTM per interval waktu</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-purple-500 uppercase">Interval:</span>
                            <button id="btnInterval1" onclick="setForecastInterval(1)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-purple-600 text-white transition-all">1 Menit</button>
                            <button id="btnInterval5" onclick="setForecastInterval(5)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-purple-200 text-purple-600 hover:bg-purple-50 transition-all">5 Menit</button>
                        </div>
                    </div>
                    <!-- Live forecast status pill -->
                    <div class="mt-3 flex flex-wrap gap-2" id="forecastStatusPills">
                        <div class="flex items-center gap-1.5 bg-white/70 rounded-full px-3 py-1 border border-purple-100">
                            <span class="text-[10px] font-bold text-purple-500">NEXT FORECAST:</span>
                            <span id="nextForecastStatus" class="text-[10px] font-black text-purple-800 font-mono">—</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/70 rounded-full px-3 py-1 border border-purple-100">
                            <span class="text-[10px] font-bold text-purple-500">RF CONF:</span>
                            <span id="nextRfConf" class="text-[10px] font-black text-indigo-700 font-mono">—</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/70 rounded-full px-3 py-1 border border-purple-100">
                            <span class="text-[10px] font-bold text-purple-500">LSTM CONF:</span>
                            <span id="nextLstmConf" class="text-[10px] font-black text-indigo-700 font-mono">—</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-purple-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-purple-500 font-bold">Forecast Status Timeline</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Keluaran Layer 1 — LSTM-Ensemble Forecasting · status prediksi next window vs status aktual</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="prevForecastSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8592;</button>
                            <span id="forecastSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextForecastSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="forecastStatusChart" height="110"></canvas>
                    <div class="flex flex-wrap gap-4 mt-3 text-[10px] font-semibold text-slate-500">
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-slate-300 opacity-60"></span> Aktual</span>
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-purple-500"></span> Forecast</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-purple-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-purple-500 font-bold">Model Confidence Score</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Layer 2 — RF Confidence &amp; LSTM Confidence per timestamp</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="prevConfSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8592;</button>
                            <span id="confSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextConfSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="confidenceChart" height="110"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-purple-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-purple-500 font-bold">Projected Moisture Trend</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Proyeksi moisture next <span id="projectionWindowLabel">1 menit</span> ke depan (moving average)</p>
                        </div>
                    </div>
                    <canvas id="forecastMoistureChart" height="110"></canvas>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase">Proyeksi selanjutnya:</span>
                        <span id="projectedMoistureVal" class="font-mono text-sm font-black text-purple-700">—</span>
                        <span class="text-[10px] text-slate-400">%</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-5">

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Auto Refresh</h3>
                        <button id="refreshToggleBtn" onclick="toggleAutoRefresh()" class="px-3 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">
                            ● Aktif
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">Data sensor diperbarui otomatis setiap <strong class="text-slate-600">10 detik</strong> dari endpoint <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded">/api/sensor-now</code>.</p>
                    <div class="mt-3 text-xs text-slate-400">
                        Terakhir refresh: <span id="lastRefreshTime" class="font-mono text-slate-600">—</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <!-- Logo BMKG Resmi via Wikimedia SVG -->
                            <img src="{{ asset('images/logo bmkg.png') }}" alt="BMKG Logo" class="w-7 h-9 object-contain shrink-0">
                            <div>
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 font-mono leading-none">BMKG WEATHER</h3>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Pacet, Mojokerto</p>
                            </div>
                        </div>
                        <span class="bg-sky-50 text-sky-600 text-[10px] font-bold px-2 py-0.5 rounded-md border border-sky-100 font-mono">
                            LIVE
                        </span>
                    </div>

                    <!-- Data Cuaca BMKG -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono pt-1">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Kondisi</p>
                                <p class="font-bold text-slate-800">Cerah</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Suhu</p>
                                <p class="font-bold text-slate-800">26°C</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Kelembapan</p>
                                <p class="font-bold text-slate-800">RH 80%</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Curah Hujan</p>
                                <p class="font-bold text-slate-800">0.1 mm/jam</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">LARAS AI Assistant</h3>
                            <p class="text-[10px] text-slate-400">Powered by Gemini AI</p>
                        </div>
                    </div>
                    <div id="chatLog" class="h-52 overflow-y-auto p-4 space-y-3 text-xs">
                        <div class="flex gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-[9px] font-bold text-indigo-600">AI</span>
                            </div>
                            <div class="bg-slate-50 rounded-xl px-3 py-2 text-slate-600 max-w-[85%]">
                                LARAS AI Assistant siap. Kirim kueri kondisi sensor atau status longsor.
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 p-3 flex gap-2">
                        <input id="chatInput" type="text" placeholder="Tanya kondisi sensor..." class="flex-1 text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 text-slate-700">
                        <button onclick="sendChat()" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Riwayat Log ({{ count($logs) }} entri)</h3>
                    </div>
                    <div class="overflow-x-auto max-h-72 overflow-y-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="text-left px-4 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Waktu</th>
                                    <th class="text-right px-3 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Moist%</th>
                                    <th class="text-right px-3 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Vibr</th>
                                    <th class="text-center px-3 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($logs->sortByDesc('timestamp')->take(50) as $log)
                                @php
                                    $ls = strtoupper($log->status_ai ?? 'UNKNOWN');
                                    $lsBg = $ls === 'BAHAYA' ? 'bg-rose-100 text-rose-700' : ($ls === 'WASPADA' ? 'bg-yellow-100 text-yellow-700' : 'bg-emerald-100 text-emerald-700');
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-2.5 font-mono text-slate-500 text-[10px]">
                                        {{ \Carbon\Carbon::parse($log->timestamp)->format('d M H:i') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-mono text-slate-700">{{ number_format($log->soil_moisture ?? 0, 1) }}</td>
                                    <td class="px-3 py-2.5 text-right font-mono text-slate-700">{{ number_format($log->vibration ?? 0, 3) }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $lsBg }}">{{ $ls }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data log.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        

            </div>
        </div>
    </main>
</div>

<script>
Chart.defaults.font.family = 'Inter, ui-sans-serif, sans-serif';
Chart.defaults.color = '#64748b';

const PAGE = 10;

function enterDashboard() {
    const lp = document.getElementById('landing-page');
    const dp = document.getElementById('dashboard-page');
    lp.style.opacity = '0';
    lp.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        lp.style.display = 'none';
        dp.style.display = 'block';
        dp.style.opacity = '0';
        dp.style.transform = 'translateY(10px)';
        setTimeout(() => {
            dp.style.opacity = '1';
            dp.style.transform = 'translateY(0)';
            initCharts();
            startAutoRefresh();
            startBmkgRefresh();
        }, 50);
    }, 350);
}

function backToLanding() {
    const lp = document.getElementById('landing-page');
    const dp = document.getElementById('dashboard-page');
    dp.style.opacity = '0';
    dp.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        dp.style.display = 'none';
        lp.style.display = 'block';
        lp.style.opacity = '0';
        lp.style.transform = 'translateY(10px)';
        setTimeout(() => {
            lp.style.opacity = '1';
            lp.style.transform = 'translateY(0)';
        }, 50);
    }, 350);
}

function goToLandingSection(sectionId) {
    backToLanding();
    setTimeout(() => {
        const el = document.getElementById(sectionId);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 450);
}

function fmtLabel(ts) {
    if (!ts) return 'N/A';
    const d = new Date(ts);
    if (isNaN(d.getTime())) return ts;
    const day = String(d.getDate()).padStart(2, '0');
    const mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()];
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return day + ' ' + mon + ' ' + hh + ':' + mm;
}

let moistSlide = 0, moistDeepSlide = 0, accelSlide = 0, currentSlide = 0;
let moistureChart = null, moistureDeepChart = null, accelChart = null, statusChart = null;
let allStatusData = [];

function statusToNumber(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN') return 1;
    if (su === 'WASPADA') return 2;
    if (su === 'BAHAYA') return 3;
    return 0;
}

function getStatusColor(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN') return { bg: 'rgba(34,197,94,0.25)', border: '#22c55e' };
    if (su === 'WASPADA') return { bg: 'rgba(234,179,8,0.25)', border: '#eab308' };
    if (su === 'BAHAYA') return { bg: 'rgba(244,63,94,0.25)', border: '#f43f5e' };
    return { bg: 'rgba(148,163,184,0.2)', border: '#94a3b8' };
}

function renderMoistureSlide() {
    const total = Math.max(1, Math.ceil(phpLogs.length / PAGE));
    const slice = phpLogs.slice(moistSlide * PAGE, (moistSlide + 1) * PAGE);
    document.getElementById('moistSlideInfo').textContent = 'Slide ' + (moistSlide + 1) + ' / ' + total;
    if (moistureChart) moistureChart.destroy();
    moistureChart = new Chart(document.getElementById('moistureChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [{
                label: 'Moisture %',
                data: slice.map(d => d.soil_moisture),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.08)',
                fill: true, tension: 0.4, pointRadius: 3, borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100, grid: { color: '#f1f5f9' } }, x: { grid: { color: '#f1f5f9' } } }
        }
    });
}

function prevMoist() { if (moistSlide > 0) { moistSlide--; renderMoistureSlide(); } }
function nextMoist() { if (moistSlide < Math.ceil(phpLogs.length / PAGE) - 1) { moistSlide++; renderMoistureSlide(); } }
function renderMoistureDeepSlide() {
    const total = Math.max(1, Math.ceil(phpLogs.length / PAGE));
    const slice = phpLogs.slice(moistDeepSlide * PAGE, (moistDeepSlide + 1) * PAGE);
    document.getElementById('moistDeepSlideInfo').textContent = 'Slide ' + (moistDeepSlide + 1) + ' / ' + total;
    if (moistureDeepChart) moistureDeepChart.destroy();
    moistureDeepChart = new Chart(document.getElementById('moistureDeepChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [{
                label: 'Moisture Dalam %',
                data: slice.map(d => d.moisture_percent_deep),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                fill: true, tension: 0.4, pointRadius: 3, borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100, grid: { color: '#f1f5f9' } }, x: { grid: { color: '#f1f5f9' } } }
        }
    });
}

function prevMoistDeep() { if (moistDeepSlide > 0) { moistDeepSlide--; renderMoistureDeepSlide(); } }
function nextMoistDeep() { if (moistDeepSlide < Math.ceil(phpLogs.length / PAGE) - 1) { moistDeepSlide++; renderMoistureDeepSlide(); } }

function renderAccelSlide() {
    const total = Math.max(1, Math.ceil(phpLogs.length / PAGE));
    const slice = phpLogs.slice(accelSlide * PAGE, (accelSlide + 1) * PAGE);
    document.getElementById('accelSlideInfo').textContent = 'Slide ' + (accelSlide + 1) + ' / ' + total;
    if (accelChart) accelChart.destroy();
    accelChart = new Chart(document.getElementById('accelChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [
                { label: 'X', data: slice.map(d => d.accel_x), borderColor: '#ef4444', tension: 0.4, pointRadius: 2, borderWidth: 1.5 },
                { label: 'Y', data: slice.map(d => d.accel_y), borderColor: '#f59e0b', tension: 0.4, pointRadius: 2, borderWidth: 1.5 },
                { label: 'Z', data: slice.map(d => d.accel_z), borderColor: '#10b981', tension: 0.4, pointRadius: 2, borderWidth: 1.5 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: { x: { grid: { color: '#f1f5f9' } }, y: { grid: { color: '#f1f5f9' } } }
        }
    });
}

function prevAccel() { if (accelSlide > 0) { accelSlide--; renderAccelSlide(); } }
function nextAccel() { if (accelSlide < Math.ceil(phpLogs.length / PAGE) - 1) { accelSlide++; renderAccelSlide(); } }

function renderSlide() {
    const total = Math.max(1, Math.ceil(allStatusData.length / PAGE));
    const slice = allStatusData.slice(currentSlide * PAGE, (currentSlide + 1) * PAGE);
    document.getElementById('slideInfo').textContent = 'Slide ' + (currentSlide + 1) + ' / ' + total;
    if (statusChart) statusChart.destroy();
    statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [{
                label: 'Status Longsor',
                data: slice.map(d => statusToNumber(d.status_ai)),
                backgroundColor: slice.map(d => getStatusColor(d.status_ai).bg),
                borderColor: slice.map(d => getStatusColor(d.status).border),
                borderWidth: 1.5, borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    min: 0, max: 3.5, grid: { color: '#f1f5f9' },
                    ticks: { stepSize: 1, callback: v => { if(v==1) return 'AMAN'; if(v==2) return 'WASPADA'; if(v==3) return 'BAHAYA'; return ''; } }
                },
                x: { grid: { color: '#f1f5f9' } }
            }
        }
    });
}

function prevSlide() { if (currentSlide > 0) { currentSlide--; renderSlide(); } }
function nextSlide() { if (currentSlide < Math.ceil(allStatusData.length / PAGE) - 1) { currentSlide++; renderSlide(); } }

function initCharts() {
    moistSlide = Math.max(0, Math.ceil(phpLogs.length / PAGE) - 1);
    moistDeepSlide = Math.max(0, Math.ceil(phpLogs.length / PAGE) - 1);
    accelSlide = moistSlide;
    renderMoistureSlide();
    renderMoistureDeepSlide();
    renderAccelSlide();

    fetch('/api/status-history')
        .then(r => r.json())
        .then(data => {
            allStatusData = data;
            currentSlide = Math.max(0, Math.ceil(data.length / PAGE) - 1);
            renderSlide();
        })
        .catch(() => {
            allStatusData = phpLogs.map(l => ({ timestamp: l.timestamp, status: l.status_ai }));
            currentSlide = Math.max(0, Math.ceil(allStatusData.length / PAGE) - 1);
            renderSlide();
        });
    loadForecastData();
}

let autoRefreshEnabled = true;
let autoRefreshInterval = null;

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const btn = document.getElementById('refreshToggleBtn');
    if (autoRefreshEnabled) {
        btn.textContent = '● Aktif';
        btn.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors';
        startAutoRefresh();
    } else {
        btn.textContent = '○ Nonaktif';
        btn.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors';
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    }
}

function updateDOMWithLatest(d) {
    if (!d) return;
    const status = (d.status_ai || '').toString().toUpperCase();

    const card = document.getElementById('statusCard');
    const txt = document.getElementById('statusText');
    card.className = 'transition-all duration-300 rounded-2xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between border-l-4 gap-4 ';
    if (status === 'BAHAYA') {
        card.className += 'bg-rose-50 border-rose-500';
        txt.className = 'text-3xl font-extrabold tracking-tight text-rose-700';
        txt.textContent = '⚠ BAHAYA : EVAKUASI SEGERA';
    } else if (status === 'WASPADA') {
        card.className += 'bg-yellow-50 border-yellow-500';
        txt.className = 'text-3xl font-extrabold tracking-tight text-yellow-700';
        txt.textContent = '⚡ WASPADA : SIAGA PENUH';
    } else {
        card.className += 'bg-white border-emerald-500';
        txt.className = 'text-3xl font-extrabold tracking-tight text-emerald-700';
        txt.textContent = '✓ SISTEM AMAN';
    }

    const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
    setEl('valMoisture', parseFloat(d.soil_moisture || 0).toFixed(1));
    setEl('valMoistureDeep', parseFloat(d.moisture_percent_deep || 0).toFixed(1));
    setEl('valRainfall', parseFloat(d.rainfall_mm || 0).toFixed(1));
    setEl('valPitch', parseFloat(d.pitch || 0).toFixed(2));
    setEl('valRoll', parseFloat(d.roll || 0).toFixed(2));
    setEl('valVibration', parseFloat(d.vibration || 0).toFixed(3));
    setEl('lastUpdate', d.timestamp || '—');

    const moistureBar = document.getElementById('valMoistureBar');
    if (moistureBar) {
        const mp = Math.min(100, Math.max(0, parseFloat(d.soil_moisture || 0)));
        moistureBar.style.width = mp + '%';
        moistureBar.className = 'h-1.5 rounded-full transition-all duration-300 ' + (mp > 75 ? 'bg-rose-500' : (mp > 55 ? 'bg-yellow-500' : 'bg-blue-500'));
    }
    const moistureDeepBar = document.getElementById('valMoistureDeepBar');
    if (moistureDeepBar) {
        const mdp = Math.min(100, Math.max(0, parseFloat(d.moisture_percent_deep || 0)));
        moistureDeepBar.style.width = mdp + '%';
        moistureDeepBar.className = 'h-1.5 rounded-full transition-all duration-300 ' + (mdp > 75 ? 'bg-rose-500' : (mdp > 55 ? 'bg-yellow-500' : 'bg-emerald-500'));
    }
    const rainCat = document.getElementById('valRainfallCategory');
    if (rainCat) {
        const r = parseFloat(d.rainfall_mm || 0);
        let cat = 'Tidak Ada Hujan', color = 'text-slate-400';
        if (r > 50) { cat = 'Sangat Lebat'; color = 'text-rose-600'; }
        else if (r > 20) { cat = 'Lebat'; color = 'text-orange-600'; }
        else if (r > 5) { cat = 'Sedang'; color = 'text-amber-600'; }
        else if (r > 0) { cat = 'Ringan'; color = 'text-sky-600'; }
        rainCat.textContent = cat;
        rainCat.className = 'text-sm font-bold ' + color;
    }

    const now = new Date();
    const timeStr = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0') + ':' + now.getSeconds().toString().padStart(2,'0');
    setEl('lastRefreshTime', timeStr);
}

function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => {
        if (!autoRefreshEnabled) return;
        fetch('/api/sensor-now')
            .then(r => r.json())
            .then(d => updateDOMWithLatest(d))
            .catch(() => {});
    }, 10000);
}

let bmkgRefreshInterval = null;

function updateBmkgDisplay(w) {
    const el = document.getElementById('bmkg-display');
    if (!el) return;
    let text = '☁ ' + (w.condition || 'N/A');
    if (w.temp)     text += ' · ' + w.temp + '°C';
    if (w.humidity) text += ' · RH ' + w.humidity + '%';
    if (w.rainfall && parseFloat(w.rainfall) > 0) text += ' · ' + w.rainfall + ' mm/jam';
    el.textContent = text;
}

function fetchBmkg() {
    fetch('/api/weather-bmkg')
        .then(r => r.json())
        .then(w => updateBmkgDisplay(w))
        .catch(() => {});
}

function startBmkgRefresh() {
    if (bmkgRefreshInterval) clearInterval(bmkgRefreshInterval);
    fetchBmkg(); // langsung fetch pertama kali
    bmkgRefreshInterval = setInterval(fetchBmkg, 5 * 60 * 1000); // refresh tiap 5 menit
}

function appendChat (role, text) {
    const log = document.getElementById('chatLog');
    const div = document.createElement('div');
    div.className = 'flex gap-2 ' + (role === 'user' ? 'flex-row-reverse' : '');
    const avatar = document.createElement('div');
    avatar.className = 'w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-[9px] font-bold ' + (role === 'user' ? 'bg-blue-100 text-blue-600' : 'bg-indigo-100 text-indigo-600');
    avatar.textContent = role === 'user' ? 'OP' : 'AI';
    const bubble = document.createElement('div');
    bubble.className = 'rounded-xl px-3 py-2 text-slate-600 max-w-[85%] text-xs ' + (role === 'user' ? 'bg-blue-50 text-blue-800' : 'bg-slate-50');
    bubble.style.whiteSpace = 'pre-line';
    bubble.innerText = text;
    div.appendChild(avatar);
    div.appendChild(bubble);
    log.appendChild(div);
    log.scrollTop = log.scrollHeight;
}

function sendChat() {
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    appendChat('user', msg);

   fetch('/api/chatbot', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('typing-indicator')?.remove();
        appendChat('ai', d.reply || 'Tidak ada respons.');
    })
    .catch(() => {
        document.getElementById('typing-indicator')?.remove();
        appendChat('ai', 'Error: Koneksi ke AI engine gagal.');
    });
}

document.getElementById('chatInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') sendChat(); });


let forecastInterval = 1;
let allForecastData  = [];
let forecastSlide    = 0;
let confSlide        = 0;
let forecastStatusChart  = null;
let confidenceChart      = null;
let forecastMoistureChart = null;

function setForecastInterval(min) {
    forecastInterval = min;
    document.getElementById('btnInterval1').className = min === 1
        ? 'px-3 py-1.5 text-xs font-bold rounded-lg bg-purple-600 text-white transition-all'
        : 'px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-purple-200 text-purple-600 hover:bg-purple-50 transition-all';
    document.getElementById('btnInterval5').className = min === 5
        ? 'px-3 py-1.5 text-xs font-bold rounded-lg bg-purple-600 text-white transition-all'
        : 'px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-purple-200 text-purple-600 hover:bg-purple-50 transition-all';
    const lbl = document.getElementById('projectionWindowLabel');
    if (lbl) lbl.textContent = min + ' menit';
    renderForecastCharts();
}

function statusToNumForecast(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN')    return 1;
    if (su === 'WASPADA') return 2;
    if (su === 'BAHAYA')  return 3;
    return 0;
}

function forecastStatusColor(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN')    return { bg: 'rgba(124,58,237,0.5)',  border: '#7c3aed' };
    if (su === 'WASPADA') return { bg: 'rgba(167,139,250,0.5)', border: '#a78bfa' };
    if (su === 'BAHAYA')  return { bg: 'rgba(239,68,68,0.6)',   border: '#ef4444' };
    return { bg: 'rgba(148,163,184,0.3)', border: '#94a3b8' };
}

function sampleByInterval(data, intervalMin) {
    if (!data.length) return data;
    if (data.length < 2) return data;
    const t1 = new Date(data[0].timestamp).getTime();
    const t2 = new Date(data[1].timestamp).getTime();
    const diffMs = Math.abs(t2 - t1);
    const diffMin = diffMs / 60000;
    const step = Math.max(1, Math.round(intervalMin / (diffMin || 1)));
    return data.filter((_, i) => i % step === 0);
}

function generateProjectedTimestamps(lastTs, count, intervalMin) {
    const result = [];
    let base = new Date(lastTs).getTime();
    for (let i = 1; i <= count; i++) {
        base += intervalMin * 60 * 1000;
        const d = new Date(base);
        const day = String(d.getDate()).padStart(2,'0');
        const mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()];
        const hh  = String(d.getHours()).padStart(2,'0');
        const mm  = String(d.getMinutes()).padStart(2,'0');
        result.push(day + ' ' + mon + ' ' + hh + ':' + mm);
    }
    return result;
}

function projectMoisture(data, steps, intervalMin) {
    if (!data.length) return [];
    const sampled = sampleByInterval(data, intervalMin);
    const window  = Math.min(5, sampled.length);
    const last5   = sampled.slice(-window).map(d => parseFloat(d.moisture_percent || 0));
    const avg     = last5.reduce((a, b) => a + b, 0) / last5.length;
    const trend   = last5.length >= 2 ? (last5[last5.length-1] - last5[0]) / last5.length * 0.3 : 0;
    const result  = [];
    for (let i = 1; i <= steps; i++) {
        result.push(Math.min(100, Math.max(0, avg + trend * i)));
    }
    return result;
}

const FORECAST_PAGE = 10;

function renderForecastStatusChart(data) {
    const sampled = sampleByInterval(data, forecastInterval);
    const total   = Math.max(1, Math.ceil(sampled.length / FORECAST_PAGE));
    forecastSlide = Math.min(forecastSlide, total - 1);
    const slice   = sampled.slice(forecastSlide * FORECAST_PAGE, (forecastSlide + 1) * FORECAST_PAGE);
    document.getElementById('forecastSlideInfo').textContent = 'Slide ' + (forecastSlide + 1) + ' / ' + total;

    if (forecastStatusChart) forecastStatusChart.destroy();
    forecastStatusChart = new Chart(document.getElementById('forecastStatusChart'), {
        type: 'bar',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [
                {
                    label: 'Status Aktual',
                    data: slice.map(d => statusToNumForecast(d.status)),
                    backgroundColor: 'rgba(148,163,184,0.35)',
                    borderColor: '#94a3b8',
                    borderWidth: 1.5,
                    borderRadius: 3,
                    barPercentage: 0.9,
                    categoryPercentage: 0.55,
                },
                {
                    label: 'Forecast Status',
                    data: slice.map(d => statusToNumForecast(d.forecast_status)),
                    backgroundColor: slice.map(d => forecastStatusColor(d.forecast_status).bg),
                    borderColor:     slice.map(d => forecastStatusColor(d.forecast_status).border),
                    borderWidth: 1.5,
                    borderRadius: 3,
                    barPercentage: 0.9,
                    categoryPercentage: 0.55,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const v = ctx.raw;
                            const label = v === 1 ? 'AMAN' : v === 2 ? 'WASPADA' : v === 3 ? 'BAHAYA' : 'N/A';
                            return ctx.dataset.label + ': ' + label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0, max: 3.5, grid: { color: '#f3f0ff' },
                    ticks: { stepSize: 1, callback: v => { if(v==1) return 'AMAN'; if(v==2) return 'WASPADA'; if(v==3) return 'BAHAYA'; return ''; }, color: '#7c3aed' }
                },
                x: { grid: { color: '#f3f0ff' } }
            }
        }
    });
}

function renderConfidenceChart(data) {
    const sampled = sampleByInterval(data, forecastInterval);
    const total   = Math.max(1, Math.ceil(sampled.length / FORECAST_PAGE));
    confSlide     = Math.min(confSlide, total - 1);
    const slice   = sampled.slice(confSlide * FORECAST_PAGE, (confSlide + 1) * FORECAST_PAGE);
    document.getElementById('confSlideInfo').textContent = 'Slide ' + (confSlide + 1) + ' / ' + total;

    if (confidenceChart) confidenceChart.destroy();
    confidenceChart = new Chart(document.getElementById('confidenceChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [
                {
                    label: 'RF Confidence',
                    data: slice.map(d => d.rf_confidence != null ? parseFloat(d.rf_confidence) : null),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.08)',
                    fill: false, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    spanGaps: true
                },
                {
                    label: 'LSTM Confidence',
                    data: slice.map(d => d.lstm_confidence != null ? parseFloat(d.lstm_confidence) : null),
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6,182,212,0.08)',
                    fill: false, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    spanGaps: true
                },
                {
                    label: 'Forecast Confidence',
                    data: slice.map(d => d.forecast_confidence != null ? parseFloat(d.forecast_confidence) : null),
                    borderColor: '#a78bfa',
                    backgroundColor: 'rgba(167,139,250,0.08)',
                    fill: false, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    borderDash: [4, 3],
                    spanGaps: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + (ctx.raw != null ? ctx.raw.toFixed(1) + '%' : 'N/A')
                    }
                }
            },
            scales: {
                y: { min: 0, max: 100, grid: { color: '#f3f0ff' }, ticks: { callback: v => v + '%', color: '#7c3aed' } },
                x: { grid: { color: '#f3f0ff' } }
            }
        }
    });
}

function renderForecastMoistureChart(data) {
    const sampled     = sampleByInterval(data, forecastInterval);
    const histSlice   = sampled.slice(-FORECAST_PAGE); // last N historical points
    const projSteps   = 5;
    const projValues  = projectMoisture(data, projSteps, forecastInterval);
    const lastTs      = histSlice.length ? histSlice[histSlice.length - 1].timestamp : new Date().toISOString();
    const projLabels  = generateProjectedTimestamps(lastTs, projSteps, forecastInterval);

    const histLabels  = histSlice.map(d => fmtLabel(d.timestamp));
    const histValues  = histSlice.map(d => parseFloat(d.moisture_percent || 0));

    const allLabels   = [...histLabels, ...projLabels];
    const histDataset = [...histValues, ...new Array(projSteps).fill(null)];
    const projDataset = [...new Array(histLabels.length - 1).fill(null), histValues[histValues.length - 1] || null, ...projValues];

    if (projValues.length) {
        document.getElementById('projectedMoistureVal').textContent = projValues[0].toFixed(1);
    }

    if (forecastMoistureChart) forecastMoistureChart.destroy();
    forecastMoistureChart = new Chart(document.getElementById('forecastMoistureChart'), {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Moisture Aktual (%)',
                    data: histDataset,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    spanGaps: false
                },
                {
                    label: 'Proyeksi +' + forecastInterval + 'min',
                    data: projDataset,
                    borderColor: '#a78bfa',
                    backgroundColor: 'rgba(167,139,250,0.12)',
                    fill: true, tension: 0.4, pointRadius: 4, borderWidth: 2,
                    borderDash: [5, 4],
                    pointStyle: 'triangle',
                    spanGaps: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                annotation: {}
            },
            scales: {
                y: { min: 0, max: 100, grid: { color: '#f3f0ff' }, ticks: { callback: v => v + '%', color: '#7c3aed' } },
                x: { grid: { color: '#f3f0ff' } }
            }
        }
    });
}

function updateForecastPills(data) {
    if (!data.length) return;
    const latest = data[data.length - 1];
    const el = document.getElementById('nextForecastStatus');
    const rfEl = document.getElementById('nextRfConf');
    const lstmEl = document.getElementById('nextLstmConf');
    if (el) {
        el.textContent = latest.forecast_status || '—';
        const s = (latest.forecast_status || '').toUpperCase();
        el.className = 'text-[10px] font-black font-mono ' + (s === 'BAHAYA' ? 'text-rose-700' : s === 'WASPADA' ? 'text-yellow-700' : 'text-emerald-700');
    }
    if (rfEl)   rfEl.textContent   = latest.rf_confidence   != null ? parseFloat(latest.rf_confidence).toFixed(1)   + '%' : '—';
    if (lstmEl) lstmEl.textContent = latest.lstm_confidence != null ? parseFloat(latest.lstm_confidence).toFixed(1) + '%' : '—';
}

function renderForecastCharts() {
    if (!allForecastData.length) return;
    forecastSlide = Math.max(0, Math.ceil(sampleByInterval(allForecastData, forecastInterval).length / FORECAST_PAGE) - 1);
    confSlide     = forecastSlide;
    updateForecastPills(allForecastData);
    renderForecastStatusChart(allForecastData);
    renderConfidenceChart(allForecastData);
    renderForecastMoistureChart(allForecastData);
}

function prevForecastSlide() { if (forecastSlide > 0) { forecastSlide--; renderForecastStatusChart(allForecastData); } }
function nextForecastSlide() {
    const max = Math.ceil(sampleByInterval(allForecastData, forecastInterval).length / FORECAST_PAGE) - 1;
    if (forecastSlide < max) { forecastSlide++; renderForecastStatusChart(allForecastData); }
}
function prevConfSlide() { if (confSlide > 0) { confSlide--; renderConfidenceChart(allForecastData); } }
function nextConfSlide() {
    const max = Math.ceil(sampleByInterval(allForecastData, forecastInterval).length / FORECAST_PAGE) - 1;
    if (confSlide < max) { confSlide++; renderConfidenceChart(allForecastData); }
}

function loadForecastData() {
    fetch('/api/forecast-history')
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) throw new Error('empty');
            allForecastData = data.map(d => ({
                timestamp:           d.timestamp,
                status:              (d.status || 'AMAN').toString().toUpperCase(),
                forecast_status:     (d.forecast_status || d.status || 'AMAN').toString().toUpperCase(),
                forecast_confidence: d.forecast_confidence != null ? parseFloat(d.forecast_confidence) : null,
                rf_confidence:       d.rf_confidence       != null ? parseFloat(d.rf_confidence)       : null,
                lstm_confidence:     d.lstm_confidence     != null ? parseFloat(d.lstm_confidence)     : null,
                moisture_percent:    parseFloat(d.moisture_percent || 0),
                vibration:           parseFloat(d.vibration || 0),
                pitch:               parseFloat(d.pitch || 0),
            }));
            renderForecastCharts();
        })
        .catch(() => {
            allForecastData = phpLogs.map(l => ({
                timestamp:           l.timestamp,
                status:              (l.status || l.status_ai || 'AMAN').toString().toUpperCase(),
                forecast_status:     (l.forecast_status || l.status || l.status_ai || 'AMAN').toString().toUpperCase(),
                forecast_confidence: l.forecast_confidence != null ? parseFloat(l.forecast_confidence) : null,
                rf_confidence:       l.rf_confidence       != null ? parseFloat(l.rf_confidence)       : null,
                lstm_confidence:     l.lstm_confidence     != null ? parseFloat(l.lstm_confidence)     : null,
                moisture_percent:    parseFloat(l.moisture_percent || l.soil_moisture || 0),
                vibration:           parseFloat(l.vibration || 0),
                pitch:               parseFloat(l.pitch || 0),
            }));
            renderForecastCharts();
        });
}


</script>

<meta name="csrf-token" content="{{ csrf_token() }}">

</body>
</html>