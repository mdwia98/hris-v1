<?php
/**
 * Cuti - Create Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';


$page_title = 'Ajukan Cuti';

$error = '';
$success = '';

// ==== PROSES FORM DITEMPATKAN SEBELUM HEADER.HTML ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $karyawan_id = $_POST['karyawan_id'] ?? '';
    $nama_karyawan = $_POST['nama_karyawan'] ?? '';
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $jenis_cuti = $_POST['jenis_cuti'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    $file_cuti = $_POST['file_cuti'] ?? null;

    if (empty($karyawan_id) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($jenis_cuti)) {
        $error = 'Semua field harus diisi';
    } elseif (strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
        $error = 'Tanggal selesai harus setelah tanggal mulai';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO cuti (karyawan_id, nama_karyawan, tanggal_mulai, tanggal_selesai, jenis_cuti, alasan, file_cuti, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->bind_param("issssss", $karyawan_id, $nama_karyawan, $tanggal_mulai, $tanggal_selesai, $jenis_cuti, $alasan, $file_cuti);
        if ($stmt->execute()) {
            header('Location: ' . BASE_URL . 'pages/cuti/index.php');
            exit();
        } else {
            $error = 'Terjadi kesalahan: ' . $conn->error;
        }
    }
}

// ambil data karyawan setelah proses POST selesai
$karyawan = $conn->query("SELECT id, nip, nama FROM karyawan ORDER BY nama");

require_once '../../includes/header.php';
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-calendar-x"></i> Ajukan Cuti</h1>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error); ?>
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
                        <option value="<?= $k['id']; ?>">
                            <?= htmlspecialchars($k['nip'] . ' - ' . $k['nama']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Cuti *</label>
                    <select name="jenis_cuti" class="form-control" required>
                        <option value="">-- Pilih Jenis Cuti --</option>
                        <option value="Cuti Tahunan">Cuti Tahunan</option>
                        <option value="Cuti Sakit">Cuti Sakit</option>
                        <option value="Cuti Khusus">Cuti Khusus</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">File Cuti *</label>
                    <input type="file" name="file_cuti" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alasan/Keterangan *</label>
                <textarea name="alasan" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Ajukan Cuti
            </button>
            <a href="<?= BASE_URL; ?>pages/cuti/index.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Input Cuti - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
