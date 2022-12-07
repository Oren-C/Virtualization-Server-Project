<?php
require "detectAdmin.php";

require_once __DIR__.'/../../vendor/autoload.php';
$configs = require(__DIR__.'/../../includes/configs/proxAuth.php');
require_once __DIR__ . "/../../includes/sqlfunctions/vmSqlFuncs.php";
require_once __DIR__."/../../includes/integrations/proxmoxcommands.php";

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if((isset($_POST['vmtempid']) && ctype_digit($_POST['vmtempid']))){
        $vmTemplateId = (int)$_POST['vmtempid'];

        try{
            if(isset($_POST['remove'])){
                //Remove button pressed
                deleteVmTemplateFromDb($vmTemplateId);


            }else{
                //Submit button pressed

                //Let's do an insert on duplicate key update
                $vmTemplateName = getVmName($vmTemplateId);
                if(! is_null($vmTemplateName)){
                    insertUpdateVmTemplate($vmTemplateId, $vmTemplateName);
                }
            }
        }catch (Exception $e){
            $msg = $msg."An error occurred. Did you enter a valid vmid?";
        }

    }
}


?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html" />
    <title>VM Template Select</title>
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
    <h1><b>VM Template Select</b></h1>
</header>

<h2>Current VMs on Proxmox</h2>
<p>
<?php
foreach(getallVms() as $tmpVmid => $vmName){
    echo $tmpVmid." - ".$vmName." : ";
}
?>
</p>
<h2>Current VM templates in Database</h2>
<p>
<?php
foreach(getAllVmTemplatesFromDatabase() as $kVmid => $vmName){
    echo $kVmid." - ".$vmName." : ";
}
?>
</p>
<h2>Assign VM as a vmtemplate id</h2>
<p>If template id already exists and name is different submit and name change will apply</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" >
    <table class="form" border="0">
        <tr>
            <td></td>
            <td style="color:red;">
                <?php echo $msg; ?></td>
        </tr>
        <tr>
            <th><label for="name"><strong>VM Template ID #:</strong></label></th>
            <td><input class="inp-text" name="vmtempid" id="vmtempid" type="text" size="10" /></td>
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