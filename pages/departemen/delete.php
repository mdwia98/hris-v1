<?php
/**
 * Departemen - Delete
 */

require_once '../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Check if department has employees
    $count = $conn->query("SELECT COUNT(*) as count FROM karyawan WHERE departemen IN (SELECT nama_departemen FROM departemen WHERE id = $id)")->fetch_assoc()['count'];
    
    // Only delete if no employees
    if ($count == 0) {
        $conn->query("DELETE FROM departemen WHERE id = $id");
    }
}

// Redirect back
header('Location: ' . BASE_URL . 'pages/departemen/index.php');
exit();
?>
