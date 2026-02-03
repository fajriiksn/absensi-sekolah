-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2026 at 11:54 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_mtsn`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id_absen` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` enum('Hadir','Sakit','Izin','Alpa') NOT NULL DEFAULT 'Alpa',
  `keterangan` text DEFAULT NULL,
  `is_notif_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id_absen`, `id_siswa`, `tanggal`, `jam_masuk`, `jam_pulang`, `status`, `keterangan`, `is_notif_sent`) VALUES
(1, 1, '2026-02-02', '18:51:00', '18:47:53', 'Alpa', '', 0),
(2, 1, '2026-01-30', '18:25:00', NULL, 'Izin', '', 0),
(3, 2, '2026-02-02', '19:57:00', NULL, 'Sakit', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(20) NOT NULL,
  `id_walikelas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `id_walikelas`) VALUES
(1, '7A', 2),
(2, '7B', NULL),
(3, '8A', NULL),
(4, '8B', NULL),
(5, '9A', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_sekolah` varchar(100) NOT NULL DEFAULT 'MTs NEGERI DIGITAL',
  `alamat_sekolah` text DEFAULT NULL,
  `logo_sekolah` varchar(255) DEFAULT 'logo_default.png',
  `kepala_sekolah` varchar(100) DEFAULT NULL,
  `nip_kepsek` varchar(50) DEFAULT NULL,
  `jam_masuk` time DEFAULT '07:00:00',
  `jam_pulang` time DEFAULT '14:00:00',
  `wajib_pulang` tinyint(1) DEFAULT 1,
  `api_token` varchar(255) DEFAULT NULL,
  `api_endpoint` varchar(255) DEFAULT 'https://api.fonnte.com/send',
  `pesan_masuk` text DEFAULT NULL,
  `pesan_pulang` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_sekolah`, `alamat_sekolah`, `logo_sekolah`, `kepala_sekolah`, `nip_kepsek`, `jam_masuk`, `jam_pulang`, `wajib_pulang`, `api_token`, `api_endpoint`, `pesan_masuk`, `pesan_pulang`) VALUES
(1, 'MTSN 5 AGAM', 'Jl. Pendidikan No. 1, Indonesia', 'logo_sekolah.png', '', '', '07:00:00', '14:00:00', 0, '', 'https://api.fonnte.com/send', 'Assalamualaikum, {nama} telah hadir di sekolah pada pukul {jam}. Terima kasih.', 'Assalamualaikum, {nama} telah pulang sekolah pada pukul {jam}. Hati-hati di jalan.');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `nama_ortu` varchar(100) DEFAULT NULL,
  `no_hp_ortu` varchar(20) NOT NULL,
  `foto_siswa` varchar(255) DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nis`, `nama_siswa`, `jenis_kelamin`, `id_kelas`, `nama_ortu`, `no_hp_ortu`, `foto_siswa`) VALUES
(1, '12233', 'Aldian Yosra', 'L', 1, NULL, '62899887766', '69806396a2e59.png'),
(2, '45670', 'Putri', 'P', 1, NULL, '62899887761', 'default.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','walikelas') NOT NULL DEFAULT 'walikelas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', '2026-02-02 08:06:42'),
(2, 'Rangga Ferdinan, S.Pd', 'guru', '77e69c137812518e359196bb2f5e9bb9', 'walikelas', '2026-02-02 12:58:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absen`),
  ADD KEY `fk_absen_siswa` (`id_siswa`),
  ADD KEY `idx_tanggal` (`tanggal`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `fk_kelas_walikelas` (`id_walikelas`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `fk_siswa_kelas` (`id_kelas`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absen_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_walikelas` FOREIGN KEY (`id_walikelas`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
