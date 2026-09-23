-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Sep 2026 pada 07.25
-- Versi server: 10.4.25-MariaDB
-- Versi PHP: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ukk_2026`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_guru`
--

CREATE TABLE `t_guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_guru`
--

INSERT INTO `t_guru` (`id`, `nip`, `nama`, `email`, `status_aktif`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '1980010001', 'Ahmad Hidayat', 'guru1@sekolah.sch.id', 1, 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, '1980020002', 'Bambang Setiawan', 'guru2@sekolah.sch.id', 1, 2, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, '1980030003', 'Cahya Permana', 'guru3@sekolah.sch.id', 1, 3, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, '1980040004', 'Dewi Anggraini', 'guru4@sekolah.sch.id', 1, 4, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, '1980050005', 'Eko Pratama', 'guru5@sekolah.sch.id', 1, 5, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, '1980060006', 'Fitri Lestari', 'guru6@sekolah.sch.id', 1, 6, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, '1980070007', 'Gilang Ramadhan', 'guru7@sekolah.sch.id', 1, 7, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, '1980080008', 'Hana Sari', 'guru8@sekolah.sch.id', 1, 8, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, '1980090009', 'Indra Wijaya', 'guru9@sekolah.sch.id', 1, 9, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, '1980100010', 'Jajang Nurjaman', 'guru10@sekolah.sch.id', 1, 10, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, '1980110011', 'Kiki Amelia', 'guru11@sekolah.sch.id', 1, 11, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, '1980120012', 'Lilis Kurniasih', 'guru12@sekolah.sch.id', 1, 12, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, '1980130013', 'Maman Suherman', 'guru13@sekolah.sch.id', 1, 13, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, '1980140014', 'Novi Oktavia', 'guru14@sekolah.sch.id', 1, 14, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, '1980150015', 'Oman Saputra', 'guru15@sekolah.sch.id', 1, 15, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, '1980160016', 'Pipit Maharani', 'guru16@sekolah.sch.id', 1, 16, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, '1980170017', 'Rizal Fauzi', 'guru17@sekolah.sch.id', 1, 17, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, '1980180018', 'Sinta Nirmala', 'guru18@sekolah.sch.id', 1, 18, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, '1980190019', 'Taufik Hidayat', 'guru19@sekolah.sch.id', 1, 19, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, '1980200020', 'Ujang Kurnia', 'guru20@sekolah.sch.id', 1, 20, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, '1980210021', 'Vina Aprilia', 'guru21@sekolah.sch.id', 1, 21, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, '1980220022', 'Wawan Hermawan', 'guru22@sekolah.sch.id', 1, 22, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, '1980230023', 'Yulia Safitri', 'guru23@sekolah.sch.id', 1, 23, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, '1980240024', 'Zainal Abidin', 'guru24@sekolah.sch.id', 1, 24, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, '1980250025', 'Asep Firmansyah', 'guru25@sekolah.sch.id', 1, 25, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, '1980260026', 'Beni Kurnia', 'guru26@sekolah.sch.id', 1, 26, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, '1980270027', 'Cici Puspita', 'guru27@sekolah.sch.id', 1, 27, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, '1980280028', 'Dadan Maulana', 'guru28@sekolah.sch.id', 1, 28, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, '1980290029', 'Euis Kartika', 'guru29@sekolah.sch.id', 1, 29, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, '1980300030', 'Feri Irawan', 'guru30@sekolah.sch.id', 1, 30, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, '1980310031', 'Galih Setiawan', 'guru31@sekolah.sch.id', 1, 31, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, '1980320032', 'Hesti Wulandari', 'guru32@sekolah.sch.id', 1, 32, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, '1980330033', 'Imam Fauzan', 'guru33@sekolah.sch.id', 1, 33, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, '1980340034', 'Joko Haryanto', 'guru34@sekolah.sch.id', 1, 34, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, '1980350035', 'Kurnia Sari', 'guru35@sekolah.sch.id', 1, 35, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, '1980360036', 'Lukman Hakim', 'guru36@sekolah.sch.id', 1, 36, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, '1980370037', 'Mira Oktaviani', 'guru37@sekolah.sch.id', 1, 37, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, '1980380038', 'Nana Sujana', 'guru38@sekolah.sch.id', 1, 38, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, '1980390039', 'Oki Firmansyah', 'guru39@sekolah.sch.id', 1, 39, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, '1980400040', 'Prita Lestari', 'guru40@sekolah.sch.id', 1, 40, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, '1980410041', 'Rudi Hartono', 'guru41@sekolah.sch.id', 1, 41, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, '1980420042', 'Sari Ningsih', 'guru42@sekolah.sch.id', 1, 42, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, '1980430043', 'Tedi Setiawan', 'guru43@sekolah.sch.id', 1, 43, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, '1980440044', 'Umi Rahma', 'guru44@sekolah.sch.id', 1, 44, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, '1980450045', 'Vicky Pratama', 'guru45@sekolah.sch.id', 1, 45, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, '1980460046', 'Widi Astuti', 'guru46@sekolah.sch.id', 1, 46, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, '1980470047', 'Yani Mulyani', 'guru47@sekolah.sch.id', 1, 47, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, '1980480048', 'Yudi Kurniawan', 'guru48@sekolah.sch.id', 1, 48, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, '1980490049', 'Zulfa Aulia', 'guru49@sekolah.sch.id', 1, 49, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, '1980500050', 'Rina Marlina', 'guru50@sekolah.sch.id', 1, 50, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_kelas`
--

CREATE TABLE `t_kelas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tingkat` varchar(20) NOT NULL,
  `jurusan` varchar(100) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_kelas`
--

INSERT INTO `t_kelas` (`id`, `nama`, `tingkat`, `jurusan`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, '10 RPL 1', '10', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, '11 TKJ 1', '11', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, '12 AKL 1', '12', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, '10 BD 1', '10', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, '11 MPLB 1', '11', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, '12 RPL 2', '12', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, '10 TKJ 2', '10', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, '11 AKL 2', '11', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, '12 BD 2', '12', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, '10 MPLB 2', '10', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, '11 RPL 3', '11', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, '12 TKJ 3', '12', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, '10 AKL 3', '10', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, '11 BD 3', '11', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, '12 MPLB 3', '12', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, '10 RPL 4', '10', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, '11 TKJ 4', '11', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, '12 AKL 4', '12', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, '10 BD 4', '10', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, '11 MPLB 4', '11', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, '12 RPL 5', '12', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, '10 TKJ 5', '10', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, '11 AKL 5', '11', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, '12 BD 5', '12', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, '10 MPLB 5', '10', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, '11 RPL 6', '11', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, '12 TKJ 6', '12', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, '10 AKL 6', '10', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, '11 BD 6', '11', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, '12 MPLB 6', '12', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, '10 RPL 7', '10', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, '11 TKJ 7', '11', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, '12 AKL 7', '12', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, '10 BD 7', '10', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, '11 MPLB 7', '11', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, '12 RPL 8', '12', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, '10 TKJ 8', '10', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, '11 AKL 8', '11', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, '12 BD 8', '12', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, '10 MPLB 8', '10', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, '11 RPL 9', '11', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, '12 TKJ 9', '12', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, '10 AKL 9', '10', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, '11 BD 9', '11', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, '12 MPLB 9', '12', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, '10 RPL 10', '10', 'RPL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, '11 TKJ 10', '11', 'TKJ', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, '12 AKL 10', '12', 'AKL', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, '10 BD 10', '10', 'BD', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, '11 MPLB 10', '11', 'MPLB', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_kelas_siswa`
--

CREATE TABLE `t_kelas_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_kelas_siswa`
--

INSERT INTO `t_kelas_siswa` (`id`, `siswa_id`, `tahun_ajaran_id`, `kelas_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, 2, 1, 2, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, 3, 1, 3, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, 4, 1, 4, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, 5, 1, 5, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, 6, 1, 6, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, 7, 1, 7, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, 8, 1, 8, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, 9, 1, 9, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, 10, 1, 10, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, 11, 1, 11, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, 12, 1, 12, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, 13, 1, 13, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, 14, 1, 14, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, 15, 1, 15, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, 16, 1, 16, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, 17, 1, 17, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, 18, 1, 18, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, 19, 1, 19, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, 20, 1, 20, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, 21, 1, 21, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, 22, 1, 22, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, 23, 1, 23, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, 24, 1, 24, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, 25, 1, 25, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, 26, 1, 26, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, 27, 1, 27, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, 28, 1, 28, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, 29, 1, 29, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, 30, 1, 30, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, 31, 1, 31, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, 32, 1, 32, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, 33, 1, 33, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, 34, 1, 34, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, 35, 1, 35, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, 36, 1, 36, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, 37, 1, 37, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, 38, 1, 38, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, 39, 1, 39, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, 40, 1, 40, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, 41, 1, 41, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, 42, 1, 42, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, 43, 1, 43, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, 44, 1, 44, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, 45, 1, 45, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, 46, 1, 46, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, 47, 1, 47, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, 48, 1, 48, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, 49, 1, 49, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, 50, 1, 50, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran`
--

CREATE TABLE `t_pelanggaran` (
  `id` int(11) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `poin` int(11) NOT NULL,
  `deskripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_pelanggaran`
--

INSERT INTO `t_pelanggaran` (`id`, `pelanggaran_kategori_id`, `kode`, `nama`, `poin`, `deskripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 'P001', 'Pelanggaran 1', 7, 'Deskripsi pelanggaran nomor 1', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, 2, 'P002', 'Pelanggaran 2', 9, 'Deskripsi pelanggaran nomor 2', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, 3, 'P003', 'Pelanggaran 3', 11, 'Deskripsi pelanggaran nomor 3', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, 4, 'P004', 'Pelanggaran 4', 13, 'Deskripsi pelanggaran nomor 4', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, 5, 'P005', 'Pelanggaran 5', 15, 'Deskripsi pelanggaran nomor 5', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, 6, 'P006', 'Pelanggaran 6', 17, 'Deskripsi pelanggaran nomor 6', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, 7, 'P007', 'Pelanggaran 7', 19, 'Deskripsi pelanggaran nomor 7', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, 8, 'P008', 'Pelanggaran 8', 21, 'Deskripsi pelanggaran nomor 8', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, 9, 'P009', 'Pelanggaran 9', 23, 'Deskripsi pelanggaran nomor 9', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, 10, 'P010', 'Pelanggaran 10', 25, 'Deskripsi pelanggaran nomor 10', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, 11, 'P011', 'Pelanggaran 11', 27, 'Deskripsi pelanggaran nomor 11', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, 12, 'P012', 'Pelanggaran 12', 29, 'Deskripsi pelanggaran nomor 12', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, 13, 'P013', 'Pelanggaran 13', 31, 'Deskripsi pelanggaran nomor 13', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, 14, 'P014', 'Pelanggaran 14', 33, 'Deskripsi pelanggaran nomor 14', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, 15, 'P015', 'Pelanggaran 15', 35, 'Deskripsi pelanggaran nomor 15', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, 16, 'P016', 'Pelanggaran 16', 37, 'Deskripsi pelanggaran nomor 16', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, 17, 'P017', 'Pelanggaran 17', 39, 'Deskripsi pelanggaran nomor 17', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, 18, 'P018', 'Pelanggaran 18', 41, 'Deskripsi pelanggaran nomor 18', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, 19, 'P019', 'Pelanggaran 19', 43, 'Deskripsi pelanggaran nomor 19', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, 20, 'P020', 'Pelanggaran 20', 45, 'Deskripsi pelanggaran nomor 20', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, 21, 'P021', 'Pelanggaran 21', 47, 'Deskripsi pelanggaran nomor 21', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, 22, 'P022', 'Pelanggaran 22', 49, 'Deskripsi pelanggaran nomor 22', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, 23, 'P023', 'Pelanggaran 23', 51, 'Deskripsi pelanggaran nomor 23', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, 24, 'P024', 'Pelanggaran 24', 53, 'Deskripsi pelanggaran nomor 24', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, 25, 'P025', 'Pelanggaran 25', 55, 'Deskripsi pelanggaran nomor 25', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, 26, 'P026', 'Pelanggaran 26', 57, 'Deskripsi pelanggaran nomor 26', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, 27, 'P027', 'Pelanggaran 27', 59, 'Deskripsi pelanggaran nomor 27', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, 28, 'P028', 'Pelanggaran 28', 61, 'Deskripsi pelanggaran nomor 28', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, 29, 'P029', 'Pelanggaran 29', 63, 'Deskripsi pelanggaran nomor 29', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, 30, 'P030', 'Pelanggaran 30', 65, 'Deskripsi pelanggaran nomor 30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, 31, 'P031', 'Pelanggaran 31', 67, 'Deskripsi pelanggaran nomor 31', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, 32, 'P032', 'Pelanggaran 32', 69, 'Deskripsi pelanggaran nomor 32', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, 33, 'P033', 'Pelanggaran 33', 71, 'Deskripsi pelanggaran nomor 33', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, 34, 'P034', 'Pelanggaran 34', 73, 'Deskripsi pelanggaran nomor 34', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, 35, 'P035', 'Pelanggaran 35', 75, 'Deskripsi pelanggaran nomor 35', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, 36, 'P036', 'Pelanggaran 36', 77, 'Deskripsi pelanggaran nomor 36', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, 37, 'P037', 'Pelanggaran 37', 79, 'Deskripsi pelanggaran nomor 37', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, 38, 'P038', 'Pelanggaran 38', 81, 'Deskripsi pelanggaran nomor 38', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, 39, 'P039', 'Pelanggaran 39', 83, 'Deskripsi pelanggaran nomor 39', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, 40, 'P040', 'Pelanggaran 40', 85, 'Deskripsi pelanggaran nomor 40', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, 41, 'P041', 'Pelanggaran 41', 87, 'Deskripsi pelanggaran nomor 41', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, 42, 'P042', 'Pelanggaran 42', 89, 'Deskripsi pelanggaran nomor 42', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, 43, 'P043', 'Pelanggaran 43', 91, 'Deskripsi pelanggaran nomor 43', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, 44, 'P044', 'Pelanggaran 44', 93, 'Deskripsi pelanggaran nomor 44', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, 45, 'P045', 'Pelanggaran 45', 95, 'Deskripsi pelanggaran nomor 45', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, 46, 'P046', 'Pelanggaran 46', 97, 'Deskripsi pelanggaran nomor 46', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, 47, 'P047', 'Pelanggaran 47', 99, 'Deskripsi pelanggaran nomor 47', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, 48, 'P048', 'Pelanggaran 48', 101, 'Deskripsi pelanggaran nomor 48', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, 49, 'P049', 'Pelanggaran 49', 103, 'Deskripsi pelanggaran nomor 49', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, 50, 'P050', 'Pelanggaran 50', 105, 'Deskripsi pelanggaran nomor 50', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran_kategori`
--

CREATE TABLE `t_pelanggaran_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_pelanggaran_kategori`
--

INSERT INTO `t_pelanggaran_kategori` (`id`, `nama`, `deskripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'Kedisiplinan 1', 'Kategori pelanggaran 1', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, 'Kerapian 1', 'Kategori pelanggaran 2', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, 'Kehadiran 1', 'Kategori pelanggaran 3', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, 'Sikap 1', 'Kategori pelanggaran 4', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, 'Akademik 1', 'Kategori pelanggaran 5', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, 'Kebersihan 1', 'Kategori pelanggaran 6', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, 'Tata Tertib 1', 'Kategori pelanggaran 7', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, 'Kesopanan 1', 'Kategori pelanggaran 8', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, 'Keamanan 1', 'Kategori pelanggaran 9', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, 'Lingkungan 1', 'Kategori pelanggaran 10', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, 'Kedisiplinan 2', 'Kategori pelanggaran 11', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, 'Kerapian 2', 'Kategori pelanggaran 12', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, 'Kehadiran 2', 'Kategori pelanggaran 13', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, 'Sikap 2', 'Kategori pelanggaran 14', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, 'Akademik 2', 'Kategori pelanggaran 15', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, 'Kebersihan 2', 'Kategori pelanggaran 16', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, 'Tata Tertib 2', 'Kategori pelanggaran 17', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, 'Kesopanan 2', 'Kategori pelanggaran 18', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, 'Keamanan 2', 'Kategori pelanggaran 19', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, 'Lingkungan 2', 'Kategori pelanggaran 20', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, 'Kedisiplinan 3', 'Kategori pelanggaran 21', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, 'Kerapian 3', 'Kategori pelanggaran 22', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, 'Kehadiran 3', 'Kategori pelanggaran 23', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, 'Sikap 3', 'Kategori pelanggaran 24', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, 'Akademik 3', 'Kategori pelanggaran 25', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, 'Kebersihan 3', 'Kategori pelanggaran 26', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, 'Tata Tertib 3', 'Kategori pelanggaran 27', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, 'Kesopanan 3', 'Kategori pelanggaran 28', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, 'Keamanan 3', 'Kategori pelanggaran 29', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, 'Lingkungan 3', 'Kategori pelanggaran 30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, 'Kedisiplinan 4', 'Kategori pelanggaran 31', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, 'Kerapian 4', 'Kategori pelanggaran 32', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, 'Kehadiran 4', 'Kategori pelanggaran 33', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, 'Sikap 4', 'Kategori pelanggaran 34', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, 'Akademik 4', 'Kategori pelanggaran 35', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, 'Kebersihan 4', 'Kategori pelanggaran 36', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, 'Tata Tertib 4', 'Kategori pelanggaran 37', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, 'Kesopanan 4', 'Kategori pelanggaran 38', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, 'Keamanan 4', 'Kategori pelanggaran 39', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, 'Lingkungan 4', 'Kategori pelanggaran 40', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, 'Kedisiplinan 5', 'Kategori pelanggaran 41', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, 'Kerapian 5', 'Kategori pelanggaran 42', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, 'Kehadiran 5', 'Kategori pelanggaran 43', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, 'Sikap 5', 'Kategori pelanggaran 44', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, 'Akademik 5', 'Kategori pelanggaran 45', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, 'Kebersihan 5', 'Kategori pelanggaran 46', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, 'Tata Tertib 5', 'Kategori pelanggaran 47', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, 'Kesopanan 5', 'Kategori pelanggaran 48', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, 'Keamanan 5', 'Kategori pelanggaran 49', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, 'Lingkungan 5', 'Kategori pelanggaran 50', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_pelanggaran_siswa`
--

CREATE TABLE `t_pelanggaran_siswa` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nama_siswa` varchar(150) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `pelanggaran_id` int(11) NOT NULL,
  `nama_pelanggaran` varchar(150) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text NOT NULL,
  `poin` int(11) NOT NULL,
  `tindakan` text NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_pelanggaran_siswa`
--

INSERT INTO `t_pelanggaran_siswa` (`id`, `tahun_ajaran_id`, `siswa_id`, `nama_siswa`, `kelas_id`, `nama_kelas`, `pelanggaran_id`, `nama_pelanggaran`, `pelanggaran_kategori_id`, `guru_id`, `nama_guru`, `tanggal`, `keterangan`, `poin`, `tindakan`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Andi Saputra', 1, '10 RPL 1', 1, 'Pelanggaran 1', 1, 1, 'Ahmad Hidayat', '2026-01-01', 'Keterangan pelanggaran siswa 1', 7, 'Tindakan pembinaan 1', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, 1, 2, 'Budi Santoso', 2, '11 TKJ 1', 2, 'Pelanggaran 2', 2, 2, 'Bambang Setiawan', '2026-02-02', 'Keterangan pelanggaran siswa 2', 9, 'Tindakan pembinaan 2', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, 1, 3, 'Citra Lestari', 3, '12 AKL 1', 3, 'Pelanggaran 3', 3, 3, 'Cahya Permana', '2026-03-03', 'Keterangan pelanggaran siswa 3', 11, 'Tindakan pembinaan 3', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, 1, 4, 'Deni Kurniawan', 4, '10 BD 1', 4, 'Pelanggaran 4', 4, 4, 'Dewi Anggraini', '2026-04-04', 'Keterangan pelanggaran siswa 4', 13, 'Tindakan pembinaan 4', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, 1, 5, 'Eka Putri', 5, '11 MPLB 1', 5, 'Pelanggaran 5', 5, 5, 'Eko Pratama', '2026-05-05', 'Keterangan pelanggaran siswa 5', 15, 'Tindakan pembinaan 5', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, 1, 6, 'Fajar Nugraha', 6, '12 RPL 2', 6, 'Pelanggaran 6', 6, 6, 'Fitri Lestari', '2026-06-06', 'Keterangan pelanggaran siswa 6', 17, 'Tindakan pembinaan 6', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, 1, 7, 'Gina Maharani', 7, '10 TKJ 2', 7, 'Pelanggaran 7', 7, 7, 'Gilang Ramadhan', '2026-07-07', 'Keterangan pelanggaran siswa 7', 19, 'Tindakan pembinaan 7', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, 1, 8, 'Hendra Wijaya', 8, '11 AKL 2', 8, 'Pelanggaran 8', 8, 8, 'Hana Sari', '2026-08-08', 'Keterangan pelanggaran siswa 8', 21, 'Tindakan pembinaan 8', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, 1, 9, 'Intan Permata', 9, '12 BD 2', 9, 'Pelanggaran 9', 9, 9, 'Indra Wijaya', '2026-09-09', 'Keterangan pelanggaran siswa 9', 23, 'Tindakan pembinaan 9', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, 1, 10, 'Joko Susanto', 10, '10 MPLB 2', 10, 'Pelanggaran 10', 10, 10, 'Jajang Nurjaman', '2026-10-10', 'Keterangan pelanggaran siswa 10', 25, 'Tindakan pembinaan 10', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, 1, 11, 'Karina Sari', 11, '11 RPL 3', 11, 'Pelanggaran 11', 11, 11, 'Kiki Amelia', '2026-11-11', 'Keterangan pelanggaran siswa 11', 27, 'Tindakan pembinaan 11', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, 1, 12, 'Lukman Hakim', 12, '12 TKJ 3', 12, 'Pelanggaran 12', 12, 12, 'Lilis Kurniasih', '2026-12-12', 'Keterangan pelanggaran siswa 12', 29, 'Tindakan pembinaan 12', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, 1, 13, 'Maya Anggraini', 13, '10 AKL 3', 13, 'Pelanggaran 13', 13, 13, 'Maman Suherman', '2026-01-13', 'Keterangan pelanggaran siswa 13', 31, 'Tindakan pembinaan 13', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, 1, 14, 'Nanda Pratama', 14, '11 BD 3', 14, 'Pelanggaran 14', 14, 14, 'Novi Oktavia', '2026-02-14', 'Keterangan pelanggaran siswa 14', 33, 'Tindakan pembinaan 14', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, 1, 15, 'Oki Ramadhan', 15, '12 MPLB 3', 15, 'Pelanggaran 15', 15, 15, 'Oman Saputra', '2026-03-15', 'Keterangan pelanggaran siswa 15', 35, 'Tindakan pembinaan 15', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, 1, 16, 'Putri Amelia', 16, '10 RPL 4', 16, 'Pelanggaran 16', 16, 16, 'Pipit Maharani', '2026-04-16', 'Keterangan pelanggaran siswa 16', 37, 'Tindakan pembinaan 16', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, 1, 17, 'Qori Aulia', 17, '11 TKJ 4', 17, 'Pelanggaran 17', 17, 17, 'Rizal Fauzi', '2026-05-17', 'Keterangan pelanggaran siswa 17', 39, 'Tindakan pembinaan 17', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, 1, 18, 'Raka Firmansyah', 18, '12 AKL 4', 18, 'Pelanggaran 18', 18, 18, 'Sinta Nirmala', '2026-06-18', 'Keterangan pelanggaran siswa 18', 41, 'Tindakan pembinaan 18', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, 1, 19, 'Salsa Nabila', 19, '10 BD 4', 19, 'Pelanggaran 19', 19, 19, 'Taufik Hidayat', '2026-07-19', 'Keterangan pelanggaran siswa 19', 43, 'Tindakan pembinaan 19', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, 1, 20, 'Tegar Maulana', 20, '11 MPLB 4', 20, 'Pelanggaran 20', 20, 20, 'Ujang Kurnia', '2026-08-20', 'Keterangan pelanggaran siswa 20', 45, 'Tindakan pembinaan 20', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, 1, 21, 'Umi Kalsum', 21, '12 RPL 5', 21, 'Pelanggaran 21', 21, 21, 'Vina Aprilia', '2026-09-21', 'Keterangan pelanggaran siswa 21', 47, 'Tindakan pembinaan 21', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, 1, 22, 'Vino Akbar', 22, '10 TKJ 5', 22, 'Pelanggaran 22', 22, 22, 'Wawan Hermawan', '2026-10-22', 'Keterangan pelanggaran siswa 22', 49, 'Tindakan pembinaan 22', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, 1, 23, 'Wahyu Hidayat', 23, '11 AKL 5', 23, 'Pelanggaran 23', 23, 23, 'Yulia Safitri', '2026-11-23', 'Keterangan pelanggaran siswa 23', 51, 'Tindakan pembinaan 23', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, 1, 24, 'Xenia Oktaviani', 24, '12 BD 5', 24, 'Pelanggaran 24', 24, 24, 'Zainal Abidin', '2026-12-24', 'Keterangan pelanggaran siswa 24', 53, 'Tindakan pembinaan 24', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, 1, 25, 'Yogi Prasetyo', 25, '10 MPLB 5', 25, 'Pelanggaran 25', 25, 25, 'Asep Firmansyah', '2026-01-25', 'Keterangan pelanggaran siswa 25', 55, 'Tindakan pembinaan 25', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, 1, 26, 'Zahra Fadillah', 26, '11 RPL 1', 26, 'Pelanggaran 26', 26, 26, 'Beni Kurnia', '2026-02-26', 'Keterangan pelanggaran siswa 26', 57, 'Tindakan pembinaan 26', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, 1, 27, 'Agus Setiawan', 27, '12 TKJ 1', 27, 'Pelanggaran 27', 27, 27, 'Cici Puspita', '2026-03-27', 'Keterangan pelanggaran siswa 27', 59, 'Tindakan pembinaan 27', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, 1, 28, 'Bella Novitasari', 28, '10 AKL 1', 28, 'Pelanggaran 28', 28, 28, 'Dadan Maulana', '2026-04-01', 'Keterangan pelanggaran siswa 28', 61, 'Tindakan pembinaan 28', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, 1, 29, 'Cahyo Ramadhan', 29, '11 BD 1', 29, 'Pelanggaran 29', 29, 29, 'Euis Kartika', '2026-05-02', 'Keterangan pelanggaran siswa 29', 63, 'Tindakan pembinaan 29', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, 1, 30, 'Dinda Safitri', 30, '12 MPLB 1', 30, 'Pelanggaran 30', 30, 30, 'Feri Irawan', '2026-06-03', 'Keterangan pelanggaran siswa 30', 65, 'Tindakan pembinaan 30', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, 1, 31, 'Erwin Maulana', 31, '10 RPL 2', 31, 'Pelanggaran 31', 31, 31, 'Galih Setiawan', '2026-07-04', 'Keterangan pelanggaran siswa 31', 67, 'Tindakan pembinaan 31', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, 1, 32, 'Fani Azzahra', 32, '11 TKJ 2', 32, 'Pelanggaran 32', 32, 32, 'Hesti Wulandari', '2026-08-05', 'Keterangan pelanggaran siswa 32', 69, 'Tindakan pembinaan 32', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, 1, 33, 'Galih Permana', 33, '12 AKL 2', 33, 'Pelanggaran 33', 33, 33, 'Imam Fauzan', '2026-09-06', 'Keterangan pelanggaran siswa 33', 71, 'Tindakan pembinaan 33', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, 1, 34, 'Hani Rahmawati', 34, '10 BD 2', 34, 'Pelanggaran 34', 34, 34, 'Joko Haryanto', '2026-10-07', 'Keterangan pelanggaran siswa 34', 73, 'Tindakan pembinaan 34', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, 1, 35, 'Ilham Fauzi', 35, '11 MPLB 2', 35, 'Pelanggaran 35', 35, 35, 'Kurnia Sari', '2026-11-08', 'Keterangan pelanggaran siswa 35', 75, 'Tindakan pembinaan 35', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, 1, 36, 'Jihan Nuraini', 36, '12 RPL 3', 36, 'Pelanggaran 36', 36, 36, 'Lukman Hakim', '2026-12-09', 'Keterangan pelanggaran siswa 36', 77, 'Tindakan pembinaan 36', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, 1, 37, 'Kevin Alfarizi', 37, '10 TKJ 3', 37, 'Pelanggaran 37', 37, 37, 'Mira Oktaviani', '2026-01-10', 'Keterangan pelanggaran siswa 37', 79, 'Tindakan pembinaan 37', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, 1, 38, 'Laila Mutiara', 38, '11 AKL 3', 38, 'Pelanggaran 38', 38, 38, 'Nana Sujana', '2026-02-11', 'Keterangan pelanggaran siswa 38', 81, 'Tindakan pembinaan 38', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, 1, 39, 'Miko Aditya', 39, '12 BD 3', 39, 'Pelanggaran 39', 39, 39, 'Oki Firmansyah', '2026-03-12', 'Keterangan pelanggaran siswa 39', 83, 'Tindakan pembinaan 39', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, 1, 40, 'Nia Kartika', 40, '10 MPLB 3', 40, 'Pelanggaran 40', 40, 40, 'Prita Lestari', '2026-04-13', 'Keterangan pelanggaran siswa 40', 85, 'Tindakan pembinaan 40', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, 1, 41, 'Opik Firmansyah', 41, '11 RPL 4', 41, 'Pelanggaran 41', 41, 41, 'Rudi Hartono', '2026-05-14', 'Keterangan pelanggaran siswa 41', 87, 'Tindakan pembinaan 41', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, 1, 42, 'Puspa Sari', 42, '12 TKJ 4', 42, 'Pelanggaran 42', 42, 42, 'Sari Ningsih', '2026-06-15', 'Keterangan pelanggaran siswa 42', 89, 'Tindakan pembinaan 42', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, 1, 43, 'Rian Kurnia', 43, '10 AKL 4', 43, 'Pelanggaran 43', 43, 43, 'Tedi Setiawan', '2026-07-16', 'Keterangan pelanggaran siswa 43', 91, 'Tindakan pembinaan 43', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, 1, 44, 'Siti Aisyah', 44, '11 BD 4', 44, 'Pelanggaran 44', 44, 44, 'Umi Rahma', '2026-08-17', 'Keterangan pelanggaran siswa 44', 93, 'Tindakan pembinaan 44', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, 1, 45, 'Tio Ramadhan', 45, '12 MPLB 4', 45, 'Pelanggaran 45', 45, 45, 'Vicky Pratama', '2026-09-18', 'Keterangan pelanggaran siswa 45', 95, 'Tindakan pembinaan 45', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, 1, 46, 'Ulfa Nurfadilah', 46, '10 RPL 5', 46, 'Pelanggaran 46', 46, 46, 'Widi Astuti', '2026-10-19', 'Keterangan pelanggaran siswa 46', 97, 'Tindakan pembinaan 46', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, 1, 47, 'Vera Anjani', 47, '11 TKJ 5', 47, 'Pelanggaran 47', 47, 47, 'Yani Mulyani', '2026-11-20', 'Keterangan pelanggaran siswa 47', 99, 'Tindakan pembinaan 47', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, 1, 48, 'Wawan Gunawan', 48, '12 AKL 5', 48, 'Pelanggaran 48', 48, 48, 'Yudi Kurniawan', '2026-12-21', 'Keterangan pelanggaran siswa 48', 101, 'Tindakan pembinaan 48', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, 1, 49, 'Yuni Astuti', 49, '10 BD 5', 49, 'Pelanggaran 49', 49, 49, 'Zulfa Aulia', '2026-01-22', 'Keterangan pelanggaran siswa 49', 103, 'Tindakan pembinaan 49', 'Proses', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, 1, 50, 'Zaki Mubarak', 50, '11 MPLB 5', 50, 'Pelanggaran 50', 50, 50, 'Rina Marlina', '2026-02-23', 'Keterangan pelanggaran siswa 50', 105, 'Tindakan pembinaan 50', 'Selesai', '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_siswa`
--

CREATE TABLE `t_siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(30) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` char(1) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_siswa`
--

INSERT INTO `t_siswa` (`id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, '20260001', '002026000001', 'Andi Saputra', 'L', '2009-02-02', 'Jl. Pendidikan No. 1', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, '20260002', '002026000002', 'Budi Santoso', 'P', '2010-03-03', 'Jl. Pendidikan No. 2', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, '20260003', '002026000003', 'Citra Lestari', 'L', '2008-04-04', 'Jl. Pendidikan No. 3', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, '20260004', '002026000004', 'Deni Kurniawan', 'P', '2009-05-05', 'Jl. Pendidikan No. 4', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, '20260005', '002026000005', 'Eka Putri', 'L', '2010-06-06', 'Jl. Pendidikan No. 5', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, '20260006', '002026000006', 'Fajar Nugraha', 'P', '2008-07-07', 'Jl. Pendidikan No. 6', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, '20260007', '002026000007', 'Gina Maharani', 'L', '2009-08-08', 'Jl. Pendidikan No. 7', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, '20260008', '002026000008', 'Hendra Wijaya', 'P', '2010-09-09', 'Jl. Pendidikan No. 8', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, '20260009', '002026000009', 'Intan Permata', 'L', '2008-10-10', 'Jl. Pendidikan No. 9', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, '20260010', '002026000010', 'Joko Susanto', 'P', '2009-11-11', 'Jl. Pendidikan No. 10', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, '20260011', '002026000011', 'Karina Sari', 'L', '2010-12-12', 'Jl. Pendidikan No. 11', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, '20260012', '002026000012', 'Lukman Hakim', 'P', '2008-01-13', 'Jl. Pendidikan No. 12', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, '20260013', '002026000013', 'Maya Anggraini', 'L', '2009-02-14', 'Jl. Pendidikan No. 13', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, '20260014', '002026000014', 'Nanda Pratama', 'P', '2010-03-15', 'Jl. Pendidikan No. 14', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, '20260015', '002026000015', 'Oki Ramadhan', 'L', '2008-04-16', 'Jl. Pendidikan No. 15', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, '20260016', '002026000016', 'Putri Amelia', 'P', '2009-05-17', 'Jl. Pendidikan No. 16', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, '20260017', '002026000017', 'Qori Aulia', 'L', '2010-06-18', 'Jl. Pendidikan No. 17', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, '20260018', '002026000018', 'Raka Firmansyah', 'P', '2008-07-19', 'Jl. Pendidikan No. 18', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, '20260019', '002026000019', 'Salsa Nabila', 'L', '2009-08-20', 'Jl. Pendidikan No. 19', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, '20260020', '002026000020', 'Tegar Maulana', 'P', '2010-09-21', 'Jl. Pendidikan No. 20', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, '20260021', '002026000021', 'Umi Kalsum', 'L', '2008-10-22', 'Jl. Pendidikan No. 21', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, '20260022', '002026000022', 'Vino Akbar', 'P', '2009-11-23', 'Jl. Pendidikan No. 22', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, '20260023', '002026000023', 'Wahyu Hidayat', 'L', '2010-12-24', 'Jl. Pendidikan No. 23', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, '20260024', '002026000024', 'Xenia Oktaviani', 'P', '2008-01-25', 'Jl. Pendidikan No. 24', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, '20260025', '002026000025', 'Yogi Prasetyo', 'L', '2009-02-26', 'Jl. Pendidikan No. 25', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, '20260026', '002026000026', 'Zahra Fadillah', 'P', '2010-03-27', 'Jl. Pendidikan No. 26', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, '20260027', '002026000027', 'Agus Setiawan', 'L', '2008-04-01', 'Jl. Pendidikan No. 27', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, '20260028', '002026000028', 'Bella Novitasari', 'P', '2009-05-02', 'Jl. Pendidikan No. 28', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, '20260029', '002026000029', 'Cahyo Ramadhan', 'L', '2010-06-03', 'Jl. Pendidikan No. 29', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, '20260030', '002026000030', 'Dinda Safitri', 'P', '2008-07-04', 'Jl. Pendidikan No. 30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, '20260031', '002026000031', 'Erwin Maulana', 'L', '2009-08-05', 'Jl. Pendidikan No. 31', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, '20260032', '002026000032', 'Fani Azzahra', 'P', '2010-09-06', 'Jl. Pendidikan No. 32', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, '20260033', '002026000033', 'Galih Permana', 'L', '2008-10-07', 'Jl. Pendidikan No. 33', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, '20260034', '002026000034', 'Hani Rahmawati', 'P', '2009-11-08', 'Jl. Pendidikan No. 34', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, '20260035', '002026000035', 'Ilham Fauzi', 'L', '2010-12-09', 'Jl. Pendidikan No. 35', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, '20260036', '002026000036', 'Jihan Nuraini', 'P', '2008-01-10', 'Jl. Pendidikan No. 36', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, '20260037', '002026000037', 'Kevin Alfarizi', 'L', '2009-02-11', 'Jl. Pendidikan No. 37', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, '20260038', '002026000038', 'Laila Mutiara', 'P', '2010-03-12', 'Jl. Pendidikan No. 38', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, '20260039', '002026000039', 'Miko Aditya', 'L', '2008-04-13', 'Jl. Pendidikan No. 39', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, '20260040', '002026000040', 'Nia Kartika', 'P', '2009-05-14', 'Jl. Pendidikan No. 40', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, '20260041', '002026000041', 'Opik Firmansyah', 'L', '2010-06-15', 'Jl. Pendidikan No. 41', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, '20260042', '002026000042', 'Puspa Sari', 'P', '2008-07-16', 'Jl. Pendidikan No. 42', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, '20260043', '002026000043', 'Rian Kurnia', 'L', '2009-08-17', 'Jl. Pendidikan No. 43', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, '20260044', '002026000044', 'Siti Aisyah', 'P', '2010-09-18', 'Jl. Pendidikan No. 44', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, '20260045', '002026000045', 'Tio Ramadhan', 'L', '2008-10-19', 'Jl. Pendidikan No. 45', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, '20260046', '002026000046', 'Ulfa Nurfadilah', 'P', '2009-11-20', 'Jl. Pendidikan No. 46', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, '20260047', '002026000047', 'Vera Anjani', 'L', '2010-12-21', 'Jl. Pendidikan No. 47', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, '20260048', '002026000048', 'Wawan Gunawan', 'P', '2008-01-22', 'Jl. Pendidikan No. 48', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, '20260049', '002026000049', 'Yuni Astuti', 'L', '2009-02-23', 'Jl. Pendidikan No. 49', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, '20260050', '002026000050', 'Zaki Mubarak', 'P', '2010-03-24', 'Jl. Pendidikan No. 50', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_tahun_ajaran`
--

CREATE TABLE `t_tahun_ajaran` (
  `id` int(11) NOT NULL,
  `nama` varchar(20) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_tahun_ajaran`
--

INSERT INTO `t_tahun_ajaran` (`id`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, '2027/2028', '2027-07-01', '2028-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, '2028/2029', '2028-07-01', '2029-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, '2029/2030', '2029-07-01', '2030-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, '2030/2031', '2030-07-01', '2031-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, '2031/2032', '2031-07-01', '2032-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, '2032/2033', '2032-07-01', '2033-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, '2033/2034', '2033-07-01', '2034-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, '2034/2035', '2034-07-01', '2035-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, '2035/2036', '2035-07-01', '2036-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, '2036/2037', '2036-07-01', '2037-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, '2037/2038', '2037-07-01', '2038-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, '2038/2039', '2038-07-01', '2039-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, '2039/2040', '2039-07-01', '2040-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, '2040/2041', '2040-07-01', '2041-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, '2041/2042', '2041-07-01', '2042-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, '2042/2043', '2042-07-01', '2043-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, '2043/2044', '2043-07-01', '2044-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, '2044/2045', '2044-07-01', '2045-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, '2045/2046', '2045-07-01', '2046-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, '2046/2047', '2046-07-01', '2047-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, '2047/2048', '2047-07-01', '2048-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, '2048/2049', '2048-07-01', '2049-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, '2049/2050', '2049-07-01', '2050-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, '2050/2051', '2050-07-01', '2051-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, '2051/2052', '2051-07-01', '2052-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, '2052/2053', '2052-07-01', '2053-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, '2053/2054', '2053-07-01', '2054-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, '2054/2055', '2054-07-01', '2055-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, '2055/2056', '2055-07-01', '2056-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, '2056/2057', '2056-07-01', '2057-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, '2057/2058', '2057-07-01', '2058-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, '2058/2059', '2058-07-01', '2059-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, '2059/2060', '2059-07-01', '2060-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, '2060/2061', '2060-07-01', '2061-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, '2061/2062', '2061-07-01', '2062-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, '2062/2063', '2062-07-01', '2063-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, '2063/2064', '2063-07-01', '2064-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, '2064/2065', '2064-07-01', '2065-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, '2065/2066', '2065-07-01', '2066-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, '2066/2067', '2066-07-01', '2067-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, '2067/2068', '2067-07-01', '2068-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, '2068/2069', '2068-07-01', '2069-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, '2069/2070', '2069-07-01', '2070-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, '2070/2071', '2070-07-01', '2071-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, '2071/2072', '2071-07-01', '2072-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, '2072/2073', '2072-07-01', '2073-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, '2073/2074', '2073-07-01', '2074-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, '2074/2075', '2074-07-01', '2075-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, '2075/2076', '2075-07-01', '2076-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_users`
--

CREATE TABLE `t_users` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL,
  `email` varchar(225) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT current_timestamp(),
  `password` varchar(225) NOT NULL,
  `remember_token` varchar(100) NOT NULL,
  `role` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_users`
--

INSERT INTO `t_users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Pengguna 1', 'user1@sekolah.sch.id', '2026-01-01 01:00:00', 'password1', 'token001', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, 'Pengguna 2', 'user2@sekolah.sch.id', '2026-01-01 01:00:00', 'password2', 'token002', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, 'Pengguna 3', 'user3@sekolah.sch.id', '2026-01-01 01:00:00', 'password3', 'token003', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, 'Pengguna 4', 'user4@sekolah.sch.id', '2026-01-01 01:00:00', 'password4', 'token004', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, 'Pengguna 5', 'user5@sekolah.sch.id', '2026-01-01 01:00:00', 'password5', 'token005', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, 'Pengguna 6', 'user6@sekolah.sch.id', '2026-01-01 01:00:00', 'password6', 'token006', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, 'Pengguna 7', 'user7@sekolah.sch.id', '2026-01-01 01:00:00', 'password7', 'token007', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, 'Pengguna 8', 'user8@sekolah.sch.id', '2026-01-01 01:00:00', 'password8', 'token008', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, 'Pengguna 9', 'user9@sekolah.sch.id', '2026-01-01 01:00:00', 'password9', 'token009', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, 'Pengguna 10', 'user10@sekolah.sch.id', '2026-01-01 01:00:00', 'password10', 'token010', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, 'Pengguna 11', 'user11@sekolah.sch.id', '2026-01-01 01:00:00', 'password11', 'token011', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, 'Pengguna 12', 'user12@sekolah.sch.id', '2026-01-01 01:00:00', 'password12', 'token012', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, 'Pengguna 13', 'user13@sekolah.sch.id', '2026-01-01 01:00:00', 'password13', 'token013', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, 'Pengguna 14', 'user14@sekolah.sch.id', '2026-01-01 01:00:00', 'password14', 'token014', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, 'Pengguna 15', 'user15@sekolah.sch.id', '2026-01-01 01:00:00', 'password15', 'token015', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, 'Pengguna 16', 'user16@sekolah.sch.id', '2026-01-01 01:00:00', 'password16', 'token016', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, 'Pengguna 17', 'user17@sekolah.sch.id', '2026-01-01 01:00:00', 'password17', 'token017', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, 'Pengguna 18', 'user18@sekolah.sch.id', '2026-01-01 01:00:00', 'password18', 'token018', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, 'Pengguna 19', 'user19@sekolah.sch.id', '2026-01-01 01:00:00', 'password19', 'token019', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, 'Pengguna 20', 'user20@sekolah.sch.id', '2026-01-01 01:00:00', 'password20', 'token020', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, 'Pengguna 21', 'user21@sekolah.sch.id', '2026-01-01 01:00:00', 'password21', 'token021', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, 'Pengguna 22', 'user22@sekolah.sch.id', '2026-01-01 01:00:00', 'password22', 'token022', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, 'Pengguna 23', 'user23@sekolah.sch.id', '2026-01-01 01:00:00', 'password23', 'token023', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, 'Pengguna 24', 'user24@sekolah.sch.id', '2026-01-01 01:00:00', 'password24', 'token024', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, 'Pengguna 25', 'user25@sekolah.sch.id', '2026-01-01 01:00:00', 'password25', 'token025', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, 'Pengguna 26', 'user26@sekolah.sch.id', '2026-01-01 01:00:00', 'password26', 'token026', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, 'Pengguna 27', 'user27@sekolah.sch.id', '2026-01-01 01:00:00', 'password27', 'token027', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, 'Pengguna 28', 'user28@sekolah.sch.id', '2026-01-01 01:00:00', 'password28', 'token028', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, 'Pengguna 29', 'user29@sekolah.sch.id', '2026-01-01 01:00:00', 'password29', 'token029', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, 'Pengguna 30', 'user30@sekolah.sch.id', '2026-01-01 01:00:00', 'password30', 'token030', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, 'Pengguna 31', 'user31@sekolah.sch.id', '2026-01-01 01:00:00', 'password31', 'token031', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, 'Pengguna 32', 'user32@sekolah.sch.id', '2026-01-01 01:00:00', 'password32', 'token032', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, 'Pengguna 33', 'user33@sekolah.sch.id', '2026-01-01 01:00:00', 'password33', 'token033', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, 'Pengguna 34', 'user34@sekolah.sch.id', '2026-01-01 01:00:00', 'password34', 'token034', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, 'Pengguna 35', 'user35@sekolah.sch.id', '2026-01-01 01:00:00', 'password35', 'token035', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, 'Pengguna 36', 'user36@sekolah.sch.id', '2026-01-01 01:00:00', 'password36', 'token036', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, 'Pengguna 37', 'user37@sekolah.sch.id', '2026-01-01 01:00:00', 'password37', 'token037', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, 'Pengguna 38', 'user38@sekolah.sch.id', '2026-01-01 01:00:00', 'password38', 'token038', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, 'Pengguna 39', 'user39@sekolah.sch.id', '2026-01-01 01:00:00', 'password39', 'token039', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, 'Pengguna 40', 'user40@sekolah.sch.id', '2026-01-01 01:00:00', 'password40', 'token040', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, 'Pengguna 41', 'user41@sekolah.sch.id', '2026-01-01 01:00:00', 'password41', 'token041', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, 'Pengguna 42', 'user42@sekolah.sch.id', '2026-01-01 01:00:00', 'password42', 'token042', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, 'Pengguna 43', 'user43@sekolah.sch.id', '2026-01-01 01:00:00', 'password43', 'token043', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, 'Pengguna 44', 'user44@sekolah.sch.id', '2026-01-01 01:00:00', 'password44', 'token044', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, 'Pengguna 45', 'user45@sekolah.sch.id', '2026-01-01 01:00:00', 'password45', 'token045', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, 'Pengguna 46', 'user46@sekolah.sch.id', '2026-01-01 01:00:00', 'password46', 'token046', 'admin', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, 'Pengguna 47', 'user47@sekolah.sch.id', '2026-01-01 01:00:00', 'password47', 'token047', 'guru', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, 'Pengguna 48', 'user48@sekolah.sch.id', '2026-01-01 01:00:00', 'password48', 'token048', 'wali_kelas', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, 'Pengguna 49', 'user49@sekolah.sch.id', '2026-01-01 01:00:00', 'password49', 'token049', 'siswa', '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, 'Pengguna 50', 'user50@sekolah.sch.id', '2026-01-01 01:00:00', 'password50', 'token050', 'petugas', '2026-01-01 01:00:00', '2026-09-23 01:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_wali_kelas`
--

CREATE TABLE `t_wali_kelas` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `t_wali_kelas`
--

INSERT INTO `t_wali_kelas` (`id`, `tahun_ajaran_id`, `kelas_id`, `guru_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(2, 2, 2, 2, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(3, 3, 3, 3, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(4, 4, 4, 4, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(5, 5, 5, 5, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(6, 6, 6, 6, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(7, 7, 7, 7, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(8, 8, 8, 8, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(9, 9, 9, 9, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(10, 10, 10, 10, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(11, 11, 11, 11, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(12, 12, 12, 12, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(13, 13, 13, 13, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(14, 14, 14, 14, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(15, 15, 15, 15, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(16, 16, 16, 16, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(17, 17, 17, 17, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(18, 18, 18, 18, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(19, 19, 19, 19, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(20, 20, 20, 20, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(21, 21, 21, 21, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(22, 22, 22, 22, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(23, 23, 23, 23, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(24, 24, 24, 24, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(25, 25, 25, 25, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(26, 26, 26, 26, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(27, 27, 27, 27, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(28, 28, 28, 28, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(29, 29, 29, 29, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(30, 30, 30, 30, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(31, 31, 31, 31, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(32, 32, 32, 32, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(33, 33, 33, 33, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(34, 34, 34, 34, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(35, 35, 35, 35, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(36, 36, 36, 36, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(37, 37, 37, 37, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(38, 38, 38, 38, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(39, 39, 39, 39, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(40, 40, 40, 40, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(41, 41, 41, 41, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(42, 42, 42, 42, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(43, 43, 43, 43, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(44, 44, 44, 44, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(45, 45, 45, 45, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(46, 46, 46, 46, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(47, 47, 47, 47, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(48, 48, 48, 48, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(49, 49, 49, 49, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00'),
(50, 50, 50, 50, '2026-07-01', '2027-06-30', 1, '2026-01-01 01:00:00', '2026-09-23 01:00:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `t_guru`
--
ALTER TABLE `t_guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `t_kelas`
--
ALTER TABLE `t_kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`);

--
-- Indeks untuk tabel `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pelanggaran_id` (`pelanggaran_id`),
  ADD KEY `kelas_id_2` (`kelas_id`),
  ADD KEY `pelanggaran_id_2` (`pelanggaran_id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indeks untuk tabel `t_siswa`
--
ALTER TABLE `t_siswa`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_users`
--
ALTER TABLE `t_users`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `t_guru`
--
ALTER TABLE `t_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_kelas`
--
ALTER TABLE `t_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_siswa`
--
ALTER TABLE `t_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_users`
--
ALTER TABLE `t_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `t_guru`
--
ALTER TABLE `t_guru`
  ADD CONSTRAINT `t_guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t_users` (`id`);

--
-- Ketidakleluasaan untuk tabel `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD CONSTRAINT `t_kelas_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`),
  ADD CONSTRAINT `t_kelas_siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`),
  ADD CONSTRAINT `t_kelas_siswa_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`);

--
-- Ketidakleluasaan untuk tabel `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD CONSTRAINT `t_pelanggaran_ibfk_1` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_3` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_4` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_5` FOREIGN KEY (`pelanggaran_id`) REFERENCES `t_pelanggaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_6` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD CONSTRAINT `t_wali_kelas_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_wali_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`),
  ADD CONSTRAINT `t_wali_kelas_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
