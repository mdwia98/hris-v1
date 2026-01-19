<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['file'])) {
    die("Tidak ada file diupload.");
}

// Buat folder tmp_import jika belum ada
$tmpDir = __DIR__ . "/tmp_import/";
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

// Simpan file sementara
$tmpFile = $tmpDir . time() . "_" . basename($_FILES['file']['name']);
move_uploaded_file($_FILES['file']['tmp_name'], $tmpFile);

// Load Excel
$spreadsheet = IOFactory::load($tmpFile);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

// Ambil header
$headers = array_shift($data);

require_once '../../includes/header.php';
?>

<div class="content-header">
    <h1><i class="bi bi-eye-fill"></i> Preview Import Karyawan</h1>
</div>

<div class="card">
    <div class="card-body">

        <div class="alert alert-warning">
            <i class="bi bi-info-circle"></i>
            Silakan review data berikut sebelum diimport.  
            Klik <strong>Import Sekarang</strong> untuk memasukkan ke database.
        </div>

        <form action="proses_import.php" method="POST">
            <input type="hidden" name="tmp_file" value="<?php echo $tmpFile; ?>">

            <div style="max-height: 400px; overflow:auto;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $h): ?>
                                <th><?php echo htmlspecialchars($h); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $col): ?>
                                    <td><?php echo htmlspecialchars($col); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button class="btn btn-success mt-3">
                <i class="bi bi-check-circle"></i> Import Sekarang
            </button>

            <a href="import.php" class="btn btn-secondary mt-3 ms-2">
                Kembali
            </a>
        </form>

    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
