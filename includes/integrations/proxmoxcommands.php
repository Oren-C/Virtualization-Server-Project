<?php
/*
 * TODO:
 * So I'm not gonna do any/much error checking with the api, but that is something needs to be done.
 */
/*
echo "test<br>";
include "./sqlfunctions/vmSqlFuncs.php";

$vmarr = array(
    array(100, "hi1"),
    array(101, "hi2"),
    array(102, "hi3"),
    array(104, "hi5")
);
unsafeArrayInsert("./integrations/testDbConfig.php", "testingid", $vmarr);
*/
//echo "hi about to do it good";

//try{
    //cloneVM(104);
    //getAllVms();
    //echo var_dump(isVmRunning(105));
    //startVmFromVariousStates(105);
    //setupVncPort(105);

    //testingoutput(105);
    //echo stopUnsafe(105);
    //echo setVmToState(105, 'shutdown');
    /*
    echo getVmQmpStatus(105);
    echo "<br>";
    //startVmFromVariousStates(105);
    setVmToState(105, 'stop');
    echo "<br>";
    echo getVmQmpStatus(105);
    */

//}catch (Exception $ex){
  //  echo "Code: ".$ex->getCode()." Message: ".$ex->getMessage();
//}

/**
 * @return \Corsinvest\ProxmoxVE\Api\PveClient
 * @throws Exception Login failure
 */
function getProxAndLogin() : \Corsinvest\ProxmoxVE\Api\PveClient
{
    require_once __DIR__ . '/../../vendor/autoload.php';
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $client = new Corsinvest\ProxmoxVE\Api\PveClient($configs["host"]);


    if($client->login($configs["username"],$configs["password"],$configs["realm"])){
        return $client;
    }else{
        throw new Exception("Failed to login to proxmox", -1);
    }
}

/**
 * I think it waits not 100% sure
 * @param int $vmtemplate Template to clone from
 * @return int new vmid of cloned vm
 * @throws Exception not all errors are accounted for
 */
function cloneVM(int $vmtemplate): int
{
    $client = getProxAndLogin();

    $newid = $client->getCluster()->getNextid()->nextid();
    if($newid->isSuccessStatusCode()){
        $newid = (int)$newid->getResponse()->data;
    }else{
        throw new Exception("Error getting new vmid", 0);
    }
    $configs = require(__DIR__ . '/../configs/proxAuth.php');

    $cloneResult = $client->getNodes()->get($configs['node'])->getQemu()->get($vmtemplate)->getClone()->cloneVm($newid);
    if($cloneResult->isSuccessStatusCode()){
        return $newid;
    }else{
        //Doesn't really give much in terms off errors here, possible one I ran into if its not using a certain
        //drive type. Such as scsi it will give an error with using a linked clone.
        throw new Exception("Failed to clone VM", 1);
    }

}

function getVmName(int $pVmid){
    try{
        $client = getProxAndLogin();

        $configs = require(__DIR__ . '/../configs/proxAuth.php');
        $result = $client->getNodes()->get($configs['node'])->getQemu()->get($pVmid)->getStatus()->getCurrent()->vmStatus();

        if($result->isSuccessStatusCode()){
            return $result->getResponse()->data->name;
        }else{
            return NULL;
        }
    }catch (Exception $e){
        return NULL;
    }

}

/**
 * @return array Keys are vmid and values are vm name
 * @throws Exception
 */
function getAllVms(): array
{

    $client = getProxAndLogin();
    /*
    foreach ($client->getNodes()->Index()->getResponse()->data as $node) {
        echo "\n" . $node->id;
    }
    */
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $retArr = array();
    foreach ($client->getNodes()->get($configs['node'])->getQemu()->Vmlist()->getResponse()->data as $vm) {
        //echo "\n" . $vm->vmid ." - " .$vm->name;
        $retArr[$vm->vmid] = $vm->name;
        //echo $vm->status;
    }
    return $retArr;

}

/**
 * @param int $vmid
 * @return string
 * @throws Exception
 */
function getVmQmpStatus(int $vmid) : string
{
    $client = getProxAndLogin();

    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $status =
        $client->getNodes()->get($configs['node'])->getQemu()->get($vmid)->getStatus()->getCurrent()->vmStatus();
    if($status->isSuccessStatusCode()){
        $status = $status->getResponse()->data;

        //echo var_dump($status->qmpstatus);
        //echo var_dump($status->status);
        return $status->qmpstatus;

    }else{
        throw new Exception("Failed to get VM Status", 3);
    }

}

/**
 * @param int $vmid
 * @return bool
 * @throws Exception When failing to connect or given invalid vmid
 */
function isVmRunning(int $vmid): bool
{
    return isVmStartedFromString(getVmQmpStatus($vmid));
}

function isVmRunningFromString(string $qmpstatus): bool
{
    return $qmpstatus == 'running';
}

/**
 * Starts vm from paused and stopped states
 * @param int $vmid
 * @return string taskid returns empty string if vm is in incorrect state
 * @throws Exception
 */
function startVmFromVariousStatesNoWait(int $vmid): string
{
    $qmpstatus = getVmQmpStatus($vmid);
    $taskid = "";
    if(! isVmRunningFromString($qmpstatus)){
        if ($qmpstatus == 'paused'){
            $taskid = setVmToState($vmid, 'resume');
        }elseif($qmpstatus == 'stopped'){
            $taskid = setVmToState($vmid, 'start');
        }
    }
    return $taskid;
}

/**
 * Stops vm doesn't do any checks
 * @param int $vmid
 * @return string taskid returns empty string if vm is in incorrect state
 * @throws Exception
 */
function stopUnsafe(int $vmid): string
{
    return setVmToState($vmid, 'stop');
}


//TODO: make $state a enum
/**
 * No checks occur here on current state of VM
 * @param int $vmid
 * @param string $state State to set vm to (not all states are accounted for)
 * @param int|null $timeout Setting certain states allow setting a timeout
 * @param bool $suspendToDisk For suspend if set true will write to disk when suspending vm
 * @throws Exception
 * @return string taskid - Don't need unless want to check on status of task or to wait for it to finish
 */
function setVmToState(int $vmid, string $state, int $timeout = null, bool $suspendToDisk = false) : string
{
    $client = getProxAndLogin();
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $result = $client->getNodes()->get($configs['node'])->getQemu()->get($vmid)->getStatus();

    //match is a new feature in php 8 you'll get a parse error if version before.
    //Shutdown is safe shutdown this will sometimes fail, due to vm wanting to confirm with user
    //o generally just do a stop
    $result = match ($state) {
        'reboot' => $result->getReboot()->vmReboot($timeout),
        'resume' => $result->getResume()->vmResume(),
        'shutdown' => $result->getShutdown()->vmShutdown(timeout: $timeout),
        'start' => $result->getStart()->vmStart(timeout: $timeout),
        'stop' => $result->getStop()->vmStop(timeout: $timeout),
        'suspend' => $result->getSuspend()->vmSuspend(todisk: $suspendToDisk),
        default => throw new Exception("Invalid state given"),
    };

    if(! $result->isSuccessStatusCode()){
        throw new Exception("Error occurred with task: ".$result->getError());
    }else{
        return $result->getResponse()->data;
    }

}

//this should be done when the user clicks view
/**
 * @param int $vmid
 * @return array consists of port and password field
 * @throws Exception
 */
function setupVncPort(int $vmid) : array
{
    $client = getProxAndLogin();
    //$ticketres = $client->getAccess()->getTicket()->createTicket("poop", "poop@pve");
    //var_dump($ticketres);
    //$proxre = $client->getNodes()->get('pve')->getQemu()->get($vmid)->getVncproxy()->vncproxy();

    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $proxre = $client->getNodes()->get($configs['node'])->getQemu()->get($vmid)->getVncproxy()->vncproxy(generate_password: true, websocket: true);
    //var_dump($proxre->getResponse());
    $proxre = $proxre->getResponse()->data;
    $ticket = (string)$proxre->ticket;
    $pass = $proxre->password;
    $port = (int)$proxre->port;
    $task = $proxre->upid;

    //echo "password: ".$pass;

    //var_dump($port);
    //var_dump($ticket);
    //$vncre = $client->getNodes()->get('pve')->getQemu()->get($vmid)->getVncwebsocket()->vncwebsocket($port, $ticket);
    //var_dump($vncre);
    //echo "<br>";
    //echo "<br>";

    //$url = "https://192.168.1.247:{$port}/?console=kvm&vmid={$vmid}&vmname=testbsub&node=pve&resize=scale&novnc=1&password={$pass}";
    //$url = "https://192.168.1.247:{$port}/?console=kvm&vmid={$vmid}&vmname=testbsub&node=pve&resize=scale&novnc=1";
    //echo "<a href={$url} target='_blank'>TEST</a>";
    return array('port'=>$port, 'password'=>$pass, 'task'=>$task);


}

function awaitTaskAndReturnStatus(string $task){

    $client = getProxAndLogin();
    //echo "Wait for task to finish";
    //If  waitForTaskToFinish returns true that means time ran out - nevermind
    //var_dump($client->waitForTaskToFinish($task, timeOut: 10000));
    //I made it to where it returns true if task is finished.

    //if true task is still runing so I can assume
    $blResult = $client->waitForTaskToFinish($task, timeOut: 20000);
    if(! $blResult){
        //Task finished return exit status
        return $client->getExitStatusTask($task);
    }else{
        //Task not finished return null
        return NULL;
    }
    /*
    while(! $client->waitForTaskToFinish($task, timeOut: 20000)){

    }
    return $client->getExitStatusTask($task);
    */
    /*
    $counter = 0;
    while($client->getExitStatusTask($task) == NULL){
        echo "hit";
        var_dump($client->taskIsRunning($task));
        echo ++$counter;
        var_dump($client->waitForTaskToFinish($task));

    }
    */
    //var_dump($client->waitForTaskToFinish($task));
    //echo "After task finished: ";
    //var_dump($client->getExitStatusTask($task));

}

/**
 * @param int $pVmid
 * @return void
 * @throws Exception
 */
function deleteVm(int $pVmid){
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $node = $configs['node'];
    $client = getProxAndLogin();


    awaitTaskAndReturnStatus(stopUnsafe($pVmid));
    $result = $client->getNodes()->get($node)->getQemu()->get($pVmid)->destroyVm(true, true);
}

function getFirewallEnabled($pVmid){
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $node = $configs['node'];
    $client = getProxAndLogin();

    $result = $client->getNodes()->get($node)->getQemu()->get($pVmid)->getFirewall()->getOptions()->getOptions();

    if(! $result->isSuccessStatusCode()){
        throw new Exception("Error occurred with task: ".$result->getError());
    }else{
        return $result->getResponse()->data->enable;
    }
}

function setFirewallEnabled($pVmid){
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $node = $configs['node'];
    $client = getProxAndLogin();

    $result = $client->getNodes()->get($node)->getQemu()->get($pVmid)->getFirewall()->getOptions()->setOptions(enable: true);

    if(! $result->isSuccessStatusCode()){
        throw new Exception("Error occurred with task: ".$result->getError());
    }
}

function setupVmForOnlyInternetAccessFromConfig(int $pVmid){
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $node = $configs['node'];
    $vmNetwork = $configs['vmnetwork'];
    $proxLanNetwork = $configs['proxlannetwork'];
    $gateway = $configs['vmgateway'];
    $dhcpServer = $configs['vmdhcpserver'];

    setupVmForOnlyInternetAccess($pVmid, $vmNetwork, $proxLanNetwork, $gateway, $dhcpServer);

}

function setupVmForOnlyInternetAccess(int $pVmid, string $pVmNetwork, string $pProxLanNetwork, string $pGateway, string $pDhcpServer){
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $node = $configs['node'];

    blockVmAccessToNetwork($node, $pVmid, $pVmNetwork);
    blockVmAccessToNetwork($node, $pVmid, $pProxLanNetwork);
    grantAccessToVM($node,$pVmid, $pGateway);
    grantAccessToVM($node, $pVmid, $pDhcpServer);

}

function blockVmAccessToNetwork(string $pNode, int $pVmid, string $pNetwork){
    $client = getProxAndLogin();

    $result = $client->getNodes()->get($pNode)->getQemu()->get($pVmid)->getFirewall()->getRules()->createRule('DROP','out',
        'Prevent network access', $pNetwork, enable: true);
    if(! $result->isSuccessStatusCode()){
        throw new Exception("Error occurred in blcokVmAccessToNetwork");
    }
}

function grantAccessToVM(string $pNode, int $pVmid, string $pAllowIP){
    $client = getProxAndLogin();
    $result = $client->getNodes()->get($pNode)->getQemu()->get($pVmid)->getFirewall()->getRules()->createRule('ACCEPT', 'out',
    'Allow access to '.$pAllowIP, dest: $pAllowIP, enable: true, pos: 0);
    if(! $result->isSuccessStatusCode()){
        throw new Exception("Error occurred in allowVmAccessToNetwork");
    }

}

/**
 * Just for testing don't use
 * @param $vmid
 * @return void
 * @throws Exception
 */
function testingoutput($vmid){
    $client = getProxAndLogin();
    echo "Poop";
    echo "<br>";
    $configs = require(__DIR__ . '/../configs/proxAuth.php');
    $result = $client->getNodes()->get($configs['node'])->getQemu()->get(105)->getStatus()->getShutdown()->vmShutdown();

    echo "doofo: ";
    var_dump($result->getError());
    echo "Get Response: ";
    var_dump($result->getResponse());
    echo "Is Succ: ";
    var_dump($result->isSuccessStatusCode());
    echo "Response in error: ";
    var_dump($result->responseInError());
    echo "Get method type: ";
    var_dump($result->getMethodType());
    echo "Get Reason Phrase: ";
    var_dump($result->getReasonPhrase());
    echo "Get Status Code: ";
    var_dump($result->getStatusCode());

    echo "<br>";
    $client->waitForTaskToFinish($result->getResponse()->data,5000, 50000);
    echo "After";
    $task = $result->getResponse()->data;
    var_dump($client->taskIsRunning($task));

    //var_dump($client->readTaskStatus($result->getResponse()->data));

    var_dump($client->get("/nodes/{$client->getNodeFromTask($task)}/tasks/{$task}/status")->getResponse()->data->status == "running");
    //$this->get("/nodes/{$this->getNodeFromTask($task)}/tasks/{$task}/status");



}
?>

