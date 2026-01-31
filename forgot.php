<?php
session_start();

// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kasir";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // pastikan sudah install composer require phpmailer/phpmailer

// Step 1: Kirim kode OTP ke email user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_code'])) {
    $email = trim($_POST['email']);

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid');</script>";
    } else {
        // Cek email di database
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generate kode OTP
            $otp = rand(100000, 999999);

            // Simpan OTP dan email di session
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_time'] = time(); // simpan waktu OTP dibuat
            $_SESSION['reset_step'] = 2;

            // Kirim email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'smknhumas1@gmail.com'; // ganti email pengirim
                $mail->Password = 'cksn dcgo xacf srcw';       // ganti App Password Gmail
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('emailpengirim@gmail.com', 'Kasir App'); 
                $mail->addAddress($email); 

                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Reset Password';
                $mail->Body    = "Halo,<br>Ini adalah kode verifikasi reset password kamu: <b>$otp</b><br>Kode ini berlaku 5 menit.";

                $mail->send();
                echo "<script>alert('Kode OTP sudah dikirim ke email');</script>";
            } catch (Exception $e) {
                echo "<script>alert('Email gagal dikirim. Error: {$mail->ErrorInfo}');</script>";
            }
        } else {
            echo "<script>alert('Email tidak ada di database');</script>";
        }
        $stmt->close();
    }
}

// Step 2: Verifikasi kode OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_code'])) {
    $otp_input = trim($_POST['otp']);
    $current_time = time();

    if (isset($_SESSION['otp']) && isset($_SESSION['otp_time']) && $current_time - $_SESSION['otp_time'] <= 300) {
        if ($_SESSION['otp'] == $otp_input) {
            $_SESSION['reset_step'] = 3; // lanjut ke reset password
        } else {
            echo "<script>alert('Kode OTP salah');</script>";
        }
    } else {
        echo "<script>alert('Kode OTP sudah kadaluarsa');</script>";
        session_unset();
        session_destroy();
    }
}

// Step 3: Reset Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = $_SESSION['reset_email'] ?? '';

    if (!empty($email)) {
        if ($new_password === $confirm_password) {
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE admin SET password=? WHERE email=?");
            $stmt->bind_param("ss", $hash, $email);

            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                echo "<script>
                        alert('Password berhasil diubah');
                        window.location.href='login.php';
                      </script>";
                exit;
            } else {
                echo "<script>alert('Gagal mengubah password');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Password baru dan konfirmasi tidak cocok');</script>";
        }
    } else {
        echo "<script>alert('Terjadi kesalahan. Silakan ulangi proses forgot password.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rich Noodles</title>
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
            margin-top: 10px;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.3);
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

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: 600;
            color: var(--gray);
            transition: var(--transition);
        }

        .step.active {
            background: var(--primary);
            color: white;
        }

        .step-line {
            width: 40px;
            height: 2px;
            background: #e9ecef;
            margin: 0 5px;
            align-self: center;
        }

        .step-label {
            font-size: 0.7rem;
            margin-top: 5px;
            text-align: center;
            color: var(--gray);
        }

        .step-container {
            display: flex;
            flex-direction: column;
            align-items: center;
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
            
            .step {
                width: 25px;
                height: 25px;
                font-size: 0.8rem;
            }
            
            .step-line {
                width: 30px;
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
            <p>Reset Password - Atur ulang kata sandi Anda dengan mudah</p>
        </div>
        <div class="form-section">
            <div class="step-indicator">
                <div class="step-container">
                    <div class="step <?php echo (!isset($_SESSION['reset_step']) || $_SESSION['reset_step'] == 1) ? 'active' : ''; ?>">1</div>
                    <div class="step-label">Email</div>
                </div>
                <div class="step-line"></div>
                <div class="step-container">
                    <div class="step <?php echo (isset($_SESSION['reset_step']) && $_SESSION['reset_step'] == 2) ? 'active' : ''; ?>">2</div>
                    <div class="step-label">OTP</div>
                </div>
                <div class="step-line"></div>
                <div class="step-container">
                    <div class="step <?php echo (isset($_SESSION['reset_step']) && $_SESSION['reset_step'] == 3) ? 'active' : ''; ?>">3</div>
                    <div class="step-label">Password</div>
                </div>
            </div>

            <?php if (!isset($_SESSION['reset_step']) || $_SESSION['reset_step'] == 1) { ?>
                <h2>Lupa Password?</h2>
                <p class="subtitle">Masukkan email Anda untuk menerima kode OTP</p>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" required>
                        <div class="form-text">Kami akan mengirim kode OTP ke email ini</div>
                    </div>
                    <button type="submit" name="send_code" class="btn">KIRIM KODE OTP</button>
                </form>
            <?php } elseif ($_SESSION['reset_step'] == 2) { ?>
                <h2>Verifikasi OTP</h2>
                <p class="subtitle">Masukkan kode OTP yang dikirim ke email Anda</p>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="otp">Kode OTP</label>
                        <input type="text" class="form-control" id="otp" name="otp" placeholder="Masukkan 6 digit kode OTP" required maxlength="6">
                        <div class="form-text">Kode OTP berlaku selama 5 menit</div>
                    </div>
                    <button type="submit" name="verify_code" class="btn">VERIFIKASI OTP</button>
                </form>
            <?php } elseif ($_SESSION['reset_step'] == 3) { ?>
                <h2>Reset Password</h2>
                <p class="subtitle">Buat password baru untuk akun Anda</p>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="new_password">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Masukkan password baru" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password baru" required>
                        <div class="form-text">Pastikan password baru dan konfirmasi sama</div>
                    </div>
                    <button type="submit" name="reset_password" class="btn">UBAH PASSWORD</button>
                </form>
            <?php } ?>
            
            <a href="login.php" class="back-link">← Kembali ke halaman login</a>
        </div>
    </div>
</body>
</html>