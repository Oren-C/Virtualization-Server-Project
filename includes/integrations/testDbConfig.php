<?php
$host = '192.168.1.249';
$db = 'proxvms';
$user = 'php';
$password = 'crazymonk22';


$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$dbh = new PDO($dsn, $user, $password);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>