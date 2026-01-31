-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Jan 2026 pada 05.18
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
-- Database: `toko_buku`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_chat_contacts` (IN `p_user_id` INT)   BEGIN
    -- Untuk user pembeli (bisa chat dengan penjual atau admin)
    IF EXISTS (SELECT 1 FROM users WHERE id_user = p_user_id AND role = 'pembeli') THEN
        -- Ambil semua penjual yang pernah mengirim/menerima pesan
        SELECT DISTINCT 
            u.id_user as contact_id,
            u.username as contact_name,
            u.role as contact_role,
            u.gambar as contact_image,
            COALESCE(
                (SELECT c.isi_pesan 
                 FROM chat c 
                 WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                    OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
                 ORDER BY c.waktu_kirim DESC 
                 LIMIT 1),
                'Belum ada pesan'
            ) as last_message,
            COALESCE(
                (SELECT c.waktu_kirim 
                 FROM chat c 
                 WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                    OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
                 ORDER BY c.waktu_kirim DESC 
                 LIMIT 1),
                NOW()
            ) as last_message_time,
            COALESCE(
                (SELECT COUNT(*) 
                 FROM chat c 
                 WHERE c.id_penerima = p_user_id 
                   AND c.id_pengirim = u.id_user 
                   AND c.status_baca = 'terkirim'),
                0
            ) as unread_count,
            (SELECT COUNT(*) 
             FROM chat c 
             WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                OR (c.id_pengirim = u.id_id_user AND c.id_penerima = p_user_id)) as total_messages
        FROM users u
        WHERE u.role IN ('penjual', 'admin')
          AND u.id_user != p_user_id
          AND EXISTS (
              SELECT 1 FROM chat c 
              WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                 OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
          )
        ORDER BY last_message_time DESC;
        
    -- Untuk user penjual (bisa chat dengan pembeli)
    ELSEIF EXISTS (SELECT 1 FROM users WHERE id_user = p_user_id AND role = 'penjual') THEN
        -- Ambil semua pembeli yang pernah mengirim/menerima pesan
        SELECT DISTINCT 
            u.id_user as contact_id,
            u.username as contact_name,
            u.role as contact_role,
            u.gambar as contact_image,
            COALESCE(
                (SELECT c.isi_pesan 
                 FROM chat c 
                 WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                    OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
                 ORDER BY c.waktu_kirim DESC 
                 LIMIT 1),
                'Belum ada pesan'
            ) as last_message,
            COALESCE(
                (SELECT c.waktu_kirim 
                 FROM chat c 
                 WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                    OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
                 ORDER BY c.waktu_kirim DESC 
                 LIMIT 1),
                NOW()
            ) as last_message_time,
            COALESCE(
                (SELECT COUNT(*) 
                 FROM chat c 
                 WHERE c.id_penerima = p_user_id 
                   AND c.id_pengirim = u.id_user 
                   AND c.status_baca = 'terkirim'),
                0
            ) as unread_count,
            (SELECT COUNT(*) 
             FROM chat c 
             WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)) as total_messages
        FROM users u
        WHERE u.role = 'pembeli'
          AND u.id_user != p_user_id
          AND EXISTS (
              SELECT 1 FROM chat c 
              WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                 OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
          )
        ORDER BY last_message_time DESC;
        
    -- Untuk admin (bisa chat dengan semua user)
    ELSE
        -- Ambil semua user yang pernah mengirim/menerima pesan
        SELECT DISTINCT 
            u.id_user as contact_id,
            u.username as contact_name,
            u.role as contact_role,
            u.gambar as contact_image,
            COALESCE(
                (SELECT c.isi_pesan 
                 FROM chat c 
                 WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                    OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
                 ORDER BY c.waktu_kirim DESC 
                 LIMIT 1),
                'Belum ada pesan'
            ) as last_message,
            COALESCE(
                (SELECT c.waktu_kirim 
                 FROM chat c 
                 WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                    OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
                 ORDER BY c.waktu_kirim DESC 
                 LIMIT 1),
                NOW()
            ) as last_message_time,
            COALESCE(
                (SELECT COUNT(*) 
                 FROM chat c 
                 WHERE c.id_penerima = p_user_id 
                   AND c.id_pengirim = u.id_user 
                   AND c.status_baca = 'terkirim'),
                0
            ) as unread_count,
            (SELECT COUNT(*) 
             FROM chat c 
             WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)) as total_messages
        FROM users u
        WHERE u.id_user != p_user_id
          AND EXISTS (
              SELECT 1 FROM chat c 
              WHERE (c.id_pengirim = p_user_id AND c.id_penerima = u.id_user)
                 OR (c.id_pengirim = u.id_user AND c.id_penerima = p_user_id)
          )
        ORDER BY last_message_time DESC;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

CREATE TABLE `chat` (
  `id_chat` int(11) NOT NULL,
  `id_pengirim` int(11) NOT NULL,
  `id_penerima` int(11) NOT NULL,
  `peran_pengirim` enum('pembeli','penjual','admin') NOT NULL,
  `isi_pesan` text NOT NULL,
  `waktu_kirim` datetime DEFAULT current_timestamp(),
  `status_baca` enum('terkirim','terbaca') DEFAULT 'terkirim',
  `tipe_pesan` enum('teks','gambar','file') DEFAULT 'teks',
  `nama_file` varchar(255) DEFAULT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT 0,
  `id_transaksi` int(11) DEFAULT 0,
  `tipe_file` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `chat`
--

INSERT INTO `chat` (`id_chat`, `id_pengirim`, `id_penerima`, `peran_pengirim`, `isi_pesan`, `waktu_kirim`, `status_baca`, `tipe_pesan`, `nama_file`, `ukuran_file`, `id_produk`, `id_transaksi`, `tipe_file`) VALUES
(1, 5, 4, 'pembeli', 'p', '2026-01-22 09:12:35', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(2, 5, 4, 'pembeli', 'ppp', '2026-01-22 16:08:33', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(3, 4, 5, 'penjual', 'woi', '2026-01-22 16:19:19', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(4, 4, 5, 'penjual', 'weh', '2026-01-22 16:32:34', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(5, 5, 4, 'pembeli', 'pp', '2026-01-22 17:23:21', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(6, 5, 4, 'pembeli', 'ppp', '2026-01-22 17:27:32', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(7, 5, 4, 'pembeli', 'ppp', '2026-01-22 17:32:48', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(8, 4, 5, 'penjual', 'pp', '2026-01-22 18:10:17', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(9, 4, 5, 'penjual', 'pagi bro', '2026-01-23 08:01:24', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(10, 4, 5, 'penjual', 'woi', '2026-01-23 08:44:12', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(11, 4, 5, 'penjual', 'anjai', '2026-01-23 09:09:30', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(12, 4, 5, 'penjual', 'p', '2026-01-23 09:09:42', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(13, 4, 5, 'penjual', 'p', '2026-01-23 09:09:55', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(14, 4, 5, 'penjual', 'p', '2026-01-23 09:51:59', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(15, 5, 4, 'pembeli', 'pagi', '2026-01-23 12:19:36', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(16, 5, 4, 'pembeli', 'p', '2026-01-23 12:38:15', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(17, 5, 4, 'pembeli', 'p', '2026-01-23 13:03:23', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(18, 5, 4, 'pembeli', 'dean', '2026-01-25 13:58:41', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(19, 4, 5, 'penjual', 'ppp', '2026-01-29 07:46:42', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(20, 4, 5, 'penjual', 'PP', '2026-01-29 08:58:54', 'terbaca', 'teks', NULL, NULL, 0, 0, NULL),
(21, 5, 4, 'pembeli', 'PP', '2026-01-29 09:00:19', 'terkirim', 'teks', NULL, NULL, 0, 0, NULL),
(22, 5, 4, 'pembeli', 'ppp', '2026-01-29 17:58:09', 'terkirim', 'teks', NULL, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `katagori`
--

CREATE TABLE `katagori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `katagori`
--

INSERT INTO `katagori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Novel'),
(3, 'Cerpen'),
(4, 'Romance'),
(5, 'Thriller'),
(6, 'Musik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(20) UNSIGNED NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_order` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `type` enum('notifikasi','pesan') DEFAULT 'notifikasi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_user`, `id_order`, `id_produk`, `judul`, `pesan`, `is_read`, `type`, `created_at`) VALUES
(1, 5, 7, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260122-2488 berhasil diproses. Menunggu approval penjual.', 1, 'notifikasi', '2026-01-22 11:24:19'),
(2, 4, 7, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260122-2488', 1, 'notifikasi', '2026-01-22 11:24:19'),
(3, 5, 3, NULL, 'Pesanan Dikirim', 'Pesanan Anda dengan invoice INV-20260121-2134 telah dikirim. No. Resi: JNE1234567890 (jne). Anda dapat melacak paket di: https://www.jne.co.id/id/tracking/trace?resi=JNE1234567890', 1, 'notifikasi', '2026-01-22 11:47:31'),
(4, 5, 1, NULL, 'Transaksi Ditolak', 'Transaksi Anda dengan invoice INV-20260121-4347 telah ditolak oleh penjual. Silakan hubungi penjual untuk informasi lebih lanjut.', 1, 'notifikasi', '2026-01-22 11:47:58'),
(5, 5, 6, NULL, 'Transaksi Disetujui', 'Transaksi Anda dengan invoice INV-20260122-0159 telah disetujui oleh penjual dan sedang diproses.', 1, 'notifikasi', '2026-01-22 11:48:22'),
(6, 5, 6, NULL, 'Pesanan Dikirim', 'Pesanan Anda dengan invoice INV-20260122-0159 telah dikirim. No. Resi: WHN123456789 (wahana). Anda dapat melacak paket di: https://www.wahana.com/cek-resi?resi=WHN123456789', 1, 'notifikasi', '2026-01-22 22:56:51'),
(7, 5, 2, NULL, 'Pesanan Dikirim', 'Pesanan Anda dengan invoice INV-20260121-0457 telah dikirim. No. Resi: POS123456789ID (pos). Anda dapat melacak paket di: https://www.posindonesia.co.id/id/tracking?resi=POS123456789ID', 1, 'notifikasi', '2026-01-22 23:47:00'),
(8, 5, 7, NULL, 'Transaksi Disetujui', 'Transaksi Anda dengan invoice INV-20260122-2488 telah disetujui oleh penjual dan sedang diproses.', 1, 'notifikasi', '2026-01-23 00:29:14'),
(9, 5, 8, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260123-3336 berhasil diproses. Menunggu approval penjual.', 1, 'notifikasi', '2026-01-23 05:38:43'),
(10, 4, 8, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Cinta Tak Ada Mati. Invoice: INV-20260123-3336', 1, 'notifikasi', '2026-01-23 05:38:43'),
(11, 5, 9, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260124-7742 berhasil diproses. Menunggu approval penjual.', 1, 'notifikasi', '2026-01-24 11:05:42'),
(12, 4, 9, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260124-7742', 1, 'notifikasi', '2026-01-24 11:05:42'),
(13, 5, 10, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260124-6306 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-24 12:08:47'),
(14, 4, 10, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Cinta Tak Ada Mati. Invoice: INV-20260124-6306', 1, 'notifikasi', '2026-01-24 12:08:47'),
(15, 4, 10, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice ', 1, 'notifikasi', '2026-01-24 12:09:02'),
(16, 4, 10, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice ', 1, 'notifikasi', '2026-01-24 12:09:12'),
(17, 4, 10, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice ', 1, 'notifikasi', '2026-01-24 12:09:22'),
(18, 5, 11, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260124-9589 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-24 12:17:57'),
(19, 4, 11, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260124-9589', 1, 'notifikasi', '2026-01-24 12:17:57'),
(20, 4, 11, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260124-9589', 1, 'notifikasi', '2026-01-24 12:18:06'),
(21, 5, 12, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260124-7738 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-24 12:28:45'),
(22, 4, 12, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260124-7738', 1, 'notifikasi', '2026-01-24 12:28:45'),
(23, 4, 12, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260124-7738', 1, 'notifikasi', '2026-01-24 12:29:04'),
(24, 5, 13, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260124-7860 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-24 12:45:34'),
(25, 4, 13, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Harga Sebuah Percaya . Invoice: INV-20260124-7860', 1, 'notifikasi', '2026-01-24 12:45:34'),
(26, 4, 13, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260124-7860', 1, 'notifikasi', '2026-01-24 12:45:46'),
(27, 5, 14, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260125-1993 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-25 06:51:05'),
(28, 4, 14, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Cinta Tak Ada Mati. Invoice: INV-20260125-1993', 1, 'notifikasi', '2026-01-25 06:51:05'),
(29, 4, 14, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260125-1993', 1, 'notifikasi', '2026-01-25 06:51:21'),
(30, 5, 15, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260125-3375 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-25 06:59:28'),
(31, 4, 15, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260125-3375', 1, 'notifikasi', '2026-01-25 06:59:28'),
(32, 5, 16, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260125-1415 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-25 07:05:11'),
(33, 4, 16, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260125-1415', 1, 'notifikasi', '2026-01-25 07:05:11'),
(34, 4, 16, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260125-1415', 1, 'notifikasi', '2026-01-25 07:06:05'),
(35, 5, 17, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260126-7105 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-26 07:34:14'),
(36, 4, 17, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Harga Sebuah Percaya . Invoice: INV-20260126-7105', 1, 'notifikasi', '2026-01-26 07:34:14'),
(37, 4, 17, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260126-7105', 1, 'notifikasi', '2026-01-26 07:44:09'),
(38, 5, 18, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260126-3361 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-26 13:30:45'),
(39, 4, 18, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Ibu, Aku Ngga Sekuat Itu . Invoice: INV-20260126-3361', 1, 'notifikasi', '2026-01-26 13:30:45'),
(40, 4, 18, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260126-3361', 1, 'notifikasi', '2026-01-26 13:31:06'),
(41, 5, 19, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260126-9999 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-26 13:44:28'),
(42, 4, 19, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Ibu, Aku Ngga Sekuat Itu . Invoice: INV-20260126-9999', 1, 'notifikasi', '2026-01-26 13:44:28'),
(43, 4, 19, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260126-9999', 1, 'notifikasi', '2026-01-26 13:44:54'),
(44, 5, 20, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-2285 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 12:01:04'),
(45, 8, 20, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260127-2285', 0, 'notifikasi', '2026-01-27 12:01:04'),
(46, 4, 20, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Harga Sebuah Percaya . Invoice: INV-20260127-2285', 1, 'notifikasi', '2026-01-27 12:01:04'),
(47, 8, 20, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-2285', 0, 'notifikasi', '2026-01-27 12:17:59'),
(48, 4, 20, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-2285', 1, 'notifikasi', '2026-01-27 12:17:59'),
(49, 5, 21, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-3754 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 12:30:48'),
(50, 4, 21, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Cinta Tak Ada Mati. Invoice: INV-20260127-3754', 1, 'notifikasi', '2026-01-27 12:30:48'),
(51, 8, 21, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260127-3754', 0, 'notifikasi', '2026-01-27 12:30:48'),
(52, 5, 21, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-3754', 1, 'notifikasi', '2026-01-27 12:51:42'),
(53, 5, 22, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-4798 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 13:01:12'),
(54, 8, 22, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260127-4798', 0, 'notifikasi', '2026-01-27 13:01:12'),
(55, 4, 22, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260127-4798', 1, 'notifikasi', '2026-01-27 13:01:12'),
(56, 5, 23, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-1772 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 13:18:50'),
(57, 8, 23, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260127-1772', 0, 'notifikasi', '2026-01-27 13:18:50'),
(58, 4, 23, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Ibu, Aku Ngga Sekuat Itu . Invoice: INV-20260127-1772', 1, 'notifikasi', '2026-01-27 13:18:50'),
(59, 4, 23, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-1772 sebesar Rp 145.000', 1, 'notifikasi', '2026-01-27 13:45:10'),
(60, 5, 24, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-9459 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 13:53:20'),
(61, 8, 24, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260127-9459', 0, 'notifikasi', '2026-01-27 13:53:20'),
(62, 4, 24, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Harga Sebuah Percaya . Invoice: INV-20260127-9459', 1, 'notifikasi', '2026-01-27 13:53:20'),
(63, 8, 24, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-9459', 0, 'notifikasi', '2026-01-27 14:02:29'),
(64, 4, 24, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-9459', 1, 'notifikasi', '2026-01-27 14:02:29'),
(65, 5, 25, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-7596 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 14:09:20'),
(66, 8, 25, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260127-7596', 0, 'notifikasi', '2026-01-27 14:09:20'),
(67, 4, 25, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260127-7596', 1, 'notifikasi', '2026-01-27 14:09:20'),
(68, 8, 25, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-7596', 0, 'notifikasi', '2026-01-27 14:09:40'),
(69, 4, 25, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-7596', 1, 'notifikasi', '2026-01-27 14:09:40'),
(70, 5, 26, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260127-0164 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-27 14:10:15'),
(71, 4, 26, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Cinta Tak Ada Mati. Invoice: INV-20260127-0164', 1, 'notifikasi', '2026-01-27 14:10:15'),
(72, 4, 26, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260127-0164', 1, 'notifikasi', '2026-01-27 14:11:17'),
(73, 5, 24, NULL, 'Transaksi Ditolak', 'Transaksi Anda dengan invoice INV-20260127-9459 telah ditolak oleh penjual. Silakan hubungi penjual untuk informasi lebih lanjut.', 1, 'notifikasi', '2026-01-27 14:11:43'),
(74, 5, 22, NULL, 'Transaksi Ditolak', 'Transaksi Anda dengan invoice INV-20260127-4798 telah ditolak oleh penjual. Silakan hubungi penjual untuk informasi lebih lanjut.', 1, 'notifikasi', '2026-01-27 14:11:48'),
(75, 5, 27, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260128-9339 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-28 13:17:17'),
(76, 8, 27, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260128-9339', 0, 'notifikasi', '2026-01-28 13:17:17'),
(77, 4, 27, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk:  Harga Sebuah Percaya . Invoice: INV-20260128-9339', 1, 'notifikasi', '2026-01-28 13:17:17'),
(78, 8, 27, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260128-9339', 0, 'notifikasi', '2026-01-28 13:17:40'),
(79, 4, 27, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260128-9339', 1, 'notifikasi', '2026-01-28 13:17:40'),
(80, 5, 28, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260128-2048 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-28 13:31:50'),
(81, 8, 28, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260128-2048', 0, 'notifikasi', '2026-01-28 13:31:50'),
(82, 8, 28, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260128-2048', 0, 'notifikasi', '2026-01-28 13:32:02'),
(83, 5, 29, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260128-2192 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-28 13:33:10'),
(84, 8, 29, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260128-2192', 0, 'notifikasi', '2026-01-28 13:33:10'),
(85, 4, 29, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: Enigma. Invoice: INV-20260128-2192', 1, 'notifikasi', '2026-01-28 13:33:10'),
(86, 8, 29, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260128-2192', 0, 'notifikasi', '2026-01-28 13:33:27'),
(87, 4, 29, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260128-2192', 1, 'notifikasi', '2026-01-28 13:33:27'),
(88, 5, 29, NULL, 'Transaksi Disetujui', 'Transaksi Anda dengan invoice INV-20260128-2192 telah disetujui oleh penjual dan sedang diproses.', 1, 'notifikasi', '2026-01-29 00:45:48'),
(89, 5, 29, NULL, 'Pesanan Dikirim', 'Pesanan Anda dengan invoice INV-20260128-2192 telah dikirim. No. Resi: JNE42324UY4 (j&t). Anda dapat melacak paket di: https://jet.co.id/track?resi=JNE42324UY4', 1, 'notifikasi', '2026-01-29 01:58:17'),
(90, 5, 30, NULL, 'Transaksi Berhasil', 'Transaksi dengan invoice INV-20260129-6064 berhasil diproses. Silakan upload bukti pembayaran.', 1, 'notifikasi', '2026-01-29 02:00:49'),
(91, 4, 30, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: The Giftedd. Invoice: INV-20260129-6064', 0, 'notifikasi', '2026-01-29 02:00:49'),
(92, 8, 30, NULL, 'Pesanan Baru', 'Anda memiliki pesanan baru untuk produk: My School President. Invoice: INV-20260129-6064', 0, 'notifikasi', '2026-01-29 02:00:49'),
(93, 4, 30, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260129-6064', 0, 'notifikasi', '2026-01-29 02:01:07'),
(94, 8, 30, NULL, 'Bukti Pembayaran Diupload', 'Pembeli telah mengupload bukti pembayaran untuk invoice INV-20260129-6064', 0, 'notifikasi', '2026-01-29 02:01:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_penjual` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `stok` int(11) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `modal` decimal(12,2) NOT NULL,
  `keuntungan` decimal(12,2) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_penjual`, `id_kategori`, `nama_produk`, `stok`, `harga_jual`, `modal`, `keuntungan`, `gambar`, `deskripsi`, `created_at`) VALUES
(18, 4, 1, ' Ibu, Aku Ngga Sekuat Itu ', 97, 65000.00, 2000.00, 3000.00, 'produk_69710e2024d01_1769016864.jpg', 'Helobagas', '2026-01-16 12:28:40'),
(19, 4, 1, ' Harga Sebuah Percaya ', 47, 55000.00, 2000.00, 3000.00, 'produk_69710df3ce904_1769016819.jpg', 'Tere Liye', '2026-01-16 13:38:41'),
(21, 4, 1, 'Enigma', 11, 100000.00, 80000.00, 20000.00, 'produk_1769016621_69710d2d5ec80.jpg', 'Parbdee Tawesuk', '2026-01-22 00:30:21'),
(22, 4, 3, 'Cinta Tak Ada Mati', 55, 30000.00, 20000.00, 10000.00, 'produk_1769018107_697112fbdf022.jpg', 'Eka Kurniawan', '2026-01-22 00:55:07'),
(23, 8, 1, 'My School President', 17, 80000.00, 50000.00, 30000.00, 'produk_1769515228_6978a8dc9541e.jpg', 'Pruesapha', '2026-01-27 19:00:28'),
(24, 4, 1, 'The Giftedd', 47, 70000.00, 60000.00, 10000.00, 'produk_1769651808_697abe60b8b49.jpg', 'SandOtnim', '2026-01-29 08:56:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `id_user` int(11) NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `total_bayar` decimal(15,2) NOT NULL,
  `metode_pembayaran` varchar(20) DEFAULT 'transfer',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp(),
  `approve` enum('approve','tidak') NOT NULL DEFAULT 'tidak',
  `no_resi` varchar(100) DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `invoice_number`, `id_user`, `total_harga`, `total_bayar`, `metode_pembayaran`, `bukti_pembayaran`, `status`, `tanggal_transaksi`, `approve`, `no_resi`, `kurir`) VALUES
(1, 'INV-20260121-4347', 5, 10000.00, 10000.00, 'transfer', 'payment_proof_1768977698_69707522ebc94.jpeg', 'ditolak', '2026-01-21 06:41:38', 'tidak', NULL, NULL),
(2, 'INV-20260121-0457', 5, 5000.00, 5000.00, 'transfer', 'payment_proof_1768987762_69709c72374d7.jpeg', 'selesai', '2026-01-21 09:29:22', 'approve', 'POS123456789ID', 'pos'),
(3, 'INV-20260121-2134', 5, 5000.00, 5000.00, 'transfer', 'payment_proof_1768991219_6970a9f325ef6.jpg', 'selesai', '2026-01-21 10:26:59', 'approve', 'JNE1234567890', 'jne'),
(4, 'INV-20260121-2990', 5, 5000.00, 5000.00, 'transfer', 'payment_proof_1768991252_6970aa149103b.jpg', 'ditolak', '2026-01-21 10:27:32', 'tidak', NULL, NULL),
(5, 'INV-20260121-3512', 5, 5000.00, 5000.00, 'transfer', 'payment_proof_1768997849_6970c3d993d33.png', 'selesai', '2026-01-21 12:17:29', 'approve', 'JT1234567890123', 'j&t'),
(6, 'INV-20260122-0159', 5, 100000.00, 100000.00, 'transfer', 'payment_proof_1769041249_69716d61b250a.png', 'selesai', '2026-01-22 00:20:49', 'approve', 'WHN123456789', 'wahana'),
(7, 'INV-20260122-2488', 5, 100000.00, 100000.00, 'transfer', 'payment_proof_1769081059_697208e32469e.jpg', 'processing', '2026-01-22 11:24:19', 'approve', NULL, NULL),
(8, 'INV-20260123-3336', 5, 30000.00, 30000.00, 'transfer', 'payment_proof_1769146723_6973096374468.jpg', 'pending', '2026-01-23 05:38:43', 'tidak', NULL, NULL),
(9, 'INV-20260124-7742', 5, 100000.00, 100000.00, 'transfer', 'payment_proof_1769252742_6974a786176db.jpeg', 'pending', '2026-01-24 11:05:42', 'tidak', NULL, NULL),
(10, 'INV-20260124-6306', 5, 30000.00, 30000.00, 'qris', 'payment_proof_1769256562_6974b67268aeb.png', 'pending', '2026-01-24 12:08:47', 'tidak', NULL, NULL),
(11, 'INV-20260124-9589', 5, 100000.00, 100000.00, 'qris', 'payment_proof_1769257086_6974b87eaec20.png', 'pending', '2026-01-24 12:17:57', 'tidak', NULL, NULL),
(12, 'INV-20260124-7738', 5, 100000.00, 100000.00, 'transfer', 'payment_proof_1769257744_6974bb102db91.png', 'pending', '2026-01-24 12:28:45', 'tidak', NULL, NULL),
(13, 'INV-20260124-7860', 5, 55000.00, 55000.00, 'qris', 'payment_proof_1769258746_6974befaad26b.jpeg', 'pending', '2026-01-24 12:45:34', 'tidak', NULL, NULL),
(14, 'INV-20260125-1993', 5, 30000.00, 30000.00, 'qris', 'payment_proof_1769323881_6975bd696ac15.png', 'pending', '2026-01-25 06:51:05', 'tidak', NULL, NULL),
(15, 'INV-20260125-3375', 5, 100000.00, 100000.00, 'qris', NULL, 'pending', '2026-01-25 06:59:28', 'tidak', NULL, NULL),
(16, 'INV-20260125-1415', 5, 100000.00, 100000.00, 'transfer', 'payment_proof_1769324765_6975c0dd00394.png', 'pending', '2026-01-25 07:05:11', 'tidak', NULL, NULL),
(17, 'INV-20260126-7105', 5, 55000.00, 55000.00, 'qris', 'payment_proof_1769413449_69771b49dbf7c.jpg', 'pending', '2026-01-26 07:34:14', 'tidak', NULL, NULL),
(18, 'INV-20260126-3361', 5, 65000.00, 65000.00, 'transfer', 'payment_proof_1769434266_69776c9a29fc5.jpg', 'pending', '2026-01-26 13:30:45', 'tidak', NULL, NULL),
(19, 'INV-20260126-9999', 5, 65000.00, 65000.00, 'transfer', 'payment_proof_1769435094_69776fd6b2aec.jpeg', 'pending', '2026-01-26 13:44:28', 'tidak', NULL, NULL),
(20, 'INV-20260127-2285', 5, 135000.00, 135000.00, 'transfer', 'payment_proof_1769516279_6978acf73fdad.jpeg', 'pending', '2026-01-27 12:01:04', 'tidak', NULL, NULL),
(21, 'INV-20260127-3754', 5, 110000.00, 110000.00, 'transfer', 'payment_proof_21_1769518302_6978b4de1e9bf.jpg', 'pending', '2026-01-27 12:30:48', 'tidak', NULL, NULL),
(22, 'INV-20260127-4798', 5, 180000.00, 180000.00, 'transfer', NULL, 'ditolak', '2026-01-27 13:01:12', 'tidak', NULL, NULL),
(23, 'INV-20260127-1772', 5, 145000.00, 145000.00, 'transfer', 'payment_proof_23_4_1769521510.png', 'pending', '2026-01-27 13:18:50', 'tidak', NULL, NULL),
(24, 'INV-20260127-9459', 5, 135000.00, 135000.00, 'transfer', NULL, 'ditolak', '2026-01-27 13:53:20', 'tidak', NULL, NULL),
(25, 'INV-20260127-7596', 5, 180000.00, 180000.00, 'transfer', 'payment_proof_25_8_1769522980_0.png', 'pending', '2026-01-27 14:09:20', 'tidak', NULL, NULL),
(26, 'INV-20260127-0164', 5, 30000.00, 30000.00, 'transfer', 'payment_proof_26_4_1769523077_0.jpg', 'pending', '2026-01-27 14:10:15', 'tidak', NULL, NULL),
(27, 'INV-20260128-9339', 5, 135000.00, 135000.00, 'transfer', 'payment_proof_27_8_1769606260_0.jpeg', 'pending', '2026-01-28 13:17:17', 'tidak', NULL, NULL),
(28, 'INV-20260128-2048', 5, 80000.00, 80000.00, 'transfer', 'payment_proof_28_8_1769607122_0.jpeg', 'pending', '2026-01-28 13:31:50', 'tidak', NULL, NULL),
(29, 'INV-20260128-2192', 5, 180000.00, 180000.00, 'transfer', 'payment_proof_29_8_1769607207_0.jpeg', 'dikirim', '2026-01-28 13:33:10', 'approve', 'JNE42324UY4', 'j&t'),
(30, 'INV-20260129-6064', 5, 150000.00, 150000.00, 'transfer', 'payment_proof_30_4_1769652067_0.jpeg', 'pending', '2026-01-29 02:00:49', 'tidak', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id_detail`, `id_transaksi`, `id_produk`, `nama_produk`, `qty`, `harga`, `subtotal`) VALUES
(1, 1, 18, 'Indomie', 1, 5000.00, 5000.00),
(2, 1, 19, 'mick', 1, 5000.00, 5000.00),
(3, 2, 19, 'mick', 1, 5000.00, 5000.00),
(4, 3, 18, 'Indomie', 1, 5000.00, 5000.00),
(5, 4, 18, 'Indomie', 1, 5000.00, 5000.00),
(6, 5, 18, 'Indomie', 1, 5000.00, 5000.00),
(7, 6, 21, 'Enigma', 1, 100000.00, 100000.00),
(8, 7, 21, 'Enigma', 1, 100000.00, 100000.00),
(9, 8, 22, 'Cinta Tak Ada Mati', 1, 30000.00, 30000.00),
(10, 9, 21, 'Enigma', 1, 100000.00, 100000.00),
(11, 10, 22, 'Cinta Tak Ada Mati', 1, 30000.00, 30000.00),
(12, 11, 21, 'Enigma', 1, 100000.00, 100000.00),
(13, 12, 21, 'Enigma', 1, 100000.00, 100000.00),
(14, 13, 19, ' Harga Sebuah Percaya ', 1, 55000.00, 55000.00),
(15, 14, 22, 'Cinta Tak Ada Mati', 1, 30000.00, 30000.00),
(16, 15, 21, 'Enigma', 1, 100000.00, 100000.00),
(17, 16, 21, 'Enigma', 1, 100000.00, 100000.00),
(18, 17, 19, ' Harga Sebuah Percaya ', 1, 55000.00, 55000.00),
(19, 18, 18, ' Ibu, Aku Ngga Sekuat Itu ', 1, 65000.00, 65000.00),
(20, 19, 18, ' Ibu, Aku Ngga Sekuat Itu ', 1, 65000.00, 65000.00),
(21, 20, 23, 'My School President', 1, 80000.00, 80000.00),
(22, 20, 19, ' Harga Sebuah Percaya ', 1, 55000.00, 55000.00),
(23, 21, 22, 'Cinta Tak Ada Mati', 1, 30000.00, 30000.00),
(24, 21, 23, 'My School President', 1, 80000.00, 80000.00),
(25, 22, 23, 'My School President', 1, 80000.00, 80000.00),
(26, 22, 21, 'Enigma', 1, 100000.00, 100000.00),
(27, 23, 23, 'My School President', 1, 80000.00, 80000.00),
(28, 23, 18, ' Ibu, Aku Ngga Sekuat Itu ', 1, 65000.00, 65000.00),
(29, 24, 23, 'My School President', 1, 80000.00, 80000.00),
(30, 24, 19, ' Harga Sebuah Percaya ', 1, 55000.00, 55000.00),
(31, 25, 23, 'My School President', 1, 80000.00, 80000.00),
(32, 25, 21, 'Enigma', 1, 100000.00, 100000.00),
(33, 26, 22, 'Cinta Tak Ada Mati', 1, 30000.00, 30000.00),
(34, 27, 23, 'My School President', 1, 80000.00, 80000.00),
(35, 27, 19, ' Harga Sebuah Percaya ', 1, 55000.00, 55000.00),
(36, 28, 23, 'My School President', 1, 80000.00, 80000.00),
(37, 29, 23, 'My School President', 1, 80000.00, 80000.00),
(38, 29, 21, 'Enigma', 1, 100000.00, 100000.00),
(39, 30, 24, 'The Giftedd', 1, 70000.00, 70000.00),
(40, 30, 23, 'My School President', 1, 80000.00, 80000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','penjual','pembeli') NOT NULL,
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif',
  `gambar` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `nama_bank` varchar(50) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_chat_time` datetime DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` datetime DEFAULT NULL,
  `device_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nik`, `username`, `password`, `email`, `role`, `status`, `gambar`, `alamat`, `nama_bank`, `no_rekening`, `last_login`, `remember_token`, `created_at`, `last_chat_time`, `is_online`, `last_seen`, `device_token`) VALUES
(1, '3175066104080002', 'aca', '$2y$10$jx.GsKcRv9NBmR8mjotxdOdRI1DUJhuMpb32QZ5ED5MUOo5gg9PaS', 'khansaafifah58@gmail.com', 'super_admin', 'aktif', 'profile_1_1769646067.jpeg', '', NULL, NULL, '2026-01-29 02:06:10', '', '2026-01-13 19:32:25', NULL, 0, NULL, NULL),
(4, '3175066301800010', 'Dian Widiyanti', '$2y$10$LSqMqmG8gh7.7EZhqMmPCeJIfpBk5zor1OH8qg5MLjFDRdu68Hw66', 'dianwidiyanti0506@gmail.com', 'penjual', 'aktif', 'profile_4_1769651982.jpeg', 'Jln. Kayu Tinggi, No.6', 'Seabank', '901913426279', '2026-01-29 02:07:11', '', '2026-01-13 20:52:15', NULL, 0, NULL, NULL),
(5, '3303152409870001', 'Kaisah Talita Rumi', '$2y$10$BXQpHkb691SH9Kl.Dzxr1us8Om98cDP7gG69lT/eO3AEm9JjfvNgK', 'ancakhnsa@gmail.com', 'pembeli', 'aktif', 'profile_5_1769652149.jpeg', 'Jl.Radjiman', NULL, NULL, '2026-01-29 10:57:44', '', '2026-01-14 16:37:54', NULL, 1, '2026-01-22 16:34:55', NULL),
(7, '3303152409898001', 'Mick Metas', '$2y$10$zwNqOUTZtMdHT.3aoh8Yb.LbZg7oBoNAmORfBVDyyv0XHjr86uosm', 'mtsmck6@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln. Bangkok', NULL, NULL, NULL, NULL, '2026-01-19 21:15:52', NULL, 0, NULL, NULL),
(8, '3175066622800010', 'dunk', '$2y$10$uXtEhSwN5oHeDdjcQCiOquB0Xux7S78n8bM4F64V99muVqdrX5x6C', 'dunk@gmail.com', 'penjual', 'aktif', 'profile_8_1768913753.jpeg', 'Jln. Thamasat', 'Seabank', '901787605282', '2026-01-27 11:51:54', '', '2026-01-20 19:48:26', NULL, 0, NULL, NULL),
(9, '3176254382083624', 'Wesley', '$2y$10$YPCvCMTjks90u0.n0I7NFuJ9ta6lCsVrF3woxJgosG8Gxmvua2/Dy', 'wesley@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln. Williamest', NULL, NULL, NULL, NULL, '2026-01-21 23:55:17', NULL, 0, NULL, NULL),
(10, '3175643852940359', 'Jaidee', '$2y$10$HXbgboqmMxnbhGPJZusM4uKprDtirfG4RRF4tn6U8oxjoYUWYTLXS', 'jaidee@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln. Joongdunk', NULL, NULL, NULL, NULL, '2026-01-22 00:37:12', NULL, 0, NULL, NULL),
(11, '3175066301888010', 'khansaafifah', '$2y$10$7vqRa.Q/EZWTmmw4mDytA.WtO1CSidrSiPuNkEq3G1MF302KjZmBe', 'dreamysand@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln. Penggilingan', NULL, NULL, NULL, NULL, '2026-01-26 09:26:05', NULL, 0, NULL, NULL),
(12, '3175066134080002', 'william', '$2y$10$aUz/qzNpf6PzPTwhkxsXF.2MmV14K9X1wvdJqsfdFEJmFFTN93k6u', 'william@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln.Williamest', NULL, NULL, NULL, NULL, '2026-01-26 09:34:57', NULL, 0, NULL, NULL),
(13, '3175065378141001', 'Namon', '$2y$10$qFjrR0pG1Z4CkzMiiclwhewDbmshcEu6tzG0c647kfeMgiaUD/L2S', 'nanon@gmail.com', 'penjual', 'aktif', 'profile_1769406573_6977006d20447.jpeg', 'Jln.Nanonchimon', '', '', NULL, NULL, '2026-01-26 09:36:40', NULL, 0, NULL, NULL),
(14, '3175066121980002', 'Mutia', '$2y$10$q6QucaPvIIsMhj4LLa22LOjk03S750UP32JtKWrIsnsf68umMYEpu', 'mutiaulia@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln. Radjiman', '', '', NULL, NULL, '2026-01-29 07:19:10', NULL, 0, NULL, NULL),
(15, '3175055104080002', 'joss', '$2y$10$lkNDoA6Ex9QBTeJCyi3kOurES6ICbBAbxgtFzhM69k9tAGCCx3nKi', 'joss@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln,Jossgwin', 'Seabank', '123456789', NULL, NULL, '2026-01-29 18:27:30', NULL, 0, NULL, NULL),
(16, '3175066104010002', 'gawin', '$2y$10$nllMKqljSiQOEPeQ41udV.dVb9Ms1W9mFEsFVABfaOxVoKo8S8K2u', 'gawin@gmail.com', 'pembeli', 'aktif', 'default_buyer.png', 'Jln,Jossgwin', NULL, NULL, '2026-01-29 11:28:15', '', '2026-01-29 18:28:04', NULL, 0, NULL, NULL),
(17, '3175066301800080', 'juju', '$2y$10$qTxH5JuKEbUdbTb.D4nJKOVwCMi9D5UEi09DgMTQ.X8QaeDqQvHey', 'juju@gmail.com', 'penjual', 'aktif', 'default_seller.png', 'Jln.guinzly', 'Seabank', '901913426178', '2026-01-29 11:29:41', '', '2026-01-29 18:29:32', NULL, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id_chat`),
  ADD KEY `idx_pengirim` (`id_pengirim`),
  ADD KEY `idx_penerima` (`id_penerima`),
  ADD KEY `idx_waktu` (`waktu_kirim`),
  ADD KEY `idx_produk` (`id_produk`),
  ADD KEY `idx_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `katagori`
--
ALTER TABLE `katagori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `unique_user_product_active` (`id_user`,`id_produk`,`status`),
  ADD UNIQUE KEY `unique_cart_item` (`id_user`,`id_produk`,`status`),
  ADD KEY `fk_keranjang_produk` (`id_produk`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_notifikasi_order` (`id_order`),
  ADD KEY `fk_notifikasi_produk` (`id_produk`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_katagori` (`id_kategori`),
  ADD KEY `fk_produk_users` (`id_penjual`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `chat`
--
ALTER TABLE `chat`
  MODIFY `id_chat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `katagori`
--
ALTER TABLE `katagori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_pengirim`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`id_penerima`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_users` FOREIGN KEY (`id_penjual`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `katagori` (`id_kategori`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
