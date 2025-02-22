
<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/connect/database.php";
unset($_SESSION['user_id']);

header('Location: /');