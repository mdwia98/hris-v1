<?php ob_start(); ?>
<?php
/**
 * Absensi - Edit Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();


$page_title = 'Edit Absensi';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/absensi/index.php');
    exit();
}

// Get absensi data
$absensi = $conn->query("SELECT a.*, k.nama FROM absensi a JOIN karyawan k ON a.karyawan_id = k.id WHERE a.id = $id")->fetch_assoc();

if (!$absensi) {
    header('Location: ' . BASE_URL . 'pages/absensi/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jam_masuk = $_POST['jam_masuk'] ?? '';
    $jam_keluar = $_POST['jam_keluar'] ?? '';
    $status = $_POST['status'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    if (empty($status)) {
        $error = 'Status harus diisi';
    } else {
        $query = "UPDATE absensi SET 
                    jam_masuk = " . ($jam_masuk ? "'$jam_masuk'" : "NULL") . ",
                    jam_keluar = " . ($jam_keluar ? "'$jam_keluar'" : "NULL") . ",
                    status = '$status',
                    keterangan = '$keterangan'
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            $success = 'Data absensi berhasil diperbarui';
            // Reload data
            $absensi = $conn->query("SELECT a.*, k.nama FROM absensi a JOIN karyawan k ON a.karyawan_id = k.id WHERE a.id = $id")->fetch_assoc();
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-calendar-check"></i> Edit Absensi</h1>
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
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Karyawan</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($absensi['nama']); ?>" disabled>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" value="<?php echo htmlspecialchars($absensi['tanggal']); ?>" disabled>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" class="form-control" value="<?php echo $absensi['jam_masuk'] ? htmlspecialchars($absensi['jam_masuk']) : ''; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jam Keluar</label>
                    <input type="time" name="jam_keluar" class="form-control" value="<?php echo $absensi['jam_keluar'] ? htmlspecialchars($absensi['jam_keluar']) : ''; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="Hadir" <?php echo $absensi['status'] == 'Hadir' ? 'selected' : ''; ?>>Hadir</option>
                        <option value="Sakit" <?php echo $absensi['status'] == 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                        <option value="Izin" <?php echo $absensi['status'] == 'Izin' ? 'selected' : ''; ?>>Izin</option>
                        <option value="Cuti" <?php echo $absensi['status'] == 'Cuti' ? 'selected' : ''; ?>>Cuti</option>
                        <option value="Libur" <?php echo $absensi['status'] == 'Libur' ? 'selected' : ''; ?>>Libur</option>
                        <option value="Alfa" <?php echo $absensi['status'] == 'Alfa' ? 'selected' : ''; ?>>Alfa</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"><?php echo htmlspecialchars($absensi['keterangan'] ?? ''); ?></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/absensi/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit Absensi - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>