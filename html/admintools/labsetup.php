<?php
require "detectAdmin.php";

require_once __DIR__.'/../../vendor/autoload.php';
$configs = require(__DIR__.'/../../includes/configs/proxAuth.php');
$client = new Corsinvest\ProxmoxVE\Api\PveClient($configs["host"]);
require_once __DIR__."/../../includes/sqlfunctions/vmSqlFuncs.php";
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$name = test_input($_POST["name"]);
    //echo var_dump($_POST);
    // TODO:
    // Confused on XSS prevention in php. The old default is now deprecated and the new advice is to not
    // filter or sanitize input at all, just sanitize output. But I don't have time to do that.
    // https://stackoverflow.com/questions/69207368/constant-filter-sanitize-string-is-deprecated
    if(isset($_POST['labid'])){
        //Remove form


    }else{
        // Create form

        $inputLabName = NULL;
        $inputVms = NULL;
        //Get labname from string
        if(isset($_POST['name'])){
            $inputLabName = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        //Get assigned VM's from string
        if(isset($_POST['vmid'])){
            $inputVms = filter_var($_POST['vmid'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        if(is_null($inputLabName) || is_null($inputVms)){
            $msg = $msg."Lab name or vm template is blank";
        }else{
            //Get vm's from comma seperated string
            $splitStrVms = explode(",", $inputVms);
            $splitVms = array();

            foreach ($splitStrVms as $x){
                //echo "ha ".$x;
                if(ctype_digit($x)){
                    $splitVms[] = (int)$x;
                }
            }
            if(count($splitVms) > 0){
                try{
                    createLab($inputLabName, $splitVms);
                }catch (Exception $e){
                    $msg = $msg."Error creating labs. Did you enter valid vm template id?";
                }
            }

        }
    }

}



?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html" />
    <title>Lab Setup</title>
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
              <a href="./adminhome.php">Admin Home</a>
          <?php endif; ?>
      </span>
    <span class="w3-bar-item w3-right">VirtualMachines</span>
</div>

<header class="w3-container" style="padding-top:40px">
    <h1><b>Lab Setup</b></h1>
</header>
<h2>Current Labs</h2>
<?php
require __DIR__.'/../../includes/admintools/lab/getlabs.php'
?>
<h2>Current VM Templates</h2>
<p>
    <?php
    foreach(getAllVmTemplatesFromDatabase() as $vmTempId => $vmTempName){
        echo $vmTempId." -> ".$vmTempName. " : ";
    }
    ?>
</p>
<h2>Create a lab</h2>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" >
    <table class="form" border="0">
        <tr>
            <td></td>
            <td style="color:red;">
                <?php echo $msg; ?></td>
        </tr>
        <tr>
            <th><label for="name"><strong>Lab name:</strong></label></th>
            <td><input class="inp-text" name="name" id="name" type="text" size="30" /></td>
        </tr>
        <tr>
            <th><label for="name"><strong>VM template ID(s)(comma seperated):</strong></label></th>
            <td><input class="inp-text" name="vmid" id="vmid" type="text" size="30" /></td>
        </tr>
        <tr>
            <td></td>
            <td class="submit-button-right">
                <input class="send_btn" type="submit" value="Submit" alt="Submit" title="Submit" />
            </td>
        </tr>
    </table>
</form>

<h2>Remove a lab TODO</h2>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" >
    <table class="form" border="0">
        <tr>
            <td></td>
            <td style="color:red;">
                <?php echo $msg; ?></td>
        </tr>
        <tr>
            <th><label for="labid"><strong>Lab ID:</strong></label></th>
            <td><input class="inp-text" name="labid" id="labid" type="text" size="30" /></td>
        </tr>
        <tr>
            <td></td>
            <td class="submit-button-right">
                <input class="send_btn" type="submit" value="Remove" alt="Remove" title="Remove" />
            </td>
        </tr>
    </table>
</form>



</body>