<?php
require_once('config.php');

if (!isset($_SESSION)) {
    session_start();
}

header('Content-Type: application/json');

$classroom = $_GET['classroom'] ?? '';
$today = date('Y-m-d');

// 查询特定教室在未来28天内的已租借的日期
$query = "SELECT DISTINCT rent_date FROM rental_table WHERE classroom = ? AND rent_status = 'Y' AND rent_date >= ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $classroom, $today);
$stmt->execute();
$result = $stmt->get_result();

$unavailableDates = [];
while ($row = $result->fetch_assoc()) {
    $unavailableDates[] = $row['rent_date'];
}

echo json_encode($unavailableDates);

$stmt->close();
$conn->close();
?>
