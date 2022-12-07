<?php
session_start();

if($_SERVER["REQUEST_URI"] == "/admintools/detectAdmin.php"){
    header("HTTP/1.0 404 Not Found");
    exit();
}else if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ //If not logged
    header("location: ../login_form/login.php");
    exit;
}else if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true){
    header("location: ../serversv2.php");
    exit;
}
?>