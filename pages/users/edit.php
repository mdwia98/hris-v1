<?php
ob_start();
session_start();
/**
 * Edit Pengguna
 */
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Edit Pengguna';
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Pengguna tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $username, $email, $role, $status, $id);
    $stmt->execute();
    $stmt->close();
    addLog($_SESSION['user']['id'], "Update User", "Mengubah data user: $username");

    header("Location: index.php");
    exit;
}
?>

<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Pengguna</h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input name="email" type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="karyawan" <?php if ($user['role'] == 'karyawan') echo 'selected'; ?>>Karyawan</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="aktif" <?php if ($user['status'] == 'aktif') echo 'selected'; ?>>Aktif</option>
                    <option value="nonaktif" <?php if ($user['status'] == 'nonaktif') echo 'selected'; ?>>Nonaktif</option>
                </select>
            </div>
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit User - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>