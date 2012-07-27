<?php
/**
Copyright © Samuel Ronce 2010
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated 
documentation files (the "Software"), to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and 
to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions 
of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT 
LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/
function searchUser($username, $exception = null) {
	global $db;
	/*$sql = 'SELECT user_id FROM ' . USERS . ' 
			WHERE username="' . htmlspecialchars($username) . '"';
	$result = $db->query($sql);
	$data = $result->fetch_array();*/
	$data = $db->dataUser($username, $exception);
	if (empty($data))
		return false;
	else
		return $data['user_id'];
}


function userData($user_id, $select = null) {
	global $db;
	/*$result = $db->query('SELECT ' . ($select == null ? '*' :  implode(',', $select)) . ' FROM ' . USERS . ' u, ' . GROUPS . ' g
						  WHERE u.group_id=g.group_id AND user_id=' . $user_id);
	return $data = $result->fetch_array();*/
	return  $db->dataGlobalUser($user_id, $select);
}

function groupData($group_id, $select = null) {
	global $db;
	$result = $db->query('SELECT ' . ($select == null ? '*' :  implode(',', $select)) . ' FROM . ' . GROUPS . '
						  WHERE group_id=' . $group_id);
	return $data = $result->fetch_array();
}

function synchronizedMembers($only_activ = true) {
	global $db;
	$where = '';
	if ($only_activ) {
		$where = 'user_activ=1 AND ';
	}
	$where .= 'user_id != ' . ANONYMOUS_ID;
	$data = $db->select(USERS, $where, null, null, 'COUNT(*) AS nb_member', 'one');
	return $db->update(CONFIG, array(
		'config_value' 	=>	$data['nb_member']
	), 'config_name="total_users"');
}

?>