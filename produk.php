<?php
session_start();
include "koneksi.php";

// Cek login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Ambil data akun berdasarkan email
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();
$stmt->close();

// Ambil data kategori untuk dropdown
$sqlKategori = "SELECT * FROM kategori";
$resultKategori = $conn->query($sqlKategori);

// Cek apakah ada filter kategori
$filterKategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;

// Ambil produk sesuai filter kategori
if ($filterKategori > 0) {
    $sqlProduk = "SELECT * FROM produk WHERE fid_kategori = ?";    
    $stmt = $conn->prepare($sqlProduk);
    $stmt->bind_param("i", $filterKategori);
    $stmt->execute();
    $resultProduk = $stmt->get_result();
    $stmt->close();
} else {
    $sqlProduk = "SELECT * FROM produk";
    $resultProduk = $conn->query($sqlProduk);
}

// Hitung jumlah total quantity dari semua produk di dalam cart
$sqlCartCount = "SELECT SUM(quantity) as total FROM cart";
$resultCartCount = $conn->query($sqlCartCount);
$total_cart_items = 0;
if ($resultCartCount) {
    $rowCartCount = $resultCartCount->fetch_assoc();
    $total_cart_items = $rowCartCount['total'] ?? 0;
}

// ==========================
// Tambah produk lewat icon carts (?tambah_keranjang=id)
// ==========================
if (isset($_GET['tambah_keranjang'])) {
    $id_produk = intval($_GET['tambah_keranjang']);
    $qty = 1; // default nambah 1

    // Cek maksimal item di keranjang
    if ($total_cart_items >= 10) {
        echo "<script>alert('Keranjang penuh! Maksimal 10 item.'); window.location.href='produk.php?kategori=" . $filterKategori . "';</script>";
        exit();
    }

    // Cek stok
    $sqlStok = "SELECT stok FROM produk WHERE id_produk = ?";
    $stmt = $conn->prepare($sqlStok);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $res = $stmt->get_result();
    $stokData = $res->fetch_assoc();
    $stmt->close();

    if (!$stokData || $stokData['stok'] <= 0) {
        echo "<script>alert('Stok produk tidak mencukupi!'); window.location.href='produk.php?kategori=" . $filterKategori . "';</script>";
        exit();
    }

    // Cek apakah produk sudah ada di cart
    $sqlCheckCart = "SELECT * FROM cart WHERE id_produk = ?";
    $stmt = $conn->prepare($sqlCheckCart);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity
        $stmt->close();
        $sqlUpdateCart = "UPDATE cart SET quantity = quantity + 1 WHERE id_produk = ?";
        $stmt = $conn->prepare($sqlUpdateCart);
        $stmt->bind_param("i", $id_produk);
        $stmt->execute();
    } else {
        // Insert baru
        $stmt->close();
        $sqlInsertCart = "INSERT INTO cart (id_produk, quantity, waktu_masuk) VALUES (?, 1, NOW())";
        $stmt = $conn->prepare($sqlInsertCart);
        $stmt->bind_param("i", $id_produk);
        $stmt->execute();
    }
    $stmt->close();

    // Update stok produk
    $sqlUpdateStok = "UPDATE produk SET stok = stok - 1 WHERE id_produk = ?";
    $stmt = $conn->prepare($sqlUpdateStok);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $stmt->close();

    // Redirect ke keranjang
    header("Location: keranjang.php");
    exit();
}

// ==========================
// Hapus produk dari database
// ==========================
if (isset($_GET['hapus'])) {
    $id_produk = intval($_GET['hapus']);

    $sqlDeleteCart = "DELETE FROM cart WHERE id_produk = ?";
    $stmt = $conn->prepare($sqlDeleteCart);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $stmt->close();

    $sqlDeleteProduk = "DELETE FROM produk WHERE id_produk = ?";
    $stmt = $conn->prepare($sqlDeleteProduk);
    $stmt->bind_param("i", $id_produk);

    if ($stmt->execute()) {
        echo "<script>alert('Produk berhasil dihapus!'); window.location.href='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus produk: " . $stmt->error . "'); window.location.href='produk.php';</script>";
    }
    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Rich Noodles</title>
    <link rel="website icon" type="image/png" href="asset/Richa Mart.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6D9DC5;
            --primary-light: #A7C6D9;
            --primary-dark: #4A7BA6;
            --secondary: #2D3047;
            --light: #F8F9FA;
            --dark: #212529;
            --gray: #6C757D;
            --success: #28A745;
            --danger: #DC3545;
            --warning: #FFC107;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%);
            color: var(--dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 15px 5%;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo img {
            height: 50px;
            transition: var(--transition);
        }
        
        .logo:hover img {
            transform: scale(1.05);
        }
        
        .nav-menu {
            display: flex;
            gap: 25px;
            margin-left: 40px;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .nav-menu a:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .nav-menu a.active {
            background: var(--primary);
            color: white;
        }
        
        .icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .icon-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .icon-btn:hover {
            background: var(--primary-light);
        }
        
        .icons img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        
        .profile-dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 10px 0;
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 101;
        }
        
        .dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
        }
        
        .dropdown-menu a:hover {
            background: var(--primary-light);
        }
        
        .dropdown-menu a.logout {
            color: var(--danger);
        }
        
        .dropdown-menu a.logout:hover {
            background: var(--danger);
            color: white;
        }
        
        .expired-badge.warning {
            background: var(--warning);
            color: var(--dark);
        }


        .dropdown-menu img {
            width: 18px;
            height: 18px;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px 5%;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-dark);
        }

        .page-title h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .page-title p {
            color: var(--gray);
            font-size: 1rem;
        }

        .controls-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .filter-kategori select {
            padding: 10px 15px;
            border: 2px solid var(--primary);
            border-radius: 8px;
            background: white;
            color: var(--primary-dark);
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            min-width: 200px;
        }

        .filter-kategori select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(109, 157, 197, 0.2);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 14px;
        }

        .scan-btn {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .scan-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .add-btn {
            background: var(--primary);
            color: white;
        }

        .add-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary-light);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .product-price {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .product-stock {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 15px 0;
        }

        .stock-control {
            background: var(--primary-light);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .stock-control:hover {
            background: var(--primary);
        }

        .stock-control img {
            width: 16px;
            height: 16px;
        }

        .stock-count {
            font-weight: 600;
            font-size: 1.1rem;
            min-width: 30px;
            text-align: center;
        }

        .product-expiry {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 15px;
        }

        .expired-badge {
            background: var(--danger);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: inline-block;
        }

        .product-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            text-decoration: none;
        }

        .update-icon {
            background: var(--primary-light);
        }

        .update-icon:hover {
            background: var(--primary);
        }

        .cart-icon {
            background: var(--success);
        }

        .cart-icon:hover {
            background: #218838;
        }

        .delete-icon {
            background: var(--danger);
        }

        .delete-icon:hover {
            background: #c82333;
        }

        .action-icon img {
            width: 18px;
            height: 18px;
        }

        .footer {
            padding: 25px 5%;
            background: var(--secondary);
            color: white;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .nav-menu {
                gap: 15px;
                margin-left: 20px;
            }
            
            .nav-menu a {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 12px 4%;
                flex-wrap: wrap;
            }
            
            .nav-menu {
                order: 3;
                width: 100%;
                justify-content: center;
                margin: 15px 0 0 0;
                gap: 10px;
            }
            
            .logo-text {
                font-size: 1.3rem;
            }
            
            .controls-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-section {
                justify-content: center;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .logo-text {
                display: none;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-kategori select {
                min-width: 150px;
            }
            
            .nav-menu {
                flex-wrap: wrap;
            }
            
            .nav-menu a {
                font-size: 0.8rem;
                padding: 5px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="asset/Richa Mart.png" alt="Rich Noodles">
        </div>
        
        <nav class="nav-menu">
            <a href="admin.php">ADMIN</a>
            <a href="member.php">MEMBER</a>
            <a href="kategori.php">KATEGORI</a>
            <a href="produk.php" class="active">PRODUK</a>
            <a href="laporan.php">LAPORAN</a>
        </nav>
        
        <div class="icons">
            <a href="keranjang.php" class="icon-btn" style="position: relative;">
                <img src="asset/keranjang.png" alt="Cart">
                <?php if ($total_cart_items > 0): ?>
                    <span class="cart-badge"><?= $total_cart_items ?></span>
                <?php endif; ?>
            </a>
            
            <div class="profile-dropdown">
                <button class="icon-btn" id="profile-btn">
                    <img id="profile-pic" src="<?= $account['gambar']; ?>" alt="User">
                </button>
                <div class="dropdown-menu" id="dropdown-menu">
                    <a href="profile.php">
                    <img id="profile-pic" src="<?= $account['gambar']; ?>" alt="User">
                        Profile
                    </a>
                    <a href="register.php">Register</a>
                    <a href="logout.php" class="logout">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-title">
            <h1>Daftar Produk</h1>
            <p>Kelola produk mie instan di toko Anda</p>
        </div>

        <div class="controls-container">
            <div class="filter-section">
                <div class="filter-kategori">
                    <form method="get" action="produk.php">
                        <select name="kategori" onchange="this.form.submit()">
                            <option value="0">-- Semua Kategori --</option>
                            <?php while ($row = $resultKategori->fetch_assoc()) { ?>
                                <option value="<?= $row['id_kategori'] ?>" <?= $filterKategori == $row['id_kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['kategori']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="Barcode/testing.php" class="action-btn scan-btn">
                    <img src="asset/scan.png" alt="Scan" style="width: 20px; height: 20px;">
                    Scan Barcode
                </a>
                <a href="create/c_produk.php" class="action-btn add-btn">
                    <img src="asset/plus.png" alt="Tambah" style="width: 18px; height: 18px;">
                    Tambah Produk
                </a>
            </div>
        </div>

        <div class="product-grid">
            <?php while ($row = $resultProduk->fetch_assoc()) { ?>
                <?php 
                    $expired = strtotime($row['tanggal_expired']) < time();
                    $stok_asli = (int)$row['stok'];
                    $stok = $expired ? 0 : $stok_asli; // kalau expired, stok otomatis jadi 0
                ?>
                <div class="product-card">
                    <a href="d_produk.php?id=<?= urlencode($row['id_produk']) ?>">
                        <img src="asset/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="product-image">
                    </a>
                    
                    <div class="product-name"><?= htmlspecialchars($row['nama_produk']) ?></div>
                    <div class="product-price">Rp<?= number_format($row['harga_jual'], 0, ',', '.') ?></div>

                    <?php if ($expired): ?>
                        <div class="expired-badge">PRODUK EXPIRED!</div>
                    <?php elseif ($stok <= 10): ?>
                        <div class="expired-badge" style="background: var(--warning); color: var(--dark);">
                            ⚠️ Stok Menipis
                        </div>
                    <?php endif; ?>

                    <div class="product-stock">
                        <button class="stock-control" onclick="changeStok('<?= $row['id_produk'] ?>', -1)">
                            <img src="asset/minus.png" alt="Minus">
                        </button>
                        <span class="stock-count" id="stok-<?= $row['id_produk'] ?>">
                            <?= htmlspecialchars($stok) ?>
                        </span>
                        <button class="stock-control" onclick="changeStok('<?= $row['id_produk'] ?>', 1)">
                            <img src="asset/plus.png" alt="Plus">
                        </button>
                    </div>

                    <div class="product-expiry">Exp: <?= date('d-m-Y', strtotime($row['tanggal_expired'])) ?></div>

                    <div class="product-actions">
                        <?php if ($expired): ?>
                            <!-- PRODUK EXPIRED — stok dianggap 0, bisa update & hapus -->
                            <a class="action-icon update-icon" href="update/produk.php?id=<?= urlencode($row['id_produk']) ?>">
                                <img src="asset/update.png" alt="Update">
                            </a>
                            <a class="action-icon delete-icon" href="produk.php?hapus=<?= urlencode($row['id_produk']) ?>" onclick="return confirm('Yakin ingin menghapus produk expired ini?');">
                                <img src="asset/delete.png" alt="Delete">
                            </a>

                        <?php elseif ($stok == 0): ?>
                            <!-- PRODUK HABIS — bisa edit & hapus -->
                            <a class="action-icon update-icon" href="update/produk.php?id=<?= urlencode($row['id_produk']) ?>">
                                <img src="asset/update.png" alt="Update">
                            </a>
                            <a class="action-icon delete-icon" href="produk.php?hapus=<?= urlencode($row['id_produk']) ?>" onclick="return confirm('Yakin ingin menghapus produk ini?');">
                                <img src="asset/delete.png" alt="Delete">
                            </a>

                        <?php else: ?>
                            <!-- PRODUK MASIH ADA STOK -->
                            <a class="action-icon update-icon" href="update/produk.php?id=<?= urlencode($row['id_produk']) ?>">
                                <img src="asset/update.png" alt="Update">
                            </a>
                            <a class="action-icon cart-icon" href="produk.php?tambah_keranjang=<?= urlencode($row['id_produk']) ?>">
                                <img src="asset/keranjang.png" alt="Tambah ke Keranjang">
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <p>© 2025 Rich Noodles - Solusi Terbaik untuk Bisnis Mie Instan Anda</p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const profilePic = document.getElementById("profile-pic");
            const savedProfile = localStorage.getItem("profilePicture");
            
            if (savedProfile) {
                profilePic.src = savedProfile;
            }

            // Profile dropdown toggle
            const profileBtn = document.getElementById("profile-btn");
            const dropdownMenu = document.getElementById("dropdown-menu");

            profileBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle("active");
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function (event) {
                if (!profileBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove("active");
                }
            });
        });

        function changeStok(idProduk, change) {
            const stokElement = document.getElementById('stok-' + idProduk);
            let currentStok = parseInt(stokElement.textContent);
            
            let newStok = currentStok + change;
            if (newStok < 0) {
                alert("Stok tidak dapat kurang dari 0");
                return;
            }

            fetch('update_stok.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_produk: idProduk, stok: newStok })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    stokElement.textContent = newStok;
                } else {
                    alert('Gagal memperbarui stok! Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
            });
        }
    </script>
</body>
</html>