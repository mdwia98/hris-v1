<?php
ob_start();

/**
 * Absensi - List Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';

$page_title = 'Manajemen Absensi';

// Get filter date (didefinisikan dulu sebelum dipakai)
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Ambil data user dari session
$user_role   = $_SESSION['user']['role'] ?? null;
$karyawan_id = $_SESSION['user']['karyawan_id'] ?? null;

// Kondisi filter awal
$where_filter = "WHERE DATE(a.tanggal) = '$filter_date'";

// Jika role karyawan, hanya tampilkan data karyawan tersebut
if ($user_role === 'karyawan' && $karyawan_id != null) {
    $where_filter .= " AND a.karyawan_id = '$karyawan_id'";
}

// Query data absensi
$result = $conn->query("
    SELECT a.*, k.nama, k.nip 
    FROM absensi a
    JOIN karyawan k ON a.karyawan_id = k.id
    $where_filter
    ORDER BY a.jam_masuk DESC
");

// === SUMMARY ===
$summary_filter = "WHERE DATE(tanggal) = '$filter_date'";

if ($user_role === 'karyawan' && $karyawan_id != null) {
    $summary_filter .= " AND karyawan_id = '$karyawan_id'";
}

$summary = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'Alfa' THEN 1 ELSE 0 END) as alfa,
        SUM(CASE WHEN status = 'Cuti' THEN 1 ELSE 0 END) as cuti
    FROM absensi
    $summary_filter
")->fetch_assoc();
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-calendar-check"></i> Manajemen Absensi</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Absensi</li>
        </ol>
    </nav>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card yellow">
            <div class="stat-number"><?php echo $summary['total'] ?? 0; ?></div>
            <div class="stat-label">Total Absensi</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $summary['hadir'] ?? 0; ?></div>
            <div class="stat-label">Hadir</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $summary['sakit'] ?? 0; ?></div>
            <div class="stat-label">Sakit</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card dark">
            <div class="stat-number"><?php echo $summary['alfa'] ?? 0; ?></div>
            <div class="stat-label">Alfa</div>
        </div>
    </div>
</div>

<!-- Filter & Action -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <?php if ($user_role != 'karyawan'): ?>
                    <a href="<?php echo BASE_URL; ?>pages/absensi/create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Input Absensi
                    </a>
                <?php endif; ?>

                <button class="btn btn-success" onclick="exportTableToCSV('absensiTable', 'absensi.csv')">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Absensi Tanggal <?php echo date('d M Y', strtotime($filter_date)); ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="absensiTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama Karyawan</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th>Keterangan</th>
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
                        <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                        <td><?php echo $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>
                        <td>
                            <?php
                            $status_class = [
                                'Hadir' => 'bg-success',
                                'Sakit' => 'bg-warning',
                                'Izin' => 'bg-info',
                                'Alfa' => 'bg-danger',
                                'Cuti' => 'bg-secondary'
                            ];
                            $class = $status_class[$row['status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                        <td>
                            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] != 'karyawan'): ?>
                            <a href="<?php echo BASE_URL; ?>pages/absensi/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                                </a>
                            <a href="<?php echo BASE_URL; ?>pages/absensi/delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" 
                               data-action="delete" 
                              data-message="Apakah Anda yakin ingin menghapus data absensi ini?">
                               <i class="bi bi-trash"></i> Hapus
                                </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                                <?php endif; ?>
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
    document.title = "Absensi - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>