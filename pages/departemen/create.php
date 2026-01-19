<?php ob_start(); ?>
<?php
/**
 * Departemen - Create Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';

$page_title = 'Tambah Departemen';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_departemen'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    
    if (empty($nama)) {
        $error = 'Nama departemen harus diisi';
    } else {
        $query = "INSERT INTO departemen (nama_departemen, keterangan) VALUES ('$nama', '$keterangan')";
        
        if ($conn->query($query)) {
            header('Location: ' . BASE_URL . 'pages/departemen/index.php');
            exit();
        } else {
            $error = 'Terjadi kesalahan: ' . $conn->error;
        }
    }
}
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-building"></i> Tambah Departemen Baru</h1>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Nama Departemen *</label>
                <input type="text" name="nama_departemen" class="form-control" placeholder="Contoh: IT, HR, Finance" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan departemen (opsional)"></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/departemen/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Tambah Departemen - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>