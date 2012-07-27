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
require_once('../commons.php');
require_once('../includes/adm/adm_functions.php');

$post = requestVarPost('f', '');
if ($user->session_data['session_browser'] == "Shockwave Flash") {
	switch ($post) {
		case 'userAvatar':
			$user_id = requestVarPost('user_id', '');
			echo uploadAvatar($user_id);
		break;
		case 'uploadIcon':
			echo uploadIcon();
		break;
	}
	
}
elseif ($user->isCan($permission['users']['admin_style']) && $post == 'changeTmpDesign') {
	$selector_name = requestVarPost('selector_name', '');
	$field = requestVarPost('field', '');
	$value = requestVarPost('value', '');
	
	$suffixe = '';
	if ($field != 'selector_activ') {
		$suffixe = '_m';
	}
	echo $db->update(DESIGN, array($field . $suffixe => $value), 'selector_name="' . $selector_name . '"');
	
}
elseif ($user->in_admin_panel) {
	$orderforum = requestVarPost('order', '');
	$id = requestVarPost('id', '');
	switch ($post) {
		case 'lastForum': 
			$result = $db->query('SELECT forum_id FROM ' . FORUMS . ' ORDER BY forum_id DESC');
			$data = $result->fetch_array();
			echo $data['forum_id'];
		break;
		case 'insertForumDefault': 
			$db->insert(FORUMS, array(
				'forum_name'	=>	_t('Nouveau forum'),
				'forum_desc'	=> '',
				'forum_rules'	=> ''
			));
			echo $db->insert_id;
		break;
		case 'updateOrderForum': 
			echo $db->update(CONFIG, array('config_value' => $orderforum), 'config_name="order_forums"');
		break;
		case 'insertPermission': 
			$result = $db->select(GROUPS);
			$insert = 'INSERT INTO ' . GROUPS_PERMISSION . ' (group_id, forum_id, group_permission) VALUES ';
			$permission = 'df';
			while ($data = $result->fetch_array()) {
				switch ($data['group_id']) {
					case GROUP_ADMIN_ID:
						$permission = '1fffffff';
					break;
					case GROUP_VISITOR_ID:
						$permission = '5';
					break;
					case GROUP_MEMBER_ID:
						$permission = 'df';
					break;
					case GROUP_MODO_ID:
						$permission = '1fff97ff';
					break;	
				}
				$insert .= '(' . $data['group_id'] . ', ' . $id . ', "' . $permission . '"),';
			}
			$insert = preg_replace('#,$#', '', $insert);
			echo json_encode(array('success' => $db->query($insert)));
			
		break;
		case 'changePermission': 
			$mode = requestVarPost('mode', '');
			$left_id = requestVarPost('left_id', '');
			$right_id = requestVarPost('right_id', '');
			$a_permission = requestVarPost('permission', '');
			
			switch($mode) {
				case 'forum_user':
				case 'all_forum_group':
				case 'forum_group':
					$type_permission = 'forum';
				break;
				case 'user':
				case 'group':
					$type_permission = 'users';
				break;
				
			}
			
			$size = count($a_permission);
			$str_permis = 0x0;
			for ($i=0; $i < $size ; $i++) {
				$str_permis += $permission[$type_permission][$a_permission[$i]];
			
			}
			$str_permis = dechex($str_permis);
			if ($mode == 'forum_group' || $mode == 'all_forum_group') {
				$condition = 'forum_id=' . $right_id . ' AND group_id =' . $left_id;
				$data = $db->select(GROUPS_PERMISSION, $condition, null, null, 'group_id', 'one');
				if (isset($data['group_id'])) {
					echo $db->update(GROUPS_PERMISSION, array('group_permission' => $str_permis), $condition);
				}
				else {
					echo $db->insert(GROUPS_PERMISSION, array(
						'group_id' 			=> $left_id,
						'forum_id'			=> $right_id,
						'group_permission' 	=> $str_permis
					));
				}
			}
			elseif ($mode == 'forum_user') {
				$condition = 'forum_id=' . $right_id . ' AND user_id =' . $left_id;
				$data = $db->select(USERS_PERMISSION, $condition, null, null, 'user_id', 'one');
				if (isset($data['user_id'])) {
					echo $db->update(USERS_PERMISSION, array('user_permission' => $str_permis), $condition);
				}
				else {
					echo $db->insert(USERS_PERMISSION, array(
						'user_id' 			=> $left_id,
						'forum_id'			=> $right_id,
						'user_permission' 	=> $str_permis
					));
				}
			}
			elseif ($mode == 'user') {
				echo $db->update(USERS, array('user_permissions' => $str_permis), 'user_id =' . $left_id);
			}
			elseif ($mode == 'group') {
				echo $db->update(GROUPS, array('group_permissions' => $str_permis), 'group_id=' . $left_id);
			}
			
		break;
		case 'plugins': 
			$filename = requestVarPost('name', '');
			include('../plugins/' . $filename . '/adm/adm_' . $filename . '.class.php');
			$name_class = 'Adm' . ucfirst($filename);
			new $name_class($filename);
		break;
		case 'installPlugin': 
			$plugin_name = requestVarPost('plugin_name', '');
			intallPlugin($plugin_name);
		break;
		case 'activLastPlugin': 
			$data = $db->select(PLUGINS, null, 'plugin_id DESC', null, 'plugin_id', 'one');
			echo $db->update(PLUGINS, array('plugin_activ' => 1), 'plugin_id=' . $data['plugin_id']);
		break;
		case 'pluginHowInstall':
			$plugin_name = requestVarPost('plugin_name', '');
			echo displayHowInstallPlugin($plugin_name);
		break;
		case 'activPlugin': 
			$plugin_name = requestVarPost('plugin_name', '');
			$activ = requestVarPost('activ', 1);
			echo $db->update(PLUGINS, array('plugin_activ' => $activ), 'plugin_filename="' . $plugin_name . '"');
		break;
		case 'deletePlugin': 
			$plugin_name = requestVarPost('plugin_name', '');
			echo deletePlugin($plugin_name);
		break;
		case 'userPermission':
			$username = requestVarPost('username', '');
			$user_id = searchUser($username);
			if ($user_id !== false) {
				echo json_encode(array('user_id' => $user_id));
			}
			else {
				echo json_encode(array('user_id' => 0));
			}
		break;
		case 'userPref':
			$user_id = requestVarPost('user_id', '');
			echo settingUserPref($user_id);
		break;
		case 'userComment':
			$user_id = requestVarPost('user_id', '');
			$user_comment = requestVarPost('user_comment', '');
			echo $db->update(USERS, array('user_comment' => $user_comment), 'user_id=' . $user_id);
		break;
		case 'userSign':
			$user_id = requestVarPost('user_id', '');
			$user_sig = requestVarPost('user_sig', '');
			echo $db->update(USERS, array('user_sig' => htmlDoubleQuote($user_sig)), 'user_id=' . $user_id);
		break;
		case 'userGlobalProfil':
			$user_id = requestVarPost('user_id', '');
			$username = requestVarPost('username', '');
			$user_password = requestVarPost('user_password', '');
			$user_password_confirm = requestVarPost('user_password_confirm', '');
			$user_email = requestVarPost('user_email', '');
			
			$update = array(
				'username' 		=> $username,
				'user_email' 	=> $user_email
			);
			if ($user_password != '' && $user_password == $user_password_confirm) {
				$update['user_password'] = md5($user_password);
			}
			
			echo $db->update(USERS, $update, 'user_id=' . $user_id);
		break;
		case 'groupDefault':
			$user_id = requestVarPost('user_id', '');
			$group_id = requestVarPost('group_id', '');
			echo $db->update(USERS, array('group_id' => $group_id), 'user_id=' . $user_id);
		break;
		case 'userGroupDelete': 
			$user_id = requestVarPost('user_id', '');
			$group_id = requestVarPost('group_id', '');
			echo $db->delete(USERS_GROUP, array('group_id' => $group_id, 'user_id' => $user_id));
		break;
		case 'userGroupAdd': 
			$user_id = requestVarPost('user_id', '');
			$group_id = requestVarPost('group_id', '');
			echo $db->insert(USERS_GROUP, array(
					'group_id' 			=> $group_id, 
					'user_id' 			=> $user_id, 
					'user_status' 		=> 1,
					'user_date_joined'	=> time()
			));
		break;
		case 'groupDelete': 
			$group_id = requestVarPost('group_id', '');
			$result = $db->select(USERS_GROUP . ' ug, ' . USERS . ' u', 'ug.user_id=u.user_id AND ug.group_id=u.group_id AND ug.group_id=' . $group_id);
			$str = '';
			while ($data = $result->fetch_assoc()) {
				$str .= $data['user_id'] . ',';
			}
			$str = preg_replace('#,$#', '', $str);
			if ($str != '') {
				$db->update(USERS, array('group_id' => GROUP_MEMBER_ID), 'user_id IN (' . $str . ')');
			}
			$b = $db->delete(USERS_GROUP, array('group_id' => $group_id));
			$b &= $db->delete(GROUPS, array('group_id' => $group_id));
			echo $b;
		break;
		case 'activUserInactive':
			$users_id = requestVarPost('users_id', '');
			$users_id = preg_replace('#,$#', '', $users_id);
			synchronizedMembers();
			echo $db->update(USERS, array('user_activ' => 1), 'user_id IN (' . $users_id . ')');
		break;
		case 'deleteUserInactive':
			$users_id = requestVarPost('users_id', '');
			$users_id = preg_replace('#,$#', '', $users_id);
			synchronizedMembers();
			echo $db->query('DELETE FROM ' . USERS . ' WHERE user_id IN (' . $users_id . ')');
		break;
		case 'deleteforum':
			$forum_id = requestVarPost('forum_id', '');
			$new_forum_id = requestVarPost('new_forum_id', '');
			$b = 0;
			if ($forum_id != '' && $new_forum_id != '') {
				$b  = $db->delete(FORUMS, array('forum_id' => $forum_id));
				$b  &= $db->delete(GROUPS_PERMISSION, array('forum_id' => $forum_id));
				$b  &= $db->delete(USERS_PERMISSION, array('forum_id' => $forum_id));
				if ($b) {
					$b &= $db->update(TOPICS, array('forum_id' => $new_forum_id), 'forum_id=' . $forum_id);
					$b &= $db->update(POSTS, array('forum_id' => $new_forum_id), 'forum_id=' . $forum_id);
					$b &= $db->update(TOPICS_READ, array('forum_id' => $new_forum_id), 'forum_id=' . $forum_id);
					synchronizedForum($new_forum_id);
				}
			}
			echo $b;
		break;
		case 'userActiv':
			$user_id = requestVarPost('user_id', '');
			$activ = requestVarPost('activ', '');
			
			if ($activ == 0) {
				$db->update(USERS, array('user_activ_reason' => 1), 'user_id=' . $user_id);
			}
			$b = $db->update(USERS, array('user_activ' => $activ), 'user_id=' . $user_id);
			synchronizedMembers();
			echo $b;
		break;
		case 'userGlobal':
			$user_id = requestVarPost('user_id', '');
			$forum_id = requestVarPost('forum_id', '');
			$mode = requestVarPost('mode', '');
			if ($user_id != FONDATOR_ID) {
				switch($mode) {
					case 'move_all_msg': 
						echo userGlobalMoveAllMsg($user_id, $forum_id);
					break;
					case 'delete_all_msg': 
						echo userGlobalDeleteAllMsg($user_id);
					break;
					case 'delete_user': 
						echo userGlobalDeleteUser($user_id);
					break;
					
				}
			}
		break;
		case 'selectorDesign': 
			$result = $db->select(DESIGN);
			$row = array();
			while ($data = $result->fetch_array()) {
				$row[] = $data['selector_name'];
			}
			echo json_encode($row);
		break;
		case 'changeDesign': 
			$mode = requestVarPost('mode', '');
			switch ($mode) {
					case 'submit_tmp_design': 
						echo submitTmpDesign();
					break;
					case 'init_tmp_design': 
						echo initTmpDesign();
					break;
				}
		break;
		case 'changeImageSet': 
			$images = requestVarPost('images', '');
			$b = true;
			for ($i=0 ; $i < sizeof($images) ; $i++) {
				$b &= $db->update(DESIGN_IMAGESET, array(
					'image_filename' 	=> $images[$i]["image_filename"],
					'image_height' 		=> $images[$i]["image_height"],
					'image_width' 		=> $images[$i]["image_width"],
					'image_lang' 		=> $images[$i]["image_lang"],
				), 'image_id=' . $images[$i]["image_id"]);
			}
			echo $b;
		break;
		case 'langDefault':
			$lang = requestVarPost('lang', '');
			echo $db->update(CONFIG, array('config_value' => $lang), 'config_name="lang_default"');
		break;
		case 'update':
			$last_version = requestVarPost('last_version', 0);
			if ($last_version != 0) {
				echo update($last_version);
			}
			else  {
				echo 0;
			}
		break;
		case 'changeTheme': 
			$theme = requestVarPost('theme', 'smoothness');
			$user->setCookie('theme', $theme, 'phpcforum_adm', '/');
		break;
		case 'admNote': 
			$adm_note = requestVarPost('adm_note', '');
			echo $db->update(CONFIG, array('config_value' => htmlspecialchars($adm_note)), 'config_name="admin_note"');
		break;
		case 'deleteInstall': 
			echo deleteInstall();
		break;
		case 'selectTemplates': 
			$tpl_id = requestVarPost('tpl_id', 0);
			echo selectTemplate($tpl_id);
		break;
		case 'updateTemplate':
			$tpl_id = requestVarPost('tpl_id', 0);
			$content = requestVarPost('content', '');
			echo updateTemplate($tpl_id, $content);
		break;
		case 'changeTemplate':
			echo changeTemplates();
		break;
		case 'settingIconset':
			$mode = requestVarPost('iconset_mode', '');
			echo json_encode(settingIconset($mode));
		break;
		case 'deleteIconset':
			$iconset_id = requestVarPost('iconset_id', 0);
			echo json_encode(deleteIconset($iconset_id));
		break;
		case 'deleteIcon':
			$icon_id = requestVarPost('icon_id', 0);
			echo json_encode(deleteIcon($icon_id));
		break;
		case 'readIconset':
			$iconset_id = requestVarPost('iconset_id', 0);
			echo json_encode($db->select(ICONSET, 'iconset_id=' . $iconset_id, null, null, '*', 'one'));
		break;
		case 'readIcon':
			$icon_id = requestVarPost('icon_id', 0);
			echo json_encode($db->select(ICONS, 'icon_id=' . $icon_id, null, null, '*', 'one'));
		break;
		case 'settingIcon':
			$mode = requestVarPost('icon_mode', 'add');
			echo json_encode(settingIcon($mode));
		break;
		case 'userProfile':
			echo panelSettingUser(true);
		break;
		case 'settingForum':
			$mode = requestVarPost('mode', '');
			echo panelForumCrud($mode, true);
		break;
		case 'settingGroup':
			$mode = requestVarPost('mode', '');
			echo panelGroupCrud($mode, true);
		break;
		case 'positionIcons':
			$icon = requestVarPost('icon', array());
			echo json_encode(positionIcons($icon));
		break;
		
		
	}
}

?>
 

 