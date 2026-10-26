{{--
    Navbar bersama untuk semua halaman (Beranda/Live Monitoring, Data Lanskap, Peta Evakuasi, Admin).
    Header  : logo di kiri; di kanan tombol Darurat 112, Masuk Petugas / ikon profil, lalu tombol
              garis tiga paling kanan -> membuka menu samping dari sisi kanan (drawer).
              Menu halaman hanya ada di drawer (tidak diulang di header).
    Pemakaian:  @include('partials.navbar', ['active' => 'lanskap'])
    active : 'home' | 'live' | 'lanskap' | 'peta' | 'petugas'
--}}
@php
    $active = $active ?? null;
    $top    = 'px-2.5 xl:px-3 py-2 rounded-lg text-[14px] font-medium transition whitespace-nowrap';
    $topIdle= 'text-deep-700 hover:text-teal-600 hover:bg-air-100';
    $topOn  = 'text-teal-700 bg-air-100 font-semibold';
    $item   = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition';
    $idle   = 'text-deep-700 hover:text-teal-700 hover:bg-air-100';
    $on     = 'text-teal-700 bg-air-100 border-l-4 border-teal-500';

    // Jumlah peringatan yang menunggu verifikasi kader (hanya untuk petugas)
    $pendingVerif = 0;
    if (auth()->check() && auth()->user()->isStaff()) {
        try { $pendingVerif = \App\Models\AlertVerification::where('state', 'pending')->count(); } catch (\Throwable $e) {}
    }

@endphp

<style>section[id] { scroll-margin-top: 84px; }</style>

<!-- HEADER -->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-air-300/70">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-[68px] flex items-center gap-4">

        <!-- Logo -->
        <a href="/" class="flex items-center gap-3 shrink-0" data-nav="home">
            <span class="hidden sm:flex w-10 h-10 rounded-xl bg-air-100 border border-air-300 items-center justify-center overflow-hidden">
                <img src="/images/logo_laras.png" alt="Logo LARAS" class="w-8 h-8 object-contain">
            </span>
            <span class="leading-tight">
                <span class="block text-[15px] sm:text-[17px] font-bold tracking-tight text-deep-900 font-heading">LARAS Control Center</span>
                <span class="hidden sm:block xl:hidden 2xl:block text-[11px] text-deep-500">Peringatan Dini &amp; Evakuasi Longsor</span>
                <span class="hidden xl:block 2xl:hidden text-[11px] text-deep-500">Sistem Peringatan Dini Longsor</span>
            </span>
        </a>

        <!-- Kanan -->
        <div class="ml-auto flex items-center gap-2 sm:gap-3">
            <a href="tel:112" class="hidden md:inline-flex items-center gap-1.5 px-2 py-2 text-[14px] font-semibold text-rose-600 hover:text-rose-700 whitespace-nowrap">
                <span class="material-symbols-outlined text-[20px]">emergency</span> Darurat 112
            </a>
             @if($pendingVerif > 0)
                <a href="{{ route('verifikasi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[13px] font-bold animate-pulse whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">notification_important</span>{{ $pendingVerif }} perlu verifikasi
                </a>
            @endif
            @auth
                <!-- Ikon profil: klik untuk melihat nama, peran, dan tombol keluar -->
                <div class="relative" id="profilWrap">
                    <button type="button" id="profilBtn" aria-haspopup="true" aria-expanded="false" aria-label="Profil {{ Auth::user()->name }}" title="{{ Auth::user()->name }}"
                            class="w-10 h-10 rounded-full bg-teal-500 hover:bg-teal-600 text-white inline-flex items-center justify-center shadow-sm ring-2 ring-white focus:outline-none focus-visible:ring-air-300 transition">
                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">person</span>
                    </button>
                    <div id="profilMenu" class="hidden absolute right-0 mt-2 w-60 bg-white border border-air-300 rounded-xl shadow-float p-2 z-50">
                        <div class="flex items-center gap-3 px-2 py-2">
                            <span class="w-10 h-10 shrink-0 rounded-full bg-air-100 text-teal-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">person</span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-deep-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] font-mono uppercase font-semibold text-teal-600">{{ Auth::user()->role }}</p>
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="border-t border-air-200 mt-1 pt-1">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-2 py-2 rounded-lg text-sm font-semibold text-rose-600 hover:bg-rose-50 transition">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">logout</span>Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 rounded-lg bg-teal-500 hover:bg-teal-600 text-white text-[14px] font-semibold font-heading shadow-sm transition whitespace-nowrap">Masuk Petugas</a>
            @endauth

            <!-- Tombol garis tiga di paling kanan: membuka menu samping dari kanan -->
            <button type="button" id="sideMenuOpen" aria-controls="sideMenu" aria-expanded="false" aria-label="Buka menu"
                    class="w-10 h-10 shrink-0 inline-flex items-center justify-center rounded-lg bg-white border border-air-300 text-teal-700 shadow-sm hover:bg-teal-500 hover:border-teal-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-air-300 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>
                </svg>
            </button>
        </div>
    </div>
</header>

<!-- LATAR GELAP (klik untuk menutup menu) -->
<div id="sideMenuOverlay" class="fixed inset-0 z-50 bg-deep-900/30 opacity-0 pointer-events-none transition-opacity duration-200"></div>

<!-- MENU SAMPING -->
<aside id="sideMenu" aria-label="Menu" aria-hidden="true"
       class="fixed inset-y-0 right-0 z-50 w-80 max-w-[88vw] bg-white border-l border-air-300 shadow-float flex flex-col translate-x-full transition-transform duration-200 ease-out">

    <div class="flex items-center justify-between px-5 h-[68px] border-b border-air-300/70">
        <a href="/" class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl bg-air-100 border border-air-300 flex items-center justify-center overflow-hidden">
                <img src="/images/logo_laras.png" alt="Logo LARAS" class="w-7 h-7 object-contain">
            </span>
            <span class="text-[16px] font-bold tracking-tight text-deep-900 font-heading">LARAS</span>
        </a>
        <button type="button" id="sideMenuClose" aria-label="Tutup menu"
                class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-deep-500 hover:bg-air-100 hover:text-deep-900 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    @auth
        <div class="mx-4 mt-4 p-3 rounded-xl bg-air-100 border border-air-300 flex items-center gap-3">
            <span class="w-10 h-10 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold font-heading">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-deep-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-[10px] font-mono uppercase font-semibold text-teal-600">{{ Auth::user()->role }}</p>
            </div>
        </div>
    @endauth

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <p class="px-3 pb-1 text-[10px] font-mono font-semibold uppercase tracking-widest text-deep-400">Menu</p>
        <a href="/" data-nav="home" class="{{ $item }} {{ $active === 'home' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">home</span>Beranda</a>
        <a href="/#live" data-nav="live" class="{{ $item }} {{ $active === 'live' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">monitoring</span>Live Monitoring</a>
        <a href="{{ route('peta-evakuasi.index') }}" class="{{ $item }} {{ $active === 'peta' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">map</span>Peta Evakuasi</a>
        <a href="{{ route('lanskap.index') }}" class="{{ $item }} {{ $active === 'lanskap' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">landscape</span>Data Lanskap</a>
        <a href="{{ route('data-realtime') }}" class="{{ $item }} {{ $active === 'realtime' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">sensors</span>Data Sensor Realtime</a>

        <p class="px-3 pt-4 pb-1 text-[10px] font-mono font-semibold uppercase tracking-widest text-deep-400">Tentang LARAS</p>
        <a href="/#edukasi" class="{{ $item }} {{ $idle }}"><span class="material-symbols-outlined text-[20px]">menu_book</span>Edukasi Longsor</a>
        <a href="/#tentang" class="{{ $item }} {{ $idle }}"><span class="material-symbols-outlined text-[20px]">neurology</span>Cara Kerja &amp; AI</a>
        <a href="/#peran" class="{{ $item }} {{ $idle }}"><span class="material-symbols-outlined text-[20px]">groups</span>Peran Pengguna</a>
        <a href="/#developers" class="{{ $item }} {{ $idle }}"><span class="material-symbols-outlined text-[20px]">badge</span>Tim Pengembang</a>

      @can('kelola-data')
            <p class="px-3 pt-4 pb-1 text-[10px] font-mono font-semibold uppercase tracking-widest text-deep-400">Kader</p>
            <a href="{{ route('verifikasi.index') }}" class="{{ $item }} {{ $active === 'verifikasi' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">verified_user</span>Verifikasi Peringatan
                @if($pendingVerif > 0)<span class="ml-auto px-2 py-0.5 rounded-full bg-rose-600 text-white text-[11px]">{{ $pendingVerif }}</span>@endif
            </a>
        @endcan
        @can('kelola-akun')
            <p class="px-3 pt-4 pb-1 text-[10px] font-mono font-semibold uppercase tracking-widest text-deep-400">Admin</p>
            <a href="{{ route('register') }}" class="{{ $item }} {{ $active === 'petugas' ? $on : $idle }}"><span class="material-symbols-outlined text-[20px]">person_add</span>Kelola Petugas</a>
        @endcan

        <p class="px-3 pt-4 pb-1 text-[10px] font-mono font-semibold uppercase tracking-widest text-deep-400">Darurat</p>
        <a href="tel:112" class="{{ $item }} text-rose-600 hover:bg-rose-50"><span class="material-symbols-outlined text-[20px]">call</span>Kontak Darurat (112)</a>
    </nav>

    <div class="border-t border-air-300/70 p-4">
        @auth
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 transition">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="block w-full text-center px-3 py-2.5 rounded-lg bg-teal-500 hover:bg-teal-600 text-white text-sm font-semibold transition">Masuk Petugas</a>
            <p class="text-[11px] text-deep-400 text-center mt-2">Warga tidak perlu login untuk melihat informasi.</p>
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
            menu.classList.remove('translate-x-full');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            menu.setAttribute('aria-hidden', 'false');
            openBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }
        function closeMenu() {
            menu.classList.add('translate-x-full');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            menu.setAttribute('aria-hidden', 'true');
            openBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
        openBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
        menu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));

        // Menu kecil di ikon profil
        const profilBtn = document.getElementById('profilBtn');
        const profilMenu = document.getElementById('profilMenu');
        if (profilBtn && profilMenu) {
            const tutupProfil = () => { profilMenu.classList.add('hidden'); profilBtn.setAttribute('aria-expanded', 'false'); };
            profilBtn.addEventListener('click', e => {
                e.stopPropagation();
                const buka = profilMenu.classList.toggle('hidden') === false;
                profilBtn.setAttribute('aria-expanded', buka ? 'true' : 'false');
            });
            document.addEventListener('click', e => { if (!document.getElementById('profilWrap').contains(e.target)) tutupProfil(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupProfil(); });
        }

        // Di Beranda, "Beranda" vs "Live Monitoring" aktif mengikuti #live di URL
        window.larasSetNav = function (key) {
            const on  = ['text-teal-700', 'bg-air-100'];
            document.querySelectorAll('[data-nav]').forEach(a => {
                if (a.tagName !== 'A' || a.querySelector('img')) return;
                const isOn = a.dataset.nav === key;
                on.forEach(c => a.classList.toggle(c, isOn));
                a.classList.toggle('font-semibold', isOn && !a.closest('aside'));
                a.classList.toggle('text-deep-700', !isOn);
            });
        };
    })();
</script>