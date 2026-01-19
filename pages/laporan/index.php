<?php
ob_start();
session_start();
/**
 * Laporan - Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();

$page_title = 'Laporan';

// Get statistics
$total_karyawan = $conn->query("SELECT COUNT(*) as count FROM karyawan")->fetch_assoc()['count'];
$karyawan_aktif = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'karyawan' AND status = 'aktif'")->fetch_assoc()['count'];
$total_departemen = $conn->query("SELECT COUNT(*) as count FROM departemen")->fetch_assoc()['count'];

// Get absensi stats
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$absensi_stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'Alfa' THEN 1 ELSE 0 END) as alfa
    FROM absensi
    WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun
")->fetch_assoc();

writeLog('Laporan', 'View', "Mengakses halaman laporan");
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-file-earmark-text"></i> Laporan</h1>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-lg-3">
        <div class="stat-card">
            <i class="bi bi-people" style="font-size: 2rem;"></i>
            <div class="stat-number"><?php echo $total_karyawan; ?></div>
            <div class="stat-label">Total Karyawan</div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="stat-card yellow">
            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
            <div class="stat-number"><?php echo $karyawan_aktif; ?></div>
            <div class="stat-label">Karyawan Aktif</div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="stat-card">
            <i class="bi bi-building" style="font-size: 2rem;"></i>
            <div class="stat-number"><?php echo $total_departemen; ?></div>
            <div class="stat-label">Departemen</div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="stat-card dark">
            <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
            <div class="stat-number"><?php echo $absensi_stats['hadir'] ?? 0; ?></div>
            <div class="stat-label">Hadir Bulan Ini</div>
        </div>
    </div>
</div>

<!-- Report Sections -->
<div class="row">
    <!-- Laporan Karyawan -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-people"></i> Laporan Karyawan Per Departemen
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Jumlah Karyawan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $depts = $conn->query("
                                SELECT departemen, COUNT(*) as count 
                                FROM karyawan 
                                GROUP BY departemen 
                                ORDER BY departemen
                            ");
                            
                            while ($dept = $depts->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['departemen']); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $dept['count']; ?> karyawan</span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Laporan Absensi -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i> Laporan Absensi Bulan <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-6">
                            <select name="bulan" class="form-control form-control-sm">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == $bulan ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="tahun" class="form-control form-control-sm">
                                <?php for ($i = date('Y'); $i >= date('Y') - 3; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == $tahun ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">Filter</button>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Total Absensi</strong></td>
                                <td align="right"><strong><?php echo $absensi_stats['total'] ?? 0; ?></strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Hadir</span></td>
                                <td align="right"><?php echo $absensi_stats['hadir'] ?? 0; ?></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">Sakit</span></td>
                                <td align="right"><?php echo $absensi_stats['sakit'] ?? 0; ?></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Izin</span></td>
                                <td align="right"><?php echo $absensi_stats['izin'] ?? 0; ?></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">Alfa</span></td>
                                <td align="right"><?php echo $absensi_stats['alfa'] ?? 0; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Section -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-download"></i> Export Data
    </div>
    <div class="card-body">
        <div class="btn-group w-100" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="exportTableToCSV('karyawanTable', 'laporan_karyawan.csv')">
                <i class="bi bi-download"></i> Export Karyawan
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportTableToCSV('absensiTable', 'laporan_absensi.csv')">
                <i class="bi bi-download"></i> Export Absensi
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Laporan
            </button>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Report - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>