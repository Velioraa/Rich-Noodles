<?php
session_start();
include "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Ambil data akun yang sedang login
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

$id_admin = $account['id']; // ID admin yang sedang login
date_default_timezone_set('Asia/Jakarta');

// Waktu 6 bulan yang lalu dari sekarang
$six_months_ago = date('Y-m-d H:i:s', strtotime('-6 month'));

// Hapus admin yang tidak login lebih dari 6 bulan dan bukan admin yang sedang login
$sql_delete_old_admins = "DELETE FROM admin WHERE last_login IS NOT NULL AND last_login < ? AND id != ?";
$stmt_delete_old_admins = $conn->prepare($sql_delete_old_admins);
$stmt_delete_old_admins->bind_param("si", $six_months_ago, $id_admin);
$stmt_delete_old_admins->execute();
$affected_rows = $stmt_delete_old_admins->affected_rows; // Cek jumlah akun yang dihapus

// Ambil semua admin kecuali admin yang sedang login + fitur pencarian email
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $query_admin = "SELECT * FROM admin WHERE id != ? AND email LIKE ?";
    $stmt_admin = $conn->prepare($query_admin);
    $searchTerm = "%" . $search . "%";
    $stmt_admin->bind_param('is', $id_admin, $searchTerm);
} else {
    $query_admin = "SELECT * FROM admin WHERE id != ?";
    $stmt_admin = $conn->prepare($query_admin);
    $stmt_admin->bind_param('i', $id_admin);
}

$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();


// Fungsi hapus admin secara manual
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];

    // Cek apakah yang dihapus adalah admin yang sedang login
    if ($id_hapus == $id_admin) {
        echo "<script>alert('Anda tidak bisa menghapus akun yang sedang digunakan!'); window.location.href = 'admin.php';</script>";
        exit();
    }

    // Lanjutkan penghapusan jika bukan admin yang login
    $sql_delete = "DELETE FROM admin WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $id_hapus);
    $stmt_delete->execute();

    echo "<script>alert('Admin berhasil dihapus!'); window.location.href = 'admin.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Rich Noodles</title>
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

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 40px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-header h2 {
            color: var(--primary-dark);
            font-size: 1.5rem;
        }

        .search-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            border: 2px solid var(--primary);
            border-radius: 8px;
            padding: 8px 12px;
            background: white;
            transition: var(--transition);
        }

        .search-bar:focus-within {
            box-shadow: 0 0 0 3px rgba(109, 157, 197, 0.2);
        }

        .search-bar img {
            width: 16px;
            height: 16px;
            margin-right: 8px;
        }

        .search-bar input {
            border: none;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            width: 200px;
            background: none;
        }


        .add-admin-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 14px;
        }

        .add-admin-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
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

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .view-btn, .delete-btn {
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

        .view-btn {
            background: var(--primary);
            color: white;
        }

        .view-btn:hover {
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

        .action-buttons img {
            width: 14px;
            height: 14px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow);
            position: relative;
        }

        .close {
            color: #aaa;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            color: var(--dark);
        }

        .admin-detail {
            text-align: center;
            padding: 20px 0;
        }

        .admin-detail img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid var(--primary-light);
            box-shadow: var(--shadow);
        }

        .admin-detail h3 {
            margin: 15px 0;
            color: var(--primary-dark);
            font-size: 1.4rem;
        }

        .admin-detail p {
            margin: 8px 0;
            color: var(--gray);
            font-size: 0.95rem;
        }

        .admin-detail .detail-label {
            font-weight: 600;
            color: var(--dark);
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
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
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
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .view-btn, .delete-btn {
                padding: 6px 10px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<?php if ($affected_rows > 0): ?>
    <script>alert("<?= $affected_rows ?> admin yang tidak aktif lebih dari 6 bulan telah dihapus!");</script>
<?php endif; ?>

<div class="header">
    <div class="logo">
        <img src="asset/Richa Mart.png" alt="Rich Noodles">
    </div>
    
    <nav class="nav-menu">
        <a href="admin.php" class="active">ADMIN</a>
        <a href="member.php">MEMBER</a>
        <a href="kategori.php">KATEGORI</a>
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
        <h1>Manajemen Admin</h1>
        <p>Kelola data administrator sistem Rich Noodles</p>
    </div>

    <div class="table-container">
    <div class="table-header">
        <h2>Daftar Administrator</h2>
        <div class="search-section">
            <form method="GET" action="admin.php" class="search-bar">
                <img src="asset/search.png" alt="Search">
                <input type="text" name="search" placeholder="Cari berdasarkan email..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <a href="register.php" class="add-admin-btn">
                <img src="asset/plus.png" alt="Tambah" style="width: 16px; height: 16px;">
                Tambah Admin
            </a>
        </div>
    </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = $result_admin->fetch_assoc()) { 
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="view-btn" onclick="showAdminDetail(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <img src="asset/eye.png" alt="View"> Lihat
                                </button>
                                <a href="admin.php?hapus=<?= $row['id'] ?>" 
                                   onclick="return confirm('Yakin ingin menghapus admin <?= htmlspecialchars($row['username']) ?>?');" 
                                   class="delete-btn">
                                    <img src="asset/delete.png" alt="Delete"> Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal untuk detail admin -->
<div id="adminModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="adminDetail" class="admin-detail">
            <!-- Detail admin akan dimuat di sini -->
        </div>
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

        // Modal functionality
        const modal = document.getElementById("adminModal");
        const closeBtn = document.querySelector(".close");

        closeBtn.addEventListener("click", function() {
            modal.style.display = "none";
        });

        window.addEventListener("click", function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    });

    // Fungsi untuk menampilkan detail admin
    function showAdminDetail(admin) {
        const modal = document.getElementById("adminModal");
        const adminDetail = document.getElementById("adminDetail");
        
        adminDetail.innerHTML = `
            <img src="${admin.gambar || 'asset/default.png'}" alt="${admin.username}">
            <h3>${admin.username}</h3>
            <p><span class="detail-label">Email:</span> ${admin.email}</p>
            <p><span class="detail-label">Tanggal Daftar:</span> ${new Date(admin.created_at).toLocaleDateString('id-ID')}</p>
            <p><span class="detail-label">Login terakhir:</span> ${admin.last_login ? new Date(admin.last_login).toLocaleString('id-ID') : 'Belum pernah login'}</p>
        `;
        
        modal.style.display = "block";
    }
</script>
</body>
</html>