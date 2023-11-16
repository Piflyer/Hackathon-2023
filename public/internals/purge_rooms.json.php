<?php
require "errors_if_testing.php";
require "db_conn.php";
global $conn;
header("Content-Type: application/json; charset=UTF-8");
try {
    $env = parse_ini_file('/home/sid/cd/.env');
} catch (Exception) {
    echo "{\"error\": \"Internal error\"}";
    exit();
}
if(empty($_GET['pass'])) {
    echo "{\"error\": \"Internal error\"}";
    exit();
}
if($_GET['pass'] != $env["PURGE_PASS"]) {
    echo "{\"error\": \"Internal error\"}";
    exit();
}

$sql = "DELETE FROM rooms WHERE last_edited < NOW() - INTERVAL 3 HOUR";
$result = mysqli_query($conn, $sql);
if ($result) {
    echo "{\"success\": \"Rooms deleted\"}";
    exit();
} else {
    echo "{\"error\": \"Internal error\"}";
    exit();
}