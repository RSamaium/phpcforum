<?php
require_once('../commons.php');

$username = isset($_GET['username']) ? utf8_decode($_GET['username']) : '';
$username = isset($_GET['group_founder_manage']) ? utf8_decode($_GET['group_founder_manage']) : '';

if (searchUser($username) === false) {
	echo 'false';
}
else
	echo 'true';
?>