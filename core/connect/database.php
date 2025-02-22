<?php

// error_reporting(0);  // Tắt toàn bộ báo lỗi
// ini_set('display_errors', 0); // Không hiển thị lỗi ra màn hình


session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "ads_complete";



$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    require $_SERVER['DOCUMENT_ROOT'] . "/includes/error.php"; 
    exit();
}
?>
