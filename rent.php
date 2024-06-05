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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $jsonData = $_POST['data'];
    $decodeData = json_decode($jsonData, true);
    $username = $_SESSION['username'];
    $classroom = $decodeData['classroom'];
    $rent_date = $decodeData['rent_date'];
    $start_period = $decodeData['start_period'];
    $end_period = $decodeData['end_period'];
    $reason = $decodeData['rent_reason'];
    //get ID auto increment
    $stmt = $conn->prepare("SELECT MAX(ID) FROM rental_table");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $rent_id = $row['MAX(ID)'] + 1;

    $conn->begin_transaction();
    // Insert into rent_data
    for ($i = $start_period; $i <= $end_period; $i++) {
        $stmt = $conn->prepare("INSERT INTO rental_table (ID, username, classroom, rent_date, rent_period, reason) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $rent_id, $username, $classroom, $rent_date, $i, $reason);
        $stmt->execute();

    }
    $conn->commit();
    $conn->close();
}





?>