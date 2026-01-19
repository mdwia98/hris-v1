<?php
ob_start();
session_start();
/**
 * Karyawan - List Page (dengan Search dan Filter Real-time)
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
requireManagerOrAdmin();

$page_title = 'Manajemen Karyawan';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Hitung total data
$total = $conn->query("SELECT COUNT(*) as count FROM karyawan")->fetch_assoc()['count'] ?? 0;
$total_pages = ceil($total / $per_page);

// Gunakan created_at jika ada, fallback id
$cols = $conn->query("SHOW COLUMNS FROM karyawan LIKE 'created_at'");
$orderField = ($cols && $cols->num_rows > 0) ? 'created_at' : 'id';

// Ambil data karyawan
$result = $conn->query("
    SELECT * FROM karyawan 
    ORDER BY {$orderField} DESC 
    LIMIT $offset, $per_page
");

// Ambil departemen untuk filter (jika tabel departemen ada)
$departments = $conn->query("SELECT * FROM departemen");
?>

<!-- Page Header -->
<div class="content-header">
    <?php if (isset($_GET['import_success'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
                Berhasil mengimport <?php echo htmlspecialchars($_GET['import_success']); ?> karyawan.
    <?php if (!empty($_GET['import_failed']) && intval($_GET['import_failed']) > 0): ?>
        <br>Gagal: <?php echo intval($_GET['import_failed']); ?> baris.
    <?php endif; ?>
        </div>
    <?php endif; ?>
    <h1><i class="bi bi-people"></i> Manajemen Karyawan</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Karyawan</li>
        </ol>
    </nav>
</div>

<!-- Action Bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" class="form-control" id="karyawanSearchInput" placeholder="Cari nama, NIP, atau email...">
            </div>
            <div class="col-md-3">
                <select class="form-control" id="deptFilter">
                    <option value="">Semua Departemen</option>
                    <?php while ($dept = $departments->fetch_assoc()): ?>
                    <option value="<?php echo strtolower(htmlspecialchars($dept['nama_departemen'])); ?>">
                        <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="tetap">Tetap</option>
                    <option value="kontrak">Kontrak</option>
                    <option value="magang">Magang</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="<?php echo BASE_URL; ?>pages/karyawan/create.php" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle"></i> Tambah Karyawan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Karyawan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="karyawanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Departemen</th>
                        <th>Posisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="karyawanTableBody">
                    <?php 
                    $no = $offset + 1;
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td class="nip"><?php echo htmlspecialchars($row['nip']); ?></td>
                        <td class="nama"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="departemen"><?php echo htmlspecialchars($row['departemen']); ?></td>
                        <td class="posisi"><?php echo htmlspecialchars($row['posisi']); ?></td>
                        <td class="status">
                            <?php
                                $status_lc = strtolower($row['status_kerja'] ?? '');
                                $badgeClass = 'bg-secondary';
                                if ($status_lc === 'tetap') $badgeClass = 'bg-success';
                                elseif ($status_lc === 'kontrak') $badgeClass = 'bg-warning text-dark';
                                elseif ($status_lc === 'magang') $badgeClass = 'bg-info text-dark';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($row['status_kerja']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>pages/karyawan/detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            <a href="<?php echo BASE_URL; ?>pages/karyawan/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="#" class="btn btn-sm btn-danger btn-delete" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($row['nama']); ?>">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Tidak ada data karyawan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>">Sebelumnya</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>">Selanjutnya</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus karyawan <strong id="namaToDelete"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button id="confirmDeleteBtn" class="btn btn-danger">Hapus</button>
      </div>
    </div>
  </div>
</div>

<script>
// === REAL-TIME SEARCH + FILTER ===
const karyawanSearchInput = document.getElementById('karyawanSearchInput');
const karyawanDeptFilter = document.getElementById('deptFilter');
const karyawanStatusFilter = document.getElementById('statusFilter');

function applyKaryawanFilters() {
    const rows = document.querySelectorAll('#karyawanTableBody tr');
    const searchVal = karyawanSearchInput.value.toLowerCase();
    const deptVal = karyawanDeptFilter.value.toLowerCase();
    const statusVal = karyawanStatusFilter.value.toLowerCase();

    rows.forEach(row => {
        const nama = row.querySelector('.nama') ? row.querySelector('.nama').textContent.toLowerCase() : '';
        const nip = row.querySelector('.nip') ? row.querySelector('.nip').textContent.toLowerCase() : '';
        const email = row.querySelector('.email') ? row.querySelector('.email').textContent.toLowerCase() : '';
        const dept = row.querySelector('.departemen') ? row.querySelector('.departemen').textContent.toLowerCase() : '';
        const status = row.querySelector('.status') ? row.querySelector('.status').textContent.toLowerCase() : '';

        const matchSearch = nama.includes(searchVal) || nip.includes(searchVal) || email.includes(searchVal);
        const matchDept = deptVal === '' || dept.includes(deptVal);
        const matchStatus = statusVal === '' || status.includes(statusVal);

        row.style.display = (matchSearch && matchDept && matchStatus) ? '' : 'none';
    });
}

karyawanSearchInput.addEventListener('keyup', applyKaryawanFilters);
karyawanDeptFilter.addEventListener('change', applyKaryawanFilters);
karyawanStatusFilter.addEventListener('change', applyKaryawanFilters);

// === MODAL DELETE ===
let deleteID = null;
let deleteRow = null;

// Tampilkan modal
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();

        deleteID = this.dataset.id;
        deleteRow = this.closest('tr');

        document.getElementById('namaToDelete').textContent = this.dataset.name;

        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});

// Tombol konfirmasi delete
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {

    if (!deleteID) return;

    const formData = new FormData();
    formData.append('id', deleteID);

    fetch("<?php echo BASE_URL; ?>pages/karyawan/delete.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "success") {

            // Animasi fade out row
            if (deleteRow) {
                deleteRow.style.transition = "0.5s";
                deleteRow.style.opacity = "0";

                setTimeout(() => {
                    if (deleteRow) deleteRow.remove();
                }, 500);
            }

            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();

        } else {
            alert("Gagal menghapus: " + (data.message || 'Unknown'));
        }

    })
    .catch(err => alert("Terjadi error: " + err));
});
</script>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Karyawan - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
