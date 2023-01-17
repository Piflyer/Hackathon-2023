<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");
session_start();
require "db_conn.php";
if (!(isset($_SESSION['id']) && isset($_SESSION['user_name']))) {
    echo "{\"error\": \"Not logged in\"}";
    exit();
}

if(empty($_GET['id'])) {
    echo "{\"error\": \"No room ID provided\"}";
    exit();
}

$sql = "SELECT * FROM rooms WHERE id='" . $_GET['id'] . "'";
$result = mysqli_query($conn, $sql);
if($result) {
    if (mysqli_num_rows($result) === 0) {
        echo "{\"error\": \"Room not found\"}";
        exit();
    } else {
        $row = mysqli_fetch_assoc($result);
        if ($row['owner'] !== $_SESSION['id']) {
            echo "{\"error\": \"You are not the owner of this room\"}";
            exit();
        } else {
            $sql = "DELETE FROM rooms WHERE id='" . $_GET['id'] . "'";
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