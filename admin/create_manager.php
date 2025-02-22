<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; 
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_role);
$stmt->fetch();

if($admin_role !== 'superadmin') {
    header('Location: /admin/dashboard.php');
    exit;
}
$stmt->close();



$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Mã hóa mật khẩu

    // Kiểm tra username hoặc email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $error = "❌ Email hoặc Username đã tồn tại!";
    } else {
        // Chèn tài khoản quản lý mới
        $stmt = $conn->prepare("INSERT INTO admin (name, email, username, password, role) VALUES (?, ?, ?, ?, 'manager')");
        $stmt->bind_param("ssss", $name, $email, $username, $password);
        
        if ($stmt->execute()) {
            $success = "✅ Tạo tài khoản quản lý thành công!";
        } else {
            $error = "❌ Lỗi khi tạo tài khoản!";
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Tạo tài khoản quản lý</title>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white shadow-md rounded-lg p-6 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Cấp tài khoản quản lý mới</h2>
        <p class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">Lưu ý: khi cấp tài khoản quản lí, mật khẩu mặc định sẽ là <strong>admin</strong>, lần đầu đăng nhập cần đổi mật khẩu mới có thể quản lí.</p>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 p-3 mb-4 rounded-md"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-3 mb-4 rounded-md"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700">Họ và Tên:</label>
                <input type="text" name="name" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label class="block text-gray-700">Email:</label>
                <input type="email" name="email" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label class="block text-gray-700">Username:</label>
                <input type="text" name="username" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <div>
                <input type="password" hidden value="admin" name="password" required class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 transition">
                Tạo tài khoản
            </button>
        </form>

        <div class="mt-6">
            <a href="dashboard.php" class="p-4 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition">
                ⬅ Quay lại Dashboard
            </a>
        </div>
    </div>
    
</body>
</html>
