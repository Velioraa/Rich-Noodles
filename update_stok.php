<?php
include "koneksi.php";  // Pastikan koneksi ke database sudah benar

// Ambil data yang dikirimkan dari client
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id_produk']) && isset($data['stok'])) {
    $idProduk = $data['id_produk'];
    $stok = $data['stok'];

    // Query untuk update stok produk
    $query = "UPDATE produk SET stok = ? WHERE id_produk = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $stok, $idProduk);

    if ($stmt->execute()) {
        // Kirim respons sukses jika stok berhasil diperbarui
        echo json_encode(['success' => true]);
    } else {
        // Kirim respons error jika gagal memperbarui stok
        echo json_encode(['success' => false, 'error' => 'Gagal memperbarui stok!']);
    }
} else {
    // Jika data tidak lengkap, kirim respons error
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap!']);
}

?>
