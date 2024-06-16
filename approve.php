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
    $rental_updates = $_POST['status'];
    $approved = array();

    // First, process each rental to check for conflicts
    foreach ($rental_updates as $create_time => $status) {
        $create_time = $conn->real_escape_string($create_time);

        // Get rental details from the database
        $sql = "SELECT * FROM rental_table WHERE create_time='$create_time'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if ($row) {
            $classroom = $row['classroom'];
            $rent_date = $row['rent_date'];
            $rent_period = $row['rent_period'];

            if ($status == 'Y') {
                // Check for conflicts in the current approval list
                $conflict = false;
                foreach ($approved as $approved_rental) {
                    if (
                        $approved_rental['classroom'] == $classroom &&
                        $approved_rental['rent_date'] == $rent_date &&
                        $approved_rental['rent_period'] == $rent_period
                    ) {
                        $conflict = true;
                        break;
                    }
                }

                if (!$conflict) {
                    // If no conflict, approve this rental and add to the approved list
                    $approved[$create_time] = array(
                        'classroom' => $classroom,
                        'rent_date' => $rent_date,
                        'rent_period' => $rent_period,
                    );
                    $sql_update = "UPDATE rental_table SET rent_status='Y' WHERE create_time='$create_time'";
                    $conn->query($sql_update);
                } else {
                    // If conflict, set this rental to 'N'
                    $sql_update = "UPDATE rental_table SET rent_status='N' WHERE create_time='$create_time'";
                    $conn->query($sql_update);
                }
            } else {
                // If the status is not 'Y', update it accordingly
                $sql_update = "UPDATE rental_table SET rent_status='$status' WHERE create_time='$create_time'";
                $conn->query($sql_update);
            }
        }
    }
}

$conn->close();

header("Location: admin_center.php");
exit();
?>