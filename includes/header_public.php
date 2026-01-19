<?php
$user_role = $_SESSION['user']['role'] ?? 'role';
$user_name = $_SESSION['user']['name'] ?? 'username';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : '' ; ?> - HRIS BSDM</title>
    <link rel="icon" type="image/png" href="assets/images/LOGO BSDM 2.jpeg"/>
    

    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- jQuery WAJIB ADA -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <style>
        :root {
            --BASE_URL: '<?php echo BASE_URL; ?>';
        }

        /* --- Sidebar Modern Style --- */
        body {
            background-color: #f4f6f9;
            font-family: "Poppins", sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 240px;
            background: #1e1e2d;
            color: #fff;
            transition: all 0.3s ease;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }

        .sidebar {
            left: -240px;
        }

        .sidebar .sidebar-title {
            font-size: 1rem;
            font-weight: 600;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar .nav-link {
            color: #ccc;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #007bff;
            color: #fff;
        }

        #menu-toggle {
            border: none;
            background: transparent;
            color: #007bff;
            font-size: 1.4rem;
            transition: all 0.2s;
        }

        #menu-toggle:hover {
            color: #0056b3;
            transform: scale(1.1);
        }

        .main-content {
            margin-left: 0px;
            transition: all 0.3s ease;
            padding: 20px;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        @media (max-width: 992px) {
            .sidebar {
                left: -240px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }

        /* Loading Screen */
        #page-loader {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        #page-loader.hidden { opacity: 0; visibility: hidden; }

        .loader-spinner {
            width: 64px; height: 64px;
            border: 5px solid #ddd;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loader-text {
            font-size: 1.1rem;
            color: #007bff;
            margin-top: 10px;
            font-weight: 500;
            animation: fadeIn 1.2s ease-in-out infinite alternate;
        }

        @keyframes fadeIn { from { opacity: 0.4; } to { opacity: 1; } }
    </style>
</head>

<body>
<!-- Loader -->
<div id="page-loader">
    <div class="text-center">
        <div class="loader-spinner"></div>
        <div class="loader-text">Memuat halaman...</div>
    </div>
</div>

<!-- Top Navbar -->
<nav class="navbar navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center">
            <button id="menu-toggle"><i class="bi bi-list"></i></button>
            <a class="navbar-brand ms-2 fw-semibold"class="bi bi-diagram-3"></i> HRIS BSDM
            </a>
        </div>

        <!-- JAM REALTIME -->
        <span id="waktu" class="me-4 text-secondary fw-semibold"></span>
        <!-- END -->

        <div class="dropdown">
            <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user_name); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php"><i class="bi bi-person"></i> Profil</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>settings.php"><i class="bi bi-gear"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-md-block sidebar">
            <div class="sidebar-title">
                <i class="bi bi-list-ul"></i> Menu
            </div>
            <ul class="nav flex-column">

                <li><a class="nav-link" href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                <?php if ($user_role == 'admin'): ?>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/client/index.php"><i class="bi bi-buildings"></i> Manajemen Klien</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/departemen/index.php"><i class="bi bi-building"></i> Manajemen Departemen</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/users/index.php"><i class="bi bi-shield-check"></i> Manajemen User</a></li>
                <?php endif; ?>

                <?php if ($user_role == 'admin' || $user_role == 'manajer'): ?>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/karyawan/index.php"><i class="bi bi-people"></i> Manajemen Karyawan</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/gaji/index.php"><i class="bi bi-cash-coin"></i> Manajemen Payroll</a></li>
                <?php endif; ?>

                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/absensi/index.php"><i class="bi bi-calendar-check"></i> Absensi</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/cuti/index.php"><i class="bi bi-calendar-x"></i> Cuti</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/gaji/slip.php"><i class="bi bi-file-earmark-pdf"></i> Slip Gaji</a></li>

                <?php if ($user_role == 'admin' || $user_role == 'manajer'): ?>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/performa/index.php"><i class="bi bi-graph-up"></i> Performa</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/laporan/index.php"><i class="bi bi-file-earmark-text"></i> Laporan</a></li>
                <li><a class="nav-link" href="<?php echo BASE_URL; ?>pages/logs/index.php"><i class="bi bi-clock-history"></i> Riwayat Aktivitas</a></li>
                <?php endif; ?>

            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="main-content">


<!-- ===================== SCRIPT REALTIME CLOCK ===================== -->
<script>
function tampilkanWaktu() {
    var waktu = new Date();
    var hari = waktu.getDay();
    var tanggal = waktu.getDate();
    var bulan = waktu.getMonth();
    var tahun = waktu.getFullYear();
    var jam = waktu.getHours();
    var menit = waktu.getMinutes();
    var detik = waktu.getSeconds();

    var hariArray = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    var bulanArray = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    document.getElementById("waktu").innerHTML =
        hariArray[hari] + ", " + tanggal + " " + bulanArray[bulan] + " " + tahun +
        " - " +
        String(jam).padStart(2, '0') + ":" +
        String(menit).padStart(2, '0') + ":" +
        String(detik).padStart(2, '0');

    setTimeout(tampilkanWaktu, 1000);
}
tampilkanWaktu();
</script>
<!-- ===================== END CLOCK SCRIPT ===================== -->

<script>
    // Hide loader when page is fully loaded
    window.addEventListener('load', function() {
        const loader = document.getElementById('page-loader');
        loader.classList.add('hidden');
    });

    // Toggle sidebar
    document.getElementById('menu-toggle').addEventListener('click', function() {
        const mainContent = document.getElementById('main-content');
        const sidebar = document.getElementById('sidebar');
        mainContent.classList.toggle('expanded');
        sidebar.classList.toggle('hidden');
        
    });
</script>