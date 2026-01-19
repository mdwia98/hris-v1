<?php
/**
 * Login Page
 */

session_start();
$user_role = $_SESSION['user']['role'] ?? 'role';
$user_name = $_SESSION['user']['name'] ?? 'username';
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Semua field wajib diisi';
    } else {
        $query = "
                SELECT 
                    u.id, u.username, u.email, u.password, u.role, u.status,
                    k.id AS karyawan_id, k.nama, k.nik_ktp
                FROM users u
                LEFT JOIN karyawan k ON u.id = k.user_id
                WHERE 
                    u.username = ?
                    OR u.email = ?
                    OR k.nik_ktp = ?
                LIMIT 1
                ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $login, $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

            $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $login, $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            $error = 'Password salah';
        } elseif ($user['status'] !== 'aktif') {
            $error = 'Akun tidak aktif';
        } else {

            $_SESSION['user'] = [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'email'        => $user['email'],
                'role'         => $user['role'],
                'name'         => $user['nama'] ?? $user['username'],
                'karyawan_id'  => $user['karyawan_id'],
                'nik_ktp'      => $user['nik_ktp']
            ];

            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = 'Akun tidak ditemukan';
    }

    $stmt->close();
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HRIS BSDM</title>
    <link rel="icon" href="assets/images/LOGO BSDM 2.jpeg">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1e7e34;
            --secondary: #ffc107;
            --dark: #1a1a1a;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1100px;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 80px rgba(0,0,0,.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            color: #fff;
            text-align: center;
            padding: 1.5rem;
        }

        .login-header h1 {
            font-size: 2rem;
            margin-bottom: .25rem;
        }

        .login-body {
            padding: 1.75rem;
        }

        .form-control {
            border: 2px solid var(--primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary), #15a829);
            color: #fff;
            font-weight: 600;
        }

        .carousel img {
            object-fit: contain;
            height: 350px;
            background: #fff;
        }

        @media (max-width: 768px) {
            .carousel img {
                height: 220px;
            }
        }

        .forgot-password {
            margin-left: auto;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

<div class="container login-wrapper">
    <div class="row g-4 align-items-center">

        <!-- CAROUSEL -->
        <div class="col-lg-6">
            <div id="carouselLogin" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded-4 shadow-sm bg-white">
                    <div class="carousel-item active">
                        <img src="assets/images/LOGO BSDM 2.jpeg" class="d-block w-100" alt="">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/LOGO BSDM 3.png" class="d-block w-100" alt="">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/LOGO BSDM 4.png" class="d-block w-100" alt="">
                    </div>
                </div>
            </div>
        </div>

        <!-- LOGIN FORM -->
        <div class="col-lg-6">
            <div class="login-container">

                        <div class="login-header">
                            <img src="assets/images/LOGO BSDM 4.png" alt=""></img>
                    <h1>BSDM HRIS</h1>
                    <p class="mb-0">Pusat Informasi Sumber Daya Manusia</p>
                </div>

                <div class="login-body">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-person"></i> Username
                            </label>
                            <input type="text" name="login" placeholder="Username / Email / NIK KTP" class="form-control" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" placeholder="Masukkan password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Ingat saya</label>
                            </div>
                            <a href="validate_forgot_password.php" class="small">Lupa Password?</a>
                        </div>

                        <button type="submit" class="btn btn-login w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePassword').onclick = () => {
    const p = document.getElementById('password');
    const i = event.currentTarget.querySelector('i');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.classList.toggle('bi-eye');
    i.classList.toggle('bi-eye-slash');
};
</script>
</body>
</html>