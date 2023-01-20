<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "db_conn.php";
global $conn;
header("Content-Type: application/json; charset=UTF-8");
if(empty($_GET['pass'])) {
    echo "{\"error\": \"Internal error\"}";
    exit();
}
if($_GET['pass'] != file_get_contents("../assets/secret.txt")) {
    echo "{\"error\": \"Internal error\"}";
    exit();
}

$sql = "DELETE FROM rooms WHERE last_edited < NOW() - INTERVAL 3 HOUR";
$result = mysqli_query($conn, $sql);
if ($result) {
    echo "{\"success\": \"Room deleted\"}";
    exit();
} else {
    echo "{\"error\": \"Internal error\"}";
    exit();
}