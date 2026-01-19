<?php
/**
 * Dashboard Page
 * HRIS System
 */
date_default_timezone_set('Asia/Jakarta');
require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Dashboard';

// ==== Ambil Role User ====
$user_role = $_SESSION['user']['role'] ?? '';
$user_id   = $_SESSION['user']['id'] ?? '';

// ==== Jika Role Karyawan, Ambil Data Karyawan ====
if ($user_role == 'karyawan') {
    $karyawan_login = $conn->query("SELECT id, nama FROM karyawan WHERE user_id = '$user_id'")->fetch_assoc();
    $login_karyawan_id = $karyawan_login['id'] ?? 0;
} else {
    // Admin & Manager tetap dapat check-in menggunakan tabel karyawan
    $karyawan_login = $conn->query("SELECT id, nama FROM karyawan WHERE user_id = '$user_id'")->fetch_assoc();
    $login_karyawan_id = $karyawan_login['id'] ?? 0;
}

// ==== Status Absensi Hari Ini (Untuk Tombol) ====
$today_absen = $conn->query("
    SELECT * FROM absensi 
    WHERE karyawan_id = '$login_karyawan_id' AND tanggal = CURDATE()
    LIMIT 1
")->fetch_assoc();

$already_checkin  = $today_absen && $today_absen['jam_masuk'];
$already_checkout = $today_absen && $today_absen['jam_keluar'];

// ==== Statistik Untuk Admin/Manager Saja ====
if ($user_role != 'karyawan') {
    $total_karyawan     = $conn->query("SELECT COUNT(*) AS count FROM karyawan")->fetch_assoc()['count'];
    $karyawan_aktif     = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'karyawan' AND status = 'aktif'")->fetch_assoc()['count'];
    $total_departemen   = $conn->query("SELECT COUNT(*) AS count FROM departemen")->fetch_assoc()['count'];
    $absensi_hari_ini   = $conn->query("SELECT COUNT(*) AS count FROM absensi WHERE tanggal = CURDATE() AND status = 'Hadir'")->fetch_assoc()['count'];

    $recent_karyawan = $conn->query("SELECT * FROM karyawan ORDER BY created_at DESC LIMIT 5");

    $pending_cuti = $conn->query("
        SELECT c.*, k.nama, k.nip 
        FROM cuti c
        JOIN karyawan k ON c.karyawan_id = k.id
        WHERE c.status = 'Pending'
        ORDER BY c.created_at DESC LIMIT 5
    ");
}

// ==== Query Absensi Hari Ini ====
if ($user_role == 'karyawan') {
    $recent_absensi = $conn->query("
        SELECT a.*, k.nama, k.nip 
        FROM absensi a
        JOIN karyawan k ON a.karyawan_id = k.id
        WHERE a.karyawan_id = '$login_karyawan_id' AND a.tanggal = CURDATE()
        ORDER BY a.jam_masuk DESC LIMIT 10
    ");
} else {
    $recent_absensi = $conn->query("
        SELECT a.*, k.nama, k.nip 
        FROM absensi a
        JOIN karyawan k ON a.karyawan_id = k.id
        WHERE a.tanggal = CURDATE()
        ORDER BY a.jam_masuk DESC LIMIT 10
    ");
}

// ==== Riwayat Absensi Bulan Ini (Karyawan Saja) ====
if ($user_role == 'karyawan') {
    $absensi_bulan_ini = $conn->query("
        SELECT tanggal, jam_masuk, jam_keluar, status
        FROM absensi
        WHERE karyawan_id = '$login_karyawan_id'
        AND MONTH(tanggal) = MONTH(CURDATE())
        AND YEAR(tanggal) = YEAR(CURDATE())
        ORDER BY tanggal DESC
    ");
}
?>

<!-- CUSTOM CSS TOMBOL ABSENSI -->
<style>
.checkin-box {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.check-btn {
    padding: 14px 40px;
    font-size: 1.3rem;
    border-radius: 50px;
    border: 0;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

.checkin {
    background: linear-gradient(135deg, #00c853, #009624);
}

.checkout {
    background: linear-gradient(135deg, #ff3d00, #dd2c00);
}

.check-btn:hover {
    transform: scale(1.05);
}

.status-info {
    margin-top: 10px;
    font-size: .95rem;
    opacity: .8;
}
</style>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
</div>

<!-- ======== TOMBOL CHECK-IN / CHECK-OUT UNTUK SEMUA USER ======== -->
<div class="checkin-box">

    <h4><i class="bi bi-fingerprint"></i> Absensi Kehadiran</h4>

    <?php if (!$already_checkin): ?>
        <form method="POST" action="pages/absensi/checkin.php">
            <input type="hidden" name="id_karyawan" value="<?= $login_karyawan_id ?>">
            <button class="check-btn checkin">
                <i class="bi bi-door-open"></i> Check-In
            </button>
        </form>

    <?php elseif ($already_checkin && !$already_checkout): ?>
        <form method="POST" action="pages/absensi/checkout.php">
            <input type="hidden" name="id_karyawan" value="<?= $login_karyawan_id ?>">
            <button class="check-btn checkout">
                <i class="bi bi-door-closed"></i> Check-Out
            </button>
        </form>

        <div class="status-info">
            Check-in pada: <strong><?= $today_absen['jam_masuk']; ?></strong>
        </div>

    <?php else: ?>
        <div class="alert alert-success mt-3">
            <i class="bi bi-check-circle"></i> Anda sudah menyelesaikan absensi hari ini.
        </div>
    <?php endif; ?>

</div>

<!-- Statistic Cards (Admin/Manager Only) -->
<?php if ($user_role != 'karyawan'): ?>
<div class="row mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <i class="bi bi-people" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= $total_karyawan; ?></div>
            <div class="stat-label">Total Karyawan</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card yellow">
            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= $karyawan_aktif; ?></div>
            <div class="stat-label">Karyawan Aktif</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <i class="bi bi-building" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= $total_departemen; ?></div>
            <div class="stat-label">Departemen</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card dark">
            <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= $absensi_hari_ini; ?></div>
            <div class="stat-label">Hadir Hari Ini</div>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="row">

    <!-- Karyawan Terbaru (Admin/Manager Only) -->
    <?php if ($user_role != 'karyawan'): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-people"></i> Karyawan Terbaru
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Posisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_karyawan->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nip']); ?></strong></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['departemen']); ?></td>
                                <td><?= htmlspecialchars($row['posisi']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Absensi Hari Ini -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i> Absensi Hari Ini
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <?php if ($user_role != 'karyawan'): ?><th>Nama</th><?php endif; ?>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_absensi->fetch_assoc()): ?>
                            <tr>
                                <?php if ($user_role != 'karyawan'): ?>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                <?php endif; ?>
                                <td><?= $row['jam_masuk'] ? htmlspecialchars($row['jam_masuk']) : '-'; ?></td>
                                <td><?= $row['jam_keluar'] ? htmlspecialchars($row['jam_keluar']) : '-'; ?></td>
                                <td><span class="badge bg-<?= ['Hadir'=>'success','Sakit'=>'warning','Izin'=>'info','Alfa'=>'danger'][$row['status']] ?? 'secondary'; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Pending Cuti -->
<?php if ($user_role != 'karyawan'): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-x"></i> Permintaan Cuti Menunggu Persetujuan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Jenis Cuti</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending_cuti->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']); ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_selesai'])); ?></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <a href="pages/cuti/detail.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($user_role == 'karyawan'): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Absensi Bulan Ini
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $absensi_bulan_ini->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td><?= $row['jam_masuk'] ? htmlspecialchars($row['jam_masuk']) : '-'; ?></td>
                                <td><?= $row['jam_keluar'] ? htmlspecialchars($row['jam_keluar']) : '-'; ?></td>
                                <td>
                                    <span class="badge bg-<?= ['Hadir'=>'success','Sakit'=>'warning','Izin'=>'info','Alfa'=>'danger'][$row['status']] ?? 'secondary'; ?>">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.title = "Dashboard - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
