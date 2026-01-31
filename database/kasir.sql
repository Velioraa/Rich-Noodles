-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Nov 2025 pada 13.07
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `cookie` varchar(255) DEFAULT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `email`, `username`, `password`, `gambar`, `cookie`, `last_login`, `created_at`) VALUES
(41, 'ancakhnsa@gmail.com', 'Khansa', '$2y$10$AU938LwUibvVIPvJxWtQ8OIPMFKbdW4W3u1uuoT8dGid/dKkBcy3.', 'asset/1758728270_мι¢к мєтαѕ σραѕ- ιαмкαנσяη.jpg', '', '2025-11-06 11:53:27', '2025-09-24 18:08:47'),
(47, 'joongdunk@gmail.com', 'Joong Dunk', '$2y$10$rW9fd3flUoPvsTyqUpVjLuWMAWNw0wibgHxbAbj0L0WpHqLuFZEXe', 'asset/1761385244_jaidee.jpeg', NULL, '2025-10-25 09:40:44', '2025-10-25 16:40:44'),
(48, 'pondphuwin@gmail.com', 'pond phuwin', '$2y$10$3PpwoiAoSNAUeWVA6jMbe.z5Vb7WysBKvUqA3J3Yy/kK0IO6Tjtxy', 'asset/1761385866_prempoon.jpeg', NULL, '2025-10-25 09:51:06', '2025-10-25 16:51:06'),
(49, 'aouboom@gmail.com', 'aouboom', '$2y$10$SZ3oOxXSeF0G4StWHlAC3OUQBqIRLbssHfSYALlhu/yO1O2q4oPT6', 'asset/1761386032_ceri.jpeg', NULL, '2025-10-25 09:53:52', '2025-10-25 16:53:52'),
(50, 'metas@gmail.com', 'metas', '$2y$10$TjVa9dYeKxgMVviUxcD74.TNa.IoKaTVwwVgKANSENAzPKuBnI0HK', 'asset/1761386211_1742394239_IMG_9370.JPG', NULL, '2025-10-25 09:56:51', '2025-10-25 16:56:51'),
(52, 'mtsmck6@gmail.com', 'ceri', '$2y$10$/FMveQlW4RRW17PPARoLreFsusuDBjJFfyOXzx.IogwsxgKGlHjtC', 'asset/1762430091_ceri.jpeg', '', '2025-11-06 12:03:57', '2025-11-06 18:54:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `waktu_masuk` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('checked','unchecked') NOT NULL DEFAULT 'unchecked',
  `fid_admin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail_transaksi` int(11) NOT NULL,
  `fid_transaksi` int(11) NOT NULL,
  `fid_produk` int(11) NOT NULL,
  `fid_member` int(11) DEFAULT NULL,
  `total_produk` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail_transaksi`, `fid_transaksi`, `fid_produk`, `fid_member`, `total_produk`, `subtotal`) VALUES
(104, 326, 49, NULL, 1, 25000),
(108, 330, 49, NULL, 1, 25000),
(114, 335, 51, NULL, 1, 22000),
(115, 336, 58, NULL, 1, 21100),
(116, 337, 49, NULL, 1, 25000),
(120, 341, 51, NULL, 1, 22000),
(123, 344, 51, NULL, 1, 22000),
(125, 346, 58, NULL, 1, 21100),
(127, 348, 51, NULL, 1, 22000),
(129, 350, 51, NULL, 1, 22000),
(131, 352, 49, NULL, 1, 25000),
(132, 353, 49, NULL, 1, 25000),
(133, 354, 49, NULL, 1, 25000),
(134, 355, 49, NULL, 1, 25000),
(135, 356, 49, NULL, 1, 25000),
(136, 357, 49, NULL, 1, 25000),
(137, 358, 49, NULL, 1, 25000),
(138, 359, 51, NULL, 1, 22000),
(139, 360, 49, NULL, 1, 25000),
(140, 361, 51, NULL, 1, 22000),
(141, 362, 51, NULL, 1, 22000),
(142, 363, 51, NULL, 1, 22000),
(143, 364, 51, NULL, 1, 22000),
(144, 365, 49, NULL, 1, 25000),
(145, 366, 49, NULL, 1, 25000),
(146, 367, 49, NULL, 1, 25000),
(147, 368, 51, NULL, 1, 22000),
(148, 369, 51, NULL, 1, 22000),
(149, 370, 51, NULL, 1, 22000),
(150, 371, 51, NULL, 1, 22000),
(151, 372, 49, NULL, 1, 25000),
(152, 373, 49, NULL, 1, 25000),
(153, 374, 51, NULL, 1, 22000),
(154, 375, 51, NULL, 1, 22000),
(155, 376, 51, NULL, 1, 22000),
(156, 377, 51, NULL, 1, 22000),
(157, 378, 49, NULL, 1, 25000),
(158, 379, 49, NULL, 1, 25000),
(159, 380, 51, NULL, 1, 22000),
(160, 381, 49, NULL, 1, 25000),
(161, 382, 51, NULL, 2, 44000),
(162, 382, 49, NULL, 3, 75000),
(163, 383, 49, NULL, 1, 25000),
(164, 384, 51, NULL, 1, 22000),
(165, 385, 49, NULL, 1, 25000),
(166, 386, 58, NULL, 2, 42200),
(167, 386, 51, NULL, 2, 44000),
(168, 386, 49, NULL, 2, 50000),
(169, 387, 49, NULL, 1, 25000),
(170, 387, 63, NULL, 1, 2500),
(171, 388, 51, NULL, 1, 22000),
(172, 389, 64, NULL, 1, 3000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `kategori` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `kategori`) VALUES
(34, 'Samyang'),
(35, 'MAMA OK'),
(36, 'Gaga'),
(40, 'Indomie'),
(43, 'Lemonilo');

-- --------------------------------------------------------

--
-- Struktur dari tabel `member`
--

CREATE TABLE `member` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(225) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `point` int(11) NOT NULL,
  `status` enum('aktif','tidak aktif') NOT NULL DEFAULT 'tidak aktif',
  `last_transaction` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `member`
--

INSERT INTO `member` (`id`, `email`, `username`, `password`, `no_telp`, `gambar`, `point`, `status`, `last_transaction`, `created_at`) VALUES
(48, '', 'Metas', '', '0813-1079-9098', '', 0, 'aktif', '2025-10-25 10:50:27', '2025-10-25 10:50:27'),
(49, '', 'Nanon', '', '0813-1887-3141', '', 0, 'aktif', '2025-10-25 10:50:37', '2025-10-25 10:50:37'),
(50, '', 'Chimon', '', '0821-1169-7227', '', 0, 'aktif', '2025-10-25 10:50:49', '2025-10-25 10:50:49'),
(51, '', 'Boom', '', '0882-1026-6308', '', 0, 'aktif', '2025-10-25 10:51:21', '2025-10-25 10:51:21'),
(52, '', 'Dunk', '', '0882-9366-9140', '', 0, 'aktif', '2025-10-25 10:52:04', '2025-10-25 10:52:04'),
(53, '', 'Namon', '', '0856-5575-4527', '', 2, 'aktif', '2025-10-25 11:13:56', '2025-10-25 11:13:56'),
(54, '', 'jaidee', '', '0858-8173-8997', '', 0, 'aktif', '2025-11-06 11:57:35', '2025-11-06 11:57:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `metode_pembayaran`
--

CREATE TABLE `metode_pembayaran` (
  `id_metode_pembayaran` int(11) NOT NULL,
  `nama_metode` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `metode_pembayaran`
--

INSERT INTO `metode_pembayaran` (`id_metode_pembayaran`, `nama_metode`) VALUES
(2, 'Tunai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `tanggal_expired` date NOT NULL,
  `stok` int(11) NOT NULL,
  `modal` int(11) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `keuntungan` int(11) NOT NULL,
  `fid_kategori` int(11) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `kode_barcode` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `tanggal_expired`, `stok`, `modal`, `harga_jual`, `keuntungan`, `fid_kategori`, `brand`, `gambar`, `deskripsi`, `kode_barcode`) VALUES
(49, 'Samyang Buldak Sup Ramen Ayam Pedas', '2025-11-30', 0, 20000, 25000, 5000, 34, 'Indomie', '1758543301_Samyang Buldak Sup Ramen Ayam Pedas.png', 'Enak banget guys', NULL),
(51, 'Hot Korean Noodles', '2025-10-31', 9, 16000, 22000, 6000, 35, 'Indomie', '1758544186_Hot Korean Noodles.jpeg', 'Enak banget soalnya lagu nya enak di denger', NULL),
(58, 'Shrimp Stir Fried Tomyum', '2025-10-25', 22, 20000, 21100, 1100, 35, 'Indomie', '1758728145_Shrimp Stir Fried Tomyum.jpeg', 'singto', NULL),
(63, 'Mie Gaga 100', '2025-11-01', 39, 1500, 2500, 1000, 36, '', '1761386601_jalapeno.png', 'pedes', '020959193472'),
(64, 'Indomie Rendang', '2025-11-08', 24, 2100, 3000, 900, 40, '', '1761391033_Goreng.jpeg', 'enak', '997614044460'),
(65, 'eMir', '2025-11-30', 10, 5000, 7000, 2000, 43, '', '1762430468_Goreng.jpeg', 'AZEK', '672584982961');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `total_harga` int(11) DEFAULT NULL,
  `fid_admin` int(11) NOT NULL,
  `total_bayar` int(11) NOT NULL,
  `fid_metode_pembayaran` int(11) NOT NULL,
  `total_kembalian` int(11) NOT NULL,
  `total_keuntungan` int(11) NOT NULL,
  `fid_member` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_transaksi`, `total_harga`, `fid_admin`, `total_bayar`, `fid_metode_pembayaran`, `total_kembalian`, `total_keuntungan`, `fid_member`) VALUES
(326, '2025-09-24', 25000, 41, 25000, 2, 3000, 5000, NULL),
(327, '2025-09-24', 3500, 41, 500, 2, 0, 1500, NULL),
(330, '2025-09-24', 25000, 41, 30000, 2, 5000, 5000, NULL),
(331, '2025-09-25', 3500, 41, 3500, 2, 0, 1500, NULL),
(332, '2025-09-25', 3500, 41, 4000, 2, 500, 1500, NULL),
(333, '2025-09-25', 4000, 41, 5000, 2, 1000, 1000, NULL),
(334, '2025-09-25', 7000, 41, 8000, 2, 3000, 1500, NULL),
(335, '2025-09-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(336, '2025-09-25', 21100, 41, 21100, 2, 0, 1100, NULL),
(337, '2025-09-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(338, '2025-09-25', 4000, 41, 5000, 2, 1000, 1000, NULL),
(340, '2025-09-25', 4000, 41, 5000, 2, 1000, 1000, NULL),
(341, '2025-09-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(342, '2025-10-01', 4000, 41, 5000, 2, 1000, 1000, NULL),
(343, '2025-10-08', 4000, 41, 4000, 2, 0, 1000, NULL),
(344, '2025-10-08', 22000, 41, 25000, 2, 3000, 6000, NULL),
(345, '2025-10-08', 4000, 41, 5000, 2, 1000, 1000, NULL),
(346, '2025-10-08', 21100, 41, 22000, 2, 900, 1100, NULL),
(347, '2025-10-08', 4000, 41, 5000, 2, 1000, 1000, NULL),
(348, '2025-10-08', 22000, 41, 22000, 2, 0, 6000, NULL),
(349, '2025-10-08', 4000, 41, 5000, 2, 1000, 1000, NULL),
(350, '2025-10-09', 22000, 41, 25000, 2, 3000, 6000, NULL),
(351, '2025-10-09', 4000, 41, 5000, 2, 1000, 1000, NULL),
(352, '2025-10-23', 25000, 41, 25000, 2, 0, 5000, NULL),
(353, '2025-10-23', 25000, 41, 25000, 2, 0, 5000, NULL),
(354, '2025-10-23', 25000, 41, 25000, 2, 0, 5000, NULL),
(355, '2025-10-23', 25000, 41, 25000, 2, 0, 5000, NULL),
(356, '2025-10-23', 25000, 41, 25000, 2, 0, 5000, NULL),
(357, '2025-10-25', 25000, 41, 50000, 2, 25000, 5000, NULL),
(358, '2025-10-25', 25000, 41, 30000, 2, 5000, 5000, NULL),
(359, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(360, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(361, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(362, '2025-10-25', 22000, 41, 25000, 2, 3000, 6000, NULL),
(363, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(364, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(365, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(366, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(367, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(368, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(369, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(370, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(371, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(372, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(373, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(374, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(375, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(376, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(377, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(378, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(379, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(380, '2025-10-25', 22000, 41, 22000, 2, 0, 6000, NULL),
(381, '2025-10-25', 25000, 41, 25000, 2, 0, 5000, NULL),
(382, '2025-10-25', 119000, 41, 120000, 2, 1000, 27000, NULL),
(383, '2025-10-25', 25000, 41, 13000, 2, 0, 5000, NULL),
(384, '2025-10-25', 22000, 45, 22000, 2, 0, 6000, NULL),
(385, '2025-10-25', 25000, 45, 25000, 2, 0, 5000, NULL),
(386, '2025-10-25', 136200, 41, 140000, 2, 3800, 24200, NULL),
(387, '2025-10-25', 27500, 41, 28000, 2, 500, 6000, NULL),
(388, '2025-10-25', 22000, 41, 25000, 2, 3000, 6000, NULL),
(389, '2025-11-06', 3000, 52, 10000, 2, 7000, 900, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail_transaksi`),
  ADD UNIQUE KEY `fid_member` (`fid_member`),
  ADD KEY `FK 4` (`fid_produk`),
  ADD KEY `FK 5` (`fid_transaksi`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  ADD PRIMARY KEY (`id_metode_pembayaran`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `FK 1` (`fid_kategori`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `FK 2` (`fid_admin`),
  ADD KEY `FK 6` (`fid_metode_pembayaran`),
  ADD KEY `fk_transaksi_member` (`fid_member`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=427;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `member`
--
ALTER TABLE `member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  MODIFY `id_metode_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=390;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `FK 3` FOREIGN KEY (`fid_member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK 4` FOREIGN KEY (`fid_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK 5` FOREIGN KEY (`fid_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_3` FOREIGN KEY (`fid_member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `FK 1` FOREIGN KEY (`fid_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `FK 6` FOREIGN KEY (`fid_metode_pembayaran`) REFERENCES `metode_pembayaran` (`id_metode_pembayaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_member` FOREIGN KEY (`fid_member`) REFERENCES `member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
