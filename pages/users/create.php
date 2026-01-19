<?php
ob_start();
session_start();
/**
 * Tambah Pengguna Baru
 */
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Tambah Pengguna';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $username, $email, $password, $role, $status);

    if ($stmt->execute()) {
        $success = "Pengguna berhasil ditambahkan!";
    } else {
        $error = "Gagal menambah pengguna. Coba lagi.";
    }
    $stmt->close();
    addLog($_SESSION['user']['id'], "Create User", "Membuat user baru: $username");
}
?>

<div class="content-header">
    <h1><i class="bi bi-person-plus"></i> Tambah Pengguna</h1>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input name="email" type="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input name="password" type="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="admin">Admin</option>
                    <option value="manajer">Manajer</option>
                    <option value="karyawan">Karyawan</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Tambah User - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
