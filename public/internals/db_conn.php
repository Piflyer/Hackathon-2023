<?php
try {
    $env = parse_ini_file('/home/sid/cd/.env');
} catch (Exception) {
    $env = [];
}
$sname = $env["DATABASE_SERVER"] ?? "localhost";
$unmae = $env["DATABASE_USER"] ?? "root";
$password = $env["DATABASE_PASS"] ?? "";
$db_name = $env["DATABASE_NAME"] ?? "metaverse";

global $conn;
try {
    $conn = mysqli_connect($sname, $unmae, $password, $db_name);
} catch (mysqli_sql_exception) {
    die("Error connecting to backend, " . $sname);
}

if (!$conn) {
    echo "Connection failed!";
    die(1);
}