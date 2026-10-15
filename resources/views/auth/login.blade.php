<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-air-50 text-deep-900 min-h-screen flex flex-col justify-between">

    @include('partials.navbar', ['active' => null])

    <div class="max-w-md w-full mx-auto px-6 py-10 my-auto">

        <div class="mb-8 text-center">
            <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Autentikasi Sistem</span>
            <h2 class="text-3xl font-black text-slate-900 mt-2">Login Kader &amp; Admin</h2>
            <p class="text-slate-500 text-sm mt-1">Masuk untuk mengakses hak kelola data lapangan dan sistem kontrol.</p>
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

        <form method="POST" action="{{ route('login') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="nama@email.com"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="remember" value="1" class="accent-blue-600">
                Ingat saya
            </label>

            <button type="submit" class="w-full mt-2 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition duration-200">
                Masuk ke Sistem
            </button>

            <div class="text-center text-xs text-slate-500 pt-3 border-t border-slate-100 space-y-1.5">
                <p>Belum punya akun? <a href="{{ route('signup') }}" class="text-blue-600 font-bold hover:underline">Daftar sebagai kader</a></p>
                <p class="text-slate-400">Akun baru aktif setelah disetujui Admin. Warga tidak perlu login untuk melihat informasi.</p>
            </div>
        </form>

    </div>

    <footer class="text-center py-6 text-xs text-slate-400 font-mono">
        LARAS Early Warning System &copy; 2026
    </footer>

</body>
</html>