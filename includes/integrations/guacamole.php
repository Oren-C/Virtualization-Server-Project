<?php
//updateConnection(1, 5900, "test4324");

/*
function searchIP($mac){
    $out = shell_exec("sudo ./searchIP.sh | fgrep '$mac'");
    $ip = substr($out, strpos($out, "192.168.1."), 13); //Filtra solo la IP de la salida
    return trim($ip);
}

function createConnectionw10($username, $ip){
    include "./integrations/dbConfig.php";
    $sql = "SELECT connection_id FROM guacamole_connection WHERE connection_name LIKE '%- $username'";
    $query = $mysqli->query($sql);
    if ($query === FALSE) { //Error
        echo "Could not successfully run query ($sql) from DB: " . $mysqli->error;
        exit;
    }
    $connectionid = $query->fetch_row()[0];

    $sql = "UPDATE guacamole_connection_parameter SET parameter_value='$ip' WHERE connection_id = '$connectionid' AND parameter_name = 'hostname'";
    $query = $mysqli->query($sql); //Update the IP of the machine in Guacamole
    if ($query === FALSE) { //Error
        echo "Could not successfully run query ($sql) from DB: " . $mysqli->error;
        exit;
    }		
}
*/
/*Minimum tables to create connection
- guacamole_connection id is autoincrement
- guacamole_connection_parameter hostname, password, port
- guacamole_connection_permission entity_id, connection_id, permission

*/
//function createConnection($username)
function createConnection(string  $connectionName, string $protocol){
    $sqlInsert = "INSERT INTO guacamole_db.guacamole_connection (connection_name, protocol)
                  VALUES (:name,:protocol)";
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";
    $stmt = $dbh->prepare($sqlInsert);
    $stmt->bindValue("name", $connectionName);
    $stmt->bindValue("protocol", $protocol);

    $stmt->execute();
    $dbh->lastInsertId();

}


function updateConnection($connectionId, $vncPort, $vncPass){
    //From a glance these looks like they can be combined but each query modifies a seperate row.
    $sqlPassUpdate = "UPDATE guacamole_db.guacamole_connection_parameter as gcp
            SET parameter_value = :pass
            WHERE gcp.connection_id = :conid AND gcp.parameter_name = 'password'";

    //require __DIR__."/../configs/proxDbConfig.php";
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";
    $stmt = $dbh->prepare($sqlPassUpdate);
    $stmt->bindValue("pass", $vncPass);
    $stmt->bindValue("conid", $connectionId);

    $stmt->execute();


    $sqlPortUpdate = "UPDATE guacamole_db.guacamole_connection_parameter as gcp
            SET parameter_value = :port
            WHERE gcp.connection_id = :conid AND gcp.parameter_name = 'port'";


    $stmt = $dbh->prepare($sqlPortUpdate);
    $stmt->bindValue("port", $vncPort);
    $stmt->bindValue("conid", $connectionId);
    $stmt->execute();
    /*
    try{
        $dbh->commit();
    } catch(PDOException $e){
        $dbh->rollBack();
    }
    */



}

/*
 require __DIR__."/configs/proxDbConfig.php";
$stmt = $dbh->prepare("select ge.entity_id, gug.user_group_id, gug.entity_id as group_entity_id, ge2.name as group_name
from guacamole_db.guacamole_entity as ge
inner join guacamole_db.guacamole_user_group_member as gugm on ge.entity_id = gugm.member_entity_id
inner join guacamole_db.guacamole_user_group as gug on gugm.user_group_id = gug.user_group_id
inner join guacamole_db.guacamole_entity as ge2 on gug.entity_id = ge2.entity_id
where ge.entity_id = :entity
order by user_group_id ASC");

$stmt->bindValue("entity", $_SESSION["entity_id"]);
$stmt->execute();

foreach ($stmt->fetchAll() as $item){
    echo var_dump($item);
}
 */


?>