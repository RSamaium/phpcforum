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

$sid = isset($_GET['sid']) ? $_GET['sid'] : exit();
session_id($sid);

include('commons.php');

$api_key = requestVarPost('api_key', '');
if ($api_key != $config['api_key']) {
	exit();
}

$action = requestVarPost('action', '');
$params = requestVarPost('params', array());

switch ($action) {
	case 'users':
		$result = $db->select(USERS, 'user_id != ' . ANONYMOUS_ID);
		$row = array();
		while ($data = $result->fetch_assoc()) {
			$row[] = $data;
		}
		echo json_encode($row);
	break;
	case 'userByName':
		echo json_encode($db->dataUser($params['username']));
	break;
	case 'topics':
		echo json_encode($db->dataTopics($params['order'], $params['limit'], $params['message']));
	break;
	case 'topic':
		echo json_encode($db->dataTopic($params['topic_id']));
	break;
	case 'post':
		echo json_encode($db->dataPost($params['post_id']));
	break;
	case 'postNewTopic':
		echo json_encode(postNewTopic(true, $params['forum_id'], $params['post_subject'], $params['post_text'], '', array(), 0, 0, false));
	break;
	case 'replyTopic':
		echo json_encode(replyTopic(true, $params['forum_id'], $params['topic_id'], $params['post_subject'], $params['post_text']));
	break;
	case 'login':
		if ($user->isRegister()) {
			echo null;
		}
		else {
			echo json_encode(login());
		}
	break;
	case 'logout':
		if ($user->isRegister()) {
			echo json_encode(logout());
		}
		else {
			echo null;
		}
	break;
	case 'userIsRegister':
		echo json_encode($user->isRegister());
	break;
	case 'search':
		echo json_encode($db->searchKeyWords($params['start'], $config['nb_page_search'], $params['type_search'], $params['keywords'], $params['author'], true));
	break;
	case 'newUser':
		$error = array();
		echo json_encode(profile_register(true));
	break;
	case 'postNewPm':
		$error = array();
		echo json_encode(profile_send_pm(true, 'new'));
	break;
	case 'forgetPassword':
		$error = array();
		echo json_encode(profile_forget_pass(true));
	
	break;
	case 'usersOnline':
		echo json_encode(displayWhoOnline());
	break;
	

}



?> 
