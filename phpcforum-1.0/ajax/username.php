<?php
require_once('../commons.php');

$username = isset($_GET['username']) ? utf8_decode($_GET['username']) : '';
$user_id = requestVar('user_id', null);

if (searchUser($username, $user_id) === false) {
	echo 'true';
}
else
	echo 'false';
?>
 

 