<?php
/**
 * Help Page
 */

require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Bantuan';
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-question-circle"></i> Bantuan & FAQ</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Dashboard -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h5>
            </div>
            <div class="card-body">
                <p>Dashboard menampilkan ringkasan data penting sistem:</p>
                <ul>
                    <li>Total karyawan dan departemen</li>
                    <li>Absensi hari ini</li>
                    <li>Karyawan terbaru</li>
                    <li>Permintaan cuti yang menunggu</li>
                </ul>
            </div>
        </div>
        
        <!-- Karyawan -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Manajemen Karyawan</h5>
            </div>
            <div class="card-body">
                <p><strong>Cara Menambah Karyawan:</strong></p>
                <ol>
                    <li>Klik menu "Karyawan"</li>
                    <li>Klik tombol "Tambah Karyawan"</li>
                    <li>Isi data pribadi dan pekerjaan</li>
                    <li>Klik "Simpan"</li>
                </ol>
                <p class="mt-2"><strong>Data yang Diperlukan:</strong></p>
                <ul>
                    <li>NIP (Nomor Identitas Pegawai)</li>
                    <li>Nama lengkap</li>
                    <li>Email</li>
                    <li>Departemen dan posisi</li>
                    <li>Gaji pokok</li>
                </ul>
            </div>
        </div>
        
        <!-- Absensi -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Sistem Absensi</h5>
            </div>
            <div class="card-body">
                <p><strong>Cara Input Absensi:</strong></p>
                <ol>
                    <li>Klik menu "Absensi"</li>
                    <li>Klik tombol "Input Absensi"</li>
                    <li>Pilih karyawan</li>
                    <li>Masukkan jam masuk dan keluar</li>
                    <li>Pilih status (Hadir, Sakit, Izin, dll)</li>
                    <li>Klik "Simpan"</li>
                </ol>
                <p class="mt-2"><strong>Status Absensi:</strong></p>
                <ul>
                    <li><span class="badge bg-success">Hadir</span> - Karyawan hadir</li>
                    <li><span class="badge bg-warning text-dark">Sakit</span> - Izin sakit</li>
                    <li><span class="badge bg-info">Izin</span> - Izin khusus</li>
                    <li><span class="badge bg-danger">Alfa</span> - Tidak hadir tanpa keterangan</li>
                </ul>
            </div>
        </div>
        
        <!-- Cuti -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Pengajuan Cuti</h5>
            </div>
            <div class="card-body">
                <p><strong>Cara Mengajukan Cuti:</strong></p>
                <ol>
                    <li>Klik menu "Cuti"</li>
                    <li>Klik tombol "Ajukan Cuti"</li>
                    <li>Pilih karyawan</li>
                    <li>Pilih jenis cuti</li>
                    <li>Tentukan tanggal mulai dan selesai</li>
                    <li>Masukkan alasan</li>
                    <li>Klik "Ajukan Cuti"</li>
                </ol>
                <p class="mt-2"><strong>Status Cuti:</strong></p>
                <ul>
                    <li><span class="badge bg-warning text-dark">Pending</span> - Menunggu persetujuan</li>
                    <li><span class="badge bg-success">Disetujui</span> - Sudah disetujui</li>
                    <li><span class="badge bg-danger">Ditolak</span> - Ditolak oleh manager</li>
                </ul>
            </div>
        </div>
        
        <!-- Gaji -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Manajemen Gaji</h5>
            </div>
            <div class="card-body">
                <p><strong>Cara Input Gaji:</strong></p>
                <ol>
                    <li>Klik menu "Gaji"</li>
                    <li>Klik tombol "Input Gaji"</li>
                    <li>Pilih karyawan, bulan, dan tahun</li>
                    <li>Masukkan gaji pokok, tunjangan, bonus, potongan</li>
                    <li>Klik "Simpan"</li>
                </ol>
                <p class="mt-2"><strong>Cara Lihat Slip Gaji:</strong></p>
                <ol>
                    <li>Klik menu "Slip Gaji"</li>
                    <li>Pilih bulan dan tahun</li>
                    <li>Klik tombol "Cetak" atau "Download"</li>
                </ol>
            </div>
        </div>
        
        <!-- Performa -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Penilaian Performa</h5>
            </div>
            <div class="card-body">
                <p><strong>Cara Memberikan Penilaian:</strong></p>
                <ol>
                    <li>Klik menu "Performa"</li>
                    <li>Klik tombol "Tambah Penilaian"</li>
                    <li>Pilih karyawan</li>
                    <li>Masukkan nilai kinerja (0-100)</li>
                    <li>Masukkan nilai perilaku (0-100)</li>
                    <li>Tambahkan catatan (opsional)</li>
                    <li>Klik "Simpan"</li>
                </ol>
                <p class="mt-2"><strong>Kriteria Penilaian:</strong></p>
                <ul>
                    <li>85-100: Sangat Baik</li>
                    <li>70-84: Baik</li>
                    <li>60-69: Cukup</li>
                    <li>&lt;60: Kurang</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Quick Tips -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Tips & Trik</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>📝 Pencarian:</strong>
                    <p style="font-size: 0.9rem;">Gunakan kotak pencarian untuk mencari data dengan cepat.</p>
                </div>
                <div class="mb-3">
                    <strong>📊 Export Data:</strong>
                    <p style="font-size: 0.9rem;">Gunakan tombol export untuk download data ke CSV.</p>
                </div>
                <div class="mb-3">
                    <strong>🔐 Keamanan:</strong>
                    <p style="font-size: 0.9rem;">Jangan bagikan password akun Anda kepada siapapun.</p>
                </div>
                <div class="mb-3">
                    <strong>⏰ Backup:</strong>
                    <p style="font-size: 0.9rem;">Admin harus backup database secara berkala.</p>
                </div>
            </div>
        </div>
        
        <!-- Contact Support -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-telephone"></i> Hubungi Support</h5>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> support@hris.com</p>
                <p><strong>Telepon:</strong> (021) 123-4567</p>
                <p><strong>Jam Kerja:</strong> Senin-Jumat, 09:00-17:00</p>
                <button class="btn btn-primary btn-sm w-100 mt-2">
                    <i class="bi bi-envelope"></i> Hubungi Kami
                </button>
            </div>
        </div>
        
        <!-- FAQ -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-left-dots"></i> FAQ</h5>
            </div>
            <div class="card-body">
                <details class="mb-2">
                    <summary style="cursor: pointer;"><strong>Lupa Password?</strong></summary>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Hubungi admin untuk reset password.</p>
                </details>
                
                <details class="mb-2">
                    <summary style="cursor: pointer;"><strong>Berapa lama data tersimpan?</strong></summary>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Data tersimpan selamanya sampai dihapus secara manual.</p>
                </details>
                
                <details>
                    <summary style="cursor: pointer;"><strong>Bisa akses dari HP?</strong></summary>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Ya, sistem sudah responsive untuk mobile devices.</p>
                </details>
            </div>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Bantuan & FAQ - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
