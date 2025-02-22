<?php

require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";
session_start();
session_unset();  // Xóa tất cả biến session
session_destroy(); // Hủy toàn bộ session

// header('Location: /admin/login.php');
// exit();