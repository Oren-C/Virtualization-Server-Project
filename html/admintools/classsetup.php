<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ //If not logged
    header("location: ../login_form/login.php");
    exit;
}else if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true){
    header("location: ../serversv2.php");
    exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html" />
    <title>Poop</title>
    <meta name="description" content="Poop page"/>
    <meta name="keywords" content="poop"/>
    <meta charset="UTF-8">
    <link href="../style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <h1>Admin Home Page</h1>

    <p>So temporarily users and groups can be done inside of guacamole however the downside is you'll have to enter users individually. </p>
    <p>Links</p>
    <ul>
        <li>Temporary User Creation - (You'll need to login to guacamole using same credentials after clicking link)

        <li><a href="">Class setup</a></li>
        <li><a href="./labsetup.php">Lab Setup</a> </li>
    </ul>

</body>
