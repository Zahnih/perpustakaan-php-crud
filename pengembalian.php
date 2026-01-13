<?php
include 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 1. PROSES PENGEMBALIAN BUKU
if (isset($_POST['kembali'])) {
    $id_peminjaman = htmlspecialchars($_POST['id_peminjaman']);
    $tgl_sekarang = date('Y-m-d');

    // Update stok buku
    $detail = mysqli_query($koneksi, "SELECT * FROM detail_peminjaman WHERE id_peminjaman='$id_peminjaman'");
    while ($d = mysqli_fetch_assoc($detail)) {
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + {$d['jumlah']} WHERE id_buku='{$d['id_buku']}'");
    }

    // Update tgl_kembali agar Trigger Denda jalan
    mysqli_query($koneksi, "UPDATE peminjaman SET tanggal_kembali='$tgl_sekarang' WHERE id_peminjaman='$id_peminjaman'");
    
    header("Location: pengembalian.php?pesan=kembali_sukses");
    exit();
}

// 2. PROSES ACC BAYAR DENDA
if (isset($_POST['bayar_denda'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    mysqli_query($koneksi, "UPDATE peminjaman SET status_denda='Lunas' WHERE id_peminjaman='$id_peminjaman'");
    
    header("Location: pengembalian.php?pesan=bayar_sukses");
    exit();
}

// Fetch Pinjaman Aktif (Buku yang belum balik)
$peminjaman = mysqli_query($koneksi, "SELECT p.*, a.nama, b.judul FROM peminjaman p 
    JOIN anggota a ON p.id_anggota = a.id_anggota 
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN buku b ON dp.id_buku = b.id_buku
    WHERE p.tanggal_kembali IS NULL ORDER BY p.tanggal_pinjam ASC");

// Fetch Riwayat & Denda (Buku yang sudah balik)
$riwayat = mysqli_query($koneksi, "SELECT p.*, a.nama, b.judul FROM peminjaman p 
    JOIN anggota a ON p.id_anggota = a.id_anggota 
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN buku b ON dp.id_buku = b.id_buku
    WHERE p.tanggal_kembali IS NOT NULL ORDER BY p.tanggal_kembali DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengembalian & Denda | My Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .table-container { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge { padding: 8px 12px; border-radius: 6px; }
        .btn-sm { padding: 5px 10px; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex" id="wrapper">
   <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0 text-dark">Pengembalian & Konfirmasi Denda 💰</h3>
            <span class="text-muted small"><?= date('l, d F Y') ?></span>
        </div>
        
        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php 
                    if($_GET['pesan'] == 'kembali_sukses') echo "Buku berhasil dikembalikan! Jika telat, denda otomatis terhitung.";
                    if($_GET['pesan'] == 'bayar_sukses') echo "Pembayaran denda telah di-ACC. Status menjadi Lunas.";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-container mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary p-2 rounded me-3 text-white"><i class="bi bi-journal-arrow-down"></i></div>
                <h5 class="m-0 fw-bold">Buku Belum Kembali</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Deadline</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($peminjaman) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($peminjaman)): ?>
                            <tr>
                                <td><span class="fw-bold"><?= $row['nama'] ?></span></td>
                                <td><?= $row['judul'] ?></td>
                                <td>
                                    <?php 
                                        $deadline = strtotime($row['tanggal_deadline']);
                                        $today = strtotime(date('Y-m-d'));
                                        $color = ($today > $deadline) ? 'text-danger fw-bold' : 'text-dark';
                                    ?>
                                    <span class="<?= $color ?>"><?= date('d/m/Y', $deadline) ?></span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" onsubmit="return confirm('Konfirmasi pengembalian buku?');">
                                        <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman'] ?>">
                                        <button type="submit" name="kembali" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                                            <i class="bi bi-box-arrow-in-left me-1"></i> Kembalikan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada buku yang sedang dipinjam.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-dark p-2 rounded me-3 text-white"><i class="bi bi-clock-history"></i></div>
                <h5 class="m-0 fw-bold">Riwayat & Status Denda</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Tgl Kembali</th>
                            <th>Denda</th>
                            <th>Status</th>
                            <th class="text-center">Aksi (ACC Bayar)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($riwayat) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($riwayat)): ?>
                            <tr>
                                <td><?= $row['nama'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></td>
                                <td>
                                    <span class="<?= $row['denda'] > 0 ? 'text-danger' : 'text-muted' ?> fw-bold">
                                        Rp <?= number_format($row['denda'], 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['denda'] == 0): ?>
                                        <span class="badge bg-light text-secondary border">Tepat Waktu</span>
                                    <?php elseif($row['status_denda'] == 'Lunas'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">Hutang</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($row['denda'] > 0 && $row['status_denda'] == 'Belum Bayar'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman'] ?>">
                                            <button type="submit" name="bayar_denda" class="btn btn-success btn-sm rounded-pill shadow-sm">
                                                <i class="bi bi-check-lg me-1"></i> ACC Bayar
                                            </button>
                                        </form>
                                    <?php elseif($row['denda'] == 0): ?>
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    <?php else: ?>
                                        <i class="bi bi-check-all text-success fs-5"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat pengembalian.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>