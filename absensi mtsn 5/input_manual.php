<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){ header("location:login.php"); exit; }
include 'config/koneksi.php';
include 'layout/header.php';

// Ambil pengaturan
$q_set = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id=1");
$setting = mysqli_fetch_assoc($q_set);

// --- LOGIKA SIMPAN ---
if(isset($_POST['btn_simpan'])){
    $id_siswa = $_POST['id_siswa'];
    $tanggal  = $_POST['tanggal'];
    $jam      = $_POST['jam_masuk'];
    $status   = $_POST['status'];
    $ket      = $_POST['keterangan'];

    // Cek Duplikasi
    $cek = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_siswa='$id_siswa' AND tanggal='$tanggal'");
    
    if(mysqli_num_rows($cek) > 0){
        $query_save = "UPDATE absensi SET jam_masuk='$jam', status='$status', keterangan='$ket' WHERE id_siswa='$id_siswa' AND tanggal='$tanggal'";
        $aksi = "diupdate";
    } else {
        $query_save = "INSERT INTO absensi (id_siswa, tanggal, jam_masuk, status, keterangan, is_notif_sent) 
                       VALUES ('$id_siswa', '$tanggal', '$jam', '$status', '$ket', 0)";
        $aksi = "disimpan";
    }

    if(mysqli_query($koneksi, $query_save)){
        // Kirim WA jika Hadir
        if($status == 'Hadir'){
            $q_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa='$id_siswa'");
            $d_s = mysqli_fetch_assoc($q_s);
            $pesan = str_replace(['{nama}', '{jam}'], [$d_s['nama_siswa'], $jam], $setting['pesan_masuk']);
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $setting['api_endpoint'],
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => array('target' => $d_s['no_hp_ortu'], 'message' => $pesan),
              CURLOPT_HTTPHEADER => array("Authorization: ".$setting['api_token']),
            ));
            curl_exec($curl);
            curl_close($curl);
        }
        echo "<script>
            Swal.fire({ title: 'Berhasil!', text: 'Data absensi berhasil $aksi.', icon: 'success', timer: 1500, showConfirmButton: false })
            .then(() => { window.location='input_manual.php?id_kelas=".$_POST['filter_kelas']."'; });
        </script>";
    } else {
        echo "<script>Swal.fire('Error', '".mysqli_error($koneksi)."', 'error');</script>";
    }
}

$filter_kelas = isset($_GET['id_kelas']) ? $_GET['id_kelas'] : '';
?>

<style>
    /* Hilangkan background abu-abu bawaan body jika ada, biar bersih */
    .content-wrapper { background: #f8fafc; min-height: 100vh; }

    /* Card Putih Bersih tanpa Border */
    .card-clean {
        background: #ffffff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        height: 100%; /* Agar tinggi sama */
        padding: 30px;
    }

    /* Input Field Modern */
    .form-control, .form-select {
        background-color: #f1f5f9;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 500;
        transition: 0.3s;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff;
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    /* STATUS SELECTION CARDS (PILIHAN BESAR) */
    .status-option {
        display: none; /* Sembunyikan radio button asli */
    }
    .status-card {
        cursor: pointer;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s ease;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .status-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .status-card i { font-size: 2rem; margin-bottom: 10px; display: block; }
    
    /* Warna Saat Dipilih */
    .status-option:checked + .status-card.hadir {
        border-color: #10b981; background-color: #ecfdf5; color: #047857;
    }
    .status-option:checked + .status-card.sakit {
        border-color: #f59e0b; background-color: #fffbeb; color: #b45309;
    }
    .status-option:checked + .status-card.izin {
        border-color: #3b82f6; background-color: #eff6ff; color: #1d4ed8;
    }
    .status-option:checked + .status-card.alpa {
        border-color: #ef4444; background-color: #fef2f2; color: #b91c1c;
    }

    /* Warna Icon Default */
    .status-card.hadir i { color: #10b981; }
    .status-card.sakit i { color: #f59e0b; }
    .status-card.izin i { color: #3b82f6; }
    .status-card.alpa i { color: #ef4444; }

    /* Judul Section */
    .section-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 15px;
    }
</style>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Input Absensi Manual</h3>
            <p class="text-muted small mb-0">Kelola kehadiran siswa tanpa kartu QR.</p>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-4 col-md-5">
            <div class="card-clean">
                <div class="section-title"><i class="bi bi-sliders"></i> Konfigurasi</div>
                
                <form action="" method="GET">
                    <div class="mb-4">
                        <label class="form-label fw-bold">1. Pilih Kelas</label>
                        <select name="id_kelas" class="form-select form-select-lg" onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            <?php
                            $q_k = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                            while($k = mysqli_fetch_assoc($q_k)){
                                $sel = ($filter_kelas == $k['id_kelas']) ? 'selected' : '';
                                echo "<option value='".$k['id_kelas']."' $sel>Kelas ".$k['nama_kelas']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>

                <div class="mb-3">
                    <label class="form-label fw-bold">2. Tanggal</label>
                    <input type="date" name="tanggal" form="formAbsen" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">3. Jam Catat</label>
                    <input type="time" name="jam_masuk" form="formAbsen" class="form-control" value="<?php echo date('H:i'); ?>">
                </div>

                <div class="alert alert-light border mt-4 small">
                    <i class="bi bi-info-circle-fill text-primary"></i> 
                    Pastikan memilih kelas terlebih dahulu agar daftar nama siswa muncul di sebelah kanan.
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7">
            <div class="card-clean">
                <form action="" method="POST" id="formAbsen">
                    <input type="hidden" name="filter_kelas" value="<?php echo $filter_kelas; ?>">
                    
                    <div class="section-title"><i class="bi bi-person-check"></i> Form Kehadiran</div>

                    <div class="mb-4">
                        <label class="form-label fw-bold h5 mb-3">Siapa yang mau diabsen?</label>
                        <select name="id_siswa" class="form-select form-select-lg py-3 fs-5" required <?php echo ($filter_kelas == '') ? 'disabled style="background:#f1f5f9; cursor:not-allowed;"' : ''; ?>>
                            <option value="">
                                <?php echo ($filter_kelas == '') ? '⛔ Mohon Pilih Kelas di Panel Kiri Dulu' : '-- Cari Nama Siswa --'; ?>
                            </option>
                            <?php
                            if($filter_kelas != ''){
                                $q_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas='$filter_kelas' ORDER BY nama_siswa ASC");
                                while($s = mysqli_fetch_assoc($q_s)){
                                    echo "<option value='".$s['id_siswa']."'>".$s['nama_siswa']." (NIS: ".$s['nis'].")</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <label class="form-label fw-bold mb-3">Status Kehadiran</label>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-sm-3">
                            <input type="radio" name="status" id="st_hadir" value="Hadir" class="status-option" checked>
                            <label for="st_hadir" class="status-card hadir">
                                <i class="bi bi-check-circle-fill"></i>
                                <span class="fw-bold">Hadir</span>
                            </label>
                        </div>
                        <div class="col-6 col-sm-3">
                            <input type="radio" name="status" id="st_sakit" value="Sakit" class="status-option">
                            <label for="st_sakit" class="status-card sakit">
                                <i class="bi bi-thermometer-half"></i>
                                <span class="fw-bold">Sakit</span>
                            </label>
                        </div>
                        <div class="col-6 col-sm-3">
                            <input type="radio" name="status" id="st_izin" value="Izin" class="status-option">
                            <label for="st_izin" class="status-card izin">
                                <i class="bi bi-envelope-paper-fill"></i>
                                <span class="fw-bold">Izin</span>
                            </label>
                        </div>
                        <div class="col-6 col-sm-3">
                            <input type="radio" name="status" id="st_alpa" value="Alpa" class="status-option">
                            <label for="st_alpa" class="status-card alpa">
                                <i class="bi bi-x-circle-fill"></i>
                                <span class="fw-bold">Alpa</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Catatan / Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Tulis alasan jika sakit/izin..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-light px-4 py-2 fw-bold text-muted border">Batal</a>
                        <button type="submit" name="btn_simpan" class="btn btn-primary px-5 py-2 fw-bold shadow" <?php echo ($filter_kelas == '') ? 'disabled' : ''; ?>>
                            <i class="bi bi-save me-2"></i> SIMPAN DATA
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php include 'layout/footer.php'; ?>