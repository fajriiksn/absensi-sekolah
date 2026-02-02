<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){ header("location:login.php"); exit; }
include 'config/koneksi.php';

// 1. AMBIL INPUT
$bulan = $_GET['bulan'];
$tahun = $_GET['tahun'];
$id_kelas = $_GET['id_kelas'];

if(empty($id_kelas)){ die("Silakan pilih kelas terlebih dahulu."); }

// 2. DATA PENDUKUNG
// Nama Bulan Indo
$nama_bulan_arr = ["01"=>"Januari", "02"=>"Februari", "03"=>"Maret", "04"=>"April", "05"=>"Mei", "06"=>"Juni", "07"=>"Juli", "08"=>"Agustus", "09"=>"September", "10"=>"Oktober", "11"=>"November", "12"=>"Desember"];
$nama_bulan = $nama_bulan_arr[$bulan];

// Jumlah Hari dalam bulan tsb
$jml_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Data Kelas & Wali Kelas
$q_kelas = mysqli_query($koneksi, "SELECT kelas.*, users.nama_lengkap as walikelas FROM kelas LEFT JOIN users ON kelas.id_walikelas = users.id_user WHERE id_kelas='$id_kelas'");
$d_kelas = mysqli_fetch_assoc($q_kelas);
$nama_kelas = $d_kelas['nama_kelas'];
$nama_wali = $d_kelas['walikelas'] ?? ".........................";

// Data Sekolah (untuk Kota di TTD)
$q_sek = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$d_sek = mysqli_fetch_assoc($q_sek);
// Ambil kata kota dari alamat (Simple logic: ambil kata terakhir alamat, atau manual 'Padang')
$kota = "Padang"; // Bisa diganti manual

// 3. AMBIL DATA ABSENSI SEBULAN (Disimpan dalam Array agar cepat)
$data_absen = [];
$q_absen = mysqli_query($koneksi, "SELECT absensi.*, siswa.id_siswa 
                                   FROM absensi 
                                   JOIN siswa ON absensi.id_siswa = siswa.id_siswa 
                                   WHERE siswa.id_kelas = '$id_kelas' 
                                   AND MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");

while($row = mysqli_fetch_assoc($q_absen)){
    // Format Array: [id_siswa][tanggal_angka] = 'Hadir/Sakit/...'
    $tgl_angka = (int)date('d', strtotime($row['tanggal']));
    $data_absen[$row['id_siswa']][$tgl_angka] = $row['status'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Rekap Absen <?php echo "$nama_bulan $tahun - Kelas $nama_kelas"; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; -webkit-print-color-adjust: exact; }
        @page { size: A4 landscape; margin: 10mm; }
        
        /* HEADER TABEL */
        table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        
        /* WARNA HEADER SESUAI GAMBAR */
        .bg-dark { background-color: #212529; color: white; }
        .bg-header-bulan { background-color: #212529; color: white; font-weight: bold; text-transform: uppercase; }
        
        /* WARNA CELL STATUS */
        .sel-hadir { background-color: #d1fae5; color: #065f46; font-weight: bold; } /* Hijau Muda */
        .sel-sakit { background-color: #fef3c7; color: #92400e; font-weight: bold; } /* Kuning Muda */
        .sel-izin  { background-color: #dbeafe; color: #1e40af; font-weight: bold; } /* Biru Muda */
        .sel-alpa  { background-color: #fee2e2; color: #b91c1c; font-weight: bold; } /* Merah Muda */
        .sel-libur { color: red; font-weight: bold; background-color: #f8f9fa; }
        
        /* KOLOM TOTAL */
        .th-total-h { background-color: #d1fae5; color: #065f46; }
        .th-total-s { background-color: #fef3c7; color: #92400e; }
        .th-total-i { background-color: #dbeafe; color: #1e40af; }
        .th-total-a { background-color: #fee2e2; color: #b91c1c; }

        .no-print { background: #eee; padding: 10px; margin-bottom: 20px; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" style="font-weight:bold; padding: 10px 20px;">CETAK HALAMAN INI (LANDSCAPE)</button>
    </div>

    <h3 style="text-align: center; margin-bottom: 5px;">REKAPITULASI KEHADIRAN SISWA</h3>
    <p style="text-align: center; margin-top: 0;">Kelas: <?php echo $nama_kelas; ?> | Periode: <?php echo "$nama_bulan $tahun"; ?></p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="bg-dark" width="30">No</th>
                <th rowspan="2" class="bg-dark" width="250">Nama Siswa</th>
                <th colspan="<?php echo $jml_hari; ?>" class="bg-header-bulan">Bulan (<?php echo $nama_bulan; ?>)</th>
                <th colspan="4" class="bg-dark">Total</th>
            </tr>
            <tr class="bg-dark">
                <?php 
                for($d=1; $d<=$jml_hari; $d++){
                    // Cek Hari Libur (Minggu)
                    $timestamp = strtotime("$tahun-$bulan-$d");
                    $hari_minggu = (date('N', $timestamp) == 7);
                    $color = $hari_minggu ? 'style="color: #ff6b6b;"' : '';
                    echo "<th width='25' $color>$d</th>";
                }
                ?>
                <th width="30" class="th-total-h">H</th>
                <th width="30" class="th-total-s">S</th>
                <th width="30" class="th-total-i">I</th>
                <th width="30" class="th-total-a">A</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // AMBIL SISWA
            $q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas='$id_kelas' ORDER BY nama_siswa ASC");
            $no = 1;

            if(mysqli_num_rows($q_siswa) > 0){
                while($s = mysqli_fetch_assoc($q_siswa)){
                    $id = $s['id_siswa'];
                    $tot_h = 0; $tot_s = 0; $tot_i = 0; $tot_a = 0;

                    echo "<tr>";
                    echo "<td>".$no++."</td>";
                    echo "<td style='text-align:left; padding-left:5px; font-weight:bold;'>".$s['nama_siswa']."</td>";

                    // LOOP TANGGAL 1 SAMPAI AKHIR BULAN
                    for($d=1; $d<=$jml_hari; $d++){
                        $status = isset($data_absen[$id][$d]) ? $data_absen[$id][$d] : '-';
                        
                        // Cek Hari Minggu
                        $timestamp = strtotime("$tahun-$bulan-$d");
                        $is_minggu = (date('N', $timestamp) == 7);

                        // Tentukan Tampilan Cell
                        if($is_minggu){
                            echo "<td class='sel-libur'>L</td>"; // L = Libur
                        } else {
                            if($status == 'Hadir') { echo "<td class='sel-hadir'>H</td>"; $tot_h++; }
                            elseif($status == 'Sakit') { echo "<td class='sel-sakit'>S</td>"; $tot_s++; }
                            elseif($status == 'Izin') { echo "<td class='sel-izin'>I</td>"; $tot_i++; }
                            elseif($status == 'Alpa') { echo "<td class='sel-alpa'>A</td>"; $tot_a++; }
                            else { echo "<td>-</td>"; }
                        }
                    }

                    // KOLOM TOTAL
                    echo "<td class='fw-bold'>$tot_h</td>";
                    echo "<td class='fw-bold'>$tot_s</td>";
                    echo "<td class='fw-bold'>$tot_i</td>";
                    echo "<td style='color:red; font-weight:bold;'>$tot_a</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='".($jml_hari+6)."'>Belum ada data siswa di kelas ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div style="float: right; width: 300px; text-align: center; margin-top: 30px;">
        <p><?php echo $kota; ?>, <?php echo date('d').' '.$nama_bulan.' '.date('Y'); ?></p>
        <p>Wali Kelas,</p>
        <br><br><br>
        <p style="font-weight: bold; text-decoration: underline;">( <?php echo $nama_wali; ?> )</p>
    </div>

</body>
</html>