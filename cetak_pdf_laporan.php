<?php
session_start();
include "koneksi.php";

require_once(__DIR__ . '/vendor/autoload.php');
use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil filter tahun dan tanggal
$tahun = $_GET['tahun'] ?? date('Y');
$tanggalFilter = $_GET['tanggal'] ?? null;

// ---------------------
// Hitung Keuntungan Per Bulan - DENGAN FILTER
// ---------------------
$keuntungan_bulan = array_fill(1, 12, 0);

$sql_bulanan = "SELECT 
        MONTH(t.tanggal_transaksi) AS bulan,
        SUM((p.harga_jual - p.modal) * dt.total_produk) AS keuntungan
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id_transaksi = dt.fid_transaksi
    JOIN produk p ON dt.fid_produk = p.id_produk
    WHERE 1=1";

$params_bulanan = [];
$types_bulanan = "";

if ($tahun) {
    $sql_bulanan .= " AND YEAR(t.tanggal_transaksi) = ?";
    $params_bulanan[] = $tahun;
    $types_bulanan .= "i";
}
if ($tanggalFilter) {
    $sql_bulanan .= " AND DATE(t.tanggal_transaksi) = ?";
    $params_bulanan[] = $tanggalFilter;
    $types_bulanan .= "s";
}
$sql_bulanan .= " GROUP BY MONTH(t.tanggal_transaksi) ORDER BY MONTH(t.tanggal_transaksi)";

$stmt_bulanan = $conn->prepare($sql_bulanan);
if ($params_bulanan) {
    $stmt_bulanan->bind_param($types_bulanan, ...$params_bulanan);
}
$stmt_bulanan->execute();
$res = $stmt_bulanan->get_result();
while ($row = $res->fetch_assoc()) {
    $keuntungan_bulan[(int)$row['bulan']] = (float)$row['keuntungan'];
}

// ---------------------
// Total Keseluruhan - DENGAN FILTER
// ---------------------
$sql_total = "SELECT 
    SUM((p.harga_jual - p.modal) * dt.total_produk) AS total_keuntungan,
    SUM(p.modal * dt.total_produk) AS total_modal,
    SUM(p.harga_jual * dt.total_produk) AS total_penjualan
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.fid_transaksi
JOIN produk p ON dt.fid_produk = p.id_produk
WHERE 1=1";

$params_total = [];
$types_total = "";

if ($tahun) {
    $sql_total .= " AND YEAR(t.tanggal_transaksi) = ?";
    $params_total[] = $tahun;
    $types_total .= "i";
}
if ($tanggalFilter) {
    $sql_total .= " AND DATE(t.tanggal_transaksi) = ?";
    $params_total[] = $tanggalFilter;
    $types_total .= "s";
}

$stmt_total = $conn->prepare($sql_total);
if ($params_total) {
    $stmt_total->bind_param($types_total, ...$params_total);
}
$stmt_total->execute();
$total_data = $stmt_total->get_result()->fetch_assoc();
$total_keuntungan = $total_data['total_keuntungan'] ?? 0;
$total_modal = $total_data['total_modal'] ?? 0;
$total_penjualan = $total_data['total_penjualan'] ?? 0;

// ---------------------
// Ambil Data Transaksi Lengkap - DENGAN FILTER
// ---------------------
$transaksiQuery = "SELECT 
    t.id_transaksi,
    t.fid_member,
    CASE 
        WHEN t.fid_member IS NULL OR t.fid_member = 0 THEN 'Non-Member'
        WHEN m.username IS NULL THEN 'Member (Data tidak ditemukan)'
        ELSE m.username 
    END AS nama_member,
    CASE 
        WHEN t.fid_member IS NULL OR t.fid_member = 0 THEN '-'
        WHEN m.no_telp IS NULL THEN '-'
        ELSE m.no_telp 
    END AS no_telp_member,
    t.total_harga,
    t.total_bayar,
    t.total_keuntungan,
    t.total_kembalian,
    t.fid_metode_pembayaran,
    mp.nama_metode,
    a.username AS nama_kasir,
    t.tanggal_transaksi
FROM transaksi t
LEFT JOIN member m ON t.fid_member = m.id
LEFT JOIN metode_pembayaran mp ON t.fid_metode_pembayaran = mp.id_metode_pembayaran
LEFT JOIN admin a ON t.fid_admin = a.id
WHERE 1=1";

$params_transaksi = [];
$types_transaksi = "";

if ($tahun) {
    $transaksiQuery .= " AND YEAR(t.tanggal_transaksi) = ?";
    $params_transaksi[] = $tahun;
    $types_transaksi .= "i";
}
if ($tanggalFilter) {
    $transaksiQuery .= " AND DATE(t.tanggal_transaksi) = ?";
    $params_transaksi[] = $tanggalFilter;
    $types_transaksi .= "s";
}

$transaksiQuery .= " ORDER BY t.tanggal_transaksi DESC";

$stmtTrans = $conn->prepare($transaksiQuery);
if ($params_transaksi) {
    $stmtTrans->bind_param($types_transaksi, ...$params_transaksi);
}
$stmtTrans->execute();
$transaksiResult = $stmtTrans->get_result();

// ---------------------
// Judul Laporan
// ---------------------
$judul_laporan = "LAPORAN PENJUALAN";
if ($tahun && $tanggalFilter) {
    $judul_laporan .= " TANGGAL " . date('d/m/Y', strtotime($tanggalFilter));
} elseif ($tahun) {
    $judul_laporan .= " TAHUN " . $tahun;
} elseif ($tanggalFilter) {
    $judul_laporan .= " TANGGAL " . date('d/m/Y', strtotime($tanggalFilter));
} else {
    $judul_laporan .= " SEMUA PERIODE";
}

// ---------------------
// HTML UNTUK PDF
// ---------------------
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: "Helvetica", Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; line-height: 1.4; }
    .header { text-align: center; margin-bottom: 20px; padding: 15px; border: 2px solid #4A90E2; border-radius: 8px; background: #f8f9fa; }
    h1 { color: #2c3e50; margin: 0 0 5px 0; font-size: 24px; font-weight: bold; }
    h2 { color: #4A90E2; margin: 0; font-size: 16px; font-weight: bold; }
    .summary-grid { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 10px; }
    .summary-card { flex: 1; background: white; padding: 15px; border: 1px solid #ddd; border-radius: 8px; text-align: center; }
    .summary-card h3 { color: #2c3e50; margin-bottom: 8px; font-size: 12px; }
    .summary-card p { font-size: 16px; color: #27ae60; font-weight: bold; margin: 0; }
    .chart-container { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; }
    .chart-title { color: #2c3e50; margin-bottom: 15px; font-size: 14px; font-weight: bold; text-align: center; }
    .bar-chart-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    .bar-chart-table td { vertical-align: bottom; text-align: center; padding: 5px 2px; border: none; width: 8.33%; }
    .bar-container { height: 120px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
    .bar {
        width: 25px;
        background-color: #007bff; /* biru solid */
        border: 1px solid #0056b3;
        border-radius: 3px 3px 0 0;
        min-height: 3px;
    }
    .bar-value { font-size: 8px; margin-bottom: 3px; color: #666; font-weight: bold; }
    .bar-label { font-size: 9px; margin-top: 5px; color: #333; font-weight: bold; }
    .table-container { background: white; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; }
    .section-title { color: #2c3e50; margin-bottom: 12px; font-size: 16px; font-weight: bold; border-bottom: 2px solid #4A90E2; padding-bottom: 5px; }
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data-table th { background: #4A90E2; color: white; padding: 8px 5px; text-align: center; font-weight: bold; font-size: 10px; border: 1px solid #357ABD; }
    table.data-table td { padding: 6px 5px; border: 1px solid #ddd; text-align: center; font-size: 9px; }
    table.data-table tr:nth-child(even) { background-color: #f8f9fa; }
    .footer { text-align: center; margin-top: 20px; padding: 15px; color: #666; font-size: 9px; border-top: 1px solid #ddd; }
</style>
</head>
<body>
    <div class="header">
        <h1>RICH NOODLES</h1>
        <h2>' . htmlspecialchars($judul_laporan) . '</h2>
    </div>';

// summary
$html .= '
<div class="summary-grid">
    <div class="summary-card"><h3>TOTAL KEUNTUNGAN</h3><p>Rp ' . number_format($total_keuntungan, 0, ',', '.') . '</p></div>
    <div class="summary-card"><h3>TOTAL PENJUALAN</h3><p>Rp ' . number_format($total_penjualan, 0, ',', '.') . '</p></div>
    <div class="summary-card"><h3>TOTAL MODAL</h3><p>Rp ' . number_format($total_modal, 0, ',', '.') . '</p></div>
</div>';

// diagram batang
$maxKeuntungan = max($keuntungan_bulan) > 0 ? max($keuntungan_bulan) : 100000;
$html .= '
<div class="chart-container">
    <div class="chart-title">DIAGRAM BATANG - KEUNTUNGAN PER BULAN</div>
    <table class="bar-chart-table"><tr>';
$bulanNama = [1=>'JAN','FEB','MAR','APR','MEI','JUN','JUL','AGU','SEP','OKT','NOV','DES'];
foreach ($keuntungan_bulan as $bulan=>$nilai) {
    $height = ($nilai / $maxKeuntungan) * 100;
    $height = max($height, 3);
    if ($nilai >= 1000000) $displayValue='Rp '.number_format($nilai/1000000,1,',','.').'JT';
    elseif ($nilai >= 1000) $displayValue='Rp '.number_format($nilai/1000,0,',','.').'K';
    else $displayValue=$nilai>0?'Rp '.number_format($nilai,0,',','.'): '-';
    $html .= '<td><div class="bar-container">
                <div class="bar-value">'.$displayValue.'</div>
                <div class="bar" style="height:'.$height.'px;"></div>
                <div class="bar-label">'.$bulanNama[$bulan].'</div>
              </div></td>';
}
$html .= '</tr></table>
<div style="text-align:center;font-size:9px;color:#666;">* Diagram menunjukkan keuntungan per bulan</div></div>';

// tabel transaksi
$html .= '
<div class="table-container">
    <div class="section-title">DAFTAR TRANSAKSI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>NO</th><th>TANGGAL</th><th>KASIR</th><th>MEMBER</th><th>METODE BAYAR</th>
                <th>TOTAL HARGA</th><th>TOTAL BAYAR</th><th>KEMBALIAN</th><th>KEUNTUNGAN</th>
            </tr>
        </thead><tbody>';

$no = 1;
if ($transaksiResult && $transaksiResult->num_rows > 0) {
    while ($row = $transaksiResult->fetch_assoc()) {
        $html .= "<tr>
                    <td>{$no}</td>
                    <td>".date('d/m/Y H:i', strtotime($row['tanggal_transaksi']))."</td>
                    <td>".htmlspecialchars($row['nama_kasir']??'-')."</td>
                    <td>".htmlspecialchars($row['nama_member'])."</td>
                    <td>".htmlspecialchars($row['nama_metode']??'-')."</td>
                    <td>Rp ".number_format($row['total_harga'],0,',','.')."</td>
                    <td>Rp ".number_format($row['total_bayar'],0,',','.')."</td>
                    <td>Rp ".number_format($row['total_kembalian'],0,',','.')."</td>
                    <td style='color:#27ae60;font-weight:bold;'>Rp ".number_format($row['total_keuntungan'],0,',','.')."</td>
                 </tr>";
        $no++;
    }
} else {
    $html .= '<tr><td colspan="9" style="text-align:center;color:#666;">Tidak ada transaksi</td></tr>';
}
$html .= '</tbody></table></div>
<div class="footer"><p>Laporan dibuat pada: '.date('d/m/Y H:i').'</p><p>&copy; '.date('Y').' Rich Noodles</p></div>
</body></html>';

// generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Laporan_Penjualan_Tahun_" . $tahun . ".pdf";
$dompdf->stream($filename, ["Attachment" => true, "compress" => true]);
exit;
?>
