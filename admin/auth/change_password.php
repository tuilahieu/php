<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; // Kết nối database

$success = $error = "";

// Kiểm tra nếu Admin đã đăng nhập
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['admin_id'];
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Lấy thông tin mật khẩu của Admin từ database
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Kiểm tra mật khẩu cũ có đúng không
    if (!password_verify($current_password, $hashed_password)) {
        $error = "❌ Mật khẩu cũ không đúng!";
    } elseif ($new_password !== $confirm_password) {
        $error = "❌ Mật khẩu mới không khớp!";
    } else {
        // Mã hóa mật khẩu mới và cập nhật
        $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hashed_password, $admin_id);

        if ($stmt->execute()) {
            $success = "✅ Đổi mật khẩu thành công!";
        } else {
            $error = "❌ Lỗi khi đổi mật khẩu!";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Đổi mật khẩu</title>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white shadow-md rounded-lg p-6 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Đổi mật khẩu</h2>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 p-3 mb-4 rounded-md"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-3 mb-4 rounded-md"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700">Mật khẩu cũ:</label>
                <input type="password" name="current_password" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label class="block text-gray-700">Mật khẩu mới:</label>
                <input type="password" name="new_password" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label class="block text-gray-700">Nhập lại mật khẩu mới:</label>
                <input type="password" name="confirm_password" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 transition">
                Đổi mật khẩu
            </button>
        </form>

        <div class="mt-6">
            <a href="/admin/dashboard.php" class="p-4 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition">
                ⬅ Quay lại Dashboard
            </a>
        </div>
    </div>
</body>
</html>
