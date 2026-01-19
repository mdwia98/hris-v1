<?php
ob_start();
session_start();
/**
 * Karyawan - Create Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php'; // <-- tambahkan ini
requireManagerOrAdmin();

$page_title = 'Tambah Karyawan';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $departemen = trim($_POST['departemen'] ?? '');
    $posisi = trim($_POST['posisi'] ?? '');
    $status_kerja = trim($_POST['status_kerja'] ?? '');
    $gaji = trim($_POST['gaji'] ?? '');
    $tanggal_join = trim($_POST['tanggal_join'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username)) {
        $username = strtolower(str_replace(' ', '', $nama)) . rand(100, 999);
    }

    if (empty($nip) || empty($nama) || empty($email)) {
        $error = 'NIP, nama, dan email harus diisi';
        writeLog('karyawan', 'create_failed', "Input tidak lengkap saat menambah karyawan ($nama - $nip)");
    } else {
        // Create user account first
        $hashed_password = password_hash($password ?: 'bsdm2025@', PASSWORD_BCRYPT);
        $user_query = "INSERT INTO users (username, email, password, role, status) 
                       VALUES ('$username', '$email', '$hashed_password', 'karyawan', 'aktif')";
        
        if ($conn->query($user_query)) {
            $user_id = $conn->insert_id;

            // Log: user account berhasil dibuat
            writeLog('users', 'create', "Buat akun user karyawan: $username (user_id: $user_id)");

            // Insert employee
            $karyawan_query = "INSERT INTO karyawan (user_id, nip, nama, email, telepon, alamat, tanggal_lahir, jenis_kelamin, departemen, posisi, status_kerja, gaji, tanggal_join)
                               VALUES ($user_id, '$nip', '$nama', '$email', '$telepon', '$alamat', '$tanggal_lahir', '$jenis_kelamin', '$departemen', '$posisi', '$status_kerja', $gaji, '$tanggal_join')";
            
            if ($conn->query($karyawan_query)) {
                // Log ketika berhasil buat data karyawan
                writeLog('karyawan', 'create', "Tambah karyawan: $nama ($nip), user_id:$user_id");

                header('Location: ' . BASE_URL . 'pages/karyawan/index.php');
                exit();
            } else {
                $error = 'Error: ' . $conn->error;
                // Log gagal insert karyawan
                writeLog('karyawan', 'create_failed', "Gagal buat karyawan untuk user_id:$user_id | Error: {$conn->error}");
            }
        } else {
            $error = 'Error: ' . $conn->error;
            // Log ketika gagal buat akun user
            writeLog('users', 'create_failed', "Gagal buat akun user $username | Error: {$conn->error}");
        }
    }
}

// Get departments
$departments = $conn->query("SELECT * FROM departemen ORDER BY nama_departemen");
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-person-plus"></i> Tambah Karyawan Baru</h1>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <h5 class="mb-3"><i class="bi bi-person"></i> Informasi Pribadi</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP *</label>
                    <input type="text" name="nip" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon *</label>
                    <input type="tel" name="telepon" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir *</label>
                    <input type="date" name="tanggal_lahir" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alamat *</label>
                <textarea name="alamat" class="form-control" rows="2" required></textarea>
            </div>
            
            <hr>
            <h5 class="mb-3"><i class="bi bi-briefcase"></i> Informasi Pekerjaan</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departemen *</label>
                    <select name="departemen" class="form-control" required>
                        <option value="">-- Pilih Departemen --</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($dept['nama_departemen']); ?>">
                            <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Posisi *</label>
                    <select name="posisi" class="form-control" required>
                        <option value="">-- Pilih Posisi --</option>
                        <option value="Manager">Manager</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kerja *</label>
                    <select name="status_kerja" class="form-control" required>
                        <option value="">-- Pilih Status Kerja --</option>
                        <option value="Tetap">Tetap</option>
                        <option value="Kontrak">Kontrak</option>
                        <option value="Magang">Magang</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gaji Pokok *</label>
                    <input type="number" name="gaji" class="form-control" step="1" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Tanggal Join *</label>
                <input type="date" name="tanggal_join" class="form-control" required>
            </div>
            
            <hr>
            <h5 class="mb-3"><i class="bi bi-lock"></i> Akun Sistem</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Kosongkan untuk auto-generate">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan untuk password default">
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="<?php echo BASE_URL; ?>pages/karyawan/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Tambah User - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>