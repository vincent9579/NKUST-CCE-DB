<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    $status = "invalid";
    $is_admin = "N";
} else {
    $status = "valid";
    $is_admin = $_SESSION['is_admin'];
}

$conn = require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['status'] as $create_time => $status) {
        $create_time = $conn->real_escape_string($create_time);
        $status = $conn->real_escape_string($status);
        $sql = "UPDATE rental_table SET rent_status='$status' WHERE create_time='$create_time'";
        $conn->query($sql);
    }
}

$conn->close();
header("Location: admin_center.php");
exit();
?>