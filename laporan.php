<?php
include 'config.php';

// Ambil semua data peminjaman lengkap
$laporan = mysqli_query($koneksi, "
    SELECT 
        a.nama, 
        b.judul, 
        dp.jumlah, 
        p.tanggal_pinjam, 
        p.tanggal_deadline,
        p.tanggal_kembali
    FROM peminjaman p
    JOIN anggota a ON p.id_anggota = a.id_anggota
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN buku b ON dp.id_buku = b.id_buku
    ORDER BY p.tanggal_pinjam DESC
");

// Export ke Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=laporan_peminjaman_lengkap.xls");
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>Nama Anggota</th>
                    <th>Judul Buku</th>
                    <th>Jumlah</th>
                    <th>Tanggal Pinjam</th>
                    <th>Deadline</th>
                    <th>Tanggal Kembali</th>
                </tr>
            </thead>
            <tbody>";
    while ($d = mysqli_fetch_assoc($laporan)) {
        echo "<tr>
                <td>".htmlspecialchars($d['nama'])."</td>
                <td>".htmlspecialchars($d['judul'])."</td>
                <td>".htmlspecialchars($d['jumlah'])."</td>
                <td>".htmlspecialchars($d['tanggal_pinjam'])."</td>
                <td>".htmlspecialchars($d['tanggal_deadline'])."</td>
                <td>".($d['tanggal_kembali'] ?? '-') ."</td>
              </tr>";
    }
    echo "</tbody></table>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Lengkap - Perpustakaanku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100">
        <div class="container-fluid p-4">
            <h3 class="mb-4">Laporan Peminjaman Lengkap</h3>

            <a href="laporan.php?export=excel" class="btn btn-success mb-3">
                <i class="bi bi-file-earmark-excel"></i> Export ke Excel
            </a>

            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered align-middle">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Anggota</th>
                            <th>Judul Buku</th>
                            <th>Jumlah</th>
                            <th>Tanggal Pinjam</th>
                            <th>Deadline</th>
                            <th>Tanggal Kembali</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        mysqli_data_seek($laporan, 0);
                        while ($row = mysqli_fetch_assoc($laporan)) { ?>
                            <tr class="text-center">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_deadline']) ?></td>
                                <td><?= $row['tanggal_kembali'] ?? '-' ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (mysqli_num_rows($laporan) == 0) { ?>
                            <tr><td colspan="7" class="text-center">Tidak ada data laporan.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>
