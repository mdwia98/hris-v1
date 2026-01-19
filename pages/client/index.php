<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
requireAdmin();
require_once '../../includes/header.php';

$page_title = 'Manajemen Client';
?>

<div class="content-header">
    <h1><i class="bi bi-people"></i> Manajemen Client</h1>
</div>

<!-- Action Bar -->
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between flex-wrap">

        <!-- Search -->
        <input type="text" id="searchInput" class="form-control w-25" placeholder="Cari nama client...">

        <!-- Filter Perusahaan -->
        <select id="filterPerusahaan" class="form-select w-25">
            <option value="">Semua Perusahaan</option>

            <?php 
            $companies = $conn->query("SELECT DISTINCT perusahaan FROM clients ORDER BY perusahaan");
            while ($c = $companies->fetch_assoc()):
            ?>
                <option value="<?php echo $c['perusahaan']; ?>">
                    <?php echo $c['perusahaan']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Add Button (OPEN MODAL) -->
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-circle"></i> Tambah Client
        </button>
    </div>
</div>

<!-- Table Container -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Daftar Client
    </div>
    <div class="card-body" id="dataContainer">
        <div class="text-center py-5">
            <div class="spinner-border"></div>
            <p>Mengambil data...</p>
        </div>
    </div>
</div>

<!-- CREATE / EDIT MODAL -->
<div class="modal fade" id="clientModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="clientForm">

            <input type="hidden" name="id" id="clientId">

            <div class="mb-3">
                <label>Nama Client</label>
                <input type="text" class="form-control" name="nama_client" id="nama_client" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" class="form-control" name="email" id="email">
            </div>

            <div class="mb-3">
                <label>Telepon</label>
                <input type="text" class="form-control" name="telepon" id="telepon">
            </div>

            <div class="mb-3">
                <label>Perusahaan</label>
                <input type="text" class="form-control" name="perusahaan" id="perusahaan">
            </div>

        </form>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" onclick="saveClient()">Simpan</button>
      </div>

    </div>
  </div>
</div>

<script>

// Load data awal
document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function loadData(page = 1) {
    let search = document.getElementById("searchInput").value;
    let perusahaan = document.getElementById("filterPerusahaan").value;

    fetch("client_ajax.php?page=" + page 
        + "&search=" + encodeURIComponent(search) 
        + "&perusahaan=" + encodeURIComponent(perusahaan)
    )
    .then(res => res.text())
    .then(html => {
        document.getElementById("dataContainer").innerHTML = html;
    });
}

// --- Search + Filter ---
document.getElementById("searchInput").addEventListener("keyup", () => loadData());
document.getElementById("filterPerusahaan").addEventListener("change", () => loadData());

// -----------------------
// OPEN MODAL CREATE
// -----------------------
function openCreateModal() {
    document.getElementById("modalTitle").innerHTML = "Tambah Client";
    document.getElementById("clientForm").reset();
    document.getElementById("clientId").value = "";

    new bootstrap.Modal(document.getElementById('clientModal')).show();
}

// -----------------------
// OPEN MODAL EDIT
// -----------------------
function openEditModal(id) {
    fetch("client_ajax.php?action=get&id=" + encodeURIComponent(id))
    .then(res => res.json())
    .then(data => {

        document.getElementById("modalTitle").innerHTML = "Edit Client";

        document.getElementById("clientId").value = data.id ?? "";
        document.getElementById("nama_client").value = data.nama_client ?? "";
        document.getElementById("email").value = data.email ?? "";
        document.getElementById("telepon").value = data.telepon ?? "";
        document.getElementById("perusahaan").value = data.perusahaan ?? "";

        new bootstrap.Modal(document.getElementById('clientModal')).show();
    });
}

// -----------------------
// SAVE CLIENT (CREATE / EDIT)
// -----------------------
function saveClient() {

    const form = new FormData(document.getElementById("clientForm"));
    const idVal = document.getElementById("clientId").value;

    // Tambahkan action
    if (idVal === "" || idVal === null) {
        form.append("action", "create");
    } else {
        form.append("action", "update");
    }

    fetch("client_ajax.php", {
        method: "POST",
        body: form
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);

        loadData();

        let modal = bootstrap.Modal.getInstance(document.getElementById('clientModal'));
        if (modal) modal.hide();
    });
}

// -----------------------
// DELETE
// -----------------------
function deleteClient(id) {
    if (!confirm("Yakin ingin menghapus?")) return;

    fetch("client_ajax.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: "action=delete&id=" + encodeURIComponent(id)
    })
    .then(res => res.json())
    .then(data => {

        // HAPUS BARIS LANGSUNG DARI TABEL
        let row = document.getElementById("row-" + id);
        if (row) row.remove();

        // Kemudian refresh seluruh tabel (AJAX)
        loadData();

        // Notifikasi
        console.log(data.message); // sementara, nanti bisa ganti toast
    });
}

</script>
<script>
    // Masukkan kode ini di setiap halaman menu yang berbeda
    document.title = "Data Client - HRIS BSDM";
</script>

<?php require_once '../../includes/footer.php'; ?>