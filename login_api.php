<?php
$conn = require_once "config.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收 Post 訊息: 使用者名稱/密碼，儲存為變數 $username / $password
    $username = $_POST["username"];
    $password = $_POST["password"];

    // 檢查用戶名是否存在
    $stmt = $conn->prepare("SELECT * FROM user_data WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // 驗證密碼
        if (password_verify($password, $row["user_password"])) {
            // 將 user_id 和 user_name 儲存至 session，可攜帶至 welcome.php
            $_SESSION["id"] = $row["user_id"];
            $_SESSION["username"] = $row["user_name"];
            $_SESSION["is_admin"] = $row["is_admin"];
            message_success("登入成功! (Password Is Valid!)");
        } else {
            message_alert("帳號或密碼錯誤 (Invalid Password)");
        }
    } else {
        message_alert("帳號或密碼錯誤 (Invalid Username)");
    }
    
    $stmt->close();
}

// 關閉 DB 連接
mysqli_close($conn);

function message_alert($message) {
    echo "<script>alert('$message'); window.location.href='login.php';</script>";
    return true;
}

function message_success($message) {
    echo "<script>alert('$message'); window.location.href='index.php';</script>";
    return true;
}
?>
