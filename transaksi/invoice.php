<?php
session_start();
include '../koneksi.php';

// Pastikan data invoice ada
if (!isset($_SESSION['struk']) || empty($_SESSION['struk'])) {
    echo "<script>alert('Data invoice tidak ditemukan!'); window.location.href='../produk.php';</script>";
    exit;
}

// Ambil data invoice
$data = $_SESSION['struk'];

// Tanggal transaksi (ambil dari data invoice atau default hari ini)
$tanggal = isset($data['tanggal']) ? $data['tanggal'] : date("d-m-Y H:i:s");

// Kasir (ambil dari username session admin)
$kasir = isset($_SESSION['username']) ? $_SESSION['username'] : "Kasir";

// Jika perlu mengambil dari database, gunakan query berikut:
// $query_kasir = mysqli_query($koneksi, "SELECT username FROM admin WHERE id = '".$_SESSION['id']."'");
// $kasir_data = mysqli_fetch_assoc($query_kasir);
// $kasir = $kasir_data['username'] ?? "Kasir";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rich Noodles - Struk Transaksi</title>
    <link rel="website icon" type="image/png" href="../asset/Richa Mart.png">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Courier New', monospace;
        }
        
        body {
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt-container {
            background: white;
            padding: 25px;
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #5ca6ff;
            position: relative;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #e0e0e0;
        }

        .logo img {
            height: 70px;
            margin-bottom: 10px;
        }

        .store-name {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }

        .store-address {
            font-size: 14px;
            margin-bottom: 5px;
            line-height: 1.4;
            color: #666;
        }

        .store-contact {
            font-size: 13px;
            color: #888;
        }

        .receipt-info {
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }

        .transaction-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .transaction-items th {
            border-bottom: 2px solid #e0e0e0;
            padding: 10px 5px;
            text-align: left;
            background: #f0f7ff;
        }

        .transaction-items td {
            padding: 8px 5px;
            vertical-align: top;
            border-bottom: 1px dashed #f0f0f0;
        }

        .item-name {
            width: 50%;
        }

        .item-qty {
            width: 15%;
            text-align: center;
        }

        .item-price {
            width: 35%;
            text-align: right;
        }

        .divider {
            border-top: 2px dashed #e0e0e0;
            margin: 15px 0;
        }

        .total-section {
            font-size: 16px;
            margin-top: 15px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #e0e0e0;
            padding-top: 10px;
            margin-top: 10px;
            color: #2c3e50;
        }

        .footer-message {
            text-align: center;
            font-size: 13px;
            margin-top: 20px;
            line-height: 1.5;
            padding-top: 15px;
            border-top: 2px dashed #e0e0e0;
            color: #666;
        }

        .buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .buttons button {
            padding: 12px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
            min-width: 100px;
        }

        .buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .send-struk { 
            background: #4CAF50; 
            color: white; 
        }
        
        .send-struk:hover {
            background: #45a049;
        }
        
        .print-struk { 
            background: #2196F3; 
            color: white; 
        }
        
        .print-struk:hover {
            background: #0b7dda;
        }
        
        .print-btn { 
            background: #FF9800; 
            color: white; 
        }
        
        .print-btn:hover {
            background: #e68900;
        }

        .receipt-number {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 12px;
            color: #888;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                border-top: 4px solid #5ca6ff;
                max-width: 100%;
            }
            
            .buttons {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .receipt-container {
                padding: 15px;
                max-width: 350px;
            }
            
            .buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .buttons button {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-number">No: <?= date('YmdHis') ?></div>
        
        <div class="receipt-header">
            <div class="logo">
                <img src="../asset/Richa Mart.png" alt="Rich Noodles">
            </div>
            <div class="store-name">RICH NOODLES</div>
            <div class="store-address">
                Jl. Dr. KRT Radjiman Widyodiningrat<br>
                Jatinegara, Jakarta
            </div>
            <div class="store-contact">Telp: (021) 1234567</div>
        </div>
        
        <div class="receipt-info">
            <div><strong>Tanggal:</strong> <?= htmlspecialchars($tanggal) ?></div>
            <div><strong>Kasir:</strong> <?= htmlspecialchars($kasir) ?></div>
            <div><strong>No. Transaksi:</strong> TRX-<?= date('YmdHis') ?></div>
        </div>
        
    <table class="transaction-items">
    <tr>
        <th class="item-qty">Qty</th>
        <th class="item-name">Item</th>
        <th class="item-price">Harga</th>
        <th class="item-price">Subtotal</th>
    </tr>
<?php 
$subtotal = 0;
foreach ($data['items'] as $item): 
    $hargaSatuan = (float)$item['harga_jual'];
    $qty = (int)$item['quantity'];
    $totalItem = $hargaSatuan * $qty;
    $subtotal += $totalItem;
?>
<tr>
    <td><?= $qty ?></td>
    <td><?= htmlspecialchars($item['nama_produk']) ?></td>
    <td>Rp <?= number_format($hargaSatuan, 0, ',', '.') ?></td>
    <td>Rp <?= number_format($totalItem, 0, ',', '.') ?></td>
</tr>
<?php endforeach; ?>
</table>

<div class="divider"></div>

<div class="total-section">
    <?php
// Hitung diskon dari poin
$diskonPoin = isset($data['point_dipakai']) ? ((int)$data['point_dipakai'] * 1000) : 0;
$grandTotal = $subtotal - $diskonPoin;

// Ambil langsung dari session
$hargaBayar = isset($data['total_bayar']) ? (int)$data['total_bayar'] : 0;
$kembalian  = isset($data['total_kembalian']) ? (int)$data['total_kembalian'] : 0;
    ?>
    <div class="total-line">
        <span>Subtotal:</span>
        <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
    </div>
    <div class="total-line">
        <span>Potongan Koin:</span>
        <span>Rp <?= number_format($diskonPoin, 0, ',', '.') ?></span>
    </div>
    <div class="total-line grand-total">
        <span>TOTAL:</span>
        <span>Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
    </div>
    <div class="total-line">
        <span>Harga Bayar:</span>
        <span>Rp <?= number_format($hargaBayar, 0, ',', '.') ?></span>
    </div>
    <div class="total-line">
        <span>Kembalian:</span>
        <span>Rp <?= number_format($kembalian, 0, ',', '.') ?></span>
    </div>
</div>
        
        <div class="footer-message">
            Terima kasih atas kunjungan Anda<br>
            Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan<br>
            Selamat datang kembali
        </div>
        
        <div class="buttons">
            <a href="../wa.php"><button class="send-struk">Send WA</button></a>
            <button onclick="downloadAndRedirect()" class="print-struk">Print PDF</button>
            <button onclick="window.print()" class="print-btn">Print</button>
        </div>
    </div>
    
    <script>
        function downloadAndRedirect() {
            window.open('cetak_invoice.php', '_blank');
            setTimeout(() => {
                window.location.href = '../produk.php';
            }, 2000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const receiptNumber = document.querySelector('.receipt-number');
            const now = new Date();
            const transactionId = 'TRX-' + now.getFullYear() + 
                                 String(now.getMonth()+1).padStart(2, '0') + 
                                 String(now.getDate()).padStart(2, '0') + 
                                 String(now.getHours()).padStart(2, '0') + 
                                 String(now.getMinutes()).padStart(2, '0') + 
                                 String(now.getSeconds()).padStart(2, '0');
            
            receiptNumber.textContent = 'No: ' + transactionId;
            
            const transactionInfo = document.querySelector('.receipt-info div:last-child');
            if (transactionInfo) {
                transactionInfo.innerHTML = '<strong>No. Transaksi:</strong> ' + transactionId;
            }
        });
    </script>
</body>
</html>