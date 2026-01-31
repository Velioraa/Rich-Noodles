<?php
session_start();

if (isset($_GET['kode'])) {
    $kode = $_GET['kode'];

    header("Content-Type: image/png");

    // Buat gambar kosong dengan ukuran tertentu
    $width = 200;
    $height = 80;
    $image = imagecreate($width, $height);

    // Warna latar belakang putih
    $bg = imagecolorallocate($image, 255, 255, 255);
    // Warna teks hitam
    $text_color = imagecolorallocate($image, 0, 0, 0);

    // Tulis teks kode barcode
    imagestring($image, 5, 50, 30, $kode, $text_color);

    // Kirim gambar ke browser
    imagepng($image);
    imagedestroy($image);
}
?>
