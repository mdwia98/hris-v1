<?php

// Ambil Data Tabel (LIMIT 100)
$limit = 100;

$dataQuery = $conn->query("SELECT * FROM `$selected_table` LIMIT $limit");

$columns = [];
if ($dataQuery) {
    $fields = $dataQuery->fetch_fields();
    foreach ($fields as $f) {
        $columns[] = $f->name;
    }
}

?>
<h4>Data Tabel: <?= $selected_table ?></h4>

<?php if (empty($columns)): ?>
    <div class="alert alert-warning">Tabel tidak memiliki data.</div>
<?php else: ?>

<div class="table-responsive mt-3">
<table class="table table-bordered table-striped table-sm">
    <thead class="table-dark">
        <tr>
            <?php foreach ($columns as $col): ?>
                <th><?= $col ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $dataQuery->fetch_assoc()): ?>
        <tr>
            <?php foreach ($columns as $col): ?>
                <td><?= htmlspecialchars($row[$col]) ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<p class="text-muted">
Menampilkan maksimal <?= $limit ?> baris pertama.
</p>

<?php endif; ?>
