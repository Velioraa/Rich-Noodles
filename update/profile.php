<?php
session_start();
include "../koneksi.php";

// Ambil ID dari URL atau Formulir
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

// Cek apakah ID valid
if ($id <= 0) {
    echo "<script>alert('ID admin tidak valid!'); window.location.href='profile.php';</script>";
    exit();
}

// Ambil data admin berdasarkan ID
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare statement gagal: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Data admin tidak ditemukan!'); window.location.href='profile.php';</script>";
    exit();
}

$data = $result->fetch_assoc();

// Proses update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Cek apakah email sudah digunakan oleh admin lain
    $checkEmailQuery = "SELECT id FROM admin WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $checkEmailResult = $stmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        echo "<script>alert('Admin sudah ada!');</script>";
    } else {
        // Cek apakah email sudah digunakan oleh admin lain
$checkEmailQuery = "SELECT id FROM admin WHERE email = ? AND id != ?";
$stmtCheckEmail = $conn->prepare($checkEmailQuery);
$stmtCheckEmail->bind_param("si", $email, $id);
$stmtCheckEmail->execute();
$checkEmailResult = $stmtCheckEmail->get_result();

if ($checkEmailResult->num_rows > 0) {
    echo "<script>alert('Admin sudah ada dengan email tersebut!'); window.history.back();</script>";
    exit();
}

// Cek apakah username sudah digunakan oleh admin lain
$checkUsernameQuery = "SELECT id FROM admin WHERE username = ? AND id != ?";
$stmtCheckUsername = $conn->prepare($checkUsernameQuery);
$stmtCheckUsername->bind_param("si", $username, $id);
$stmtCheckUsername->execute();
$checkUsernameResult = $stmtCheckUsername->get_result();

if ($checkUsernameResult->num_rows > 0) {
    echo "<script>alert('Admin sudah ada dengan username tersebut!'); window.history.back();</script>";
    exit();
}
            $gambar_baru = $data['gambar'];

            // Cek apakah ada file baru diunggah
            if (!empty($_FILES['pfp']['name'])) {
                $targetDir = "../asset/"; // Folder penyimpanan gambar
                $fileName = time() . "_" . basename($_FILES["pfp"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                // Validasi tipe file (hanya jpg, png, jpeg)
                $allowTypes = array('jpg', 'png', 'jpeg');
                if (in_array($fileType, $allowTypes)) {
                    if (move_uploaded_file($_FILES["pfp"]["tmp_name"], $targetFilePath)) {
                        $gambar_baru = "asset/" . $fileName;

                        // Hapus gambar lama jika bukan default
                        if ($data['gambar'] && file_exists("../" . $data['gambar']) && $data['gambar'] != "asset/default.png") {
                            unlink("../" . $data['gambar']);
                        }
                    } else {
                        echo "<script>alert('Gagal mengunggah gambar!');</script>";
                        exit();
                    }
                } else {
                    echo "<script>alert('Format gambar tidak valid!');</script>";
                    exit();
                }
            }

            // Update data di database
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE admin SET username = ?, email = ?, password = ?, gambar = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssssi", $username, $email, $hashed_password, $gambar_baru, $id);
            } else {
                $updateQuery = "UPDATE admin SET username = ?, email = ?, gambar = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("sssi", $username, $email, $gambar_baru, $id);
            }

            if ($stmt->execute()) {
                // Perbarui session agar perubahan langsung terlihat
                $_SESSION['email'] = $email;
                $_SESSION['gambar'] = $gambar_baru;

                echo "<script>alert('Data admin berhasil diperbarui!'); window.location.href='../profile.php?id=$id';</script>";
            } else {
                echo "<script>alert('Gagal memperbarui data!');</script>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Rich Noodles</title>
    <link rel="website icon" type="png" href="asset/Richa Mart.png">
    <style>
        body {
            background-image: url('../asset/bg.jpeg');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .back-btn {
            margin: 20px;
            background: white;
            color: #5ca6ff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 500px;
            margin: 50px auto;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #000;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .btn {
            padding: 12px;
            font-size: 16px;
            color: white;
            background-color: #4C7FED;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="back-btn" onclick="window.location.href='../profile.php?id=<?= $id ?>';">&#8592;</div>

<div class="form-container">
    <h2>Update Profile</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
        <input type="password" name="password" placeholder="Masukkan password baru (jika ingin mengganti)">
        <input type="file" name="pfp">
        <button type="submit" class="btn">Update</button>
    </form>
</div>

</body>
</html>
