<?php
session_start();
include "../koneksi.php";

// Data dummy (simulasikan dari form atau keranjang belanja)
$total_harga           = 3500;
$fid_admin             = 29;
$total_bayar           = 10000;
$fid_metode_pembayaran = 2;
$total_kembalian       = 6500;
$total_keuntungan      = 500;
$fid_member            = 31;
$potongan              = 1000;

// Simulasi item dari keranjang
$items = [
    ["nama" => "Indomie Goreng", "qty" => 2, "harga" => 3000],
    ["nama" => "Mie Sedaap", "qty" => 1, "harga" => 3500]
];

// Simpan ke tabel transaksi
$query = "INSERT INTO transaksi (
    tanggal_transaksi, total_harga, fid_admin, total_bayar, 
    fid_metode_pembayaran, total_kembalian, total_keuntungan, fid_member
) VALUES (
    NOW(), '$total_harga', '$fid_admin', '$total_bayar', 
    '$fid_metode_pembayaran', '$total_kembalian', '$total_keuntungan', '$fid_member'
)";
$result = mysqli_query($conn, $query);

// Cek hasil insert
if ($result) {
    // Ambil data admin
    $sql = "SELECT * FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $fid_admin);
    $stmt->execute();
    $res = $stmt->get_result();
    $account = $res->fetch_assoc();

    // Simpan struk ke session
    $_SESSION['struk'] = [
        "tanggal"  => date("Y-m-d H:i:s"),
        "kasir"    => $account['nama'], // pastikan ada kolom `nama`
        "items"    => $items,
        "potongan" => $potongan,
        "total"    => $total_harga,
    ];

    // Arahkan ke invoice
    header("Location: invoice.php");
    exit;
} else {
    echo "Gagal menyimpan transaksi: " . mysqli_error($conn);
}
