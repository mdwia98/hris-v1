<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

$id = $_POST['id'];
$today = date("Y-m-d");
$time  = date("H:i:s");

$conn->query("
    INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, status)
    VALUES ('$id', '$today', '$time', 'Hadir')
");

header("Location: ../../dashboard.php");
exit;
