<?php
session_start();
if (!isset($_SESSION['MaND']) || $_SESSION['VaiTro'] != 1) {
    echo json_encode([]); exit;
}

include 'config.php';

$maDH = (int)($_GET['maDH'] ?? 0);
if (!$maDH) { echo json_encode([]); exit; }

$sql = "SELECT ct.SoLuong, ct.DonGia,
               ISNULL(sp.TenSP, N'Sản phẩm không còn') AS TenSP
        FROM ChiTietDonHang ct
        LEFT JOIN SanPham sp ON sp.MaSP = ct.MaSP
        WHERE ct.MaDH = ?";

$stmt  = sqlsrv_query($conn, $sql, [$maDH]);
$items = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $items[] = $row;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($items, JSON_UNESCAPED_UNICODE);
