# Deploy Web LARAS ke Vercel

Laravel dijalankan di Vercel memakai runtime komunitas **vercel-php@0.8.0 (PHP 8.4)** sebagai
serverless function. Database tetap Supabase. Notifikasi WhatsApp **tidak** dikirim dari web,
karena sudah ditangani workflow n8n (`LARAS_WA_LARAVEL=false`).

## File khusus Vercel

| File | Fungsi |
|---|---|
| `api/index.php` | Pintu masuk Laravel untuk Vercel |
| `vercel.json` | Runtime PHP, region Seoul (`icn1`, dekat Supabase), rute file statis, dan pengaturan non-rahasia |
| `.vercelignore` | File yang tidak di-upload (`vendor`, `.env`, log, tes, `*.patch`, `*.bat`) |

Perubahan kode pendukung:
- `bootstrap/app.php`: `trustProxies` supaya Laravel membuat URL `https://` di belakang proxy Vercel.
- `config/services.php` + `WhatsAppAlertService::evaluate()`: saklar `LARAS_WA_LARAVEL`.

## Environment Variables yang diisi di Vercel

Pengaturan non-rahasia (cache ke `/tmp`, log ke `stderr`, session & cache di database, WA web mati)
sudah ada di `vercel.json`. Yang **wajib** diisi di *Project Settings → Environment Variables*:

| Nama | Isi |
|---|---|
| `APP_NAME` | `LARAS` |
| `APP_KEY` | hasil `php artisan key:generate --show` (diawali `base64:`) |
| `APP_URL` | alamat Vercel, mis. `https://weblaras.vercel.app` (isi setelah deploy pertama, lalu redeploy) |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | host pooler Supabase, mis. `aws-0-ap-northeast-2.pooler.supabase.com` |
| `DB_PORT` | `6543` (sama dengan `.env` lokal) |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.<kode-project>` |
| `DB_PASSWORD` | password database Supabase |
| `DB_SSLMODE` | `require` |
| `GEMINI_API_KEY` | API key Gemini (chatbot) |

Opsional: `LARAS_DESA`, `LARAS_KECAMATAN`, `LARAS_KABUPATEN`, `N8N_TOKEN`.
**Jangan** isi `GOWA_*` dan `WA_*` di Vercel.

## Masalah yang mungkin muncul

| Gejala | Penyebab / solusi |
|---|---|
| `500` dan log `No application encryption key` | `APP_KEY` belum diisi |
| Form login error 419 / link `http://` | `APP_URL` belum diisi alamat `https://…vercel.app`, lalu redeploy |
| `could not translate host name` / timeout DB | `DB_HOST` salah; pakai host **pooler**, bukan `db.xxx.supabase.co` |
| `prepared statement "…" already exists` | Ganti `DB_PORT` ke `5432` (session pooler), lalu redeploy |
| Gambar tidak muncul | Gambar harus ada di `public/images/` dan sudah di-commit |

Log error: Vercel → project → **Logs** (atau *Deployments → … → Runtime Logs*).
