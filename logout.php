<?php
/**
 * Logout Page with detailed logging and re-login button
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // 0 di production, 1 kalau mau debug cepat

session_start();

// include DB - pastikan path ini benar dan file meng-set $conn (mysqli)
require_once 'config/database.php';

// helper untuk dapatkan IP client
function get_client_ip() {
    $keys = ['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ips = explode(',', $_SERVER[$k]);
            return trim($ips[0]);
        }
    }
    return 'UNKNOWN';
}

// Ambil data user sebelum dihapus session
$user = $_SESSION['user'] ?? null;

$username = $user['username'] ?? null;
$user_id  = $user['id'] ?? null;
$role     = $user['role'] ?? null;

$aksi = 'Logout';
$ip = get_client_ip();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$halaman = $_SERVER['REQUEST_URI'] ?? 'logout.php';

$keterangan = "User " . ($username ?? 'unknown') . " (role: " . ($role ?? '-') . ") melakukan logout.";

// Pastikan koneksi DB tersedia ($conn)
$logged_to_db = false;
if (isset($conn) && $user_id) {
    // siapkan statement
    $sql = "INSERT INTO logs_activity (user_id, username, aksi, keterangan, ip, user_agent, halaman) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("issssss", $user_id, $username, $aksi, $keterangan, $ip, $user_agent, $halaman);
        if ($stmt->execute()) {
            $logged_to_db = true;
        } else {
            // catat error ke file agar bisa dicek
            $err = date('Y-m-d H:i:s') . " - DB execute error: " . $stmt->error . " | SQL: $sql | user_id:$user_id | username:$username\n";
            file_put_contents(__DIR__ . '/logs/db_errors.log', $err, FILE_APPEND);
        }
        $stmt->close();
    } else {
        $err = date('Y-m-d H:i:s') . " - DB prepare error: " . $conn->error . " | SQL: $sql\n";
        file_put_contents(__DIR__ . '/logs/db_errors.log', $err, FILE_APPEND);
    }
} else {
    // jika $conn tidak ada atau user tidak tersedia, simpan log ke file lokal
    $note = date('Y-m-d H:i:s') . " - Could not log to DB. conn_exists:" . (isset($conn)?'1':'0') . " | user_id:" . ($user_id ?? 'null') . " | username:" . ($username ?? 'null') . " | ip:$ip | ua:".substr($user_agent,0,120) . "\n";
    @file_put_contents(__DIR__ . '/logs/logout_fallback.log', $note, FILE_APPEND);
}

// Hancurkan session (setelah pencatatan)
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Logout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (sesuaikan versi) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="text-center bg-white shadow p-4 rounded" style="min-width:320px;">
        <h4 class="text-success"><i class="bi bi-check-circle"></i> Berhasil Logout</h4>
        <p class="text-muted">Terima kasih, <strong><?php echo htmlspecialchars($username ?? 'Pengguna'); ?></strong>.</p>

        <?php if ($logged_to_db): ?>
            <div class="small text-success mb-2">Detail log telah tersimpan.</div>
        <?php else: ?>
            <div class="small text-warning mb-2">Detail log tidak tersimpan ke DB. Cek file logs/db_errors.log atau logs/logout_fallback.log</div>
        <?php endif; ?>

        <a href="login.php" class="btn btn-primary mt-2"><i class="bi bi-box-arrow-in-right"></i> Login Kembali</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
