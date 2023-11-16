<?php
require "errors_if_testing.php";
global $config;
require "../../conf.php";
require_once("db_conn.php");
$conn = create_connection($config["DATABASE_SERVER"], $config["DATABASE_USER"], $config["DATABASE_PASS"], $config["DATABASE_NAME"]);
header("Content-Type: application/json; charset=UTF-8");

if (empty($_GET['pass'])) {
    echo "{\"error\": \"Internal error\"}";
    exit();
}
if ($_GET['pass'] != $config["PURGE_PASS"]) {
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