<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){ header("location:login.php"); exit; }
include 'config/koneksi.php';
include 'layout/header.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$id_kelas = isset($_GET['id_kelas']) ? $_GET['id_kelas'] : '';
?>

<div class="container-fluid px-4">
    <div class="mb-4 mt-3">
        <h3 class="fw-bold text-success"><i class="bi bi-calendar-month"></i> Rekap Absensi Bulanan</h3>
        <p class="text-muted">Laporan detail kehadiran siswa per hari dalam satu bulan (Format Matriks).</p>
    </div>
        
    <div class="card card-custom bg-white shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="cetak_bulanan.php" method="GET" target="_blank" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="fw-bold small mb-1">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php
                        $bln = ["01"=>"Januari", "02"=>"Februari", "03"=>"Maret", "04"=>"April", "05"=>"Mei", "06"=>"Juni", 
                                "07"=>"Juli", "08"=>"Agustus", "09"=>"September", "10"=>"Oktober", "11"=>"November", "12"=>"Desember"];
                        foreach($bln as $k => $v){
                            $sel = ($k == $bulan) ? 'selected' : '';
                            echo "<option value='$k' $sel>$v</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold small mb-1">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php 
                        for($i=date('Y'); $i>=2023; $i--){
                            $sel = ($i == $tahun) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        } 
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold small mb-1">Kelas (Wajib Pilih)</label>
                    <select name="id_kelas" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        $q_k = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while($k = mysqli_fetch_assoc($q_k)){
                            $sel = ($id_kelas == $k['id_kelas']) ? 'selected' : '';
                            echo "<option value='".$k['id_kelas']."' $sel>Kelas ".$k['nama_kelas']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i> Silakan pilih filter di atas dan klik <b>Cetak Laporan</b> untuk melihat hasil.
    </div>
</div>

<?php include 'layout/footer.php'; ?>