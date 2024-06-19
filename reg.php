<?php
$conn = require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $std_id = $_POST['std_id'];
    $std_name = $_POST['std_name'];
    $std_departments = $_POST['std_departments'];
    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];
    $confirm_password = $_POST['confirm_password'];
    $is_admin = "N";
    
    // Validate input
    if ($user_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash password
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert student information
        $stmt = $conn->prepare("INSERT INTO student_table (std_id, std_name, std_departments) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $std_id, $std_name, $std_departments);
        $stmt->execute();
        $stmt->close();

        // Insert user information
        $stmt = $conn->prepare("INSERT INTO user_data (user_name, user_password, is_admin) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_name, $hashed_password, $is_admin);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Establish association
        $stmt = $conn->prepare("INSERT INTO student_account (user_id, std_id) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $std_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();
        // 跳出提示框 click ok 跳轉到 login.php
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    
        
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        // 跳出提示框 click ok 跳轉到 register.php
        echo "<script>alert('Registration failed!'); window.location.href='register.php';</script>";
    }

    $conn->close();
} else {
    die("Invalid request method.");
}