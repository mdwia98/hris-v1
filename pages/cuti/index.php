<?php
/**
 * Cuti - List Page (Versi Final: keamanan, paginasi, logging, role-check)
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';
require_once '../../includes/header.php';

$page_title = 'Manajemen Cuti';

// --- Ambil dan sanitasi input ---
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Pagination
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit  = 10;
$offset = ($page - 1) * $limit;

// Ambil role & karyawan_id dari session (sudah aman karena includes/session.php memaksa login)
$user_role   = $_SESSION['user']['role'] ?? '';
$karyawan_id = isset($_SESSION['user']['karyawan_id']) ? intval($_SESSION['user']['karyawan_id']) : null;

// --- Build where clauses & params untuk prepared statements ---
$where_clauses = [];
$params = [];    // values
$types  = '';    // types for bind_param

// Jika user adalah karyawan -> batasi hasil hanya milik dirinya
if ($user_role === 'karyawan') {
    if (empty($karyawan_id)) {
        // session inconsistency — hentikan dan beri pesan
        die("Error: karyawan_id tidak ditemukan dalam session. Silakan hubungi administrator.");
    }
    $where_clauses[] = "c.karyawan_id = ?";
    $types .= 'i';
    $params[] = $karyawan_id;
}

// Filter status jika diset
$allowed_status = ['Pending','Disetujui','Ditolak','']; // '' berarti semua
if ($filter_status !== '') {
    // Validasi status masuk akal
    if (!in_array($filter_status, $allowed_status)) {
        $filter_status = ''; // ignore jika tak valid
    } else {
        $where_clauses[] = "c.status = ?";
        $types .= 's';
        $params[] = $filter_status;
    }
}

// Gabungkan WHERE
$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// -----------------------------
// HITUNG TOTAL UNTUK PAGINATION
// -----------------------------
$count_sql = "SELECT COUNT(*) AS total FROM cuti c
              JOIN karyawan k ON c.karyawan_id = k.id
              $where_sql";

$count_stmt = $conn->prepare($count_sql);
if ($count_stmt === false) {
    die("Prepare failed (count): " . $conn->error);
}
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_res = $count_stmt->get_result()->fetch_assoc();
$totalRows = intval($count_res['total'] ?? 0);
$count_stmt->close();

$totalPages = (int) ceil($totalRows / $limit);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit; // recompute in case page adjusted

// -----------------------------
// AMBIL DATA CUTI (WITH PAGINATION)
// -----------------------------
$data_sql = "
    SELECT c.*, k.nama, k.nip 
    FROM cuti c
    JOIN karyawan k ON c.karyawan_id = k.id
    $where_sql
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
";

$data_stmt = $conn->prepare($data_sql);
if ($data_stmt === false) {
    die("Prepare failed (data): " . $conn->error);
}

// bind params (existing params + limit & offset)
if (!empty($params)) {
    // types + two integers for limit & offset
    $bind_types = $types . 'ii';
    $bind_values = array_merge($params, [$limit, $offset]);
    $data_stmt->bind_param($bind_types, ...$bind_values);
} else {
    // only limit & offset
    $data_stmt->bind_param('ii', $limit, $offset);
}

$data_stmt->execute();
$result = $data_stmt->get_result();

// -----------------------------
// SUMMARY (counts) — ikut filter karyawan, namun tidak ikut filter status
// Jika Anda ingin summary ikut filter status, ubah query berikut sesuai kebutuhan
// -----------------------------
$summary_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as rejected
    FROM cuti c
";

$summary_params = [];
$summary_types = '';

if ($user_role === 'karyawan') {
    $summary_sql .= " WHERE karyawan_id = ?";
    $summary_types .= 'i';
    $summary_params[] = $karyawan_id;
}

$summary_stmt = $conn->prepare($summary_sql);
if ($summary_stmt === false) {
    die("Prepare failed (summary): " . $conn->error);
}
if (!empty($summary_params)) {
    $summary_stmt->bind_param($summary_types, ...$summary_params);
}
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
$summary_stmt->close();

// -----------------------------
// LOG: halaman diakses (optional, untuk audit)
// -----------------------------
writeLog('cuti', 'view', 'Melihat daftar cuti' . ($filter_status ? " status=$filter_status" : ''));

// -----------------------------
// RENDER HTML
// -----------------------------
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-calendar-x"></i> Manajemen Cuti</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars(BASE_URL); ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Cuti</li>
        </ol>
    </nav>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card yellow">
            <div class="stat-number"><?php echo intval($summary['total'] ?? 0); ?></div>
            <div class="stat-label">Total Permintaan</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo intval($summary['pending'] ?? 0); ?></div>
            <div class="stat-label">Menunggu</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo intval($summary['approved'] ?? 0); ?></div>
            <div class="stat-label">Disetujui</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card dark">
            <div class="stat-number"><?php echo intval($summary['rejected'] ?? 0); ?></div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>
</div>

<!-- Filter & Action -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2" role="search" aria-label="Filter Cuti">
                    <select name="status" class="form-select" aria-label="Filter status">
                        <option value="">Semua Status</option>
                        <option value="Pending" <?php echo $filter_status == 'Pending' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Disetujui" <?php echo $filter_status == 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="Ditolak" <?php echo $filter_status == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>pages/cuti/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajukan Cuti
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Permintaan Cuti
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Durasi (Hari)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()): 
                        // Pastikan tanggal valid
                        $tgl_mulai = $row['tanggal_mulai'] ?? null;
                        $tgl_selesai = $row['tanggal_selesai'] ?? null;

                        $durasi = '-';
                        if ($tgl_mulai && $tgl_selesai) {
                            $start = strtotime($tgl_mulai);
                            $end = strtotime($tgl_selesai);
                            if ($start !== false && $end !== false) {
                                $durasi = (($end - $start) / (60 * 60 * 24)) + 1;
                                $durasi = intval($durasi);
                            }
                        }

                        // Escape output
                        $nama = htmlspecialchars($row['nama'] ?? '-');
                        $jenis = htmlspecialchars($row['jenis_cuti'] ?? '-');
                        $status = htmlspecialchars($row['status'] ?? '-');
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $nama; ?></strong></td>
                        <td><?php echo $jenis; ?></td>
                        <td><?php echo $tgl_mulai ? date('d M Y', strtotime($tgl_mulai)) : '-'; ?></td>
                        <td><?php echo $tgl_selesai ? date('d M Y', strtotime($tgl_selesai)) : '-'; ?></td>
                        <td><?php echo is_int($durasi) ? $durasi . ' hari' : '-'; ?></td>
                        <td>
                            <?php
                            $status_class_map = [
                                'Pending' => 'bg-warning text-dark',
                                'Disetujui' => 'bg-success',
                                'Ditolak' => 'bg-danger'
                            ];
                            $badge_class = $status_class_map[$row['status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars(BASE_URL); ?>pages/cuti/detail.php?id=<?php echo intval($row['id']); ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Lihat
                            </a>

                            <?php 
                            // Tampilkan tombol Edit/Hapus hanya bila status = Pending
                            // dan user adalah admin/manajer atau owner (karyawan yang mengajukan)
                            $is_pending = ($row['status'] === 'Pending');
                            $is_owner = ($user_role === 'karyawan' && intval($row['karyawan_id']) === $karyawan_id);
                            $is_admin_or_manager = in_array($user_role, ['admin','manajer']);
                            if ($is_pending && ($is_admin_or_manager || $is_owner)): 
                            ?>
                                <a href="<?php echo htmlspecialchars(BASE_URL); ?>pages/cuti/edit.php?id=<?php echo intval($row['id']); ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="<?php echo htmlspecialchars(BASE_URL); ?>pages/cuti/delete.php?id=<?php echo intval($row['id']); ?>" class="btn btn-sm btn-danger" data-action="delete">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalRows > $limit): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?status=<?php echo urlencode($filter_status); ?>&page=<?php echo max(1, $page - 1); ?>">Sebelumnya</a>
                </li>

                <?php
                // tampilkan maksimal 7 nomor halaman (sliding window)
                $maxLinks = 7;
                $start = max(1, $page - intval($maxLinks/2));
                $end = min($totalPages, $start + $maxLinks - 1);
                if ($end - $start + 1 < $maxLinks) {
                    $start = max(1, $end - $maxLinks + 1);
                }
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?status=<?php echo urlencode($filter_status); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?status=<?php echo urlencode($filter_status); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Berikutnya</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</div>

<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Data Cuti - HRIS BSDM";
</script>

<?php
// close statement(s)
$data_stmt->close();
$data_stmt = null;

require_once '../../includes/footer.php';
?>
