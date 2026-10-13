{{--
    Navbar bersama untuk semua halaman (homepage/Live Control, Data Lanskap, Peta Evakuasi).
    Bentuk: bar atas kecil + menu samping kiri (drawer) yang dibuka dengan tombol titik tiga.
    Pemakaian:  @include('partials.navbar', ['active' => 'lanskap'])
    active : 'home' | 'lanskap' | 'peta' | 'petugas'
--}}
@php
    $active = $active ?? null;
    $item   = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition';
    $idle   = 'text-slate-600 hover:text-slate-900 hover:bg-slate-100';
    $on     = 'text-blue-700 bg-blue-50';
@endphp

<style>section[id] { scroll-margin-top: 88px; }</style>

<!-- BAR ATAS -->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3">

        <!-- Tombol titik tiga: buka menu samping -->
        <button type="button" id="sideMenuOpen" aria-controls="sideMenu" aria-expanded="false" aria-label="Buka menu"
                class="w-10 h-10 shrink-0 inline-flex items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
            </svg>
        </button>

        <!-- Logo -->
        <a href="/" class="flex items-center gap-2.5 shrink-0">
            <img src="/images/logo_laras.png" alt="Logo LARAS" class="w-9 h-9 rounded-lg object-cover">
            <span class="leading-none">
                <span class="block text-lg font-extrabold tracking-tight text-slate-900">LARAS</span>
                <span class="block text-[9px] uppercase tracking-[0.2em] text-slate-400 font-bold mt-1">Control Center</span>
            </span>
        </a>

        <!-- Kanan: Live Control + nama user -->
        <div class="ml-auto flex items-center gap-3">
            <a href="/#live" class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold shadow-sm transition">
                <span class="relative flex w-2 h-2">
                    <span class="absolute inline-flex w-full h-full rounded-full bg-white/70 animate-ping"></span>
                    <span class="relative inline-flex w-2 h-2 rounded-full bg-white"></span>
                </span>
                <span>Live<span class="hidden sm:inline"> Control</span></span>
            </a>
            @auth
                <div class="text-right hidden sm:block">
                    <p class="text-[13px] font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] font-mono uppercase font-semibold text-blue-600 leading-tight">{{ Auth::user()->role }}</p>
                </div>
            @endauth
        </div>
    </div>
</header>

<!-- LATAR GELAP (klik untuk menutup menu) -->
<div id="sideMenuOverlay" class="fixed inset-0 z-50 bg-slate-900/40 opacity-0 pointer-events-none transition-opacity duration-200"></div>

<!-- MENU SAMPING KIRI -->
<aside id="sideMenu" aria-label="Menu utama" aria-hidden="true"
       class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-white border-r border-slate-200 shadow-xl flex flex-col -translate-x-full transition-transform duration-200 ease-out">

    <!-- Kepala menu -->
    <div class="flex items-center justify-between px-4 py-4 border-b border-slate-100">
        <a href="/" class="flex items-center gap-2.5">
            <img src="/images/logo_laras.png" alt="Logo LARAS" class="w-8 h-8 rounded-lg object-cover">
            <span class="text-base font-extrabold tracking-tight text-slate-900">LARAS</span>
        </a>
        <button type="button" id="sideMenuClose" aria-label="Tutup menu"
                class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Daftar menu -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <p class="px-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Menu</p>

        <a href="/" class="{{ $item }} {{ $active === 'home' ? $on : $idle }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
            Beranda
        </a>
        <a href="/#live" class="{{ $item }} {{ $idle }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            Live Control Center
        </a>
        <a href="/#edukasi" class="{{ $item }} {{ $idle }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Edukasi
        </a>
        <a href="{{ route('lanskap.index') }}" class="{{ $item }} {{ $active === 'lanskap' ? $on : $idle }}" @if($active === 'lanskap') aria-current="page" @endif>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
            Data Lanskap
        </a>
        <a href="{{ route('peta-evakuasi.index') }}" class="{{ $item }} {{ $active === 'peta' ? $on : $idle }}" @if($active === 'peta') aria-current="page" @endif>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 6v16l7-4 8 4 7-4V2l-7 4-8-4-7 4z"/><path d="M8 2v16M16 6v16"/></svg>
            Peta Evakuasi
        </a>

        <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Tentang LARAS</p>
        <a href="/#features" class="{{ $item }} {{ $idle }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Fitur
        </a>
        <a href="/#algorithms" class="{{ $item }} {{ $idle }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6v6H9zM9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/></svg>
            Algoritma AI
        </a>
        <a href="/#developers" class="{{ $item }} {{ $idle }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Tim Developer
        </a>

        @can('kelola-akun')
            <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Admin</p>
            <a href="{{ route('register') }}" class="{{ $item }} {{ $active === 'petugas' ? $on : $idle }}" @if($active === 'petugas') aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                Tambah Petugas
            </a>
        @endcan

        <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Darurat</p>
        <a href="tel:112" class="{{ $item }} text-rose-600 hover:bg-rose-50">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Kontak Darurat (112)
        </a>
    </nav>

    <!-- Bagian bawah: status login -->
    <div class="border-t border-slate-100 p-4">
        @auth
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] font-mono uppercase font-semibold text-blue-600">{{ Auth::user()->role }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-2 rounded-lg text-[13px] font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 transition">Logout</button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}" class="block w-full text-center px-3 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold transition">Login Petugas</a>
        @endauth
    </div>
</aside>

<script>
    (function () {
        const menu    = document.getElementById('sideMenu');
        const overlay = document.getElementById('sideMenuOverlay');
        const openBtn = document.getElementById('sideMenuOpen');
        const closeBtn = document.getElementById('sideMenuClose');

        function openMenu() {
            menu.classList.remove('-translate-x-full');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            menu.setAttribute('aria-hidden', 'false');
            openBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeMenu() {
            menu.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            menu.setAttribute('aria-hidden', 'true');
            openBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        openBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
        // Tutup menu saat salah satu link diklik (berguna untuk link #anchor)
        menu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
    })();
</script>