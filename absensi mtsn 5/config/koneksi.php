<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "absensi_mtsn";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set timezone agar jam absen sesuai waktu lokal (WIB)
date_default_timezone_set('Asia/Jakarta');
?>