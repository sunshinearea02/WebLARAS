<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4 py-12">

    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        
        <!-- Header -->
        <div class="text-center">
            <a href="/" class="inline-block mb-3">
                <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-12 h-12 rounded-xl object-cover shadow-sm mx-auto" onerror="this.src='https://via.placeholder.com/48'">
            </a>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Pendaftaran Akun Petugas</h1>
            <p class="text-xs text-slate-500 mt-1">Daftarkan akun Admin atau Kader baru untuk sistem LARAS.</p>
        </div>

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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email / Username <span class="text-rose-500">*</span></label>
                <input type="text" name="email" value="{{ old('email') }}" required placeholder="admin1@laras.id"
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
                    <option value="kader">Kader</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl shadow-md transition">
                Buat Akun &amp; Masuk
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100">
            <p class="text-xs text-slate-500">Sudah punya akun? 
                <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Masuk di sini</a>
            </p>
        </div>

    </div>

</body>
</html>