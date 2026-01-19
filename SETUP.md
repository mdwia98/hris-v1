## 🚀 Panduan Cepat Setup HRIS System

### ⚡ Langkah 1: Import Database

1. Buka **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Klik **Import**
3. Pilih file: `database/hris_db.sql`
4. Klik **Go** untuk import

**Database dan tabel akan terbuat otomatis!**

### 🔧 Langkah 2: Konfigurasi

Edit file `config/database.php` jika diperlukan:

```php
define('DB_HOST', 'localhost');    // Host MySQL
define('DB_USER', 'root');         // Username MySQL
define('DB_PASS', '');             // Password MySQL
define('DB_NAME', 'hris_db');      // Nama database
```

### 🌐 Langkah 3: Akses Aplikasi

Buka browser dan kunjungi:
- **Main**: `http://localhost/bsdmv2`
- **Login**: `http://localhost/bsdmv2/login.php`

### 👤 Login Demo

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Employee Account:**
- Username: `karyawan1`
- Password: `admin123`

---

## 📋 Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 👥 Manajemen Karyawan | Tambah, edit, lihat data karyawan |
| 📅 Absensi | Input dan tracking absensi harian |
| 🗓️ Cuti | Pengajuan dan approval cuti |
| 💰 Gaji | Manajemen gaji dan slip gaji |
| 📊 Performa | Penilaian kinerja karyawan |
| 📈 Laporan | Generate laporan sistem |

---

## 🎨 Tema Warna

- **Hijau Utama**: #1e7e34
- **Kuning**: #ffc107
- **Hitam**: #1a1a1a

---

## 🔐 Data Demo yang Sudah Tersedia

### Karyawan
- **Budi Santoso** (NIP: 0001) - IT Programmer
- **Siti Nurhaliza** (NIP: 0002) - HR Manager

### Departemen
- IT
- HR
- Finance
- Marketing
- Operations

---

## ⚙️ Troubleshooting

**Masalah: Database Connection Error**
- Pastikan MySQL running
- Cek konfigurasi di `config/database.php`

**Masalah: Login gagal**
- Clear cookies browser
- Check session PHP enabled

**Masalah: File not found**
- Pastikan file di `c:\xampp\htdocs\bsdmv2`

---

## 📚 Dokumentasi Lengkap

Lihat file `README.md` untuk dokumentasi lengkap sistem.

Selamat menggunakan HRIS System! 🎉
