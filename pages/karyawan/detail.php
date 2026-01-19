<?php
ob_start();
/**
 * Karyawan - Detail Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Detail Karyawan';

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
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-person"></i> Detail Karyawan</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-text"></i> Informasi Pribadi
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Nama Lengkap</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['nama']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>NIP</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['nip']); ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Email</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Telepon</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['telepon'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Tanggal Lahir</strong></label>
                        <p><?php echo $karyawan['tanggal_lahir'] ? date('d F Y', strtotime($karyawan['tanggal_lahir'])) : '-'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Jenis Kelamin</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['jenis_kelamin'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Alamat</strong></label>
                    <p><?php echo htmlspecialchars($karyawan['alamat'] ?? '-'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-briefcase"></i> Informasi Pekerjaan
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Departemen</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['departemen']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Posisi</strong></label>
                        <p><?php echo htmlspecialchars($karyawan['posisi']); ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Status Kerja</strong></label>
                        <p><span class="badge bg-success"><?php echo htmlspecialchars($karyawan['status_kerja']); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Tanggal Join</strong></label>
                        <p><?php echo date('d F Y', strtotime($karyawan['tanggal_join'])); ?></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Gaji Pokok</strong></label>
                    <p><strong>Rp <?php echo number_format($karyawan['gaji'], 0, ',', '.'); ?></strong></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-tools"></i> Aksi
            </div>
            <div class="card-body">
                <a href="<?php echo BASE_URL; ?>pages/karyawan/index.php" class="btn btn-secondary w-100 mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                
                <a href="<?php echo BASE_URL; ?>pages/karyawan/edit.php?id=<?php echo $karyawan['id']; ?>" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-activity"></i> Quick Links
            </div>
            <div class="card-body">
                <a href="<?php echo BASE_URL; ?>pages/absensi/index.php?karyawan=<?php echo $karyawan['id']; ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="bi bi-calendar-check"></i> Lihat Absensi
                </a>
                <a href="<?php echo BASE_URL; ?>pages/cuti/index.php" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-calendar-x"></i> Lihat Cuti
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Detail Karyawan - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>