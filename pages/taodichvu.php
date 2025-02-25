<?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php" ?>
<?php 

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; 
$stmt = $conn->prepare("SELECT service_code, name, price FROM services");
$stmt->execute();
$result = $stmt->get_result();
$services = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
    <!-- START BODY -->

    <div class="my-24 mx-auto bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
        <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Tạo dv Quảng Cáo</h2>

        <form id="form" method="POST" class="space-y-5">
            <!-- Tên dịch vụ -->
            <div>
                <label for="service_name" class="block font-medium text-gray-700">Tên đơn</label>
                <input type="text" id="service_name" name="service_name" required 
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
            </div>

            <!-- Loại dịch vụ -->
            <div>
                <label for="service_type" class="block font-medium text-gray-700">Loại dịch vụ</label>
                <select id="service_type" name="service_type" required
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
                    <?php foreach ($services as $service): ?>
                        <option value="<?= htmlspecialchars($service['service_code']) ?>">
                            <?= htmlspecialchars($service['name']) ?> (<?= number_format($service['price']) ?> VND / 1)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="amount" class="block font-medium text-gray-700">Số lượng</label>
                <input id="amount" name="amount" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg" type="number" placeholder="Số lượng tối thiểu 500">
            </div>

            <!-- Mô tả dịch vụ -->
            <div>
                <label for="description" class="block font-medium text-gray-700">Ghi chú nếu có</label>
                <textarea id="description" name="description" rows="4"
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"></textarea>
            </div>

            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <p><span id="lastCost"></span></p>
            </div>
            <!-- Nút tạo dịch vụ -->
            <button type="submit" 
                class="w-full bg-blue-500 text-white p-3 rounded-lg hover:bg-blue-600 transition text-lg font-semibold">
                Tạo Dịch Vụ
            </button>
        </form>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const serviceSelect = document.querySelector("#service_type");
        const amountInput = document.querySelector("#amount");
        const lastCostSpan = document.querySelector("#lastCost");

        function calculatePrice() {
            let selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            let pricePerUnitMatch = selectedOption.textContent.match(/\d+/g);
            if (!pricePerUnitMatch) return;

            let pricePerUnit = parseInt(pricePerUnitMatch[0]); // Lấy giá từ option
            let amount = parseInt(amountInput.value) || 0;

            if (amount < 500) {
                lastCostSpan.textContent = "Số lượng tối thiểu là 500";
                lastCostSpan.style.color = "red";
                return;
            }

            let totalCost = pricePerUnit * amount;
            lastCostSpan.textContent = "Giá: " + new Intl.NumberFormat("vi-VN").format(totalCost) + " VND";
            lastCostSpan.style.color = "black";
        }

        serviceSelect.addEventListener("change", calculatePrice);
        amountInput.addEventListener("input", calculatePrice);
    });
</script>



    </body>

</html>


<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php"; 

    $user_id = $_SESSION['user_id'];
    $service_name = trim($_POST['service_name']);
    $service_type = $_POST['service_type'];
    $amount = intval($_POST['amount']); // Lấy số lượng
    $note = trim($_POST['description']);

    if ($amount < 500) {
        die("<script>Swal.fire({
            title: 'Không thành công!',
            text: 'Số lượng tối thiểu là 500!',
            icon: 'error',
        })</script>");
    }

    // Lấy ID và đơn giá của dịch vụ
    $stmt = $conn->prepare("SELECT id, price FROM services WHERE service_code = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("s", $service_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();

    if (!$service) {
        die("Dịch vụ không hợp lệ!");
    }

    $service_id = $service['id'];
    $service_price = $service['price'];

    // Tính tổng giá tiền
    $total_price = $service_price * $amount;

    // Lấy số dư của user
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        die("Không tìm thấy người dùng!");
    }

    $current_balance = $user['balance'];

    // Kiểm tra số dư
    if ($current_balance < $total_price) {
        die("<script>Swal.fire({
            title: 'Không thành công!',
            text: 'Số dư không đủ, vui lòng nạp thêm!',
            icon: 'error',
        })</script>");
    }

    // Cập nhật số dư của user
    $new_balance = $current_balance - $total_price;
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("di", $new_balance, $user_id);
    $stmt->execute();
    $stmt->close();

    // Thêm đơn hàng vào bảng orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, service_id, service_name, status, notes, price) 
                            VALUES (?, ?, ?, 'pending', ?, ?)");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("iissi", $user_id, $service_id, $service_name, $note, $total_price);
    if (!$stmt->execute()) {
        die("Lỗi khi thêm đơn hàng: " . $stmt->error);
    }
    $stmt->close();

    echo("<script>
        Swal.fire({
            title: 'Thành công!',
            text: 'Số dư còn lại: " . number_format($new_balance) . " VND !',
            icon: 'success',
        }).then(() => window.location.href = '/pages/dashboard.php');
        
        </script>");
        
    // header("Location: /pages/dashboard.php");
    exit();
}
?>
