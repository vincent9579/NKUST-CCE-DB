<!DOCTYPE html>
<html>
<head>
    <!-- 會員註冊 -->
    <meta charset='utf-8'>
    <title>會員註冊</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link href="css/web.css" rel="stylesheet">
    <script>
        function validateForm() {
            var password = document.forms["registerForm"]["password"].value;
            var passwordCheck = document.forms["registerForm"]["password_check"].value;
            if (password.length < 6) {
                alert("密碼長度不足，至少需要6個字符");
                return false;
            }
            if (password !== passwordCheck) {
                alert("請確認密碼是否輸入正確");
                return false;
            }
        }
    </script>
</head>
<body>
    <h1>會員註冊</h1>
    <form name="registerForm" method="post" action="register.php" onsubmit="return validateForm()">
        帳號：<input type="text" name="username" required><br/><br/>
        密碼：<input type="password" name="password" id="password" required><br/><br/>
        確認密碼：<input type="password" name="password_check" id="password_check" required><br/><br/>
        <input type="submit" value="註冊" name="submit">
        <input type="reset" value="重設" name="reset">
        <input type="button" onclick="location.href='index.php'" value="上一頁">
    </form>
    <?php 
    session_start();
    $conn = require_once("config.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // 密碼哈希處理

        $stmt = $conn->prepare("SELECT * FROM user_data WHERE user_name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO user_data (user_name, user_password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $passwordHash);
            if ($stmt->execute()) {
                echo "<br><br>註冊成功! 3秒後將自動跳轉頁面<br>";
                echo "<br><a href='index.php'>未成功跳轉頁面請點擊此</a>";
                header("refresh:3;url=index.php");
                exit;
            } else {
                echo "<br>資料庫錯誤: " . $conn->error;
            }
        } else {
            echo "<br><br>該帳號已有人使用! 3秒後將自動跳轉頁面<br>";
            echo "<br><a href='register.php'>未成功跳轉頁面請點擊此</a>";
            header("refresh:3;url=register.php");
            exit;
        }
        $stmt->close();
    }

    $conn->close();
    ?>
</body>
</html>
