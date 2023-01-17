<?php

$sname = "localhost";
$unmae = "root";
$password = "";

$db_name = "metaverse";

global $conn;
$conn = mysqli_connect($sname, $unmae, $password, $db_name);

if (!$conn) {
    echo "Connection failed!";
    die(1);
}