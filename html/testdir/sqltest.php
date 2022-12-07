<?php
require __DIR__ . "/../../includes/sqlfunctions/vmSqlFuncs.php";
try{
    $poop = getAssignedVM(1,1,1,104);
    echo "done".$poop;
    if(is_null($poop)){
        echo "it null";
    }
}catch(Exception $e){
    echo $e->getMessage();
}
