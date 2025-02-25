<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

// Lấy danh sách đơn hàng
$query = "SELECT orders.id, users.username, orders.service_name, orders.status, orders.price, orders.notes, orders.created_at, services.name
          FROM orders 
          JOIN users ON orders.user_id = users.id 
          JOIN services ON orders.service_id = services.id 
          ORDER BY orders.created_at DESC";
$orders = $conn->query($query);

// Xử lý cập nhật trạng thái đơn hàng và hoàn tiền
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // Lấy thông tin đơn hàng trước khi cập nhật
    $stmt = $conn->prepare("SELECT user_id, price, status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        die("Đơn hàng không tồn tại.");
    }

    // Kiểm tra nếu đơn hàng chưa bị hủy trước đó và bây giờ chuyển sang 'canceled'
    if ($order['status'] !== 'canceled' && $status === 'canceled') {
        $user_id = $order['user_id'];
        $refund_amount = $order['price'];

        // Hoàn tiền vào tài khoản người dùng
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("ii", $refund_amount, $user_id);
        $stmt->execute();
    }

    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    header('Location: orders.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold mb-4">Quản lý Đơn hàng</h2>

        <table class="w-full border-collapse border border-gray-300 bg-white shadow-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Khách hàng</th>
                    <th class="border p-2">Tên đơn</th>
                    <th class="border p-2">Dịch vụ</th>
                    <th class="border p-2">Giá</th>
                    <th class="border p-2">Trạng thái</th>
                    <th class="border p-2">Ghi chú</th>
                    <th class="border p-2">Ngày tạo</th>
                    <th class="border p-2">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $orders->fetch_assoc()): ?>
                    <tr>
                        <td class="border p-2"><?= $row['id'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['username']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['service_name']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="border p-2"><?= number_format(htmlspecialchars($row['price'])) . ' VNĐ' ?></td>
                        <td class="border p-2 text-blue-500 font-bold" id="status-<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </td>
                        <td class="border p-2"><?= htmlspecialchars($row['notes']) ?></td>
                        <td class="border p-2"><?= $row['created_at'] ?></td>
                        <td class="border p-2">
                            <button class="bg-yellow-500 text-white px-3 py-1 rounded" onclick="updateOrder(<?= $row['id'] ?>, '<?= $row['status'] ?>')">Sửa</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-6">
            <a href="dashboard.php" class="p-4 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition">
                ⬅ Quay lại Dashboard
            </a>
        </div>
    </div>

    

    <script>
        function updateOrder(id, currentStatus) {
            Swal.fire({
                title: 'Cập nhật trạng thái',
                input: 'select',
                inputOptions: {
                    'pending': 'Chờ xử lý',
                    'processing': 'Đang xử lý',
                    'completed': 'Hoàn thành',
                    'canceled': 'Đã hủy'
                },
                inputValue: currentStatus,
                showCancelButton: true,
                confirmButtonText: 'Lưu',
                cancelButtonText: 'Hủy',
                preConfirm: (newStatus) => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                        <input name="update_order" value="1">
                        <input name="order_id" value="${id}">
                        <input name="status" value="${newStatus}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>