<?php
/*TODO:
 * This should either be a function that returns pdo as a type
 * or should be a class kinda of like a singleton maybe
 */
$host = '<GUACAMOLE DATABASE IP>';
$db = 'proxvms';
$user = '<DB USERNAME>';
$password = '<DB PASSWORD>';


$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$dbh = new PDO($dsn, $user, $password);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
return $dbh;
?>