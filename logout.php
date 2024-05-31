<?php 
////logout.php(必要！瀏覽器會記錄登入過的使用者，除非你去清除cookie)
//可將變數儲存在session
session_start(); 
//將 session 陣列清除
$_SESSION = array(); 
//將所有 session 清除
session_destroy(); 
// pop up 訊息
echo "<script>alert('登出成功!'); </script>";
// 跳轉至首頁
header("refresh:0;url=index.php");
exit;
?>