<?php
// Get name and labid from labs
// Get vmid(s) from lab_vm_assignments
// Get vmname from vm_temps

//TODO: Uncomment this
//$dbh = require "../configs/proxDbConfig.php";
//TODO: COMMENT THIS OUT

require __DIR__ . "/../../configs/proxDbConfig.php";
$stmt = $dbh->prepare("SELECT labs.labid, labs.name as LabName, vm_templates.vmid, vm_templates.name as VmName, 
       lab_group_assignments.user_group_id
FROM labs
INNER JOIN lab_vm_assignments ON labs.labid = lab_vm_assignments.labid
LEFT JOIN vm_templates ON lab_vm_assignments.vmid = vm_templates.vmid
LEFT JOIN lab_group_assignments ON labs.labid = lab_group_assignments.labid
ORDER BY labs.labid ASC, vm_templates.vmid ASC");
$stmt->execute();
$modArr = array();
$labid = INF;
$labname = "";
$vmname = array();
$grpid = array();
/*
$test = $stmt->fetchAll();
echo var_dump($test);
*/

echo "<br>";
foreach($stmt->fetchAll() as $item){
    $grpid = array();
    if($labid != $item['labid']){
        $labid = $item['labid'];
        $labname = $item['LabName'];
        $vmname[$item['vmid']] = $item['VmName'];
        //Prevent duplicates in array
        $grpid[$item['user_group_id']] = $item['user_group_id'];
        $modArr[$labid] = array('LabName'=>$labname, 'VmInfo'=>$vmname, 'grpid'=>$grpid);
    }else{
        //$vmname[] = $item["VmName"];
        $modArr[$labid]['VmInfo'][$item['vmid']] = $item['VmName'];
        $modArr[$labid]['grpid'][$item['user_group_id']] = $item['user_group_id'];
    }
}
echo "<table> <tr> <th>Lab ID</th> <th>Lab Name</th> <th>VM Info</th> <th>Assigned groups</th></tr>";
foreach ($modArr as $intLabID => $item) {
    echo "<tr> <td>".$intLabID."</td> <td>".$item['LabName']."</td><td><ul>";
    foreach($item['VmInfo'] as $vmid => $strVmName){
        echo "<li>".$vmid." - ".$strVmName."</li>";
    }
    echo "</ul></td>";

    echo "<td><ul>";
    foreach($item['grpid'] as $strGrpId){
        echo "<li>".$strGrpId."</li>";
    }
    echo "</ul></td></tr>";


}
echo "</table>";

