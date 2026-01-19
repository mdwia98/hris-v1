<?php
/**
 * Settings Page
 */

require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Pengaturan Sistem';
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-gear"></i> Pengaturan Sistem</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-tools"></i> Pengaturan Umum
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Pengaturan sistem sedang dalam tahap pengembangan. Silakan hubungi administrator untuk perubahan pengaturan.
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-shield-lock"></i> Keamanan
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Ubah Password</strong></label>
                    <a href="change_password.php" class="btn btn-warning">
                        <i class="bi bi-key"></i> Ubah Password
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi Sistem
            </div>
            <div class="card-body">
                <p><strong>Nama Sistem:</strong> HRIS v1.0</p>
                <p><strong>Database:</strong> MySQL</p>
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Server:</strong> Apache</p>
                <hr>
                <p style="font-size: 0.85rem; color: #999;">
                    <i class="bi bi-heart-fill" style="color: #1e7e34;"></i> 
                    Dikembangkan dengan cinta untuk manajemen SDM yang lebih baik.
                </p>
            </div>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Settings - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
