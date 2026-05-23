-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Bulan Mei 2026 pada 12.25
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `checkvaluethriftyippee`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `aset_baju`
--

CREATE TABLE `aset_baju` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `harga_thrift` int(11) DEFAULT NULL,
  `harga_baru` int(11) DEFAULT NULL,
  `skor_ovr` decimal(5,2) DEFAULT NULL,
  `harga_wajar` int(11) DEFAULT NULL,
  `status_keputusan` varchar(50) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `status_stok` varchar(20) DEFAULT 'Tersedia',
  `skor_warna` int(11) DEFAULT 5,
  `skor_bahan` int(11) DEFAULT 5,
  `skor_kualitas` int(11) DEFAULT 5,
  `skor_gaya` int(11) DEFAULT 5,
  `skor_fit` int(11) DEFAULT 5,
  `skor_fungsi` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aset_baju`
--

INSERT INTO `aset_baju` (`id`, `nama_barang`, `merk`, `harga_thrift`, `harga_baru`, `skor_ovr`, `harga_wajar`, `status_keputusan`, `tanggal`, `status_stok`, `skor_warna`, `skor_bahan`, `skor_kualitas`, `skor_gaya`, `skor_fit`, `skor_fungsi`) VALUES
(7, 'TS Varsity', 'Touchh', 350000, 425000, 63.00, 267750, 'REJECT (Overpriced)', '2026-05-23', 'Tersedia', 5, 5, 5, 5, 5, 5),
(8, 'T-Shirt', 'Versace', 500000, 700000, 67.00, 469000, 'REJECT (Overpriced)', '2026-05-23', 'Tersedia', 5, 5, 5, 5, 5, 5),
(9, 'celana dalam', 'klll', 100000, 175000, 71.00, 124250, 'LAYAK (Sesuai Harga)', '2026-05-23', 'Terjual', 7, 8, 9, 5, 7, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `harga_thrift` int(11) DEFAULT NULL,
  `harga_baru` int(11) DEFAULT NULL,
  `kondisi` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `saran` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `nama_barang`, `merk`, `harga_thrift`, `harga_baru`, `kondisi`, `tanggal`, `saran`) VALUES
(16, 'Jaket Bomber ', 'Fila', 200000, 300000, 89, '2026-04-06', 'LAYAK (HARGA WAJAR)'),
(17, 'Denim ', 'Levi\'s', 100000, 700000, 99, '2026-04-06', 'SANGAT LAYAK (MURAH BANGET!)'),
(18, 'Topi', 'Valhallaaaaaa', 100000, 97000, 100, '2026-04-06', 'TIDAK LAYAK (MENDING BELI BARU)');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `aset_baju`
--
ALTER TABLE `aset_baju`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aset_baju`
--
ALTER TABLE `aset_baju`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
