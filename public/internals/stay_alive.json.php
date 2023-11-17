<?php
require "errors_if_testing.php";
session_start();
global $config;
require "../../conf.php";
require_once("db_conn.php");
$conn = create_connection($config["DATABASE_SERVER"], $config["DATABASE_USER"], $config["DATABASE_PASS"], $config["DATABASE_NAME"]);
header("Content-Type: application/json; charset=UTF-8");
if (!(isset($_SESSION['id']) && isset($_SESSION['user_name']))) {
    echo "{\"error\": \"Not logged in\"}";
    exit();
}
if (empty($_GET['room'])) {
    echo "{\"error\": \"No room ID provided\"}";
    exit();
}
$sql = "SELECT * FROM rooms WHERE id='" . $_GET['room'] . "'";
$result = mysqli_query($conn, $sql);
if ($result) {
    if (mysqli_num_rows($result) === 0) {
        echo "{\"error\": \"Room not found\"}";
        exit();
    } else {
        $row = mysqli_fetch_assoc($result);
        $inside = (array)json_decode($row['inside']);
        if (!in_array($_SESSION['id'], $inside)) {
            echo "{\"error\": \"You are not in this room\"}";
            exit();
        }
    }
} else {
    echo "{\"error\": \"Internal error\"}";
    exit();
}

$_GET['room'] = mysqli_escape_string($conn, $_GET['room']);
$sql = "UPDATE rooms SET last_edited=NOW() WHERE id='" . $_GET['room'] . "'";
$result = mysqli_query($conn, $sql);
if ($result) {
    echo "{\"success\": \"Room updated\"}";
    exit();
} else {
    echo "{\"error\": \"Internal error\"}";
    exit();
}