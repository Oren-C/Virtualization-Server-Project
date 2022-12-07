<?php
//Exit if not admin
include 'detectAdmin.php';
$hostsConfig = include(__DIR__.'/../../includes/configs/hostNameConfig.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html" />
    <title>Poop</title>
    <meta name="description" content="Poop page"/>
    <meta name="keywords" content="poop"/>
    <meta charset="UTF-8">
    <link href="../style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

    <div class="w3-bar w3-top w3-black w3-large" style="z-index:4">
      <span class="w3-bar-item w3-left">
        Welcome, <?php echo $_SESSION["username"]; ?> - <a href="../login_form/logout.php">Logout</a>
          <a href="../serversv2.php">Home</a>
          <?php if($_SESSION["admin"]) : ?>
              <a href="adminhome.php">Admin Home</a>
          <?php endif; ?>
      </span>
        <span class="w3-bar-item w3-right">VirtualMachines</span>
    </div>

    <header class="w3-container" style="padding-top:40px">
        <h1><b>Admin Home Page</b></h1>
    </header>

    <h1>Admin Home Page</h1>
    <p>Test</p>
    <p>Links</p>
    <ul>
        <li>Temporary User Creation - (You'll need to login to guacamole using same credentials after clicking link)
        <a href="<?php echo $hostsConfig["guacamole"]."/#/settings/users"; ?>" target="_blank">Test</a></li>
        <li><a href="vmtemplateselect.php">VM Template Setup</a></li>
        <li><a href="./labsetup.php">Lab Setup</a> </li>
        <li><a href="./labgroupassign.php">Lab Group Assign</a> </li>
    </ul>

</body>
