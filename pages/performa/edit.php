<?php
ob_start();
session_start();
/**
 * Performa - Edit Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();

$page_title = 'Edit Penilaian Performa';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/performa/index.php');
    exit();
}

// Get performa data
$performa = $conn->query("SELECT * FROM performa WHERE id = $id")->fetch_assoc();

if (!$performa) {
    header('Location: ' . BASE_URL . 'pages/performa/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nilai_kinerja = (float)($_POST['nilai_kinerja'] ?? 0);
    $nilai_perilaku = (float)($_POST['nilai_perilaku'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    
    if ($nilai_kinerja < 0 || $nilai_kinerja > 100 || $nilai_perilaku < 0 || $nilai_perilaku > 100) {
        $error = 'Nilai harus antara 0-100';
    } else {
        $user_id = $_SESSION['user_id'];
        $query = "UPDATE performa SET 
                    nilai_kinerja = $nilai_kinerja,
                    nilai_perilaku = $nilai_perilaku,
                    catatan = '$catatan',
                    dievaluasi_oleh = $user_id
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            $success = 'Data performa berhasil diperbarui';
            $performa = $conn->query("SELECT * FROM performa WHERE id = $id")->fetch_assoc();
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}

// Get employee info
$karyawan = $conn->query("SELECT * FROM karyawan WHERE id = {$performa['karyawan_id']}")->fetch_assoc();
$rata_rata = ($performa['nilai_kinerja'] + $performa['nilai_perilaku']) / 2;
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Penilaian Performa</h1>
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
                <p><?php echo date('F Y', mktime(0, 0, 0, $performa['bulan'], 1, $performa['tahun'])); ?></p>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Rata-rata</strong></label>
                <p><strong><?php echo number_format($rata_rata, 2); ?>%</strong></p>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Kinerja (0-100) *</label>
                    <div class="input-group">
                        <input type="number" name="nilai_kinerja" class="form-control" min="0" max="100" step="0.1" value="<?php echo $performa['nilai_kinerja']; ?>" required onchange="updateRataRata()">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Perilaku (0-100) *</label>
                    <div class="input-group">
                        <input type="number" name="nilai_perilaku" class="form-control" min="0" max="100" step="0.1" value="<?php echo $performa['nilai_perilaku']; ?>" required onchange="updateRataRata()">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-control" rows="3"><?php echo htmlspecialchars($performa['catatan'] ?? ''); ?></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/performa/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function updateRataRata() {
    // Optional: add real-time calculation
}
</script>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit Performance - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
