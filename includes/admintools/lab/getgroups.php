<?php
// Get name and labid from labs
// Get vmid(s) from lab_vm_assignments
// Get vmname from vm_temps

//TODO: Uncomment this
//$dbh = require __DIR__"/../../configs/proxDbConfig.php";
//TODO: COMMENT THIS OUT

require __DIR__ . "/../../configs/proxDbConfig.php";


$stmt = $dbh->prepare("SELECT gug.user_group_id, gug.entity_id, ge.name FROM guacamole_db.guacamole_user_group AS gug
INNER JOIN guacamole_db.guacamole_entity as ge ON gug.entity_id = ge.entity_id
ORDER BY gug.user_group_id ASC");
$stmt->execute();


echo "<br>";
$groupInfo = array();
foreach($stmt->fetchAll() as $item){
    $groupInfo[$item['user_group_id']] = $item['name'];
}
echo "<table> <tr> <th>Group ID</th> <th>Group Name</th> <th>VM Info</th> <th>Assigned groups</th></tr>";
foreach ($groupInfo as $groupId => $name) {
    echo "<tr> <td>".$groupId."</td> <td>".$name."</td></tr>";



}
echo "</table>";

