<?php

$sname = getenv("DATABASE_SERVER") ?: "localhost";
$unmae = getenv("DATABASE_USER") ?: "root";
$password = getenv("DATABASE_SERVER") ?: "rootpass";
$db_name = getenv("DATABASE_NAME") ?: "metaverse";

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