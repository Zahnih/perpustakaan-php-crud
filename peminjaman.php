<?php
include 'config.php';

// 1. PROSES TAMBAH PEMINJAMAN
if (isset($_POST['simpan'])) {
    $id_anggota = htmlspecialchars($_POST['id_anggota']);
    $id_buku = htmlspecialchars($_POST['id_buku']);
    $jumlah = (int)$_POST['jumlah'];
    $tanggal_pinjam = htmlspecialchars($_POST['tanggal_pinjam']); 
    $tanggal_deadline = htmlspecialchars($_POST['tanggal_deadline']); 

    // Cek stok buku
    $cek_stok = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id_buku='$id_buku'");
    $buku_data = mysqli_fetch_assoc($cek_stok);

    if ($buku_data['stok'] >= $jumlah) {
        // Simpan ke tabel peminjaman (tanggal_kembali NULL karena baru pinjam)
        mysqli_query($koneksi, "INSERT INTO peminjaman (id_anggota, tanggal_pinjam, tanggal_deadline, tanggal_kembali) 
                                VALUES ('$id_anggota', '$tanggal_pinjam', '$tanggal_deadline', NULL)");
        
        $id_peminjaman = mysqli_insert_id($koneksi);

        // Simpan ke detail_peminjaman
        mysqli_query($koneksi, "INSERT INTO detail_peminjaman (id_peminjaman, id_buku, jumlah) 
                                VALUES ('$id_peminjaman', '$id_buku', '$jumlah')");

        // Kurangi stok buku
        mysqli_query($koneksi, "UPDATE buku SET stok = stok - $jumlah WHERE id_buku='$id_buku'");

        header("Location: peminjaman.php?pesan=sukses");
        exit;
    } else {
        header("Location: peminjaman.php?pesan=gagal");
        exit;
    }
}

// 2. PROSES PENGEMBALIAN BUKU (Update Stok & Tanggal Kembali)
if (isset($_GET['kembalikan'])) {
    $id_p = $_GET['kembalikan'];
    $tgl_sekarang = date('Y-m-d');

    // Ambil data buku untuk mengembalikan stok
    $query_detail = mysqli_query($koneksi, "SELECT id_buku, jumlah FROM detail_peminjaman WHERE id_peminjaman='$id_p'");
    $data_detail = mysqli_fetch_assoc($query_detail);
    $id_b = $data_detail['id_buku'];
    $jml = $data_detail['jumlah'];

    // Update tanggal_kembali dan tambah stok buku
    mysqli_query($koneksi, "UPDATE peminjaman SET tanggal_kembali='$tgl_sekarang' WHERE id_peminjaman='$id_p'");
    mysqli_query($koneksi, "UPDATE buku SET stok = stok + $jml WHERE id_buku='$id_b'");

    header("Location: peminjaman.php?pesan=kembali");
    exit;
}

// FETCH DATA UNTUK TABEL - PERUBAHAN 1: GANTI DESC JADI ASC
$peminjaman = mysqli_query($koneksi, "
    SELECT p.*, a.nama, b.judul, dp.jumlah 
    FROM peminjaman p
    JOIN anggota a ON p.id_anggota = a.id_anggota
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN buku b ON dp.id_buku = b.id_buku
    ORDER BY p.id_peminjaman ASC
");

// FETCH DATA UNTUK MODAL
$anggota_list = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY nama ASC");
$buku_list = mysqli_query($koneksi, "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peminjaman Buku - Perpustakaanku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100 p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold">Data Peminjaman</h3>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahPeminjaman">
                    <i class="bi bi-plus-lg"></i> Tambah Peminjaman
                </button>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] == 'sukses'): ?>
                    <div class="alert alert-success border-0 shadow-sm">Peminjaman berhasil dicatat!</div>
                <?php elseif ($_GET['pesan'] == 'kembali'): ?>
                    <div class="alert alert-info border-0 shadow-sm">Buku telah dikembalikan & Stok diperbarui!</div>
                <?php elseif ($_GET['pesan'] == 'gagal'): ?>
                    <div class="alert alert-danger border-0 shadow-sm">Stok buku tidak mencukupi!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-responsive rounded shadow-sm">
                <table class="table table-dark table-striped table-bordered align-middle mb-0">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Anggota</th>
                            <th>Buku</th>
                            <th>Jml</th>
                            <th>Tgl Pinjam</th>
                            <th>Deadline (7 Hari)</th>
                            <th>Tgl Kembali</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($peminjaman)) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="text-start"><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="text-start"><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= $row['jumlah'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                <td class="text-warning fw-bold"><?= date('d/m/Y', strtotime($row['tanggal_deadline'])) ?></td>
                                <td>
                                    <?php if($row['tanggal_kembali'] == NULL): ?>
                                        <span class="badge bg-danger">Masih Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['tanggal_kembali'] == NULL): ?>
                                        <a href="peminjaman.php?kembalikan=<?= $row['id_peminjaman'] ?>" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Proses pengembalian buku?')">
                                             <i class="bi bi-arrow-left-right"></i> Kembalikan
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white px-3 py-2">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="tambahPeminjaman" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content bg-dark text-white border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Transaksi Pinjam Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Anggota</label>
                            <select name="id_anggota" class="form-select" required>
                                <option value="">- Pilih Anggota -</option>
                                <?php while ($a = mysqli_fetch_assoc($anggota_list)) { ?>
                                    <option value="<?= $a['id_anggota'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Buku</label>
                            <select name="id_buku" class="form-select" required>
                                <option value="">- Pilih Buku -</option>
                                <?php while ($b = mysqli_fetch_assoc($buku_list)) { ?>
                                    <option value="<?= $b['id_buku'] ?>"><?= htmlspecialchars($b['judul']) ?> (Stok: <?= $b['stok'] ?>)</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pinjam</label>
                            <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pinjam</label>
                            <input type="date" name="tanggal_pinjam" id="tgl_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Deadline (7 Hari)</label>
                            <input type="date" name="tanggal_deadline" id="tgl_deadline" class="form-control" readonly required>
                            <small class="text-info">*Otomatis diset seminggu dari hari pinjam</small>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="submit" name="simpan" class="btn btn-primary w-100 shadow">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const inputPinjam = document.getElementById('tgl_pinjam');
    const inputDeadline = document.getElementById('tgl_deadline');

    function hitungDeadline() {
        if (inputPinjam.value) {
            let date = new Date(inputPinjam.value);
            date.setDate(date.getDate() + 7); 
            
            let yyyy = date.getFullYear();
            let mm = String(date.getMonth() + 1).padStart(2, '0');
            let dd = String(date.getDate()).padStart(2, '0');
            
            inputDeadline.value = `${yyyy}-${mm}-${dd}`;
        }
    }

    inputPinjam.addEventListener('change', hitungDeadline);
    window.onload = hitungDeadline;
</script>

</body>
</html>