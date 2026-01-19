<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
requireAdmin();
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;

$client = $conn->query("SELECT * FROM clients WHERE id = $id")->fetch_assoc();

if (!$client) {
    die("Client tidak ditemukan.");
}

$page_title = "Edit Client";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_client'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $perusahaan = $_POST['perusahaan'];

    $stmt = $conn->prepare("UPDATE clients SET nama_client=?, email=?, telepon=?, perusahaan=? WHERE id=?");
    $stmt->bind_param("ssssi", $nama, $email, $telepon, $perusahaan, $id);

    if ($stmt->execute()) {
        $success = "Client berhasil diperbarui!";
        addLog($_SESSION['user']['id'], "Update Client", "Mengubah data client: $nama_client");

    } else {
        $error = "Gagal memperbarui client.";
    }
}
?>

<div class="content-header">
    <h1><i class="bi bi-pencil"></i> Edit Client</h1>
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
                <input type="text" name="nama_client" class="form-control" value="<?php echo $client['nama_client']; ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $client['email']; ?>">
            </div>

            <div class="mb-3">
                <label>Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?php echo $client['telepon']; ?>">
            </div>

            <div class="mb-3">
                <label>Perusahaan</label>
                <input type="text" name="perusahaan" class="form-control" value="<?php echo $client['perusahaan']; ?>">
            </div>

            <button class="btn btn-success" type="submit">
                <i class="bi bi-save"></i> Update
            </button>

            <a href="<?php echo BASE_URL; ?>pages/client/index.php" class="btn btn-secondary">Kembali</a>

        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
