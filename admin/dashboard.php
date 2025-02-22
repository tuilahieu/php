<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

var_dump($_SESSION);
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_role']);
    header('Location: /admin/login.php');
    exit();
}
// Truy vấn thống kê
$total_services = $conn->query("SELECT COUNT(*) AS count FROM services")->fetch_assoc()['count'];
$total_banks = $conn->query("SELECT COUNT(*) AS count FROM admin_banks")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$total_completed_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];
$total_transactions = $conn->query("SELECT COUNT(*) AS count FROM transactions")->fetch_assoc()['count'];
$total_money_added = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE status = 'completed'")->fetch_assoc()['total'];
$total_money_added = $total_money_added ? number_format($total_money_added) : 0;

// Kiểm tra xem có đơn hàng nào đang pending không
$pending_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];

// Kiểm tra xem có đơn nạp nào đang pending không
$pending_transactions = $conn->query("SELECT COUNT(*) AS count FROM transactions WHERE status = 'pending'")->fetch_assoc()['count'];

// Tổng doanh thu
$total_revenue = $conn->query("SELECT SUM(s.price) AS revenue 
                               FROM orders o 
                               JOIN services s ON o.service_id = s.id 
                               WHERE o.status = 'completed'")->fetch_assoc()['revenue'];
$total_revenue = $total_revenue ? number_format($total_revenue) : 0;
 
// số quản lý
$total_managers = $conn->query("SELECT COUNT(*) AS count FROM admin WHERE role = 'manager'")->fetch_assoc()['count'];

$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT name, password, email, username, role FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_name, $admin_password, $admin_email, $admin_username, $admin_role);
$stmt->fetch();
$stmt->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Dashboard Admin </title>
</head>
<body>
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Thống kê</h2>

    <?php if ($pending_orders > 0 || $pending_transactions > 0): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <strong>Thông báo:</strong>
        <ul>
            <?php if ($pending_orders > 0): ?>
                <li>⚠️ Có <strong><?= $pending_orders ?></strong> đơn hàng cần xử lý.</li>
            <?php endif; ?>
            <?php if ($pending_transactions > 0): ?>
                <li>⚠️ Có <strong><?= $pending_transactions ?></strong> đơn nạp tiền đang chờ xác nhận.</li>
            <?php endif; ?>
        </ul>
    </div>

    
<?php endif; ?>

<?php 
if(password_verify('admin', $admin_password)){
    echo '<div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">⚠️ CẢNH BÁO: Bạn đang sử dụng mật khẩu mặc định. Vui lòng đổi mật khẩu trước khi quản lí.</div>';
    echo '<a href="auth/change_password.php" class="p-4 bg-blue-500 text-white rounded-lg shadow hover:bg-red-600 transition">
            Đổi mật khẩu
        </a>';
    exit;
} ?>


<div class="my-12">
    <div class="p-4 bg-gray-200 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700">👤 Tài khoản Admin</h3>
        <p class="text-gray-800"><strong>Tên:</strong> <?= htmlspecialchars($admin_name) ?></p>
        <p class="text-gray-800"><strong>Email:</strong> <?= htmlspecialchars($admin_email) ?></p>
        <p class="text-gray-800"><strong>Username:</strong> <?= htmlspecialchars($admin_username) ?></p>
        <p class="text-gray-800"><strong>Vai trò:</strong> <?= ($admin_role === 'superadmin') ? "Super Admin" : "Quản lý" ?></p>
    </div>

    <div class="mt-4 flex space-x-4">
        <?php
            if($admin_role === 'superadmin') {
                echo '<a href="create_manager.php" class="p-4 bg-cyan-500 text-white rounded-lg shadow hover:bg-purple-600 transition">
                Cấp quyền quản lý mới
            </a>';
            }

            
        ?>
        <a href="auth/change_password.php" class="p-4 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition">
            Đổi mật khẩu
        </a>
        <a href="managers.php" class="p-4 bg-blue-500 text-white rounded-lg shadow hover:bg-red-600 transition">
            Danh sách Quản lý
        </a>
        <a href="?logout=true" class="p-4 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition">
            Đăng xuất
        </a>
    </div>
</div>




    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-blue-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Dịch vụ</h3>
            <p class="text-2xl"><?= $total_services ?></p>
        </div>

        <div class="p-6 bg-green-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Ngân hàng</h3>
            <p class="text-2xl"><?= $total_banks ?></p>
        </div>

        <div class="p-6 bg-yellow-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Người dùng</h3>
            <p class="text-2xl"><?= $total_users ?></p>
        </div>

        <div class="p-6 bg-red-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Đơn hàng</h3>
            <p class="text-2xl"><?= $total_orders ?></p>
        </div>

        <div class="p-6 bg-purple-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Đơn hàng hoàn thành</h3>
            <p class="text-2xl"><?= $total_completed_orders ?></p>
        </div>

        <div class="p-6 bg-indigo-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Giao dịch nạp tiền</h3>
            <p class="text-2xl"><?= $total_transactions ?></p>
        </div>

        <div class="p-6 bg-gray-700 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Tổng số tiền đã nạp</h3>
            <p class="text-2xl"><?= $total_money_added ?> VND</p>
        </div>

        <div class="p-6 bg-orange-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Tổng doanh thu</h3>
            <p class="text-2xl"><?= $total_revenue ?> VND</p>
        </div>

        <div class="p-6 bg-pink-500 text-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Số quản lý</h3>
            <p class="text-2xl"><?= $total_managers ?> </p>
        </div>
    </div>
</div>
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="services.php" class="p-4 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 transition">
            Quản lý dịch vụ
        </a>
        <a href="banks.php" class="p-4 bg-green-500 text-white rounded-lg shadow hover:bg-green-600 transition">
            Quản lý ngân hàng
        </a>
        <a href="users.php" class="p-4 bg-yellow-500 text-white rounded-lg shadow hover:bg-yellow-600 transition">
            Quản lý người dùng
        </a>
        <a href="orders.php" class="p-4 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition">
            Quản lý đơn hàng
        </a>
        <a href="transactions.php" class="p-4 bg-purple-500 text-white rounded-lg shadow hover:bg-purple-600 transition">
            Quản lý nạp tiền
        </a>
    </div>
</div>

</body>
</html>