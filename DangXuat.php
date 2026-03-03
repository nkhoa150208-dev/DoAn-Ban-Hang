<?php
session_start();
session_destroy();
header('Location: DangNhap.php');
exit;
?>