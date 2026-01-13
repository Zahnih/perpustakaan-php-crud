<?php
include 'config.php';
session_start();

// Kalau sudah login, langsung redirect
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Tangkap notifikasi sukses dari signup
$success = '';
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Proses login saat form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1");
    $user = mysqli_fetch_assoc($query);

    if ($user) {
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Perpustakaanku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page d-flex align-items-center justify-content-center">

<div class="login-card text-white">
    <h3 class="text-center mb-4">Login Admin</h3>

    <?php if (!empty($success)) : ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>
        <a href="signup.php" class="btn btn-outline-dark w-100 mt-2">Belum punya akun? Daftar</a>
    </form>
</div>

</body>
</html>
