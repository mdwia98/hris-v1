<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

$id = $_POST['id_karyawan'];
$today = date("Y-m-d");
$time  = date("H:i:s");

$conn->query("
    UPDATE absensi 
    SET jam_keluar = '$time'
    WHERE karyawan_id = '$id'
    AND tanggal = '$today'
    LIMIT 1
");

header("Location: ../../dashboard.php");
exit;
