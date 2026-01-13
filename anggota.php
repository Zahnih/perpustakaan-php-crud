<?php
include 'config.php';

$notif = '';

// Tambah Anggota
if (isset($_POST['tambah'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $tanggal_daftar = htmlspecialchars($_POST['tanggal_daftar']);

    mysqli_query($koneksi, "INSERT INTO anggota (nama, alamat, tanggal_daftar) VALUES ('$nama', '$alamat', '$tanggal_daftar')");
    header("Location: anggota.php");
    exit;
}

// Edit Anggota
if (isset($_POST['update'])) {
    $id = $_POST['id_anggota'];
    $nama = htmlspecialchars($_POST['nama']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $tanggal_daftar = htmlspecialchars($_POST['tanggal_daftar']);

    mysqli_query($koneksi, "UPDATE anggota SET nama='$nama', alamat='$alamat', tanggal_daftar='$tanggal_daftar' WHERE id_anggota='$id'");
    header("Location: anggota.php");
    exit;
}

// Hapus Anggota
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // Cek apakah anggota masih punya pinjaman aktif (tanggal_kembali NULL)
    $cek_pinjaman_hapus = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_anggota='$id' AND tanggal_kembali IS NULL");

    if (mysqli_num_rows($cek_pinjaman_hapus) > 0) {
        $notif = "Anggota ini masih dalam peminjaman dan tidak dapat dihapus.";
    } else {
        mysqli_query($koneksi, "DELETE FROM anggota WHERE id_anggota='$id'");
        header("Location: anggota.php");
        exit;
    }
}

// --- PERBAIKAN DI SINI: ORDER BY id_anggota ASC ---
$cari = '';
if (isset($_GET['cari'])) {
    $cari = htmlspecialchars($_GET['cari']);
    // Menggunakan ASC agar ID 1001 tetap di paling atas
    $anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE nama LIKE '%$cari%' OR alamat LIKE '%$cari%' ORDER BY id_anggota ASC");
} else {
    // Menggunakan ASC agar ID 1001 tetap di paling atas
    $anggota = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY id_anggota ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Anggota - Perpustakaanku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100">

        <div class="container-fluid p-4">

            <?php if ($notif != ''): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill"></i> <?= $notif ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Data Anggota</h3>
                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-person-plus"></i> Tambah Anggota
                </button>
            </div>

            <form class="d-flex mb-3" method="GET" action="">
                <input type="text" class="form-control me-2" name="cari" value="<?= $cari ?>" placeholder="Cari nama atau alamat...">
                <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i> Cari</button>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-bordered table-striped align-middle">
                    <thead class="text-center">
                        <tr>
                            <th>Status</th>
                            <th>ID Anggota</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
if (mysqli_num_rows($anggota) > 0) {
    while ($row = mysqli_fetch_assoc($anggota)) {
        $id_anggota = $row['id_anggota'];

        // Cek status pinjaman aktif
        $pinjaman_aktif = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_anggota='$id_anggota' AND tanggal_kembali IS NULL");

        $status = 'green';
        $warna = 'bg-success';

        if (mysqli_num_rows($pinjaman_aktif) > 0) {
            $pinjam = mysqli_fetch_assoc($pinjaman_aktif);
            $deadline = $pinjam['tanggal_deadline'];

            if (strtotime(date('Y-m-d')) > strtotime($deadline)) {
                $status = 'red';
                $warna = 'bg-danger';
            } else {
                $status = 'yellow';
                $warna = 'bg-warning';
            }
        }
?>
<tr class="text-center">
    <td>
        <span class="d-inline-block rounded-circle <?= $warna ?>" style="width: 12px; height: 12px;" title="<?= ($warna=='bg-success')?'Tidak Meminjam':(($warna=='bg-warning')?'Meminjam (Belum Telat)':'Terlambat') ?>"></span>
    </td>
    <td><?= $row['id_anggota']; ?></td>
    <td><?= $row['nama']; ?></td>
    <td><?= $row['alamat']; ?></td>
    <td><?= $row['tanggal_daftar']; ?></td>
    <td>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_anggota']; ?>">
            <i class="bi bi-pencil-square"></i> Edit
        </button>
        <a href="anggota.php?hapus=<?= $row['id_anggota']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus anggota ini?');">
            <i class="bi bi-trash"></i> Hapus
        </a>
    </td>
</tr>

<div class="modal fade" id="modalEdit<?= $row['id_anggota']; ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">Edit Anggota</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <input type="hidden" name="id_anggota" value="<?= $row['id_anggota']; ?>">
              <div class="mb-3">
                  <label class="form-label">Nama</label>
                  <input type="text" name="nama" class="form-control" value="<?= $row['nama']; ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Alamat</label>
                  <textarea name="alamat" class="form-control" required><?= $row['alamat']; ?></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label">Tanggal Daftar</label>
                  <input type="date" name="tanggal_daftar" class="form-control" value="<?= $row['tanggal_daftar']; ?>" required>
              </div>
          </div>
          <div class="modal-footer">
              <button type="submit" name="update" class="btn btn-success">Update</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>Data tidak ditemukan.</td></tr>";
}
?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Anggota</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Nama</label>
                  <input type="text" name="nama" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Alamat</label>
                  <textarea name="alamat" class="form-control" required></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label">Tanggal Daftar</label>
                  <input type="date" name="tanggal_daftar" class="form-control" required>
              </div>
          </div>
          <div class="modal-footer">
              <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>