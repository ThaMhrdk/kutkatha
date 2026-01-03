# 📦 PANDUAN HOSTING LARAVEL KE SERVER

## Informasi Server
- **Host:** sisteminformasikotacerdas.id
- **URL Web:** https://kutkatha.sisteminformasikotacerdas.id/
- **SSH Username:** sistem18
- **SSH Port:** 45022
- **Database:** sistem18_kutkatha
- **phpMyAdmin:** https://pma.sisteminformasikotacerdas.id/

---

## 🔧 LANGKAH 1: Setup Tools

### A. Install FileZilla
1. Download dari: https://filezilla-project.org/download.php
2. Install seperti biasa

### B. Install PuTTY
1. Download dari: https://putty.org/index.html
2. Install seperti biasa

---

## 🔑 LANGKAH 2: Setup Koneksi SSH di PuTTY

1. Buka **PuTTYgen** (terinstall bersama PuTTY)
2. Klik **Load** → pilih file private key SSH yang diberikan dosen
3. Klik **Save private key** → simpan sebagai file `.ppk`
4. Buka **PuTTY**:
   - **Host Name:** sisteminformasikotacerdas.id
   - **Port:** 45022
   - **Connection → SSH → Auth → Credentials** → Browse ke file `.ppk`
   - **Session** → Save session dengan nama "KUTKATHA"
5. Klik **Open** untuk connect

---

## 📁 LANGKAH 3: Setup FileZilla (SFTP)

1. Buka FileZilla
2. **File → Site Manager → New Site**
3. Isi:
   - **Protocol:** SFTP - SSH File Transfer Protocol
   - **Host:** sisteminformasikotacerdas.id
   - **Port:** 45022
   - **Logon Type:** Key file
   - **User:** sistem18
   - **Key file:** Browse ke file `.ppk` yang sudah dibuat
4. Klik **Connect**

---

## 🚀 LANGKAH 4: Persiapan & Push ke GitHub

### A. Build Assets (Jika menggunakan Vite/Node.js)
Jalankan di komputer lokal sebelum push:
```bash
npm install
npm run build
```

### B. Push ke GitHub
```bash
# Pastikan sudah ada repository di GitHub

# Add semua perubahan
git add .

# Commit
git commit -m "Ready for production deployment"

# Push ke GitHub
git push origin main
```

**Catatan:** File `.env.production`, `public/build/`, `index.php.production`, dan `app.php.production` akan ikut ter-push karena sudah dikeluarkan dari `.gitignore`

---

## 📤 LANGKAH 5: Clone dari GitHub ke Server (via PuTTY)

### ⚠️ PENTING: Pisahkan folder public dan folder inti Laravel untuk keamanan!

### Struktur folder di server:
```
/home/sistem18/
├── kutkatha/                  ← Folder INTI Laravel (diluar public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/               ← akan di-generate via composer install
│   ├── .env                  ← copy dari .env.production
│   ├── artisan
│   └── composer.json
│
└── public_html/               ← Folder PUBLIC (hanya file publik)
    ├── index.php             ← GUNAKAN index.php.production
    ├── .htaccess
    ├── robots.txt
    ├── build/                ← hasil npm run build
    ├── images/
    └── storage/              ← symlink ke ../kutkatha/storage/app/public
```

### 🚀 METODE 1: Via GitHub (RECOMMENDED - Lebih Cepat!)

Connect ke server via PuTTY, lalu jalankan:

```bash
# 1. Masuk ke home directory
cd ~

# 2. Clone repository dari GitHub
git clone https://github.com/USERNAME/kutkatha.git kutkatha

# 3. Pindahkan ISI folder public ke public_html
cp -r kutkatha/public/* public_html/

# 4. Copy file production ke lokasi yang benar
cp kutkatha/public/index.php.production public_html/index.php
cp kutkatha/bootstrap/app.php.production kutkatha/bootstrap/app.php

# 5. Lanjut ke LANGKAH 6 untuk konfigurasi
```

---

### 📁 METODE 2: Via FileZilla (Manual Upload)

#### BAGIAN 1: Upload folder INTI ke `/home/sistem18/kutkatha/`
Upload semua KECUALI:
- ❌ Folder `public/` (akan diupload terpisah)
- ❌ Folder `vendor/` (akan di-install via composer)
- ❌ Folder `node_modules/`
- ❌ File `.env` (akan dibuat dari .env.production)

Yang diupload:
- ✅ Folder `app/`
- ✅ Folder `bootstrap/`
- ✅ Folder `config/`
- ✅ Folder `database/`
- ✅ Folder `resources/`
- ✅ Folder `routes/`
- ✅ Folder `storage/`
- ✅ File `artisan`
- ✅ File `composer.json`
- ✅ File `composer.lock`
- ✅ File `.env.production`

#### BAGIAN 2: Upload ISI folder `public/` ke `/home/sistem18/public_html/`
- ✅ File `index.php.production` → rename jadi `index.php` di server
- ✅ File `.htaccess`
- ✅ File `robots.txt`
- ✅ Folder `build/` (hasil npm run build)
- ✅ Folder `images/`
- ✅ File lainnya di public/

---

## ⚙️ LANGKAH 6: Konfigurasi Server via PuTTY

### Connect ke server dan jalankan perintah berikut:

```bash
# 1. Masuk ke folder INTI Laravel (bukan public_html!)
cd ~/kutkatha

# 2. Copy .env.production menjadi .env
cp .env.production .env

# 3. Install dependencies dengan Composer
composer install --optimize-autoloader --no-dev

# 4. Generate application key (jika belum ada)
php artisan key:generate

# 5. Set permission folder storage dan cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 6. Jalankan migrasi database
php artisan migrate --force

# 7. (Opsional) Jalankan seeder jika perlu
php artisan db:seed --force

# 8. Buat symbolic link storage ke public_html
# PENTING: Karena struktur terpisah, buat symlink manual
ln -s ~/kutkatha/storage/app/public ~/public_html/storage

# 9. Clear dan cache config untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Set permission ulang setelah cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## 🌐 LANGKAH 7: Verifikasi Struktur

Setelah selesai, struktur folder harus seperti ini:
```
/home/sistem18/
├── kutkatha/                    ← Folder inti (TIDAK bisa diakses publik!)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
│
└── public_html/                 ← Folder publik (diakses via URL)
    ├── index.php               ← Sudah dimodifikasi path-nya
    ├── .htaccess
    ├── build/
    ├── images/
    └── storage → (symlink)     ← Link ke ../kutkatha/storage/app/public
```

**Keuntungan struktur ini:**
- ✅ File sensitif (.env, config, dll) tidak bisa diakses via URL
- ✅ Lebih aman dari serangan hacker
- ✅ Best practice untuk production

---

## 📱 LANGKAH 8: Update Flutter untuk Production

File Flutter sudah diupdate! Pastikan menggunakan URL:
```dart
static const String baseUrl = 'https://kutkatha.sisteminformasikotacerdas.id/api';
```

### Build APK Release:
```bash
cd kutkatha_mobile
flutter build apk --release
```

APK akan ada di: `build/app/outputs/flutter-apk/app-release.apk`

---

## 🔍 LANGKAH 8: Testing

### Test API dari Browser:
1. Buka: https://kutkatha.sisteminformasikotacerdas.id/
2. Test endpoint: https://kutkatha.sisteminformasikotacerdas.id/api/psikologs

### Test dari Flutter:
1. Build dan install APK
2. Coba login/register
3. Cek koneksi API

---

## ❗ TROUBLESHOOTING

### Error 500 Internal Server Error
```bash
# Masuk ke folder kutkatha
cd ~/kutkatha

# Cek log error
tail -f storage/logs/laravel.log

# Pastikan permission benar
chmod -R 775 storage bootstrap/cache
```

### Error "Class not found" atau Autoload
```bash
cd ~/kutkatha
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error Database Connection
1. Cek credentials di `.env`
2. Pastikan database sudah dibuat di phpMyAdmin
3. Pastikan user database punya akses

### CORS Error di Flutter
Pastikan file `config/cors.php` mengizinkan semua origin:
```php
'allowed_origins' => ['*'],
```

### SSL/HTTPS tidak bekerja
Hubungi admin server untuk setup SSL certificate.

### Error "File not found" untuk assets/images
Pastikan symlink storage sudah dibuat dengan benar:
```bash
ls -la ~/public_html/storage
# Harus menunjuk ke ../kutkatha/storage/app/public
```

---

## 📊 DATABASE SETUP

1. Buka https://pma.sisteminformasikotacerdas.id/
2. Login dengan:
   - **Username:** sistem18_kutkatha
   - **Password:** qw^lrR)*@)M45LiA
3. Pilih database `sistem18_kutkatha`
4. Import file SQL jika ada, atau biarkan migration Laravel yang buat tabelnya

---

## 📝 FILE YANG PERLU DIRENAME DI SERVER

Setelah upload, rename file berikut di server:

| File Lokal | Rename di Server |
|------------|------------------|
| `public/index.php.production` | `public_html/index.php` |
| `bootstrap/app.php.production` | `kutkatha/bootstrap/app.php` |
| `.env.production` | `kutkatha/.env` |

---

## ✅ CHECKLIST DEPLOYMENT

- [ ] `npm run build` sudah dijalankan (jika pakai Vite/Node.js)
- [ ] Folder INTI Laravel sudah diupload ke `/home/sistem18/kutkatha/`
- [ ] ISI folder public sudah diupload ke `/home/sistem18/public_html/`
- [ ] `index.php.production` sudah direname jadi `index.php`
- [ ] `bootstrap/app.php.production` sudah direname jadi `app.php`
- [ ] `.env.production` sudah dicopy/rename jadi `.env`
- [ ] `composer install` berhasil dijalankan
- [ ] Database migration berhasil
- [ ] Permission storage & cache sudah benar (775)
- [ ] Symlink storage sudah dibuat
- [ ] Cache sudah di-generate
- [ ] Test akses web berhasil
- [ ] Test API dari Flutter berhasil
- [ ] Database migration berhasil
- [ ] Permission storage & cache sudah benar
- [ ] Storage link sudah dibuat
- [ ] Cache sudah di-generate
- [ ] Domain/subdomain sudah pointing ke folder public
- [ ] Flutter sudah diupdate ke URL production
- [ ] Test login dari aplikasi Flutter berhasil
