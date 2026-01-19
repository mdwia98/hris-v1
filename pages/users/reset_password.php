<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

// Hanya admin & manajer boleh reset password
if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'manajer') {
    die("Akses ditolak");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);

    // Password default
    $new_password = password_hash("bsdm2025", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Password berhasil direset menjadi bsdm2025.";
    } else {
        $_SESSION['error'] = "Gagal mereset password!";
    }

    header("Location: index.php");
    exit;
}
?>
