<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; // File kết nối CSDL ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php" ?>

<div class="my-24 mx-auto bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Đăng ký</h2>

        <form action="register.php" method="POST" class="space-y-5">
            <div>
                <label for="name" class="block font-medium text-gray-700">Họ và tên</label>
                <input type="name" id="name" name="name" required 
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
            </div>

            <div>
                <label for="email" class="block font-medium text-gray-700">Email đăng ký</label>
                <input type="email" id="email" name="email" required 
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
            </div>
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
                Đăng ký
            </button>
        </form>

        <!-- Link đăng ký -->
        <p class="text-center mt-4 text-gray-600 text-lg">
            Đã có tài khoản? <a href="login.php" class="text-blue-500 hover:underline font-medium">Đăng nhập</a>
        </p>
    </div>

</body>
</html>

<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra dữ liệu đầu vào
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        echo("<script>
        Swal.fire({
            title: 'Thất bại',
            text: 'Vui lòng nhập đầy đủ thông tin !',
            icon: 'error',
        });
        
        </script>");
    }

    // Kiểm tra email & username đã tồn tại chưa
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo("<script>
        Swal.fire({
            title: 'Thất bại',
            text: 'Tên đăng nhập đã tồn tại !',
            icon: 'error',
        });
        
        </script>");
    }
    
    // Mã hóa mật khẩu trước khi lưu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Lưu vào database
    $stmt = $conn->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $username, $hashed_password);
    
    if ($stmt->execute()) {
        echo("<script>
            Swal.fire({
                title: 'Thành công',
                text: 'Đăng kí thành công !',
                icon: 'success',
            }).then(() => window.location.href = '/auth/login.php');
        
        </script>");
    } else {
        echo("<script>
            Swal.fire({
                title: 'Thất bại',
                text: 'Lỗi không xác định',
                icon: 'error',
            });
        
        </script>");
    }

    $stmt->close();
}


?>
