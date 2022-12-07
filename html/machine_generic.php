<?php

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ //If not logged
    header("location: ./login_form/login.php");
    exit;
}

// Get params from url
$url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
// Append the host(domain name, ip) to the URL.
$url.= $_SERVER['HTTP_HOST'];

// Append the requested resource location to the URL
$url.= $_SERVER['REQUEST_URI'];

//echo $url;

$url_components = parse_url($url);
if(!array_key_exists('query', $url_components)){
    exit;
}
parse_str($url_components['query'], $params);

//TODO: Need to verify these
$user_group_id = $params['group'];
$labid = $params['labid'];
$vmid = $params['vmid'];


//echo "<br>";
//echo ' hi '.$params['name'];

//Now get url for view
//First need to get conn id
//  Conn id is already created for each vm temp in each lab
//  So easy way and secure but bad performance - I'll pull from database here
$user_entity_id = $_SESSION['entity_id'];
$conName = "G{$user_group_id}L{$labid}U{$user_entity_id}V{$vmid}";
require __DIR__ . "/../includes/sqlfunctions/vmSqlFuncs.php";
$conId = getConnectionIdFromConnName($conName);
$hostsConfig = require(__DIR__.'/../includes/configs/hostNameConfig.php');
//echo $hostsConfig["guacamole"]."/#/settings/users";
$viewUrl = $hostsConfig['guacamole']."/#/client/".base64_encode($conId."\0c\0mysql");
//$viewUrl = $hostsConfig['guacamole']."/#/client".base64_encode($conId."\0c\0mysql");

//I forgor I have to store the vmid of the vm that is cloned. I just wasted so much time trying to avoid creating
//another table. Now I have to make a new one.
// New table - user_assignend_vms : entity_id, user_group_id, labid, vm_template_id, vm_clone_id
// Could perhaps add a vm status or last checked time here for when removing but I'm not gonna get to that.

if($_SERVER['REQUEST_METHOD'] == "POST"){
    //require_once __DIR__ . "/../includes/integrations/proxmoxcommands.php";
    require_once __DIR__ . "/../includes/integrations/proxguacintegration.php";
    if (isset($_POST['start'])) { //START
        machineGenericStartVm($user_entity_id, $user_group_id, $labid, $vmid, $conId);
        /*
        //echo "hi bob";
        //First step check if vm has been created already
        //  query new table user_assigned_vms
        $assigned_vm_id = getAssignedVM($user_entity_id, $user_group_id, $labid, $vmid);
        if (is_null($assigned_vm_id)) {
            //Vm does not exist so need to clone
            try {
                $assigned_vm_id = cloneVM($vmid);
                insertAssignedVM($user_entity_id, $user_group_id, $labid, $vmid, $assigned_vm_id);
                setFirewallEnabled($assigned_vm_id);
                setupVmForOnlyInternetAccessFromConfig($assigned_vm_id);
            } catch (Exception $e) {
                //echo $e->getMessage();
            }

        }
        //vm has already been created so just check status and start
        try {
            $taskid = startVmFromVariousStatesNoWait($assigned_vm_id);
            awaitTaskAndReturnStatus($taskid);
            //echo "finished start vm";
            doVncStuff($assigned_vm_id, $conId);
            //echo "finished vnc stuff";
        } catch (Exception $e) {
            //echo "Exception occured " . $e->getMessage() . $e->getTraceAsString();
        }
        */
    }else{

        if(isset($_POST['delete'])){
            /*
            //Delete the associated user_assigned_vms
            $assigned_vm_id = getAssignedVM($user_entity_id, $user_group_id, $labid, $vmid);
            if (! is_null($assigned_vm_id)) {
                //Vm exists so I need to delete from table and stop and delete from proxmox

                try{
                    deleteVm($assigned_vm_id);
                    deleteUserAssignedVm($user_entity_id, $user_group_id, $labid, $vmid, $assigned_vm_id);
                }catch(Exception $e){
                    echo $e->getMessage();
                }

            }
            */
            machineGenericDelete($user_entity_id, $user_group_id, $labid, $vmid);

        }elseif(isset($_POST['reset'])){
            machineGenericDelete($user_entity_id, $user_group_id, $labid, $vmid);
            machineGenericStartVm($user_entity_id, $user_group_id, $labid, $vmid, $conId);
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Servers</title>
    <link rel='stylesheet' type='text/css' href='./style.css'>
    <script>
        /*
        window.onload = function() {
            window.open('https://www.stackoverflow.com/')
        }
        */
    </script>
</head>

<body>
    <form action='machine_generic.php? <?php echo "group={$user_group_id}&labid={$labid}&vmid={$vmid}"; ?>'
          method='post' id='<?php echo "formw10"; ?>'>
        <span class='vmstatus'>VM Status: <?php echo "TODO"; ?> &nbsp&nbsp</span>
        <input type='submit' onclick="setTimeout(func, 1500);" name='start' value='Start/Resume' />
        <input type='submit' name='delete' value='Delete' />
        <input type='submit' onclick="setTimeout(func, 2250);" name='reset' value='Reset' />

    </form>
    <script type="text/javascript">
        function func () {
            window.open("<?php echo $viewUrl; ?>");
            return true;
        }
    </script>


</body>

</html>



