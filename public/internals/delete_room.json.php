<?php
require "errors_if_testing.php";
header("Content-Type: application/json; charset=UTF-8");
session_start();
global $config;
require "../../conf.php";
require_once("db_conn.php");
$conn = create_connection($config["DATABASE_SERVER"], $config["DATABASE_USER"], $config["DATABASE_PASS"], $config["DATABASE_NAME"]);
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
        if ($row['owner'] !== $_SESSION['id']) {
            echo "{\"error\": \"You are not the owner of this room\"}";
            exit();
        } else {
            $sql = "DELETE FROM rooms WHERE id='" . $_GET['room'] . "'";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                echo "{\"success\": \"Room deleted\"}";
                exit();
            } else {
                echo "{\"error\": \"Internal error\"}";
                exit();
            }
        }
    }
} else {
    echo "{\"error\": \"Internal error\"}";
    exit();
}