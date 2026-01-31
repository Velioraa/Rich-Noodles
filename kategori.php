<?php
session_start();
include "koneksi.php";

// Cek apakah ada request untuk menghapus kategori
if (isset($_GET['delete_id'])) {
    $id_kategori = $_GET['delete_id'];

    // Pastikan ID kategori adalah angka
    if (is_numeric($id_kategori)) {
        // Query untuk menghapus kategori berdasarkan id
        $queryDelete = "DELETE FROM kategori WHERE id_kategori = $id_kategori";

        if ($conn->query($queryDelete) === TRUE) {
            // Jika berhasil, redirect ke halaman kategori
            header("Location: kategori.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "ID kategori tidak valid.";
    }
}

// Ambil data kategori
$queryKategori = "SELECT * FROM kategori";
$resultKategori = $conn->query($queryKategori);

// Ambil data admin berdasarkan sesi
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Rich Noodles</title>
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

        .add-btn {
            background: var(--primary);
            color: white;
        }

        .add-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 40px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .category-name {
            font-weight: 500;
            color: var(--dark);
            font-size: 1rem;
        }

        .action-buttons-table {
            display: flex;
            gap: 8px;
        }

        .update-btn, .delete-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
            font-weight: 500;
        }

        .update-btn {
            background: var(--primary);
            color: white;
        }

        .update-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .delete-btn {
            background: var(--danger);
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .action-buttons-table img {
            width: 14px;
            height: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-state img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            opacity: 0.5;
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
            
            .action-buttons {
                justify-content: center;
            }
            
            .table-container {
                padding: 20px;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .logo-text {
                display: none;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .nav-menu {
                flex-wrap: wrap;
            }
            
            .nav-menu a {
                font-size: 0.8rem;
                padding: 5px 8px;
            }
            
            .action-buttons-table {
                flex-direction: column;
                gap: 5px;
            }
            
            .update-btn, .delete-btn {
                padding: 6px 10px;
                font-size: 11px;
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
        <a href="kategori.php" class="active">KATEGORI</a>
        <a href="produk.php">PRODUK</a>
        <a href="laporan.php">LAPORAN</a>
    </nav>
    
    <div class="icons">
        <a href="keranjang.php" class="icon-btn" style="position: relative;">
            <img src="asset/keranjang.png" alt="Cart">
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
        <h1>Manajemen Kategori</h1>
        <p>Kelola kategori produk mie instan</p>
    </div>

    <div class="controls-container">
        <div></div> <!-- Spacer untuk alignment -->
        
        <div class="action-buttons">
            <a href="create/c_kategori.php" class="action-btn add-btn">
                <img src="asset/plus.png" alt="Tambah" style="width: 18px; height: 18px;">
                Tambah Kategori
            </a>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultKategori->num_rows > 0): ?>
                    <?php while ($kategori = $resultKategori->fetch_assoc()) { ?>
                        <tr>
                            <td class="category-name"><?= htmlspecialchars($kategori['kategori']) ?></td>
                            <td>
                                <div class="action-buttons-table">
                                    <a href="update/kategori.php?id=<?= $kategori['id_kategori'] ?>" class="update-btn">
                                        <img src="asset/update.png" alt="Update">
                                        Edit
                                    </a>
                                    <a href="kategori.php?delete_id=<?= $kategori['id_kategori'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus kategori <?= htmlspecialchars($kategori['kategori']) ?>?')" 
                                       class="delete-btn">
                                        <img src="asset/delete.png" alt="Delete">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="empty-state">
                            <img src="asset/folder.png" alt="No Data">
                            <p>Belum ada kategori yang ditambahkan</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
</script>
</body>
</html>