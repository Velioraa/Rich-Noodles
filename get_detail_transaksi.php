<?php
include "koneksi.php";

$id_transaksi = $_GET['id'] ?? 0;

// Ambil data transaksi beserta member, kasir, dan metode pembayaran
$sqlTransaksi = "SELECT 
    t.id_transaksi,
    t.total_harga,
    t.total_bayar,
    (t.total_bayar - t.total_harga) AS total_kembalian,
    t.total_keuntungan,
    a.username AS nama_kasir,
    m.username AS nama_member,
    mp.nama_metode,
    t.tanggal_transaksi
FROM transaksi t
LEFT JOIN admin a ON t.fid_admin = a.id
LEFT JOIN member m ON t.fid_member = m.id
LEFT JOIN metode_pembayaran mp ON t.fid_metode_pembayaran = mp.id_metode_pembayaran
WHERE t.id_transaksi = ?";
$stmt = $conn->prepare($sqlTransaksi);
$stmt->bind_param("i", $id_transaksi);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

// Ambil detail transaksi
$sqlDetail = "SELECT 
    p.nama_produk,
    dt.total_produk AS jumlah,
    dt.subtotal
FROM detail_transaksi dt
LEFT JOIN produk p ON dt.fid_produk = p.id_produk
WHERE dt.fid_transaksi = ?";
$stmt = $conn->prepare($sqlDetail);
$stmt->bind_param("i", $id_transaksi);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Kirim JSON
echo json_encode([
    "success" => true,
    "transaksi" => $transaksi,
    "detail" => $detail
]);
?>
