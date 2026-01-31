<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Ambil data admin
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();
$id_admin = $account['id'];

// Default
$point_member = 0;
$id_member = null;

// Ambil isi keranjang
$cartItems = [];
$totalHarga = 0;
$totalKeuntungan = 0;
$status = "checked";
$queryCart = "
    SELECT cart.id_produk, produk.nama_produk, cart.quantity, produk.harga_jual, produk.modal, cart.waktu_masuk 
    FROM cart 
    JOIN produk ON cart.id_produk = produk.id_produk
    WHERE cart.status = ?
";
$stmt = $conn->prepare($queryCart);
$stmt->bind_param("s", $status);
$stmt->execute();
$cartResult = $stmt->get_result();

while ($row = $cartResult->fetch_assoc()) {
    $cartItems[] = $row;
    $totalHarga += $row['quantity'] * $row['harga_jual'];
    $keuntungan = ($row['harga_jual'] - $row['modal']) * $row['quantity'];
    $totalKeuntungan += $keuntungan;
}

// Proses transaksi saat form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_telp = $_POST['no_telp'];
    $total_bayar = $_POST['uang_bayar'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $kembalian = $_POST['kembalian'] ?? 0;
    $total_harga = $_POST['total_harga'] ?? 0;
    $point_dipakai = $_POST['point_dipakai'];
    $point_didapat = floor($total_bayar / 10000);

    // Validasi metode pembayaran
    $cekMetode = $conn->prepare("SELECT id_metode_pembayaran FROM metode_pembayaran WHERE id_metode_pembayaran = ?");
    $cekMetode->bind_param("i", $metode_pembayaran);
    $cekMetode->execute();
    $resultMetode = $cekMetode->get_result();

    if ($resultMetode->num_rows == 0) {
        echo "<script>alert('Metode pembayaran tidak valid!'); window.location.href='transaksi.php';</script>";
        exit;
    }

    // Simpan transaksi utama
    $insertTransaksi = $conn->prepare("
        INSERT INTO transaksi (fid_member, total_harga, total_bayar, fid_metode_pembayaran, total_kembalian, total_keuntungan, fid_admin, tanggal_transaksi)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $insertTransaksi->bind_param("iiisiii", $id_member, $total_harga, $total_bayar, $metode_pembayaran, $kembalian, $totalKeuntungan, $id_admin);

    if ($insertTransaksi->execute()) {
        $id_transaksi = $conn->insert_id;

        // Simpan detail transaksi
        foreach ($cartItems as $item) {
            $fid_produk = $item['id_produk'];
            $total_produk = $item['quantity'];
            $subtotal = $item['harga_jual'] * $total_produk;

            $insertDetail = $conn->prepare("
                INSERT INTO detail_transaksi (fid_transaksi, fid_produk, total_produk, subtotal)
                VALUES (?, ?, ?, ?)
            ");
            $insertDetail->bind_param("iiid", $id_transaksi, $fid_produk, $total_produk, $subtotal);
            $insertDetail->execute();
        }

        // Update poin & status member jika ada
        if ($no_telp) {
            $updateMember = $conn->prepare("
                UPDATE member SET status = 'aktif', last_transaction = NOW(), point = point - ? + ? WHERE no_telp = ?
            ");
            $updateMember->bind_param("iis", $point_dipakai, $point_didapat, $no_telp);
            $updateMember->execute();
        }

        // Kosongkan keranjang
        $hapusCart = $conn->prepare("DELETE FROM cart WHERE status = ?");
        $hapusCart->bind_param("s", $status);
        $hapusCart->execute();

        // Simpan ke session untuk invoice
        $_SESSION['id_transaksi'] = $id_transaksi;
        $_SESSION['total_bayar'] = $total_bayar;
        $_SESSION['tanggal_transaksi'] = date("Y-m-d");

        // ✅ Tambahan: simpan detail struk
        $_SESSION['struk'] = [
            "tanggal"  => date("Y-m-d H:i:s"),
            "kasir"    => $account['nama'] ?? $_SESSION['email'],
            "items"    => $cartItems,
            "total"    => $totalHarga,
            "total_bayar" => $total_bayar,
            "total_kembalian" => $kembalian,
            "total_keuntungan" => $totalKeuntungan,
            "point_dipakai" => $point_dipakai,
            "point_didapat" => $point_didapat
        ];

        echo "<script>alert('Transaksi berhasil!'); window.location.href='invoice.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan transaksi!'); window.location.href='transaksi.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Rich Noodles</title>
    <link rel="website icon" type="image/png" href="../asset/Richa Mart.png">
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
        
        .main-container {
            flex: 1;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        .card-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
        
        .page-title {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .transaction-table th {
            background: #5ca6ff;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .transaction-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eaeaea;
        }
        
        .transaction-table tr:last-child td {
            border-bottom: none;
        }
        
        .transaction-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .transaction-table tr:hover {
            background-color: #f0f8ff;
        }
        
        .transaction-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }
        
        .input-group {
            width: 48%;
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            text-align: left;
        }
        
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d0e5ff;
            border-radius: 8px;
            font-size: 16px;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus, .input-group select:focus {
            border-color: #5ca6ff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(92, 166, 255, 0.2);
        }
        
        .input-group input[readonly] {
            background-color: #f5f9ff;
            color: #5a5a5a;
        }
        
        .summary-section {
            width: 100%;
            margin-top: 20px;
            padding: 20px;
            background: #f0f8ff;
            border-radius: 10px;
            text-align: right;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .summary-total {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px dashed #d0e5ff;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .print-struk {
            background: #5ca6ff;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(92, 166, 255, 0.3);
        }
        
        .print-struk:hover {
            background: #4a8feb;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(92, 166, 255, 0.4);
        }
        
        .footer {
            padding: 20px;
            background: white;
            color: #7f8c8d;
            font-size: 14px;
            margin-top: auto;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .member-info {
            background: #e1f0ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
            display: none;
        }
        
        .member-info.active {
            display: block;
        }
        
        .member-info p {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .member-points {
            color: #5ca6ff;
            font-weight: 600;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .input-group {
                width: 100%;
            }
            
            .transaction-form {
                flex-direction: column;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .print-struk {
                width: 100%;
                justify-content: center;
            }
            
            .transaction-table {
                font-size: 14px;
            }
            
            .transaction-table th, .transaction-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="back-btn" onclick="window.location.href='../keranjang.php';">
            <i class="fas fa-arrow-left"></i>
        </div>
        <div class="logo">
            <img src="../asset/Richa Mart.png" alt="Rich Noodles">
        </div>
    </div>
    
    <div class="main-container">
        <h1 class="page-title">
            <i class="fas fa-cash-register"></i> Transaksi
        </h1>
        
        <div class="card-container">
            <h2 style="text-align: left; margin-bottom: 20px; color: #2c3e50;">
                <i class="fas fa-shopping-basket"></i> Daftar Produk
            </h2>
            
            <table class="transaction-table">
                <tr>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Tanggal</th>
                </tr>
                <?php foreach ($cartItems as $item) : ?>
                <tr data-id-produk="<?= htmlspecialchars($item['id_produk']) ?>">
                    <td data-nama-produk data-id-produk="<?= htmlspecialchars($item['id_produk']) ?>">
                        <?= htmlspecialchars($item['nama_produk']) ?>
                    </td>
                    <td data-quantity data-id-produk="<?= htmlspecialchars($item['id_produk']) ?>">
                        <?= htmlspecialchars($item['quantity']) ?>
                    </td>
                    <td data-harga-jual data-id-produk="<?= htmlspecialchars($item['id_produk']) ?>">
                        Rp. <?= number_format($item['harga_jual'] * $item['quantity'], 0, ',', '.') ?>
                    </td>
                    <td data-waktu-masuk data-id-produk="<?= htmlspecialchars($item['id_produk']) ?>">
                        <?= htmlspecialchars($item['waktu_masuk']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <form method="POST" action="transaksi.php">
                <div class="transaction-form">
                    <div class="input-group">
                        <label for="metode_pembayaran">
                            <i class="fas fa-credit-card"></i> Metode Pembayaran:
                        </label>
                        <select name="metode_pembayaran" id="metode_pembayaran">
                            <option value="2">Tunai</option>
                            <!-- Tambahkan opsi lain sesuai data yang ada -->
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="no-telp">
                            <i class="fas fa-user"></i> NO. TELP MEMBER
                        </label>
                        <input type="text" id="no-telp" name="no_telp" value="<?= $_SESSION['no_telp'] ?? '' ?>" placeholder="Masukkan nomor telepon member">
                    </div>

                    <div class="input-group">
                        <label for="total-harga">
                            <i class="fas fa-tag"></i> TOTAL HARGA
                        </label>
                        <input type="text" id="total-harga" value="Rp. <?= number_format($totalHarga, 0, ',', '.') ?>" readonly>
                    </div>

                    <div class="input-group">
                        <label for="potongan-harga">
                            <i class="fas fa-percentage"></i> POTONGAN HARGA (Poin)
                        </label>
                        <input type="number" id="potongan-harga" name="point_dipakai" placeholder="0" min="0">
                    </div>

                    <div class="input-group">
                        <label for="uang-bayar">
                            <i class="fas fa-money-bill-wave"></i> UANG BAYAR
                        </label>
                        <input type="number" id="uang-bayar" name="uang_bayar" placeholder="Masukkan jumlah uang" required>
                    </div>

                    <div class="input-group">
                        <label for="kembalian">
                            <i class="fas fa-coins"></i> KEMBALIAN
                        </label>
                        <input type="number" id="kembalian" value="" name="kembalian" readonly>
                    </div>
                </div>
                
                <div class="summary-section">
                    <div class="summary-item">
                        <span>Total Harga:</span>
                        <span>Rp. <?= number_format($totalHarga, 0, ',', '.') ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Potongan:</span>
                        <span id="display-potongan">Rp. 0</span>
                    </div>
                    <div class="summary-total">
                        <span>Total Bayar:</span>
                        <span id="display-total-bayar">Rp. <?= number_format($totalHarga, 0, ',', '.') ?></span>
                    </div>
                </div>

                <input type="hidden" id="total_harga" name="total_harga" value="<?= $totalHarga ?>" readonly>

                <div class="buttons">
                    <button type="submit" onclick="sendToInvoice()" class="print-struk">
                        <i class="fas fa-receipt"></i> Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="footer">
        © 2025 RICH NOODLES
    </div>

    <script>
        function sendToInvoice() {
            const trs = document.querySelectorAll("tr[data-id-produk]");
            const total = document.querySelector("input[name='total_harga']");
            const diskon = document.querySelector("input[name='point_dipakai']");
            let invoice_items = {
                total: total.value,
                diskon: diskon.value,
                data: []
            };

            trs.forEach((row, index) => {
                const nama_produk = row.querySelector("td[data-nama-produk]").innerText;
                const quantity = row.querySelector("td[data-quantity]").innerText;
                const harga_jual = row.querySelector("td[data-harga-jual]").innerText;

                invoice_items.data.push({
                    nama_produk: nama_produk,
                    quantity: quantity,
                    harga_jual: harga_jual
                })
            });

            console.log(invoice_items);
            fetch("invoice.php?createinvoice", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(invoice_items)
            })
            .then(response => response.text())
            .then(data => {
                console.log("Respon dari server:", data);
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }
    </script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let totalHargaKeranjang = <?= $totalHarga ?>;
            const pointMember = <?= $point_member ?>;
            
            // Format Rupiah
            function formatRupiah(angka) {
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            
            // Hitung kembalian dan update tampilan
            function hitungKembalian() {
                let totalHarga = totalHargaKeranjang;
                let potongan = parseInt(document.getElementById("potongan-harga").value) || 0;
                let uangBayar = parseInt(document.getElementById("uang-bayar").value) || 0;
                
                // Hitung potongan dalam rupiah (1 poin = 1000)
                let potonganRupiah = potongan * 1000;
                let totalSetelahDiskon = totalHarga - potonganRupiah;
                if (totalSetelahDiskon < 0) totalSetelahDiskon = 0;
                
                // Update tampilan
                document.getElementById("display-potongan").textContent = "Rp. " + formatRupiah(potonganRupiah);
                document.getElementById("display-total-bayar").textContent = "Rp. " + formatRupiah(totalSetelahDiskon);
                
                // Hitung kembalian
                let kembalian = uangBayar - totalSetelahDiskon;
                document.getElementById("kembalian").value = kembalian >= 0 ? kembalian : 0;
            }
            
            // Cek nomor telepon dan hitung potongan
            function cekNoTelpDanHitungPotongan() {
                let potongan = 0;
                let point_dipakai = 0;

                if (pointMember > 0 && totalHargaKeranjang > 0) {
                    potongan = pointMember * 1000;
                    if (potongan > totalHargaKeranjang) {
                        potongan = totalHargaKeranjang;
                    }
                    point_dipakai = Math.floor(potongan / 1000);
                }

                document.getElementById("potongan-harga").value = point_dipakai;
                hitungKembalian();
            }

            // Event listeners
            document.getElementById("no-telp").addEventListener("input", cekNoTelpDanHitungPotongan);
            document.getElementById("uang-bayar").addEventListener("input", hitungKembalian);
            document.getElementById("potongan-harga").addEventListener("input", hitungKembalian);

            // Inisialisasi
            cekNoTelpDanHitungPotongan();
        });
    </script>
</body>
</html>