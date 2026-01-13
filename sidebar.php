<!-- sidebar.php -->
<div class="border-end" id="sidebar-wrapper"
>
    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
        <div class="sidebar-heading text-white fw-bold sidebar-text fs-5" style="background-color: transparent;" >📚 My Library</div>
        <button class="btn btn-sm btn-outline-light" id="menu-toggle" title="Toggle Sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
    <div class="list-group list-group-flush">
        <a href="index.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-house me-2"></i> <span class="sidebar-text">Dashboard</span>
        </a>
        <a href="cari_buku.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-search me-2"></i> <span class="sidebar-text">Cari Buku</span>
        </a>
        <a href="buku.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-book me-2"></i> <span class="sidebar-text">Data Buku</span>
        </a>
        <a href="anggota.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-people me-2"></i> <span class="sidebar-text">Data Anggota</span>
        </a>
        <a href="peminjaman.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-arrow-right-square me-2"></i> <span class="sidebar-text">Peminjaman</span>
        </a>
        <a href="pengembalian.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-arrow-left-square me-2"></i> <span class="sidebar-text">Pengembalian</span>
        </a>
        <a href="laporan.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-file-earmark-text me-2"></i> <span class="sidebar-text">Laporan</span>
        </a>
        <a href="logout.php" class="list-group-item list-group-item-action text-white sidebar-item" style="background-color: transparent;">
            <i class="bi bi-box-arrow-in-right me-2"></i> <span class="sidebar-text">Logout</span>
        </a>
    </div>
</div>


<script>
    const wrapper = document.getElementById('wrapper');
    const toggleBtn = document.getElementById('menu-toggle');
    const icon = toggleBtn.querySelector('i');

    // Load state dari localStorage
    if (localStorage.getItem('sidebar') === 'minimized') {
        wrapper.classList.add('minimized');
        icon.classList.replace('bi-chevron-left', 'bi-chevron-right');
    }

    toggleBtn.addEventListener('click', function () {
        wrapper.classList.toggle('minimized');
        const minimized = wrapper.classList.contains('minimized');

        // Simpan status ke localStorage
        localStorage.setItem('sidebar', minimized ? 'minimized' : 'expanded');

        // Ganti ikon
        icon.classList.replace(minimized ? 'bi-chevron-left' : 'bi-chevron-right',
                               minimized ? 'bi-chevron-right' : 'bi-chevron-left');
    });
</script>

