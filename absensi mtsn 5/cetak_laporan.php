<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){ header("location:login.php"); exit; }
include 'config/koneksi.php';

// Ambil Data Sekolah
$q_instansi = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$d_instansi = mysqli_fetch_assoc($q_instansi);

// Ambil Filter dari URL
$tgl_awal  = $_GET['tgl_awal'];
$tgl_akhir = $_GET['tgl_akhir'];
$id_kelas  = $_GET['id_kelas'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cetak Laporan - <?php echo $d_instansi['nama_sekolah']; ?></title>
    <style>
        /* Reset CSS agar sama di semua browser */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        /* Setting Kertas A4 */
        @page {
            size: A4;
            margin: 1cm 1.5cm; /* Atas-Bawah 1cm, Kiri-Kanan 1.5cm */
        }

        /* --- 1. SETTING KOP SURAT (MENGGUNAKAN TABEL) --- */
        .table-kop {
            width: 100%;
            border-bottom: 4px double black; /* Garis ganda di bawah kop */
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .table-kop td {
            border: none; /* Hilangkan garis kotak tabel khusus kop */
            vertical-align: middle;
        }
        
        /* Logo Kiri & Kanan (Ukuran Tetap) */
        .td-logo {
            width: 100px;
            text-align: center;
        }
        .img-logo {
            width: 80px;
            height: auto;
        }

        /* Teks Tengah */
        .td-text {
            text-align: center;
        }
        .td-text h2 { margin: 0; font-size: 20pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .td-text p { margin: 2px 0; font-size: 11pt; }
        .td-text .periode { font-style: italic; font-size: 10pt; margin-top: 5px; }

        /* --- 2. SETTING TABEL DATA --- */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-data th, .table-data td {
            border: 1px solid black;
            padding: 6px 8px;
            font-size: 11pt;
        }
        .table-data th { 
            background-color: #f0f0f0; 
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        /* --- 3. SETTING TANDA TANGAN (FLOAT KANAN) --- */
        .ttd-container {
            width: 100%;
            margin-top: 30px;
            display: block; /* Pastikan blok baru */
        }
        .ttd-box {
            float: right; /* Paksa ke kanan */
            width: 280px;
            text-align: center;
        }
        
        /* Hapus tombol print saat dicetak */
        .no-print {
            background: #eee; padding: 15px; text-align: center;
            font-family: sans-serif; border-bottom: 1px solid #ccc;
        }
        @media print { .no-print { display: none; } }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" style="padding:10px 20px; font-weight:bold; cursor:pointer;">CETAK SEKARANG</button>
    </div>

    <table class="table-kop">
        <tr>
            <td class="td-logo">
                <img src="assets/img/logokiri.png" class="img-logo" onerror="this.src='https://via.placeholder.com/80?text=Logo+1'">
            </td>
            
            <td class="td-text">
                <h2><?php echo $d_instansi['nama_sekolah']; ?></h2>
                <p><?php echo $d_instansi['alamat_sekolah']; ?></p>
                <p class="periode">Laporan Absensi Periode: <?php echo date('d/m/Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d/m/Y', strtotime($tgl_akhir)); ?></p>
            </td>

            <td class="td-logo">
                <img src="assets/img/logokanan.png" class="img-logo" onerror="this.src='https://via.placeholder.com/80?text=Logo+2'">
            </td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="10%">Jam</th>
                <th width="10%">NIS</th>
                <th width="25%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th width="10%">Status</th>
                <th>Keterangan</th>
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
            $q_sql .= " ORDER BY absensi.tanggal DESC, kelas.nama_kelas ASC, siswa.nama_siswa ASC";

            $run_q = mysqli_query($koneksi, $q_sql);
            $no = 1;
            if(mysqli_num_rows($run_q) > 0){
                while($row = mysqli_fetch_assoc($run_q)) : ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td class="text-center"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td class="text-center"><?php echo substr($row['jam_masuk'], 0, 5); ?></td>
                    <td class="text-center"><?php echo $row['nis']; ?></td>
                    <td class="text-left"><?php echo $row['nama_siswa']; ?></td>
                    <td class="text-center"><?php echo $row['nama_kelas']; ?></td>
                    <td class="text-center"><?php echo $row['status']; ?></td>
                    <td class="text-left"><?php echo $row['keterangan']; ?></td>
                </tr>
                <?php endwhile; 
            } else {
                echo "<tr><td colspan='8' class='text-center' style='padding:20px;'>Data tidak ditemukan pada periode ini.</td></tr>";
            } ?>
        </tbody>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Padang, <?php echo date('d F Y'); ?></p>
            <p>Kepala Sekolah,</p>
            <br><br><br><br>
            <p style="text-decoration: underline; font-weight: bold; margin-bottom: 0;">
                <?php echo $d_instansi['kepala_sekolah']; ?>
            </p>
            <p style="margin-top: 5px;">NIP. <?php echo $d_instansi['nip_kepsek']; ?></p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>