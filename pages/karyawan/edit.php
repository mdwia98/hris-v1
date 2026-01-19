<?php
ob_start();
session_start();
/**
 * Karyawan - Edit Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Edit Karyawan';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/karyawan/index.php');
    exit();
}

// Get karyawan data
$karyawan = $conn->query("SELECT * FROM karyawan WHERE id = $id")->fetch_assoc();

if (!$karyawan) {
    header('Location: ' . BASE_URL . 'pages/karyawan/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $departemen = trim($_POST['departemen'] ?? '');
    $posisi = trim($_POST['posisi'] ?? '');
    $status_kerja = trim($_POST['status_kerja'] ?? '');
    $gaji = trim($_POST['gaji'] ?? '');
    $tanggal_join = trim($_POST['tanggal_join'] ?? '');
    
    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email harus diisi';
    } else {
        $query = "UPDATE karyawan SET 
                    nama = '$nama',
                    email = '$email',
                    telepon = '$telepon',
                    alamat = '$alamat',
                    tanggal_lahir = '$tanggal_lahir',
                    jenis_kelamin = '$jenis_kelamin',
                    departemen = '$departemen',
                    posisi = '$posisi',
                    status_kerja = '$status_kerja',
                    gaji = $gaji,
                    tanggal_join = '$tanggal_join'
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            $success = 'Data karyawan berhasil diperbarui';

            // 📌 LOG: Edit Karyawan (nama) – ID: xx
                writeLog(
                    'karyawan',
                    'edit',
                    "Edit Karyawan ({$nama}) – ID: {$id}"
            );

            // Reload data
            $karyawan = $conn->query("SELECT * FROM karyawan WHERE id = $id")->fetch_assoc();
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}

// Get departments
$departments = $conn->query("SELECT * FROM departemen ORDER BY nama_departemen");
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Karyawan</h1>
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
            <h5 class="mb-3"><i class="bi bi-person"></i> Informasi Pribadi</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($karyawan['nip']); ?>" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($karyawan['nama']); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($karyawan['email']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="tel" name="telepon" class="form-control" value="<?php echo htmlspecialchars($karyawan['telepon'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($karyawan['tanggal_lahir'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" <?php echo $karyawan['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo $karyawan['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($karyawan['alamat'] ?? ''); ?></textarea>
            </div>
            
            <hr>
            <h5 class="mb-3"><i class="bi bi-briefcase"></i> Informasi Pekerjaan</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departemen</label>
                    <select name="departemen" class="form-control">
                        <option value="">-- Pilih Departemen --</option>
                        <?php 
                        $departments->data_seek(0);
                        while ($dept = $departments->fetch_assoc()): 
                        ?>
                        <option value="<?php echo htmlspecialchars($dept['nama_departemen']); ?>" <?php echo $karyawan['departemen'] == $dept['nama_departemen'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Posisi</label>
                    <input type="text" name="posisi" class="form-control" value="<?php echo htmlspecialchars($karyawan['posisi'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kerja</label>
                    <select name="status_kerja" class="form-control">
                        <option value="Tetap" <?php echo $karyawan['status_kerja'] == 'Tetap' ? 'selected' : ''; ?>>Tetap</option>
                        <option value="Kontrak" <?php echo $karyawan['status_kerja'] == 'Kontrak' ? 'selected' : ''; ?>>Kontrak</option>
                        <option value="Magang" <?php echo $karyawan['status_kerja'] == 'Magang' ? 'selected' : ''; ?>>Magang</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gaji Pokok</label>
                    <input type="number" name="gaji" class="form-control" step="1" value="<?php echo htmlspecialchars($karyawan['gaji'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Tanggal Join</label>
                <input type="date" name="tanggal_join" class="form-control" value="<?php echo htmlspecialchars($karyawan['tanggal_join'] ?? ''); ?>">
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/karyawan/detail.php?id=<?php echo $karyawan['id']; ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit Karyawan - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
