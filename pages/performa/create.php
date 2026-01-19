<?php ob_start(); ?>
<?php
session_start();
/**
 * Performa - Create Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();

$page_title = 'Tambah Penilaian Performa';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
    $bulan = (int)($_POST['bulan'] ?? 0);
    $tahun = (int)($_POST['tahun'] ?? 0);
    $nilai_kinerja = (float)($_POST['nilai_kinerja'] ?? 0);
    $nilai_perilaku = (float)($_POST['nilai_perilaku'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($karyawan_id) || empty($bulan) || empty($tahun) || empty($nilai_kinerja) || empty($nilai_perilaku)) {
        $error = 'Semua field harus diisi';
    } else if ($nilai_kinerja < 0 || $nilai_kinerja > 100 || $nilai_perilaku < 0 || $nilai_perilaku > 100) {
        $error = 'Nilai harus antara 0-100';
    } else {
        // Check if record exists
        $check = $conn->query("SELECT id FROM performa WHERE karyawan_id = $karyawan_id AND bulan = $bulan AND tahun = $tahun");
        
        if ($check->num_rows > 0) {
            // Update
            $query = "UPDATE performa SET 
                        nilai_kinerja = $nilai_kinerja,
                        nilai_perilaku = $nilai_perilaku,
                        catatan = '$catatan',
                        dievaluasi_oleh = $user_id
                      WHERE karyawan_id = $karyawan_id AND bulan = $bulan AND tahun = $tahun";
        } else {
            // Insert
            $query = "INSERT INTO performa (karyawan_id, bulan, tahun, nilai_kinerja, nilai_perilaku, catatan, dievaluasi_oleh)
                      VALUES ($karyawan_id, $bulan, $tahun, $nilai_kinerja, $nilai_perilaku, '$catatan', $user_id)";
        }
        
        if ($conn->query($query)) {
            header('Location: ' . BASE_URL . 'pages/performa/index.php?bulan=' . $bulan . '&tahun=' . $tahun);
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
    <h1><i class="bi bi-graph-up"></i> Tambah Penilaian Performa</h1>
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
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Bulan *</label>
                    <select name="bulan" class="form-control" required>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == date('n') ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun *</label>
                    <select name="tahun" class="form-control" required>
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == date('Y') ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Kinerja (0-100) *</label>
                    <div class="input-group">
                        <input type="number" name="nilai_kinerja" class="form-control" min="0" max="100" step="0.1" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Perilaku (0-100) *</label>
                    <div class="input-group">
                        <input type="number" name="nilai_perilaku" class="form-control" min="0" max="100" step="0.1" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan penilaian (opsional)"></textarea>
            </div>
            
            <div class="alert alert-info" id="rataRata" style="display: none;">
                <strong>Rata-rata Penilaian: <span id="rataRataValue">0</span>%</strong>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/performa/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kinerjaInput = document.querySelector('input[name="nilai_kinerja"]');
    const perilakuInput = document.querySelector('input[name="nilai_perilaku"]');
    const rataRataDiv = document.getElementById('rataRata');
    const rataRataValue = document.getElementById('rataRataValue');
    
    function updateRataRata() {
        const kinerja = parseFloat(kinerjaInput.value) || 0;
        const perilaku = parseFloat(perilakuInput.value) || 0;
        const rata = ((kinerja + perilaku) / 2).toFixed(2);
        rataRataValue.textContent = rata;
        rataRataDiv.style.display = 'block';
    }
    
    kinerjaInput.addEventListener('change', updateRataRata);
    perilakuInput.addEventListener('change', updateRataRata);
});
</script>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Input Performance - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>