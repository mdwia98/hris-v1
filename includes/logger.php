<?php
/**
 * HRIS - Centralized Logger (MySQL)
 */

require_once __DIR__ . '/../config/database.php';

function writeLog($module, $action, $detail = null)
{
    global $conn;

    if (!isset($_SESSION)) session_start();

    $user_id  = $_SESSION['user']['id'] ?? null;
    $username = $_SESSION['user']['username'] ?? null;
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("
        INSERT INTO hris_logs (user_id, username, module, action, detail, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssss", $user_id, $username, $module, $action, $detail, $ip);
    $stmt->execute();
}

function addLog($user_id, $action, $detail) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, detail) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $detail);
    $stmt->execute();
}
