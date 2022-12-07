<?php
require "detectAdmin.php";

require_once __DIR__.'/../../vendor/autoload.php';
$configs = require(__DIR__.'/../../includes/configs/proxAuth.php');

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$name = test_input($_POST["name"]);
    //var_dump($_POST);
    //I need to do some filtering on input
    if((isset($_POST['groupid']) && ctype_digit($_POST['groupid']))&&
        (isset($_POST['labid']) && ctype_digit($_POST['labid']))){
        $group_id = (int)$_POST['groupid'];
        $labid = (int)$_POST['labid'];

        require_once __DIR__ . "/../../includes/sqlfunctions/vmSqlFuncs.php";
        if(isset($_POST['remove'])){
            //Remove-button pressed

            //Remove entry from lab_group_assignments

            $con_group_id = getConnectionGroupId($labid,$group_id);
            try{
                deleteLabGroupAssignment($labid, $group_id);

                //delete connection_group_id and it should cascade and delete all connections
                deleteConnectionGroup($con_group_id);
            }catch (Exception $e){
                $msg = $msg.$e->getMessage();
            }
        }else{
            //Submit-button pressed

            //Create connection group
            $con_group_name = "G".$group_id."L".$labid;
            //echo $con_group_name;
            try{
                $con_group_id = createConnectionGroup($con_group_name);
                //echo "Here is gorup id".$con_group_id;

                //Insert into lab_group_assignments
                createLabGroupAssignment($labid, $group_id, $con_group_id);

                //grant user read permission to connection_group
                //  Get all users in group
                //  Do a for loop through users to do an insert ignore with array
                // -Update nvm did it with a insert select
                setConnectionGroupPermissionsWithSelect($group_id, $con_group_id);

                //create connection for each user * lab assigned VM's
                //  insert into guacamole_connection
                //  Values to set connection_id, connection_name, parent_id, protocol
                createConnections($group_id, $labid, $configs["host"]);

            }catch (Exception $e){
                $msg = $msg.$e->getMessage();
            }








            //grant permissions for those connections
        }
    }

    /*
    if(isset($_POST['labid'])){
        //Remove form


    }else{
        // Create form

        //Get labname from string
        if(isset($_POST['name'])){
            echo $_POST['name'];
        }
        //Get assigned VM's from string
        if(isset($_POST['vmid'])){
            echo $_POST['vmid'];
        }

        //Insert into database
    }
    */
}


?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html" />
    <title>Lab Group Assign</title>
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
    <h1><b>Lab Group Assign</b></h1>
</header>

<h2>Current Labs</h2>
<?php
require __DIR__.'/../../includes/admintools/lab/getlabs.php'
?>
<h2>Current groups</h2>
<?php
require __DIR__.'/../../includes/admintools/lab/getgroups.php'
?>
<h2>Assign/Remove lab to/from group</h2>
<p>Labs can be assigned to multiple groups</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" >
    <table class="form" border="0">
        <tr>
            <td></td>
            <td style="color:red;">
                <?php echo $msg; ?></td>
        </tr>
        <tr>
            <th><label for="name"><strong>Group ID:</strong></label></th>
            <td><input class="inp-text" name="groupid" id="groupid" type="text" size="30" /></td>
        </tr>
        <tr>
            <th><label for="name"><strong>Lab ID:</strong></label></th>
            <td><input class="inp-text" name="labid" id="labid" type="text" size="30" /></td>
        </tr>
        <tr>
            <td></td>
            <td class="submit-button-right">
                <input class="send_btn" type="submit" name="submit" value="Submit" alt="Submit" title="Submit" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="submit-button-right">
                <input class="send_btn" type="submit" name="remove" value="Remove" alt="Remove" title="Remove" />
            </td>
        </tr>
    </table>
</form>



</body>