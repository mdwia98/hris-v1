<?php
/**
 * Validate Forgot Password (Lupa Password)
 */

session_start();
require_once 'config/database.php';

$page_title = 'Validasi Lupa Password';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik    = $_POST['nik'] ?? '';
    $email  = $_POST['email'] ?? '';
    $company = $_POST['company'] ?? '';
    $nip    = $_POST['nip'] ?? '';

    if (empty($nik) || empty($email) || empty($company) || empty($nip)) {
        $error = "Semua field wajib diisi.";
    } else {
        // Cek pada tabel karyawan
        $stmt = $conn->prepare("
            SELECT id, user_id, nama, email, perusahaan 
            FROM karyawan 
            WHERE nik_ktp = ? AND email = ? AND perusahaan = ? AND nip = ?
            LIMIT 1
        ");
        $stmt->bind_param("ssss", $nik, $email, $company, $nip);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Simpan session reset
            $_SESSION['reset_user'] = [
                'id' => $row['user_id'],       // ID dari tabel users
                'nama' => $row['nama'],
                'email' => $row['email']
            ];

            header("Location: change_password_public.php?reset=" . $row['user_id'] . "&nama=" . urlencode($row['nama']));
            exit();
        } else {
            $error = "Data tidak ditemukan atau tidak cocok.";
        }
    }
}
?>

<?php require_once 'includes/header_public.php'; ?>

<div class="content-header text-center">
    <h1><i class="bi bi-shield-lock"></i> Lupa Password</h1>
    <p class="text-muted">Masukkan data identitas Anda untuk memverifikasi akun.</p>
</div>

<?php if ($error): ?>
<div class="alert alert-danger text-center">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="card shadow-sm mx-auto" style="max-width: 450px;">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">NIK KTP</label>
                <input type="text" name="nik" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Perusahaan</label>
                <input type="text" name="company" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">NIP</label>
                <input type="text" name="nip" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check-circle"></i> Verifikasi
            </button>

            <a href="login.php" class="btn btn-link w-100 mt-2">
                Kembali ke Login
            </a>

        </form>
    </div>
</div>

<script>
    document.title = "Lupa Password - HRIS BSDM";
</script>

<?php require_once 'includes/footer_public.php'; ?>
