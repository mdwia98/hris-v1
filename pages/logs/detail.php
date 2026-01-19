<?php
ob_start();
session_start();
/**
 * Logs - Detail Log View
 */
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM hris_logs WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="content-header">
    <h1><i class="bi bi-file-earmark-text"></i> Detail Log</h1>
</div>

<div class="card p-3">

    <?php if ($log) { ?>
        <table class="table table-bordered">
            <tr><th>ID</th><td><?= $log['id'] ?></td></tr>
            <tr><th>Tanggal</th><td><?= $log['created_at'] ?></td></tr>
            <tr><th>User</th><td><?= $log['username'] ?> (ID: <?= $log['user_id'] ?>)</td></tr>
            <tr><th>Module</th><td><?= $log['module'] ?></td></tr>
            <tr><th>Action</th><td><?= $log['action'] ?></td></tr>
            <tr><th>Deskripsi</th><td><?= nl2br($log['description']) ?></td></tr>
            <tr><th>IP Address</th><td><?= $log['ip'] ?></td></tr>
            <tr><th>User Agent</th><td><?= $log['user_agent'] ?></td></tr>
        </table>
    <?php } else { ?>
        <div class="alert alert-danger">Log tidak ditemukan.</div>
    <?php } ?>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Detail Log - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
