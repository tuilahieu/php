<?php 
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; // File kết nối CSDL
?>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php" ?>

<div class="my-24 mx-auto bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Đăng nhập</h2>

        <form action="login.php" method="POST" class="space-y-5">
            <!-- Email -->
            <div>
                <label for="username" class="block font-medium text-gray-700">Tài khoản</label>
                <input type="username" id="username" name="username" required 
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
            </div>

            <!-- Mật khẩu -->
            <div>
                <label for="password" class="block font-medium text-gray-700">Mật khẩu</label>
                <input type="password" id="password" name="password" required 
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
            </div>

            <!-- Nút đăng nhập -->
            <button type="submit" 
                class="w-full bg-blue-500 text-white p-3 rounded-lg hover:bg-blue-600 transition text-lg font-semibold">
                Đăng nhập
            </button>
        </form>

        <!-- Link đăng ký -->
        <p class="text-right mt-4 text-gray-600 text-lg">
            <a href="register.php" class="text-blue-500 hover:underline font-medium">Quên mật khẩu ?</a>
        </p>

        <!-- Link đăng ký -->
        <p class="text-center mt-4 text-gray-600 text-lg">
            Chưa có tài khoản? <a href="register.php" class="text-blue-500 hover:underline font-medium">Đăng ký</a>
        </p>
    </div>

</body>
</html>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($password)) {
        echo "<script>
        Swal.fire({
            title: 'Lỗi!',
            text: 'Vui lòng nhập đầy đủ thông tin.',
            icon: 'error',
            confirmButtonText: 'Thử lại'
        });
        </script>";
        exit();
    }

    // Chuẩn bị câu truy vấn
    $stmt = $conn->prepare("SELECT id, name, password, balance FROM users WHERE username = ?");
    if ($stmt === false) {
        die("Lỗi chuẩn bị truy vấn: " . htmlspecialchars($conn->error));
    }

    // Gán tham số và thực thi truy vấn
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Liên kết các biến với kết quả trả về
    $stmt->bind_result($id, $name, $hashed_password, $balance);

    // Kiểm tra kết quả
    if ($stmt->fetch()) {
        // Xác minh mật khẩu
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            echo "<script>
            Swal.fire({
                title: 'Thành công!',
                text: 'Đăng nhập thành công!',
                icon: 'success',
            }).then(() => window.location.href = '/pages/dashboard.php');
            </script>";
            exit();
        } else {
            echo "<script>
            Swal.fire({
                title: 'Lỗi!',
                text: 'Sai mật khẩu.',
                icon: 'error',
                confirmButtonText: 'Thử lại'
            });
            </script>";
        }
    } else {
        echo "<script>
        Swal.fire({
            title: 'Lỗi!',
            text: 'Tài khoản không tồn tại.',
            icon: 'error',
            confirmButtonText: 'Thử lại'
        });
        </script>";
    }

    // Đóng câu lệnh và kết nối
    $stmt->close();
    $conn->close();
}
?>
