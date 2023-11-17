<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
function randomPassword(): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 5; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function randomRoom(): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyz0123456789';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        if ($i != 2) $pass[] = '-';
    }

    return implode($pass); //turn the array into a string
}

$id = randomRoom();
$pass = randomPassword();

/**
 * @throws Exception
 */
function tryagain($connection, $id)
{
    $sql = "SELECT id FROM rooms WHERE id='$id'";
    $result = mysqli_query($connection, $sql);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            return tryagain($connection, random_int(10001, 99999));
        } else {
            return $id;
        }
    } else {
        echo "{\"error\": \"Internal error\"}";
        die(1);
    }
}

try {
    $id = tryagain($conn, $id);
} catch (Exception $e) {
    echo "{
        \"error\": \"Internal error\"
    }";
    die(1);
}

$sql = "INSERT INTO rooms (id, password, owner, inside) VALUES ('$id', '$pass', '" . $_SESSION['id'] . "', \"[\\\"" . $_SESSION['id'] . "\\\"]\")";
$result = mysqli_query($conn, $sql);
if ($result) {
    echo "{
        \"id\": \"$id\",
        \"pass\": \"$pass\"
    }";
    exit();
} else {
    echo "{
        \"error\": \"Internal error\"
    }";
    die(1);
}