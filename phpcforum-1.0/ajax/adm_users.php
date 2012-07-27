<?php
require_once('../commons.php');

if (isset($_GET['term'])) {
	$json = array();
	$query = $_GET['term'];
	$result = $db->query('SELECT username, user_id FROM ' . USERS . ' WHERE user_activ=1 AND username LIKE "'  . $query . '%" AND user_id != ' . ANONYMOUS_ID);
	while ($data = $result->fetch_array()) {
		$json[] = array(
			'id' => $data['user_id'],
			'label' => $data['username'],
			'value' => $data['username']
		);
	}
	$suggest = preg_replace('#,$#', '', $suggest);
	echo json_encode($json);
}
?>
 

 