<?php
session_start();
include "koneksi.php";
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Pastikan data struk ada
if (!isset($_SESSION['struk']) || empty($_SESSION['struk'])) {
    echo "Data struk tidak ditemukan!";
    exit;
}

$data = $_SESSION['struk'];

// Ambil data utama
$tanggal = isset($data['tanggal']) ? $data['tanggal'] : date("d-m-Y H:i:s");
$kasir = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kasir';
$items = isset($data['items']) ? $data['items'] : [];

// Hitung total dan diskon
$subtotal = 0;
foreach ($items as $item) {
    $hargaSatuan = (float)$item['harga_jual'];
    $qty = (int)$item['quantity'];
    $totalItem = $hargaSatuan * $qty;
    $subtotal += $totalItem;
}

$diskonPoin = isset($data['point_dipakai']) ? ((int)$data['point_dipakai'] * 1000) : 0;
$grandTotal = $subtotal - $diskonPoin;
$hargaBayar = isset($data['total_bayar']) ? (int)$data['total_bayar'] : 0;
$kembalian  = isset($data['total_kembalian']) ? (int)$data['total_kembalian'] : 0;

// Konfigurasi Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Bangun HTML untuk PDF
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 10px; }
        .info { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .total { text-align: right; font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h3>RICH NOODLES</h3>
        <p>Jl. Dr. KRT Radjiman Widyodiningrat, Jatinegara, Jakarta</p>
    </div>
    <div class="info">
        <p>Tanggal: ' . htmlspecialchars($tanggal) . '<br>
        Kasir: ' . htmlspecialchars($kasir) . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Qty</th>
                <th>Item</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';

foreach ($items as $item) {
    $hargaSatuan = (float)$item['harga_jual'];
    $qty = (int)$item['quantity'];
    $totalItem = $hargaSatuan * $qty;

    $html .= '
        <tr>
            <td>' . htmlspecialchars($qty) . '</td>
            <td>' . htmlspecialchars($item['nama_produk']) . '</td>
            <td class="right">Rp ' . number_format($hargaSatuan, 0, ',', '.') . '</td>
            <td class="right">Rp ' . number_format($totalItem, 0, ',', '.') . '</td>
        </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="total">Subtotal: Rp ' . number_format($subtotal, 0, ',', '.') . '</div>
    <div class="total">Potongan Koin: Rp ' . number_format($diskonPoin, 0, ',', '.') . '</div>
    <div class="total">TOTAL: Rp ' . number_format($grandTotal, 0, ',', '.') . '</div>
    <div class="total">Harga Bayar: Rp ' . number_format($hargaBayar, 0, ',', '.') . '</div>
    <div class="total">Kembalian: Rp ' . number_format($kembalian, 0, ',', '.') . '</div>

    <div class="footer">
        Terima kasih atas kunjungannya!<br>
        Barang yang sudah dibeli tidak dapat dikembalikan.<br>
        Selamat datang kembali!
    </div>
</body>
</html>';

// Render ke PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$pdfOutput = $dompdf->output();

// Simpan PDF sementara
$tempFilePath = 'temp/' . uniqid() . '.pdf';
file_put_contents($tempFilePath, $pdfOutput);

// Kirim ke WhatsApp lewat Ultramsg
$token = 'wiq10sk3b9i4p4w9';
$instanceId = 'instance111688';
$phone = '6285655754527'; // ganti dengan no tujuan

$url = "https://api.ultramsg.com/$instanceId/messages/document";
$postData = [
    'token'    => $token,
    'to'       => $phone,
    'filename' => 'Rich Noodles.pdf',
    'document' => 'https://86257141a084.ngrok-free.app/aca/kasir/' . $tempFilePath,
    'caption'  => "Hallo kami dari *Rich Noodles* ingin memberikan struk belanja Anda.\n\nTerima kasih sudah berbelanja di Rich Noodles 😊"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
$response = curl_exec($ch);
curl_close($ch);

// Debug respons (boleh dihapus setelah testing)
echo "<pre>$response</pre>";

// Kembali ke halaman produk
header("Location: produk.php");
exit;
?>
