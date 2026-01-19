<?php
/**
 * Generate Slip Gaji - PDF (FINAL + SECURE + HIDE ZERO + LOGO)
 */

ob_start();
date_default_timezone_set('Asia/Jakarta');

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../vendor/autoload.php';
require_once '../../includes/logger.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ==============================
// VALIDASI LOGIN
// ==============================
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// ==============================
// DATA USER LOGIN
// ==============================
$user_role = $_SESSION['user']['role'];
$karyawan_id   = $_SESSION['user']['karyawan_id']; // diasumsikan = karyawan_id

// ==============================
// VALIDASI PARAMETER
// ==============================
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('ID slip gaji tidak valid');
}

// ==============================
// QUERY DENGAN OTORISASI ROLE
// ==============================
$sql = "
    SELECT 
        g.*,
        k.nama,
        k.nip,
        k.posisi,
        k.departemen,
        k.status_ptkp,
        k.penempatan,
        k.nomor_rekening,
        k.nama_bank,
        g.hari_kerja
    FROM gaji g
    LEFT JOIN karyawan k ON g.karyawan_id = k.id
    WHERE g.id = ?
";

$params = [$id];
$types  = "i";

// Jika role karyawan → hanya boleh akses slip sendiri
if ($user_role === 'karyawan') {
    $sql .= " AND g.karyawan_id = ?";
    $params[] = $karyawan_id;
    $types   .= "i";
}

$query = $conn->prepare($sql);
$query->bind_param($types, ...$params);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    exit('Akses ditolak');
}

$slip = $result->fetch_assoc();

// ==============================
// HITUNG TOTAL
// ==============================
$total_kotor     = $slip['gaji_pokok'] + $slip['tunjangan'] + $slip['bonus'];
$total_potongan  = $slip['potongan'];

// ==============================
// LOG AKTIVITAS
// ==============================
writeLog(
    'Gaji',
    'Generate Slip PDF',
    "User {$user_id} ({$user_role}) mencetak slip ID {$slip['id']} untuk {$slip['nama']}"
);

// ==============================
// SETUP PDF
// ==============================
$options = new Options();
$options->set('isRemoteEnabled', true);
$pdf = new Dompdf($options);

// ==============================
// LOGO & PERIODE
// ==============================
$logoURL = "http://" . $_SERVER['HTTP_HOST'] . "/bsdmv2/assets/img/logo.png";
$periode = date("F Y", strtotime($slip['tahun'] . "-" . $slip['bulan'] . "-01"));

// ==============================
// ALLOWANCE (HIDE ZERO)
// ==============================
$allowanceRows = "";

if ($slip['gaji_pokok'] != 0)
    $allowanceRows .= "<tr><td>Gaji Pokok</td><td>Rp " . number_format($slip['gaji_pokok'], 0, ',', '.') . "</td></tr>";

if ($slip['tunjangan'] != 0)
    $allowanceRows .= "<tr><td>Tunjangan</td><td>Rp " . number_format($slip['tunjangan'], 0, ',', '.') . "</td></tr>";

if ($slip['bonus'] != 0)
    $allowanceRows .= "<tr><td>Bonus</td><td>Rp " . number_format($slip['bonus'], 0, ',', '.') . "</td></tr>";

if ($allowanceRows === "") {
    $allowanceRows = "<tr><td colspan='2' style='text-align:center;color:#777'>Tidak ada data</td></tr>";
}

// ==============================
// DEDUCTION (HIDE ZERO)
// ==============================
$deductionRows = "";

if ($slip['potongan'] != 0)
    $deductionRows .= "<tr><td>Potongan</td><td>Rp " . number_format($slip['potongan'], 0, ',', '.') . "</td></tr>";

if ($deductionRows === "") {
    $deductionRows = "<tr><td colspan='2' style='text-align:center;color:#777'>Tidak ada potongan</td></tr>";
}

// ==============================
// HTML PDF
// ==============================
$html = "
<style>
body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
.header { text-align: center; margin-top: 50px; margin-bottom: 10px; }
.logo { width: 90px; margin-bottom: 10px; }
.company-name { font-size: 22px; font-weight: bold; }
.company-address { font-size: 12px; color: #555; }
.title { font-size: 20px; font-weight: bold; margin-top: 10px; }
.section-title { margin-top: 20px; font-size: 15px; font-weight: bold; border-bottom: 2px solid #444; padding-bottom: 5px; }
.table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #999; padding: 8px; }
th { background: #efefef; text-align: left; }
.column-wrapper { width: 100%; display: table; }
.col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
.col-right { padding-left: 10px; padding-right: 0; }
.total-row { background: #e3f7e3; font-weight: bold; }
.footer { margin-top: 30px; font-size: 11px; text-align: center; color: #777; }
</style>

<div class='header'>
    <img class='logo' src='{$logoURL}'>
    <div class='company-name'>PT. BANGUN SUMBER DAYA MANDIRI</div>
    <div class='company-address'>Ruko Mitra Matraman Block C Kav No.3 Jl. Matraman Raya No. 148 Jakarta Timur 13150</div>
    <div class='company-address'>Telp: (021) 2101 5533 / Email: bsdm.info@gmail.com</div>
    <hr>
    <div class='title'>SLIP GAJI KARYAWAN</div>
</div>

<div class='section-title'>Informasi Karyawan</div>

<div class='column-wrapper'>
<div class='col'>
<table class='table'>
<tr><th>NIP</th><td>{$slip['nip']}</td></tr>
<tr><th>Nama</th><td>{$slip['nama']}</td></tr>
<tr><th>Penempatan</th><td>{$slip['penempatan']}</td></tr>
<tr><th>Departemen</th><td>{$slip['departemen']}</td></tr>
<tr><th>Jabatan</th><td>{$slip['posisi']}</td></tr>
</table>
</div>

<div class='col col-right'>
<table class='table'>
<tr><th>Periode</th><td>{$periode}</td></tr>
<tr><th>Status PTKP</th><td>{$slip['status_ptkp']}</td></tr>
<tr><th>No Rekening</th><td>{$slip['nomor_rekening']}</td></tr>
<tr><th>Nama Bank</th><td>{$slip['nama_bank']}</td></tr>
<tr><th>Hari Kerja</th><td>{$slip['hari_kerja']}</td></tr>
</table>
</div>
</div>

<div class='section-title'>Rincian Gaji</div>

<div class='column-wrapper'>
<div class='col'>
<table class='table'>
<tr><th colspan='2' style='text-align:center'>Allowance</th></tr>
$allowanceRows
<tr class='total-row'>
<td>Total Gaji Kotor</td>
<td>Rp " . number_format($total_kotor, 0, ',', '.') . "</td>
</tr>
</table>
</div>

<div class='col col-right'>
<table class='table'>
<tr><th colspan='2' style='text-align:center'>Deduction</th></tr>
$deductionRows
<tr class='total-row'>
<td>Total Potongan</td>
<td>Rp " . number_format($total_potongan, 0, ',', '.') . "</td>
</tr>
</table>
</div>
</div>

<table class='table' style='margin-top:20px;'>
<tr class='total-row'>
<th>Total Gaji Bersih</th>
<td>Rp " . number_format($slip['total_gaji'], 0, ',', '.') . "</td>
</tr>
</table>

<div class='footer'>
Slip ini dicetak otomatis oleh Sistem HRIS BSDM.<br>
Dokumen ini sah meskipun tanpa tanda tangan.
</div>
";

// ==============================
// GENERATE PDF
// ==============================
$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();

$filename = "Slip-Gaji-{$slip['bulan']}-{$slip['tahun']}-{$slip['nama']}-" . date('Y-m-d H_i_s') . ".pdf";
$pdf->stream($filename, ["Attachment" => false]);
exit;
