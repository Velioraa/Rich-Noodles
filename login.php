<?php
session_start();
// Database connection settings
$servername = "localhost";
$username = "root"; // Username database
$password = ""; // Password database
$dbname = "kasir"; // Nama database

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Hapus admin yang tidak login lebih dari 6 bulan sebelum memproses login
$six_months_ago = date('Y-m-d H:i:s', strtotime('-6 month'));
$delete_query = "DELETE FROM admin WHERE last_login IS NOT NULL AND last_login < ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("s", $six_months_ago);
$stmt->execute();
$stmt->close();

// Cek jika user sudah login lewat sesi atau cookie
if (isset($_SESSION["username"]) && !empty($_SESSION["username"])) {
    header("Location: produk.php");
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['Email']) ? $_POST['Email'] : '';
    $password = isset($_POST['Password']) ? $_POST['Password'] : '';
    $remember = isset($_POST["remember"]) ? $_POST["remember"] : '';

    // Validasi input
    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan Password tidak boleh kosong!');</script>";
    } else {
        // Siapkan query untuk mengambil user admin berdasarkan email
        $stmt = $conn->prepare('SELECT * FROM admin WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = $row["username"];

            // Jika akun tidak ditemukan (karena sudah dihapus), beri pesan peringatan
            if (is_null($row['last_login'])) {
                echo "<script>alert('Akun Anda telah dihapus karena tidak login selama 6 bulan! Silakan hubungi admin.'); window.location.href='login.php';</script>";
                exit();
            }

            // Cek kapan terakhir kali login
            $last_login = strtotime($row['last_login']);
            $six_months_ago = strtotime('-6 months');

            if ($last_login < $six_months_ago) {
                echo "<script>alert('Maaf, Anda tidak bisa login karena sudah 6 bulan tidak login!'); window.location.href='login.php';</script>";
                exit();
            }

            // Cek jika password benar
                if (password_verify($password, $row['password'])) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['email'] = $row["email"];
                    $_SESSION["username"] = $username;

                    // Set cookie jika checkbox 'remember me' dicentang
                    $cookie = "";
                    if (!empty($remember)) {
                        $cookie = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
                        setcookie("token", "$cookie", time() + (86400 * 30), "/");
                    }

                    // Update waktu login terakhir dan simpan cookie baru
                    $stmt = $conn->prepare("UPDATE admin SET last_login = NOW(), cookie = ? WHERE username = ?");
                    $stmt->bind_param("ss", $cookie, $username);
                    $stmt->execute();

                    // Redirect ke halaman produk setelah login berhasil
                    echo "<script>
                            alert('Selamat Datang! $username');
                            window.location.href = 'produk.php';
                        </script>";
                    exit();
                } else {
                    echo "<script>alert('Password salah!');</script>";
                }
            } else {
                echo "<script>alert('Email tidak ditemukan!');</script>";
            }

        $stmt->close();
    }
}

// Tutup koneksi database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rich Noodles - Login</title>
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
            align-items: center;
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
            max-width: 350px;
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
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
        }

        .forgot-password {
            font-size: 0.9rem;
            color: var(--primary);
            text-align: center;
            margin-top: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-password:hover {
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
            <p>Admin Dashboard - Kelola toko mie instan Anda dengan mudah</p>
        </div>
        <div class="form-section">
            <h2>Masuk ke Akun</h2>
            <p class="subtitle">Silakan masuk untuk mengelola toko Anda</p>
            <form method="post">
                <div class="form-group">
                    <label class="form-label" for="Email">Email</label>
                    <input type="email" class="form-control" id="Email" name="Email" placeholder="Masukkan email Anda" required>
                    <div class="form-text">Gunakan email yang terdaftar</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="Password">Password</label>
                    <input type="password" class="form-control" id="Password" name="Password" placeholder="Masukkan password Anda" required>
                </div>

                <button type="submit" name="submit" class="btn">SIGN IN</button>
            </form>
            <div class="forgot-password">
                <a href="forgot.php">Forgot password?</a>
            </div>
        </div>
    </div>
</body>
</html>