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

include('commons.php');

$start = requestVar('start', 0, 'unsigned int');
$member = requestVar('u', 0, 'unsigned int');
$mode = requestVar('mode', 'memberlist');
$search = htmlspecialchars(requestVar('m', ''));

$error_txt = '';

if ($mode == 'viewprofile' || $mode == 'direct_viewprofile_p1' || $mode == 'direct_viewprofile_p2') {
	if (idIsExist($member, 'user') && $member != ANONYMOUS_ID) {
		$template->assignVars(viewprofile(userData($member)));
		$template->assignVars(array(
			'L_REGDATE'				=> _t('Inscrit le'),
			'L_LASTVISIT'			=> _t('Dernière visite le'),
			'L_INFO'				=> _t('Informations générales'),
			'L_AVERT'				=> _t('Avertissement'),
			'L_NB_POST'				=> _t('Nombre de messages'),
			'L_PERSONNAL_INFO'		=> _t('Informations personnelles'),
			'L_FROM'				=> _t('Localisation'),
			'L_JOB'					=> _t('Emploi'),
			'L_HOBBIES'				=> _t('Loisirs'),
			'L_BIRTHDAY'			=> _t('Age'),
			'L_SEX'					=> _t('Sexe'),
			'L_WEBSITE'				=> _t('Site Internet'),
			'L_CONTACT'				=> _t('Adresses de contact'),
			'L_MSN'					=> _t('MSN'),
			'L_YAHOO'				=> _t('Yahoo!'),
			'L_SKYPE'				=> _t('Skype'),
			'L_FACEBOOK'			=> _t('Facebook'),
			'L_TWITTER'				=> _t('Twitter'),
			'L_CONTACT_PM'			=> _t('Contacter par message privé'),
			'L_SIGN'				=> _t('Signature'),
			'L_COMMENT_USER'		=> _t('Commentaire sur ce membre'),
			'L_PROFILE_OF'			=> _t('Profil de')
		));
	 }
	 else {
		$mode = 'memberlist';
	 }
}
elseif($mode == 'groups') {
	$group_id = requestVar('g', 0, 'unsigned int');
	$user_group = requestVarPost('user_group', 0, '');
	$submit = isset($_POST['submit_user_group']);
	$submit_join = isset($_POST['submit_join_group']);
	$submit_attent = isset($_POST['submit_user_attent_group']);
	$submit_delete = isset($_POST['submit_user_delete_group']);
	$error = array();
	
	if (idIsExist($group_id, 'group') && $group_id != GROUP_VISITOR_ID && $group_id != GROUP_MEMBER_ID) {
		
		$sql_group = 'SELECT group_founder_manage, group_name, group_desc, group_color, group_type FROM ' . USERS . ' u, ' . GROUPS . ' g 
				WHERE g.group_founder_manage=u.user_id AND g.group_id=' . $group_id;
		$result_group = $db->query($sql_group);
		$data_group = $result_group->fetch_array();
		
		$result = $db->query('SELECT user_id FROM ' .  USERS_GROUP . ' WHERE group_id=' . $group_id . ' AND user_id=' . $user->data['user_id']);
		$data_user_group = $result->fetch_array();
		
		$group_manager = $data_group['group_founder_manage'];
		
		if ($submit && $user->isCan($permission['users']['add_del_user_group'])) {
			if ($user_group != '') {
				$user_group_id = searchUser($user_group);
				if ($user_group_id !== false) {
					$result = $db->query('SELECT user_id FROM ' .  USERS_GROUP . ' WHERE group_id=' . $group_id . ' AND user_id=' . $user_group_id);
					$data = $result->fetch_array();
					
					if (empty($data['user_id'])) {
						$db->query('INSERT INTO ' . USERS_GROUP . ' VALUES(
								' . $group_id . ',
								' . $user_group_id . ',
								1,
								' . time() . ')'
								);
						$db->query('UPDATE ' . USERS . ' SET group_id=' . $group_id . ' 
									WHERE user_id=' . $user_group_id);
					}
					else {
						$error[] = _t('Ce membre est déjà dans ce groupe');
					}
				}
				else {
					$error[] = sprintf(_t('Le membre <b>%s</b> n\'existe pas'), htmlspecialchars($user_group));
				}
			}
			else {
				$error[] = _t('Aucun membre assigné');
			}
		}
		elseif($submit_join && $user->data['user_id'] != ANONYMOUS_ID) {
				if (empty($data_user_group['user_id'])) {
					$db->query('INSERT INTO ' . USERS_GROUP . ' VALUES(
						' . $group_id . ',
						' . $user->data['user_id'] . ',
						' . $data_group['group_type'] . ',
						' . time() . ')'
						);
						if ($data_group['group_type']  == 1) {
							$db->query('UPDATE ' . USERS . ' SET group_id=' . $group_id . ' 
										WHERE user_id=' . $user->data['user_id']);
						}
					}
					else {
						$error[] = _t('Vous êtes déjà dans ce groupe');
					}
		}
		elseif ($submit_attent) {
			$users_attent = requestVarPost('user_attent_gestion_group', '');
			if ($users_attent != '') {
				$in = dataCheckBox($users_attent);
				$db->query('UPDATE ' . USERS_GROUP . ' SET user_status=1, user_date_joined=' . time() . ' 
							WHERE user_id IN (' . $in . ') AND group_id=' . $group_id);
				$db->query('UPDATE ' . USERS . ' SET group_id=' . $group_id . ' 
							WHERE user_id IN (' . $in . ')');
			}
		
		}
		elseif($submit_delete) {
			$users_delete = requestVarPost('user_delete_group', array());
			$users_attent = requestVarPost('user_attent_gestion_group', array());
			if (!empty($users_delete) || !empty($users_attent)) {
				$users_delete = array_merge($users_delete, $users_attent);
				$in = dataCheckBox($users_delete);
				$db->query('DELETE FROM ' . USERS_GROUP . ' WHERE user_id IN (' . $in . ') AND group_id=' . $group_id);
				$db->query('UPDATE ' . USERS . ' SET group_id=' . GROUP_MEMBER_ID . ' WHERE user_id IN (' . $in . ')');
			}
		}
		$sql = 'SELECT DISTINCT * FROM ' . USERS . ' u, ' . GROUPS . ' g, ' . USERS_GROUP . ' ug 
				WHERE ug.user_id=u.user_id AND ug.group_id=g.group_id AND g.group_id=' . $group_id;
		$where = ' AND ug.user_status = 1';
		$result = $db->query($sql . $where);
		$nb_user_group = 0;
		while ($data = $result->fetch_array()) {
			$grouprow = array(
				'GROUP_MANAGER'			=>	$group_manager == $data['user_id'],
				'USER_DATE_JOINED'		=>	strTime($data['user_date_joined'], 8)

			);
			$grouprow = array_merge($grouprow, viewprofile($data));
			$template->assignBlockVars('group', $grouprow);
			$nb_user_group++;			
		}
		$where = ' AND ug.user_status = 0';
		$result = $db->query($sql . $where);
		$user_attent = 0;
		while ($data = $result->fetch_array()) {
			$grouprow = array(
				'USER_DATE_JOINED'		=>	strTime($data['user_date_joined'], 8)

			);
			$grouprow = array_merge($grouprow, viewprofile($data));
			$template->assignBlockVars('group_attent', $grouprow);
			$user_attent++;			
		}
	
		$result_group = $db->query($sql_group);
		$data_group = $result_group->fetch_array(); // Mise à jour des données après la soumission
		$template->assignVars(array(
				'GROUP_NAME' 				=> 	$data_group['group_name'],
				'GROUP_COLOR' 				=> 	$data_group['group_color'],
				'GROUP_DESC' 				=> 	$data_group['group_desc'],
				'GROUP_STATUS'				=>  $data_group['group_type'] == 0 ? 'groupe fermé' : ($data_group['group_type']  == 1 ? 'groupe ouvert' : 'groupe invisible'),
				'GROUP_ATTENT' 				=> 	$user_attent > 0,
				'USER_GROUP_DELETE' 		=> 	$user_attent > 0 || $nb_user_group > 1,
				'U_ICON_STAR'				=>  image('star_group'),
				'JOIN_GROUP'				=>  ($data_group['group_type'] == 0 ||$data_group['group_type'] == 1) && empty($data_user_group['user_id']) && $user->data['user_id'] != ANONYMOUS_ID,
				'CAN_ADD_USER_GROUPE'		=>  $user->isCan($permission['users']['add_del_user_group']),
				
				'L_USERNAME'				=> _t('Nom d\'utilisateur'),
				'L_LASTVISIT'				=> _t('Dernière visite'),
				'L_NB_POST'					=> _t('Nombre de messages'),
				'L_JOIN_GROUP'				=> _t('Rejoint le groupe'),
				'L_CHECK'					=> _t('Cocher'),
				'L_MANAGER'					=> _t('Manager du groupe'),
				'L_ADD_THE_MEMBER'			=> _t('Ajouter ce membre'),
				'L_JOIN_THIS_GROUP'			=> _t('Joindre le groupe'),
				'L_REMOVE_MEMBER'			=> _t('Enlever les membres cochés du groupe'),
				'L_MEMBER_ATTENT'			=> _t('Membre en attente d\'intégrer le groupe'),
				'L_MEMBER_ATTENT_TIME'		=> _t('A souhaité de rejoindre le groupe le'),
				'L_ADD_MEMBER'				=> _t('Ajouter les membres cochés au groupe')
			)
		);
		
	 }
	 else {
		$mode = 'memberlist';
	 }
	 
	 displayError();
}
if ($mode == 'memberlist') {
	$sql = 'SELECT * FROM ' . USERS . ' u, ' . GROUPS . ' g';
	$where = ' WHERE u.group_id=g.group_id AND user_activ=1 AND user_id != ' . ANONYMOUS_ID . ($search != '' ? ' AND username LIKE "%' . $search . '%"' : '');
	$limit = ' LIMIT ' . $start . ', ' . $config['members_per_page'];
	$result = $db->query($sql . $where . $limit);

	$sql = 'SELECT COUNT(*) AS nb_membre FROM ' . USERS . ' u, ' . GROUPS . ' g';
	$result_count = $db->query($sql . $where);
	$member_count = $result_count->fetch_array();

	$id = 0;
	while ($data = $result->fetch_array()) {
		$id++;
		$template->assignBlockVars('member', viewprofile($data));
	}
	
	$template->assignVars(array_merge(displayGlobaleTextProfile(), array(
				'CAN_VIEW_PROFILE' 			=> $user->isCan($permission['users']['user_viewprofile']),
				'AUTO_COMPLETION'			=> $config['activ_auto_completion'],
				'L_SEARCH_MEMBER'			=> _t('Rechercher un membre'),
				'L_LABEL_SEARCH_MEMBER'		=> _t('Nom du membre'),
				'L_REGDATE'					=> _t('Inscrit le'),
				'USERNAME_SEARCH'			=> $search,
				'L_MEMBER_FORUM'			=> sprintf(_nt('Un membre sur le forum', 'Membres du forum (%s au total)', $member_count['nb_membre']), $member_count['nb_membre']),
				'PAGINATION' 				=> pagination('memberlist.php', null, $member_count['nb_membre'], $config['members_per_page'], $start)
			))
	);		
	
	
	
	$mode = 'memberlist';
}

$template->assignVars(array(
		'MODE' 	=> $mode
	)
);



pageHeader($template, _t('Membres'));
$template->setTemplate('members.html');
?> 
