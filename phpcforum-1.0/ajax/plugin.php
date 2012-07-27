<?php
$ajax = true;
require_once('../commons.php');
$data= requestVarPost('data', null);
Plugins::flag('ajax', array($data));
?>
 

 