<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";


// Xử lý thêm dịch vụ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $service_code = $_POST['service_code'];
    $name = $_POST['name'];
    $price = $_POST['price'];

    if (!empty($name) && is_numeric($price)) {
        // Kiểm tra xem service_code đã tồn tại chưa
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM services WHERE service_code = ?");
        $check_stmt->bind_param("s", $service_code);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            die("Lỗi: Mã dịch vụ đã tồn tại, vui lòng nhập mã khác!");
        }

        // Nếu không bị trùng, tiến hành thêm mới
        $stmt = $conn->prepare("INSERT INTO services (service_code, name, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $service_code, $name, $price);
        
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    }
    // Thêm thông báo vào bảng admin_notice
    $notice = "Thêm dịch vụ mới: " . htmlspecialchars($name) . " giá: " .number_format($price) . ' VNĐ';
    $stmt = $conn->prepare("INSERT INTO admin_notice (message) VALUES (?)");
    $stmt->bind_param("s", $notice);
    $stmt->execute();
    $stmt->close();

    header("Location: services.php");
    exit();
}


// Xử lý cập nhật dịch vụ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_service'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];

    if (!empty($name) && is_numeric($price)) {
        $stmt = $conn->prepare("UPDATE services SET name = ?, price = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $price, $id);
        $stmt->execute();
        // Thêm thông báo vào bảng admin_notice
        $notice = "Cập nhật dịch vụ: " . htmlspecialchars($name) . " giá: " .number_format($price) . ' VNĐ';
        $stmt = $conn->prepare("INSERT INTO admin_notice (message) VALUES (?)");
        $stmt->bind_param("s", $notice);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: services.php");
    exit();
}

// Xử lý xóa dịch vụ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_service'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: services.php");
    exit();
}

// Lấy danh sách dịch vụ
$query = "SELECT * FROM services ORDER BY id DESC";
$services = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Dịch vụ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Quản lý Dịch vụ</h2>

    <!-- Form thêm dịch vụ -->
    <form method="POST" class="mb-6 p-4 bg-white shadow rounded">
        <h3 class="text-xl font-semibold mb-2">Thêm dịch vụ</h3>
        <div class="flex space-x-4">
            <input type="text" name="name" placeholder="Tên dịch vụ" class="p-2 border rounded w-1/3" required>
            <input type="number" name="price" placeholder="Giá" class="p-2 border rounded w-1/3" required>
            <input type="text" name="service_code" placeholder="Mã dịch vụ (Tên DV + _ADS)" class="p-2 border rounded w-1/3" required>
            <button type="submit" name="add_service" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Thêm
            </button>
        </div>
    </form>

    <!-- Bảng danh sách dịch vụ -->
    <table class="w-full bg-white shadow rounded border">
        <thead>
            <tr class="bg-gray-200">
                <th class="border p-2">ID</th>
                <th class="border p-2">Tên dịch vụ</th>
                <th class="border p-2">Giá</th>
                <th class="border p-2">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $services->fetch_assoc()) : ?>
                <tr>
                    <td class="border p-2"><?= $row['id'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="border p-2"><?= number_format($row['price']) ?> VND</td>
                    <td class="border p-2 text-center">
                        <!-- Nút sửa -->
                        <button onclick="editService(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', <?= $row['price'] ?>)"
                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                            Sửa
                        </button>

                        <!-- Nút xoá -->
                        <button onclick="deleteService(<?= $row['id'] ?>)"
                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                            Xóa
                        </button>
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

<!-- Form ẩn để sửa dịch vụ -->
<form id="editForm" method="POST" class="hidden">
    <input type="hidden" name="id" id="edit_id">
    <input type="hidden" name="name" id="edit_name">
    <input type="hidden" name="price" id="edit_price">
    <input type="hidden" name="edit_service">
</form>

<!-- Form ẩn để xóa dịch vụ -->
<form id="deleteForm" method="POST" class="hidden">
    <input type="hidden" name="id" id="delete_id">
    <input type="hidden" name="delete_service">
</form>

<script>
// Hàm sửa dịch vụ
function editService(id, name, price) {
    Swal.fire({
        title: "Chỉnh sửa dịch vụ",
        html: `
            <input id="swal_name" class="swal2-input" placeholder="Tên dịch vụ" value="${name}">
            <input id="swal_price" class="swal2-input" type="number" placeholder="Giá" value="${price}">
        `,
        showCancelButton: true,
        confirmButtonText: "Cập nhật",
        preConfirm: () => {
            const newName = document.getElementById("swal_name").value;
            const newPrice = document.getElementById("swal_price").value;
            if (!newName || isNaN(newPrice)) {
                Swal.showValidationMessage("Vui lòng nhập đầy đủ thông tin!");
            } else {
                document.getElementById("edit_id").value = id;
                document.getElementById("edit_name").value = newName;
                document.getElementById("edit_price").value = newPrice;
                document.getElementById("editForm").submit();
            }
        }
    });
}

// Hàm xoá dịch vụ
function deleteService(id) {
    Swal.fire({
        title: "Xóa dịch vụ?",
        text: "Bạn có chắc chắn muốn xóa dịch vụ này?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Xóa",
        cancelButtonText: "Hủy",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("delete_id").value = id;
            document.getElementById("deleteForm").submit();
        }
    });
}
</script>

</body>
</html>
