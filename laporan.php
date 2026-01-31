<?php
session_start();
include "koneksi.php";

// Cek login admin
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Ambil data admin login
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();

// ----------------------------
// FILTER (tahun & tanggal)
// ----------------------------
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');
$tanggalFilter = $_GET['tanggal'] ?? null;

// ----------------------------
// GRAFIK KEUNTUNGAN PER BULAN
// ----------------------------
$keuntungan_bulan = array_fill(1, 12, 0.0);

$sql_bulanan = "SELECT 
        MONTH(t.tanggal_transaksi) AS bulan,
        SUM((p.harga_jual - p.modal) * dt.total_produk) AS keuntungan
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id_transaksi = dt.fid_transaksi
    JOIN produk p ON dt.fid_produk = p.id_produk
    WHERE YEAR(t.tanggal_transaksi) = ?
    GROUP BY MONTH(t.tanggal_transaksi)
    ORDER BY MONTH(t.tanggal_transaksi)";
$stmt_bulanan = $conn->prepare($sql_bulanan);
$stmt_bulanan->bind_param("i", $tahun);
$stmt_bulanan->execute();
$result_bulanan = $stmt_bulanan->get_result();
while ($row = $result_bulanan->fetch_assoc()) {
    $keuntungan_bulan[(int)$row['bulan']] = (float)$row['keuntungan'];
}

// ----------------------------
// TOTAL KESELURUHAN
// ----------------------------
$total_keuntungan = array_sum($keuntungan_bulan);

// Query untuk total modal dan total penjualan
$sql_total = "SELECT 
    SUM(p.modal * dt.total_produk) AS total_modal,
    SUM(p.harga_jual * dt.total_produk) AS total_penjualan
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.fid_transaksi
JOIN produk p ON dt.fid_produk = p.id_produk
WHERE YEAR(t.tanggal_transaksi) = ?";

if ($tanggalFilter) {
    $sql_total .= " AND DATE(t.tanggal_transaksi) = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("is", $tahun, $tanggalFilter);
} else {
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("i", $tahun);
}

$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_data = $result_total->fetch_assoc();
$total_modal = $total_data['total_modal'] ?? 0;
$total_penjualan = $total_data['total_penjualan'] ?? 0;

// ----------------------------
// TABEL TRANSAKSI (FILTER + PAGINATION + TAHUN)
// ----------------------------
$limit = 10;
$page  = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Hitung total transaksi (distinct)
if ($tanggalFilter) {
    $countQuery = "SELECT COUNT(DISTINCT t.id_transaksi) AS total
                   FROM transaksi t
                   WHERE DATE(t.tanggal_transaksi) = ? AND YEAR(t.tanggal_transaksi) = ?";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->bind_param("si", $tanggalFilter, $tahun);
} else {
    $countQuery = "SELECT COUNT(DISTINCT t.id_transaksi) AS total
                   FROM transaksi t
                   WHERE YEAR(t.tanggal_transaksi) = ?";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->bind_param("i", $tahun);
}
$stmtCount->execute();
$totalData = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
$totalPages = max(1, ceil($totalData / $limit));

// Ambil data transaksi beserta member, admin, metode pembayaran
$transaksiQuery = "SELECT 
    t.id_transaksi,
    t.fid_member,
    COALESCE(m.username, '-') AS nama_member,
    COALESCE(m.no_telp, '-') AS no_telp_member,
    t.total_harga,
    t.total_bayar,
    t.total_keuntungan,
    t.total_kembalian,
    t.fid_metode_pembayaran,
    mp.nama_metode,
    a.username AS nama_kasir,
    t.tanggal_transaksi
FROM transaksi t
LEFT JOIN member m ON t.fid_member = m.id
LEFT JOIN metode_pembayaran mp ON t.fid_metode_pembayaran = mp.id_metode_pembayaran
LEFT JOIN admin a ON t.fid_admin = a.id
WHERE YEAR(t.tanggal_transaksi) = ?";

if ($tanggalFilter) {
    $transaksiQuery .= " AND DATE(t.tanggal_transaksi) = ?";
}

$transaksiQuery .= " ORDER BY t.tanggal_transaksi DESC LIMIT ? OFFSET ?";
$stmtTrans = $conn->prepare($transaksiQuery);

// Bind sesuai kondisi
if ($tanggalFilter) {
    $stmtTrans->bind_param("isii", $tahun, $tanggalFilter, $limit, $offset);
} else {
    $stmtTrans->bind_param("iii", $tahun, $limit, $offset);
}
$stmtTrans->execute();
$transaksiResult = $stmtTrans->get_result();

// ----------------------------
// DETAIL PRODUK PER TRANSAKSI
// ----------------------------
$produkQuery = "SELECT 
    p.nama_produk, 
    dt.total_produk AS jumlah, 
    (dt.total_produk * p.harga_jual) AS subtotal
FROM detail_transaksi dt 
LEFT JOIN produk p ON dt.fid_produk = p.id_produk 
WHERE dt.fid_transaksi = ?";
$produkStmt = $conn->prepare($produkQuery);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - Rich Noodles</title>
    <link rel="website icon" type="image/png" href="asset/Richa Mart.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .filter-section {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.9rem;
        }

        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 2px solid var(--primary);
            border-radius: 6px;
            background: white;
            font-size: 14px;
            transition: var(--transition);
        }

        .filter-group select:focus, .filter-group input:focus {
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

        .print-btn {
            background: var(--primary);
            color: white;
        }

        .print-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
        }

        /* Chart Styles */
        .chart-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 25px;
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card h3 {
            color: var(--gray);
            font-size: 1rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .summary-card .amount {
            color: var(--primary-dark);
            font-size: 1.8rem;
            font-weight: 700;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 30px;
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

        .view-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 12px;
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

        .view-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .view-btn img {
            width: 14px;
            height: 14px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 25px 0;
        }

        .pagination a, .pagination span {
            padding: 8px 14px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
            transition: var(--transition);
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .current {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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
            max-width: 700px;
            max-height: 80vh;
            overflow: auto;
            position: relative;
            box-shadow: var(--shadow);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            transition: var(--transition);
        }

        .close-btn:hover {
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
            
            .filter-section {
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
            
            .summary-grid {
                grid-template-columns: 1fr;
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
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media print {
            .header, .action-buttons, .pagination { 
                display: none !important; 
            }
            body { 
                background: none; 
            }
            .main-content {
                padding: 0;
            }
            .table-container, .chart-container, .summary-grid {
                box-shadow: none;
                padding: 0;
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
        <a href="produk.php">PRODUK</a>
        <a href="laporan.php" class="active">LAPORAN</a>
    </nav>
    
    <div class="icons">
        <a href="keranjang.php" class="icon-btn" style="position: relative;">
            <img src="asset/keranjang.png" alt="Cart">
        </a>
        
        <div class="profile-dropdown">
            <button class="icon-btn" id="profile-btn">
                <img id="profile-pic" src="<?= htmlspecialchars($account['gambar'] ?? 'asset/default.png') ?>" alt="User">
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
        <h1>Laporan Penjualan</h1>
        <p>Analisis dan monitoring performa penjualan Rich Noodles</p>
    </div>

    <!-- Filter Controls -->
    <div class="controls-container">
        <div class="filter-section">
            <div class="filter-group">
                <label for="tahun">Tahun Grafik</label>
                <select id="tahun" name="tahun" onchange="document.getElementById('laporanFilter').submit()">
                    <option value="">Pilih Tahun</option>
                    <?php
                    $current_year = date('Y');
                    for ($y = $current_year; $y >= $current_year - 5; $y--) {
                        $selected = ($tahun == $y) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>
            
            <form method="GET" id="laporanFilter" class="filter-section">
                <div class="filter-group">
                    <label for="tanggal">Filter Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggalFilter ?? '') ?>" 
                           onchange="this.form.submit()">
                </div>
                <input type="hidden" name="tahun" value="<?= $tahun ?>">
            </form>
        </div>
        
        <div class="action-buttons">
            <a href="cetak_pdf_laporan.php?tahun=<?= $tahun ?>&tanggal=<?= $tanggalFilter ?>" 
               class="action-btn print-btn">
                Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Grafik Keuntungan -->
    <div class="chart-container">
        <canvas id="chartKeuntungan" width="1100" height="350"></canvas>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <h3>Total Keuntungan</h3>
            <div class="amount">Rp <?= number_format($total_keuntungan, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card">
            <h3>Total Modal</h3>
            <div class="amount">Rp <?= number_format($total_modal, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card">
            <h3>Total Penjualan</h3>
            <div class="amount">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="table-container">
        <h2 style="margin-bottom: 20px; color: var(--primary-dark);">Detail Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Produk</th>
                    <th>Total Harga</th>
                    <th>Total Bayar</th>
                    <th>Keuntungan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($transaksiResult && $transaksiResult->num_rows > 0): ?>
                    <?php $no = $offset + 1; ?>
                    <?php while ($row = $transaksiResult->fetch_assoc()): ?>
                        <?php
                        // Ambil semua detail produk untuk transaksi ini
                        $produkStmt->bind_param("i", $row['id_transaksi']);
                        $produkStmt->execute();
                        $produkRes = $produkStmt->get_result();
                        $produkList = [];
                        while ($p = $produkRes->fetch_assoc()) {
                            $produkList[] = $p['nama_produk'] . " (x" . $p['jumlah'] . ")";
                        }
                        $produkText = htmlspecialchars(implode(", ", $produkList));
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td style="text-align:left;"><?= $produkText ?></td>
                            <td>Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row['total_bayar'] ?? 0, 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row['total_keuntungan'] ?? 0, 0, ',', '.'); ?></td>
                            <td>
                                <button class="view-btn" onclick="showDetail(<?= $row['id_transaksi'] ?>)">
                                    <img src="asset/eye.png" alt="View">
                                    Lihat
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--gray);">
                            Tidak ada transaksi untuk periode yang dipilih.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Sebelumnya</a>
            <?php endif; ?>

            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Selanjutnya &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeDetail()">&times;</span>
        <h2>Detail Transaksi</h2>
        <div id="modalContent"></div>
    </div>
</div>

<div class="footer">
    <div class="footer-content">
        <p>© 2025 Rich Noodles - Solusi Terbaik untuk Bisnis Mie Instan Anda</p>
    </div>
</div>

<script>
    // Chart
    const ctx = document.getElementById('chartKeuntungan').getContext('2d');
    const data = {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [{
            label: 'Keuntungan (Rp)',
            data: <?= json_encode(array_values($keuntungan_bulan)) ?>,
            backgroundColor: '#6D9DC5',
            borderColor: '#4A7BA6',
            borderWidth: 1,
            borderRadius: 4
        }]
    };
    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: false,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Keuntungan (Rp)', font: { size: 14 } },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    title: { display: true, text: 'Bulan', font: { size: 14 } }
                }
            },
            plugins: { 
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Keuntungan: Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    };
    new Chart(ctx, config);

    // Profile dropdown functionality
    document.addEventListener("DOMContentLoaded", function () {
        const profilePic = document.getElementById("profile-pic");
        const savedProfile = localStorage.getItem("profilePicture");
        if (savedProfile) profilePic.src = savedProfile;

        const profileBtn = document.getElementById("profile-btn");
        const dropdownMenu = document.getElementById("dropdown-menu");

        profileBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle("active");
        });

        document.addEventListener("click", function (event) {
            if (!profileBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove("active");
            }
        });
    });

    // Modal detail fetch
    function showDetail(transaksiId) {
        fetch('get_detail_transaksi.php?id=' + transaksiId)
            .then(response => response.json())
            .then(data => {
                let content = '';
                if (data.success && data.detail.length > 0) {
                    content = `
                        <div style="text-align:left; margin-bottom:20px;">
                            <p><strong>Kasir:</strong> ${data.transaksi.nama_kasir || '-'}</p>
                            <p><strong>Tanggal:</strong> ${formatDateTime(data.transaksi.tanggal_transaksi)}</p>
                            <p><strong>Member:</strong> ${data.transaksi.nama_member || 'Non-member'}</p>
                            <p><strong>Metode Pembayaran:</strong> ${data.transaksi.nama_metode || '-'}</p>
                        </div>
                        <h3 style="margin-bottom:15px;">Detail Barang</h3>
                        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                            <thead>
                                <tr style="background:#f1f1f1;">
                                    <th style="padding:10px; text-align:left;">Nama Barang</th>
                                    <th style="padding:10px; text-align:center;">Jumlah</th>
                                    <th style="padding:10px; text-align:right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    data.detail.forEach(item => {
                        content += `
                            <tr>
                                <td style="padding:10px; text-align:left;">${item.nama_produk}</td>
                                <td style="padding:10px; text-align:center;">${item.jumlah}</td>
                                <td style="padding:10px; text-align:right;">Rp ${formatRupiah(item.subtotal)}</td>
                            </tr>
                        `;
                    });
                    content += `</tbody></table>
                        <div style="text-align:left; border-top:1px solid #ddd; padding-top:15px;">
                            <p><strong>Total Harga:</strong> Rp ${formatRupiah(data.transaksi.total_harga)}</p>
                            <p><strong>Total Bayar:</strong> Rp ${formatRupiah(data.transaksi.total_bayar)}</p>
                            <p><strong>Kembalian:</strong> Rp ${formatRupiah(data.transaksi.total_kembalian)}</p>
                            <p style="color:#27ae60; font-weight:bold;"><strong>Keuntungan:</strong> Rp ${formatRupiah(data.transaksi.total_keuntungan)}</p>
                        </div>
                    `;
                } else {
                    content = `<p style="text-align:center; padding:20px;">Tidak ada detail transaksi.</p>`;
                }
                document.getElementById('modalContent').innerHTML = content;
                document.getElementById('detailModal').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('modalContent').innerHTML = `<p style="text-align:center; padding:20px;">Tidak bisa menampilkan detail transaksi.</p>`;
                document.getElementById('detailModal').style.display = 'flex';
            });
    }

    function closeDetail() { 
        document.getElementById('detailModal').style.display = 'none'; 
    }

    function formatRupiah(angka) {
        if (angka === null || angka === undefined) return '0';
        return Number(angka).toLocaleString('id-ID');
    }

    function formatDateTime(dateString) {
        const d = new Date(dateString);
        return d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    }

    // Tutup modal jika klik di luar
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target === modal) closeDetail();
    }
</script>
</body>
</html>