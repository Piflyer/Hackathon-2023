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
function randomPassword() {
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 5; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

$id = random_int(10001, 99999);
$pass = randomPassword();

function tryagain($connection, $id){
    $sql = "SELECT id FROM rooms WHERE id='$id'";
    $result = mysqli_query($connection, $sql);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            tryagain(random_int(10001, 99999));
        }
        else {
            return $id;
        }
    }
    else {
        echo "{\"error\": \"Internal error 1\"}";
        die(1);
    }
}

$id = tryagain($conn, $id);

$sql = "INSERT INTO rooms (id, password, owner) VALUES ('$id', '$pass', '" . $_SESSION['id'] . "')";
$result = mysqli_query($conn, $sql);
if($result) {
    echo "{
        \"id\": \"$id\",
        \"pass\": \"$pass\"
    }";
    exit();
}
else {
    echo "{
        \"error\": \"Internal error 2\"
    }";
    die(1);
}