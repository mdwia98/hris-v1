<?php ob_start(); ?>
<?php
/**
 * Gaji - Create Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Input Gaji';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
    $bulan = (int)($_POST['bulan'] ?? 0);
    $tahun = (int)($_POST['tahun'] ?? 0);
    $gaji_pokok = (float)($_POST['gaji_pokok'] ?? 0);
    $tunjangan = (float)($_POST['tunjangan'] ?? 0);
    $bonus = (float)($_POST['bonus'] ?? 0);
    $potongan = (float)($_POST['potongan'] ?? 0);
    
    if (empty($karyawan_id) || empty($bulan) || empty($tahun)) {
        $error = 'Karyawan, bulan, dan tahun harus diisi';
    } else {
        $total_gaji = $gaji_pokok + $tunjangan + $bonus - $potongan;
        
        // Check if record exists
        $check = $conn->query("SELECT id FROM gaji WHERE karyawan_id = $karyawan_id AND bulan = $bulan AND tahun = $tahun");
        
        if ($check->num_rows > 0) {
            // Update
            $query = "UPDATE gaji SET 
                        gaji_pokok = $gaji_pokok,
                        tunjangan = $tunjangan,
                        bonus = $bonus,
                        potongan = $potongan,
                        total_gaji = $total_gaji
                      WHERE karyawan_id = $karyawan_id AND bulan = $bulan AND tahun = $tahun";
        } else {
            // Insert
            $query = "INSERT INTO gaji (karyawan_id, bulan, tahun, gaji_pokok, tunjangan, bonus, potongan, total_gaji, status)
                      VALUES ($karyawan_id, $bulan, $tahun, $gaji_pokok, $tunjangan, $bonus, $potongan, $total_gaji, 'Draft')";
        }
        
        if ($conn->query($query)) {
            header('Location: ' . BASE_URL . 'pages/gaji/index.php?bulan=' . $bulan . '&tahun=' . $tahun);
            exit();
        } else {
            $error = 'Terjadi kesalahan: ' . $conn->error;
        }
    }
}

// Get employees with their base salary
$karyawan = $conn->query("SELECT id, nip, nama, gaji FROM karyawan ORDER BY nama");
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-cash-coin"></i> Input Gaji</h1>
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
                    <select name="karyawan_id" class="form-control" id="karyawanSelect" required onchange="setSalary()">
                        <option value="">-- Pilih Karyawan --</option>
                        <?php while ($k = $karyawan->fetch_assoc()): ?>
                        <option value="<?php echo $k['id']; ?>" data-salary="<?php echo $k['gaji']; ?>">
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
                        <?php for ($i = date('Y'); $i >= date('Y') - 10; $i--): ?>
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
                    <label class="form-label">Gaji Pokok *</label>
                    <input type="number" name="gaji_pokok" class="form-control" step="1" id="gajiPokok" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tunjangan</label>
                    <input type="number" name="tunjangan" class="form-control" step="1" value="0">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bonus</label>
                    <input type="number" name="bonus" class="form-control" step="1" value="0">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Potongan</label>
                    <input type="number" name="potongan" class="form-control" step="1" value="0">
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/gaji/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function setSalary() {
    const select = document.getElementById('karyawanSelect');
    const selectedOption = select.options[select.selectedIndex];
    const salary = selectedOption.getAttribute('data-salary');
    document.getElementById('gajiPokok').value = salary || '';
}
</script>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Input Gaji - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>