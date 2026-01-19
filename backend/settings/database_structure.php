<?php
ob_start();
session_start();
/**
 * database_structure.php
 * All-in-one Database Structure Manager
 * - Single file containing UI + actions + export
 *
 * Placement: /backend/settings/database_structure.php
 * Assumes: ../../config/database.php exists and defines $conn (mysqli)
 */
require_once '../../includes/session.php';


requireAdmin();
error_reporting(E_ALL);
ini_set('display_errors',1);

// adjust this path if your database.php located elsewhere
require_once '../../config/database.php';

// check mysqli conn
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi MySQL (mysqli) tidak ditemukan. Pastikan database.php menyediakan \$conn.");
}

// --- Helpers ---
function e($s){
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}
function redirect($url){
    header("Location: $url");
    exit;
}
function detect_primary_key($conn, $table) {
    $res = $conn->query("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
    if ($res && $r = $res->fetch_assoc()) return $r['Column_name'];
    return null;
}
function table_exists($conn, $table) {
    $r = $conn->query("SHOW TABLES LIKE '".$conn->real_escape_string($table)."'");
    return $r && $r->num_rows>0;
}

// --- Load tables
$tables = [];
$q = $conn->query("SHOW TABLES");
while ($r = $q->fetch_array()) $tables[] = $r[0];

// --- Selected table
$selected_table = $_GET['table'] ?? ($_POST['selected_table'] ?? null);
if ($selected_table && !table_exists($conn, $selected_table)) {
    $selected_table = null;
}

// --- Export handlers (must be before any HTML output)
if (isset($_GET['export']) && $selected_table) {
    $export = $_GET['export'];
    if ($export === 'json') {
        // export structure JSON
        $res = $conn->query("SHOW COLUMNS FROM `{$selected_table}`");
        $cols = [];
        while ($r = $res->fetch_assoc()) $cols[] = $r;
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="'.$selected_table.'_structure.json"');
        echo json_encode($cols, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($export === 'sql') {
        // export create table SQL
        $res = $conn->query("SHOW CREATE TABLE `{$selected_table}`");
        $row = $res->fetch_assoc();
        $sql = ($row['Create Table'] ?? $row['Create View'] ?? '');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="'.$selected_table.'_structure.sql"');
        echo $sql . ";\n";
        exit;
    }
    if ($export === 'data') {
        // export data to XLSX if PhpSpreadsheet exists, else CSV
        $search = $_GET['search'] ?? '';
        // build search SQL
        $where = "";
        if ($search !== '') {
            $colsRes = $conn->query("SHOW COLUMNS FROM `{$selected_table}`");
            $conds = [];
            while ($c = $colsRes->fetch_assoc()) {
                $field = $c['Field'];
                $conds[] = "`$field` LIKE '%".$conn->real_escape_string($search)."%'";
            }
            if (count($conds)) $where = "WHERE ".implode(' OR ', $conds);
        }
        $sql = "SELECT * FROM `{$selected_table}` $where";
        $res = $conn->query($sql);
        // fetch fields and rows
        $fields = [];
        if ($res) {
            $finfo = $res->fetch_fields();
            foreach ($finfo as $f) $fields[] = $f->name;
        }
        $rows = [];
        if ($res) {
            while ($rr = $res->fetch_assoc()) $rows[] = $rr;
        }

        // try PhpSpreadsheet
        if (file_exists(__DIR__.'/../../vendor/autoload.php')) {
            require_once __DIR__.'/../../vendor/autoload.php';
            try {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Header
                $colIdx = 1;
                foreach ($fields as $h) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . "1";
                    $sheet->setCellValue($cell, $h);
                    $colIdx++;
            }

                // Data rows
                $rowIdx = 2;
                foreach ($rows as $r) {
                    $colIdx = 1;
                    foreach ($fields as $h) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowIdx;
                    $sheet->setCellValue($cell, $r[$h]);
                    $colIdx++;
                }
                    $rowIdx++;
            }

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $fn = $selected_table . "_data.xlsx";
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'.$fn.'"');
                $writer->save('php://output');
                exit;
            } catch (Exception $ex) {
                // fallback to CSV below
            }
        }
        // fallback CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$selected_table.'_data.csv"');
        $out = fopen('php://output','w');
        // UTF-8 BOM for Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, $fields);
        foreach ($rows as $r) {
            $line = [];
            foreach ($fields as $h) $line[] = $r[$h];
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }
}

// --- Handle POST actions (single-file routing)
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // sanitize table name usage
    $st = $selected_table ? $conn->real_escape_string($selected_table) : null;

    // ADD TABLE
    if ($action === 'add_table') {
        $tn = trim($_POST['table_name'] ?? '');
        $col = trim($_POST['first_column'] ?? '');
        $type = trim($_POST['first_type'] ?? 'INT(11)');
        if ($tn === '' || $col === '') {
            $feedback = "<div class='alert alert-danger'>Nama tabel dan kolom pertama wajib.</div>";
        } else {
            if (!preg_match('/^[A-Za-z0-9_]+$/',$tn)) {
                $feedback = "<div class='alert alert-danger'>Nama tabel mengandung karakter tidak valid.</div>";
            } else {
                $sql = "CREATE TABLE `{$tn}` (`{$col}` {$conn->real_escape_string($type)}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                if ($conn->query($sql)) {
                    $feedback = "<div class='alert alert-success'>Tabel <b>".e($tn)."</b> berhasil dibuat.</div>";
                    // reload page to new table
                    redirect('?table='.urlencode($tn));
                } else {
                    $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
                }
            }
        }
    }

    // RENAME TABLE
    if ($action === 'rename_table' && $st) {
        $new = trim($_POST['new_table_name'] ?? '');
        if ($new === '' || !preg_match('/^[A-Za-z0-9_]+$/',$new)) {
            $feedback = "<div class='alert alert-danger'>Nama baru tidak valid.</div>";
        } else {
            $sql = "RENAME TABLE `{$st}` TO `{$new}`";
            if ($conn->query($sql)) {
                redirect('?table='.urlencode($new).'&msg=renamed');
            } else {
                $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
            }
        }
    }

    // DROP TABLE
    if ($action === 'drop_table' && $st) {
        $sql = "DROP TABLE `{$st}`";
        if ($conn->query($sql)) {
            redirect('database_structure.php?msg=dropped');
        } else {
            $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
        }
    }

    // ADD COLUMN (with position)
    if ($action === 'add_column' && $st) {
        $field = trim($_POST['col_name'] ?? '');
        $type = trim($_POST['col_type'] ?? 'VARCHAR(255)');
        $position = $_POST['col_position'] ?? 'AFTER';
        if ($field === '') $feedback = "<div class='alert alert-danger'>Nama kolom wajib.</div>";
        else {
            if ($position === 'FIRST') $pos_sql = " FIRST";
            else {
                $after = trim($_POST['col_after'] ?? '');
                if ($after !== '') $pos_sql = " AFTER `".$conn->real_escape_string($after)."`";
                else $pos_sql = "";
            }
            $sql = "ALTER TABLE `{$st}` ADD COLUMN `".$conn->real_escape_string($field)."` {$conn->real_escape_string($type)} {$pos_sql}";
            if ($conn->query($sql)) {
                $feedback = "<div class='alert alert-success'>Kolom ditambahkan.</div>";
                redirect('?table='.urlencode($selected_table));
            } else {
                $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
            }
        }
    }

    // EDIT COLUMN (CHANGE)
    if ($action === 'edit_column' && $st) {
        $old = $_POST['old_name'] ?? '';
        $new = $_POST['new_name'] ?? '';
        $type = $_POST['new_type'] ?? '';
        if ($old === '' || $new === '') $feedback = "<div class='alert alert-danger'>Data kolom tidak lengkap.</div>";
        else {
            $sql = "ALTER TABLE `{$st}` CHANGE `".$conn->real_escape_string($old)."` `".$conn->real_escape_string($new)."` ".$conn->real_escape_string($type);
            if ($conn->query($sql)) {
                $feedback = "<div class='alert alert-success'>Kolom diubah.</div>";
                redirect('?table='.urlencode($selected_table));
            } else {
                $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
            }
        }
    }

    // DROP COLUMN
    if ($action === 'drop_column' && $st) {
        $field = $_POST['drop_field'] ?? '';
        if ($field === '') $feedback = "<div class='alert alert-danger'>Kolom tidak ditemukan.</div>";
        else {
            $sql = "ALTER TABLE `{$st}` DROP COLUMN `".$conn->real_escape_string($field)."`";
            if ($conn->query($sql)) {
                $feedback = "<div class='alert alert-success'>Kolom dihapus.</div>";
                redirect('?table='.urlencode($selected_table));
            } else {
                $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
            }
        }
    }

    // EDIT RECORD (update)
    if ($action === 'edit_record' && $st) {
        // find primary key
        $pk = detect_primary_key($conn, $st);
        if (!$pk) {
            $feedback = "<div class='alert alert-danger'>Tidak ditemukan primary key untuk tabel ini; edit tidak didukung.</div>";
        } else {
            // reconstruct SET from posted fields
            $sets = [];
            $idval = null;
            foreach ($_POST as $k => $v) {
                if ($k === 'action' || $k === 'selected_table' || $k === 'pk_name') continue;
                if ($k === $pk) $idval = $v;
                $sets[] = "`".$conn->real_escape_string($k)."` = '".$conn->real_escape_string($v)."'";
            }
            if ($idval === null) $feedback = "<div class='alert alert-danger'>Primary key value tidak disertakan.</div>";
            else {
                $sql = "UPDATE `{$st}` SET ".implode(',', $sets)." WHERE `".$conn->real_escape_string($pk)."` = '".$conn->real_escape_string($idval)."' LIMIT 1";
                if ($conn->query($sql)) {
                    $feedback = "<div class='alert alert-success'>Record diupdate.</div>";
                    redirect('?table='.urlencode($selected_table));
                } else {
                    $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
                }
            }
        }
    }

    // DELETE RECORD
    if ($action === 'delete_record' && $st) {
        $pk = detect_primary_key($conn, $st);
        if (!$pk) {
            $feedback = "<div class='alert alert-danger'>Tidak ditemukan primary key untuk tabel ini; delete tidak didukung.</div>";
        } else {
            $id = $_POST['pk_value'] ?? '';
            if ($id === '') $feedback = "<div class='alert alert-danger'>Nilai primary key tidak disediakan.</div>";
            else {
                $sql = "DELETE FROM `{$st}` WHERE `".$conn->real_escape_string($pk)."` = '".$conn->real_escape_string($id)."' LIMIT 1";
                if ($conn->query($sql)) {
                    $feedback = "<div class='alert alert-success'>Record dihapus.</div>";
                    redirect('?table='.urlencode($selected_table));
                } else {
                    $feedback = "<div class='alert alert-danger'>".$conn->error."</div>";
                }
            }
        }
    }

} // end POST actions

// --- Refresh table_structure after any possible change
$table_structure = [];
if ($selected_table) {
    $res = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($selected_table)."`");
    while ($r = $res->fetch_assoc()) $table_structure[] = $r;
}

// --- Data view: pagination + search
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

// prepare search SQL
$search_sql = '';
if ($selected_table && $search !== '') {
    // build OR conditions across all columns
    $colRes = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($selected_table)."`");
    $conds = [];
    while ($c = $colRes->fetch_assoc()) {
        $fn = $c['Field'];
        $conds[] = "`$fn` LIKE '%".$conn->real_escape_string($search)."%'";
    }
    if (count($conds)) $search_sql = "WHERE ".implode(' OR ', $conds);
}

// total rows
$total_rows = 0;
$total_pages = 1;
$data_rows = [];
$columns = [];
if ($selected_table) {
    $countQ = $conn->query("SELECT COUNT(*) as cnt FROM `".$conn->real_escape_string($selected_table)."` $search_sql");
    $total_rows = ($countQ && $cr = $countQ->fetch_assoc()) ? intval($cr['cnt']) : 0;
    $total_pages = max(1, ceil($total_rows / $limit));

    // fetch page data
    $dataQ = $conn->query("SELECT * FROM `".$conn->real_escape_string($selected_table)."` $search_sql LIMIT $limit OFFSET $offset");
    if ($dataQ) {
        $fields = $dataQ->fetch_fields();
        foreach ($fields as $f) $columns[] = $f->name;
        while ($r = $dataQ->fetch_assoc()) $data_rows[] = $r;
    }
}

// --- Messages from GET (rename/drop)
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'renamed') $feedback = "<div class='alert alert-success'>Tabel berhasil di-rename.</div>";
    if ($_GET['msg'] === 'dropped') $feedback = "<div class='alert alert-success'>Tabel berhasil dihapus.</div>";
}

// ---- HTML UI ----
?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Database Structure Manager — <?= e(DB_NAME) ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!-- Bootstrap 5 + FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background:#f4f6f9; font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
        .sidebar { width: 260px; position: fixed; top:0; left:0; bottom:0; background:#1f2937; color:#e5e7eb; padding:18px 12px; overflow:auto; }
        .sidebar h4 { color:#fff; text-align:center; margin-bottom:18px; font-weight:600; }
        .sidebar .list-group-item { background:transparent; color:#cbd5e1; border:0; padding:10px 12px; }
        .sidebar .list-group-item.active { background:#0ea5e9; color:#fff; border-radius:8px; }
        .content { margin-left: 280px; padding:24px; }
        .card { border-radius:10px; }
        .mono { font-family: monospace; }
        .table-responsive { overflow:auto; }
        .small-muted { color:#6b7280; font-size:0.9rem; }
        /* make modal large inputs monospace for SQL */
        textarea.mono { font-family: Consolas, Monaco, monospace; }
    </style>
</head>
<body>

<div class="sidebar shadow-sm">
    <h4><a class="nav-link" href="<?php echo BASE_URL; ?>dashboard.php"><i class="fa fa-database"></i> DB Manager</a></h4>

    <button class="btn btn-success w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalAddTable">
        <i class="fa fa-plus"></i> Tambah Tabel
    </button>

    <div class="list-group">
        <?php foreach ($tables as $t): ?>
            <a href="?table=<?= urlencode($t) ?>" class="list-group-item <?= ($selected_table === $t ? 'active' : '') ?>">
                <i class="fa fa-table me-2"></i> <?= e($t) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <hr class="my-3" style="border-color:#334155;">
    <div class="small-muted text-center">Database: <strong><?= e(DB_NAME) ?></strong></div>
</div>

<div class="content">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-0"><i class="fa fa-sliders"></i> Database Structure Manager</h3>
            <div class="small-muted">Kelola struktur & data tabel</div>
        </div>
        <div>
            <?php if ($selected_table): ?>
                <div class="btn-group">
                    <a class="btn btn-outline-secondary" href="?table=<?= urlencode($selected_table) ?>&export=json"><i class="fa fa-file-code"></i> Export JSON</a>
                    <a class="btn btn-outline-secondary" href="?table=<?= urlencode($selected_table) ?>&export=sql"><i class="fa fa-file-import"></i> Export SQL</a>
                    <a class="btn btn-outline-success" href="?table=<?= urlencode($selected_table) ?>&export=data&search=<?= urlencode($search) ?>"><i class="fa fa-file-excel"></i> Export Data</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?= $feedback ?>

    <?php if (!$selected_table): ?>
        <div class="card p-4 mb-3">
            <h5>Pilih tabel dari sidebar</h5>
            <p class="small-muted mb-0">Klik nama tabel di sidebar untuk melihat struktur dan datanya.</p>
        </div>
    <?php else: ?>

    <!-- Table header controls -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Tabel: <span class="mono"><?= e($selected_table) ?></span></h5>
                <div class="small-muted">Columns: <?= count($table_structure) ?> &nbsp; | &nbsp; Rows: <?= $total_rows ?></div>
            </div>
            <div>
                <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#modalRenameTable">
                    <i class="fa fa-edit"></i> Rename
                </button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDropTable">
                    <i class="fa fa-trash"></i> Drop Table
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs: Structure / Data -->
    <ul class="nav nav-tabs" id="mainTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-structure">Struktur</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-data">Data</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-sql">SHOW CREATE</a></li>
    </ul>

    <div class="tab-content mt-3">
        <!-- STRUCTURE -->
        <div class="tab-pane fade show active" id="tab-structure">
            <div class="card mb-3">
                <div class="card-body">
                    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAddColumn"><i class="fa fa-plus"></i> Tambah Kolom</button>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th><th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_structure as $col): ?>
                                    <tr>
                                        <td><?= e($col['Field']) ?></td>
                                        <td><?= e($col['Type']) ?></td>
                                        <td><?= e($col['Null']) ?></td>
                                        <td><?= e($col['Key']) ?></td>
                                        <td><?= e($col['Default']) ?></td>
                                        <td><?= e($col['Extra']) ?></td>
                                        <td style="min-width:150px;">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditColumn" 
                                                data-field="<?= e($col['Field']) ?>" data-type="<?= e($col['Type']) ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDropColumn"
                                                data-field="<?= e($col['Field']) ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($table_structure)===0): ?>
                                    <tr><td colspan="7" class="text-center small-muted">Tabel kosong (tidak ada kolom?)</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATA -->
        <div class="tab-pane fade" id="tab-data">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-center">
                        <input type="hidden" name="table" value="<?= e($selected_table) ?>">
                        <div class="col-auto">
                            <input class="form-control" name="search" placeholder="Cari..." value="<?= e($search) ?>">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary">Cari</button>
                        </div>
                    </form>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <th><?= e($col) ?></th>
                                    <?php endforeach; ?>
                                    <th style="width:140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data_rows as $row): ?>
                                    <tr>
                                        <?php foreach ($columns as $c): ?>
                                            <td><?= e($row[$c]) ?></td>
                                        <?php endforeach; ?>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditRecord"
                                                data-row='<?= json_encode($row, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>'>
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteRecord"
                                                data-pk="<?= e(detect_primary_key($conn, $selected_table) ?? '') ?>"
                                                data-val="<?= e(reset($row)) ?>">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($data_rows)===0): ?>
                                    <tr><td colspan="<?= max(1, count($columns)+1) ?>" class="text-center small-muted">Tidak ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="small-muted">Menampilkan halaman <?= $page ?> / <?= $total_pages ?> (<?= $total_rows ?> baris)</div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li class="page-item <?= ($page<=1?'disabled':'') ?>">
                                    <a class="page-link" href="?table=<?= urlencode($selected_table) ?>&page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Prev</a>
                                </li>
                                <?php
                                $start = max(1, $page-2);
                                $end = min($total_pages, $page+2);
                                for ($i=$start;$i<=$end;$i++): ?>
                                    <li class="page-item <?= ($i==$page?'active':'') ?>">
                                        <a class="page-link" href="?table=<?= urlencode($selected_table) ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page>=$total_pages?'disabled':'') ?>">
                                    <a class="page-link" href="?table=<?= urlencode($selected_table) ?>&page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>

        <!-- SHOW CREATE -->
        <div class="tab-pane fade" id="tab-sql">
            <div class="card">
                <div class="card-body">
                    <h6>SHOW CREATE TABLE <?= e($selected_table) ?></h6>
                    <pre class="mono p-3 bg-light border"><?php
                        $r = $conn->query("SHOW CREATE TABLE `".$conn->real_escape_string($selected_table)."`");
                        if ($r && $d=$r->fetch_assoc()) {
                            echo e($d['Create Table'] ?? $d['Create View'] ?? '—');
                        } else {
                            echo "Gagal menampilkan CREATE: ".e($conn->error);
                        }
                    ?></pre>
                </div>
            </div>
        </div>
    </div>

    <?php endif; // selected table ?>

    <hr class="mt-4">
    <div class="small-muted">Created by DB Structure Manager — gunakan hati-hati di environment produksi.</div>
</div>

<!-- MODALS -->

<!-- Add Table -->
<div class="modal fade" id="modalAddTable" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white"><h5 class="modal-title">Tambah Tabel</h5></div>
      <div class="modal-body">
          <label>Nama tabel</label>
          <input name="table_name" class="form-control" required pattern="[A-Za-z0-9_]+">
          <label class="mt-2">Kolom pertama</label>
          <div class="row g-2">
              <div class="col"><input name="first_column" class="form-control" placeholder="id"></div>
              <div class="col"><input name="first_type" class="form-control" placeholder="INT(11)"></div>
          </div>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="add_table">
        <button class="btn btn-success">Buat Tabel</button>
      </div>
    </form>
  </div>
</div>

<!-- Rename Table -->
<div class="modal fade" id="modalRenameTable" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning"><h5 class="modal-title">Rename Tabel</h5></div>
      <div class="modal-body">
        <label>Nama baru</label>
        <input name="new_table_name" class="form-control" required pattern="[A-Za-z0-9_]+">
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="rename_table">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <button class="btn btn-warning">Rename</button>
      </div>
    </form>
  </div>
</div>

<!-- Drop Table -->
<div class="modal fade" id="modalDropTable" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Drop Table</h5></div>
      <div class="modal-body">
        Hapus tabel <strong><?= e($selected_table) ?></strong>? Semua data akan hilang.
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="drop_table">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <button class="btn btn-danger">Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Column -->
<div class="modal fade" id="modalAddColumn" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white"><h5 class="modal-title">Tambah Kolom</h5></div>
      <div class="modal-body">
        <label>Nama Kolom</label>
        <input name="col_name" class="form-control" required pattern="[A-Za-z0-9_]+">
        <label class="mt-2">Tipe</label>
        <input name="col_type" class="form-control" placeholder="VARCHAR(255)">

        <label class="mt-2">Posisi</label>
        <select name="col_position" id="col_position" class="form-select">
            <option value="AFTER">Setelah kolom...</option>
            <option value="FIRST">Sebagai kolom pertama</option>
        </select>

        <div id="after_holder" class="mt-2">
            <label>Pilih kolom sebelumnya</label>
            <select name="col_after" class="form-select">
                <?php foreach ($table_structure as $c): ?>
                    <option value="<?= e($c['Field']) ?>"><?= e($c['Field']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="add_column">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <button class="btn btn-success">Tambah Kolom</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Column -->
<div class="modal fade" id="modalEditColumn" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning"><h5 class="modal-title">Edit Kolom</h5></div>
      <div class="modal-body">
        <label>Nama Lama</label>
        <input name="old_name" id="edit_old_name" class="form-control" readonly>
        <label class="mt-2">Nama Baru</label>
        <input name="new_name" id="edit_new_name" class="form-control" required>
        <label class="mt-2">Tipe</label>
        <input name="new_type" id="edit_new_type" class="form-control">
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="edit_column">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <button class="btn btn-warning">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Drop Column -->
<div class="modal fade" id="modalDropColumn" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus Kolom</h5></div>
      <div class="modal-body">
        Hapus kolom <strong id="drop_col_name"></strong> ?
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="drop_column">
        <input type="hidden" name="drop_field" id="drop_field">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <button class="btn btn-danger">Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Record -->
<div class="modal fade" id="modalEditRecord" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content" id="formEditRecord">
      <div class="modal-header bg-warning"><h5 class="modal-title">Edit Record</h5></div>
      <div class="modal-body" id="editRecordBody">
        <!-- fields injected by JS -->
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="edit_record">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <input type="hidden" name="pk_name" id="edit_pk_name" value="<?= e(detect_primary_key($conn,$selected_table) ?? '') ?>">
        <button class="btn btn-warning">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Record -->
<div class="modal fade" id="modalDeleteRecord" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus Record</h5></div>
      <div class="modal-body">
        Hapus record ini? <div class="mt-2"><strong id="deleteRecordInfo"></strong></div>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="action" value="delete_record">
        <input type="hidden" name="selected_table" value="<?= e($selected_table) ?>">
        <input type="hidden" name="pk_name" id="del_pk_name" value="<?= e(detect_primary_key($conn,$selected_table) ?? '') ?>">
        <input type="hidden" name="pk_value" id="del_pk_value">
        <button class="btn btn-danger">Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
    // show/hide after selector
    const pos = document.getElementById('col_position');
    if (pos) pos.addEventListener('change', function(){
        document.getElementById('after_holder').style.display = (this.value === 'AFTER' ? 'block' : 'none');
    });

    // prefill edit column modal
    var editColModal = document.getElementById('modalEditColumn');
    if (editColModal) {
        editColModal.addEventListener('show.bs.modal', function(e){
            const btn = e.relatedTarget;
            const field = btn.getAttribute('data-field');
            const type = btn.getAttribute('data-type');
            document.getElementById('edit_old_name').value = field;
            document.getElementById('edit_new_name').value = field;
            document.getElementById('edit_new_type').value = type;
        });
    }

    // prefill drop column
    var dropColModal = document.getElementById('modalDropColumn');
    if (dropColModal) {
        dropColModal.addEventListener('show.bs.modal', function(e){
            const btn = e.relatedTarget;
            const f = btn.getAttribute('data-field');
            document.getElementById('drop_col_name').innerText = f;
            document.getElementById('drop_field').value = f;
        });
    }

    // Edit record: populate fields
    var editRecordModal = document.getElementById('modalEditRecord');
    if (editRecordModal) {
        editRecordModal.addEventListener('show.bs.modal', function(e){
            const btn = e.relatedTarget;
            const rowJson = btn.getAttribute('data-row');
            let row = {};
            try { row = JSON.parse(rowJson); } catch(e){ row = {}; }
            let html = '';
            for (const k in row) {
                const val = row[k] === null ? '' : row[k];
                html += `<div class="mb-3"><label class="form-label">${k}</label>
                    <input class="form-control" name="${k}" value="${String(val).replaceAll('"','&quot;')}"></div>`;
            }
            document.getElementById('editRecordBody').innerHTML = html;
            // set pk name hidden (already set from server)
        });
    }

    // Delete record: fill pk value
    var delRecordModal = document.getElementById('modalDeleteRecord');
    if (delRecordModal) {
        delRecordModal.addEventListener('show.bs.modal', function(e){
            const btn = e.relatedTarget;
            const pk = btn.getAttribute('data-pk') || '';
            const val = btn.getAttribute('data-val') || '';
            document.getElementById('deleteRecordInfo').innerText = (pk ? (pk + ' = ' + val) : val);
            document.getElementById('del_pk_name').value = pk;
            document.getElementById('del_pk_value').value = val;
        });
    }

})();
</script>
<?php ob_end_flush(); ?>
</body>
</html>
