<?php
ob_clean();
header('Content-Type: application/json');
require_once '../../config/database.php';

// DataTables variables
$draw   = $_POST['draw'] ?? 1;
$start  = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;

// Filters
$module = $conn->real_escape_string($_POST['module'] ?? '');
$action = $conn->real_escape_string($_POST['action'] ?? '');
$from   = $conn->real_escape_string($_POST['from'] ?? '');
$to     = $conn->real_escape_string($_POST['to'] ?? '');

// Base query
$query = "FROM hris_logs sl 
          WHERE 1=1";

// Apply filters
if ($module !== '') $query .= " AND sl.module = '$module'";
if ($action !== '') $query .= " AND sl.action = '$action'";
if ($from !== '' && $to !== '') $query .= " AND DATE(sl.created_at) BETWEEN '$from' AND '$to'";

// Count total
$totalQuery = $conn->query("SELECT COUNT(*) AS total $query");
$totalData = $totalQuery->fetch_assoc()['total'];

// Retrieve data
$dataQuery = $conn->query("
    SELECT sl.* $query
    ORDER BY sl.created_at DESC
    LIMIT $start, $length
");

$data = [];
while ($row = $dataQuery->fetch_assoc()) {

    // Jika user_id ada, ambil dari tabel users
    if (!empty($row['user_id'])) {
        $u = $conn->query("SELECT username FROM users WHERE id = {$row['user_id']} LIMIT 1");
        $uData = $u->fetch_assoc();
        $username = $uData['username'] ?? $row['username'];
    } else {
        $username = $row['username'] ?: "Unknown";
    }

    $created = $row['created_at']
               ? date('d-m-Y H:i:s', strtotime($row['created_at']))
               : '-';

    $data[] = [
    $row['id'],
    $created,
    $username,
    ucfirst($row['module']),
    ucfirst($row['action']),
    $row['detail'] ?: '-'  // hanya satu kolom
];
}

echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalData),
    "data" => $data
]);
?>
