<?php
$user_id = $_SESSION['user']['id'];

$menus = $db->query("
    SELECT m.* 
    FROM menus m
    JOIN user_permissions up ON up.menu_id = m.id
    WHERE up.user_id = $user_id
    ORDER BY m.sort_order ASC
");
?>

<ul class="sidebar-menu">
    <?php while ($m = $menus->fetch_assoc()): ?>
        <li>
            <a href="<?= $m['url']; ?>">
                <i class="<?= $m['icon']; ?>"></i>
                <span><?= $m['menu_name']; ?></span>
            </a>
        </li>
    <?php endwhile; ?>
</ul>
