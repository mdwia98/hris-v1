<?php
/**
 * dashboard.php
 * Gabungan: Statistik Dashboard + Selfie & GPS Absensi
 * HRIS System
 */

require_once 'config/database.php';
require_once 'includes/session.php';

// pastikan user login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/header.php';

$page_title = 'Dashboard';

// ambil role & user id
$user_role = $_SESSION['user']['role'] ?? '';
$user_id   = $_SESSION['user']['id'] ?? '';

// jika role karyawan, ambil data karyawan terkait (menggunakan user_id)
$login_karyawan_id = 0;
$karyawan_nama = '';
if ($user_role === 'karyawan') {
    $stmt = $conn->prepare("SELECT id, nama FROM karyawan WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $karyawan_login = $res->fetch_assoc();
    if ($karyawan_login) {
        $login_karyawan_id = (int)$karyawan_login['id'];
        $karyawan_nama = $karyawan_login['nama'];
    }
    $stmt->close();
}

// Statistik (Admin / Manager only)
if ($user_role !== 'karyawan') {
    $total_karyawan = $conn->query("SELECT COUNT(*) AS count FROM karyawan")->fetch_assoc()['count'] ?? 0;
    $karyawan_aktif = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'karyawan' AND status = 'aktif'")->fetch_assoc()['count'] ?? 0;
    $total_departemen = $conn->query("SELECT COUNT(*) AS count FROM departemen")->fetch_assoc()['count'] ?? 0;
    $absensi_hari_ini = $conn->query("SELECT COUNT(*) AS count FROM absensi WHERE tanggal = CURDATE() AND status = 'Hadir'")->fetch_assoc()['count'] ?? 0;

    $recent_karyawan = $conn->query("SELECT * FROM karyawan ORDER BY created_at DESC LIMIT 5");

    $pending_cuti = $conn->query("
        SELECT c.*, k.nama, k.nip 
        FROM cuti c
        JOIN karyawan k ON c.karyawan_id = k.id
        WHERE c.status = 'Pending'
        ORDER BY c.created_at DESC LIMIT 5
    ");
}

// Query Absensi Hari Ini (untuk tabel recent_absensi)
// Jika role karyawan => hanya tampil untuk dirinya (menggunakan kolom jam_masuk/jam_keluar)
if ($user_role === 'karyawan' && $login_karyawan_id) {
    $stmt = $conn->prepare("
        SELECT a.*, k.nama, k.nip
        FROM absensi a
        JOIN karyawan k ON a.karyawan_id = k.id
        WHERE a.karyawan_id = ? AND a.tanggal = CURDATE()
        ORDER BY a.jam_masuk DESC LIMIT 10
    ");
    $stmt->bind_param("i", $login_karyawan_id);
    $stmt->execute();
    $recent_absensi = $stmt->get_result();
    $stmt->close();
} else {
    // admin/manager sees semua absensi hari ini
    $recent_absensi = $conn->query("
        SELECT a.*, k.nama, k.nip
        FROM absensi a
        JOIN karyawan k ON a.karyawan_id = k.id
        WHERE a.tanggal = CURDATE()
        ORDER BY a.jam_masuk DESC LIMIT 10
    ");
}

// Riwayat Absensi Bulan Ini (karyawan saja)
if ($user_role === 'karyawan' && $login_karyawan_id) {
    $stmt = $conn->prepare("
        SELECT tanggal, jam_masuk, jam_keluar, status
        FROM absensi
        WHERE karyawan_id = ?
        AND MONTH(tanggal) = MONTH(CURDATE())
        AND YEAR(tanggal) = YEAR(CURDATE())
        ORDER BY tanggal DESC
    ");
    $stmt->bind_param("i", $login_karyawan_id);
    $stmt->execute();
    $absensi_bulan_ini = $stmt->get_result();
    $stmt->close();
}

// Cek apakah karyawan sudah check-in hari ini (untuk menampilkan tombol Check-In/Check-Out)
$absen_today = null;
if ($user_role === 'karyawan' && $login_karyawan_id) {
    $stmt = $conn->prepare("SELECT * FROM absensi WHERE karyawan_id = ? AND DATE(tanggal) = CURDATE() LIMIT 1");
    $stmt->bind_param("i", $login_karyawan_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $absen_today = $res->fetch_assoc() ?: null;
    $stmt->close();
}
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Karyawan: Selfie + GPS Absensi -->
<?php if ($user_role !== 'superadmin'): ?>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="fw-bold mb-2">Absensi Hari Ini</h4>
                <p class="text-muted small mb-3">Pastikan GPS aktif dan izin kamera diperbolehkan</p>

                <?php if (!$absen_today) : ?>
                    <!-- BELUM CHECK-IN -->
                    <button class="btn btn-primary btn-lg px-4 rounded-pill" onclick="openAttendanceModal('checkin')">
                        🚀 Check-In
                    </button>
                <?php elseif ($absen_today && empty($absen_today['jam_keluar'])) : ?>
                    <!-- SUDAH CHECK-IN, BELUM CHECK-OUT -->
                    <button class="btn btn-danger btn-lg px-4 rounded-pill" onclick="openAttendanceModal('checkout')">
                        🔚 Check-Out
                    </button>

                    <p class="mt-3 text-success fw-semibold">
                        ✔ Sudah Check-In: <?= date('H:i', strtotime($absen_today['jam_masuk'])); ?>
                    </p>
                <?php else : ?>
                    <!-- SUDAH LENGKAP -->
                    <div class="alert alert-success rounded-pill mt-3">
                        ✔ Absensi hari ini sudah lengkap
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

<!-- Statistic Cards (Admin/Manager Only) -->
<?php if ($user_role != 'karyawan'): ?>
<div class="row mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <i class="bi bi-people" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= (int)$total_karyawan; ?></div>
            <div class="stat-label">Total Karyawan</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card yellow">
            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= (int)$karyawan_aktif; ?></div>
            <div class="stat-label">Karyawan Aktif</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <i class="bi bi-building" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= (int)$total_departemen; ?></div>
            <div class="stat-label">Departemen</div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card dark">
            <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
            <div class="stat-number"><?= (int)$absensi_hari_ini; ?></div>
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
                            <?php if ($recent_karyawan && $recent_karyawan->num_rows): ?>
                                <?php while ($row = $recent_karyawan->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nip']); ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['departemen']); ?></td>
                                    <td><?= htmlspecialchars($row['posisi']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                            <?php endif; ?>
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
                            <?php if ($recent_absensi && $recent_absensi->num_rows): ?>
                                <?php while ($row = $recent_absensi->fetch_assoc()): ?>
                                <tr>
                                    <?php if ($user_role != 'karyawan'): ?>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <?php endif; ?>
                                    <td><?= $row['jam_masuk'] ? htmlspecialchars($row['jam_masuk']) : '-'; ?></td>
                                    <td><?= $row['jam_keluar'] ? htmlspecialchars($row['jam_keluar']) : '-'; ?></td>
                                    <?php
                                        $status_color_map = [
                                            'Hadir' => 'success',
                                            'Sakit' => 'warning',
                                            'Izin'  => 'info',
                                            'Alfa'  => 'danger'
                                        ];
                                        $badge = $status_color_map[$row['status']] ?? 'secondary';
                                    ?>
                                    <td><span class="badge bg-<?= $badge; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="<?= $user_role != 'karyawan' ? 4 : 3; ?>" class="text-center">Tidak ada data absensi hari ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Pending Cuti (Admin/Manager Only) -->
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
                            <?php if ($pending_cuti && $pending_cuti->num_rows): ?>
                                <?php while ($row = $pending_cuti->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                                    <td><?= htmlspecialchars($row['jenis_cuti']); ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_selesai'])); ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status']); ?></span></td>
                                    <td>
                                        <a href="pages/cuti/detail.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Tidak ada permintaan cuti menunggu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- Riwayat Absensi Bulan Ini -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history"></i> Riwayat Absensi Bulan Ini</div>
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
                            <?php if (isset($absensi_bulan_ini) && $absensi_bulan_ini->num_rows): ?>
                                <?php while ($row = $absensi_bulan_ini->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?= $row['jam_masuk'] ? htmlspecialchars($row['jam_masuk']) : '-'; ?></td>
                                    <td><?= $row['jam_keluar'] ? htmlspecialchars($row['jam_keluar']) : '-'; ?></td>
                                    <?php $badge = $status_color_map[$row['status']] ?? 'secondary'; ?>
                                    <td><span class="badge bg-<?= $badge; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">Tidak ada riwayat absensi bulan ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================== MODAL SELFIE ===================== -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow-lg">

      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="modalTitle">Selfie Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">
        <video id="cameraStream" width="100%" autoplay class="rounded-4 shadow"></video>
        <canvas id="selfieCanvas" class="d-none"></canvas>

        <div class="mt-3">
            <button class="btn btn-dark rounded-pill px-4" onclick="takeSelfie()">
                📸 Ambil Foto
            </button>
        </div>

        <img id="selfiePreview" class="d-none rounded-4 shadow mt-3" width="100%">

        <form id="attendanceForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">
            <input type="hidden" name="selfie" id="selfie">
            <input type="hidden" name="mode" id="mode">

            <button id="submitBtn" type="submit"
                    class="btn btn-primary btn-lg rounded-pill mt-4 w-100 d-none">
                Simpan Absensi
            </button>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
// ====== buka modal ======
function openAttendanceModal(mode) {
    document.getElementById("mode").value = mode;

    if (mode === 'checkin') {
        document.getElementById("modalTitle").innerHTML = "📍 Selfie Check-In";
        document.getElementById("attendanceForm").action = "pages/absensi/absensi_checkin.php";
    } else {
        document.getElementById("modalTitle").innerHTML = "📍 Selfie Check-Out";
        document.getElementById("attendanceForm").action = "pages/absensi/absensi_checkout.php";
    }

    // minta posisi
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById("lat").value = pos.coords.latitude;
                document.getElementById("lng").value = pos.coords.longitude;
            },
            err => alert("GPS tidak aktif atau izin lokasi ditolak.")
        );
    } else {
        alert("Geolocation tidak didukung di browser ini.");
    }

    // nyalakan kamera
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
        .then(stream => {
            document.getElementById("cameraStream").srcObject = stream;
        })
        .catch(err => {
            alert("Kamera tidak bisa digunakan atau izin ditolak.");
        });
    } else {
        alert("Browser tidak mendukung kamera.");
    }

    new bootstrap.Modal(document.getElementById('attendanceModal')).show();
}


// ====== ambil selfie ======
function takeSelfie() {
    let video = document.getElementById("cameraStream");
    let canvas = document.getElementById("selfieCanvas");
    let preview = document.getElementById("selfiePreview");

    if (!video.videoWidth || !video.videoHeight) {
        alert("Kamera belum siap. Coba lagi sebentar.");
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video, 0, 0);

    let imageData = canvas.toDataURL("image/png");

    preview.src = imageData;
    preview.classList.remove("d-none");

    document.getElementById("selfie").value = imageData;
    document.getElementById("submitBtn").classList.remove("d-none");
}
</script>
<?php endif; ?>

<script>
    // judul halaman
    document.title = "Dashboard - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
