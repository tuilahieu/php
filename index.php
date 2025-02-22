<?php require $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php" ?>

<?php
    if(isset($_SESSION['user_id'])) {
        header('Location: /pages/dashboard.php');
    };
?>
    <!-- START BODY -->

        <!-- Banner -->
    <section class="w-full">
        <img src="https://minsocial.vn/uploads/images/tet-2022.png" alt="Banner" class="w-full h-auto">
    </section>

    <!-- S1 -->
    <section class="container mx-auto my-10 text-center">
        <h2 class="text-2xl font-bold mb-6">Các nền tảng quảng cáo</h2>
        <div class="grid xl:grid-cols-3 gap-6">
            <!--  -->
            <div class="bg-white px-6 py-8 border rounded-lg shadow-md">
                <div class="w-24 h-24 mx-auto mb-6">
                <img src="/assets/images/facebook.png" alt="FACEBOOK" class="w-full h-full mb-6 object-cover rounded-lg">
                </div>
                <h3 class="text-lg font-bold pb-4">Facebook ADS</h3>
            </div>
            <!--  -->
            <div class="bg-white px-6 py-8 border rounded-lg shadow-md">
                <div class="w-24 h-24 mx-auto mb-6">
                    <img src="/assets/images/tiktok.png" alt="TIKTOK" class="w-full h-full mb-6 object-cover rounded-lg">
                </div>
                <h3 class="text-xl font-semibold">TikTok ADS</h3>
            </div>
            <!--  -->
            <div class="bg-white px-6 py-8 border rounded-lg shadow-md">
                <div class="w-24 h-24 mx-auto mb-6">
                    <img src="/assets/images/youtube.png" alt="YOUTUBE" class="w-full h-full mb-6 object-cover rounded-lg">
                </div>
                <h3 class="text-xl font-semibold">Youtube ADS</h3>
            </div>
        </div>
    </section>


<!-- Giới thiệu tính năng -->
<section class="container mx-auto my-10 text-center">
    <h2 class="text-2xl font-bold mb-6">Tính năng nổi bật</h2>
    <div class="grid xl:grid-cols-3 gap-6">
        <div class="bg-white px-6 py-8 border rounded-lg shadow-md">
            <div class="w-24 h-24 mx-auto mb-6">
                <img src="https://www.fbvip.net/images/gJwXr6FFZKZCGKWaz4RB.png" alt="ORDER" class="w-full h-full mb-6 object-cover rounded-lg">
            </div>
            <h3 class="text-xl font-semibold">Đặt hàng nhanh</h3>
            <p class="text-gray-600">Đặt hàng chỉ với vài cú click.</p>
        </div>
        <div class="bg-white px-6 py-8 border rounded-lg shadow-md">
            <div class="w-24 h-24 mx-auto mb-6">
                <img src="https://www.fbvip.net/images/EfZWQVfV6nQzu2vMmnwC.png" alt="SUPORT" class="w-full h-full mb-6 object-cover rounded-lg">
            </div>
            <h3 class="text-xl font-semibold">Hỗ trợ 24/7</h3>
            <p class="text-gray-600">Luôn sẵn sàng hỗ trợ bạn mọi lúc.</p>
        </div>
        <div class="bg-white px-6 py-8 border rounded-lg shadow-md">
            <div class="w-24 h-24 mx-auto mb-6">
                <img src="https://www.fbvip.net/images/j5C6IQz7gIXPgjFJxmRz.png" alt="PAYMENT" class="w-full h-full mb-6 object-cover rounded-lg">
            </div>
            <h3 class="text-xl font-semibold">Thanh toán online</h3>
            <p class="text-gray-600">Hỗ trợ nhiều phương thức thanh toán.</p>
        </div>
    </div>
</section>
        
    <!-- END BODY -->

    <!-- FOOTER -->
    <?php require $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php" ?> ?>
    </body>

</html>