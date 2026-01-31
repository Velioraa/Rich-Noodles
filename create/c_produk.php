<?php
session_start();
include '../koneksi.php'; // Pastikan koneksi ke database

// Function untuk generate kode barcode unik (12 digit angka)
function generateBarcode($length = 12) {
    $characters = '0123456789';
    $barcode = '';
    for ($i = 0; $i < $length; $i++) {
        $barcode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $barcode;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset(
            $_POST['nama_produk'],
            $_POST['stok'],
            $_POST['modal'],
            $_POST['harga_jual'],
            $_POST['tanggal_expired'],
            $_POST['kategori'],
            $_POST['deskripsi']
        )
    ) {
        $nama_produk    = trim($_POST['nama_produk']);
        $stok           = (int) $_POST['stok'];
        $modal          = (int) $_POST['modal'];
        $harga_jual     = (int) $_POST['harga_jual'];
        $tanggal_expired= $_POST['tanggal_expired'];
        $fid_kategori   = (int) $_POST['kategori'];
        $deskripsi      = trim($_POST['deskripsi']);

        // Hitung keuntungan
        $keuntungan = $harga_jual - $modal;

        // Generate kode barcode unik (ulang sampai dapat yang belum ada di DB)
        do {
            $kode_barcode = generateBarcode();
            $cek_barcode = "SELECT id_produk FROM produk WHERE kode_barcode = ?";
            $stmt_barcode = $conn->prepare($cek_barcode);
            $stmt_barcode->bind_param("s", $kode_barcode);
            $stmt_barcode->execute();
            $stmt_barcode->store_result();
        } while ($stmt_barcode->num_rows > 0);

        // Cek apakah nama produk sudah ada di database
        $cek_produk = "SELECT id_produk FROM produk WHERE nama_produk = ?";
        $stmt_cek = $conn->prepare($cek_produk);
        $stmt_cek->bind_param("s", $nama_produk);
        $stmt_cek->execute();
        $stmt_cek->store_result();

        if ($stmt_cek->num_rows > 0) {
            echo "<script>alert('Produk sudah ada!'); window.location.href='../produk.php';</script>";
            exit();
        }

        // Periksa apakah kategori ada
        $query = "SELECT id_kategori FROM kategori WHERE id_kategori = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $fid_kategori);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('Kategori tidak ditemukan!'); window.location.href='../produk.php';</script>";
            exit();
        }

        // Proses upload gambar
        $gambar = 'default.png'; // Default jika tidak ada gambar diunggah
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../asset/";
            $file_name = time() . '_' . basename($_FILES['gambar']['name']); // Rename file
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $valid_extensions)) {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    $gambar = $file_name;
                } else {
                    echo "<script>alert('Gagal mengunggah gambar!');</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Format gambar tidak valid! Hanya JPG, JPEG, PNG, GIF diperbolehkan.');</script>";
                exit();
            }
        }

        // Simpan produk ke database
        $query = "INSERT INTO produk 
            (nama_produk, kode_barcode, tanggal_expired, stok, modal, harga_jual, keuntungan, fid_kategori, gambar, deskripsi) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssiiiisss",
            $nama_produk,
            $kode_barcode,
            $tanggal_expired,
            $stok,
            $modal,
            $harga_jual,
            $keuntungan,
            $fid_kategori,
            $gambar,
            $deskripsi
        );

        if ($stmt->execute()) {
            echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href='../produk.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal menambahkan produk!');</script>";
        }
    } else {
        echo "<script>alert('Mohon isi semua field!');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Rich Noodles</title>
    <link rel="icon" type="image/png" href="../asset/Richa Mart.png">
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
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 90%;
            max-width: 800px;
            background: white;
            box-shadow: var(--shadow);
            border-radius: 12px;
            overflow: hidden;
            transition: var(--transition);
        }

        .container:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 25px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../asset/Richa Mart.png') no-repeat center center;
            background-size: 80px;
            opacity: 0.1;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
        }

        .logo img {
            width: 70%;
            height: 70%;
            object-fit: contain;
        }

        .form-header h1 {
            font-size: 1.4rem;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .form-header p {
            font-size: 0.8rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .form-section {
            padding: 25px;
        }

        .profit-preview {
            background: var(--primary-light);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .profit-preview p {
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.85rem;
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 13px;
            border-radius: 6px;
            border: 1.5px solid #ddd;
            background: #f9f9f9;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(109, 157, 197, 0.15);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%236D9DC5' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 10px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
            font-size: 13px;
        }

        .file-input {
            width: 100%;
            padding: 10px 12px;
            font-size: 13px;
            border-radius: 6px;
            border: 1.5px solid #ddd;
            background: #f9f9f9;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }

        .file-input::file-selector-button {
            background: var(--primary-light);
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            color: var(--primary-dark);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-right: 8px;
            font-size: 12px;
        }

        .file-input::file-selector-button:hover {
            background: var(--primary);
            color: white;
        }

        .form-text {
            font-size: 0.7rem;
            color: var(--gray);
            margin-top: 4px;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            background: var(--primary);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(109, 157, 197, 0.25);
        }

        .back-link {
            font-size: 0.8rem;
            color: var(--primary);
            text-align: center;
            margin-top: 15px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: block;
        }

        .back-link:hover {
            color: var(--primary-dark);
        }

        /* Animasi untuk logo */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                width: 95%;
            }

            .form-header {
                padding: 20px;
            }

            .form-header h1 {
                font-size: 1.3rem;
            }

            .form-section {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .button-group {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                width: 100%;
            }
            
            .form-header {
                padding: 15px;
            }
            
            .logo {
                width: 50px;
                height: 50px;
            }
            
            .form-header h1 {
                font-size: 1.2rem;
            }
            
            .form-section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <div class="logo floating">
                <img src="../asset/Richa Mart.png" alt="Rich Noodles">
            </div>
            <h1>Tambah Produk Baru</h1>
            <p>Lengkapi detail produk untuk menambahkannya ke inventori</p>
        </div>

        <div class="form-section">
            <div class="profit-preview" id="profitPreview">
                <p>💰 Keuntungan: <span id="profitValue">Rp 0</span></p>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="nama_produk">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" placeholder="Masukkan nama produk" required>
                        <div class="form-text">Nama produk yang akan ditampilkan</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="kategori">Kategori</label>
                        <select class="form-control" id="kategori" name="kategori" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            <?php
                            $query = "SELECT * FROM kategori";
                            $result = $conn->query($query);

                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id_kategori'] . "'>" . htmlspecialchars($row['kategori']) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="form-text">Pilih kategori produk</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stok">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" placeholder="Jumlah stok" min="0" required>
                        <div class="form-text">Jumlah produk yang tersedia</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="modal">Modal (Rp)</label>
                        <input type="number" class="form-control" id="modal" name="modal" placeholder="Harga modal" min="0" required>
                        <div class="form-text">Harga beli produk</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="harga_jual">Harga Jual (Rp)</label>
                        <input type="number" class="form-control" id="harga_jual" name="harga_jual" placeholder="Harga jual" min="0" required>
                        <div class="form-text">Harga jual ke konsumen</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tanggal_expired">Tanggal Expired</label>
                        <input type="date" class="form-control" id="tanggal_expired" name="tanggal_expired" required>
                        <div class="form-text">Tanggal kadaluarsa produk</div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="gambar">Gambar Produk</label>
                        <input type="file" class="file-input" id="gambar" name="gambar" accept="image/*">
                        <div class="form-text">Format: JPG, JPEG, PNG, GIF (opsional)</div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="deskripsi">Deskripsi Produk</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" placeholder="Masukkan deskripsi produk" required></textarea>
                        <div class="form-text">Deskripsi detail tentang produk</div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">TAMBAH PRODUK</button>
                </div>
            </form>
            
            <a href="../produk.php" class="back-link">← Kembali ke halaman produk</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Fokus ke input nama produk saat halaman dimuat
            document.getElementById('nama_produk').focus();
            
            // Hitung keuntungan otomatis
            const modalInput = document.getElementById('modal');
            const hargaJualInput = document.getElementById('harga_jual');
            const profitValue = document.getElementById('profitValue');
            
            function calculateProfit() {
                const modal = parseInt(modalInput.value) || 0;
                const hargaJual = parseInt(hargaJualInput.value) || 0;
                const profit = hargaJual - modal;
                
                profitValue.textContent = 'Rp ' + profit.toLocaleString('id-ID');
                
                // Update warna berdasarkan profit
                if (profit > 0) {
                    profitValue.style.color = '#28a745';
                } else if (profit < 0) {
                    profitValue.style.color = '#dc3545';
                } else {
                    profitValue.style.color = '#6c757d';
                }
            }
            
            modalInput.addEventListener('input', calculateProfit);
            hargaJualInput.addEventListener('input', calculateProfit);
            
            // Set tanggal minimal untuk expired date (hari ini)
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_expired').min = today;
            
            // Validasi form sebelum submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const namaProduk = document.getElementById('nama_produk');
                const stok = document.getElementById('stok');
                const modal = document.getElementById('modal');
                const hargaJual = document.getElementById('harga_jual');
                const tanggalExpired = document.getElementById('tanggal_expired');
                const kategori = document.getElementById('kategori');
                const deskripsi = document.getElementById('deskripsi');
                
                if (namaProduk.value.trim() === '') {
                    e.preventDefault();
                    alert('Mohon isi nama produk!');
                    namaProduk.focus();
                    return;
                }
                
                if (stok.value < 0) {
                    e.preventDefault();
                    alert('Stok tidak boleh negatif!');
                    stok.focus();
                    return;
                }
                
                if (modal.value < 0) {
                    e.preventDefault();
                    alert('Modal tidak boleh negatif!');
                    modal.focus();
                    return;
                }
                
                if (hargaJual.value < 0) {
                    e.preventDefault();
                    alert('Harga jual tidak boleh negatif!');
                    hargaJual.focus();
                    return;
                }
                
                if (parseInt(hargaJual.value) < parseInt(modal.value)) {
                    const confirmSubmit = confirm('Harga jual lebih rendah dari modal. Apakah Anda yakin ingin melanjutkan?');
                    if (!confirmSubmit) {
                        e.preventDefault();
                        hargaJual.focus();
                        return;
                    }
                }
                
                if (kategori.value === '') {
                    e.preventDefault();
                    alert('Mohon pilih kategori!');
                    kategori.focus();
                    return;
                }
                
                if (deskripsi.value.trim() === '') {
                    e.preventDefault();
                    alert('Mohon isi deskripsi produk!');
                    deskripsi.focus();
                    return;
                }
                
                if (tanggalExpired.value === '') {
                    e.preventDefault();
                    alert('Mohon pilih tanggal expired!');
                    tanggalExpired.focus();
                    return;
                }
            });

            // Preview gambar sebelum upload
            const gambarInput = document.getElementById('gambar');
            gambarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = file.size / 1024 / 1024; // MB
                    if (fileSize > 5) {
                        alert('Ukuran file terlalu besar! Maksimal 5MB.');
                        e.target.value = '';
                    }
                }
            });
        });
    </script>
</body>
</html>