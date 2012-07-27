<?php
require_once('../includes/adm/adm_commons.php');

if (isset($_GET['query'])) {
	$suggest = '';
	$query = $_GET['query'];
	$result = $db->query('SELECT tag FROM ' . SEARCH_TAGS . ' WHERE tag LIKE "'  . $query . '%"');
	while ($data = $result->fetch_array()) {
		$suggest .= "'" . $data['tag'] . "',";
	}
	$suggest = preg_replace('#,$#', '', $suggest);
	
	echo "{
	 query: '$query',
	 suggestions:[$suggest]
	}";
}
?>
 

 