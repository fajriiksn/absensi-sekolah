<?php
session_start();
include 'config/koneksi.php';

// Logika Login
if (isset($_POST['btn_login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // Ubah password ke MD5 (Sesuai dummy data yang kita buat di awal: admin123)
    // Catatan: Untuk produksi nyata, disarankan pakai password_verify() / Bcrypt
    $password_md5 = md5($password);

    // Cek User di Database
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password_md5'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Set Session
        $_SESSION['status'] = "sudah_login";
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama_lengkap'];
        $_SESSION['role'] = $data['role'];

        // Redirect ke Dashboard
        header("location:index.php");
    } else {
        $error = true; // Trigger alert error di HTML bawah
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absensi MTsN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #064e3b 0%, #10b981 100%);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-login {
            background-color: #064e3b;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #047857;
        }
        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
        }
        .logo-login {
            width: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <img src="assets/img/logo.png" alt="Logo MTs" class="logo-login" onerror="this.src='https://via.placeholder.com/80?text=Logo'">
        
        <h4 class="fw-bold mb-1">SIAKAD MTsN</h4>
        <p class="text-muted small mb-4">Silakan login untuk mengelola absensi</p>

        <?php if(isset($error)) : ?>
            <div class="alert alert-danger alert-dismissible fade show text-start p-2 small" role="alert">
                <strong>Gagal!</strong> Username atau Password salah.
                <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-floating mb-3 text-start">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-4 text-start">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <button type="submit" name="btn_login" class="btn btn-primary btn-login w-100 mb-3">MASUK SEKARANG</button>
        </form>

        <div class="text-muted small mt-3">
            &copy; 2024 MTs Negeri Digital System
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>