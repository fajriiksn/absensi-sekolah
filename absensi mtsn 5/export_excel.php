<?php
// Skrip PHP Native untuk Export Excel Sederhana
include 'config/koneksi.php';

// Ambil Filter dari URL (GET)
$tgl_awal  = $_GET['tgl_awal'];
$tgl_akhir = $_GET['tgl_akhir'];
$id_kelas  = $_GET['id_kelas'];

// Nama File saat didownload
$filename = "Laporan_Absensi_" . date('Ymd') . ".xls";

// Header agar browser membaca ini sebagai file Excel
header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// --- QUERY DATA (Sama persis dengan laporan.php) ---
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

<h3>LAPORAN DATA ABSENSI SISWA</h3>
<p>Periode: <?php echo $tgl_awal; ?> s/d <?php echo $tgl_akhir; ?></p>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #4CAF50; color: white;">
            <th>No</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($row = mysqli_fetch_assoc($result)) : 
        ?>
        <tr>
            <td align="center"><?php echo $no++; ?></td>
            <td align="center"><?php echo $row['tanggal']; ?></td>
            <td align="center"><?php echo $row['jam_masuk']; ?></td>
            <td align="center" style="mso-number-format:'@';"><?php echo $row['nis']; ?></td> 
            <td><?php echo $row['nama_siswa']; ?></td>
            <td align="center"><?php echo $row['nama_kelas']; ?></td>
            <td align="center"><?php echo $row['status']; ?></td>
            <td><?php echo $row['keterangan']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>