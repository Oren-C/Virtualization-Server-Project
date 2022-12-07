<?php

session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ //If not logged
    header("location: ./login_form/login.php");
    exit;
}
// First step is to get labs
//  Get this from group numbers
//


require __DIR__ . "/../includes/configs/proxDbConfig.php";
$stmt = $dbh->prepare("select ge.entity_id, gug.user_group_id, gug.entity_id as group_entity_id, ge2.name as group_name 
from guacamole_db.guacamole_entity as ge 
inner join guacamole_db.guacamole_user_group_member as gugm on ge.entity_id = gugm.member_entity_id
inner join guacamole_db.guacamole_user_group as gug on gugm.user_group_id = gug.user_group_id
inner join guacamole_db.guacamole_entity as ge2 on gug.entity_id = ge2.entity_id
where ge.entity_id = :entity
order by user_group_id ASC");

$stmt->bindValue("entity", $_SESSION["entity_id"]);
$stmt->execute();
$studentAndClasses = array();
foreach ($stmt->fetchAll() as $item){
    $studentAndClasses[$item['user_group_id']] = array('entity_id'=>$item['entity_id'],
        'group_entity_id'=>$item['group_entity_id'], 'group_name'=>$item['group_name'], 'lab_group'=>NULL);

}
//$studentAndClasses = $stmt->fetchAll();
//I have users groups it is in
//so now fetch proxvms database vms assigned to those groups / labs

$stmt->closeCursor();

foreach ($studentAndClasses as $groupid => $groupinfo){
    //echo "here is group id ".$groupid." ".$groupinfo['group_name'];
    //var_dump($groupinfo);
    $stmt = $dbh->prepare("select lvm.labid, vt.vmid, vt.name from vm_templates as vt
                                inner join lab_vm_assignments as lvm on vt.vmid = lvm.vmid
                                join lab_group_assignments as lga on lvm.labid = lga.labid
                                where lga.user_group_id = :user_group_id
                                order by lvm.labid ASC, vt.vmid ASC");

    $stmt->bindValue("user_group_id", (int)$groupid);
    $stmt->execute();

    $labArray = array();

    foreach ($stmt->fetchAll() as $item){
        //echo var_dump($item);
        $labArray[$item['labid']][] = array($item['vmid'] => $item['name']);
    }
    //var_dump($labArray);
    $studentAndClasses[$groupid]['lab_group'] = $labArray;
    $stmt->closeCursor();
}

/*
var_dump($labArray);
echo "poop";
//var_dump($labArray[1]);
foreach ($labArray as $labid => $lab){
    foreach($lab as $vmgroup){
        foreach ($vmgroup as $vmid => $vmname){
            echo "labid: ".$labid." vmid: ".$vmid." ".$vmname;
        }
    }

}
*/
//echo "poop";
//var_dump($studentAndClasses);

foreach ($studentAndClasses as $item){
    //Further devide by lab


}


?>

<!DOCTYPE html>
<html>
<title>My Dashboard</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="./style.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--<script type="text/javascript" src="./servers.js"></script>-->
<style>
html,body,h1,h2,h3,h4,h5 {font-family: "Raleway", sans-serif}
</style>
<body class="w3-light-grey" onload="updateInfo()">

<!-- Top container -->
<div class="w3-bar w3-top w3-black w3-large" style="z-index:4">
  <span class="w3-bar-item w3-left">
    Welcome, <?php echo $_SESSION["username"]; ?> - <a href="./login_form/logout.php">Logout</a>
      <a href="serversv2.php">Home</a>
      <?php if($_SESSION["admin"]) : ?>
          <a href="./admintools/adminhome.php">Admin Home</a>
      <?php endif; ?>
  </span>
  <span class="w3-bar-item w3-right">VirtualMachines</span>
</div>

<!-- CONTENT -->
<div class="w3-main" style="margin-left:200px;margin-top:43px;margin-right:200px;">

  <!-- Header -->
  <header class="w3-container" style="padding-top:22px">
    <h5><b><i class="fa fa-dashboard"></i> My Dashboard</b></h5>
  </header>

  <div class="w3-row-padding w3-margin-bottom">
    <div class="w3-quarter">
      <div class="w3-container w3-red w3-padding-16">
        <div class="w3-left"><i class="fa fa-comment w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3>-</h3>
        </div>
        <div class="w3-clear"></div>
        <h4>Machines ON</h4>
      </div>
    </div>
    <div class="w3-quarter">
      <div class="w3-container w3-blue w3-padding-16">
        <div class="w3-left"><i class="fa fa-eye w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3>-</h3>
        </div>
        <div class="w3-clear"></div>
        <h4>Free Slots</h4>
      </div>
    </div>
    <div class="w3-quarter">
      <div class="w3-container w3-teal w3-padding-16">
        <div class="w3-left"><i class="fa fa-share-alt w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3><?php echo $_SESSION["slots"]; ?></h3>
        </div>
        <div class="w3-clear"></div>
        <h4>Your Available Slots</h4>
      </div>
    </div>
    <!--
    <div class="w3-quarter">
      <div class="w3-container w3-orange w3-text-white w3-padding-16">
        <div class="w3-left"><i class="fa fa-users w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3>TO DO</h3>
        </div>
        <div class="w3-clear"></div>
        <h4>Users</h4>
      </div>
    </div>
    -->
  </div>

  <div class="w3-panel">
  <h5><i class="fa fa-laptop"></i> My classes</h5>
    <div class="w3-row-padding" style="margin:0 -16px">
      <div class="w3-container">
        <table class="w3-table w3-white">
          <!--
          <tr>
            <td><i class="fa fa-user w3-text-blue w3-large"></i>&nbsp;&nbsp; Windows 10 - Basic </td>
            <td id="w10">
              <iframe id="framew10" src="machine_w10_simple.php" height=60 width=100% style="border:none;" onload="frameLoadedW10()"></iframe>
            </td>
            <td class="timer"><img src="./img/crono.png"><i>
              <?php
              /*
                if(isset($_SESSION['timeStartedW10'])){
                  $now = time();
                  $timeSince = $now - $_SESSION['timeStartedW10'];
                  $remainingSeconds = 300 - $timeSince; //5 min
                  if($remainingSeconds < 1){ //Only occurs when the page is reloaded
                    $_SESSION["slots"]++;
                    $_SESSION["ons"]--;
                    stopVM("w10-simple-clone-" . $_SESSION["username"]); //TODO: Check needed
                    deleteVM("w10-simple-clone-" . $_SESSION["username"]);
                    unset($_SESSION['timeStartedW10']);
                  }
                }
                */
              ?>
              <div id="w10time"></div></i></td>
            <td class="connect"><a href="http://osc3b.my.to:3333/guacamole/" target="_blank">
              <img src="./img/connect<?php //if(!isset($_SESSION['timeStartedW10'])){ echo "Off"; }?>.png" alt="Connect"></a></td>
          </tr>
          <tr>
            <td><i class="fa fa-user w3-text-red w3-large"></i>&nbsp;&nbsp; Ubuntu 20.04 </td>
            <td id="u20">
              <iframe id="frameu20" src="" height=60 width=100% style="border:none;" onload=""></iframe>
            </td>
            <td><i>Off</i></td>
            <td class="connect"><a href="http://osc3b.my.to:3333/guacamole/" target="_blank"><img src="./img/connectOff.png" alt="Connect"></a></td>
          </tr>
          <tr>
            <td><i class="fa fa-user w3-text-green w3-large"></i>&nbsp;&nbsp; Debian 10 </td>
            <td id="d10">
              <iframe id="framed10" src="" height=60 width=100% style="border:none;" onload=""></iframe>
            </td>
            <td><i>Off</i></td>
            <td class="connect"><a href="http://osc3b.my.to:3333/guacamole/" target="_blank"><img src="./img/connectOff.png" alt="Connect"></a></td>
          </tr>
          <tr>

            <td><i class="fa fa-user w3-text-yellow w3-large"></i>&nbsp;&nbsp; ... </td>
            <td><iframe src="" height=60 width=100% style="border:none;"></iframe></td>
            <td><i>Off</i></td>
            <td class="connect"><a href="http://osc3b.my.to:3333/guacamole/" target="_blank"><img src="./img/connectOff.png" alt="Connect"></a></td>
          </tr>
          -->
            <?php foreach($studentAndClasses as $groupid => $group) : ?>
                <tr>
                    <td>
                        <table class="w3-table w3-striped w3-white">
                            <th>
                                <?php echo $group['group_name']; ?>
                            </th>
                            <tr>
                            <?php foreach($group['lab_group'] as $labid => $lab) : ?>
                                <tr>
                                    <table class="w3-table w3-white">
                                        <tr>
                                            <td>
                                                <?php echo "Lab: ".$labid; ?>
                                            </td>
                                        </tr>
                                        <?php foreach($lab as $vmgroup) : ?>
                                            <?php foreach($vmgroup as $vmid => $vmname) : ?>


                                                <tr>
                                                    <td>

                                                    <td><i class="fa fa-user w3-text-blue w3-large"></i>&nbsp;&nbsp; VM - <?php echo $vmname; ?> </td>
                                                    <td id="w10">
                                                        <iframe id="framew10" src="machine_generic.php?<?php echo "group={$groupid}&labid={$labid}&vmid={$vmid}"?>" height=60 width=100% style="border:none;"></iframe>
                                                    </td>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </table>
                                </tr>
                            <?php endforeach; ?>
                            </tr>

                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>

          
        </table>
      </div>
    </div>
  </div>
  <hr>
  <div class="w3-container">
    <h5><i class="fa fa-bell"></i> Stats</h5>
    <p>Your Machines ON</p>
    <div class="w3-grey">
      <div id="onBar" class="w3-container w3-center w3-padding w3-green" style="width:4%"><?php echo $_SESSION["ons"]; ?>/3</div>
    </div>

    <p>Your Machines Slots</p>
    <div class="w3-grey">
      <div id="slotsBar" class="w3-container w3-center w3-padding w3-red" style="width:4%"><?php echo 3-$_SESSION["slots"]; ?>/3</div>
    </div>
  </div>
  <hr>

  <div class="w3-container">
  <br>

  <!-- Footer -->
  <footer class="w3-container w3-padding-16 w3-dark-grey">
    <h4>Made by Oscar Boronat. Modified for use at SFA by Oren Cummings</h4>
    <p>Code available in GitHub: <a href="https://github.com/osc3b/proxmox-guacamole-client">https://github.com/osc3b/proxmox-guacamole-client</a></p>
    <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
  </footer>

</div>

<script type='text/javascript'>

//This functions needs to be on the php code to work, the remaining ones are on the .js

function setupRefresh() {
    setTimeout("updateInfo();", 100); // milliseconds
}

var reloaded = false;

function updateInfo(){
  /*
    var timeNow = Math.floor(Date.now() / 1000);
    timeW10 = "<?php 
      if(isset($_SESSION['timeStartedW10']))
        echo $_SESSION['timeStartedW10'];
      else
        echo "null";
    ?>";
    var timeSince = timeNow - timeW10;
    var timeLeft = 300 - timeSince;
    if(timeLeft > 0){ //Windows 10 timer
        document.getElementById("w10time").innerHTML = timeLeft + " segs";
        reloaded = false;
    }else{
        document.getElementById("w10time").innerHTML = "-";
        if(timeLeft <= 0 && !reloaded){ //NaN values are not valid
          reloaded = true;
          console.log("Time W10 Consumed. Reload.");
          window.location = window.location.href; //reload
        }
    }
    var slots = "<?php echo 3-$_SESSION["slots"]; ?>"
    if(slots <= 0) //Slots bar
      document.getElementById("slotsBar").style.width = "4%";
    else{
      var p = slots * 33;
      document.getElementById("slotsBar").style.width = p + "%";
    }
    var ons = "<?php echo $_SESSION["ons"]; ?>"
    if(ons <= 0) //On bar
      document.getElementById("onBar").style.width = "4%";
    else{
      var p = ons * 33;
      document.getElementById("onBar").style.width = p + "%";
    }
      
    console.log("Info updated.");
    setupRefresh();
    */
}

</script>

</body>
</html>