<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @include('partials.theme')
  
 <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-air-50 text-deep-900 min-h-screen">

    @include('partials.navbar', ['active' => 'petugas'])

    <main class="flex flex-col items-center gap-6 px-4 py-10">

    <!-- PENDAFTAR MENUNGGU PERSETUJUAN -->
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900 tracking-tight">Menunggu Persetujuan</h2>
            <span class="text-[11px] font-bold font-mono px-2.5 py-1 rounded-full {{ $pending->isEmpty() ? 'bg-slate-100 text-slate-500' : 'bg-sun-100 text-sun-800' }}">{{ $pending->count() }} pendaftar</span>
        </div>

        @forelse($pending as $p)
            <div class="flex items-center justify-between gap-3 border border-slate-100 rounded-xl px-4 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $p->name }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ $p->email }}{{ $p->phone ? ' · ' . $p->phone : '' }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">Daftar {{ optional($p->created_at)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form method="POST" action="{{ route('petugas.approve', $p) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('petugas.reject', $p) }}" onsubmit="return confirm('Tolak dan hapus pendaftaran {{ $p->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition">Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400">Belum ada pendaftar baru.</p>
        @endforelse
    </div>

    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        
        <!-- Header -->
        <div class="text-center">
            <a href="/" class="inline-block mb-3">
                <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-12 h-12 rounded-xl object-cover shadow-sm mx-auto" onerror="this.src='https://via.placeholder.com/48'">
            </a>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Buat Akun Petugas</h1>
            <p class="text-xs text-slate-500 mt-1">Akun yang dibuat Admin di sini langsung aktif.</p>
            <p class="text-[11px] text-slate-400 mt-1">Login sebagai: <strong>{{ Auth::user()->name }}</strong> (admin)</p>
        </div>

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold p-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3 rounded-xl space-y-1">
            <p class="font-bold">Periksa kembali isian form:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form Register -->
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin1@laras.id"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="081234567890"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Peran (Role) <span class="text-rose-500">*</span></label>
                <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white font-medium">
                    <option value="kader" @selected(old('role', 'kader') === 'kader')>Kader</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Ulangi Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" required placeholder="Ketik ulang password"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl shadow-md transition">
                Buat Akun Petugas
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100">
            <a href="/#live" class="text-xs text-blue-600 font-bold hover:underline">← Kembali ke Live Control</a>
        </div>

    </div>

    </main>

</body>
</html>