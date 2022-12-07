<?php

//$arr is a multidemnsional array that should be in order
/**
 * @param $dbFile
 * @param $table
 * @param $arr
 * @return void
 * @throws Exception
 */
function insertMultiArrayIgnore($table, $arr){
    require __DIR__ . "/../configs/proxDbConfig.php";

    /*
    $sql = "INSERT IGNORE INTO $table VALUES ";
    for ($row = 0; $row < count($arr); $row++){
        $tempStr = NULL;
        for($col = 0; $col < count($arr[$row]); $col++){
            if(gettype($arr[$row][$col]) == "string"){
                $tempStr = $tempStr."'".$arr[$row][$col]."'".",";
            }else{
                $tempStr = $tempStr.$arr[$row][$col].",";
            }
        }
        $sql = $sql."(".substr($tempStr, 0, -1)."),";
    }
    $sql = substr($sql, 0, -1).";";
    echo "Previous sql:<br>";
    echo $sql."<br>";
    */
    $nsql = "INSERT IGNORE INTO $table VALUES ";
    for ($row = 0; $row < count($arr); $row++){
        $nsql = $nsql.'(';
        for($col = 0; $col < count($arr[$row]); $col++){
            $nsql = $nsql.'?,';
        }
        $nsql = substr($nsql, 0, -1).'),';
    }
    $nsql = substr($nsql, 0, -1).';';
    /*
    echo "newish sql:<br>";
    echo $nsql."<br>";
    */

    $sth = $dbh->prepare($nsql);
    $counter = 1;
    for ($row = 0; $row < count($arr); $row++){
        for($col = 0; $col < count($arr[$row]); $col++){
            $sth->bindValue($counter++, $arr[$row][$col]);
        }
    }

    try{
        $dbh->beginTransaction();
        $sth->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Failed to delete all from table");
    }




    /*
    if($mysqli->query($sql) === TRUE){
        echo "New records created succ";
    }else{
        echo "error: " . $sql . "<br>" . $mysqli->error;
    }
    */

}

//WARNING: Extremely dangerous function.
/**
 * Warning EXTREMELY DANGEROUS
 * @param $dbFile
 * @param $table
 * @return void
 * @throws Exception
 */
function deleteAllFromTable($table){
    require __DIR__ . "/../configs/proxDbConfig.php";

    $stmt = $dbh->prepare("DELETE FROM $table");
    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Failed to delete all from table");
    }
}

function getOneTest($dbFile, $table){
    include $dbFile;

    try{
        $sql = "select * from $table";
        $sth = $dbh->query($sql);

        //echo implode(" ",$sth->fetch());
        echo implode(" ",$sth->fetch());
        echo "test2<br>";
        echo var_dump($sth->fetch()[0]);
        $sth->closeCursor();

    }catch (PDOException $e){
        echo "<br>error encountered<br>";
        echo $e;
    }
}

/**
 * @param int $pEntity_id
 * @param int $pUser_group_id
 * @param int $pLabid
 * @param int $pVm_template_id
 * @param int $pVm_clone_id
 * @return void
 * @throws Exception
 */
function deleteUserAssignedVm(int $pEntity_id, int $pUser_group_id, int $pLabid,
                              int $pVm_template_id, int $pVm_clone_id){
    require __DIR__ . "/../configs/proxDbConfig.php";

    $stmt = $dbh->prepare("DELETE FROM user_assigned_vms
                           WHERE entity_id = :entity_id AND user_group_id = :user_group_id AND labid = :labid AND
                           vm_template_id = :vm_template_id AND vm_clone_id = :vm_clone_id");
    $stmt->bindValue('entity_id', $pEntity_id);
    $stmt->bindValue('user_group_id', $pUser_group_id);
    $stmt->bindValue('labid', $pLabid);
    $stmt->bindValue('vm_template_id', $pVm_template_id);
    $stmt->bindValue('vm_clone_id', $pVm_clone_id);
    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Failed to delete user vm assignment");
    }

}

function deleteVmTemplateFromDb(int $pTemplateVmid){
    require __DIR__ . "/../configs/proxDbConfig.php";

    $stmt = $dbh->prepare("DELETE FROM vm_templates WHERE vmid = :tmpVmid");
    $stmt->bindValue('tmpVmid', $pTemplateVmid);
    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error occurred on delete vm template");
    }
}

/**
 * @param int $pTemplateVmid
 * @param string $pTemplateName
 * @return void
 * @throws Exception
 */
function insertUpdateVmTemplate(int $pTemplateVmid, string $pTemplateName){
    require __DIR__ . "/../configs/proxDbConfig.php";

    $stmt = $dbh->prepare("INSERT INTO vm_templates (vmid, name)
                           VALUES (:vmid, :vmname)
                           ON DUPLICATE KEY UPDATE name = :vmname2");
    $stmt->bindValue('vmid', $pTemplateVmid);
    $stmt->bindValue('vmname', $pTemplateName);
    $stmt->bindValue('vmname2', $pTemplateName);

    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error occurred on insert update vm template".$e->getMessage());
    }
}

/**
 * @return array key is vmid value is vm name
 */
function getAllVmTemplatesFromDatabase() :  array
{
    require __DIR__."/../configs/proxDbConfig.php";

    $stmt = $dbh->prepare("SELECT vmid, name FROM vm_templates");
    $stmt->execute();
    $retVmArr = array();

    foreach($stmt->fetchAll() as $item){
        $retVmArr[$item['vmid']] = $item['name'];
    }

    return $retVmArr;
}

/**
 * @param string $pLabName
 * @param array $pVmIdArray
 * @return void
 * @throws Exception
 */
function createLab(string $pLabName, array $pVmIdArray){
    //Insert lab name and get auto incd labid
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("INSERT INTO labs (name)
                          VALUES (:lab_name)");
    $stmt->bindValue("lab_name", $pLabName);

    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $retConGroupId = $dbh->lastInsertId();
        $dbh->commit();

        $lvmSql = "INSERT INTO lab_vm_assignments (labid, vmid)
                   VALUES (:lab_name, :vmid)";
        //Cycle through vms in array inserting them into lab_vm_assignments
        foreach ($pVmIdArray as $tmpVmid) {
            $stmt = $dbh->prepare($lvmSql);
            $stmt->bindValue("lab_name", $retConGroupId);
            $stmt->bindValue("vmid", $tmpVmid);

            $dbh->beginTransaction();
            $stmt->execute();
            $dbh->commit();
        }

    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error creating lab ".$e->getMessage());
    }


}

/**
 * @param int $labid
 * @param int $user_group_id
 * @return int
 */
function getConnectionGroupId(int $pLabid, int $pUser_group_id) : int
{
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("SELECT connection_group_id FROM lab_group_assignments
                           WHERE labid = :labid AND user_group_id = :user_group_id");
    $stmt->bindValue("labid", $pLabid);
    $stmt->bindValue("user_group_id", $pUser_group_id);

    $stmt->execute();

    return $stmt->fetch()[0];
}

/**
 * @param int $pLabid
 * @param int $pUser_group_id
 * @param int $pConn_group_id
 * @return void
 * @throws Exception
 */
function createLabGroupAssignment(int $pLabid, int $pUser_group_id, int $pConn_group_id): void
{
    require __DIR__ . "/../configs/proxDbConfig.php";

    $stmt = $dbh->prepare("INSERT INTO lab_group_assignments (labid, user_group_id, connection_group_id)
                           VALUES (:labid, :user_group_id,:connection_group_id)");
    $stmt->bindValue("labid", $pLabid);
    $stmt->bindValue("user_group_id", $pUser_group_id);
    $stmt->bindValue("connection_group_id", $pConn_group_id);

    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error creating lab group assignment ".$e->getMessage());
    }
}

/**
 * @param int $pLabid
 * @param int $pUser_group_id
 * @return void
 * @throws Exception
 */
function deleteLabGroupAssignment(int $pLabid, int $pUser_group_id): void
{
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("DELETE FROM lab_group_assignments
                           WHERE labid = :labid AND user_group_id = :user_group_id");
    $stmt->bindValue("labid", $pLabid);
    $stmt->bindValue("user_group_id", $pUser_group_id);


    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error deleting lab perhaps invalid groupid and labid");
    }
}

/**
 * @param string $pConnGroupName
 * @return int gonnection_group_id
 * @throws Exception
 */
function createConnectionGroup(string $pConnGroupName) : int
{
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("INSERT INTO guacamole_db.guacamole_connection_group (connection_group_name)
                           VALUES (:connection_group_name)");
    $stmt->bindValue("connection_group_name", $pConnGroupName);



    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $retConGroupId = $dbh->lastInsertId();
        $dbh->commit();
        return $retConGroupId;
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error creating con group ".$e->getMessage());
    }
}

/**
 * @param int $pConnGroup
 * @return void
 * @throws Exception
 */
function deleteConnectionGroup(int $pConnGroup): void
{
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("DELETE FROM guacamole_db.guacamole_connection_group
                           WHERE connection_group_id = :connection_group_id");
    $stmt->bindValue("connection_group_id", $pConnGroup);


    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error deleting guacamole connection group".$e->getMessage());
    }
}

function setConnectionGroupPermissionsWithSelect(int $pUser_group_id, int $pConnGroupId, string $pPerm = "read") : void
{
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("INSERT INTO guacamole_db.guacamole_connection_group_permission
                           SELECT member_entity_id, :conn_group_id, :permission FROM guacamole_db.guacamole_user_group_member
                           WHERE user_group_id = :user_group_id");
    $stmt->bindValue("conn_group_id", $pConnGroupId);
    $stmt->bindValue("permission", $pPerm);
    $stmt->bindValue("user_group_id", $pUser_group_id);


    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error inserting group permissions".$e->getMessage());
    }
}

/**
 * Not in use currently but could be
 * @param $pUser_group_id
 * @return array
 */
function getUsersInUserGroup($pUser_group_id) : array
{
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("SELECT member_entity_id FROM guacamole_db.guacamole_user_group_member
                           WHERE user_group_id = :user_group_id");
    $stmt->bindValue("user_group_id", $pUser_group_id);

    $stmt->execute();

    $retUserArr = array();

    foreach($stmt->fetchAll() as $item){
        $retUserArr[] = $item['member_entity_id'];
    }

    return $retUserArr;
}

/**
 * @param string $pConnection_name "G{user_group_id}L{labid}U{entity_id}V{vmid}"
 * @return int connection_id
 */
function getConnectionIdFromConnName(string $pConnection_name) : int
{
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("SELECT connection_id FROM guacamole_db.guacamole_connection
                           WHERE connection_name = :conn_name");
    $stmt->bindValue("conn_name", $pConnection_name);

    $stmt->execute();


    return $stmt->fetch()[0];
}

/**
 * @param string $pPartialConnString
 * @return array
 */
function getUsersConnectionsWithLike(string $pPartialConnString) : array
{
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("SELECT connection_id FROM guacamole_db.guacamole_connection
                           WHERE connection_name LIKE :conn_name");
    $stmt->bindValue("conn_name", $pPartialConnString);

    $stmt->execute();

    $retConnArray = array();

    foreach($stmt->fetchAll() as $item){
        $retConnArray[] = $item['connection_id'];
    }

    return $retConnArray;
}

//create connection for each user * lab assigned VM's
//  insert into guacamole_connection
//  Values to set connection_id (autoinc), connection_name, parent_id, protocol
//      connection_name - "G".$user_groupid."L".$labid."U".$entity_id."V".$vmid

/**
 * @param int $pUser_group_id
 * @param int $pLabid
 * @param int $pVmid
 * @return void
 * @throws Exception
 */
function createConnections(int $pUser_group_id, int $pLabid, string $pProxmoxIP): void
{

    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";
    $stmt = $dbh->prepare("INSERT IGNORE INTO guacamole_db.guacamole_connection(connection_name, parent_id, protocol)
            SELECT CONCAT('G',gugm.user_group_id,'L',lgm.labid,'U',gugm.member_entity_id,'V',lvm.vmid) as connection_name,
            lgm.connection_group_id, 'vnc'
            FROM guacamole_db.guacamole_user_group_member AS gugm
            INNER JOIN lab_group_assignments AS lgm ON gugm.user_group_id = lgm.user_group_id
            INNER JOIN lab_vm_assignments AS lvm ON lgm.labid = lvm.labid
            WHERE gugm.user_group_id = :user_group_id AND lgm.labid = :labid
            ORDER BY lgm.labid ASC, gugm.member_entity_id ASC, lvm.vmid ASC");

    $stmt->bindValue("user_group_id", $pUser_group_id);
    $stmt->bindValue("labid", $pLabid);


    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error inserting group permissions".$e->getMessage());
    }

    //Setting permissions
    //I can't figure out the sql to do this right
    //first get list of users in group
    $usersArr = getUsersInUserGroup($pUser_group_id);
    //$dbh = require __DIR__."/../configs/proxDbConfig.php";

    echo "<br>";

    //Second get list of connections beloning to each user in a loop
    $sqlInsCon = "INSERT IGNORE INTO guacamole_db.guacamole_connection_permission
                  VALUES (:entity_id, :connection_id, :permission)";
    foreach($usersArr as $user){
        $tmpConName = "G".$pUser_group_id."L".$pLabid."U".$user."%";
        $tmpConArr = getUsersConnectionsWithLike($tmpConName);
        //echo "getUsersConn done";
        //var_dump($tmpConArr);

        //Screw it individual inserts
        foreach($tmpConArr as $conid){
            $stmt = $dbh->prepare($sqlInsCon);

            $stmt->bindValue("entity_id", $user);
            $stmt->bindValue("connection_id", $conid);
            $stmt->bindValue("permission", 'READ');


            try{
                $dbh->beginTransaction();
                $stmt->execute();
                $dbh->commit();
            }catch(PDOException $e){
                $dbh->rollback();
                throw new Exception("Error inserting group permissions".$e->getMessage());
            }
        }

    }

    //Do default connection parameters
    $tmpConName = "G".$pUser_group_id."L".$pLabid."%";
    try{
        connectionParameter('hostname', $pProxmoxIP, $tmpConName);
        connectionParameter('password','',$tmpConName);
        connectionParameter('port', '', $tmpConName);
    }catch(Exception $e){
        throw new Exception($e->getMessage());
    }



}

function connectionParameter(string $pParamName, string $pParamValue, string $pConNameLike){
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";

    $sqlParameters = "INSERT into guacamole_db.guacamole_connection_parameter
                      select connection_id, :parameter_name, :parameter_value from guacamole_db.guacamole_connection
                      where connection_name LIKE :conn_name_string";
    $stmt = $dbh->prepare($sqlParameters);

    $stmt->bindValue("parameter_name", $pParamName);
    $stmt->bindValue("parameter_value", $pParamValue);
    $stmt->bindValue("conn_name_string", $pConNameLike);

    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error inserting group permissions".$e->getMessage());
    }
}

/**
 * @param int $pEntity_id
 * @param int $pUser_group_id
 * @param int $pLabid
 * @param int $pVm_template_id
 * @return int|null
 */
function getAssignedVM(int $pEntity_id, int $pUser_group_id, int $pLabid, int $pVm_template_id) : ?int
{
    $dbh = require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("select vm_clone_id from user_assigned_vms
                           where entity_id = :entity_id and user_group_id = :user_group_id and
                                 labid = :labid and vm_template_id = :vm_template_id");
    $stmt->bindValue("entity_id", $pEntity_id);
    $stmt->bindValue("user_group_id", $pUser_group_id);
    $stmt->bindValue("labid", $pLabid);
    $stmt->bindValue("vm_template_id", $pVm_template_id);

    $stmt->execute();

    $result = $stmt->fetch();
    if(is_array($result) && key_exists('vm_clone_id', $result)){
        return $result['vm_clone_id'];
    }else{
        return NULL;
    }
}

/**
 * @param int $pEntity_id
 * @param int $pUser_group_id
 * @param int $pLabid
 * @param int $pVm_template_id
 * @param int $pVm_clone_id
 * @return void
 * @throws Exception
 */
function insertAssignedVM(int $pEntity_id, int $pUser_group_id, int $pLabid, int $pVm_template_id, int $pVm_clone_id): void
{
    require __DIR__ . "/../configs/proxDbConfig.php";


    $stmt = $dbh->prepare("INSERT INTO user_assigned_vms
                           VALUES (:entity_id, :user_group_id, :labid, :vm_template_id, :vm_clone_id)");
    $stmt->bindValue("entity_id", $pEntity_id);
    $stmt->bindValue("user_group_id", $pUser_group_id);
    $stmt->bindValue("labid", $pLabid);
    $stmt->bindValue("vm_template_id", $pVm_template_id);
    $stmt->bindValue("vm_clone_id", $pVm_clone_id);

    try{
        $dbh->beginTransaction();
        $stmt->execute();
        $dbh->commit();
    }catch(PDOException $e){
        $dbh->rollback();
        throw new Exception("Error inserting assigned vm ".$e->getMessage());
    }
}

?>