<?php
// File: cap_nhat_gio_hang.php
session_start();

if (isset($_POST['id_sanpham']) && isset($_POST['thay_doi'])) {
    $maSP = $_POST['id_sanpham'];
    $thayDoi = (int)$_POST['thay_doi'];

    if (isset($_SESSION['giohang'][$maSP])) {
        $soLuongMoi = $_SESSION['giohang'][$maSP]['SoLuong'] + $thayDoi;

        if ($soLuongMoi > 0) {
            $_SESSION['giohang'][$maSP]['SoLuong'] = $soLuongMoi;
        } else {
            // Nếu trừ xuống 0 thì xóa luôn sản phẩm
            unset($_SESSION['giohang'][$maSP]);
        }
    }
}
?>