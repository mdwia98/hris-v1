<?php
/**
 * Dashboard Page (with GPS + Selfie Attendance)
 * HRIS System
 */
require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Dashboard';

// Pastikan user login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Cek apakah sudah check-in hari ini
$today = date('Y-m-d');
$query = $conn->query("
    SELECT * FROM absensi 
    WHERE karyawan_id = '$user_id' AND DATE(jam_masuk) = '$today'
");
$absen_today = $query->fetch_assoc();
?>

<div class="container py-4">

    <h3 class="fw-bold mb-4">Dashboard</h3>

    <div class="row">

        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body text-center p-4">

                    <h4 class="fw-bold mb-3">Absensi Hari Ini</h4>
                    <p class="text-muted">Pastikan GPS aktif dan izin kamera diperbolehkan</p>

                    <?php if (!$absen_today) : ?>
                        <!-- BELUM CHECK-IN -->
                        <button class="btn btn-primary btn-lg px-4 rounded-pill shadow mt-3"
                                onclick="openAttendanceModal('checkin')">
                            🚀 Check-In
                        </button>

                    <?php elseif ($absen_today && empty($absen_today['check_out'])) : ?>
                        <!-- SUDAH CHECK-IN, BELUM CHECK-OUT -->
                        <button class="btn btn-danger btn-lg px-4 rounded-pill shadow mt-3"
                                onclick="openAttendanceModal('checkout')">
                            🔚 Check-Out
                        </button>

                        <p class="mt-3 text-success fw-semibold">
                            ✔ Sudah Check-In: <?= date('H:i', strtotime($absen_today['check_in'])) ?>
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

        <button class="btn btn-dark mt-3 rounded-pill px-4" onclick="takeSelfie()">
            📸 Ambil Foto
        </button>

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

    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById("lat").value = pos.coords.latitude;
            document.getElementById("lng").value = pos.coords.longitude;
        },
        err => alert("GPS tidak aktif! Izinkan lokasi.")
    );

    // nyalakan kamera
    navigator.mediaDevices.getUserMedia({
        video: { facingMode: "user" }
    })
    .then(stream => {
        document.getElementById("cameraStream").srcObject = stream;
    })
    .catch(err => {
        alert("Kamera tidak bisa digunakan!");
    });

    new bootstrap.Modal(document.getElementById('attendanceModal')).show();
}


// ====== ambil selfie ======
function takeSelfie() {
    let video = document.getElementById("cameraStream");
    let canvas = document.getElementById("selfieCanvas");
    let preview = document.getElementById("selfiePreview");

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
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Dashboard - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
