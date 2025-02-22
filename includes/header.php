<!DOCTYPE html>
<?php
error_reporting(0);  // Tắt toàn bộ báo lỗi
ini_set('display_errors', 0); // Không hiển thị lỗi ra màn hình
?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link href="/vendors/tailwind.min.css" rel="stylesheet">
        <script src="/vendors/sweetalert2.min.js"></script>
    </head>
    <!-- HEADER -->
    <body class="bg-gray-200">
    <header class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center flex-wrap">
            <!-- Logo -->
            <a href="/">
                <div class="flex items-center space-x-2">
                    <img src="/assets/images/icon.png" alt="Logo" class="w-12 h-12">
                    <span class="text-lg font-semibold">ADS 24/7</span>
                </div>
            </a>
            
            <!-- Buttons -->
            <?php
                session_start();
                if(!isset($_SESSION['user_id'])) {
                    echo '<div class="space-x-2 mt-2 md:mt-0">
                        <a href="/auth/login.php" class="block md:inline-block bg-blue-500 px-4 py-2 rounded-lg hover:bg-blue-600">Đăng nhập</a>
                        <a href="/auth/register.php" class="block md:inline-block bg-green-500 px-4 py-2 rounded-lg hover:bg-green-600">Đăng ký</a>
                    </div>';
                } else {
                    echo '<a href="/auth/logout.php" class="block md:inline-block bg-blue-500 px-4 py-2 rounded-lg hover:bg-blue-600">Đăng xuất</a>';
                }
            ?>
        </div>
    </header>

