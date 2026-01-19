<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak diterima']);
    exit;
}

$id = intval($_POST['id']);

// Cek apakah data ada
$check = $conn->query("SELECT id FROM karyawan WHERE id=$id");
if ($check->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    exit;
}

$delete = $conn->query("DELETE FROM karyawan WHERE id=$id");

if ($delete) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data']);
}
?>
