<?php 
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