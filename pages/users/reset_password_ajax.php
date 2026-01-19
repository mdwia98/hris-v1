<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';

header('Content-Type: application/json');

addLog($_SESSION['user']['id'], "Reset Password", "Reset password user: $username menjadi default");

// Hanya admin & manajer yang boleh
if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'manajer') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$id = intval($_POST['id']);
$new_password = password_hash("bsdm2025", PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_password, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password berhasil direset menjadi bsdm2025']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mereset password']);
}
