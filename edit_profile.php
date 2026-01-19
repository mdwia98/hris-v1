<?php
/**
 * Edit Profile Page
 */
require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Edit Profil';

// Pastikan user login
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    die('User tidak ditemukan dalam session.');
}

// Ambil data pengguna
$stmt = $conn->prepare("
    SELECT u.*, k.* 
    FROM users u 
    LEFT JOIN karyawan k ON u.id = k.user_id 
    WHERE u.id = ?
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$success = '';
$error = '';

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');

    if (empty($nama) || empty($email)) {
        $error = 'Nama dan Email wajib diisi.';
    } else {
        // Update tabel users
        $stmt1 = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt1->bind_param('si', $email, $user_id);
        $stmt1->execute();
        $stmt1->close();

        // Update tabel karyawan
        $stmt2 = $conn->prepare("
            UPDATE karyawan 
            SET nama = ?, telepon = ?, alamat = ?, tanggal_lahir = ?, jenis_kelamin = ? 
            WHERE user_id = ?
        ");
        $stmt2->bind_param('sssssi', $nama, $telepon, $alamat, $tanggal_lahir, $jenis_kelamin, $user_id);
        if ($stmt2->execute()) {
            $success = 'Profil berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui profil: ' . $conn->error;
        }
        $stmt2->close();
    }
}
?>

<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Profil</h1>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php elseif ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama'] ?? ''); ?>" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="telepon" value="<?php echo htmlspecialchars($user['telepon'] ?? ''); ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($user['tanggal_lahir'] ?? ''); ?>" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" <?php if (($user['jenis_kelamin'] ?? '') == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php if (($user['jenis_kelamin'] ?? '') == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
                <a href="profile.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Edit Profil - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php'; ?>
