<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

// Lấy danh sách giao dịch
$query = "SELECT transactions.id, users.username, admin_banks.bank_name, transactions.amount, transactions.transaction_code, transactions.status, transactions.created_at 
          FROM transactions 
          JOIN users ON transactions.user_id = users.id 
          JOIN admin_banks ON transactions.bank_id = admin_banks.id
          ORDER BY transactions.created_at DESC";

$transactions = $conn->query($query);

if (!$transactions) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

// Xử lý cập nhật trạng thái giao dịch
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_transaction'])) {
    $transaction_id = intval($_POST['transaction_id']);
    $new_status = $_POST['status'];

    // Lấy thông tin giao dịch
    $stmt = $conn->prepare("SELECT user_id, amount FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $amount);
    $stmt->fetch();
    $stmt->close();

    // Cập nhật trạng thái giao dịch
    $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $transaction_id);
    $stmt->execute();
    $stmt->close();

    // Nếu trạng thái là "completed", cộng tiền vào tài khoản user
    if ($new_status === 'completed') {
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("ii", $amount, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: transactions.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giao dịch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const swalScript = document.createElement("script");
            swalScript.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
            document.body.appendChild(swalScript);
        });

        function updateTransaction(id, currentStatus) {
            Swal.fire({
                title: 'Cập nhật trạng thái',
                input: 'select',
                inputOptions: {
                    'completed': 'Hoàn thành',
                    'failed': 'Thất bại'
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
                        <input name="update_transaction" value="1">
                        <input name="transaction_id" value="${id}">
                        <input name="status" value="${newStatus}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold mb-4">Quản lý Giao dịch</h2>

        <table class="w-full border-collapse border border-gray-300 bg-white shadow-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Người dùng</th>
                    <th class="border p-2">Ngân hàng</th>
                    <th class="border p-2">Số tiền</th>
                    <th class="border p-2">Mã giao dịch</th>
                    <th class="border p-2">Trạng thái</th>
                    <th class="border p-2">Ngày tạo</th>
                    <th class="border p-2">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td class="border p-2"><?= $row['id'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['username']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['bank_name']) ?></td>
                        <td class="border p-2"><?= number_format($row['amount']) ?> VND</td>
                        <td class="border p-2"><?= htmlspecialchars($row['transaction_code']) ?></td>
                        <td class="border p-2 text-blue-500 font-bold" id="status-<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </td>
                        <td class="border p-2"><?= $row['created_at'] ?></td>
                        <td class="border p-2">
                            <?php if ($row['status'] === 'pending'): ?>
                                <button class="bg-yellow-500 text-white px-3 py-1 rounded" onclick="updateTransaction(<?= $row['id'] ?>, '<?= $row['status'] ?>')">Sửa trạng thái đơn nạp</button>
                            <?php endif; ?>
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

</body>
</html>
