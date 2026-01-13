<?php
include 'config.php';

// Handle Tambah Buku
if (isset($_POST['tambah'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $pengarang = htmlspecialchars($_POST['pengarang']);
    $tahun_terbit = htmlspecialchars($_POST['tahun_terbit']);
    $stok = htmlspecialchars($_POST['stok']);

    $cover = '';
    if ($_FILES['cover']['name'] != '') {
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $namaFile = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['cover']['tmp_name'], 'uploads/' . $namaFile);
        $cover = $namaFile;
    }

    mysqli_query($koneksi, "INSERT INTO buku (judul, pengarang, tahun_terbit, stok, cover) VALUES ('$judul', '$pengarang', '$tahun_terbit', '$stok', '$cover')");
    header("Location: buku.php?pesan=tambah");
    exit;
}

// Handle Edit Buku
if (isset($_POST['edit'])) {
    $id_buku = $_POST['id_buku'];
    $judul = htmlspecialchars($_POST['judul']);
    $pengarang = htmlspecialchars($_POST['pengarang']);
    $tahun_terbit = htmlspecialchars($_POST['tahun_terbit']);
    $stok = htmlspecialchars($_POST['stok']);

    $coverUpdate = '';
    if ($_FILES['cover']['name'] != '') {
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $namaFile = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['cover']['tmp_name'], 'uploads/' . $namaFile);
        $coverUpdate = ", cover='$namaFile'";
    }

    mysqli_query($koneksi, "UPDATE buku SET judul='$judul', pengarang='$pengarang', tahun_terbit='$tahun_terbit', stok='$stok' $coverUpdate WHERE id_buku='$id_buku'");
    header("Location: buku.php?pesan=edit");
    exit;
}

// Handle Hapus Buku
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku='$id'");
    header("Location: buku.php?pesan=hapus");
    exit;
}

// Handle Cari Buku
$keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
if ($keyword != '') {
    $buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE judul LIKE '%$keyword%' OR pengarang LIKE '%$keyword%' ORDER BY id_buku DESC");
} else {
    $buku = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id_buku DESC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Perpustakaanku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-dark text-white">
<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Data Buku</h3>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle"></i> Tambah Buku
                </button>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <div class="alert alert-success text-dark bg-light">Data berhasil <?php echo htmlspecialchars($_GET['pesan']); ?>!</div>
            <?php endif; ?>

            <!-- Form Cari -->
            <form method="GET" class="mb-3 d-flex">
                <input type="text" name="keyword" class="form-control me-2 rounded-4" placeholder="Cari buku atau pengarang..." value="<?= htmlspecialchars($keyword); ?>">
                <button type="submit" class="btn btn-light rounded-4">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>

            <div class="table-responsive rounded-4 overflow-hidden">
                <table class="table table-bordered table-striped align-middle mb-0 bg-white text-dark">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Tahun Terbit</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($buku as $row): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center">
                                <?php if ($row['cover']): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['cover']); ?>" alt="Cover" width="50">
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td><?= htmlspecialchars($row['pengarang']); ?></td>
                            <td><?= htmlspecialchars($row['tahun_terbit']); ?></td>
                            <td><?= htmlspecialchars($row['stok']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_buku']; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="?hapus=<?= $row['id_buku']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus buku ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Buku -->
                        <div class="modal fade" id="modalEdit<?= $row['id_buku']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content bg-white text-dark">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Buku</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_buku" value="<?= $row['id_buku']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Judul Buku</label>
                                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($row['judul']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Pengarang</label>
                                                <input type="text" name="pengarang" class="form-control" value="<?= htmlspecialchars($row['pengarang']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tahun Terbit</label>
                                                <input type="date" name="tahun_terbit" class="form-control" value="<?= htmlspecialchars($row['tahun_terbit']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Stok</label>
                                                <input type="number" name="stok" class="form-control" value="<?= htmlspecialchars($row['stok']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Ganti Cover Buku (opsional)</label>
                                                <input type="file" name="cover" class="form-control" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Buku -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengarang</label>
                        <input type="text" name="pengarang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="date" name="tahun_terbit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Buku (opsional)</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
