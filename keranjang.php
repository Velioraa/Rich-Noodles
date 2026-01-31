<?php
session_start();
include "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email']; // Ambil email dari session

// Jika ada input barcode dari scanner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barcode'])) {
    $barcode = trim($_POST['barcode']);

    // Cari produk berdasarkan barcode
    $sql = "SELECT * FROM produk WHERE barcode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $resultProduk = $stmt->get_result();
    $produk = $resultProduk->fetch_assoc();

    if ($produk) {
        $id_produk = $produk['id_produk'];
        $harga = $produk['harga_jual'];

        // Cek apakah produk sudah ada di keranjang
        $sqlCheck = "SELECT * FROM cart WHERE id_produk = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $id_produk);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();

        if ($resCheck->num_rows > 0) {
            // Kalau sudah ada → update quantity
            $sqlUpdate = "UPDATE cart SET quantity = quantity + 1 WHERE id_produk = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("i", $id_produk);
            $stmtUpdate->execute();
        } else {
            // Kalau belum ada → insert
            $sqlInsert = "INSERT INTO cart (id_produk, quantity, harga, waktu_masuk, status) VALUES (?, 1, ?, NOW(), 'pending')";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("id", $id_produk, $harga);
            $stmtInsert->execute();
        }
    }

    // Refresh biar langsung update tampilan keranjang
    header("Location: keranjang.php");
    exit();
}

// Hapus item dari keranjang jika sudah lebih dari 10 menit
$query = "DELETE FROM cart WHERE TIMESTAMPDIFF(MINUTE, waktu_masuk, NOW()) > 10";
mysqli_query($conn, $query);

// Update status checked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['updatechecked'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    $status = "checked";
    
    foreach ($data as $item) {
        $sql = "UPDATE cart SET status = ? WHERE id_produk = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $item['id_produk']);
        $stmt->execute();
    }
}

// Hapus item satuan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hapus_item']) && isset($_POST['cart_id'])) {
        $id = intval($_POST['cart_id']);

        // 🔹 Ambil id_produk & qty sebelum dihapus
        $sqlGet = "SELECT id_produk, quantity FROM cart WHERE id = ?";
        $stmt = $conn->prepare($sqlGet);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $id_produk = $row['id_produk'];
            $qty = $row['quantity'];

            // 🔹 Balikin stok
            $sqlUpdate = "UPDATE produk SET stok = stok + ? WHERE id_produk = ?";
            $stmtUp = $conn->prepare($sqlUpdate);
            $stmtUp->bind_param("ii", $qty, $id_produk);
            $stmtUp->execute();
        }

        // 🔹 Hapus item dari cart
        $query = "DELETE FROM cart WHERE id = ?";
        $stmtDel = $conn->prepare($query);
        $stmtDel->bind_param("i", $id);
        if ($stmtDel->execute()) {
            header('Content-Type: application/json');
            echo json_encode(["status" => "success"]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Gagal menghapus item dari database."]);
        }
        exit;
    }
}

// Hapus semua item
if (isset($_POST['hapus_semua'])) {
    // 🔹 Ambil semua produk & qty dalam cart sebelum hapus
    $sqlGetAll = "SELECT id_produk, quantity FROM cart";
    $resAll = $conn->query($sqlGetAll);
    while ($row = $resAll->fetch_assoc()) {
        $id_produk = $row['id_produk'];
        $qty = $row['quantity'];

        // 🔹 Balikin stok
        $sqlUpdate = "UPDATE produk SET stok = stok + ? WHERE id_produk = ?";
        $stmtUp = $conn->prepare($sqlUpdate);
        $stmtUp->bind_param("ii", $qty, $id_produk);
        $stmtUp->execute();
    }

    // 🔹 Hapus semua item dari cart
    $sql = "DELETE FROM cart";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menghapus"]);
    }
    exit;
}

// Ambil semua produk dalam keranjang
$sql = "SELECT cart.id AS cart_id, cart.id_produk, cart.quantity, 
            produk.nama_produk, produk.harga_jual, produk.gambar, produk.tanggal_expired 
        FROM cart 
        JOIN produk ON cart.id_produk = produk.id_produk";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$totalHarga = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Rich Noodles</title>
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
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            overflow-x: hidden;
            color: #333;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .back-btn {
            position: absolute;
            left: 20px;
            background: #5ca6ff;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 2px 8px rgba(92, 166, 255, 0.3);
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: #4a8feb;
            transform: translateY(-2px);
        }
        .logo img {
            height: 50px;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: white;
            margin: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .cart-header h2 {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
        }

        .cart-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        #hapus-semua-btn {
            display: flex;
            align-items: center;
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 8px;
            font-size: 14px;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.3);
        }
        #hapus-semua-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        #pilih-semua {
            transform: scale(1.2);
            cursor: pointer;
            accent-color: #5ca6ff;
        }
        
        .cart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 0 20px;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            width: 100%;
            margin-bottom: 15px;
            justify-content: space-between;
            transition: all 0.3s ease;
            border-left: 4px solid #5ca6ff;
        }
        .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .cart-details {
            flex-grow: 1;
            text-align: left;
            padding-right: 10px;
        }
        .cart-details h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .cart-details .price {
            font-size: 16px;
            color: #5ca6ff;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .cart-details p {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 3px;
        }
        .timer {
            color: #e74c3c;
            font-weight: 500;
            font-size: 13px;
        }

        .quantity-control {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .top-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .delete-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #ff6b6b;
            transition: all 0.3s ease;
            padding: 5px;
            border-radius: 50%;
        }
        .delete-btn:hover {
            background: rgba(255, 107, 107, 0.1);
            transform: scale(1.1);
        }

        .select-item {
            transform: scale(1.2);
            cursor: pointer;
            accent-color: #5ca6ff;
        }

        .quantity-buttons {
            display: flex;
            align-items: center;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: #f9f9f9;
        }

        .quantity-buttons button {
            background: #f0f8ff;
            border: none;
            width: 35px;
            height: 35px;
            font-size: 18px;
            cursor: pointer;
            color: #5ca6ff;
            transition: all 0.2s ease;
        }
        .quantity-buttons button:hover {
            background: #e1f0ff;
        }

        .quantity-buttons input {
            width: 45px;
            text-align: center;
            border: none;
            font-size: 16px;
            background: white;
            font-weight: 500;
        }
        
        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            width: 100%;
            box-shadow: 0 -2px 15px rgba(0, 0, 0, 0.08);
        }

        #total-pembelian-btn {
            padding: 12px 20px;
            background: #f0f8ff;
            color: #2c3e50;
            border: 1px solid #d0e5ff;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: default;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        #total-pembelian-btn span {
            color: #5ca6ff;
            font-weight: 600;
        }
        
        #bayar-btn {
            padding: 12px 30px;
            background: #5ca6ff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(92, 166, 255, 0.3);
        }
        #bayar-btn:hover {
            background: #4a8feb;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(92, 166, 255, 0.4);
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        .empty-cart i {
            font-size: 60px;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        .empty-cart p {
            font-size: 18px;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }
            .cart-item img {
                width: 100%;
                height: 180px;
                margin-right: 0;
                margin-bottom: 15px;
            }
            .quantity-control {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                margin-top: 15px;
            }
            .cart-summary {
                flex-direction: column;
                gap: 15px;
            }
            #total-pembelian-btn, #bayar-btn {
                width: 100%;
            }
            .cart-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="back-btn" onclick="window.location.href='produk.php';">
            <i class="fas fa-arrow-left"></i>
        </div>
        <div class="logo">
            <img src="asset/Richa Mart.png" alt="Rich Noodles">
        </div>
    </div>

    <div class="cart-header">
        <h2><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h2>
        <div class="cart-actions">
            <button id="hapus-semua-btn">
                <i class="fas fa-trash-alt"></i> Hapus Semua
            </button>
            <label>
                <input type="checkbox" id="pilih-semua"> Pilih Semua
            </label>
        </div>
    </div>

    <form method="post" id="barcodeForm">
        <input type="text" name="barcode" id="barcodeInput" autofocus style="opacity:0; position:absolute;">
    </form>

    <div class="cart-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="cart-item">
                    <img src="asset/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                    <div class="cart-details">
                        <h3><?= $row['nama_produk'] ?></h3>
                        <p class="price">Rp. <?= number_format($row['harga_jual'], 0, ',', '.') ?> / pcs</p>
                        <p>Expired: <?= $row['tanggal_expired'] ?></p>
                        <p class="timer">TIMER: 10 MNT</p>
                    </div>
                    <div class="quantity-control">
                        <div class="top-controls">
                            <button class="delete-btn" data-cart-id="<?= $row['cart_id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                            <label>
                                <input type="checkbox" class="select-item" data-id-produk="<?= $row['id_produk'] ?>" data-cart-id="<?= $row['cart_id'] ?>"> Pilih
                            </label>
                        </div>
                        <div class="quantity-buttons">
                            <button class="minus-btn" data-cart-id="<?= $row['cart_id'] ?>">-</button>
                            <input type="number" value="<?= $row['quantity'] ?>" min="1" class="quantity" data-cart-id="<?= $row['cart_id'] ?>">
                            <button class="plus-btn" data-cart-id="<?= $row['cart_id'] ?>">+</button>
                        </div>
                    </div>
                </div>
                <?php $totalHarga += $row['harga_jual'] * $row['quantity']; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Keranjang belanja Anda kosong</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="cart-summary">
        <button id="total-pembelian-btn">Total Pembelian: <span id="total-pembelian">0</span></button>
        <p>Total Harga: Rp. <span id="total-harga"><?= number_format($totalHarga, 0, ',', '.') ?></span></p>
        <button id="bayar-btn" onclick="updateChecked()">Check Out</button>
    </div>

    <script>
        // Selalu fokus ke input barcode
        document.addEventListener("DOMContentLoaded", function () {
            const input = document.getElementById("barcodeInput");
            input.focus();
            document.addEventListener("click", () => input.focus());
        });

        function updateChecked() {
            const checkboxes = document.querySelectorAll(".select-item:checked");

            let selected_items = [];

            checkboxes.forEach((checkbox) => {
                const id_produk = checkbox.getAttribute("data-id-produk");

                selected_items.push({
                    id_produk: id_produk
                })
            });

            console.log(selected_items);
            fetch("keranjang.php?updatechecked", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(selected_items)
            })
            .then(response => response.text())
            .then(data => {
                console.log("Respon dari server:", data);
                window.location.href="transaksi/transaksi.php";
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            updateTotalPembelian();

            // Event listener untuk tombol hapus item
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const cartId = this.dataset.cartId;

                    if (!cartId) {
                        alert("ID produk tidak ditemukan.");
                        return;
                    }

                    if (confirm("Apakah Anda yakin ingin menghapus produk ini dari keranjang?")) {
                        fetch("keranjang.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `hapus_item=true&cart_id=${cartId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Response dari PHP:", data);
                            if (data.status === "success") {
                                this.closest(".cart-item").remove();
                                updateTotalPembelian();
                                
                                // Jika tidak ada item lagi, tampilkan pesan keranjang kosong
                                if (document.querySelectorAll(".cart-item").length === 0) {
                                    document.querySelector(".cart-container").innerHTML = `
                                        <div class="empty-cart">
                                            <i class="fas fa-shopping-cart"></i>
                                            <p>Keranjang belanja Anda kosong</p>
                                        </div>
                                    `;
                                }
                            } else {
                                alert("Gagal menghapus produk.");
                            }
                        })
                    }
                });
            });

            // Event listener tombol hapus semua
            document.getElementById("hapus-semua-btn").addEventListener("click", function () {
                if (confirm("Apakah Anda yakin ingin menghapus semua produk dari keranjang?")) {
                    fetch("keranjang.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "hapus_semua=true"
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            document.querySelectorAll(".cart-item").forEach(item => item.remove());
                            document.getElementById("total-harga").textContent = "0";
                            document.getElementById("total-pembelian").textContent = "0";
                            
                            // Tampilkan pesan keranjang kosong
                            document.querySelector(".cart-container").innerHTML = `
                                <div class="empty-cart">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>Keranjang belanja Anda kosong</p>
                                </div>
                            `;
                        } else {
                            alert("Gagal menghapus produk.");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("Terjadi kesalahan, coba lagi.");
                    });
                }
            });

            // Event listener tombol plus/minus
            document.querySelectorAll(".plus-btn, .minus-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const cartId = this.getAttribute("data-cart-id");
                    const quantityInput = document.querySelector(`.quantity[data-cart-id="${cartId}"]`);
                    let quantity = parseInt(quantityInput.value);

                    if (this.classList.contains("plus-btn")) {
                        quantity++;
                    } else if (this.classList.contains("minus-btn") && quantity > 1) {
                        quantity--;
                    }

                    // Update input tampilan sementara
                    quantityInput.value = quantity;

                    // Kirim update ke server pakai fetch/AJAX
                    fetch("update_quantity.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `cart_id=${cartId}&quantity=${quantity}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status !== "success") {
                            alert("Gagal update ke database: " + data.message);
                        } else {
                            // Optional: Refresh halaman supaya quantity dari DB ditampilkan
                            location.reload();
                        }
                    });
                });
            });

            // Fungsi untuk update total pembelian & harga
            function updateTotalPembelian() {
                let totalPembelian = 0;
                let totalHarga = 0;

                document.querySelectorAll(".cart-item").forEach(item => {
                    const checkbox = item.querySelector(".select-item");
                    if (checkbox.checked) {
                        const quantity = parseInt(item.querySelector(".quantity").value);
                        const harga = parseInt(item.querySelector(".price").textContent.replace("Rp. ", "").replace(/\./g, ""));
                        
                        totalPembelian += quantity;
                        totalHarga += harga * quantity;
                    }
                });

                document.getElementById("total-pembelian").textContent = totalPembelian;
                document.getElementById("total-harga").textContent = totalHarga.toLocaleString();
            }

            // Pilih semua checkbox
            const pilihSemuaCheckbox = document.getElementById("pilih-semua");
            const itemCheckboxes = document.querySelectorAll(".select-item");

            // Saat checkbox "Pilih Semua" di-klik
            pilihSemuaCheckbox.addEventListener("change", function () {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = pilihSemuaCheckbox.checked;
                });
                updateTotalPembelian();
            });

            // Saat ada perubahan pada salah satu checkbox item
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function () {
                    // Jika ada 1 checkbox yang tidak dicentang, "Pilih Semua" harus nonaktif
                    const semuaTercentang = [...itemCheckboxes].every(cb => cb.checked);
                    pilihSemuaCheckbox.checked = semuaTercentang;
                    updateTotalPembelian();
                });
            });

            // Update total saat quantity berubah
            document.querySelectorAll(".quantity").forEach(input => {
                input.addEventListener("change", updateTotalPembelian);
            });

            // Set total harga ke Rp. 0 saat pertama kali
            updateTotalPembelian();
        });
    </script>
</body>
</html>