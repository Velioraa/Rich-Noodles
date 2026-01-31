<?php
include '../koneksi.php'; // Pastikan koneksi ke database

$data = null; // Inisialisasi variabel agar tidak undefined

// Ambil data member berdasarkan ID
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']); // Pastikan ID adalah angka

    $query = "SELECT * FROM member WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "<script>alert('Data member tidak ditemukan!'); window.location.href='../member.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ID member tidak valid!'); window.location.href='../member.php';</script>";
    exit();
}

// Proses update member jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $no_telp = trim($_POST['no_telp']);
    $status = $_POST['status']; // Ambil status dari form

    // Cek apakah member ini statusnya "Tidak Aktif"
    $cekQuery = "SELECT username, status FROM member WHERE id = ?";
    $stmt = $conn->prepare($cekQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cekResult = $stmt->get_result();
    $memberData = $cekResult->fetch_assoc();

    if ($memberData) {
        $db_username = $memberData['username'];
        $db_status = $memberData['status'];

        if ($db_status == "Tidak Aktif") {
            echo "<script>alert('Member dengan username \"$db_username\" tidak bisa di-update karena statusnya Tidak Aktif!'); window.location.href='../member.php';</script>";
            exit();
        }
    }

    // Cek apakah no_telp sudah ada di database, kecuali yang sedang diupdate
    $checkQuery = "SELECT * FROM member WHERE no_telp = ? AND id != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $no_telp, $id);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    // Jika no_telp sudah ada di database, tampilkan pesan error
    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Nomor telepon sudah digunakan oleh member lain!');</script>";
    } else {
        // Jika no_telp unik, lakukan update
        $updateQuery = "UPDATE member SET username = ?, no_telp = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $username, $no_telp, $status, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Data member berhasil diperbarui!'); window.location.href='../member.php';</script>";
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
    <title>Update Member - Rich Noodles</title>
    <link rel="website icon" type="png" href="../asset/Richa Mart.png">
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

        input, select {
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
            box-sizing: border-box;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="back-btn" onclick="window.location.href='../member.php';">&#8592;</div>

<div class="form-container">
    <h2>Update Member</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
        <input type="text" name="no_telp" value="<?= htmlspecialchars($data['no_telp']) ?>" required>

        <!-- Dropdown untuk status -->
        <select name="status" required>
            <option value="aktif" <?= $data['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="tidak aktif" <?= $data['status'] == 'tidak aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
        </select>

        <button type="submit" class="btn">Update</button>
    </form>
</div>

</body>
</html>
