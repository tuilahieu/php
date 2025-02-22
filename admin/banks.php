<?php
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
require $_SERVER['DOCUMENT_ROOT'] . "/admin/auth/security.php";

// Fetch danh sách ngân hàng từ API VietQR
function getBanksFromAPI() {
    $curl = curl_init("https://api.vietqr.io/v2/banks");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    $banks = $data['data'] ?? [];

    // Thêm MoMo vào danh sách ngân hàng
    $banks[] = [
        "id" => "momo",
        "shortName" => "MoMo",
        "name" => "Ví MoMo",
        "code" => "momo",
        "bin" => "MOMO",
        "logo" => "https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png",
        "swiftCode" => "MOMO",
    ];

    return $banks;
}

$bankList = getBanksFromAPI();

// Xử lý thêm ngân hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_bank'])) {
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_holder = $_POST['account_holder'];

    $stmt = $conn->prepare("INSERT INTO admin_banks (bank_name, account_number, account_holder) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $bank_name, $account_number, $account_holder);
    
    if ($stmt->execute()) {
        $stmt->close();
        // Thêm thông báo vào bảng admin_notice
        $notice = "Thêm ngân hàng mới: " . htmlspecialchars($bank_name) . ' - '. htmlspecialchars($account_number);
        $stmt = $conn->prepare("INSERT INTO admin_notice (message) VALUES (?)");
        $stmt->bind_param("s", $notice);
        $stmt->execute();
        $stmt->close();
        echo "<script>Swal.fire('Thành công!', 'Đã thêm ngân hàng.', 'success').then(() => window.location.reload());</script>";
    } else {
        echo "<script>Swal.fire('Lỗi!', 'Không thể thêm ngân hàng.', 'error');</script>";
    }
    // $stmt->close();
}

// Xử lý sửa ngân hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_bank'])) {
    $bank_id = $_POST['bank_id'];
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_holder = $_POST['account_holder'];

    $stmt = $conn->prepare("UPDATE admin_banks SET bank_name=?, account_number=?, account_holder=? WHERE id=?");
    $stmt->bind_param("sssi", $bank_name, $account_number, $account_holder, $bank_id);
    
    if ($stmt->execute()) {
        echo "<script>Swal.fire('Thành công!', 'Cập nhật thành công.', 'success').then(() => window.location.reload());</script>";
    } else {
        echo "<script>Swal.fire('Lỗi!', 'Không thể cập nhật.', 'error');</script>";
    }
    $stmt->close();
}

// Xử lý xóa ngân hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_bank'])) {
    $bank_id = $_POST['bank_id'];

    $stmt = $conn->prepare("DELETE FROM admin_banks WHERE id=?");
    $stmt->bind_param("i", $bank_id);
    
    if ($stmt->execute()) {
        echo "<script>Swal.fire('Thành công!', 'Ngân hàng đã bị xóa.', 'success').then(() => window.location.reload());</script>";
    } else {
        echo "<script>Swal.fire('Lỗi!', 'Không thể xóa.', 'error');</script>";
    }
    $stmt->close();
}

// Lấy danh sách ngân hàng trong hệ thống
$query = "SELECT * FROM admin_banks ORDER BY id DESC";
$banks = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Ngân hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Quản lý Ngân hàng</h2>

    <!-- Form Thêm Ngân Hàng -->
    <form method="POST" class="bg-white p-4 rounded shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Thêm Ngân hàng</h3>

        <label class="block text-gray-700 font-medium">Chọn Ngân hàng:</label>
        <select name="bank_name" class="w-full p-2 border rounded mb-2" required>
            <option value="" disabled selected>Chọn ngân hàng</option>
            <?php foreach ($bankList as $bank) : ?>
                <option value="<?= htmlspecialchars($bank['shortName']) ?>">
                    <?= htmlspecialchars($bank['name']) ?> (<?= htmlspecialchars($bank['shortName']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label class="block text-gray-700 font-medium">Số tài khoản:</label>
        <input type="text" name="account_number" required class="w-full p-2 border rounded mb-2">

        <label class="block text-gray-700 font-medium">Chủ tài khoản:</label>
        <input type="text" name="account_holder" required class="w-full p-2 border rounded mb-2">

        <button type="submit" name="add_bank" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
            Thêm Ngân Hàng
        </button>
    </form>

    <!-- Bảng Danh Sách Ngân Hàng -->
    <table class="w-full border-collapse border border-gray-300 bg-white">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">ID</th>
                <th class="border p-2">Tên ngân hàng</th>
                <th class="border p-2">Số tài khoản</th>
                <th class="border p-2">Chủ tài khoản</th>
                <th class="border p-2">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $banks->fetch_assoc()) : ?>
                <tr>
                    <td class="border p-2"><?= $row['id'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($row['bank_name']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($row['account_number']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($row['account_holder']) ?></td>
                    <td class="border p-2">
                        <button onclick="editBank(<?= $row['id'] ?>, '<?= htmlspecialchars($row['bank_name']) ?>', '<?= htmlspecialchars($row['account_number']) ?>', '<?= htmlspecialchars($row['account_holder']) ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded mr-2">Sửa</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_bank" value="1">
                            <input type="hidden" name="bank_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded" onclick="return confirm('Bạn có chắc muốn xóa ngân hàng này?');">Xóa</button>
                        </form>
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
function editBank(id, bank_name, account_number, account_holder) {
    Swal.fire({
        title: 'Sửa Ngân hàng',
        html: `
            <input id="bank_name" class="swal2-input" placeholder="Tên ngân hàng" value="${bank_name}">
            <input id="account_number" class="swal2-input" placeholder="Số tài khoản" value="${account_number}">
            <input id="account_holder" class="swal2-input" placeholder="Chủ tài khoản" value="${account_holder}">
        `,
        showCancelButton: true,
        confirmButtonText: 'Lưu',
        preConfirm: () => {
            return {
                bank_id: id,
                bank_name: document.getElementById('bank_name').value,
                account_number: document.getElementById('account_number').value,
                account_holder: document.getElementById('account_holder').value,
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="edit_bank" value="1">
                              <input type="hidden" name="bank_id" value="${result.value.bank_id}">
                              <input type="hidden" name="bank_name" value="${result.value.bank_name}">
                              <input type="hidden" name="account_number" value="${result.value.account_number}">
                              <input type="hidden" name="account_holder" value="${result.value.account_holder}">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

</body>
</html>
