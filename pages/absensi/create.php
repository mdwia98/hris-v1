<?php ob_start(); ?>
<?php
/**
 * Absensi - Create Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Input Absensi';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $karyawan_id = $_POST['karyawan_id'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_masuk = $_POST['jam_masuk'] ?? '';
    $jam_keluar = $_POST['jam_keluar'] ?? '';
    $status = $_POST['status'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    if (empty($karyawan_id) || empty($tanggal) || empty($status)) {
        $error = 'Karyawan, tanggal, dan status harus diisi';
    } else {
        // Check if record exists
        $check = $conn->query("SELECT id FROM absensi WHERE karyawan_id = $karyawan_id AND tanggal = '$tanggal'");
        
        if ($check->num_rows > 0) {
            // Update
            $query = "UPDATE absensi SET 
                        jam_masuk = " . ($jam_masuk ? "'$jam_masuk'" : "NULL") . ",
                        jam_keluar = " . ($jam_keluar ? "'$jam_keluar'" : "NULL") . ",
                        status = '$status',
                        keterangan = '$keterangan'
                        WHERE karyawan_id = $karyawan_id AND tanggal = '$tanggal'";
        } else {
            // Insert
            $query = "INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, jam_keluar, status, keterangan)
                      VALUES ($karyawan_id, '$tanggal', 
                              " . ($jam_masuk ? "'$jam_masuk'" : "NULL") . ",
                              " . ($jam_keluar ? "'$jam_keluar'" : "NULL") . ",
                              '$status', '$keterangan')";
        }
        
        if ($conn->query($query)) {
            $success = 'Data absensi berhasil disimpan';
            addLog($_SESSION['user']['id'], "Create Absensi", "Tambah absensi untuk $karyawanNama");
            header('Location: ' . BASE_URL . 'pages/absensi/index.php?date=' . $tanggal);
            exit();
        } else {
            $error = 'Terjadi kesalahan: ' . $conn->error;
        }
    }
}

// Get employees
$karyawan = $conn->query("SELECT id, nip, nama FROM karyawan ORDER BY nama");
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-calendar-check"></i> Input Absensi</h1>
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
                    <label class="form-label">Karyawan *</label>
                    <select name="karyawan_id" class="form-control" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php while ($k = $karyawan->fetch_assoc()): ?>
                        <option value="<?php echo $k['id']; ?>">
                            <?php echo htmlspecialchars($k['nip'] . ' - ' . $k['nama']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" class="form-control">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jam Keluar</label>
                    <input type="time" name="jam_keluar" class="form-control">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Libur">Libur</option>
                        <option value="Alfa">Alfa</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
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
    document.title = "Input Absensi - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>