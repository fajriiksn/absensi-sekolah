<?php
// MATIKAN ERROR REPORTING AGAR JSON BERSIH
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include 'config/koneksi.php';

// Header JSON
header('Content-Type: application/json; charset=utf-8');

// Timezone
date_default_timezone_set('Asia/Jakarta');

try {
    // 1. Cek Koneksi & Input
    if (!$koneksi) { throw new Exception("Koneksi Database Gagal"); }
    if (!isset($_POST['nis']) || empty($_POST['nis'])) { throw new Exception("NIS tidak boleh kosong."); }

    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $tgl_hari_ini = date('Y-m-d');
    $jam_sekarang = date('H:i:s');

    // 2. Ambil Pengaturan
    $q_set = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
    $setting = mysqli_fetch_assoc($q_set);

    $jam_masuk_sekolah = isset($setting['jam_masuk']) ? $setting['jam_masuk'] : '07:00:00';
    $jam_pulang_sekolah = isset($setting['jam_pulang']) ? $setting['jam_pulang'] : '14:00:00';
    $mode_absen_pulang = isset($setting['wajib_pulang']) ? $setting['wajib_pulang'] : 1;
    
    // 3. Cari Siswa
    $q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nis = '$nis'");
    $siswa = mysqli_fetch_assoc($q_siswa);

    if (!$siswa) { throw new Exception("Siswa dengan NIS $nis tidak ditemukan."); }
    $id_siswa = $siswa['id_siswa']; 

    // 4. Cek Riwayat Absen Hari Ini
    $cek_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_siswa = '$id_siswa' AND tanggal = '$tgl_hari_ini'");
    $data_absen = mysqli_fetch_assoc($cek_absen);

    // --- LOGIKA UTAMA ---

    if ($data_absen) {
        // === SKENARIO: SUDAH ADA DATA (PULANG) ===

        // Cek Toggle Pulang
        if ($mode_absen_pulang == 0) {
            throw new Exception("Halo " . $siswa['nama_siswa'] . ", kamu sudah absen masuk hari ini.");
        }

        // Cek Double Scan Pulang
        if (!empty($data_absen['jam_pulang']) && $data_absen['jam_pulang'] != '00:00:00') {
            throw new Exception("Halo " . $siswa['nama_siswa'] . ", kamu sudah absen pulang hari ini.");
        }

        // Cek Batas Jam Pulang
        if (strtotime($jam_sekarang) < strtotime($jam_pulang_sekolah)) {
            throw new Exception("Belum waktunya pulang. Tunggu jam $jam_pulang_sekolah");
        }

        // UPDATE PULANG
        $update = mysqli_query($koneksi, "UPDATE absensi SET jam_pulang='$jam_sekarang' WHERE id_siswa='$id_siswa' AND tanggal='$tgl_hari_ini'");
        
        if ($update) {
            if (!empty($siswa['no_hp_ortu']) && !empty($setting['api_token'])) {
                $pesan = str_replace(['{nama}', '{jam}'], [$siswa['nama_siswa'], $jam_sekarang], $setting['pesan_pulang']);
                kirimWA($siswa['no_hp_ortu'], $pesan, $setting['api_token'], $setting['api_endpoint']);
            }
            // Response JSON
            echo json_encode([
                'status' => 'success', 
                'nama_siswa' => $siswa['nama_siswa'], 
                'message' => 'Hati-hati di jalan.',
                'tipe' => 'pulang' // Penanda untuk warna alert
            ]);
        } else {
            throw new Exception("Gagal update data pulang.");
        }

    } else {
        // === SKENARIO: BELUM ADA DATA (MASUK) ===
        
        $status_db = 'Hadir'; // Default Database tetap 'Hadir' agar Dashboard Hijau
        $ket_db = '';
        $pesan_layar = 'Absen Masuk Berhasil';
        $status_respon = 'success'; // Warna Alert (success=Hijau, warning=Kuning)
        
        // HITUNG KETERLAMBATAN
        if (strtotime($jam_sekarang) > strtotime($jam_masuk_sekolah)) {
            // Hitung selisih waktu
            $selisih = strtotime($jam_sekarang) - strtotime($jam_masuk_sekolah);
            $jam_telat = floor($selisih / (60 * 60));
            $menit_telat = floor(($selisih - ($jam_telat * 3600)) / 60);
            
            $teks_telat = "";
            if($jam_telat > 0) $teks_telat .= "$jam_telat Jam ";
            if($menit_telat > 0) $teks_telat .= "$menit_telat Menit";
            
            // Ubah variabel untuk database & notifikasi
            $status_db = 'Hadir'; // Tetap 'Hadir' di DB agar tidak dianggap Alpa
            $ket_db = "Terlambat $teks_telat"; 
            $pesan_layar = "Kamu Terlambat $teks_telat!";
            $status_respon = 'warning'; // Ubah warna alert jadi Kuning
        }

        // Insert Data Baru
        $insert = mysqli_query($koneksi, "INSERT INTO absensi (id_siswa, tanggal, jam_masuk, status, keterangan) VALUES ('$id_siswa', '$tgl_hari_ini', '$jam_sekarang', '$status_db', '$ket_db')");

        if ($insert) {
            // Kirim WA Masuk
            if (!empty($siswa['no_hp_ortu']) && !empty($setting['api_token'])) {
                $pesan = str_replace(['{nama}', '{jam}'], [$siswa['nama_siswa'], $jam_sekarang], $setting['pesan_masuk']);
                // Tambahkan info telat di WA jika perlu
                if($status_respon == 'warning') { $pesan .= " (Terlambat $teks_telat)"; }
                kirimWA($siswa['no_hp_ortu'], $pesan, $setting['api_token'], $setting['api_endpoint']);
            }
            
            // Response JSON ke Scan.php
            echo json_encode([
                'status' => $status_respon, // Bisa 'success' atau 'warning'
                'nama_siswa' => $siswa['nama_siswa'], 
                'message' => $pesan_layar
            ]);
        } else {
            throw new Exception("Gagal menyimpan data.");
        }
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Fungsi Kirim WA
function kirimWA($target, $pesan, $token, $url){
    if(empty($token)) return false; 
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 2,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array('target' => $target, 'message' => $pesan),
      CURLOPT_HTTPHEADER => array("Authorization: $token"),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
?>