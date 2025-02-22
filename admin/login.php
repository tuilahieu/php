

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Đăng nhập Admin</title>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Đăng nhập Admin</h2>

        <form method="POST">
            <label class="block text-gray-700 font-medium">Tên đăng nhập:</label>
            <input type="text" name="username" class="w-full p-2 border rounded mb-4" required>
            
            <label class="block text-gray-700 font-medium">Mật khẩu:</label>
            <input type="password" name="password" class="w-full p-2 border rounded mb-4" required>
            
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
                Đăng nhập
            </button>
        </form>
    </div>

</body>
</html>


<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";

// Kiểm tra nếu admin đã đăng nhập
if (isset($_SESSION['admin_id'])) {
    header("Location: /admin/dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $stmt = $conn->prepare("SELECT id, password, role FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            echo "<script>
                Swal.fire({
                    title: 'Thành công!',
                    text: 'Đăng nhập thành công!',
                    icon: 'success',
                }).then(() => {
                    window.location.href = '/admin/dashboard.php';
                });
            </script>";
            exit();
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Thất bại!',
                    text: 'Sai mật khẩu!',
                    icon: 'error',
                });
            </script>";
            exit();
        }
    } else {
        echo "<script>
                Swal.fire({
                    title: 'Thất bại!',
                    text: 'Tài khoản không tồn tại!',
                    icon: 'error',
                });
            </script>";
        exit();
    }
    $stmt->close();
}
?>
