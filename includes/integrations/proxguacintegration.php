<?php


/**
 * @param int $assigned_vm_id
 * @param int $connection_id
 * @return void
 * @throws Exception
 */
function doVncStuff(int $assigned_vm_id, int $connection_id): void
{
    require_once __DIR__ . "/proxmoxcommands.php";
    require_once __DIR__ . "/guacamole.php";


    try{
        set_time_limit(0);
        do{
            $vncInfo = setupVncPort($assigned_vm_id);
            updateConnection($connection_id, $vncInfo['port'], $vncInfo['password']);
            //echo "before await";
            $vncStatus = awaitTaskAndReturnStatus($vncInfo['task']);
            //echo "after await";
        }while($vncStatus == 'connection timed out');

        /*
        $vncInfo = setupVncPort(105);
        var_dump($vncInfo);
        updateConnection(1, $vncInfo['port'], $vncInfo['password']);
        $vncStatus = awaitTaskAndReturnStatus($vncInfo['task']);
        */
        //sleep(3);
        //echo "after Sleep";
        /*
            $vncInfo = setupVncPort(105);
            var_dump($vncInfo);
            updateConnection(1, $vncInfo['port'], $vncInfo['password']);
            $vncStatus = awaitTaskAndReturnStatus($vncInfo['task']);
        */


        //New plan
        //Have links open up first with get then post to start conneciton

    }catch (Exception $ex){
        throw new Exception($ex->getMessage() . $ex->getTraceAsString());
    }finally{
        set_time_limit(30);
    }
}

function machineGenericStartVm($user_entity_id, $user_group_id, $labid, $vmid, $conId){
    require_once __DIR__ . "/../sqlfunctions/vmSqlFuncs.php";
    require_once __DIR__ . "/proxmoxcommands.php";
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
            echo $e->getMessage();
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
        echo "Exception occured " . $e->getMessage() . $e->getTraceAsString();
    }
}

function machineGenericDelete($user_entity_id, $user_group_id, $labid, $vmid){
    require_once __DIR__ . "/../sqlfunctions/vmSqlFuncs.php";
    require_once __DIR__ . "/proxmoxcommands.php";
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
}

