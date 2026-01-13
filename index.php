<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil data statistik untuk grafik
$top_books_query = mysqli_query($koneksi, "
    SELECT b.judul, SUM(dp.jumlah) AS total
    FROM detail_peminjaman dp
    JOIN buku b ON dp.id_buku = b.id_buku
    GROUP BY dp.id_buku
    ORDER BY total DESC
    LIMIT 5
");

$top_books = $top_counts = [];
while ($row = mysqli_fetch_assoc($top_books_query)) {
    $top_books[] = $row['judul'];
    $top_counts[] = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .activity-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border-radius: 12px;
        }
        .activity-card:hover {
            transform: translateY(-5px);
        }
        .icon-box {
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 10px;
            float: right;
        }
    </style>
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fs-3 m-0">My Library - Dashboard 📊</h1>
                <span class="badge bg-white text-dark shadow-sm p-2"><?= date('d F Y') ?></span>
            </div>

            <?php
            // Query Data Statistik Dasar
            $total_buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku"))['total'];
            $total_anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM anggota"))['total'];
            $total_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM detail_peminjaman dp JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman WHERE p.tanggal_kembali IS NULL"))['total'] ?? 0;
            $total_terlambat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE tanggal_kembali IS NULL AND tanggal_deadline < CURDATE()"))['total'] ?? 0;
            
            // Logika Denda Baru (Sesuai status ACC Bayar)
            $total_denda_lunas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(denda) AS total FROM peminjaman WHERE status_denda='Lunas'"))['total'] ?? 0;
            $total_piutang_denda = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(denda) AS total FROM peminjaman WHERE status_denda='Belum Bayar'"))['total'] ?? 0;
            ?>

            <div class="row mb-2">
                <div class="col-md-4 mb-4">
                    <div class="card bg-primary text-white activity-card h-100">
                        <div class="card-body">
                            <div class="icon-box"><i class="bi bi-book fs-3"></i></div>
                            <h5>Total Buku</h5>
                            <p class="fs-2 fw-bold m-0"><?= $total_buku; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-success text-white activity-card h-100">
                        <div class="card-body">
                            <div class="icon-box"><i class="bi bi-people fs-3"></i></div>
                            <h5>Anggota</h5>
                            <p class="fs-2 fw-bold m-0"><?= $total_anggota; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-warning text-dark activity-card h-100">
                        <div class="card-body">
                            <div class="icon-box"><i class="bi bi-arrow-repeat fs-3"></i></div>
                            <h5>Buku Dipinjam</h5>
                            <p class="fs-2 fw-bold m-0"><?= $total_dipinjam; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-4">
                    <div class="card bg-danger text-white activity-card h-100">
                        <div class="card-body">
                            <div class="icon-box"><i class="bi bi-clock-history fs-3"></i></div>
                            <h5>Terlambat</h5>
                            <p class="fs-2 fw-bold m-0"><?= $total_terlambat; ?> <small class="fs-6 fw-normal text-white-50">Siswa</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark text-white activity-card h-100 border-start border-success border-5">
                        <div class="card-body">
                            <div class="icon-box text-success"><i class="bi bi-cash-stack fs-3"></i></div>
                            <h5 class="text-success">Kas Denda (Lunas)</h5>
                            <p class="fs-2 fw-bold m-0">Rp <?= number_format($total_denda_lunas, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card bg-white text-dark activity-card h-100 border-start border-warning border-5">
                        <div class="card-body">
                            <div class="icon-box text-warning"><i class="bi bi-wallet2 fs-3"></i></div>
                            <h5 class="text-warning">Piutang (Belum Bayar)</h5>
                            <p class="fs-2 fw-bold m-0 text-danger">Rp <?= number_format($total_piutang_denda, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-bar-chart-line me-2 text-primary"></i>5 Buku Paling Sering Dipinjam</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPeminjaman" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('chartPeminjaman').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($top_books) ?>,
        datasets: [{
            label: 'Kali Dipinjam',
            data: <?= json_encode($top_counts) ?>,
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: 'rgb(13, 110, 253)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: { 
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});
</script>
</body>
</html>