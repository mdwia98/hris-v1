<?php
/**
 * Absensi - Delete
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $conn->query("DELETE FROM absensi WHERE id = $id");
}

// Redirect back
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . 'pages/absensi/index.php';
header('Location: ' . $referer);
exit();
?>
