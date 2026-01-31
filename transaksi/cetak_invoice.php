<?php
session_start();
include "../koneksi.php";
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil data dari session yang benar
if (!isset($_SESSION['struk']) || empty($_SESSION['struk'])) {
    die("Data struk tidak ditemukan.");
}
$data = $_SESSION['struk'];

// Info kasir & tanggal
$kasir = $_SESSION['username'] ?? '-';
$tanggal = date('Y-m-d H:i:s');

// Konfigurasi dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Path gambar logo
$logoPath = realpath(__DIR__ . 'asset/Richa Mart.png');

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; }
        .header img { height: 50px; margin-bottom: 5px; }
        .info { margin-top: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 5px; border: 1px solid #ddd; text-align: left; }
        .total, .footer { margin-top: 10px; font-weight: bold; text-align: right; }
        .footer { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $logoPath . '">
        <p><strong>RICH NOODLES</strong><br>Jl. Dr. KRT Radjiman Widyodiningrat, Jatinegara, Jakarta</p>
    </div>
    <div class="info">
        <p>Tanggal: ' . $tanggal . '<br>Kasir: ' . $kasir . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';

$subtotal = 0;
foreach ($data['items'] as $item) {
    $harga = (float)$item['harga_jual'];
    $qty = (int)$item['quantity'];
    $total = $harga * $qty;
    $subtotal += $total;

    $html .= '<tr>
        <td>' . htmlspecialchars($item['nama_produk']) . '</td>
        <td>' . $qty . '</td>
        <td>Rp ' . number_format($harga, 0, ',', '.') . '</td>
        <td>Rp ' . number_format($total, 0, ',', '.') . '</td>
    </tr>';
}

$diskon = isset($data['point_dipakai']) ? $data['point_dipakai'] * 1000 : 0;
$totalBayar = $data['total_bayar'] ?? $subtotal;
$kembalian = $data['total_kembalian'] ?? 0;

$html .= '
        </tbody>
    </table>
    <div class="total">Potongan Koin: Rp ' . number_format($diskon, 0, ',', '.') . '</div>
    <div class="total">Total Bayar: Rp ' . number_format($totalBayar, 0, ',', '.') . '</div>
    <div class="total">Kembalian: Rp ' . number_format($kembalian, 0, ',', '.') . '</div>
    <div class="footer">Terima kasih atas kunjungannya!</div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("invoice_pembelian.pdf", ["Attachment" => true]);
?>
