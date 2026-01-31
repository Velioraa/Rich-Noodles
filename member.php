<?php
session_start();
include "koneksi.php";

// Cek apakah admin sudah login
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// ==============================
// Hanguskan poin member yang tidak bertransaksi lebih dari 1 tahun
// ==============================
$one_year_ago = date('Y-m-d H:i:s', strtotime('-1 year'));
$updatePointsQuery = "UPDATE member SET point = 0 WHERE last_transaction < ? AND status = 'aktif'";
$stmt = $conn->prepare($updatePointsQuery);
$stmt->bind_param("s", $one_year_ago);
$stmt->execute();

// ==============================
// Nonaktifkan member yang tidak bertransaksi lebih dari 3 tahun
// ==============================
$three_years_ago = date('Y-m-d H:i:s', strtotime('-3 years'));
$updateStatusQuery = "UPDATE member SET status = 'tidak aktif' WHERE last_transaction < ?";
$stmt2 = $conn->prepare($updateStatusQuery);
$stmt2->bind_param("s", $three_years_ago);
$stmt2->execute();


// Pencarian member
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $query = "SELECT * FROM member WHERE no_telp LIKE ? ORDER BY status DESC, id ASC";
    $stmt = $conn->prepare($query);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result_member = $stmt->get_result();
} else {
    $query = "SELECT * FROM member ORDER BY status DESC, id ASC";
    $result_member = mysqli_query($conn, $query);
}

// Hapus member jika tidak aktif
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // Cek status member
    $cekStatus = $conn->prepare("SELECT status FROM member WHERE id = ?");
    $cekStatus->bind_param("i", $id);
    $cekStatus->execute();
    $resultStatus = $cekStatus->get_result();

    if ($resultStatus->num_rows > 0) {
        $rowStatus = $resultStatus->fetch_assoc();

        if ($rowStatus['status'] === 'aktif') {
            echo "<script>alert('Member aktif tidak bisa dihapus!'); window.location='member.php';</script>";
        } else {
            $query = "DELETE FROM member WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo "<script>alert('Member berhasil dihapus!'); window.location='member.php';</script>";
            } else {
                echo "<script>alert('Gagal menghapus member!'); window.location='member.php';</script>";
            }

            $stmt->close();
        }
    }

    $cekStatus->close();
    exit;
}

// Aktivasi member
if (isset($_GET['aktivasi'])) {
    $id = $_GET['aktivasi'];

    $query = "UPDATE member SET status = 'aktif' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Member berhasil diaktifkan!'); window.location='member.php';</script>";
    } else {
        echo "<script>alert('Gagal mengaktifkan member!'); window.location='member.php';</script>";
    }

    $stmt->close();
    exit;
}

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
    <title>Member - Rich Noodles</title>
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
            --info: #17A2B8;
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
            padding: 10px 15px;
            background: white;
            transition: var(--transition);
        }

        .search-bar:focus-within {
            box-shadow: 0 0 0 3px rgba(109, 157, 197, 0.2);
        }

        .search-bar input {
            border: none;
            outline: none;
            font-size: 14px;
            padding: 5px;
            width: 250px;
            font-family: 'Poppins', sans-serif;
        }

        .search-bar img {
            width: 18px;
            height: 18px;
            margin-right: 8px;
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

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-aktif {
            background: #d4edda;
            color: #155724;
        }

        .status-tidak-aktif {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons-table {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .view-btn, .delete-btn, .activate-btn {
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
            white-space: nowrap;
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

        .activate-btn {
            background: var(--success);
            color: white;
        }

        .activate-btn:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .action-buttons-table img {
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

        .member-card {
            text-align: center;
            padding: 20px 0;
        }

        .member-card h3 {
            margin: 15px 0;
            color: var(--primary-dark);
            font-size: 1.4rem;
        }

        .member-card p {
            margin: 8px 0;
            color: var(--gray);
            font-size: 0.95rem;
            text-align: left;
        }

        .member-card .detail-label {
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
            
            .controls-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-section {
                justify-content: center;
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
            
            .search-bar input {
                width: 200px;
            }
            
            .action-buttons-table {
                flex-direction: column;
                gap: 5px;
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
            
            .search-bar input {
                width: 150px;
            }
            
            .view-btn, .delete-btn, .activate-btn {
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
        <a href="member.php" class="active">MEMBER</a>
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
        <h1>Manajemen Member</h1>
        <p>Kelola data member loyal Rich Noodles</p>
    </div>

    <div class="controls-container">
        <div class="search-section">
            <form class="search-bar" method="GET" action="member.php">
                <img src="asset/search.png" alt="Search">
                <input type="text" name="search" placeholder="Cari berdasarkan nomor telepon..." value="<?= htmlspecialchars($search); ?>">
            </form>
        </div>
        
        <div class="action-buttons">
            <a href="create/c_member.php" class="action-btn add-btn">
                <img src="asset/plus.png" alt="Tambah" style="width: 18px; height: 18px;">
                Tambah Member
            </a>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>No Telepon</th>
                    <th>Status</th>
                    <th>Poin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = $result_member->fetch_assoc()) { 
                    $isAktif = $row['status'] === 'aktif';
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['username'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['no_telp'] ?? 'Tidak tersedia') ?></td>
                        <td>
                            <span class="status-badge <?= $isAktif ? 'status-aktif' : 'status-tidak-aktif' ?>">
                                <?= htmlspecialchars($row['status'] ?? 'Tidak diketahui') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['point'] ?? '0') ?></td>
                        <td>
                            <div class="action-buttons-table">
                                <!-- Tombol View -->
                                <button class="view-btn" onclick="showMemberDetail(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    <img src="asset/eye.png" alt="View"> Lihat
                                </button>
                                
                                <?php if (!$isAktif): ?>
                                    <!-- Tombol Aktivasi untuk member tidak aktif -->
                                    <a href="member.php?aktivasi=<?= $row['id'] ?>" 
                                       onclick="return confirm('Yakin ingin mengaktifkan member <?= htmlspecialchars($row['username']) ?>?');" 
                                       class="activate-btn">
                                        <img src="asset/icons8-change-user-50.png" alt="Aktivasi"> Aktivasi
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Tombol Delete -->
                                <a href="member.php?hapus=<?= $row['id'] ?>" 
                                   onclick="return confirm('Yakin ingin menghapus member <?= htmlspecialchars($row['username']) ?>?');" 
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

<!-- Modal untuk detail member -->
<div id="memberModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="memberDetail" class="member-card">
            <!-- Detail member akan dimuat di sini -->
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
        const modal = document.getElementById("memberModal");
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

    // Fungsi untuk menampilkan detail member dalam card
    function showMemberDetail(member) {
        const modal = document.getElementById("memberModal");
        const memberDetail = document.getElementById("memberDetail");
        
        // Format tanggal transaksi terakhir
        const lastTransaction = member.last_transaction ? 
            new Date(member.last_transaction).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : 'Belum transaksi';
        
        memberDetail.innerHTML = `
            <h3>${member.username}</h3>
            <p><span class="detail-label">No Telepon:</span> ${member.no_telp || 'Tidak tersedia'}</p>
            <p><span class="detail-label">Status:</span> 
                <span class="status-badge ${member.status === 'aktif' ? 'status-aktif' : 'status-tidak-aktif'}">
                    ${member.status}
                </span>
            </p>
            <p><span class="detail-label">Poin:</span> ${member.point || '0'}</p>
            <p><span class="detail-label">Transaksi Terakhir:</span> ${lastTransaction}</p>
            <p><span class="detail-label">Terdaftar sejak:</span> ${new Date(member.created_at).toLocaleDateString('id-ID')}</p>
        `;
        
        modal.style.display = "block";
    }
</script>
</body>
</html>