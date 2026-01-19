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

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

<style>
:root {
    --BASE_URL: '<?php echo BASE_URL; ?>';

    /* LIGHT MODE */
    --bg-main: #f4f6f9;
    --bg-sidebar: #1e1e2d;
    --bg-navbar: #ffffff;
    --text-main: #000;
    --text-light: #fff;
    --text-muted: #ccc;
    --sidebar-hover: #007bff;
}

body.dark-mode {
    /* DARK MODE */
    --bg-main: #181818;
    --bg-sidebar: #11111a;
    --bg-navbar: #1f1f1f;
    --text-main: #e6e6e6;
    --text-light: #fff;
    --text-muted: #aaa;
    --sidebar-hover: #0d6efd;
}

/* GLOBAL */
body {
    background-color: var(--bg-main);
    color: var(--text-main);
    font-family: "Poppins", sans-serif;
    overflow-x: hidden;
    transition: background .3s, color .3s;
}

/* ================= SIDEBAR ================= */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 240px;
    background: var(--bg-sidebar);
    color: var(--text-light);
    transition: all 0.3s ease;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
}

.sidebar.hidden {
    left: -240px;
}

@media (max-width: 992px) {
    .sidebar { left: -240px; }
    .sidebar.show { left: 0 !important; }
}

.sidebar .sidebar-title {
    font-size: 1rem;
    font-weight: 600;
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    text-transform: uppercase;
}

.sidebar .nav-link {
    color: var(--text-muted);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: 0.2s;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: var(--sidebar-hover);
    color: #fff !important;
}

/* ================= TOP NAV ================= */
.navbar-custom {
    background: var(--bg-navbar) !important;
    transition: background .3s;
}

.navbar-custom .nav-link,
.navbar-custom .navbar-brand {
    color: var(--text-main) !important;
}

#menu-toggle {
    border: none;
    background: transparent;
    font-size: 1.4rem;
    color: #007bff;
}
#menu-toggle:hover { transform: scale(1.1); }

/* Dark Mode Switch */
.theme-switch {
    cursor: pointer;
    user-select: none;
}

/* ================= MAIN CONTENT ================= */
.main-content {
    margin-left: 240px;
    transition: all 0.3s ease;
    padding: 20px;
}

.main-content.expanded { margin-left: 0 !important; }

@media (max-width: 992px) {
    .main-content { margin-left: 0 !important; }
}

/* ================= LOADER ================= */
#page-loader {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255,255,255,0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(6px);
    z-index: 9999;
    transition: opacity 0.4s ease;
}
#page-loader.hidden {
    opacity: 0; visibility: hidden;
}

.loader-spinner {
    width: 64px; height: 64px;
    border: 5px solid #ddd;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Ensure smooth transitions */
.sidebar {
    transition: all .3s ease-in-out;
}
.main-content {
    transition: all .3s ease-in-out;
}

#menu-toggle i {
    transition: transform .2s ease;
}
#menu-toggle:hover i {
    transform: rotate(90deg);
}
</style>
</head>

<body id="body-mode">

<!-- LOADER -->
<div id="page-loader">
    <div class="text-center">
        <div class="loader-spinner"></div>
        <div class="loader-text">Memuat halaman...</div>
    </div>
</div>

<!-- ================= TOP NAVBAR ================= -->
<nav class="navbar navbar-custom shadow-sm sticky-top px-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center">
            <button id="menu-toggle"><i id="toggle-icon" class="bi bi-list"></i></button>
            <a class="navbar-brand ms-2 fw-semibold" href="<?php echo BASE_URL; ?>dashboard.php">
                <i class="bi bi-diagram-3"></i> HRIS BSDM
            </a>
        </div>

        <span id="waktu" class="me-4 text-secondary fw-semibold"></span>

        <div class="d-flex align-items-center">

            <!-- Dark/Light Toggle -->
            <div class="form-check form-switch me-3 theme-switch">
                <input class="form-check-input" type="checkbox" id="themeToggle">
                <label class="form-check-label" id="themeLabel">Dark Mode</label>
            </div>

            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user_name); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php"><i class="bi bi-person"></i> Profil</a></li>
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>settings.php"><i class="bi bi-gear"></i> Pengaturan</a></li>
                    <li><hr></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- ================= LAYOUT WRAPPER ================= -->
<div class="container-fluid">
<div class="row">

<!-- ================= SIDEBAR ================= -->
<nav id="sidebar" class="sidebar col-lg-2">
    <div class="sidebar-title"><i class="bi bi-list-ul"></i> Menu</div>

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
            <li><a class="nav-link" href="<?php echo BASE_URL; ?>admin/user_permission.php"><i class="bi bi-shield-lock"></i> Manajemen Izin User</a></li>
        <?php endif; ?>

        <?php if ($user_role == 'admin'): ?>
            <li><a class="nav-link" href="<?php echo BASE_URL; ?>backend/settings/database_structure.php"><i class="bi bi-database"></i> DB Manager</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content col-lg-10" id="main-content">

<!-- CLOCK -->
<script>
function tampilkanWaktu() {
    var waktu = new Date();
    var hariArray = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
    var bulanArray = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

    var teks =
        hariArray[waktu.getDay()] + ", " +
        waktu.getDate() + " " + bulanArray[waktu.getMonth()] + " " +
        waktu.getFullYear() + " - " +
        waktu.getHours().toString().padStart(2, '0') + ":" +
        waktu.getMinutes().toString().padStart(2, '0') + ":" +
        waktu.getSeconds().toString().padStart(2, '0');

    document.getElementById("waktu").innerHTML = teks;
    setTimeout(tampilkanWaktu, 1000);
}
tampilkanWaktu();
</script>

<!-- SIDEBAR TOGGLE -->
<script>
const toggleButton = document.getElementById('menu-toggle');
const toggleIcon   = document.getElementById('toggle-icon');
const sidebar      = document.getElementById('sidebar');
const content      = document.getElementById('main-content');

function updateIcon(isOpen) {
    if (isOpen) {
        toggleIcon.classList.remove('bi-list');
        toggleIcon.classList.add('bi-x-lg');
    } else {
        toggleIcon.classList.remove('bi-x-lg');
        toggleIcon.classList.add('bi-list');
    }
}

toggleButton.addEventListener('click', function () {
    const isMobile = window.innerWidth <= 992;

    if (isMobile) {
        // Drawer Mode
        sidebar.classList.toggle('show');
        updateIcon(sidebar.classList.contains('show'));
    } else {
        // Desktop Sidebar Hide Mode
        sidebar.classList.toggle('hidden');
        content.classList.toggle('expanded');
        updateIcon(!sidebar.classList.contains('hidden'));
    }
});

// Auto adjust when resizing window
window.addEventListener('resize', function () {
    const isMobile = window.innerWidth <= 992;

    if (isMobile) {
        sidebar.classList.remove('hidden');
        content.classList.remove('expanded');
        updateIcon(sidebar.classList.contains('show'));
    } else {
        sidebar.classList.remove('show');
        updateIcon(!sidebar.classList.contains('hidden'));
    }
});
</script>

<!-- DARK/LIGHT MODE -->
<script>
const bodyMode = document.getElementById('body-mode');
const toggle = document.getElementById('themeToggle');
const label  = document.getElementById('themeLabel');

// Check saved preference
const saved = localStorage.getItem('theme-mode');
if (saved === 'dark') {
    bodyMode.classList.add('dark-mode');
    toggle.checked = true;
    label.innerText = "Light Mode";
}

// Toggle
toggle.addEventListener('change', () => {
    if (toggle.checked) {
        bodyMode.classList.add('dark-mode');
        localStorage.setItem('theme-mode', 'dark');
        label.innerText = "Light Mode";
    } else {
        bodyMode.classList.remove('dark-mode');
        localStorage.setItem('theme-mode', 'light');
        label.innerText = "Dark Mode";
    }
});
</script>
