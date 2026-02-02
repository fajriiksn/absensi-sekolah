<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){ header("location:login.php"); exit; }
include 'config/koneksi.php';
include 'layout/header.php';

// --- LOGIKA TEST API FONNTE ---
if(isset($_POST['btn_test_api'])){
    $target = $_POST['nomor_test'];
    $pesan  = "Halo! Ini adalah pesan uji coba koneksi WhatsApp Absensi MTs.";
    $token  = $_POST['token_saat_ini']; // Ambil dari input hidden
    $url    = $_POST['url_saat_ini'];

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $target,
        'message' => $pesan,
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
      ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    
    // Cek respon sederhana dari Fonnte (biasanya JSON)
    $res_data = json_decode($response, true);

    if ($error) {
        echo "<script>alert('Gagal CURL: $error');</script>";
    } else {
        // Fonnte biasanya mengembalikan status: true/false
        if(isset($res_data['status']) && $res_data['status'] == true){
            echo "<script>alert('Tes Berhasil! Pesan terkirim ke $target. Response: $response');</script>";
        } else {
            echo "<script>alert('Tes Gagal! Token mungkin salah atau belum terkoneksi. Response: $response');</script>";
        }
    }
}


// --- LOGIKA SIMPAN PENGATURAN ---
if(isset($_POST['btn_simpan'])){
    // 1. Data Profil
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat_sekolah']);
    $kepsek = mysqli_real_escape_string($koneksi, $_POST['kepala_sekolah']);
    $nip    = mysqli_real_escape_string($koneksi, $_POST['nip_kepsek']);

    // 2. Data Absensi
    $j_masuk  = $_POST['jam_masuk'];
    $j_pulang = $_POST['jam_pulang'];
    $w_pulang = isset($_POST['wajib_pulang']) ? 1 : 0; // Checkbox logic

    // 3. Data API & Template
    $token      = $_POST['api_token'];
    $endpoint   = $_POST['api_endpoint'];
    $txt_masuk  = mysqli_real_escape_string($koneksi, $_POST['pesan_masuk']);
    $txt_pulang = mysqli_real_escape_string($koneksi, $_POST['pesan_pulang']);
    
    // Logic Upload Logo
    $query_img = "";
    if($_FILES['logo_sekolah']['error'] == 0){
        $ext = pathinfo($_FILES['logo_sekolah']['name'], PATHINFO_EXTENSION);
        $nama_logo = "logo_sekolah.".$ext;
        move_uploaded_file($_FILES['logo_sekolah']['tmp_name'], "assets/img/".$nama_logo);
        $query_img = ", logo_sekolah='$nama_logo'";
    }

    $sql_update = "UPDATE pengaturan SET 
        nama_sekolah='$nama', alamat_sekolah='$alamat', kepala_sekolah='$kepsek', nip_kepsek='$nip',
        jam_masuk='$j_masuk', jam_pulang='$j_pulang', wajib_pulang='$w_pulang',
        api_token='$token', api_endpoint='$endpoint', pesan_masuk='$txt_masuk', pesan_pulang='$txt_pulang'
        $query_img
        WHERE id=1";

    if(mysqli_query($koneksi, $sql_update)){
        echo "<script>alert('Semua Pengaturan Berhasil Disimpan!'); window.location='pengaturan.php';</script>";
    } else {
        echo "<script>alert('Error: ".mysqli_error($koneksi)."');</script>";
    }
}

// AMBIL DATA
$q = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id=1");
$d = mysqli_fetch_assoc($q);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-success mb-4"><i class="bi bi-gear-fill"></i> Konfigurasi Sistem</h3>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <ul class="nav nav-tabs mb-3" id="settingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="profil-tab" data-bs-toggle="tab" data-bs-target="#profil" type="button" role="tab"><i class="bi bi-building"></i> Profil Sekolah</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="absensi-tab" data-bs-toggle="tab" data-bs-target="#absensi" type="button" role="tab"><i class="bi bi-clock"></i> Aturan Absensi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab"><i class="bi bi-whatsapp"></i> Notifikasi & API</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            
            <div class="tab-pane fade show active" id="profil" role="tabpanel">
                <div class="card card-custom bg-white">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="fw-bold">Nama Sekolah</label>
                                    <input type="text" name="nama_sekolah" class="form-control" value="<?php echo $d['nama_sekolah']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Alamat</label>
                                    <textarea name="alamat_sekolah" class="form-control" rows="2"><?php echo $d['alamat_sekolah']; ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Kepala Sekolah</label>
                                        <input type="text" name="kepala_sekolah" class="form-control" value="<?php echo $d['kepala_sekolah']; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">NIP Kepsek</label>
                                        <input type="text" name="nip_kepsek" class="form-control" value="<?php echo $d['nip_kepsek']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="fw-bold mb-2">Logo Sekolah</label>
                                <div class="mb-3">
                                    <img src="assets/img/<?php echo $d['logo_sekolah']; ?>" width="120" class="border p-2 rounded shadow-sm">
                                </div>
                                <input type="file" name="logo_sekolah" class="form-control form-control-sm">
                                <small class="text-muted">Upload untuk mengganti (PNG/JPG)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="absensi" role="tabpanel">
                <div class="card card-custom bg-white">
                    <div class="card-body">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i> Pengaturan ini menentukan status "Terlambat" dan apakah siswa wajib melakukan scan saat pulang.
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Jam Masuk (Batas Terlambat)</label>
                                <input type="time" name="jam_masuk" class="form-control" value="<?php echo $d['jam_masuk']; ?>">
                                <small class="text-muted">Lewat jam ini dianggap terlambat.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Jam Pulang (Mulai Scan Pulang)</label>
                                <input type="time" name="jam_pulang" class="form-control" value="<?php echo $d['jam_pulang']; ?>">
                                <small class="text-muted">Siswa baru bisa absen pulang setelah jam ini.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Mode Absen Pulang</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="wajib_pulang" value="1" id="switchPulang" <?php echo ($d['wajib_pulang'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="switchPulang">Aktifkan Absen Pulang</label>
                                </div>
                                <small class="text-muted">Jika dimatikan, siswa hanya perlu absen Masuk saja.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="api" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-custom bg-white mb-3">
                            <div class="card-header bg-white fw-bold">Konfigurasi Fonnte</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="fw-bold">API Token</label>
                                    <input type="text" name="api_token" class="form-control" value="<?php echo $d['api_token']; ?>" placeholder="Masukkan Token Fonnte Anda">
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">API Endpoint URL</label>
                                    <input type="text" name="api_endpoint" class="form-control" value="<?php echo $d['api_endpoint']; ?>" placeholder="Default: https://api.fonnte.com/send">
                                </div>
                            </div>
                        </div>

                        <div class="card card-custom bg-white">
                            <div class="card-header bg-white fw-bold">Template Pesan WhatsApp</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="fw-bold text-success">Pesan Saat Absen Masuk</label>
                                    <textarea name="pesan_masuk" class="form-control" rows="3"><?php echo $d['pesan_masuk']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold text-primary">Pesan Saat Absen Pulang</label>
                                    <textarea name="pesan_pulang" class="form-control" rows="3"><?php echo $d['pesan_pulang']; ?></textarea>
                                </div>
                                <div class="alert alert-warning small py-2">
                                    <b>Variabel:</b> Gunakan <code>{nama}</code> untuk Nama Siswa dan <code>{jam}</code> untuk Waktu Absen.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-custom bg-gradient-green text-white mb-3">
                            <div class="card-body">
                                <h5 class="fw-bold"><i class="bi bi-broadcast"></i> Uji Coba API</h5>
                                <p class="small opacity-75">Pastikan Token sudah disimpan sebelum melakukan tes.</p>
                                <hr class="border-white">
                                
                                <label class="small fw-bold mb-1">Nomor Tujuan (HP)</label>
                                <input type="text" name="nomor_test" class="form-control form-control-sm mb-2 text-dark" placeholder="08xxxxxxxxxx / 628xxxxxxxx">
                                
                                <input type="hidden" name="token_saat_ini" value="<?php echo $d['api_token']; ?>">
                                <input type="hidden" name="url_saat_ini" value="<?php echo $d['api_endpoint']; ?>">

                                <button type="submit" name="btn_test_api" class="btn btn-warning btn-sm w-100 text-dark fw-bold">
                                    <i class="bi bi-send-fill"></i> Kirim Pesan Test
                                </button>
                            </div>
                        </div>
                        <div class="card card-custom bg-white">
                             <div class="card-body small">
                                 <h6><b>Cara mendapatkan Token:</b></h6>
                                 <ol class="ps-3 mb-0">
                                     <li>Daftar di <a href="https://fonnte.com" target="_blank">fonnte.com</a></li>
                                     <li>Login & Link Device (Scan QR)</li>
                                     <li>Copy Token di menu API</li>
                                     <li>Paste di kolom API Token</li>
                                 </ol>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <hr class="my-4">
        <button type="submit" name="btn_simpan" class="btn btn-primary btn-lg w-100 shadow">
            <i class="bi bi-save"></i> SIMPAN SEMUA PENGATURAN
        </button>

    </form>
</div>

<?php include 'layout/footer.php'; ?>