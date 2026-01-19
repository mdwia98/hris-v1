<?php
function userCanAccess($menu_key, $db)
{
    if (!isset($_SESSION['user']['id'])) return false;

    $user_id = $_SESSION['user']['id'];

    $stmt = $db->prepare("
        SELECT m.id 
        FROM menus m
        JOIN user_permissions up ON up.menu_id = m.id
        WHERE up.user_id = ?
        AND m.menu_key = ?
    ");
    $stmt->bind_param("is", $user_id, $menu_key);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}
?>
