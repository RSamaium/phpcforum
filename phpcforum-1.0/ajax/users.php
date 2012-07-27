<?php
require_once('../commons.php');

if (isset($_GET['query'])) {
	$suggest = '';
	$query = $_GET['query'];
	$result = $db->query('SELECT username FROM ' . USERS . ' WHERE user_activ=1 AND username LIKE "'  . $query . '%" AND user_id != ' . ANONYMOUS_ID);
	while ($data = $result->fetch_array()) {
		$suggest .= "'" . $data['username'] . "',";
	}
	$suggest = preg_replace('#,$#', '', $suggest);
	
	echo "{
	 query: '$query',
	 suggestions:[$suggest]
	}";
}
?>
 

 