<?php
session_start();
include 'koneksi.php'; // Menyertakan file koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $repassword = $_POST['repassword'];

    // Periksa apakah password dan retype password cocok
    if ($_POST['password'] !== $_POST['repassword']) {
        echo "<script>alert('Password dan retype password tidak cocok!'); window.location='register.php';</script>";
        exit();
    }

    // Periksa apakah email sudah terdaftar
    $cek_email = "SELECT email FROM admin WHERE email = ?";
    $stmt_cek = $conn->prepare($cek_email);
    $stmt_cek->bind_param("s", $email);
    $stmt_cek->execute();
    $stmt_cek->store_result();

    if ($stmt_cek->num_rows > 0) {
        echo "<script>alert('Admin sudah terdaftar!'); window.location='register.php';</script>";
        exit();
    }

    // Periksa apakah folder asset ada
    $target_dir = "asset/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Buat folder jika belum ada
    }

    // Proses upload gambar
    $gambar = $_FILES['gambar']['name'];
    $target_file = $target_dir . time() . "_" . basename($gambar); // Hindari duplikasi nama file

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
        // Simpan ke database
        $query = "INSERT INTO admin (username, email, password, gambar) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $password, $target_file);

        if ($stmt->execute()) {
            echo "<script>alert('Registrasi berhasil!'); window.location='admin.php';</script>";
        } else {
            echo "<script>alert('Registrasi gagal!'); window.location='register.php';</script>";
        }
    } else {
        echo "<script>alert('Upload gambar gagal! Pastikan format file benar.'); window.location='register.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rich Noodles - Register</title>
    <link rel="website icon" type="png" href="asset/Richa Mart.png">
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
            background: url('asset/Richa Mart.png') no-repeat center center;
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

        .form-section h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-dark);
            text-align: center;
        }

        .form-section .subtitle {
            color: var(--gray);
            margin-bottom: 30px;
            text-align: center;
            font-size: 0.9rem;
        }

        .form-section form {
            width: 100%;
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
            border: 1px solid #ddd;
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

        .file-input-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            width: 100%;
            padding: 12px 15px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }

        .file-input::file-selector-button {
            background: var(--primary-light);
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            color: var(--primary-dark);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-right: 10px;
        }

        .file-input::file-selector-button:hover {
            background: var(--primary);
            color: white;
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

        .btn-secondary:hover {
            background: #5a6268;
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

            .form-section h2 {
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
            
            .form-section h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <div class="logo floating">
                <img src="asset/Richa Mart.png" alt="Rich Noodles">
            </div>
            <h1>Rich Noodles</h1>
            <p>Bergabunglah dengan tim admin kami</p>
        </div>
        <div class="form-section">
            <h2>Buat Akun Baru</h2>
            <p class="subtitle">Isi data diri Anda untuk membuat akun admin</p>
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="username">Nama Lengkap</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan alamat email Anda" required>
                    <div class="form-text">Pastikan email belum terdaftar sebelumnya</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Buat password Anda" required>
                    <div class="form-text">Buat password yang mudah diingat namun aman</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="repassword">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="repassword" name="repassword" placeholder="Ulangi password Anda" required>
                    <div class="form-text">Pastikan password sama dengan di atas</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="gambar">Foto Profil</label>
                    <input type="file" class="file-input" id="gambar" name="gambar" required>
                    <div class="form-text">Format yang didukung: JPG, PNG, GIF</div>
                </div>

                <button type="submit" class="btn">DAFTAR SEKARANG</button>
                <button type="button" class="btn btn-secondary" onclick="window.location='admin.php';">KEMBALI</button>
            </form>
        </div>
    </div>
</body>
</html>