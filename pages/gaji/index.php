<?php ob_start(); ?>
<?php
/**
 * Gaji - List Page (Final Version)
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Manajemen Gaji';


// ===============================
// FILTER INPUT
// ===============================
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Pastikan bulan valid 1-12
if ($bulan < 1 || $bulan > 12) {
    $bulan = date('n');
}

// Tahun valid (range 5 tahun ke belakang)
$currentYear = date('Y');
if ($tahun < ($currentYear - 5) || $tahun > $currentYear) {
    $tahun = $currentYear;
}


// ===============================
// QUERY DATA GAJI (Prepared Statement)
// ===============================
$stmt = $conn->prepare("
    SELECT 
        g.*, 
        k.nama, 
        k.nip 
    FROM gaji g
    INNER JOIN karyawan k ON g.karyawan_id = k.id
    WHERE g.bulan = ? AND g.tahun = ?
    ORDER BY k.nama
");

$stmt->bind_param("ii", $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-cash-coin"></i> Manajemen Gaji</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-control">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $bulan ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-control">
                    <?php for ($i = $currentYear; $i >= $currentYear - 5; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $tahun ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3" style="padding-top: 2rem;">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>

            <div class="col-md-3 text-end" style="padding-top: 2rem;">
                <a href="<?php echo BASE_URL; ?>pages/gaji/create.php" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Input Gaji
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Gaji - 
        <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Bonus</th>
                        <th>Potongan</th>
                        <th>Total Gaji</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>

                        <td><?php echo htmlspecialchars($row['nip']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>

                        <td>Rp <?php echo number_format($row['gaji_pokok'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($row['tunjangan'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($row['bonus'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($row['potongan'], 0, ',', '.'); ?></td>

                        <td><strong>Rp <?php echo number_format($row['total_gaji'], 0, ',', '.'); ?></strong></td>

                        <td>
                            <?php
                            $statusClass = [
                                'Draft'     => 'bg-secondary',
                                'Diproses'  => 'bg-warning text-dark',
                                'Selesai'   => 'bg-success'
                            ];
                            ?> 
                            <span class="badge <?php echo $statusClass[$row['status']] ?? 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>

                        <td>
                            <a href="<?php echo BASE_URL; ?>pages/gaji/edit.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">
                            <i class="bi bi-info-circle"></i> Tidak ada data gaji untuk periode ini.
                        </td>
                    </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Masukkan kode ini di setiap halaman menu yang berbeda
document.title = "Payroll - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>