<?php
session_start();
// Cek Login (Opsional: Hapus bagian ini jika ingin mode Kiosk tanpa login)
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi - MTsN Digital</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); /* Tema Hijau MTs */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .scanner-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            text-align: center;
        }
        #reader {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            background: #000;
        }
        .logo-mts {
            width: 60px;
            margin-bottom: 10px;
        }
        .btn-back {
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.4);
        }
    </style>
</head>
<body>

    <div class="container d-flex flex-column align-items-center">
        
        <a href="index.php" class="btn-back">
            &larr; Kembali ke Dashboard
        </a>

        <div class="scanner-card">
            <img src="assets/img/logo.png" alt="Logo" class="logo-mts" onerror="this.src='https://via.placeholder.com/60?text=MTs'">
            <h4 class="fw-bold text-success mb-1">Absensi Digital</h4>
            <p class="text-muted small mb-4">Arahkan Kartu QR Siswa ke Kamera</p>
            
            <div id="reader"></div>

            <div class="mt-3 text-muted small">
                <span id="status-text">Menunggu Scan...</span>
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <p class="small mb-2">QR Code Rusak? Input NIS Manual:</p>
                <div class="input-group">
                    <input type="text" id="manual_nis" class="form-control" placeholder="Input NIS">
                    <button class="btn btn-success" onclick="manualAbsen()">Absen</button>
                </div>
            </div>
        </div>
    </div>

    <audio id="audio-success" src="assets/audio/beep-success.mp3"></audio>
    <audio id="audio-error" src="assets/audio/beep-error.mp3"></audio>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        // --- KONFIGURASI SCANNER ---
        let isProcessing = false; // Flag untuk mencegah spam scan

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return; // Stop jika sedang loading
            
            isProcessing = true;
            document.getElementById('status-text').innerText = "Memproses data...";
            
            // Kirim ke Backend
            prosesAbsensi(decodedText);
        }

        function onScanFailure(error) {
            // Biarkan kosong agar console bersih
        }

        // Inisialisasi Kamera
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 10, qrbox: 250 }, 
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);


        // --- FUNGSI UTAMA: KIRIM KE BACKEND ---
        function prosesAbsensi(nis) {
            let formData = new FormData();
            formData.append('nis', nis);

            fetch('proses_absen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // SUKSES
                    document.getElementById('audio-success').play();
                    Swal.fire({
                        title: 'BERHASIL!',
                        html: `<h4 class="text-success">${data.nama_siswa}</h4><p>${data.message}</p>`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        resetScanner();
                    });

                } else {
                    // GAGAL
                    document.getElementById('audio-error').play();
                    Swal.fire({
                        title: 'GAGAL!',
                        text: data.message,
                        icon: 'error',
                        timer: 3000, // Tampil lebih lama biar terbaca
                        showConfirmButton: false
                    }).then(() => {
                        resetScanner();
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire("Error System", "Periksa koneksi internet/server", "error");
                resetScanner();
            });
        }

        function resetScanner() {
            isProcessing = false;
            document.getElementById('status-text').innerText = "Menunggu Scan...";
            document.getElementById('manual_nis').value = ""; // Reset input manual
        }

        // --- FUNGSI INPUT MANUAL ---
        function manualAbsen() {
            let nis = document.getElementById('manual_nis').value;
            if(nis == "") {
                Swal.fire("Ops", "Masukkan NIS terlebih dahulu", "warning");
                return;
            }
            if (isProcessing) return;
            isProcessing = true;
            prosesAbsensi(nis);
        }
    </script>
</body>
</html>