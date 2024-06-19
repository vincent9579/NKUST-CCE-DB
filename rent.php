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

    // Check if the requested date and classroom are already booked
    $query = "SELECT * FROM rental_table WHERE classroom = ? AND rent_date = ? AND rent_status = 'Y'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $classroom, $rent_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(array("error" => "The requested date and classroom are already booked."));
        $conn->rollback();
        exit();
    }

    // Proceed with the existing insertion logic...
    if (($start_period == "A") && ($end_period == "A")) {
        $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $classroom, $rent_date, $A, $reason);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                http_response_code(500);
                echo json_encode(array("error" => "Duplicate entry"));
                $conn->rollback();
                exit();
            } else {
                throw $e;
            }
        }
    } else {
        if ($start_period == 4 && $end_period == 5) {
            $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $classroom, $rent_date, $A, $reason);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e;
                }
            }
        }
        if ($end_period == "A") {
            $stmt = $conn->prepare("INSERT INTO rental_table (username, classroom, rent_date, rent_period, reason) VALUES ( ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $classroom, $rent_date, $end_period, $reason);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e;
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
                if ($e->getCode() == 1062) {
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e;
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
                if ($e->getCode() == 1062) {
                    http_response_code(500);
                    echo json_encode(array("error" => "Duplicate entry"));
                    $conn->rollback();
                    exit();
                } else {
                    throw $e;
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
