<?php
ob_start();
// admin/user_permission.php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/header.php';
requireAdmin();

$page_title = 'Manajemen Izin User';

// ----------------------
// Pastikan ada objek koneksi $db
// ----------------------
if (!isset($db) || !$db instanceof mysqli) {
    if (isset($conn) && $conn instanceof mysqli) $db = $conn;
    elseif (isset($mysqli) && $mysqli instanceof mysqli) $db = $mysqli;
    else {
        if (defined('DB_HOST')) {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($db->connect_errno) die("DB Error: " . $db->connect_error);
        } else {
            die("Koneksi database tidak ditemukan.");
        }
    }
}

// ----------------------
// LIST SEMUA USER
// ----------------------
$users = $db->query("SELECT id, username, username FROM users ORDER BY username ASC");

// Ambil user id dari dropdown atau GET
$user_id = isset($_GET['u']) ? (int)$_GET['u'] : ($_SESSION['user']['id'] ?? 0);

// Kalau dropdown belum dipilih
if ($user_id <= 0) {
    $selected_user = null;
    $user_perm = [];
    $menus_res = [];
} else {
    // Ambil info user
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $selected_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Ambil semua menu
    $menus_res = $db->query("SELECT * FROM menus ORDER BY sort_order ASC");

    // Ambil izin user
    $stmt = $db->prepare("SELECT menu_id FROM user_permissions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $user_perm = [];
    while ($p = $res->fetch_assoc()) $user_perm[] = (int)$p['menu_id'];
    $stmt->close();
}

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="container mt-4">

    <div class="card shadow-lg border-0 rounded-4 p-4">
        <h3 class="fw-bold mb-3 text-primary">
            <i class="bi bi-shield-lock"></i> Manajemen Izin User
        </h3>

        <!-- DROPDOWN PILIH USER -->
        <form method="GET" class="mb-4">
            <label class="form-label fw-semibold">Pilih User:</label>
            <select name="u" class="form-select form-select-lg rounded-3 shadow-sm" onchange="this.form.submit()">
                <option value="">-- Pilih User --</option>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <option value="<?= $u['id']; ?>" <?= $u['id'] == $user_id ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($u['username'] . " (" . $u['username'] . ")"); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($user_id > 0 && $selected_user): ?>

            <div class="alert alert-info rounded-3 shadow-sm">
                Mengatur izin untuk: <strong><?= htmlspecialchars($selected_user['username']); ?></strong>
            </div>

            <!-- FORM IZIN -->
            <form id="permissionForm" method="POST">

                <div class="row mt-3">
                    <?php while ($m = $menus_res->fetch_assoc()): ?>
                        <div class="col-md-4 mb-3">
                            <div class="form-check bg-light p-3 rounded-3 shadow-sm">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="menu_id[]"
                                       value="<?= $m['id']; ?>"
                                       id="m<?= $m['id']; ?>"
                                       <?= in_array($m['id'], $user_perm) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-semibold" for="m<?= $m['id']; ?>">
                                    <?= htmlspecialchars($m['menu_name']); ?>
                                </label>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <button type="button" class="btn btn-primary btn-lg mt-3 shadow-sm" onclick="savePermission()">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>

            </form>

        <?php endif; ?>

    </div>

</div>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function savePermission() {
    Swal.fire({
        title: "Simpan Perubahan?",
        text: "Perubahan akses menu akan diperbarui untuk user ini.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Simpan",
        cancelButtonText: "Batal",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('permissionForm').submit();
        }
    });
}
</script>

<?php
// ----------------------
// Proses simpan perubahan
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id > 0) {

    $db->begin_transaction();
    try {
        // hapus izin lama
        $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // insert izin baru
        if (!empty($_POST['menu_id'])) {
            $stmt = $db->prepare("INSERT INTO user_permissions (user_id, menu_id) VALUES (?, ?)");
            foreach ($_POST['menu_id'] as $mid_raw) {
                $mid = (int)$mid_raw;
                $stmt->bind_param("ii", $user_id, $mid);
                $stmt->execute();
            }
            $stmt->close();
        }

        $db->commit();

        echo "<script>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Akses menu telah diperbarui.',
                    icon: 'success'
                }).then(() => {
                    window.location='user_permission.php?u=$user_id';
                });
            </script>";

    } catch (Exception $e) {
        $db->rollback();

        echo "<script>
                Swal.fire('Error', 'Gagal menyimpan perubahan.', 'error');
              </script>";
    }
}
?>
<script>
    // Highlight menu aktif
    document.addEventListener('DOMContentLoaded', function() {
        const menuItem = document.getElementById('menu-user-permission');
        if (menuItem) {
            menuItem.classList.add('active');
        }
    });
</script>

<?php require_once '../includes/footer.php'; ob_end_flush(); ?>

