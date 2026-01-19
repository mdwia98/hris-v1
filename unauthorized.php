<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="text-center">
        <h1 class="text-danger"><i class="bi bi-shield-exclamation"></i> Akses Ditolak</h1>
        <p>Kamu tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
    </div>
</body>
</html>
