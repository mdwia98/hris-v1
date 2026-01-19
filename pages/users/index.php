<?php
ob_start();
session_start();
/**
 * Users - List Page (with real-time search & role filter)
 */
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();

$page_title = 'Manajemen Pengguna';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Total user count
$total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$total_pages = ceil($total / $per_page);

// Ambil data user (gunakan created_at jika ada, kalau tidak fallback ke id)
$orderField = 'created_at';
$cols = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($cols->num_rows == 0) $orderField = 'id';

$sql = sprintf(
    "SELECT id, username, email, role, status, %s as created_at_real FROM users ORDER BY %s DESC LIMIT %d, %d",
    $orderField, $orderField, $offset, $per_page
);

$result = $conn->query($sql);
?>

<div class="content-header">
    <h1><i class="bi bi-person-lines-fill"></i> Manajemen Pengguna</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengguna</li>
        </ol>
    </nav>
</div>

<!-- Action bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" class="form-control" id="usersSearchInput" placeholder="Cari username atau email...">
            </div>
            <div class="col-md-4">
                <select class="form-control" id="usersRoleFilter">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="manajer">Manajer</option>
                    <option value="karyawan">Karyawan</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>pages/users/create.php" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle"></i> Tambah Pengguna
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Pengguna
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="usersTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php 
                    $no = $offset + 1;
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td class="username"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="role">
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['role']); ?></span>
                        </td>
                        <td>
                            <span class="badge <?php echo ($row['status'] == 'aktif') ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['created_at_real'])); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>

                            <!-- tombol reset password -->
                            <button class="btn btn-sm btn-secondary btn-reset" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>

                            <!-- tombol hapus -->
                            <button class="btn btn-sm btn-danger btn-delete" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada data pengguna.</td>
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

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin menghapus pengguna <strong id="usernameToDelete"></strong>?</p>
      </div>
      <div class="modal-footer">
        <form id="deleteForm" method="POST" action="delete.php">
          <input type="hidden" name="id" id="deleteUserId">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-secondary">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title"><i class="bi bi-arrow-clockwise"></i> Reset Password</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Reset password untuk pengguna <strong id="usernameToReset"></strong> ?</p>
        <p class="text-muted small">Password baru akan diubah menjadi: <b>bsdm2025</b></p>
      </div>
      <div class="modal-footer">
        <input type="hidden" id="resetUserId">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnConfirmReset">Ya, Reset</button>
      </div>
    </div>
  </div>
</div>

<script>
// === REAL-TIME SEARCH + ROLE FILTER ===
// Select elements at runtime and compute rows inside function to avoid stale NodeList
const usersSearchInput = document.getElementById('usersSearchInput');
const usersRoleFilter = document.getElementById('usersRoleFilter');

function applyUsersFilters() {
    const rows = document.querySelectorAll('#usersTableBody tr');
    const searchValue = usersSearchInput.value.toLowerCase();
    const roleValue = usersRoleFilter.value.toLowerCase();

    rows.forEach(row => {
        const usernameEl = row.querySelector('.username');
        const emailEl = row.querySelector('.email');
        const roleEl = row.querySelector('.role');

        const username = usernameEl ? usernameEl.textContent.toLowerCase() : '';
        const email = emailEl ? emailEl.textContent.toLowerCase() : '';
        const role = roleEl ? roleEl.textContent.toLowerCase() : '';

        const matchesSearch = username.includes(searchValue) || email.includes(searchValue);
        const matchesRole = roleValue === '' || role.includes(roleValue);

        row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
    });
}

usersSearchInput.addEventListener('keyup', applyUsersFilters);
usersRoleFilter.addEventListener('change', applyUsersFilters);

// === MODAL DELETE ===
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const username = this.dataset.username;
        document.getElementById('deleteUserId').value = id;
        document.getElementById('usernameToDelete').textContent = username;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});

// === MODAL RESET ===
document.querySelectorAll('.btn-reset').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('resetUserId').value = this.dataset.id;
        document.getElementById('usernameToReset').textContent = this.dataset.username;
        new bootstrap.Modal(document.getElementById('resetModal')).show();
    });
});

// === AJAX RESET PASSWORD ===
document.getElementById('btnConfirmReset').addEventListener('click', function() {
    const id = document.getElementById('resetUserId').value;
    if (!id) return;

    // use absolute path to reset endpoint
    fetch('<?php echo BASE_URL; ?>pages/users/reset_password_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(res => res.json())
    .then(data => {
        // Tutup modal
        bootstrap.Modal.getInstance(document.getElementById('resetModal')).hide();

        // Tampilkan toast
        const toastMessage = document.getElementById('toastMessage');
        const toastNotif = document.getElementById('toastNotif');

        if (data.status === 'success') {
            toastNotif.classList.remove('bg-danger');
            toastNotif.classList.add('bg-success');
        } else {
            toastNotif.classList.remove('bg-success');
            toastNotif.classList.add('bg-danger');
        }

        toastMessage.textContent = data.message;

        new bootstrap.Toast(toastNotif).show();
    })
    .catch(() => {
        const toastMessage = document.getElementById('toastMessage');
        const toastNotif = document.getElementById('toastNotif');

        toastNotif.classList.remove('bg-success');
        toastNotif.classList.add('bg-danger');
        toastMessage.textContent = 'Terjadi kesalahan koneksi';

        new bootstrap.Toast(toastNotif).show();
    });
});
</script>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastNotif" class="toast text-white" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Users - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
<?php ob_end_flush(); ?>