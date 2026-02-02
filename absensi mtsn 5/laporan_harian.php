<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){ header("location:login.php"); exit; }
include 'config/koneksi.php';
include 'layout/header.php';

// Filter Data
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$id_kelas  = isset($_GET['id_kelas']) ? $_GET['id_kelas'] : 'semua';
?>

<div class="container-fluid px-4">
    <div class="mb-4 mt-3">
        <h3 class="fw-bold text-success"><i class="bi bi-journal-text"></i> Laporan Harian</h3>
        <p class="text-muted">Rekap absensi detail berdasarkan rentang tanggal.</p>
    </div>
        
    <div class="card card-custom bg-white shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="fw-bold small mb-1">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="<?php echo $tgl_awal; ?>">
                </div>
                <div class="col-md-3">
                    <label class="fw-bold small mb-1">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="<?php echo $tgl_akhir; ?>">
                </div>
                <div class="col-md-3">
                    <label class="fw-bold small mb-1">Kelas</label>
                    <select name="id_kelas" class="form-select">
                        <option value="semua">Semua Kelas</option>
                        <?php
                        $q_k = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while($k = mysqli_fetch_assoc($q_k)){
                            $sel = ($id_kelas == $k['id_kelas']) ? 'selected' : '';
                            echo "<option value='".$k['id_kelas']."' $sel>".$k['nama_kelas']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill fw-bold">
                        <i class="bi bi-search"></i> Tampil
                    </button>
                    
                    <a href="cetak_laporan.php?tgl_awal=<?php echo $tgl_awal; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>&id_kelas=<?php echo $id_kelas; ?>" target="_blank" class="btn btn-secondary fw-bold">
                        <i class="bi bi-printer"></i> Cetak PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom bg-white shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Ket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_sql = "SELECT absensi.*, siswa.nis, siswa.nama_siswa, kelas.nama_kelas 
                                  FROM absensi 
                                  JOIN siswa ON absensi.id_siswa = siswa.id_siswa 
                                  JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                  WHERE absensi.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
                        if($id_kelas != 'semua') { $q_sql .= " AND siswa.id_kelas = '$id_kelas'"; }
                        $q_sql .= " ORDER BY absensi.tanggal DESC, absensi.jam_masuk DESC";

                        $run_q = mysqli_query($koneksi, $q_sql);
                        $no = 1;
                        if(mysqli_num_rows($run_q) > 0){
                            while($row = mysqli_fetch_assoc($run_q)) : ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo substr($row['jam_masuk'], 0, 5); ?></td>
                                <td><?php echo $row['nis']; ?></td>
                                <td><?php echo $row['nama_siswa']; ?></td>
                                <td><?php echo $row['nama_kelas']; ?></td>
                                <td>
                                    <?php 
                                    if($row['status']=='Hadir') echo '<span class="badge bg-success">Hadir</span>';
                                    elseif($row['status']=='Sakit') echo '<span class="badge bg-warning text-dark">Sakit</span>';
                                    elseif($row['status']=='Izin') echo '<span class="badge bg-info text-dark">Izin</span>';
                                    else echo '<span class="badge bg-danger">Alpa</span>';
                                    ?>
                                </td>
                                <td><small><?php echo $row['keterangan']; ?></small></td>
                            </tr>
                            <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>Tidak ada data.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'layout/footer.php'; ?>