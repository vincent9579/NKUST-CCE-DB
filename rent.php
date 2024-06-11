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

    $conn->begin_transaction();

    $A = "A";

    if (($start_period == "A") && ($end_period == "A")) {
        $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $classroom, $rent_date, $A, $reason);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // 1062 is the error code for duplicate entry
                http_response_code(500);
                echo json_encode(array("error" => "Duplicate entry"));
                $conn->rollback();
                exit();
            } else {
                throw $e; // rethrow the exception if it's not a duplicate entry error
            }
        }
    } else {
        if ($start_period == 4 && $end_period == 5) {

            $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $classroom, $rent_date, $A, $reason);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) { // 1062 is the error code for duplicate entry
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e; // rethrow the exception if it's not a duplicate entry error
                }
            }
        }
        if ($end_period == "A") {
            $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $classroom, $rent_date, $end_period, $reason);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) { // 1062 is the error code for duplicate entry
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e; // rethrow the exception if it's not a duplicate entry error
                }
            }
            $end_period = "4";
        }
        if ($start_period == "A") {
            $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $classroom, $rent_date, $start_period, $reason);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) { // 1062 is the error code for duplicate entry
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e; // rethrow the exception if it's not a duplicate entry error
                }
            }
            $start_period = "5";
        }
        // Insert into rent_data
        for ($i = $start_period; $i <= $end_period; $i++) {
            $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $classroom, $rent_date, $i, $reason);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) { // 1062 is the error code for duplicate entry
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e; // rethrow the exception if it's not a duplicate entry error
                }
            }
        }
    }
    $conn->commit();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $create_time = $_GET['create_time'];
    $stmt = $conn->prepare("DELETE FROM rental_table WHERE create_time = ?");
    $stmt->bind_param("s", $create_time);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: rental_record.php");
}

?>