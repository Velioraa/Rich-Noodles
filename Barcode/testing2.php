<?php
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    echo "Kode barcode: ". htmlspecialchars($barcode);
}