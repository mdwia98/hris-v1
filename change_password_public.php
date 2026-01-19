<?php
/**
 * Change Password Page
 */

session_start();
require_once 'config/database.php';
require_once 'includes/header_public.php';

$page_title = 'Ubah Password';

$reset_mode = isset($_GET['reset']) && isset($_SESSION['reset_user']);

if ($reset_mode) {
    $user_id = $_SESSION['reset_user']['id'];
}else {

$user_id = $_SESSION['user']['id'] ?? null;
}

$success = '';
$error = '';

$reset_mode = isset($_GET['reset']) && isset($_SESSION['reset_user']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($new) || empty($confirm)) {
        $error = 'Semua field harus diisi.';
    } elseif ($new !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Ambil password lama dari DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed);
        $stmt->fetch();
        $stmt->close();

            $new_hashed = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $new_hashed, $user_id);
            if ($stmt->execute()) {
                $success = 'Password berhasil diubah.';
            } else {
                $error = 'Gagal memperbarui password: ' . $conn->error;
            }
            $stmt->close();
        }
    }
?>

<div class="content-header">
    <h1><i class="bi bi-key"></i> Ubah Password</h1>
</div>
<p class="running-text">Pastikan anda mengingat password yang akan anda buat.</p>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php elseif ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
    
    <div class="mb-3">
    <label class="form-label">Password Baru</label>
    <div class="input-group">
        <input type="password" name="new_password" class="form-control" id="new_password" required>
        <span class="input-group-text password-toggle" data-target="new_password">
            <i class="bi bi-eye-slash"></i>
        </span>
    </div>
    <small id="password-strength-text" class="mt-1"></small>
    <div class="progress mt-1" style="height: 6px;">
        <div id="password-strength-bar" class="progress-bar" style="width:0%"></div>
    </div>
</div>

    <div class="mb-3">
        <label class="form-label">Konfirmasi Password Baru</label>
        <div class="input-group">
            <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
            <span class="input-group-text password-toggle" data-target="confirm_password">
                <i class="bi bi-eye-slash"></i>
            </span>
        </div>
        <small id="confirm-text" class="mt-1"></small> 
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="btn-save" target="change_password" disabled>
            <i class="bi bi-check-circle"></i> Simpan
        </button>
        <a href="login.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Login Kembali</a>
    </div>
        </form>
    </div>
</div>

<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Ubah Password - HRIS BSDM";
</script>

<?php require_once 'includes/footer_public.php'; ?>
