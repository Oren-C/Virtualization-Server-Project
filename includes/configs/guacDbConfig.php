<?php
$host = '<GUACAMOLE IP>';
$db = '<GUACAMOLE DATABASE NAME>';
$user = '<GUACAMOLE DB USER>';
$password = '<PASSWORD>';


$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$dbh = new PDO($dsn, $user, $password);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
return $dbh;
?>