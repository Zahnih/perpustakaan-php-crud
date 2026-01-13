<?php
include 'config.php';
session_start();

// Kalau sudah login, langsung redirect
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($koneksi, $_POST['confirm_password']);

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak sama.";
    } else {
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah digunakan.";
        } else {
            $insert = mysqli_query($koneksi, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
            if ($insert) {
                $_SESSION['success'] = "Akun berhasil dibuat. Silakan login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Pendaftaran gagal. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Perpustakaanku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page d-flex align-items-center justify-content-center">

<div class="login-card text-white">
    <h3 class="text-center mb-4">Daftar Akun 📋</h3>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
        </div>
        <button class="btn btn-success w-100" type="submit">Daftar</button>

        <div class="mt-3 text-center">
            Sudah punya akun? <a href="login.php" class="text-warning text-decoration-none">Login di sini</a>
        </div>
    </form>
</div>

</body>
</html>
