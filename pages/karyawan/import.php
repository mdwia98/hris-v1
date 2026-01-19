<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';

$page_title = "Upload Massal Karyawan";
?>

<div class="content-header">
    <h1><i class="bi bi-upload"></i> Upload Massal Karyawan</h1>
</div>

<div class="card">
    <div class="card-body">

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>  
            Upload file Excel dengan format kolom seperti pada template berikut:  
            <a href="sample_karyawan.xlsx" class="btn btn-sm btn-primary ms-2">
                <i class="bi bi-file-earmark-excel"></i> Download Template
            </a>
        </div>

        <form action="preview_import.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Pilih File Excel (.xlsx)</label>
        <input type="file" name="file" class="form-control" required accept=".xlsx">
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-eye"></i> Preview Data
    </button>

    <a href="index.php" class="btn btn-secondary ms-2">Kembali</a>
</form>

    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
