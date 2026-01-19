<?php
/**
 * Cuti - Delete
 */

require_once '../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Check if can delete (only pending)
    $cuti = $conn->query("SELECT * FROM cuti WHERE id = $id")->fetch_assoc();
    
    if ($cuti && $cuti['status'] == 'Pending') {
        $conn->query("DELETE FROM cuti WHERE id = $id");
    }
}

// Redirect back
header('Location: ' . BASE_URL . 'pages/cuti/index.php');
exit();
?>
