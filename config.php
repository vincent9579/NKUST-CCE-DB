<?php
define('DB_SERVER', 'localhost'); // DB server location
define('DB_USERNAME', 'root'); // DB username
define('DB_PASSWORD', ''); // DB password
define('DB_NAME', 'final_project'); // DB name

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_query($link, 'SET NAMES utf8');
if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
} else {
    return $link;
}
?>