<?php
/**
 * Privacy Policy Page
 */

require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Kebijakan Privasi';
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-shield-check"></i> Kebijakan Privasi</h1>
</div>

<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Kebijakan Privasi HRIS System</h4>
                
                <h5 class="mt-4">1. Pengumpulan Data</h5>
                <p>Sistem HRIS mengumpulkan data pribadi karyawan yang meliputi:</p>
                <ul>
                    <li>Nama dan identitas pribadi</li>
                    <li>Informasi kontak (email, telepon)</li>
                    <li>Data pekerjaan dan gaji</li>
                    <li>Riwayat absensi dan performa</li>
                </ul>
                
                <h5 class="mt-4">2. Penggunaan Data</h5>
                <p>Data digunakan untuk:</p>
                <ul>
                    <li>Manajemen sumber daya manusia</li>
                    <li>Proses administrasi gaji</li>
                    <li>Evaluasi performa karyawan</li>
                    <li>Pelaporan dan analisis</li>
                </ul>
                
                <h5 class="mt-4">3. Keamanan Data</h5>
                <p>Kami berkomitmen melindungi data Anda melalui:</p>
                <ul>
                    <li>Enkripsi password dengan bcrypt hashing</li>
                    <li>Kontrol akses berbasis role</li>
                    <li>Backup database berkala</li>
                    <li>Monitoring keamanan sistem</li>
                </ul>
                
                <h5 class="mt-4">4. Akses Data</h5>
                <p>Hanya pengguna yang terotorisasi yang dapat mengakses data:</p>
                <ul>
                    <li><strong>Admin:</strong> Akses penuh ke semua data</li>
                    <li><strong>Manager:</strong> Akses data tim mereka</li>
                    <li><strong>Karyawan:</strong> Akses data pribadi mereka</li>
                </ul>
                
                <h5 class="mt-4">5. Retensi Data</h5>
                <p>Data disimpan sesuai kebijakan perusahaan dan berlaku hukum yang ada. Anda berhak meminta penghapusan data pribadi Anda kapan saja.</p>
                
                <h5 class="mt-4">6. Compliance</h5>
                <p>Sistem ini dirancang sesuai dengan standar keamanan data dan privasi yang berlaku.</p>
                
                <h5 class="mt-4">7. Perubahan Kebijakan</h5>
                <p>Kami berhak mengubah kebijakan privasi ini kapan saja. Perubahan akan diumumkan melalui sistem.</p>
                
                <h5 class="mt-4">8. Kontak</h5>
                <p>Jika memiliki pertanyaan tentang privasi, silakan hubungi:</p>
                <p>
                    <strong>Email:</strong> privacy@hris.com<br>
                    <strong>Telepon:</strong> (021) 123-4567
                </p>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i> 
                    Terakhir diperbarui: November 2025
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Kebijakan Privasi - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
