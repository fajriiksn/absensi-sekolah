<?php
session_start();
// Cek Login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php"); exit;
}

include 'config/koneksi.php';

// 1. Ambil ID Kelas dari URL (Dikirim dari halaman data_siswa.php)
$id_kelas = isset($_GET['id_kelas']) ? $_GET['id_kelas'] : 'semua';

// 2. Logika Query Data Siswa
if($id_kelas == 'semua'){
    // Ambil Semua Siswa diurutkan per Kelas lalu Nama
    $query = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas 
                                     FROM siswa 
                                     JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                     ORDER BY kelas.nama_kelas ASC, siswa.nama_siswa ASC");
    $label_halaman = "Semua Siswa";
} else {
    // Ambil Siswa per Kelas Spesifik
    $query = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas 
                                     FROM siswa 
                                     JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                     WHERE siswa.id_kelas = '$id_kelas' 
                                     ORDER BY siswa.nama_siswa ASC");
    
    // Ambil nama kelas untuk Judul Halaman
    $q_label = mysqli_query($koneksi, "SELECT nama_kelas FROM kelas WHERE id_kelas='$id_kelas'");
    $d_label = mysqli_fetch_assoc($q_label);
    $label_halaman = "Kelas " . $d_label['nama_kelas'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu - <?php echo $label_halaman; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #eee; /* Warna background layar (bukan kertas) */
            margin: 0;
            padding: 20px;
        }

        /* --- LAYOUT GRID UTAMA --- */
        .print-area {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2 Kartu per baris (Kiri & Kanan) */
            gap: 20px; /* Jarak antar kartu */
            max-width: 210mm; /* Lebar Kertas A4 */
            margin: 0 auto;
        }

        /* --- DESAIN KARTU (ID CARD) --- */
        .id-card {
            width: 100%;
            max-width: 320px; /* Ukuran standar kartu portrait */
            height: 480px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden; /* Agar sudut tumpul rapi */
            position: relative;
            margin: 0 auto; /* Tengah di dalam kolom grid */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            page-break-inside: avoid; /* Mencegah kartu terpotong saat pindah halaman */
        }

        .card-header-mts {
            background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); /* Hijau Madrasah */
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 4px solid #fcd34d; /* Garis Kuning Emas */
            -webkit-print-color-adjust: exact; /* Wajib agar warna background tercetak */
            print-color-adjust: exact;
        }

        .school-name {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-type {
            font-size: 12px;
            margin: 0;
            opacity: 0.9;
            font-weight: 300;
        }

        .card-body-mts {
            padding: 20px;
            text-align: center;
        }

        .photo-box {
            width: 120px;
            height: 150px;
            background: #f8f9fa;
            border: 3px solid #10b981;
            border-radius: 8px;
            margin: 0 auto 15px auto;
            overflow: hidden;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Agar foto tidak gepeng */
        }

        .student-name {
            font-size: 18px;
            font-weight: 700;
            color: #064e3b;
            margin: 0 0 5px 0;
            line-height: 1.2;
        }

        .student-info {
            background-color: #e0f2f1;
            color: #004d40;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .qr-area {
            border-top: 2px dashed #ddd;
            padding-top: 15px;
            margin-top: 5px;
        }

        .qr-area img {
            width: 90px;
            height: 90px;
        }

        .qr-text {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        /* --- KHUSUS MODE PRINT (KERTAS) --- */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important; /* Hilangkan tombol saat diprint */
            }
            .print-area {
                width: 100%;
                max-width: 100%;
                gap: 10px;
                padding: 10px;
            }
            .id-card {
                box-shadow: none; /* Hilangkan bayangan biar tinta hemat */
                border: 1px solid #000; /* Border tegas */
            }
        }
    </style>
</head>
<body>

    <div class="container text-center mb-5 no-print">
        <h3 class="fw-bold text-success mb-2">Preview Cetak Kartu</h3>
        <p class="text-muted">Mode: <b><?php echo $label_halaman; ?></b> | Total: <?php echo mysqli_num_rows($query); ?> Siswa</p>
        
        <div class="alert alert-info d-inline-block text-start small">
            <i class="bi bi-info-circle"></i> <b>Tips Mencetak:</b><br>
            1. Gunakan Kertas A4.<br>
            2. Pastikan opsi <b>"Background Graphics"</b> dicentang di menu Print.<br>
            3. Set Margin ke <b>"Default"</b> atau "None".
        </div>
        <br><br>

        <button onclick="window.print()" class="btn btn-primary btn-lg px-4 shadow">
            <i class="bi bi-printer"></i> Cetak Sekarang
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg px-4 ms-2">
            Tutup Tab
        </button>
    </div>

    <div class="print-area">
        <?php 
        if(mysqli_num_rows($query) > 0) {
            while($row = mysqli_fetch_assoc($query)) : 
        ?>
            
            <div class="id-card">
                <div class="card-header-mts">
                    <h1 class="school-name">MTs NEGERI DIGITAL</h1>
                    <p class="card-type">KARTU TANDA PELAJAR</p>
                </div>
                
                <div class="card-body-mts">
                    <div class="photo-box">
                        <img src="assets/img/siswa/<?php echo $row['foto_siswa']; ?>" 
                             onerror="this.src='https://via.placeholder.com/120x150?text=No+Photo'" 
                             alt="Foto Siswa">
                    </div>

                    <h2 class="student-name"><?php echo $row['nama_siswa']; ?></h2>
                    <div class="student-info">
                        NIS: <?php echo $row['nis']; ?> &nbsp;|&nbsp; Kelas: <?php echo $row['nama_kelas']; ?>
                    </div>

                    <div class="qr-area">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $row['nis']; ?>&bgcolor=ffffff" alt="QR Code">
                        <p class="qr-text">Scan kartu ini untuk absensi kehadiran</p>
                    </div>
                </div>
            </div>
            <?php 
            endwhile; 
        } else {
            echo "<div class='alert alert-warning w-100 text-center'>Tidak ada data siswa ditemukan untuk kategori ini.</div>";
        }
        ?>
    </div>

</body>
</html>