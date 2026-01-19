# HRIS System - Human Resource Information System

Sistem Manajemen Sumber Daya Manusia (HRIS) profesional yang dibangun menggunakan PHP, MySQL, dan Bootstrap 5. Dengan tema warna yang menarik (Hijau, Kuning, dan Hitam), sistem ini menyediakan solusi lengkap untuk mengelola data karyawan, absensi, cuti, gaji, dan performa.

## 🎯 Fitur Utama

### 1. **Manajemen Karyawan**
- Tambah, edit, dan hapus data karyawan
- Informasi lengkap: NIP, nama, email, telepon, alamat
- Klasifikasi berdasarkan departemen dan posisi
- Status kerja (Tetap, Kontrak, Magang)

### 2. **Sistem Absensi**
- Input absensi harian
- Tracking jam masuk dan jam keluar
- Status absensi (Hadir, Sakit, Izin, Cuti, Libur, Alfa)
- Export data absensi ke CSV

### 3. **Manajemen Cuti**
- Pengajuan cuti dengan berbagai jenis
- Sistem approval dari manager
- Tracking durasi cuti
- Status: Pending, Disetujui, Ditolak

### 4. **Manajemen Gaji**
- Perhitungan gaji pokok, tunjangan, bonus
- Potongan otomatis (pajak, asuransi)
- Generate slip gaji
- Slip gaji per bulan dan tahun

### 5. **Penilaian Performa**
- Evaluasi kinerja karyawan
- Penilaian perilaku
- Catatan penilaian dari manager
- Tracking performa per bulan

### 6. **Dashboard**
- Statistik karyawan, departemen
- Absensi hari ini
- Permintaan cuti yang menunggu
- Data karyawan terbaru

### 7. **Manajemen User**
- Sistem login dengan role-based access
- 3 tipe user: Admin, Manager, Karyawan
- Manajemen user dan permission
- Keamanan password dengan hashing

## 🎨 Tema Visual

- **Warna Utama:** Hijau (#1e7e34)
- **Warna Sekunder:** Kuning (#ffc107)
- **Warna Gelap:** Hitam (#1a1a1a)
- **Interface:** Modern dan responsif dengan Bootstrap 5
- **Icons:** Bootstrap Icons untuk visual yang profesional

## 📋 Struktur Database

### Tabel Utama:
- `users` - Data pengguna sistem
- `karyawan` - Data karyawan
- `departemen` - Data departemen
- `absensi` - Data absensi
- `cuti` - Data permintaan cuti
- `gaji` - Data gaji karyawan
- `performa` - Data penilaian performa

## 🚀 Instalasi dan Setup

### Prerequisites:
- XAMPP (atau server lokal lainnya)
- PHP 7.4+
- MySQL 5.7+
- Browser modern (Chrome, Firefox, Safari, Edge)

### Langkah Instalasi:

1. **Clone atau download project**
   ```bash
   cd C:\xampp\htdocs
   ```

2. **Setup Database**
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Import file `database/hris_db.sql`
   - Database dan tabel akan terbuat otomatis

3. **Konfigurasi Database**
   - Edit file `config/database.php`
   - Sesuaikan: `DB_HOST`, `DB_USER`, `DB_PASS`

4. **Jalankan Aplikasi**
   - Akses: `http://localhost/bsdmv2`
   - Atau: `http://localhost/bsdmv2/login.php`

## 👤 Data Login Demo

### Admin Account:
- **Username:** `admin`
- **Password:** `admin123`
- **Role:** Admin (akses penuh ke semua fitur)

### Employee Account:
- **Username:** `karyawan1`
- **Password:** `admin123`
- **Role:** Karyawan (akses terbatas)

### Manager Account:
- **Username:** `manajer1`
- **Password:** `admin123`
- **Role:** Manager (akses ke fitur approval)

## 📁 Struktur Folder

```
bsdmv2/
├── config/
│   └── database.php          # Konfigurasi database
├── assets/
│   ├── css/
│   │   └── style.css         # Stylesheet utama
│   ├── js/
│   │   └── main.js           # JavaScript utama
│   └── images/               # Folder gambar
├── database/
│   └── hris_db.sql          # File database SQL
├── includes/
│   ├── header.php           # Header template
│   └── footer.php           # Footer template
├── pages/
│   ├── karyawan/            # Modul karyawan
│   ├── absensi/             # Modul absensi
│   ├── cuti/                # Modul cuti
│   ├── gaji/                # Modul gaji
│   └── performa/            # Modul performa
├── login.php                # Halaman login
├── logout.php               # Proses logout
├── dashboard.php            # Halaman dashboard
└── README.md                # File dokumentasi ini
```

## 🔐 Keamanan

1. **Password Hashing:** Menggunakan bcrypt hashing
2. **Session Management:** Secure session handling
3. **Database Connection:** Prepared statements untuk prevent SQL injection
4. **Input Validation:** Server-side validation untuk semua input
5. **CSRF Protection:** Token-based protection untuk form submission

## 💡 Tips Penggunaan

### Untuk Admin:
- Kelola semua data karyawan, departemen, dan gaji
- Approve/reject permintaan cuti
- Generate laporan karyawan
- Kelola user dan permission

### Untuk Manager:
- Monitor absensi tim
- Approve permintaan cuti karyawan
- Penilaian performa karyawan
- Akses laporan tim

### Untuk Karyawan:
- Lihat profil pribadi
- Ajukan permintaan cuti
- Lihat absensi pribadi
- Download slip gaji

## 🛠️ Teknologi yang Digunakan

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework UI:** Bootstrap 5.3
- **Icons:** Bootstrap Icons
- **Server:** XAMPP/Apache

## 📊 Fitur Laporan

- Laporan karyawan per departemen
- Laporan absensi bulanan
- Laporan gaji
- Laporan performa karyawan
- Export ke CSV dan PDF

## 🔄 Update dan Maintenance

### Tips Maintenance:
1. Backup database secara berkala
2. Update dependencies keamanan
3. Monitor database size
4. Clear old session files
5. Update password default secara berkala

## 📞 Troubleshooting

### Masalah Database Connection:
- Pastikan MySQL server berjalan
- Periksa konfigurasi di `config/database.php`
- Pastikan database sudah diimport

### Masalah Login:
- Pastikan session PHP enabled
- Clear browser cookies
- Periksa permission folder

### Masalah File Upload:
- Pastikan folder temp writable
- Periksa upload_max_filesize di php.ini
- Pastikan folder uploads exist

## 📄 Lisensi

Project ini dibuat untuk keperluan internal. Silakan modifikasi sesuai kebutuhan.

## 👨‍💻 Developer

Dikembangkan dengan PHP, MySQL, dan Bootstrap 5 untuk solusi HRIS yang profesional dan mudah digunakan.

## 📝 Catatan Penting

- Sistem ini dirancang untuk enterprise/perusahaan skala menengah
- Backup database secara berkala sangat penting
- Gunakan HTTPS pada production environment
- Implement 2FA untuk keamanan tambahan (opsional)
- Monitor access logs untuk audit trail

## 🎓 Fitur yang Dapat Ditambahkan

- 🔔 Sistem notifikasi real-time
- 📱 Mobile app
- 📊 Dashboard analytics yang lebih detail
- 🔐 Two-factor authentication
- 📧 Email notification
- 💬 Sistem chat/messaging
- 📁 Document management
- 🎯 Project/task management
- 💼 Performance review workflow
- 📈 Custom reports builder

---

**Terima kasih telah menggunakan HRIS System!** 

Untuk support atau pertanyaan, silakan hubungi tim development.

**Version:** 1.0.0  
**Last Updated:** November 2025
