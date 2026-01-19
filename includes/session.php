<?php
/**
 * HRIS - Session Management with Auto Timeout
 */

// Pastikan tidak ada output sebelum session_start
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// =============================
// Konfigurasi Session Timeout
// =============================
define('SESSION_TIMEOUT', 900); // 15 menit

// Cek login
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// =============================
// Auto Timeout
// =============================
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed = time() - $_SESSION['LAST_ACTIVITY'];

    if ($elapsed > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['timeout_message'] = 'Sesi Anda berakhir karena tidak ada aktivitas selama 15 menit.';
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

$_SESSION['LAST_ACTIVITY'] = time();

// =============================
// Data User
// =============================
$user_id    = $_SESSION['user']['id']   ?? null;
$user_name  = $_SESSION['user']['name'] ?? 'Pengguna';
$user_role  = $_SESSION['user']['role'] ?? 'karyawan';
$user_email = $_SESSION['user']['email'] ?? '';

// =============================
// AJAX Extend Session
// =============================
if (isset($_GET['action']) && $_GET['action'] === 'extend') {
    $_SESSION['LAST_ACTIVITY'] = time();
    echo json_encode(['status' => 'extended', 'time' => date('H:i:s')]);
    exit();
}

// =============================
// Access Control
// =============================
function requireAdmin() {
    global $user_role;
    if ($user_role !== 'admin') {
        header('Location: ' . BASE_URL . 'unauthorized.php');
        exit();
    }
}

function requireManagerOrAdmin() {
    global $user_role;
    if (!in_array($user_role, ['admin', 'manajer'])) {
        header('Location: ' . BASE_URL . 'unauthorized.php');
        exit();
    }
}

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// =============================
// Flush output
// =============================
ob_end_flush();
