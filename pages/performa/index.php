<?php
ob_start();
session_start();
/**
 * Performa - List Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();

$page_title = 'Penilaian Performa';

// Get filter
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Get performa data
$result = $conn->query("
    SELECT p.*, k.nama, k.nip FROM performa p
    JOIN karyawan k ON p.karyawan_id = k.id
    WHERE p.bulan = $bulan AND p.tahun = $tahun
    ORDER BY k.nama
");
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-graph-up"></i> Penilaian Performa</h1>
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
                    <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
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
                <a href="<?php echo BASE_URL; ?>pages/performa/create.php" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Tambah Penilaian
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Penilaian - <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Nilai Kinerja</th>
                        <th>Nilai Perilaku</th>
                        <th>Rata-rata</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                        $rata_rata = ($row['nilai_kinerja'] + $row['nilai_perilaku']) / 2;
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nip']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo number_format($row['nilai_kinerja'], 2); ?></td>
                        <td><?php echo number_format($row['nilai_perilaku'], 2); ?></td>
                        <td>
                            <strong><?php echo number_format($rata_rata, 2); ?></strong>
                            <?php
                            if ($rata_rata >= 85) $badge = 'Sangat Baik';
                            elseif ($rata_rata >= 70) $badge = 'Baik';
                            elseif ($rata_rata >= 60) $badge = 'Cukup';
                            else $badge = 'Kurang';
                            ?>
                            <br><span class="badge bg-primary" style="font-size: 0.75rem;"><?php echo $badge; ?></span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>pages/performa/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Performance - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
