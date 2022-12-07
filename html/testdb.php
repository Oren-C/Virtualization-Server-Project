<?php

echo "test<br>";
include "./sqlfunctions/vmSqlFuncs.php";

$vmarr = array(
    array(100, "hi1"),
    array(101, "hi2"),
    array(102, "hi3"),
    array(104, "hi5"),
    array(105, "hi6")
);
insertMultiArrayIgnore("./integrations/testDbConfig.php", "testingid", $vmarr);
getOneTest("./integrations/testDbConfig.php", "testingid");


?>