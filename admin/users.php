<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

// Lấy danh sách user
$query = "SELECT id, username, email, balance FROM users ORDER BY id DESC";
$users = $conn->query($query);

// Kiểm tra lỗi SQL
if (!$users) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

// Xử lý cập nhật số dư user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_balance'])) {
    $user_id = intval($_POST['user_id']);
    $balance = intval($_POST['balance']);

    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("ii", $balance, $user_id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold mb-4">Quản lý Người dùng</h2>

        <table class="w-full border-collapse border border-gray-300 bg-white shadow-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Tên</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Số dư</th>
                    <th class="border p-2">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="border p-2"><?= $row['id'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['username']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="border p-2"><?= number_format($row['balance']) ?> VND</td>
                        <td class="border p-2">
                            <button class="bg-yellow-500 text-white px-3 py-1 rounded" onclick="editBalance(<?= $row['id'] ?>, <?= $row['balance'] ?>)">Sửa số dư</button>
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
        function editBalance(id, balance) {
            Swal.fire({
                title: 'Chỉnh sửa số dư',
                html: `
                    <input id="swal-balance" class="swal2-input" placeholder="Số dư" type="number" value="${balance}">
                `,
                showCancelButton: true,
                confirmButtonText: 'Lưu',
                cancelButtonText: 'Hủy',
                preConfirm: () => {
                    const newBalance = parseInt(document.getElementById('swal-balance').value.trim());

                    if (isNaN(newBalance)) {
                        Swal.showValidationMessage('Vui lòng nhập số hợp lệ');
                        return false;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    form.innerHTML = `
                        <input name="edit_balance" value="1">
                        <input name="user_id" value="${id}">
                        <input name="balance" value="${newBalance}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>
