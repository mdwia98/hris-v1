<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
requireAdmin();
require_once '../../includes/header.php';

$page_title = 'Tambah Client';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_client']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $perusahaan = trim($_POST['perusahaan']);

    $stmt = $conn->prepare("INSERT INTO clients (nama_client, email, telepon, perusahaan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $telepon, $perusahaan);

    if ($stmt->execute()) {
        $success = "Client berhasil ditambahkan!";
    } else {
        $error = "Gagal menambah client.";
    }
    addLog($_SESSION['user']['id'], "Create Client", "Membuat client baru: $nama_client");
}
?>

<div class="content-header">
    <h1><i class="bi bi-plus-circle"></i> Tambah Client</h1>
</div>

<div class="card">
    <div class="card-body">

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label>Nama Client</label>
                <input type="text" name="nama_client" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="mb-3">
                <label>Telepon</label>
                <input type="text" name="telepon" class="form-control">
            </div>

            <div class="mb-3">
                <label>Perusahaan</label>
                <input type="text" name="perusahaan" class="form-control">
            </div>

            <button class="btn btn-success" type="submit">
                <i class="bi bi-save"></i> Simpan
            </button>

            <a href="<?php echo BASE_URL; ?>pages/client/index.php" class="btn btn-secondary">Kembali</a>

        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
