<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; // File kết nối CSDL ?>
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
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        die("Vui lòng nhập đầy đủ thông tin!");
    }

    // Truy vấn kiểm tra user
    $stmt = $conn->prepare("SELECT id, name, password, balance FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Kiểm tra mật khẩu (giả sử mật khẩu đã mã hóa bằng password_hash)
        if (password_verify($password, $user['password'])) {
            // Lưu session đăng nhập
            $_SESSION['user_id'] = $user['id'];
            
            echo("<script>
        Swal.fire({
            title: 'Thành công!',
            text: 'Đăng nhập thành công !',
            icon: 'success',
        }).then(() => window.location.href = '/pages/dashboard.php');
        
        </script>");
        exit();
        } else {
            echo "<script>
            Swal.fire({
                title: 'Lỗi!',
                text: 'Sai tài khoản hoặc mật khẩu.',
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

    $stmt->close();
}
?>

