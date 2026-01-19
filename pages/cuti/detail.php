<?php
/**
 * Cuti - Detail Page
 */

require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/header.php';

$page_title = 'Detail Cuti';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'pages/cuti/index.php');
    exit();
}

// Ambil data cuti
$cuti = $conn->query("
    SELECT c.*, file_cuti, k.nama, k.nip, k.email, k.departemen, k.posisi
    FROM cuti c
    JOIN karyawan k ON c.karyawan_id = k.id
    WHERE c.id = $id
")->fetch_assoc();

if (!$cuti) {
    header('Location: ' . BASE_URL . 'pages/cuti/index.php');
    exit();
}

$durasi = (strtotime($cuti['tanggal_selesai']) - strtotime($cuti['tanggal_mulai'])) / (60 * 60 * 24) + 1;
?>

<!-- Page Header -->
<div class="content-header">
    <h1><i class="bi bi-calendar-x"></i> Detail Permintaan Cuti</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-text"></i> Informasi Cuti
            </div>
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label><strong>Nama Karyawan</strong></label>
                        <p><?= htmlspecialchars($cuti['nama']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label><strong>NIP</strong></label>
                        <p><?= htmlspecialchars($cuti['nip']); ?></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label><strong>Departemen</strong></label>
                        <p><?= htmlspecialchars($cuti['departemen']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label><strong>Posisi</strong></label>
                        <p><?= htmlspecialchars($cuti['posisi']); ?></p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label><strong>Jenis Cuti</strong></label>
                        <p><?= htmlspecialchars($cuti['jenis_cuti']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label><strong>Durasi</strong></label>
                        <p><?= $durasi; ?> hari</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label><strong>Tanggal Mulai</strong></label>
                        <p><?= date('d F Y', strtotime($cuti['tanggal_mulai'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label><strong>Tanggal Selesai</strong></label>
                        <p><?= date('d F Y', strtotime($cuti['tanggal_selesai'])); ?></p>
                    </div>
                </div>

                <div class="mb-3">
                    <label><strong>Alasan</strong></label>
                    <p><?= nl2br(htmlspecialchars($cuti['alasan'])); ?></p>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label><strong>Status</strong></label>
                        <?php
                        $status_class = [
                            'Pending' => 'bg-warning text-dark',
                            'Disetujui' => 'bg-success',
                            'Ditolak' => 'bg-danger'
                        ];
                        ?>
                        <p>
                            <span id="status_badge" class="badge <?= $status_class[$cuti['status']] ?? 'bg-secondary'; ?>">
                                <?= htmlspecialchars($cuti['status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label><strong>Tanggal Pengajuan</strong></label>
                        <p><?= date('d F Y H:i', strtotime($cuti['created_at'])); ?></p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label><strong>File Cuti</strong></label>
                        <p>
                            <?php if (!empty($cuti['file_cuti']) && file_exists('../../pages/cuti/' . $cuti['file_cuti'])): ?>
                                <a href="<?= BASE_URL; ?>pages/cuti/<?= htmlspecialchars($cuti['file_cuti']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-download"></i> Unduh File
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Tidak ada file tersedia.</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-check-circle"></i> Aksi
            </div>
            <div class="card-body">

                <a href="<?= BASE_URL; ?>pages/cuti/index.php" class="btn btn-secondary w-100 mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>

                <?php if (
                    $cuti['status'] === 'Pending' &&
                    isset($_SESSION['user']['role']) &&
                    in_array($_SESSION['user']['role'], ['admin', 'manajer'])
                ): ?>
                    <button type="button" class="btn btn-success w-100 mb-2" id="btnApprove" data-id="<?= $cuti['id']; ?>">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>

                    <button type="button" class="btn btn-danger w-100" id="btnReject" data-id="<?= $cuti['id']; ?>">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body" style="font-size: 0.9rem;">

                <p><strong>ID Cuti:</strong> #<?= $cuti['id']; ?></p>
                <p><strong>Email Karyawan:</strong> <?= htmlspecialchars($cuti['email']); ?></p>
                <p><strong>Diajukan:</strong> <?= date('d M Y H:i', strtotime($cuti['created_at'])); ?></p>

                <div id="info_approver">
                    <?php if ($cuti['disetujui_oleh']): ?>
                        <?php
                        $ap = $conn->query("
                                    SELECT 
                                        COALESCE(k.nama, u.username) AS approver_name
                                    FROM users u
                                    LEFT JOIN karyawan k ON u.id = k.user_id
                                    WHERE u.id = {$cuti['disetujui_oleh']}
                                    ")->fetch_assoc();?>
                        <p><strong>Disetujui Oleh:</strong> <?= $ap['approver_name'] ?? '-'; ?></p>
                        <p><strong>Tanggal Disetujui:</strong>
                            <?= $cuti['tanggal_disetujui'] ? date('d M Y H:i', strtotime($cuti['tanggal_disetujui'])) : '-'; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AJAX -->
<script>
$(document).ready(function () {

    // Fungsi utama approve / reject
    function approveReject(action, id) {

        $.ajax({
            url: "approve_ajax.php",
            type: "POST",
            data: {
                id: id,
                action: action
            },
            dataType: "json",

            success: function (res) {

                if (res.status === "success") {

                    // -- Update Badge Status --
                    let badge = $("#status_badge");

                    badge.removeClass("bg-warning bg-success bg-danger");

                    if (res.new_status === "Disetujui") {
                        badge.addClass("bg-success").text("Disetujui");
                    } else {
                        badge.addClass("bg-danger").text("Ditolak");
                    }

                    // -- Update bagian approver --
                    $("#info_approver").html(`
                        <p><strong>Disetujui Oleh:</strong> ${res.approver ?? '-'}</p>
                        <p><strong>Tanggal Disetujui:</strong> ${res.tanggal_disetujui ?? '-'}</p>
                    `);

                    // -- Hapus tombol Approve/Reject --
                    $("#btnApprove").remove();
                    $("#btnReject").remove();

                    // -- Tampilkan Toast Notifikasi --
                    const toastEl = document.getElementById("notifToast");
                    const toastMsg = document.getElementById("notifMessage");

                    toastMsg.innerText = res.message;
                    new bootstrap.Toast(toastEl).show();

                } else {

                    // Jika gagal (status error dari PHP)
                    alert(res.message ?? "Terjadi kesalahan.");
                }
            },

            error: function (xhr, status, error) {
                console.log(error);
                alert("Terjadi kesalahan pada server.");
            }
        });

    }

    $(document).ready(function () {

    let selectedAction = "";
    let selectedId = 0;

    function openConfirmation(action, id) {
        selectedAction = action;
        selectedId = id;

        let msg = (action === "approve") 
            ? "Yakin ingin menyetujui cuti ini?"
            : "Yakin ingin menolak cuti ini?";

        $("#modalKonfirmasiMessage").text(msg);

        // tampilkan modal
        let modal = new bootstrap.Modal(document.getElementById('modalKonfirmasi'));
        modal.show();
    }

    // Klik tombol Approve
    $("#btnApprove").on("click", function () {
        openConfirmation("approve", $(this).data("id"));
    });

    // Klik tombol Reject
    $("#btnReject").on("click", function () {
        openConfirmation("reject", $(this).data("id"));
    });

    // Tombol OK pada modal
    $("#btnKonfirmasiOK").on("click", function () {
        approveReject(selectedAction, selectedId);

        // tutup modal
        bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasi')).hide();
    });

});
});
</script>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="notifToast" class="toast bg-primary text-white" role="alert">
        <div class="toast-body" id="notifMessage">Notifikasi</div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="modalKonfirmasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="modalKonfirmasiMessage">
                Apakah Anda yakin?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnKonfirmasiOK">Ya, Lanjutkan</button>
            </div>

        </div>
    </div>
</div>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Detail Cuti - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>

