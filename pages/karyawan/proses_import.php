<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_POST['tmp_file'])) {
    die("Tidak ada file untuk diimport.");
}

$tmpFile = $_POST['tmp_file'];

if (!file_exists($tmpFile)) {
    die("File sementara tidak ditemukan. Silakan ulangi upload.");
}

// Load Excel
$spreadsheet = IOFactory::load($tmpFile);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

// Buang header
array_shift($data);

// User login
$user_id = $_SESSION['user']['id'];
$inserted = 0;
$failed = 0;

foreach ($data as $row) {

    $nip              = trim($row[0]);
    $nama             = trim($row[1]);
    $email            = trim($row[2]);
    $telepon          = trim($row[3]);
    $alamat           = trim($row[4]);
    $tanggal_lahir    = trim($row[5]);
    $jenis_kelamin    = trim($row[6]);
    $departemen       = trim($row[7]);
    $posisi           = trim($row[8]);
    $status_kerja     = trim($row[9]);
    (int)$gaji        = trim($row[10]);
    $tanggal_join     = trim($row[11]);
    $foto             = trim($row[12]);

    if ($nip == "" || $nama == "") {
        $failed++;
        continue;
    }

    $stmt = $conn->prepare("
    INSERT INTO karyawan (
        user_id, nip, nama, email, telepon, alamat, tanggal_lahir,
        jenis_kelamin, departemen, posisi, status_kerja, gaji,
        tanggal_join, foto, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

    if (!$stmt) {
        $failed++;
        continue;
    }

    $stmt->bind_param(
        "isssssssssssss",
        $user_id,
        $nip,
        $nama,
        $email,
        $telepon,
        $alamat,
        $tanggal_lahir,
        $jenis_kelamin,
        $departemen,
        $posisi,
        $status_kerja,
        $gaji,
        $tanggal_join,
        $foto
    );

    if ($stmt->execute()) {
        $inserted++;
    } else {
        $failed++;
    }

    $stmt->close();
}

// Hapus file sementara
unlink($tmpFile);

// Redirect
header("Location: index.php?import_success=$inserted&import_failed=$failed");
exit;
