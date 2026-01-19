<?php
ob_start();
/**
 * Gaji - List Slip & Generate PDF
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';

$page_title = 'Slip Gaji';

$user_role = $_SESSION['user']['role'] ?? '';
$karyawan_id   = $_SESSION['user']['karyawan_id'] ?? 0;

// Filter
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if ($bulan < 1 || $bulan > 12) $bulan = date('n');
if ($tahun < date('Y') - 10 || $tahun > date('Y')) $tahun = date('Y');

$where  = "WHERE g.bulan = ? AND g.tahun = ?";
$params = [$bulan, $tahun];
$types  = "ii";

if ($user_role === 'karyawan') {
    $where .= " AND g.karyawan_id = ?";
    $params[] = $karyawan_id;
    $types   .= "i";
}

// Query Data Slip Gaji (Prepared)
$sql = "
    SELECT  g.*, 
            k.nama, 
            k.nip, 
            k.posisi, 
            k.departemen, 
            k.penempatan
    FROM gaji g
    JOIN karyawan k ON g.karyawan_id = k.id
    $where
    ORDER BY k.nama
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="content-header">
    <h1><i class="bi bi-file-earmark-pdf"></i> Slip Gaji</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-control">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>>
                        <?= date('F', mktime(0,0,0,$i,1)) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-control">
                    <?php for ($i = date('Y'); $i >= date('Y') - 10; $i--): ?>
                    <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3" style="padding-top: 2rem;">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Slip Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-file-earmark-pdf"></i> Daftar Slip Gaji – 
        <?= date('F Y', mktime(0,0,0,$bulan,1,$tahun)) ?>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Penempatan</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nip']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['penempatan']) ?></td>

                        <td><?= htmlspecialchars($row['departemen']) ?></td>
                        <td><?= htmlspecialchars($row['posisi']) ?></td>

                        <td><strong>Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></strong></td>

                        <td>
                            <a href="generate_pdf.php?id=<?= $row['id'] ?>" 
                               class="btn btn-danger btn-sm" target="_blank">
                                <i class="bi bi-eye"></i> Lihat PDF
                            </a>
                            <a href="generate_pdf.php?id=<?= $row['id'] ?>" 
                                    class="btn btn-success btn-sm" target="_blank"
                                    download="dokumen-<?= $row['id'] ?>.pdf">
                                <i class="bi bi-download"></i> Unduh PDF
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            <i class="bi bi-info-circle"></i> Tidak ada data gaji bulan ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.title = "Slip Gaji - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>