<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php" ?>

<?php
    if (!isset($_SESSION['user_id'])) {
        header("Location: /");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$user = $result->fetch_assoc()) { 
        header('Location: /auth/logout.php');
    }
    $stmt->close();

    // Lấy thông báo mới nhất từ bảng admin_notice
    $stmt = $conn->prepare("SELECT message, created_at FROM admin_notice ORDER BY created_at DESC LIMIT 2");
    $stmt->execute();
    $result = $stmt->get_result();

?>
    <!-- START BODY -->

    <div class="my-10 mx-auto bg-white p-6 rounded-xl shadow-lg w-full max-w-2xl">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Thông báo từ Admin</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <ul class="space-y-2">
            <?php while ($notice = $result->fetch_assoc()): ?>
                <li class="bg-gray-100 p-3 rounded-md">
                    <span class="font-semibold text-gray-700"><?= htmlspecialchars($notice['message']) ?></span>
                    <br>
                    <span class="text-sm text-gray-500"><?= htmlspecialchars($notice['created_at']) ?></span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="text-gray-600">Chưa có thông báo nào.</p>
    <?php endif; ?>

</div>

<?php
$stmt->close();
?>


    <div class="my-12 mx-auto  bg-white p-8 rounded-xl shadow-lg w-full max-w-md text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6">Thông Tin Người Dùng</h2>
        
        <!-- Tên User -->
        <p class="text-lg font-medium text-gray-700">Tên: <span class="font-semibold"><?=$user['name']?></span></p>
        
        <!-- Số Tiền Trong Tài Khoản -->
        <p class="text-lg font-medium text-gray-700 mt-2">Số dư: <span class="font-semibold text-green-600"><?=number_format($user['balance']) . ' VND'?></span></p>
        
        <!-- Nút Tạo Dịch Vụ -->
        <a href="naptien.php" 
            class="mt-6 inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition text-lg font-semibold">
            Nạp Tiền
        </a>
        <a href="taodichvu.php" 
            class="mt-6 inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition text-lg font-semibold">
            Tạo Dịch Vụ
        </a>
        <a href="lichsunaptien.php" 
            class="mt-6 inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition text-lg font-semibold">
            Lịch sử nạp tiền
        </a>
    </div>

    <?php

    $user_id = $_SESSION['user_id']; 

    // Lấy danh sách đơn hàng của user từ database
    $sql = "SELECT o.id, o.service_name, s.name AS service_name_from_services, 
               s.service_code, s.price, o.notes, o.status, o.created_at
        FROM orders o
        JOIN services s ON o.service_id = s.id
        WHERE o.user_id = ? 
        ORDER BY o.created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // xử lí huỷ và hoàn tiền nếu status = pending

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        $user_id = $_SESSION['user_id'];
    
        // Lấy giá dịch vụ để hoàn tiền
        $stmt = $conn->prepare("SELECT service_id FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($order = $result->fetch_assoc()) {
            $service_id = $order['service_id'];
    
            // Lấy giá dịch vụ từ bảng services
            $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($service = $result->fetch_assoc()) {
                $refund_amount = $service['price'];
    
                // Cập nhật trạng thái đơn hàng thành "canceled"
                $stmt = $conn->prepare("UPDATE orders SET status = 'canceled' WHERE id = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
    
                // Hoàn tiền lại vào tài khoản user
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->bind_param("di", $refund_amount, $user_id);
                $stmt->execute();

                echo("<script>
                    Swal.fire({
                        title: 'Thành công!',
                        text: 'Yêu cầu huỷ đơn thành công. Đã hoàn tiền !!',
                        icon: 'success',
                    }).then(() => window.location.href = '/pages/dashboard.php');
                    
                    </script>");
                exit();
            }
        }
        $stmt->close();
        $conn->close();
    }
    ?>

    <!-- Hiển thị danh sách đơn hàng -->
    <div class="my-24 mx-auto bg-white p-8 rounded-xl shadow-lg w-full max-w-4xl mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Lịch sử sử dụng dịch vụ</h2>
        <table class="w-full border-collapse border border-gray-300 text-center">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-3">Mã đơn</th>
                    <th class="border p-3">Tên đơn</th>
                    <th class="border p-3">Loại Dịch Vụ</th>
                    <th class="border p-3">Ghi Chú</th>
                    <th class="border p-3">Thời gian tạo</th>
                    <th class="border p-3">Trạng Thái</th>
                    <th class="border p-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $has_history = false;
                 while ($row = $result->fetch_assoc()):
                    $has_history = true;
                 ?>
                    <tr>
                        <td class="border p-3"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="border p-3"><?php echo htmlspecialchars($row['service_name']); ?></td>
                        <td class="border p-3"><?php echo htmlspecialchars($row['service_code']); ?></td>
                        <td class="border p-3"><?php echo htmlspecialchars($row['notes']); ?></td>
                        <td class="border p-3"><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td class="border p-3 <?php
                        $statusColors = [
                            'completed'  => 'text-green-600',
                            'processing' => 'text-blue-600',
                            'pending'    => 'text-yellow-600',
                            'canceled'   => 'text-red-600'
                        ];
                    
                        $statusClass = $statusColors[$row['status']] ?? 'text-gray-600';
                        echo $statusClass;
                         ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td class="border p-3">
                            <?php if ($row['status'] == 'pending'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="service_price" value="<?php echo $row['price']; ?>">
                                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                                        Hủy & hoàn tiền
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile;
                    if(!$has_history) {
                        echo '<tr class="text-center"><td colspan="6" class="p-3">Bạn chưa sử dụng dịch vụ nào.</td></tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>