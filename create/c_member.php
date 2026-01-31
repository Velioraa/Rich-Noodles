<?php
session_start();
include '../koneksi.php'; // Pastikan koneksi ke database benar

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['no_telp'])) {
        $username = trim($_POST['username']);
        $no_telp = trim($_POST['no_telp']);
        $status   = "aktif"; // Status otomatis jadi aktif

        // Cek apakah no_telp sudah ada di database
        $cek_no_telp = "SELECT no_telp FROM member WHERE no_telp = ?";
        $stmt_cek_no_telp = $conn->prepare($cek_no_telp);
        $stmt_cek_no_telp->bind_param("s", $no_telp);
        $stmt_cek_no_telp->execute();
        $stmt_cek_no_telp->store_result();

        if ($stmt_cek_no_telp->num_rows > 0) {
            echo "<script>alert('Member dengan no telepon tersebut sudah ada!'); window.location='../member.php';</script>";
            exit();
        }

        // Insert member baru dengan status otomatis "aktif"
        $sql = "INSERT INTO member (username, no_telp, status, last_transaction) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $no_telp, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Member berhasil ditambahkan!'); window.location='../member.php';</script>";
        } else {
            die("<script>alert('Gagal menambahkan member! Error: " . $stmt->error . "');</script>");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Member - Rich Noodles</title>
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

        .status-info {
            background: var(--primary-light);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .status-info p {
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.9rem;
            margin: 0;
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
            <p>Tambah member baru untuk program loyalitas</p>
        </div>
        <div class="form-section">
            <div class="form-header">
                <h2>Tambah Member Baru</h2>
                <p>Isi data member untuk bergabung dalam program loyalitas</p>
            </div>

            <div class="status-info">
                <p>📱 Member akan otomatis aktif dan dapat mengumpulkan poin</p>
            </div>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Nama Member</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan nama member" required>
                    <div class="form-text">Nama yang akan digunakan untuk identifikasi member</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="no_telp">Nomor Telepon</label>
                    <input type="text" class="form-control" id="no_telp" name="no_telp" placeholder="Masukkan nomor telepon" required>
                    <div class="form-text">Nomor telepon akan digunakan sebagai ID member</div>
                </div>

                <button type="submit" class="btn">TAMBAH MEMBER</button>
            </form>
            
            <a href="../member.php" class="back-link">← Kembali ke halaman member</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Fokus ke input username saat halaman dimuat
            document.getElementById('username').focus();
            
            // Validasi form sebelum submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const usernameInput = document.getElementById('username');
                const noTelpInput = document.getElementById('no_telp');
                
                if (usernameInput.value.trim() === '') {
                    e.preventDefault();
                    alert('Mohon isi nama member!');
                    usernameInput.focus();
                    return;
                }
                
                if (noTelpInput.value.trim() === '') {
                    e.preventDefault();
                    alert('Mohon isi nomor telepon!');
                    noTelpInput.focus();
                    return;
                }
                
                // Validasi format nomor telepon (opsional)
                const phoneRegex = /^[0-9+\-\s()]{10,}$/;
                if (!phoneRegex.test(noTelpInput.value.trim())) {
                    e.preventDefault();
                    alert('Mohon masukkan nomor telepon yang valid!');
                    noTelpInput.focus();
                    return;
                }
            });

            // Auto format nomor telepon (opsional)
            const noTelpInput = document.getElementById('no_telp');
            noTelpInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = value.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
                }
                e.target.value = value;
            });
        });
    </script>
</body>
</html>