<?php
ob_start();
session_start();
/**
 * Logs - Activity Log with Filtering & Export Excel
 */
date_default_timezone_set('Asia/Jakarta');
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
requireManagerOrAdmin();
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// --- FILTERING LOGIC (dipindahkan ke atas agar export bisa pakai $where) ---
$filter_user   = $_POST['user']   ?? $_GET['user']   ?? '';
$filter_action = $_POST['action'] ?? $_GET['action'] ?? '';
$filter_date   = $_POST['date']   ?? $_GET['date']   ?? '';

$where = "WHERE 1=1";

if (!empty($filter_user)) 
    $where .= " AND activity_logs.user_id = '".intval($filter_user)."'";

if (!empty($filter_action))
    $where .= " AND action LIKE '%".$filter_action."%'";

if (!empty($filter_date))
    $where .= " AND DATE(activity_logs.created_at) = '".$filter_date."'";

// --- EXPORT EXCEL (HARUS SEBELUM header.php) ---
if (isset($_POST['export']) && $_POST['export'] == 'excel') {

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header Excel
    $sheet->setCellValue('A1', 'User');
    $sheet->setCellValue('B1', 'Aksi');
    $sheet->setCellValue('C1', 'Detail');
    $sheet->setCellValue('D1', 'Tanggal');

    // Fetch Data
    $logs = $conn->query("SELECT activity_logs.*, users.username 
                          FROM activity_logs 
                          JOIN users ON users.id = activity_logs.user_id 
                          $where 
                          ORDER BY activity_logs.id DESC");

    $rowCount = 2;
    while ($r = $logs->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowCount, $r['username']);
        $sheet->setCellValue('B' . $rowCount, $r['action']);
        $sheet->setCellValue('C' . $rowCount, $r['detail']);
        $sheet->setCellValue('D' . $rowCount, $r['created_at']);
        $rowCount++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = "Activity Logs - " . date('Y-m-d H-i-s') . ".xlsx";

    // FIX: Hapus semua output sebelumnya
    ob_clean(); 
    ob_start();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");

    $writer->save("php://output");
    exit;
}

require_once '../../includes/header.php';
$page_title = 'Riwayat Aktivitas';
?>

<div class="content-header">
    <h1><i class="bi bi-clock-history"></i> Riwayat Aktivitas</h1>
</div>

<div class="card p-3">

    <!-- Filter Form -->
<form class="row g-3 mb-3" method="GET">
    <div class="col-md-4">
        <label class="form-label">User</label>
        <select name="user" class="form-select">
            <option value="">Semua User</option>
            <?php
            $users = $conn->query("SELECT id, username FROM users ORDER BY username");
            while($u = $users->fetch_assoc()):
                $selected = ($_GET['user'] ?? '') == $u['id'] ? 'selected' : '';
            ?>
            <option value="<?= $u['id'] ?>" <?= $selected ?>><?= $u['username'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Aksi</label>
        <select name="action" class="form-select">
            <option value="">Semua Aksi</option>
            <?php
            $actions = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
            while($a = $actions->fetch_assoc()):
                $selected = ($_GET['action'] ?? '') == $a['action'] ? 'selected' : '';
            ?>
            <option value="<?= $a['action'] ?>" <?= $selected ?>><?= $a['action'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tanggal</label>
        <input type="date" name="date" class="form-control" value="<?= $_GET['date'] ?? '' ?>">
    </div>

    <div class="col-md-12 d-flex justify-content-between">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Filter
        </button>
    </div>
</form>

<!-- FORM EXPORT (TERPISAH, TIDAK DALAM FORM GET) -->
<form method="POST" action="" target="_blank">
    <input type="hidden" name="export" value="excel">
    <input type="hidden" name="user" value="<?= $_GET['user'] ?? '' ?>">
    <input type="hidden" name="action" value="<?= $_GET['action'] ?? '' ?>">
    <input type="hidden" name="date" value="<?= $_GET['date'] ?? '' ?>">

    <button type="submit" class="btn btn-success">
        <i class="bi bi-download"></i> Export Excel
    </button>
</form>
    <hr>

    <!-- Tabel Logs -->
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
        <tr>
            <th width="50">No</th>
            <th>User</th>
            <th>Aksi</th>
            <th>Detail</th>
            <th>Tanggal</th>
        </tr>
        </thead>
        <tbody>

        <?php
        // Pagination Setup
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Filtering Logic
        $where = "WHERE 1=1";

        if (!empty($_GET['user'])) $where .= " AND activity_logs.user_id = '".intval($_GET['user'])."'";
        if (!empty($_GET['action'])) $where .= " AND action LIKE '%".$_GET['action']."%'";
        if (!empty($_GET['date'])) $where .= " AND DATE(activity_logs.created_at) = '".$_GET['date']."'";

        // Query
        $sql = "SELECT activity_logs.*, users.username 
                FROM activity_logs 
                JOIN users ON users.id = activity_logs.user_id 
                $where ORDER BY activity_logs.id DESC 
                LIMIT $limit OFFSET $offset";

        $data = $conn->query($sql);

        $no = $offset + 1;
        while ($row = $data->fetch_assoc()):
        ?>

            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['action'] ?></td>
                <td><?= $row['detail'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            </tr>

        <?php endwhile; ?>

        </tbody>
    </table>

    <?php
    // Pagination - Count total
    $count = $conn->query("SELECT COUNT(*) AS total FROM activity_logs $where")->fetch_assoc()['total'];
    $total_pages = ceil($count / $limit);
    ?>

    <nav>
        <ul class="pagination justify-content-center">
            <?php for($i=1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i==$page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "History - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>
