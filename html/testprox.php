<?php
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

require_once __DIR__.'/../vendor/autoload.php';
$configs = require(__DIR__.'/../includes/configs/proxAuth.php');
$client = new Corsinvest\ProxmoxVE\Api\PveClient($configs["host"]);

if($client->login($configs["username"],$configs["password"],$configs["realm"])){
    //get version from get method
    //echo var_dump($client->get('/version')->getResponse());
    echo "hi";
    foreach ($client->getNodes()->Index()->getResponse()->data as $node) {
        echo "\n" . $node->id;
    }

    foreach ($client->getNodes()->get("pve")->getQemu()->Vmlist()->getResponse()->data as $vm) {
        echo "\n" . $vm->vmid ." - " .$vm->name;
        echo $vm->status;


        
        
    }
}
echo "hi2";

?>