<?php
/**
 * Footer Include
 */
?>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 HRIS BSDM. Sistem Manajemen Sumber Daya Manusia Profesional.</p>
        <p>
            Dikembangkan dengan <i class="bi bi-heart-fill" style="color: var(--secondary-color);"></i> 
            oleh Tim IT BSDM.
        </p>
    </footer>

    <!-- Bootstrap JS (Satu Kali Saja) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
<script>
// =============================
// Session Timeout Warning + Countdown
// =============================
const SESSION_TIMEOUT = 15 * 60 * 1000; // 15 menit dalam ms
const WARNING_TIME = 60 * 1000;         // 1 menit sebelum timeout
let warningTimer, logoutTimer, countdownInterval;
let timeRemaining = WARNING_TIME / 1000; // detik tersisa saat popup muncul

function startSessionTimers() {
    clearTimeout(warningTimer);
    clearTimeout(logoutTimer);

    // Tampilkan popup 1 menit sebelum sesi habis
    warningTimer = setTimeout(showSessionWarning, SESSION_TIMEOUT - WARNING_TIME);

    // Logout otomatis jika tidak respon
    logoutTimer = setTimeout(() => {
        window.location.href = BASE_URL + 'logout.php';
    }, SESSION_TIMEOUT);
}

function showSessionWarning() {
    timeRemaining = WARNING_TIME / 1000; // reset countdown ke 60 detik
    updateCountdownDisplay();

    const modal = new bootstrap.Modal(document.getElementById('sessionWarningModal'));
    modal.show();

    // Jalankan countdown detik demi detik
    countdownInterval = setInterval(() => {
        timeRemaining--;
        updateCountdownDisplay();
        if (timeRemaining <= 0) {
            clearInterval(countdownInterval);
            window.location.href = BASE_URL + 'logout.php';
        }
    }, 1000);
}

function updateCountdownDisplay() {
    const el = document.getElementById('countdownTimer');
    if (el) el.textContent = timeRemaining;
}

// Tombol perpanjang sesi
async function extendSession() {
    try {
        const response = await fetch(window.location.pathname + '?action=extend');
        const result = await response.json();
        console.log('Session diperpanjang:', result.time);

        // Tutup modal, reset countdown & timer
        const modal = bootstrap.Modal.getInstance(document.getElementById('sessionWarningModal'));
        modal.hide();
        clearInterval(countdownInterval);
        startSessionTimers();
    } catch (err) {
        console.error('Gagal memperpanjang sesi:', err);
        window.location.href = BASE_URL + 'logout.php';
    }
}

// Tombol logout manual
function endSession() {
    clearInterval(countdownInterval);
    window.location.href = BASE_URL + 'logout.php';
}

document.addEventListener('DOMContentLoaded', startSessionTimers);
</script>

<!-- =============================
     Modal Peringatan Timeout + Countdown
     ============================= -->
<div class="modal fade" id="sessionWarningModal" tabindex="-1" aria-labelledby="sessionWarningLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning shadow">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="sessionWarningLabel">
          <i class="bi bi-exclamation-triangle"></i> Sesi Akan Berakhir
        </h5>
      </div>
      <div class="modal-body text-center">
        <p>Sesi Anda akan berakhir dalam <strong><span id="countdownTimer">60</span> detik</strong> karena tidak ada aktivitas.</p>
        <p>Apakah Anda ingin memperpanjang sesi?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button class="btn btn-danger" onclick="endSession()">
          <i class="bi bi-box-arrow-right"></i> Logout Sekarang
        </button>
        <button class="btn btn-success" onclick="extendSession()">
          <i class="bi bi-arrow-repeat"></i> Perpanjang Sesi
        </button>
      </div>
    </div>
  </div>
</div>
<!-- Page Loader Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const loader = document.getElementById('page-loader');
    
    // Sembunyikan loader setelah halaman siap
    window.addEventListener('load', function() {
        setTimeout(() => {
            loader.classList.add('hidden');
        }, 300); // delay kecil agar transisi lebih halus
    });

    // Tampilkan loader setiap klik link/menu
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                loader.classList.remove('hidden');
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Loader effect
    const loader = document.getElementById('page-loader');
    setTimeout(() => loader.classList.add('hidden'), 500);

    // Sidebar toggle
    const toggleBtn = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const icon = toggleBtn.querySelector('i');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('expanded');

        // Ganti ikon dinamis
        if (sidebar.classList.contains('hidden')) {
            icon.classList.remove('bi-list');
            icon.classList.add('bi-arrow-right-square');
        } else {
            icon.classList.remove('bi-arrow-right-square');
            icon.classList.add('bi-list');
        }
    });
});
</script>
<script>
/* Show / Hide Password */
document.querySelectorAll('.password-toggle').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');

        if (target.type === 'password') {
            target.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            target.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    });
});

// ========== SCRIPT VALIDASI PASSWORD DENGAN CEK ELEMENT ==========
{
    let newPass = document.getElementById('new_password');
    let confirmPass = document.getElementById('confirm_password');
    let strengthBar = document.getElementById('password-strength-bar');
    let strengthText = document.getElementById('password-strength-text');
    let confirmText = document.getElementById('confirm-text');
    let btnSave = document.getElementById('btn-save');

    // Hanya dijalankan bila semua elemen tersedia
    if (newPass && confirmPass && strengthBar && strengthText && confirmText && btnSave) {

        newPass.addEventListener('input', function () {
            let val = newPass.value;
            let strength = 0;

            if (val.length >= 6) strength++;
            if (val.match(/[A-Z]/)) strength++;
            if (val.match(/[0-9]/)) strength++;
            if (val.match(/[^A-Za-z0-9]/)) strength++;

            if (val.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'progress-bar';
                strengthText.textContent = '';
                return;
            }

            if (strength <= 1) {
                strengthBar.style.width = '33%';
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak';
            } else if (strength <= 3) {
                strengthBar.style.width = '66%';
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Medium';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Strong';
            }
        });

        function validatePasswordMatch() {
            if (confirmPass.value.length === 0) {
                confirmText.textContent = '';
                return;
            }

            if (confirmPass.value === newPass.value) {
                confirmText.textContent = 'Password cocok ✔';
                confirmText.style.color = 'green';
            } else {
                confirmText.textContent = 'Password tidak cocok ✖';
                confirmText.style.color = 'red';
            }
        }

        newPass.addEventListener('input', validatePasswordMatch);
        confirmPass.addEventListener('input', validatePasswordMatch);

        function validateAll() {
            let password = newPass.value;
            let confirm = confirmPass.value;

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;

            if (password && confirm === password && strength >= 2) {
                btnSave.disabled = false;
            } else {
                btnSave.disabled = true;
            }
        }

        newPass.addEventListener('input', validateAll);
        confirmPass.addEventListener('input', validateAll);
    }
}
</script>
</body>
</html>
