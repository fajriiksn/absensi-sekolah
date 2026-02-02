<?php
session_start();
// Cek Login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php"); exit;
}

include 'config/koneksi.php';
include 'layout/header.php';

// --- LOGIKA FILTER ---
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$id_kelas  = isset($_GET['id_kelas']) ? $_GET['id_kelas'] : 'semua';

// Membuat Query Dinamis berdasarkan Filter
$query_str = "SELECT absensi.*, siswa.nis, siswa.nama_siswa, kelas.nama_kelas 
              FROM absensi 
              JOIN siswa ON absensi.id_siswa = siswa.id_siswa 
              JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
              WHERE absensi.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if($id_kelas != 'semua'){
    $query_str .= " AND siswa.id_kelas = '$id_kelas'";
}

$query_str .= " ORDER BY absensi.tanggal DESC, absensi.jam_masuk ASC";
$result = mysqli_query($koneksi, $query_str);
?>

<div class="container-fluid">
    
    <div class="d-print-none">
        <h3 class="fw-bold text-success mb-4"><i class="bi bi-file-earmark-text"></i> Laporan Absensi</h3>

        <div class="card card-custom bg-white mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" name="tgl_awal" class="form-control" value="<?php echo $tgl_awal; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?php echo $tgl_akhir; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Filter Kelas</label>
                        <select name="id_kelas" class="form-select">
                            <option value="semua">-- Semua Kelas --</option>
                            <?php
                            $q_k = mysqli_query($koneksi, "SELECT * FROM kelas");
                            while($k = mysqli_fetch_assoc($q_k)){
                                $selected = ($id_kelas == $k['id_kelas']) ? 'selected' : '';
                                echo "<option value='".$k['id_kelas']."' $selected>".$k['nama_kelas']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Tampilkan</button>
                        
                        <button type="button" onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> PDF</button>
                        
                        <a href="export_excel.php?tgl_awal=<?php echo $tgl_awal; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>&id_kelas=<?php echo $id_kelas; ?>" target="_blank" class="btn btn-success"><i class="bi bi-file-excel"></i> Excel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card card-custom bg-white print-area">
        <div class="card-body">
            
            <div class="d-none d-print-block text-center mb-4">
                <h3 class="fw-bold">LAPORAN KEHADIRAN SISWA</h3>
                <h4>MTs NEGERI DIGITAL INDONESIA</h4>
                <p>Periode: <?php echo date('d-m-Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d-m-Y', strtotime($tgl_akhir)); ?></p>
                <hr style="border: 2px solid black;">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-success text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                <td class="text-center"><?php echo $row['jam_masuk']; ?> WIB</td>
                                <td class="text-center"><?php echo $row['nis']; ?></td>
                                <td><?php echo $row['nama_siswa']; ?></td>
                                <td class="text-center"><?php echo $row['nama_kelas']; ?></td>
                                <td class="text-center">
                                    <?php 
                                    if($row['status']=='Hadir') echo '<span class="badge bg-success text-white">Hadir</span>';
                                    else if($row['status']=='Sakit') echo '<span class="badge bg-warning text-dark">Sakit</span>';
                                    else echo '<span class="badge bg-danger text-white">'.$row['status'].'</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Tidak ada data pada periode ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="d-none d-print-block mt-5">
                <div class="row">
                    <div class="col-4 offset-8 text-center">
                        <p>Padang, <?php echo date('d F Y'); ?></p>
                        <p class="mb-5">Kepala Sekolah</p>
                        <br>
                        <p class="fw-bold text-decoration-underline">H. Fulan, S.Pd, M.Pd</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    /* Sembunyikan Sidebar, Header, dan Tombol saat ngeprint */
    .sidebar, .navbar, .d-print-none, footer {
        display: none !important;
    }
    /* Lebarkan konten utama agar full kertas */
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    /* Pastikan background warna tabel tercetak (Chrome/Edge option) */
    body {
        -webkit-print-color-adjust: exact;
    }
}
</style>

<?php include 'layout/footer.php'; ?>