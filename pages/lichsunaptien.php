<?php 
require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php"; 
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/logout.php");
    exit();
}

// Lấy lịch sử nạp tiền của user hiện tại
$stmt = $conn->prepare("
    SELECT t.transaction_code, t.amount, t.status, t.created_at, 
           b.bank_name, b.account_number 
    FROM transactions t
    JOIN admin_banks b ON t.bank_id = b.id
    WHERE t.user_id = ?
    ORDER BY t.created_at ASC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mx-auto mt-6 bg-white p-6 rounded-lg shadow-md w-full max-w-3xl">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Lịch sử nạp tiền</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 p-2">Mã giao dịch</th>
                    <th class="border border-gray-300 p-2">Ngân hàng</th>
                    <th class="border border-gray-300 p-2">Số tiền</th>
                    <th class="border border-gray-300 p-2">Trạng thái</th>
                    <th class="border border-gray-300 p-2">Ngày tạo</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['transaction_code']) ?></td>
                        <td class="border border-gray-300 p-2"><?= htmlspecialchars($row['bank_name']) ?></td>
                        <td class="border border-gray-300 p-2 text-green-600"><?= number_format($row['amount']) ?> VND</td>
                        <td class="border border-gray-300 p-2">
                            <?php 
                                $status_color = match($row['status']) {
                                    'pending' => 'text-yellow-500',
                                    'processing' => 'text-blue-500',
                                    'completed' => 'text-green-500',
                                    'canceled' => 'text-red-500',
                                    default => 'text-gray-500'
                                };
                            ?>
                            <span class="<?= $status_color ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td class="border border-gray-300 p-2"><?= $row['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-gray-600 mt-4">Bạn chưa có giao dịch nào.</p>
    <?php endif; ?>

</div>

</body>
</html>
