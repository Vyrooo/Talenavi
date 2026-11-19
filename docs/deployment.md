# Panduan Deployment Production (Deployment Guide)

Dokumen ini memuat panduan lengkap penyiapan, konfigurasi, dan pemeliharaan aplikasi **Product Catalog & Order REST API** untuk lingkungan production (Railway, Render, Fly.io, VPS Ubuntu, dsb).

---

## 1. Persiapan Deployment (Checklist Production)

Sebelum melakukan deployment, pastikan konfigurasi keamanan berikut terpenuhi:
- [x] **APP_ENV=production**: Menonaktifkan fitur debugging lokal.
- [x] **APP_DEBUG=false**: Mencegah kebocoran stack trace atau kredensial internal ke publik.
- [x] **APP_KEY**: Dihasilkan secara acak menggunakan `php artisan key:generate` dan disimpan aman.
- [x] **HTTPS/SSL Wajib**: Semua komunikasi API harus melalui protokol TLS/SSL.
- [x] **Credential via Environment**: Kredensial database, port, dan secret token diatur melalui Environment Variables hosting provider, bukan di dalam source code repository.
- [x] **Izin Akses Direktori (Permissions)**: Folder `storage/` dan `bootstrap/cache/` harus memiliki izin tulis (`chmod -R 775 storage bootstrap/cache`).

---

## 2. Environment Variables Production

Berikut adalah variabel lingkungan standar yang perlu diatur pada panel Environment hosting Anda:

```dotenv
APP_NAME="Talenavi Store API"
APP_ENV=production
APP_KEY=base64:qpVlNQLIRtz4r+MKpYIn5LhXsW9JbF/XusJet5gzp/c=
APP_DEBUG=false
APP_URL=https://api.yourdomain.com
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=your-db-host.internal
DB_PORT=3306
DB_DATABASE=talenavi_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_super_secret_db_password

SANCTUM_EXPIRATION=1440

ADMIN_NAME="Production Administrator"
ADMIN_EMAIL="admin@yourdomain.com"
ADMIN_PASSWORD=YourStrongAdminPassword123!
```

---

## 3. Strategi Deployment Berdasarkan Platform

### A. Deployment via Platform PaaS (Railway / Render / Fly.io)

Platform PaaS modern mendukung deployment otomatis berbasis Dockerfile:
1. Hubungkan repository GitHub Anda ke project baru di Railway/Render/Fly.io.
2. Buat service database MySQL pada platform terkait dan salin kredensial koneksinya (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
3. Masukkan seluruh environment variable production pada menu **Variables / Environment**.
4. Tetapkan start command container atau build command:
   ```bash
   php artisan migrate --force && php-fpm
   ```
   *(Gunakan flag `--force` karena pada `APP_ENV=production`, Laravel melarang eksekusi migration tanpa konfirmasi manual).*
5. Konfigurasikan domain publik dengan SSL otomatis dari platform.

---

### B. Deployment via VPS (Ubuntu 22.04 / 24.04 LTS)

#### 1. Setup Docker & Docker Compose pada VPS
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose-plugin git
sudo systemctl enable --now docker
```

#### 2. Clone Repository & Konfigurasi
```bash
git clone https://github.com/<username>/Talenavi.git /var/www/talenavi
cd /var/www/talenavi
cp .env.example .env
nano .env # Sesuaikan konfigurasi production
```

#### 3. Build & Jalankan Container
```bash
docker compose up -d --build
```

#### 4. Eksekusi Migration & Seeder Production
```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

#### 5. Setup Reverse Proxy Nginx & SSL (Certbot)
Gunakan Nginx host sebagai reverse proxy ke port container (misal port 8000), lalu pasang SSL gratis via Let's Encrypt:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d api.yourdomain.com
```

---

## 4. Pemeriksaan Endpoint Health Check

Setelah aplikasi online, verifikasi status kesehatan backend dengan memanggil endpoint publik:

```bash
curl -i https://api.yourdomain.com/api/health
```

**Response yang Diharapkan (HTTP 200 OK):**
```json
{
  "success": true,
  "message": "Aplikasi berjalan normal.",
  "data": {
    "status": "healthy",
    "timestamp": "2026-11-20T00:00:00+00:00",
    "services": {
      "database": "ok"
    }
  },
  "meta": null
}
```

Endpoint ini aman dipasang pada *Uptime Monitor* (seperti UptimeRobot atau AWS Route53 Health Checks) karena tidak membocorkan data sensitif.

---

## 5. Prosedur Backup & Restore Database MySQL

### Backup Otomatis (Dump):
```bash
docker compose exec db mysqldump -u root -p<DB_PASSWORD> fico_store > backup_$(date +%F_%T).sql
```

### Restore Database dari File Backup:
```bash
docker compose exec -T db mysql -u root -p<DB_PASSWORD> fico_store < backup_file.sql
```

---

## 6. Penanganan Masalah Deployment (Troubleshooting)

| Gejala Masalah | Penyebab Umum | Solusi |
|---|---|---|
| **Error 500 saat pertama kali dibuka** | `APP_KEY` belum terisi atau permission `storage/` terkunci | Jalankan `php artisan key:generate` dan `chmod -R 775 storage bootstrap/cache`. |
| **Database Connection Refused** | Container aplikasi menyala sebelum MySQL siap atau host salah | Pastikan healthcheck `db` aktif dan gunakan host `db` (bukan `127.0.0.1`) di dalam jaringan Docker Compose. |
| **Migration dibatalkan (Aborted)** | Laravel mendeteksi `APP_ENV=production` | Sertakan flag `--force` saat menjalankan perintah: `php artisan migrate --force`. |
| **Katalog atau route tidak terupdate** | Cache konfigurasi/route lama masih aktif | Bersihkan cache dengan: `php artisan optimize:clear` lalu jalankan kembali `php artisan optimize`. |
