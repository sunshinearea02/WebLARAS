{{--
    Tema LARAS (desain "Modern Precision Civic-Tech" dari Stitch).
    Dipasang TEPAT SETELAH <script src="https://cdn.tailwindcss.com"></script>
    di setiap halaman:  @include('partials.theme')

    Palet:
      Teal      #008080  -> aksi utama: tombol, link, menu aktif, garis grafik utama   (teal-*)
      Deep teal #133332  -> judul, footer, satu blok gelap per halaman                (deep-*)
      Soft blue #EAF5FB  -> panel section selang-seling, kotak ikon, info box           (air-100)
      Blue line #BFE3F2  -> garis tepi kartu, hover, garis grafik kedua                 (air-300)
      Cream     #FFEBAF  -> sorotan kecil: badge catatan, angka penting di blok gelap   (cream-*)
      Putih     #FFFFFF  -> latar halaman & kartu
    Warna status (JANGAN dipakai untuk hiasan):
      AMAN #10B981 · WASPADA #F59E0B · BAHAYA #E11D48 · BELUM ADA DATA #94A3B8
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0&display=swap" rel="stylesheet">
<script>
    (function () {
        const teal  = { 50:'#effaf9', 100:'#d5f1ef', 200:'#ade2df', 300:'#78cbc7', 400:'#36aaa5', 500:'#008080', 600:'#006e6e', 700:'#005a5a', 800:'#064a4a', 900:'#0a3d3d', 950:'#032626' };
        const deep  = { 50:'#f1f7f6', 100:'#ddebea', 200:'#bcd7d5', 300:'#8fbab7', 400:'#5e9895', 500:'#427b79', 600:'#346362', 700:'#2b5150', 800:'#1f403f', 900:'#133332', 950:'#0b2221' };
        const air   = { 50:'#f5fafd', 100:'#eaf5fb', 200:'#d6eef8', 300:'#bfe3f2', 400:'#8fcbe5', 500:'#5aaed3', 600:'#3b8fb6', 700:'#2f7394', 800:'#295e78', 900:'#264f64', 950:'#183342' };
        const cream = { 50:'#fffcf3', 100:'#fff7e0', 200:'#ffebaf', 300:'#ffde85', 400:'#fcca4f', 500:'#f2b22a', 600:'#d68f14', 700:'#b06d10', 800:'#8c5513', 900:'#734614', 950:'#422406' };

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal, deep, air, cream,
                        // nama dari tema-tema sebelumnya, supaya tidak ada kelas yang "hilang"
                        ember: teal, navy: deep, gold: cream, beige: air,
                        blue: teal, sage: teal, purple: teal, violet: teal, fuchsia: teal,
                        sky: air, cyan: air,
                        indigo: deep, payne: deep, noir: deep, olive: deep, ash: deep,
                        sun: cream, yellow: cream,
                        slate: { 900: '#133332', 950: '#0b2221' },
                        status: { aman: '#10B981', waspada: '#F59E0B', bahaya: '#E11D48', none: '#94A3B8' },
                    },
                    fontFamily: {
                        heading: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    borderRadius: { '2xl': '0.75rem', '3xl': '1rem' },
                    boxShadow: {
                        card: '0 2px 8px -2px rgba(19,51,50,.06)',
                        lift: '0 8px 24px -4px rgba(0,128,128,.12)',
                        float: '0 16px 36px -6px rgba(19,51,50,.16)',
                    },
                },
            },
        };
    })();
</script>
<style>
    body { background-color: #ffffff; color: #133332; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    h1, h2, h3, h4, .font-heading { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important; letter-spacing: -0.015em; }
    .font-mono, code { font-family: 'JetBrains Mono', ui-monospace, monospace !important; font-variant-numeric: tabular-nums; }
    ::selection { background: #BFE3F2; color: #133332; }
    :focus-visible { outline: 3px solid #BFE3F2; outline-offset: 2px; }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f5fafd; }
    ::-webkit-scrollbar-thumb { background: #bfe3f2; border-radius: 8px; }
    ::-webkit-scrollbar-thumb:hover { background: #008080; }

    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; vertical-align: middle; }

    /* Label kecil di atas judul bagian, contoh: "TELEMETRI REAL-TIME" */
    .section-tag {
        display: inline-flex; align-items: center; gap: .45rem;
        font-family: 'JetBrains Mono', monospace !important; font-size: .7rem; font-weight: 600;
        letter-spacing: .12em; text-transform: uppercase; color: #008080;
    }
    .section-tag::before { content: ''; width: 16px; height: 2px; background: #008080; border-radius: 2px; }
    .section-tag.on-dark { color: #FFEBAF; }
    .section-tag.on-dark::before { background: #FFEBAF; }
    .heading-accent::after { display: none; }

    /* Pil kecil berbingkai (kicker di hero) */
    .chip-outline { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .8rem; border-radius: 9999px;
        background: #eaf5fb; border: 1px solid #bfe3f2; color: #006e6e;
        font-family: 'JetBrains Mono', monospace !important; font-size: .68rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }

    /* Kartu standar */
    .card { background: #fff; border: 1px solid #bfe3f2; border-radius: .75rem; box-shadow: 0 2px 8px -2px rgba(19,51,50,.06); }
    .card-accent { position: relative; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .card-accent:hover { transform: translateY(-2px); border-color: #008080; box-shadow: 0 8px 24px -4px rgba(0,128,128,.12); }

    /* Catatan info: kotak biru muda + garis kiri teal */
    .info-note { background: #eaf5fb; border-left: 4px solid #008080; border-radius: .5rem; }

    /* Badge status */
    .badge { display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .6rem; border-radius: 9999px;
        font-family: 'JetBrains Mono', monospace !important; font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
    .badge::before { content: ''; width: 6px; height: 6px; border-radius: 9999px; background: currentColor; }
    .badge-aman    { background: #D1FAE5; color: #065F46; }
    .badge-waspada { background: #FEF3C7; color: #92400E; }
    .badge-bahaya  { background: #FFE4E6; color: #9F1239; }
    .badge-none    { background: #F1F5F9; color: #475569; }
    .badge-cream   { background: #FFEBAF; color: #78350F; }
    .badge-cream::before, .badge-plain::before { display: none; }

    /* Bagian lebar penuh */
    .full-bleed { width: 100vw; margin-left: calc(50% - 50vw); }
    .deep-grid, .navy-grid { background-color: #133332; }
    .air-gradient { background: linear-gradient(180deg, #f5fafd 0%, #eaf5fb 55%, #ffffff 100%); }
</style>