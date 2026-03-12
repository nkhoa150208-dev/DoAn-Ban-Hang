<?php
session_start();
include "config.php";

echo "<h3>Cột trong bảng DonHang:</h3><pre>";
$test = sqlsrv_query($conn, "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'DonHang' ORDER BY ORDINAL_POSITION");
while ($r = sqlsrv_fetch_array($test, SQLSRV_FETCH_ASSOC)) {
    echo $r['COLUMN_NAME'] . " (" . $r['DATA_TYPE'] . ")\n";
}
echo "</pre>";

echo "<h3>Session hiện tại:</h3><pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h3>Test INSERT đơn giản:</h3>";
$sql = "INSERT INTO DonHang (MaND, TongTien, TrangThai) VALUES (?, ?, N'Test')";
$stmt = sqlsrv_query($conn, $sql, [$_SESSION['MaND'] ?? 1, 0]);
if ($stmt) {
    $id = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() AS MaDH");
    $row = sqlsrv_fetch_array($id, SQLSRV_FETCH_ASSOC);
    echo "<span style='color:green'>✅ INSERT thành công! MaDH = " . $row['MaDH'] . "</span>";
    // Xóa test record
    sqlsrv_query($conn, "DELETE FROM DonHang WHERE MaDH = ?", [$row['MaDH']]);
} else {
    echo "<span style='color:red'>❌ Lỗi: ";
    $errors = sqlsrv_errors();
    echo $errors[0]['message'] . "</span>";
}
?>
