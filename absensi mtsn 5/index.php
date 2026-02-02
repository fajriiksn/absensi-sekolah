<?php 
session_start();

// 1. Cek Session: Jika belum login, tendang ke login.php
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php");
    exit;
}

include 'config/koneksi.php'; 
include 'layout/header.php'; 

// --- LOGIC PHP: MENGHITUNG DATA UNTUK DASHBOARD ---
$hari_ini = date('Y-m-d');

// A. Hitung Total Siswa
$query_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
$data_total = mysqli_fetch_assoc($query_total);
$jml_siswa = $data_total['total'];

// B. Hitung Yang Hadir Hari Ini
$query_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE tanggal = '$hari_ini' AND status = 'Hadir'");
$data_hadir = mysqli_fetch_assoc($query_hadir);
$jml_hadir = $data_hadir['total'];

// C. Hitung Yang Izin/Sakit Hari Ini
$query_izin = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE tanggal = '$hari_ini' AND (status = 'Izin' OR status = 'Sakit')");
$data_izin = mysqli_fetch_assoc($query_izin);
$jml_izin = $data_izin['total'];

// D. Hitung Belum Hadir (Total Siswa - (Hadir + Izin + Sakit))
// Catatan: Ini hitungan sederhana real-time.
$jml_alpa = $jml_siswa - ($jml_hadir + $jml_izin);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Dashboard Ringkasan</h3>
        <span class="text-muted small"><?php echo date('l, d F Y'); ?></span>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-custom bg-gradient-green text-white">
                <div class="card-body">
                    <h6 class="card-title">Hadir Hari Ini</h6>
                    <h2 class="display-6 fw-bold"><?php echo $jml_hadir; ?></h2>
                    <p class="card-text small">
                        <i class="bi bi-person-check"></i> Siswa
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-custom bg-white">
                <div class="card-body text-success">
                    <h6 class="card-title text-muted">Sakit/Izin</h6>
                    <h2 class="display-6 fw-bold"><?php echo $jml_izin; ?></h2>
                    <p class="card-text small text-muted">Perlu konfirmasi walikelas</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-custom bg-white">
                <div class="card-body text-danger">
                    <h6 class="card-title text-muted">Belum Hadir</h6>
                    <h2 class="display-6 fw-bold"><?php echo $jml_alpa; ?></h2>
                    <p class="card-text small text-muted">Potensi Alpa</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-custom bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Siswa</h6>
                    <h2 class="display-6 fw-bold"><?php echo $jml_siswa; ?></h2>
                    <p class="card-text small">Data Terdaftar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom bg-white">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-success"><i class="bi bi-clock-history"></i> 5 Absensi Masuk Terakhir</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Query mengambil 5 data absen terakhir hari ini, JOIN dengan tabel siswa dan kelas
                        $query_history = mysqli_query($koneksi, "
                            SELECT absensi.*, siswa.nama_siswa, kelas.nama_kelas 
                            FROM absensi 
                            JOIN siswa ON absensi.id_siswa = siswa.id_siswa 
                            JOIN kelas ON siswa.id_kelas = kelas.id_kelas
                            WHERE absensi.tanggal = '$hari_ini'
                            ORDER BY absensi.jam_masuk DESC 
                            LIMIT 5
                        ");

                        // Jika tidak ada data
                        if(mysqli_num_rows($query_history) == 0){
                            echo "<tr><td colspan='4' class='text-center text-muted'>Belum ada siswa yang absen hari ini.</td></tr>";
                        }

                        // Loop data
                        while($row = mysqli_fetch_assoc($query_history)) {
                        ?>
                        <tr>
                            <td><?php echo $row['jam_masuk']; ?> WIB</td>
                            <td>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['nama_siswa']); ?>&background=random" class="rounded-circle me-2" width="30"> 
                                <?php echo $row['nama_siswa']; ?>
                            </td>
                            <td><?php echo $row['nama_kelas']; ?></td>
                            <td>
                                <?php 
                                if($row['status'] == 'Hadir') {
                                    echo '<span class="badge bg-success rounded-pill">Hadir</span>';
                                } elseif ($row['status'] == 'Sakit') {
                                    echo '<span class="badge bg-warning rounded-pill">Sakit</span>';
                                } else {
                                    echo '<span class="badge bg-danger rounded-pill">'.$row['status'].'</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php } // End While ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>