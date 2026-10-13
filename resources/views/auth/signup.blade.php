<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kader — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-between">

    <nav class="max-w-5xl w-full mx-auto px-6 py-4 flex justify-between items-center bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
        <a href="/" class="flex items-center space-x-3">
            <img src="/images/logo_laras.png" alt="LARAS Logo" class="w-10 h-10 rounded-xl object-cover shadow-md">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 leading-none">LARAS</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mt-0.5">Control Center</p>
            </div>
        </a>
        <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Kembali ke Login
        </a>
    </nav>

    <div class="max-w-md w-full mx-auto px-6 py-10 my-auto">

        <div class="mb-8 text-center">
            <span class="text-xs font-bold uppercase tracking-widest text-blue-600 font-mono">Pendaftaran Kader</span>
            <h2 class="text-3xl font-black text-slate-900 mt-2">Daftar Akun Kader</h2>
            <p class="text-slate-500 text-sm mt-1">Akun Anda akan aktif setelah disetujui oleh Admin LARAS.</p>
        </div>

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

        <form method="POST" action="{{ route('signup') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="081234567890"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input id="password" type="password" name="password" required placeholder="Minimal 6 karakter"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Ulangi Password <span class="text-rose-500">*</span></label>
                <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ketik ulang password"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <button type="submit" class="w-full mt-2 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition duration-200">
                Daftar
            </button>

            <p class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Masuk di sini</a>
            </p>
        </form>
    </div>

    <footer class="text-center py-6 text-xs text-slate-400 font-mono">
        LARAS Early Warning System &copy; 2026
    </footer>

</body>
</html>