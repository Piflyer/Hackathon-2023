<?php
function create_connection($sname, $unmae, $password, $db_name): mysqli
{
    try {
        $conn = mysqli_connect($sname, $unmae, $password, $db_name);
        assert($conn);
        return $conn;
    } catch (mysqli_sql_exception) {
        die("Error connecting to backend, " . $sname);
    }
}
