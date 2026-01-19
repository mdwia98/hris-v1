<?php
/**
 * Profile Page
 */

require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/header.php';

$page_title = 'Profil Saya';

// Pastikan session aktif dan user login
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    die('User tidak ditemukan dalam session.');
}

// Get user data (gunakan prepared statement untuk keamanan)
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
?> 

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-person-circle"></i> Profil Saya</h1>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h4><?php echo htmlspecialchars($user['nama'] ?? $user['username']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($user['posisi'] ?? $user['role']); ?></p>
                <div class="badge bg-success mb-2">
                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                </div>
                <p style="margin-top: 1rem;">
                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Profile Details -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi Pribadi
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>NIP</strong></label>
                        <p><?php echo htmlspecialchars($user['nip'] ?? '-'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Username</strong></label>
                        <p><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Email</strong></label>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Telepon</strong></label>
                        <p><?php echo htmlspecialchars($user['telepon'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Departemen</strong></label>
                        <p><?php echo htmlspecialchars($user['departemen'] ?? '-'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Posisi</strong></label>
                        <p><?php echo htmlspecialchars($user['posisi'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Tanggal Lahir</strong></label>
                        <p><?php echo $user['tanggal_lahir'] ? date('d M Y', strtotime($user['tanggal_lahir'])) : '-'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Jenis Kelamin</strong></label>
                        <p><?php echo htmlspecialchars($user['jenis_kelamin'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Alamat</strong></label>
                    <p><?php echo htmlspecialchars($user['alamat'] ?? '-'); ?></p>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label"><strong>Status Kerja</strong></label>
                        <p><span class="badge bg-success"><?php echo htmlspecialchars($user['status_kerja'] ?? '-'); ?></span></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><strong>Tanggal Join</strong></label>
                        <p><?php echo $user['tanggal_join'] ? date('d M Y', strtotime($user['tanggal_join'])) : '-'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><strong>Gaji</strong></label>
                        <p><?php echo $user['gaji'] ? 'Rp ' . number_format($user['gaji'], 0, ',', '.') : '-'; ?></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><strong>Akun Status</strong></label>
                        <p><span class="badge bg-info"><?php echo htmlspecialchars($user['status']); ?></span></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-shield-lock"></i> Keamanan
            </div>
            <div class="card-body">
                <label class="form-label"><strong>Ubah Password</strong></label>
                <a href="change_password.php" class="btn btn-warning">
                    <i class="bi bi-key"></i> Ubah Password
                </a>
                <br><br>
                <label class="form-label"><strong>Edit Profil</strong></label>
                <a href="edit_profile.php" class="btn btn-secondary">
                    <i class="bi bi-pencil"></i> Edit Profil
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Profil - HRIS BSDM";
</script>

<?php require_once 'includes/footer.php';?>
