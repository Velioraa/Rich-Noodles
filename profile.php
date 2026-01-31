<?php
session_start();
include "koneksi.php";

// Pastikan pengguna sudah login
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}

// Query untuk mengambil data admin berdasarkan email
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Data admin tidak ditemukan!'); window.location.href='logout.php';</script>";
    exit();
}

$account = $result->fetch_assoc();
$id_admin = $account['id'];

// Variabel untuk pesan sukses/error
$message = '';
$message_type = '';

// Proses update profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle file upload
    $gambar = $account['gambar']; // Default ke gambar lama
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "asset/profile/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $file_name = "profile_" . $id_admin . "_" . time() . "." . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $file_path)) {
                // Hapus gambar lama jika ada
                if (!empty($account['gambar']) && file_exists($account['gambar'])) {
                    unlink($account['gambar']);
                }
                $gambar = $file_path;
            } else {
                $message = "Gagal mengupload gambar!";
                $message_type = "error";
            }
        } else {
            $message = "Format file tidak didukung! Hanya JPG, JPEG, PNG, GIF yang diizinkan.";
            $message_type = "error";
        }
    }
    
    // Validasi input
    if (empty($username) || empty($email)) {
        $message = "Username dan email tidak boleh kosong!";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
        $message_type = "error";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $message = "Password dan konfirmasi password tidak cocok!";
        $message_type = "error";
    } else {
        try {
            // Cek apakah email sudah digunakan oleh admin lain
            $check_email_sql = "SELECT id FROM admin WHERE email = ? AND id != ?";
            $check_stmt = $conn->prepare($check_email_sql);
            $check_stmt->bind_param("si", $email, $id_admin);
            $check_stmt->execute();
            $email_result = $check_stmt->get_result();
            
            if ($email_result->num_rows > 0) {
                $message = "Email sudah digunakan oleh admin lain!";
                $message_type = "error";
            } else {
                // Update data admin
                if (!empty($password)) {
                    // Jika password diubah, hash password baru
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE admin SET username = ?, email = ?, password = ?, gambar = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssssi", $username, $email, $hashed_password, $gambar, $id_admin);
                } else {
                    // Jika password tidak diubah
                    $update_sql = "UPDATE admin SET username = ?, email = ?, gambar = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("sssi", $username, $email, $gambar, $id_admin);
                }
                
                if ($update_stmt->execute()) {
                    $message = "Profile berhasil diupdate!";
                    $message_type = "success";
                    
                    // Update session email jika email diubah
                    if ($_SESSION['email'] !== $email) {
                        $_SESSION['email'] = $email;
                    }
                    
                    // Refresh data admin
                    $sql = "SELECT * FROM admin WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $id_admin);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $account = $result->fetch_assoc();
                } else {
                    $message = "Gagal mengupdate profile: " . $conn->error;
                    $message_type = "error";
                }
            }
        } catch (Exception $e) {
            $message = "Terjadi kesalahan: " . $e->getMessage();
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Rich Noodles</title>
    <link rel="website icon" type="image/png" href="asset/Richa Mart.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e6f2ff 0%, #f0f8ff 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .header {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .back-btn {
            background: #5ca6ff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(92, 166, 255, 0.3);
            text-decoration: none;
        }
        .back-btn:hover {
            background: #4a8feb;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(92, 166, 255, 0.4);
        }
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(92, 166, 255, 0.2);
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 1px solid #e6f2ff;
            margin-top: 40px;
        }
        .logo {
            height: 50px;
            margin-bottom: 20px;
        }
        .card-title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .profile-image-container {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e6f2ff;
            box-shadow: 0 4px 15px rgba(92, 166, 255, 0.3);
        }
        .image-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #5ca6ff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .image-upload-btn:hover {
            background: #4a8feb;
            transform: scale(1.1);
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d0e5ff;
            border-radius: 10px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #5ca6ff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(92, 166, 255, 0.2);
        }
        .password-note {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
            font-style: italic;
        }
        .update-btn {
            background: linear-gradient(135deg, #5ca6ff 0%, #4a8feb 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(92, 166, 255, 0.3);
        }
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(92, 166, 255, 0.4);
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
        .footer-content p {
            color: #7f8c8d;
            font-size: 14px;
        }
        .message {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            font-size: 14px;
        }
        .message.success {
            background: #e1f7e1;
            color: #2d5016;
            border: 1px solid #b8e0b8;
        }
        .message.error {
            background: #ffe1e1;
            color: #8b0000;
            border: 1px solid #ffb8b8;
        }
        .file-input {
            display: none;
        }
        .login-info {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 15px;
            font-style: italic;
        }
        @media (max-width: 768px) {
            .header {
                position: relative;
                top: 0;
                left: 0;
                align-self: flex-start;
                margin-bottom: 20px;
            }
            .profile-card {
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Tombol Back -->
    <div class="header">
        <a href="admin.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            
        </a>
    </div>

    <div class="profile-card">
        <img src="asset/Richa Mart.png" alt="Rich Noodles" class="logo">
        <h1 class="card-title">Profile Pengguna</h1>
        
        <!-- Pesan Sukses/Error -->
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Foto Profile -->
            <div class="profile-image-container">
                <img src="<?= !empty($account['gambar']) ? $account['gambar'] : 'asset/default-avatar.png' ?>" 
                     alt="Profile Picture" 
                     class="profile-image"
                     id="profile-image-preview">
                <button type="button" class="image-upload-btn" onclick="document.getElementById('gambar').click()">
                    <i class="fas fa-camera"></i>
                </button>
                <input type="file" name="gambar" id="gambar" class="file-input" accept="image/*" onchange="previewImage(this)">
            </div>

            <!-- Form Fields -->
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-input" name="username" value="<?= htmlspecialchars($account['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" name="email" value="<?= htmlspecialchars($account['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" class="form-input" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                <div class="password-note">Biarkan kosong jika tidak ingin mengubah password</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-input" name="confirm_password" placeholder="Kosongkan jika tidak ingin mengubah">
                <div class="password-note">Konfirmasi password baru</div>
            </div>

            <div class="login-info">
                <i class="fas fa-clock"></i> 
                Login terakhir: <?= date('d M Y H:i', strtotime($account['last_login'])) ?>
            </div>
            
            <button type="submit" name="update_profile" class="update-btn">
                <i class="fas fa-save"></i> UPDATE PROFILE
            </button>
        </form>
    </div>

    <div class="footer">
        <div class="footer-content">
            <p>© 2025 Rich Noodles - Solusi Terbaik untuk Bisnis Mie Instan Anda</p>
        </div>
    </div>

    <script>
        // Preview image sebelum upload
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-image-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
            
            return confirm('Apakah Anda yakin ingin mengupdate profile?');
        });

        // Auto-hide message setelah 5 detik
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => message.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>