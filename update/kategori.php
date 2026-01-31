<?php
session_start();
include "../koneksi.php"; 

if (!isset($_GET['id'])) {
    header("Location: ../kategori.php");
    exit();
}

$id_kategori = $_GET['id'];

// Mengambil data kategori berdasarkan id_kategori
$sql = "SELECT * FROM kategori WHERE id_kategori = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_kategori);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Kategori tidak ditemukan!'); window.location.href='../kategori.php';</script>";
    exit();
}

$kategori = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kategori = $_POST['nama_kategori'];

    // **Cek apakah kategori dengan nama yang sama sudah ada di database, kecuali kategori yang sedang diupdate**
    $sql_cek = "SELECT id_kategori FROM kategori WHERE kategori = ? AND id_kategori != ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("si", $nama_kategori, $id_kategori);
    $stmt_cek->execute();
    $stmt_cek->store_result();

    if ($stmt_cek->num_rows > 0) {
        // Jika kategori sudah ada
        echo "<script>alert('Kategori sudah ada!'); window.location.href='';</script>";
        exit();
    }

    // Query untuk update kategori
    $sql_update = "UPDATE kategori SET kategori = ? WHERE id_kategori = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $nama_kategori, $id_kategori);

    if ($stmt_update->execute()) {
        echo "<script>alert('Kategori berhasil diperbarui!'); window.location.href='../kategori.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui kategori!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Kategori - Rich Noodles</title>
    <link rel="website icon" type="png" href="../asset/Richa Mart.png">
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
            width: 90%;
            max-width: 500px;
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
            background-size: 60px;
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

        .current-category {
            background: var(--primary-light);
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
            text-align: center;
            border-left: 3px solid var(--primary);
        }

        .current-category p {
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.85rem;
            margin: 0;
        }

        .form-section {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
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

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            background: var(--primary);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
        }
        
        .back-link {
            font-size: 0.8rem;
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

            .button-group {
                grid-template-columns: 1fr;
                gap: 10px;
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
            <h1>Update Kategori</h1>
            <p>Perbarui nama kategori produk</p>
        </div>

        <div class="form-section">
            <div class="current-category">
                <p>📁 Kategori Saat Ini: <strong><?php echo htmlspecialchars($kategori['kategori']); ?></strong></p>
            </div>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label" for="nama_kategori">Nama Kategori Baru</label>
                    <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                           value="<?php echo htmlspecialchars($kategori['kategori']); ?>" 
                           placeholder="Masukkan nama kategori baru" required>
                    <div class="form-text">Masukkan nama kategori yang baru</div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">UPDATE KATEGORI</button>
                </div>
            </form>
            
            <a href="../kategori.php" class="back-link">← Kembali ke halaman kategori</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Fokus ke input kategori saat halaman dimuat
            document.getElementById('nama_kategori').focus();
            
            // Validasi form sebelum submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const namaKategori = document.getElementById('nama_kategori');
                
                if (namaKategori.value.trim() === '') {
                    e.preventDefault();
                    alert('Mohon isi nama kategori!');
                    namaKategori.focus();
                    return;
                }
                
                // Validasi jika nama kategori sama dengan yang lama
                const currentCategory = "<?php echo htmlspecialchars($kategori['kategori']); ?>";
                if (namaKategori.value.trim() === currentCategory) {
                    e.preventDefault();
                    alert('Nama kategori sama dengan yang lama!');
                    namaKategori.focus();
                    return;
                }
            });

            // Highlight input saat fokus
            const input = document.getElementById('nama_kategori');
            input.addEventListener('focus', function() {
                this.select();
            });
        });
    </script>
</body>
</html>