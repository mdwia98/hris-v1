<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/logger.php';

addLog($_SESSION['user']['id'], "Delete User", "Menghapus user: $username");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_POST['id'];

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: index.php");
exit;