<?php
/**
 * Departemen - List Page
 */
require_once '../../config/database.php';
require_once '../../includes/session.php';
requireAdmin();
require_once '../../includes/header.php';

$page_title = 'Manajemen Departemen';

// Ambil semua departemen
$stmt = $conn->prepare("SELECT id, nama_departemen, keterangan FROM departemen ORDER BY nama_departemen");
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-building"></i> Manajemen Departemen</h1>
</div>

<!-- Action Bar -->
<div class="card mb-4">
    <div class="card-body text-end">
        <a href="<?php echo BASE_URL; ?>pages/departemen/create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Departemen
        </a>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Departemen
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Departemen</th>
                        <th>Keterangan</th>
                        <th>Jumlah Karyawan</th>
                        <th width="300">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;

                    // Prepared statement untuk menghitung karyawan → lebih aman & cepat
                    $count_stmt = $conn->prepare("
                        SELECT COUNT(*) AS jumlah 
                        FROM karyawan 
                        WHERE departemen = ?
                    ");

                    while ($row = $result->fetch_assoc()):
                        $departemen = $row['nama_departemen'];

                        // Hitung jumlah karyawan
                        $count_stmt->bind_param("s", $departemen);
                        $count_stmt->execute();
                        $count_res = $count_stmt->get_result()->fetch_assoc();
                        $jumlah_karyawan = $count_res['jumlah'] ?? 0;
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($departemen); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['keterangan'] ?: '-'); ?></td>
                        <td>
                            <span class="badge bg-primary"><?php echo $jumlah_karyawan; ?> karyawan</span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>pages/departemen/edit.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>

                            <a href="<?php echo BASE_URL; ?>pages/departemen/delete.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               data-action="delete">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data departemen.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Set judul tab browser
    document.title = "Departemen - HRIS BSDM";
</script>

<?php 
$stmt->close();
$count_stmt->close();
require_once '../../includes/footer.php'; 
?>