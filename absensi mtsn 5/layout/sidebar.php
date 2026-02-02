<?php
// Ambil Data Pengaturan Sekolah
if(isset($koneksi)){
    $q_instansi = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
    $d_instansi = mysqli_fetch_assoc($q_instansi);
    $logo_sidebar = $d_instansi['logo_sekolah'];
    $nama_sidebar = $d_instansi['nama_sekolah'];
} else {
    $logo_sidebar = 'logo_default.png';
    $nama_sidebar = 'MTs NEGERI DIGITAL';
}
?>

<div class="sidebar d-flex flex-column p-3">
    <div class="brand-logo d-flex align-items-center mb-4 pb-2 border-bottom border-secondary">
        <img src="assets/img/<?php echo $logo_sidebar; ?>" alt="Logo" width="40" height="40" class="me-2 rounded-circle bg-white p-1" onerror="this.src='https://via.placeholder.com/40'">
        <div style="line-height: 1.2;">
            <span class="d-block fw-bold" style="font-size: 0.9rem;"><?php echo $nama_sidebar; ?></span>
            <small style="font-size: 0.65rem; opacity: 0.8;">Sistem Absensi</small>
        </div>
    </div>
    
    <ul class="nav nav-pills flex-column mb-2">
        <li class="nav-item mb-1">
            <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item mb-1">
            <a href="scan.php" target="_blank" class="nav-link text-warning bg-warning bg-opacity-10 border border-warning border-opacity-25">
                <i class="bi bi-qr-code-scan me-2"></i> <b>Buka Scanner</b>
            </a>
        </li>

        <li class="nav-header text-uppercase small mt-3 mb-1" style="opacity: 0.6; font-size: 0.7rem;">Data Master</li>
        
        <li class="nav-item mb-1">
            <a href="data_siswa.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'data_siswa.php') ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i> Data Siswa
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="data_kelas.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'data_kelas.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid me-2"></i> Data Kelas
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="data_user.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'data_user.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-badge me-2"></i> Data User (Guru)
            </a>
        </li>

        <li class="nav-item mb-1">
            <a href="input_manual.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'input_manual.php') ? 'active' : ''; ?>">
                <i class="bi bi-pencil-square me-2"></i> Input Absen Manual
            </a>
        </li>
        <li class="nav-header text-uppercase small mt-3 mb-1" style="opacity: 0.6; font-size: 0.7rem;">Laporan & Rekap</li>
        
        <li class="nav-item mb-1">
            <a href="#submenuLaporan" data-bs-toggle="collapse" class="nav-link d-flex justify-content-between align-items-center text-white" aria-expanded="false">
                <span><i class="bi bi-file-earmark-text me-2"></i> Laporan Absen</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?php echo (strpos(basename($_SERVER['PHP_SELF']), 'laporan_') !== false) ? 'show' : ''; ?>" id="submenuLaporan">
                <ul class="nav flex-column ms-3 mt-1 border-start border-white border-opacity-25 ps-2">
                    <li class="nav-item">
                        <a href="laporan_harian.php" class="nav-link py-1 small text-white-50 <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan_harian.php') ? 'text-white fw-bold' : ''; ?>">
                            <i class="bi bi-calendar-day me-2"></i> Harian / Mingguan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="laporan_bulanan.php" class="nav-link py-1 small text-white-50 <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan_bulanan.php') ? 'text-white fw-bold' : ''; ?>">
                            <i class="bi bi-calendar-month me-2"></i> Rekap Bulanan
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-header text-uppercase small mt-3 mb-1" style="opacity: 0.6; font-size: 0.7rem;">Sistem</li>
        
        <li class="nav-item mb-1">
            <a href="pengaturan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pengaturan.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear me-2"></i> Pengaturan Sekolah
            </a>
        </li>
    </ul>

    <div class="mt-auto pb-3">
        <hr class="text-white opacity-50">
        <a href="logout.php" class="nav-link text-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-center py-2 rounded">
            <i class="bi bi-box-arrow-right me-2"></i> <b>Logout</b>
        </a>
    </div>
</div>