<?php
ob_start();
/**
 * Gaji - Edit Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Edit Gaji';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/gaji/index.php');
    exit();
}

// Get gaji data
$gaji = $conn->query("SELECT * FROM gaji WHERE id = $id")->fetch_assoc();

if (!$gaji) {
    header('Location: ' . BASE_URL . 'pages/gaji/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gaji_pokok = (float)($_POST['gaji_pokok'] ?? 0);
    $tunjangan = (float)($_POST['tunjangan'] ?? 0);
    $bonus = (float)($_POST['bonus'] ?? 0);
    $potongan = (float)($_POST['potongan'] ?? 0);
    $status = $_POST['status'] ?? 'Draft';
    
    $total_gaji = $gaji_pokok + $tunjangan + $bonus - $potongan;
    
    $query = "UPDATE gaji SET 
                gaji_pokok = $gaji_pokok,
                tunjangan = $tunjangan,
                bonus = $bonus,
                potongan = $potongan,
                total_gaji = $total_gaji,
                status = '$status'
              WHERE id = $id";
    
    if ($conn->query($query)) {
        $success = 'Data gaji berhasil diperbarui';
        $gaji = $conn->query("SELECT * FROM gaji WHERE id = $id")->fetch_assoc();
    } else {
        $error = 'Error: ' . $conn->error;
    }
}

// Get employee info
$karyawan = $conn->query("SELECT * FROM karyawan WHERE id = {$gaji['karyawan_id']}")->fetch_assoc();
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Gaji</h1>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label"><strong>Karyawan</strong></label>
                <p><?php echo htmlspecialchars($karyawan['nama']); ?></p>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>NIP</strong></label>
                <p><?php echo htmlspecialchars($karyawan['nip']); ?></p>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Bulan/Tahun</strong></label>
                <p><?php echo date('F Y', mktime(0, 0, 0, $gaji['bulan'], 1, $gaji['tahun'])); ?></p>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Status</strong></label>
                <p><?php echo htmlspecialchars($gaji['status']); ?></p>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gaji Pokok *</label>
                    <input type="number" name="gaji_pokok" class="form-control" step="1" value="<?php echo $gaji['gaji_pokok']; ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tunjangan</label>
                    <input type="number" name="tunjangan" class="form-control" step="1" value="<?php echo $gaji['tunjangan']; ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bonus</label>
                    <input type="number" name="bonus" class="form-control" step="1" value="<?php echo $gaji['bonus']; ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Potongan</label>
                    <input type="number" name="potongan" class="form-control" step="1" value="<?php echo $gaji['potongan']; ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="Draft" <?php echo $gaji['status'] == 'Draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="Diproses" <?php echo $gaji['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="Selesai" <?php echo $gaji['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            
            <div class="alert alert-info">
                <strong>Total Gaji:</strong> Rp <?php echo number_format($gaji['gaji_pokok'] + $gaji['tunjangan'] + $gaji['bonus'] - $gaji['potongan'], 0, ',', '.'); ?>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/gaji/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit Gaji - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ob_end_flush(); ?>
