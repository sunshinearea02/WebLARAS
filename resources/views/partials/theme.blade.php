{{--
    Tema warna LARAS. Dipasang TEPAT SETELAH <script src="https://cdn.tailwindcss.com"></script>
    di setiap halaman:  @include('partials.theme')

    Palet:
      Payne's Gray   #4E6A83  -> warna utama (menggantikan blue / indigo / sky / cyan)
      Ash Gray       #A9B5B4  -> garis halus, latar lembut, scrollbar
      Hunyadi Yellow #FAB44B  -> aksen sorotan & status WASPADA (menggantikan amber / yellow)
      Drab Dark Brown#494822  -> aksen sekunder (olive)
      Café Noir      #4C3625  -> aksen gelap hangat (menggantikan purple / violet / fuchsia)
    Warna status AMAN (emerald) dan BAHAYA (rose) sengaja tidak diubah agar tetap mudah dikenali.
--}}
<script>
    (function () {
        const payne = { 50:'#f3f6f8', 100:'#e4eaf0', 200:'#cad5df', 300:'#a5b7c7', 400:'#7a93a9', 500:'#5d7891', 600:'#4e6a83', 700:'#41576c', 800:'#384a5a', 900:'#313f4c', 950:'#202932' };
        const noir  = { 50:'#f8f4f1', 100:'#efe6de', 200:'#ddc9b9', 300:'#c5a68e', 400:'#a9846a', 500:'#8d6a51', 600:'#6f5340', 700:'#4c3625', 800:'#3f2d20', 900:'#33251b', 950:'#1d140e' };
        const sun   = { 50:'#fff9ed', 100:'#fef0d3', 200:'#fde0a6', 300:'#fccb73', 400:'#fbbd5a', 500:'#fab44b', 600:'#e3922a', 700:'#b8701c', 800:'#93571b', 900:'#784819', 950:'#43250a' };
        const olive = { 50:'#f7f7ee', 100:'#ececd6', 200:'#d9d8ad', 300:'#bfbd7b', 400:'#a3a153', 500:'#86843b', 600:'#69672d', 700:'#494822', 800:'#3e3d1f', 900:'#35341d', 950:'#1c1b0c' };
        const ash   = { 50:'#f6f7f7', 100:'#eceff0', 200:'#d9dfdf', 300:'#c1cbca', 400:'#a9b5b4', 500:'#8d9b9a', 600:'#73817f', 700:'#5e6968', 800:'#4f5857', 900:'#444b4a', 950:'#2c3131' };

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // aksen lama dipetakan ke palet baru
                        blue: payne, indigo: payne, sky: payne, cyan: payne,
                        purple: noir, violet: noir, fuchsia: noir,
                        amber: sun, yellow: sun,
                        // nama palet baru, untuk dipakai langsung: bg-brand-payne, text-sun-600, dst.
                        payne, noir, sun, olive, ash,
                        brand: { payne: '#4E6A83', ash: '#A9B5B4', sun: '#FAB44B', olive: '#494822', noir: '#4C3625' },
                    },
                },
            },
        };
    })();
</script>
<style>
    ::selection { background: #FAB44B; color: #4C3625; }
    :focus-visible { outline-color: #4E6A83; }
    ::-webkit-scrollbar-thumb { background: #A9B5B4; }
    ::-webkit-scrollbar-thumb:hover { background: #8d9b9a; }
</style>