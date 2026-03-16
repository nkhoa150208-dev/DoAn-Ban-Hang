<?php
$serverName = "localhost\\SQLEXPRESS"; // Tên server của bạn
$database = "QLBanHang";

// BẮT BUỘC PHẢI CÓ "CharacterSet" => "UTF-8" ĐỂ KHÔNG LỖI FONT
$connectionInfo = [
    "Database" => $database, 
    "TrustServerCertificate" => true, 
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionInfo);



if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
