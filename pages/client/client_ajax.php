<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
requireAdmin();

/**
 * ============================================================
 *  GET SINGLE DATA (EDIT MODAL)
 * ============================================================
 */
if (isset($_GET['action']) && $_GET['action'] === 'get') {

    $id = intval($_GET['id']);
    $query = $conn->query("SELECT * FROM clients WHERE id = $id");

    echo json_encode($query->fetch_assoc());
    exit;
}

/**
 * ============================================================
 *  HANDLE CREATE / UPDATE / DELETE
 * ============================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    // Mapping Input Form
    $nama_client = $conn->real_escape_string($_POST['nama_client'] ?? '');
    $email       = $conn->real_escape_string($_POST['email'] ?? '');
    $telepon     = $conn->real_escape_string($_POST['telepon'] ?? '');
    $perusahaan  = $conn->real_escape_string($_POST['perusahaan'] ?? '');

    /**
     * CREATE
     */
    if ($action === 'create') {

        $sql = "INSERT INTO clients (nama_client, email, telepon, perusahaan)
                VALUES ('$nama_client', '$email', '$telepon', '$perusahaan')";

        if ($conn->query($sql)) {
            addLog($_SESSION['user']['id'], "Create Client", "Membuat client baru: $nama_client");
            echo json_encode(["status" => "success", "message" => "Client berhasil ditambahkan"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        exit;
    }

    /**
     * UPDATE
     */
    if ($action === 'update') {

        $id = intval($_POST['id']);

        $sql = "UPDATE clients SET
                nama_client='$nama_client',
                email='$email',
                telepon='$telepon',
                perusahaan='$perusahaan'
                WHERE id = $id";

        if ($conn->query($sql)) {
            addLog($_SESSION['user']['id'], "Update Client", "Mengubah data client: $nama_client");
            echo json_encode(["status" => "success", "message" => "Client berhasil diperbarui"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        exit;
    }

    /**
     * DELETE
     */
    if ($action === 'delete') {

        $id = intval($_POST['id']);

        // Ambil nama client untuk log
        $old = $conn->query("SELECT nama_client FROM clients WHERE id = $id")->fetch_assoc();
        $oldName = $old['nama_client'] ?? '';

        $sql = "DELETE FROM clients WHERE id = $id";

        if ($conn->query($sql)) {
            addLog($_SESSION['user']['id'], "Delete Client", "Menghapus client: $oldName");
            echo json_encode(["status" => "success", "message" => "Client berhasil dihapus"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        exit;
    }
}

/**
 * ============================================================
 *  LOAD TABLE DATA (DEFAULT AJAX)
 * ============================================================
 */

$page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit  = 10;
$offset = ($page - 1) * $limit;

$search     = $_GET['search'] ?? '';
$perusahaan = $_GET['perusahaan'] ?? '';

$where = "WHERE 1=1";

if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where .= " AND nama_client LIKE '%$s%'";
}

if ($perusahaan !== '') {
    $p = $conn->real_escape_string($perusahaan);
    $where .= " AND perusahaan = '$p'";
}

$sql = "
    SELECT * FROM clients
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";

$data = $conn->query($sql);

// Hitung total untuk pagination
$count = $conn->query("SELECT COUNT(*) AS total FROM clients $where")
               ->fetch_assoc()['total'];

$totalPages = ceil($count / $limit);
?>

<!-- ============================================================
     RENDER TABLE
============================================================ -->

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-light">
            <tr>
                <th width="50">No</th>
                <th>Nama Client</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Perusahaan</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>

<?php if ($data->num_rows === 0): ?>
        <tr>
            <td colspan="6" class="text-center py-4 text-muted">
                <i>Tidak ada data ditemukan</i>
            </td>
        </tr>

<?php else: 
    $no = $offset + 1;
    while ($row = $data->fetch_assoc()):
?>
        <tr id="row-<?= $row['id']; ?>">
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($row['nama_client']); ?></strong></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= htmlspecialchars($row['telepon']); ?></td>
            <td><?= htmlspecialchars($row['perusahaan']); ?></td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= $row['id']; ?>)">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteClient(<?= $row['id']; ?>)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
<?php endwhile; endif; ?>

        </tbody>
    </table>
</div>

<!-- ============================================================
     PAGINATION
============================================================ -->
<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">

        <!-- Previous -->
        <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
            <a class="page-link" href="javascript:void(0)" onclick="loadData(<?= $page - 1 ?>)">
                Sebelumnya
            </a>
        </li>

        <!-- Numbered -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($page == $i ? 'active' : '') ?>">
                <a class="page-link" href="javascript:void(0)" onclick="loadData(<?= $i ?>)">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Next -->
        <li class="page-item <?= ($page >= $totalPages ? 'disabled' : '') ?>">
            <a class="page-link" href="javascript:void(0)" onclick="loadData(<?= $page + 1 ?>)">
                Berikutnya
            </a>
        </li>

    </ul>
</nav>
<?php endif; ?>