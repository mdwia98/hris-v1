<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

header('Content-Type: application/json');

// Pastikan login
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Role check
$role = $_SESSION['user']['role'];
if (!in_array($role, ['admin', 'manajer'])) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak punya izin']);
    exit();
}

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$user_id = $_SESSION['user']['id'];

if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
    exit();
}

$new_status = ($action === 'approve') ? 'Disetujui' : 'Ditolak';


// 🔍 Cek apakah data cuti benar-benar ada
$cek = $conn->prepare("SELECT id, status FROM cuti WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$res = $cek->get_result()->fetch_assoc();

if (!$res) {
    echo json_encode(['status' => 'error', 'message' => 'Data cuti tidak ditemukan']);
    exit();
}

// 🔍 Cegah approve/reject dua kali
if ($res['status'] !== 'Pending') {
    echo json_encode(['status' => 'error', 'message' => 'Cuti ini sudah diproses sebelumnya']);
    exit();
}


// 🔄 Update status cuti
$update = $conn->prepare("
    UPDATE cuti
    SET status = ?, disetujui_oleh = ?, tanggal_disetujui = NOW()
    WHERE id = ?
");
$update->bind_param("sii", $new_status, $user_id, $id);
$update->execute();

if ($update->affected_rows <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data cuti']);
    exit();
}


// 👤 Ambil nama approver
$get = $conn->prepare("
    SELECT k.nama 
    FROM users u
    LEFT JOIN karyawan k ON k.id = u.karyawan_id
    WHERE u.id = ?
");
$get->bind_param("i", $user_id);
$get->execute();
$ap = $get->get_result()->fetch_assoc();

$approver_name = $ap['nama'] ?? 'Tidak diketahui';
$waktu = date('d M Y H:i');


// 🟢 Kembalikan response JSON
echo json_encode([
    'status' => 'success',
    'message' => ($new_status === 'Disetujui' ? 'Cuti berhasil disetujui' : 'Cuti berhasil ditolak'),
    'new_status' => $new_status,
    'approver' => $approver_name,
    'tanggal_disetujui' => $waktu
]);
exit();
?>
