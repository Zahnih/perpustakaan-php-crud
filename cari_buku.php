<?php
include 'config.php';

$cari = '';
if (isset($_GET['keyword'])) {
    $cari = htmlspecialchars($_GET['keyword']);
    $query = mysqli_query($koneksi, "SELECT * FROM buku WHERE judul LIKE '%$cari%' OR pengarang LIKE '%$cari%' ORDER BY judul ASC");
} else {
    $query = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY judul ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cari Buku - Perpustakaanku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="d-flex" id="wrapper">
   <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100">
       
        <div class="container-fluid p-4">
            <h3 class="mb-4">Cari Buku</h3>

            <form method="GET" class="d-flex mb-4">
                <input type="text" name="keyword" class="form-control me-2" placeholder="Masukkan judul atau pengarang..." value="<?= $cari ?>" >
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>

            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4">
              <?php
              while ($buku = mysqli_fetch_assoc($query)) { ?>
                <div class="col">
                  <div class="card buku-card h-100 bg-dark text-white border-secondary rounded-4">
                    <?php if (!empty($buku['cover'])) { ?>
                      <img src="uploads/<?= htmlspecialchars($buku['cover']) ?>" class="card-img-top" alt="<?= htmlspecialchars($buku['judul']) ?>" style="height:250px; object-fit:cover;">
                    <?php } else { ?>
                      <img src="uploads/default-cover.jpg" class="card-img-top" alt="Default Cover" style="height:250px; object-fit:cover;">
                    <?php } ?>
                    <div class="card-body ">
                      <h5 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h5>
                      <p class="card-text">
                        <strong>Pengarang:</strong> <?= htmlspecialchars($buku['pengarang']) ?><br>
                        <strong>Tahun:</strong> <?= htmlspecialchars($buku['tahun_terbit']) ?><br>
                        <strong>Stok:</strong> <?= htmlspecialchars($buku['stok']) ?>
                      </p>
                    </div>
                  </div>
                </div>
              <?php } ?>

              <?php if (mysqli_num_rows($query) == 0) { ?>
                <div class="col">
                  <div class="alert alert-warning text-center w-100">Buku tidak ditemukan.</div>
                </div>
              <?php } ?>
              </div>


        </div>
    </div>
</div>

</body>
</html>
