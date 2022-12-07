<?php

define ("DB_HOST", "192.168.1.249"); // set database host

define ("DB_USER", "php"); // set database user

define ("DB_PASS","crazymonk22"); // set database password

define ("DB_NAME","guacamole_db"); // set database name



$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die("Couldn't make connection.");
$mysqli->set_charset("utf8mb4");

?>