<?php
require_once __DIR__ . "/../../includes/integrations/proxmoxcommands.php";

$configs = require(__DIR__ . '/../../includes/configs/proxAuth.php');
$node = $configs['node'];
$vmNetwork = $configs['vmnetwork'];
$proxLanNetwork = $configs['proxlannetwork'];
$gateway = $configs['vmgateway'];
$dhcpServer = $configs['vmdhcpserver'];
echo 'before';
