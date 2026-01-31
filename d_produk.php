<?php
include 'koneksi.php'; // sambungkan ke database

// --- Ambil data produk berdasarkan ID dari URL ---
if (isset($_GET['id'])) {
    $id_produk = $_GET['id'];

    $query = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $query->bind_param("i", $id_produk);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $produk = $result->fetch_assoc();
    } else {
        echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('ID produk tidak diberikan!'); window.location='produk.php';</script>";
    exit;
}

// --- Generator Barcode ---
require_once 'vendor/autoload.php';
use Picqer\Barcode\BarcodeGeneratorPNG;
$generator = new BarcodeGeneratorPNG();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?= htmlspecialchars($produk['nama_produk']) ?></title>
    <link rel="website icon" type="image/png" href="asset/Richa Mart.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e6f2ff 0%, #f0f8ff 100%);
            color: #333; display: flex; flex-direction: column; min-height: 100vh;
        }
        .header {
            display: flex; align-items: center; justify-content: center;
            background: white; padding: 15px 20px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1); position: relative;
        }
        .logo img { height: 50px; }
        .back-btn {
            position: absolute; left: 20px; background: #5ca6ff; color: white;
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; font-size: 20px; box-shadow: 0 4px 12px rgba(92, 166, 255, 0.3);
            transition: all 0.3s ease; text-decoration: none;
        }
        .back-btn:hover {
            background: #4a8feb; transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(92, 166, 255, 0.4);
        }
        .main-content { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        .detail-card {
            background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden; max-width: 900px; width: 100%; display: flex; flex-direction: column;
        }
        .card-header {
            background: linear-gradient(135deg, #6D9DC5 0%, #5ca6ff 100%);
            color: white; padding: 25px; text-align: center;
        }
        .card-header h1 { font-size: 28px; font-weight: 700; margin-bottom: 5px; }
        .card-header .product-id { font-size: 14px; opacity: 0.9; }
        .card-body { display: flex; flex-wrap: wrap; padding: 0; }
        .product-image {
            flex: 1; min-width: 300px; padding: 30px; display: flex;
            justify-content: center; align-items: center; background: #f8fbff;
        }
        .product-image img {
            width: 100%; max-width: 350px; height: auto;
            border-radius: 15px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        .product-image img:hover { transform: scale(1.02); }
        .product-info {
            flex: 1; min-width: 300px; padding: 30px; border-left: 1px solid #e6f2ff;
        }
        .info-section { margin-bottom: 25px; }
        .info-section h3 {
            color: #2c3e50; font-size: 18px; margin-bottom: 15px;
            display: flex; align-items: center; gap: 10px;
        }
        .info-section h3 i { color: #5ca6ff; }
        .price-tag {
            background: linear-gradient(135deg, #27ae60, #2ecc71); color: white;
            padding: 12px 20px; border-radius: 10px; font-size: 24px;
            font-weight: 700; display: inline-block;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        .stock-info { display: flex; gap: 20px; flex-wrap: wrap; }
        .stock-item {
            background: #e6f2ff; padding: 12px 20px; border-radius: 10px;
            display: flex; align-items: center; gap: 10px; min-width: 150px;
        }
        .stock-item i { color: #5ca6ff; font-size: 18px; }
        .stock-item .label { font-size: 12px; color: #7f8c8d; }
        .stock-item .value { font-size: 16px; font-weight: 600; color: #2c3e50; }
        .description {
            background: #f8fbff; padding: 20px; border-radius: 10px;
            border-left: 4px solid #5ca6ff;
        }
        .description p { line-height: 1.6; color: #555; }
        .barcode-section {
            background: #f8fbff; padding: 25px; border-radius: 15px;
            text-align: center; margin-top: 20px; border: 2px dashed #d0e5ff;
        }
        .barcode-section h3 {
            color: #2c3e50; margin-bottom: 15px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .barcode-image {
            max-width: 300px; margin: 0 auto 15px; padding: 15px; background: white;
            border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .barcode-image img { width: 100%; height: auto; }
        .barcode-number {
            font-family: 'Courier New', monospace; font-size: 18px;
            font-weight: 700; color: #2c3e50; background: white;
            padding: 10px 20px; border-radius: 8px; display: inline-block;
            border: 2px solid #e6f2ff;
        }
        @media (max-width: 768px) {
            .card-body { flex-direction: column; }
            .product-info { border-left: none; border-top: 1px solid #e6f2ff; }
            .product-image { padding: 20px; }
            .stock-info { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>

<div class="header">
    <a href="produk.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
    <div class="logo">
        <img src="asset/Richa Mart.png" alt="Rich Noodles">
    </div>
</div>

<div class="main-content">
    <div class="detail-card">
        <div class="card-header">
            <h1><?= htmlspecialchars($produk['nama_produk']) ?></h1>
            <div class="product-id">ID Produk: #<?= htmlspecialchars($produk['id_produk']) ?></div>
        </div>
        
        <div class="card-body">
            <div class="product-image">
                <img src="asset/<?= htmlspecialchars($produk['gambar']) ?>"
                     alt="<?= htmlspecialchars($produk['nama_produk']) ?>"
                     onerror="this.src='asset/default-product.png'">
            </div>
            
            <div class="product-info">
                <div class="info-section">
                    <h3><i class="fas fa-tag"></i> Harga Produk</h3>
                    <div class="price-tag">
                        Rp <?= number_format($produk['harga_jual'], 0, ',', '.') ?> / pcs
                    </div>
                </div>
                
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Informasi Stok</h3>
                    <div class="stock-info">
                        <div class="stock-item">
                            <i class="fas fa-boxes"></i>
                            <div>
                                <div class="label">Stok Tersedia</div>
                                <div class="value"><?= htmlspecialchars($produk['stok']) ?> unit</div>
                            </div>
                        </div>
                        <div class="stock-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <div class="label">Tanggal Expired</div>
                                <div class="value"><?= date('d F Y', strtotime($produk['tanggal_expired'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3><i class="fas fa-file-alt"></i> Deskripsi Produk</h3>
                    <div class="description">
                        <p><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
                    </div>
                </div>
                
                <div class="barcode-section">
                    <h3><i class="fas fa-barcode"></i> Kode Barcode</h3>
                    <div class="barcode-image">
                        <img src="data:image/png;base64,<?= base64_encode($generator->getBarcode($produk['kode_barcode'], $generator::TYPE_CODE_128)) ?>" alt="Barcode">
                    </div>
                    <div class="barcode-number"><?= htmlspecialchars($produk['kode_barcode']) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
