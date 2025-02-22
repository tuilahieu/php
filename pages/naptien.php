<?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php" ?>
<?php 
require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; 

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
?>

<div class="mx-auto mt-6 bg-white p-6 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Nạp Tiền</h2>

        <!-- Hiển thị số dư -->
        <div class="mb-4 text-center">
            <p class="text-lg text-gray-600">Số dư hiện tại:</p>
            <p class="text-2xl font-semibold text-green-600"><?=number_format($user['balance']) . ' VND'?></p>
        </div>

        <!-- Form nạp tiền -->
        <form method="POST">
            <!-- Nhập số tiền -->
            <label class="block text-gray-700 font-medium mb-1">Chọn mốc nạp tiền: </label>
            <select name="amount" class="w-full p-2 border rounded mb-4">
                <option value="10000">10.000đ</option>
                <option value="20000">20.000đ</option>
                <option value="50000">50.000đ</option>
                <option value="100000">100000đ</option>
                <option value="200000">200.000đ</option>
                <option value="10000000">10.000.000đ</option>
            </select>

            <!-- Chọn ngân hàng -->
        <label class="block text-gray-700 font-medium mb-1">Chọn ngân hàng: </label>
        <select name="bank_id" class="w-full p-2 border rounded mb-4">
            <?php
            // Lấy danh sách ngân hàng từ database
            $query = "SELECT id, bank_name, account_number FROM admin_banks";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . 
                         htmlspecialchars($row['bank_name']) . ' - ' . 
                         htmlspecialchars($row['account_number']) . 
                         '</option>';
                }
            } else {
                echo '<option value="null">Bảo trì nạp tiền.</option>';
            }
            ?>
        </select>

            <!-- Nút nạp tiền -->
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
                Tạo mã nạp tiền
            </button>
        </form>
    </div>

</body>
</html>

<?php

// xử lí tạo mã QR

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['amount']) && isset($_POST['bank_id'])) {
        $amount = intval($_POST['amount']);
        $bank_id = intval($_POST['bank_id']);

        // Lấy thông tin ngân hàng từ database
        $stmt = $conn->prepare("SELECT bank_name, account_number, account_holder FROM admin_banks WHERE id = ?");
        $stmt->bind_param("i", $bank_id);
        $stmt->execute();
        $bank = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($bank) {
            $bank_name = strtolower(str_replace(" ", "", $bank['bank_name'])); // Chuẩn hóa tên ngân hàng
            $account_number = $bank['account_number'];
            $account_holder = urlencode($bank['account_holder']);
            $username = urlencode("Nạp tiền cho tài khoản " . $user['username']);

            $transaction_code = strtoupper(uniqid("NAP"));

            // Chèn dữ liệu vào bảng transactions
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, bank_id, amount, transaction_code, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiis", $_SESSION['user_id'], $bank_id, $amount, $transaction_code);
            $stmt->execute();
            $stmt->close();

            // Tạo URL ảnh QR VietQR
            $qr_url = "https://img.vietqr.io/image/{$bank_name}-{$account_number}-compact2.jpg?amount={$amount}&addInfo={$transaction_code}";
            $formatted_amount = number_format($amount);
            // Hiển thị giao diện nạp tiền
            echo <<<HTML
            <div class="w-1/2 mx-auto mt-6 px-12 bg-red-500 text-white p-3 rounded-md">
                <p class='font-bold'>Chú ý: Vui lòng chọn đúng mệnh giá nạp. Cố tình nạp sai mệnh giá sẽ mất tiền.</p>
            </div>
            <div class="w-1/2 mx-auto mt-6 px-12 flex flex-col md:flex-row items-center justify-between bg-white p-6 rounded-lg shadow-md">
                
                <!-- Bên trái: Thông tin tài khoản -->
                <div class="w-full md:w-1/2 pr-4">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Thông tin chuyển khoản</h3>
                    <p class="text-lg text-gray-700"><strong>Ngân hàng:</strong> {$bank['bank_name']}</p>
                    <p class="text-lg text-gray-700"><strong>Số tài khoản:</strong> {$account_number}</p>
                    <p class="text-lg text-gray-700"><strong>Tên:</strong> {$bank['account_holder']}</p>
                    <p class="text-lg text-gray-700"><strong>Số tiền:</strong> {$formatted_amount} VND</p>
                    <p class="text-lg text-gray-700"><strong>Nội dung:</strong> {$transaction_code}</p>
                </div>

                <!-- Bên phải: Mã QR -->
                <div class="w-full md:w-1/2 flex justify-center">
                    <div class="text-center">
                        <p class="text-lg font-medium text-gray-700 mb-2">Quét mã QR để nạp tiền nhanh:</p>
                        <img src="{$qr_url}" alt="QR Code" class="border rounded-lg shadow-lg max-w-xs" />
                    </div>
                </div>

            </div>
            HTML;
        } else {
            echo "<p class='text-red-500 text-center mt-4'>Ngân hàng không tồn tại.</p>";
        }
    }
}
