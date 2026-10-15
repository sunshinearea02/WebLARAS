<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kader — LARAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <style>
    </style>
</head>
<body class="bg-air-50 text-deep-900 min-h-screen flex flex-col justify-between">

    @include('partials.navbar', ['active' => null])

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