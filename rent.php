<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    $status = "invalid";
} else {
    $status = "valid";
    $is_admin = $_SESSION['is_admin'];
}

$conn = require_once ('config.php');

$classroom = $_POST['classroom'];
$std_name = $_POST['std_name'];
$rent_date = $_POST['rent_date'];
$rent_time = $_SESSION['rent_time'];
$rent_reason = $_POST['rent_reason'];

echo $classroom;
echo $std_name;
echo $rent_date;
echo $rent_time;
echo $rent_reason;

?>