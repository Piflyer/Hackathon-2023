<?php
if(str_contains($_SERVER['SERVER_NAME'], "PhpStorm")) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
