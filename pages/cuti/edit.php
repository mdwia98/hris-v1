<?php
/**
 * Cuti - Edit Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';


$page_title = 'Edit Cuti';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/cuti/index.php');
    exit();
}

// Get cuti data
$cuti = $conn->query("SELECT * FROM cuti WHERE id = $id")->fetch_assoc();

if (!$cuti || $cuti['status'] != 'Pending') {
    header('Location: ' . BASE_URL . 'pages/cuti/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $jenis_cuti = $_POST['jenis_cuti'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    
    if (empty($tanggal_mulai) || empty($tanggal_selesai) || empty($jenis_cuti)) {
        $error = 'Semua field harus diisi';
    } else if (strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
        $error = 'Tanggal selesai harus setelah tanggal mulai';
    } else {
        $query = "UPDATE cuti SET 
                    tanggal_mulai = '$tanggal_mulai',
                    tanggal_selesai = '$tanggal_selesai',
                    jenis_cuti = '$jenis_cuti',
                    alasan = '$alasan'
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            $success = 'Data cuti berhasil diperbarui';
            $cuti = $conn->query("SELECT * FROM cuti WHERE id = $id")->fetch_assoc();
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}

// Get employee info
$karyawan = $conn->query("SELECT * FROM karyawan WHERE id = {$cuti['karyawan_id']}")->fetch_assoc();
$durasi = (strtotime($cuti['tanggal_selesai']) - strtotime($cuti['tanggal_mulai'])) / (60 * 60 * 24) + 1;
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Cuti</h1>
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
            <div class="col-md-6">
                <label class="form-label"><strong>Nama Karyawan</strong></label>
                <p><?php echo htmlspecialchars($karyawan['nama']); ?></p>
            </div>
            <div class="col-md-6">
                <label class="form-label"><strong>Status</strong></label>
                <p><span class="badge bg-warning text-dark">Pending</span></p>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Cuti *</label>
                    <select name="jenis_cuti" class="form-control" required>
                        <option value="Cuti Tahunan" <?php echo $cuti['jenis_cuti'] == 'Cuti Tahunan' ? 'selected' : ''; ?>>Cuti Tahunan</option>
                        <option value="Cuti Sakit" <?php echo $cuti['jenis_cuti'] == 'Cuti Sakit' ? 'selected' : ''; ?>>Cuti Sakit</option>
                        <option value="Cuti Khusus" <?php echo $cuti['jenis_cuti'] == 'Cuti Khusus' ? 'selected' : ''; ?>>Cuti Khusus</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="<?php echo htmlspecialchars($cuti['tanggal_mulai']); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="<?php echo htmlspecialchars($cuti['tanggal_selesai']); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alasan/Keterangan *</label>
                <textarea name="alasan" class="form-control" rows="3" required><?php echo htmlspecialchars($cuti['alasan']); ?></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/cuti/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit Cuti - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
