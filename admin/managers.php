<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin admin hiện tại
$admin_id = $_SESSION['admin_id'];
$current_admin = $conn->query("SELECT * FROM admin WHERE id = $admin_id")->fetch_assoc();
$is_superadmin = $current_admin['role'] === 'superadmin';

// Nếu có yêu cầu xóa
if (isset($_GET['delete_id']) && $is_superadmin) {
    $delete_id = intval($_GET['delete_id']);

    // Không cho phép xóa chính mình hoặc Super Admin khác
    $target_admin = $conn->query("SELECT * FROM admin WHERE id = $delete_id")->fetch_assoc();
    if ($target_admin && $target_admin['role'] !== 'superadmin' && $delete_id !== $admin_id) {
        $conn->query("DELETE FROM admin WHERE id = $delete_id");
        header("Location: managers.php?deleted=1");
        exit();
    } else {
        header("Location: managers.php?error=1");
        exit();
    }
}

// Lấy danh sách tất cả admin
$admins = $conn->query("SELECT id, name, email, username, role FROM admin");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Quản lý tài khoản</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">📋 Quản lý tài khoản</h2>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($_GET['deleted'])): ?>
            <script>
                Swal.fire("Đã xóa!", "Tài khoản đã được xóa thành công.", "success");
            </script>
        <?php elseif (isset($_GET['error'])): ?>
            <script>
                Swal.fire("Lỗi!", "Bạn không thể xóa tài khoản này.", "error");
            </script>
        <?php endif; ?>

        <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-300">
                <tr>
                    <th class="border p-3">Tên</th>
                    <th class="border p-3">Email</th>
                    <th class="border p-3">Username</th>
                    <th class="border p-3">Vai trò</th>
                    <th class="border p-3">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $admins->fetch_assoc()): ?>
                    <tr class="bg-white hover:bg-gray-200">
                        <td class="border p-3"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="border p-3"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="border p-3"><?= htmlspecialchars($row['username']) ?></td>
                        <td class="border p-3">
                            <?= $row['role'] === 'superadmin' ? "Super Admin 🏆" : "Quản lý" ?>
                        </td>
                        <td class="border p-3">

                            <?php if ($is_superadmin && $row['role'] !== 'superadmin' && $row['id'] !== $admin_id): ?>
                                <button onclick="confirmDelete(<?= $row['id'] ?>)"
                                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition ml-2">
                                    Xóa
                                </button>
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

    <script>
        function confirmDelete(adminId) {
            Swal.fire({
                title: "Bạn có chắc không?",
                text: "Sau khi xóa, tài khoản này không thể khôi phục!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Xóa ngay!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "managers.php?delete_id=" + adminId;
                }
            });
        }
    </script>
</body>
</html>
