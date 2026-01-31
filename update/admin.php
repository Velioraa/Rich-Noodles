<?php
include '../koneksi.php'; // Pastikan koneksi ke database

$data = null; // Inisialisasi variabel

// Ambil data admin berdasarkan ID
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']); // Pastikan ID adalah angka

    $query = "SELECT id, username, last_login FROM admin WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "<script>alert('Data admin tidak ditemukan!'); window.location.href='../admin.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ID admin tidak valid!'); window.location.href='../admin.php';</script>";
    exit();
}

// Proses update admin jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = trim($_POST['username']);

    // Cek apakah username sudah ada di database, kecuali admin yang sedang diupdate
    $checkQuery = "SELECT id FROM admin WHERE username = ? AND id != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        // Lakukan update
        $updateQuery = "UPDATE admin SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $username, $id);

        if ($stmt->execute()) {
            echo "<script>alert(' Berhasil diperbarui!'); window.location.href='../admin.php';</script>";
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
    <title>Update Admin - Rich Noodles</title>
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

        input[type="text"] {
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

<div class="back-btn" onclick="window.location.href='../admin.php';">&#8592;</div>

<div class="form-container">
    <h2>Update Username Admin</h2>
    <form action="" method="POST">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
        <button type="submit" class="btn">Update</button>
    </form>
</div>

</body>
</html>
