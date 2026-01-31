<?php
session_start();
include '../koneksi.php'; // Pastikan koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['kategori']) && !empty($_POST['kategori'])) {
        $kategori = trim($_POST['kategori']); // Hindari spasi berlebih

        // Cek apakah kategori sudah ada
        $cek_kategori = "SELECT id_kategori FROM kategori WHERE kategori = ?";
        $stmt_cek = $conn->prepare($cek_kategori);
        $stmt_cek->bind_param("s", $kategori);
        $stmt_cek->execute();
        $stmt_cek->store_result();

        if ($stmt_cek->num_rows > 0) {
            echo "<script>alert('Kategori sudah ada!'); window.location.href='../kategori.php';</script>";
            exit();
        }

        // Query untuk menambahkan kategori
        $query = "INSERT INTO kategori (kategori) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $kategori);

        if ($stmt->execute()) {
            echo "<script>alert('Kategori berhasil ditambahkan!'); window.location.href='../kategori.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal menambahkan kategori: " . $stmt->error . "');</script>";
        }

        $stmt->close();
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
    <title>Tambah Kategori - Rich Noodles</title>
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
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 90%;
            max-width: 1000px;
            background: white;
            box-shadow: var(--shadow);
            border-radius: 15px;
            overflow: hidden;
            transition: var(--transition);
        }

        .container:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .image-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../asset/Richa Mart.png') no-repeat center center;
            background-size: contain;
            opacity: 0.1;
        }

        .logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
        }

        .logo img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .image-section h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .image-section p {
            font-size: 0.9rem;
            opacity: 0.9;
            max-width: 300px;
            position: relative;
            z-index: 1;
        }

        .form-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
            text-align: center;
        }

        .form-header p {
            color: var(--gray);
            text-align: center;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 14px;
            border-radius: 8px;
            border: 2px solid #ddd;
            background: #f9f9f9;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(109, 157, 197, 0.2);
        }

        .form-text {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 5px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            background: var(--primary);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
        }

        .btn-secondary {
            background: var(--gray);
        }

        .back-link {
            font-size: 0.9rem;
            color: var(--primary);
            text-align: center;
            margin-top: 20px;
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
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 5s ease-in-out infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .image-section {
                padding: 30px 20px;
            }

            .image-section h1 {
                font-size: 1.5rem;
            }

            .form-section {
                padding: 30px 20px;
            }

            .form-header h2 {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                width: 100%;
            }
            
            .image-section {
                padding: 20px 15px;
            }
            
            .logo {
                width: 100px;
                height: 100px;
            }
            
            .form-section {
                padding: 25px 15px;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <div class="logo floating">
                <img src="../asset/Richa Mart.png" alt="Rich Noodles">
            </div>
            <h1>Rich Noodles</h1>
            <p>Tambah kategori baru untuk produk mie instan</p>
        </div>
        <div class="form-section">
            <div class="form-header">
                <h2>Tambah Kategori Baru</h2>
                <p>Isi form berikut untuk menambahkan kategori produk</p>
            </div>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label" for="kategori">Nama Kategori</label>
                    <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Masukkan nama kategori" required>
                    <div class="form-text">Contoh: Mie Kuah, Mie Goreng, Mie Pedas, dll.</div>
                </div>

                <button type="submit" class="btn">TAMBAH KATEGORI</button>
            </form>
            
            <a href="../kategori.php" class="back-link">← Kembali ke halaman kategori</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Fokus ke input kategori saat halaman dimuat
            document.getElementById('kategori').focus();
            
            // Validasi form sebelum submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const kategoriInput = document.getElementById('kategori');
                if (kategoriInput.value.trim() === '') {
                    e.preventDefault();
                    alert('Mohon isi nama kategori!');
                    kategoriInput.focus();
                }
            });
        });
    </script>
</body>
</html>