<?php
session_start();
include "../koneksi.php"; 

// Hitung total quantity di cart
$sqlCartCount = "SELECT SUM(quantity) as total FROM cart";
$resultCartCount = $conn->query($sqlCartCount);
$total_cart_items = 0;
if ($resultCartCount) {
    $rowCartCount = $resultCartCount->fetch_assoc();
    $total_cart_items = $rowCartCount['total'] ?? 0;
}

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];

    // Cari produk dari barcode
    $sql = "SELECT id_produk, stok, tanggal_expired FROM produk WHERE kode_barcode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_produk = $row['id_produk'];
        $stok = $row['stok'];
        $tanggal_expired = $row['tanggal_expired'];
        $today = date("Y-m-d");

        // Cek maksimal 10 item di cart
        if ($total_cart_items >= 10) {
            echo "<script>alert('Keranjang penuh! Maksimal 10 item.'); window.location.href='testing.php';</script>";
            exit();
        }

        // Cek stok cukup
        if ($stok <= 0) {
            echo "<script>alert('Stok produk habis!'); window.location.href='testing.php';</script>";
            exit();
        }

        // Cek expired
        if (!empty($tanggal_expired) && $tanggal_expired < $today) {
            echo "<script>alert('Produk sudah expired sejak $tanggal_expired!'); window.location.href='testing.php';</script>";
            exit();
        }

        // Cek apakah produk sudah ada di cart
        $sqlCart = "SELECT * FROM cart WHERE id_produk = ?";
        $stmtCart = $conn->prepare($sqlCart);
        $stmtCart->bind_param("i", $id_produk);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();

        if ($resultCart->num_rows > 0) {
            // Produk sudah ada → tambah quantity
            $sqlUpdate = "UPDATE cart SET quantity = quantity + 1 WHERE id_produk = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("i", $id_produk);
            $stmtUpdate->execute();
        } else {
            // Produk baru → insert ke cart
            $sqlInsert = "INSERT INTO cart (id_produk, quantity, waktu_masuk) VALUES (?, 1, NOW())";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("i", $id_produk);
            $stmtInsert->execute();
        }

        // Kurangi stok produk
        $sqlStok = "UPDATE produk SET stok = stok - 1 WHERE id_produk = ?";
        $stmtStok = $conn->prepare($sqlStok);
        $stmtStok->bind_param("i", $id_produk);
        $stmtStok->execute();

        // Redirect ke keranjang
        header("Location: ../keranjang.php");
        exit();
    } else {
        echo "<script>alert('Produk dengan barcode $barcode tidak ditemukan!'); window.location.href='testing.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rich Noodles - Scan Barcode</title>
    <link rel="website icon" type="image/png" href="../asset/Richa Mart.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-btn:hover {
            color: #333;
        }
        
        .back-btn svg {
            margin-right: 5px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        
        button {
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #3a7bc8;
        }
        
        .scanner-icon {
            text-align: center;
            margin-bottom: 20px;
            color: #4a90e2;
        }
        
        .scanner-icon svg {
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../produk.php" class="back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
        </a>
                
        <h2>Form Scan Barcode</h2>
        
        <form action="testing.php" method="get">
            <div class="form-group">
                <label for="barcode">Barcode</label>
                <input type="text" name="barcode" id="barcode" placeholder="Masukkan kode barcode" autofocus>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>