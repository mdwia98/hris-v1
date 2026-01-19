<?php
/**
 * Departemen - Edit Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';

$page_title = 'Edit Departemen';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/departemen/index.php');
    exit();
}

// Get departemen data
$departemen = $conn->query("SELECT * FROM departemen WHERE id = $id")->fetch_assoc();

if (!$departemen) {
    header('Location: ' . BASE_URL . 'pages/departemen/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_departemen'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    
    if (empty($nama)) {
        $error = 'Nama departemen harus diisi';
    } else {
        $query = "UPDATE departemen SET nama_departemen = '$nama', keterangan = '$keterangan' WHERE id = $id";
        
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
    <h1><i class="bi bi-building"></i> Edit Departemen</h1>
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
                <input type="text" name="nama_departemen" class="form-control" value="<?php echo htmlspecialchars($departemen['nama_departemen']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"><?php echo htmlspecialchars($departemen['keterangan'] ?? ''); ?></textarea>
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
    document.title = "Edit Departemen - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
