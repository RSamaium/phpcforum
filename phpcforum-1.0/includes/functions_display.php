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

function displayForums($order, $index = false, $display_all = false, $forum_expected = array()) {
	global $template, $db, $user, $permission, $config;

	$forum_exist = true;
	if (!$display_all) {
		if ($index) {
			$forum_id = 0;
		}
		else {
			$forum_id = requestVar('f', 0, 'unsigned int');
			$forum_exist = idIsExist($forum_id, 'forum');
		}
	}
	
	if ($forum_exist) {
		$depth = 0;
		$i = 0;
		$next_is_id = false;
		$isReadSubForum = true;
		$depth_save = 0;
		$test_pos = array('{', ';', '}');
		while($i < strlen($order)) {
			$c = $order[$i];
			if ($next_is_id) {
				$_forums_id = $c;
				while (!in_array($c, $test_pos)) {
					$i++;
					$c = $order[$i];
					if (!in_array($c, $test_pos)) {
						$_forums_id .= $c;
					}
				}
				if ($isReadSubForum && $user->isCanForum($permission['forum']['forum_view'], $_forums_id) && !in_array($_forums_id, $forum_expected)) {
					$row_forum = $db->forum[$_forums_id];
					if (!isset($forum_read[$_forums_id])) {
						$forum_read[$_forums_id] = 0;
					}
					$indent = '';
					for ($j=0 ; $j < $depth ; $j++) {
							$indent .= '&nbsp;&nbsp;&nbsp;';
					}
					$forumrow = array(
						'ID'						=>  $_forums_id,
						'NAME'						=>  $row_forum['forum_name'],
						'UNREAD_IMG'				=>	$forum_read[$_forums_id] == $row_forum['nb_topics_unread'] ? image('no_new_post') : image('new_post'),
						'UNREAD'					=>  $forum_read[$_forums_id] == $row_forum['nb_topics_unread'] ? _t('Aucun nouveau message') : _t('Nouveau(x) message(s)'),
						'DESC'						=>  $row_forum['forum_desc'],
						'IMAGE'						=>  $row_forum['forum_image'],
						'NB_SUBJECT'				=>  sprintf(_nt('%s sujet', '%s sujets', $row_forum['forum_nb_subject']), '<strong>' . $row_forum['forum_nb_subject'] . '</strong>'),
						
						'NB_POST'					=>  sprintf(_nt("%s message", "%s messages", $row_forum['forum_nb_post']), '<strong>' . $row_forum['forum_nb_post'] . '</strong>'),
						'LASTPOST_TIME'				=>  strTime($row_forum['forum_last_post_time'], 8),
						'LASTPOST_SUBJECT'			=>  $row_forum['forum_last_post_subject'],
						'LASTPOST_POSTER_NAME'		=>  $row_forum['forum_last_post_poster_name'],
						'LASTPOST_POSTER_IS_ANONYMOUS'	=> $row_forum['forum_last_post_poster_id'] == ANONYMOUS_ID,
						'LASTPOST_POSTER_COLOR'		=>  $row_forum['forum_last_post_poster_colour'],
						'LASTPOST_ID'				=>  $row_forum['forum_last_post_id'],
						'HAS_LASTPOST'				=>  $row_forum['forum_last_post_id'] != 0,
						'U_LASTPOST_POSTER'			=>  makeUrl('memberlist.php', array('mode' => 'viewprofile', 'u' => $row_forum['forum_last_post_poster_id'])),
						'I_POST_LAST'				=>  image('icon_topic_latest'),
						'INDENT'					=>  $indent,
						'IS_CAT'					=>  $depth == 1,
						'U_LINK'					=>  makeUrl(($c != '{' ? 'viewtopic' : 'viewforum') . '.php', array('f' => $_forums_id))
					);
					if ($display_all) {
						$template->assignBlockVars('listforum', $forumrow);
					}
					else {
						$namerow = "";
						for ($j=0 ; $j < $depth ; $j++) {
							$namerow .= 'forum' . ($j+1) . '.';
						}
						$namerow = preg_replace('#\\.$#', '', $namerow);
						
						$template->assignBlockVars($namerow, $forumrow);
					}						
				}
				elseif ($c == '{') {
					if ($isReadSubForum) {
						$depth_save = $depth;
					}
					$isReadSubForum = false;	
				}
				$next_is_id = false;
			}
			switch ($c) {
				case '{':
					$depth++;
				break;
				case '}':
					$depth--;
					if ($depth_save == $depth) {
						$isReadSubForum = true;
					}
				break;
				case ':':
					$next_is_id = true;
				break;
			}
			$i++;
		}
	}
	
	
	$template->assignVars(array(
			'FORUM_EXIST' => $forum_exist,
			'TXT_FORUM_NOT_EXIST' => _t('Le forum n\'existe pas')
		)
	);
	
}

function orderSubForum($parent_id) {
	global $config;
	$order = $config['order_forums'];
	$test_pos = array('{', ';', '}');
	$find_str = false;
	$accolade = 0;
	$str = '';
	
	for ($i=0 ; $i < sizeof($test_pos) ; $i++) {
		$val = strrpos($order, ':' . $parent_id . $test_pos[$i]);		
		if ($val !== false) {
			$pos = $val;
			break;
		}
	}
	$pos += strlen($parent_id) + 1;
	
	do {
		$c = $order[$pos];
		if ($c == '{') {
			$accolade++;
		}
		elseif ($c == '}') {
			$accolade--;
		}
		$str .= $c;
		$pos++;
	} while ($accolade > 0 && $pos < strlen($order));

	return $str;
}

function displayAriana($forum_id, $topic_id = 0, $topic_name = '') {
	global $config, $template, $db;
	$page = '';
	$ariana = array(
		'U_LINK'	=> makeUrl('index.php'),
		'NAME'		=> $config['sitename']
	);
	$template->assignBlockVars('ariana', $ariana);
	
	$thread = threadAriana($forum_id, array());
	$thread[] = $forum_id;
	$size = sizeof($thread);
	for ($i=0 ; $i < $size ; $i++) {
		$page = preg_match('#:' . $thread[$i] . '\\{#', $config['order_forums']) ? 'viewforum' : 'viewtopic';
	
		$ariana = array(
			'U_LINK'	=> makeUrl($page . '.php', array('f' => $thread[$i])),
			'NAME'		=> $db->forum[$thread[$i]]['forum_name'],
			'LAST'		=> $topic_id == 0 ? $i == $size-1 : false
		);
		$template->assignBlockVars('ariana', $ariana);
	}
	
	if ($topic_id != 0) {
		$ariana = array(
			'U_LINK'	=> makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id)),
			'NAME'		=> strRaccourci($topic_name , 40),
			'LAST'		=> true
		);
		$template->assignBlockVars('ariana', $ariana);
	}
}

function displayListForum($order, $forum_expected = array()) {
	global $template, $db, $user, $permission;
	
	/*if (preg_match_all('#([0-9]+)(-[0-9]+)?:([0-9]+)(\\{(.*?)\\})?(;|\\}|\\{)#', $order, $matches)) {
		$forums_id = $matches[3];
		$forum_order = $matches[1];
		$subforum_order = $matches[4];
		$nb_forum = sizeof($forums_id);
		
		$indent = '';
		for ($j=0 ; $j < $depth ; $j++) {
			$indent .= '--';
		}
		for ($i=0 ; $i < $nb_forum ; $i++) {
			$row_forum = $db->forum[$forums_id[$i]];
			if ($user->isCanForum($permission['forum']['forum_view'], $forums_id[$i])) {
					
					$forumrow = array(
								'ID'		=>  $forums_id[$i],
								'NAME'		=>  $row_forum['forum_name'],
								'INDENT'	=>	$indent . '>&nbsp;',
								'IS_CAT'	=> $depth == 0
					);
					$template->assignBlockVars('listforum', $forumrow);
			}
			if (!empty($subforum_order[$i])) {
				displayListForum($subforum_order[$i] . '}', $depth+1);
			}
		}								
	}*/
	displayForums($order, false, true, $forum_expected);
	
	
	
	
	

}

function displayWhoOnline() {
	global $db, $template, $config;
	$time = time();
	
	$sql = 'SELECT COUNT(*) AS nb_session FROM ' . SESSIONS . ' WHERE session_user_id ';
	$condition = '';
	$sql2 = '= ' . ANONYMOUS_ID . ' AND session_time >= ' . ($time-300);
	$result = $db->query($sql . $condition . $sql2);
	$count_anonymous = $result->fetch_array();
	
	$condition = '!';
	/*$result = $db->query($sql . $condition . $sql2);
	$count_register = $result->fetch_array();*/
		
	$sql = 'SELECT DISTINCT username, user_id, group_color, user_options, session_viewonline FROM ' . SESSIONS . ', ' . USERS . ' u, ' . GROUPS . ' g WHERE session_user_id=u.user_id AND u.group_id=g.group_id AND session_user_id ';
	$result = $db->query($sql . $condition . $sql2);
	
	$nb_invisible = 0;
	$nb_member = 0;
	$viewonline = true;
	$users = array();
	while ($data = $result->fetch_array()) {
		
		if (profileOptions($data, 'user_mask_statut'))
			$viewonline = false;
		elseif (!$data['session_viewonline'])
			$viewonline = false;
		
		if (!$viewonline)
			$nb_invisible++;
		else
			$nb_member++;
		
		$url = makeUrl('memberlist.php', array('mode' => 'viewprofile', 'u'	=> $data['user_id']));
		$users[] = array(
				'user_id' 				=> $data['user_id'],
				'username' 				=> $data['username'],
				'user_color' 			=> $data['group_color'],
				'user_profil' 			=> $url
		);
		$user_online = array(
				'USER_ID'				=> $data['user_id'],
				'USERNAME'				=> $data['username'],
				'USER_COLOR'			=> $data['group_color'],
				'U_USER_PROFIL'			=> $url,
				'VIEW_ONLINE'			=> $viewonline,
				'SEPARATOR'				=> displaySeparator($result, $nb_member, ' ')
								
			);
		$template->assignBlockVars('useronline', $user_online);						
	}
	
	
	
	$total_user = $count_anonymous['nb_session'] + $nb_invisible + $nb_member;
	if ($total_user > $config['max_simult_user']) {
		$db->update(CONFIG, array('config_value' => $total_user), 'config_name="max_simult_user"');
		$db->update(CONFIG, array('config_value' => $time), 'config_name="time_max_simult_user"');
	}
	$last_user = $db->select(USERS, 'user_activ=1', 'user_regdate DESC', null, 'username, user_id', 'one');
	$template->assignVars(array(
		'L_STATS_TOTAL_USERS'				=> sprintf(_nt('Au total, il y a %s utilisateur en ligne', 'Au total, il y a %s utilisateurs en ligne', $total_user), '<span class="highlight">' . $total_user . '</span>'),
		'L_STATS_MEMBERS'					=> sprintf(_nt('%d inscrit', '%d inscrits', $nb_member), $nb_member), 
		'L_STATS_INVISIBLES'				=> sprintf(_nt('%d invisible', '%d invisibles', $nb_invisible), $nb_invisible),
		'L_STATS_ANONYMOUS'					=> sprintf(_nt('%d invité', '%d invités', $count_anonymous['nb_session']), $count_anonymous['nb_session']),
		'L_REGISTRER_USERS'			=> _nt('Utilisateur inscrit', 'Utilisateurs inscrits', $nb_member),
		'L_TOTAL_MSG'				=> sprintf(_nt('%s message au total', '%s messages au total', $config['total_post']), '<span class="highlight">' . $config['total_post'] . '</span>'),
		'L_TOTAL_TOPIC'				=> sprintf(_nt('%s sujet au total', '%s sujets au total', $config['total_topic']), '<span class="highlight">' . $config['total_topic'] . '</span>'),								
		'L_TOTAL_MEMBER'			=> sprintf(_nt('%s membre au total', '%s membres au total', $config['total_users']), '<span class="highlight">' . $config['total_users'] . '</span>'),	
		'L_LAST_MEMBER'				=> sprintf(_t('Notre membre le plus récent est %s'), '<span class="highlight">' . $last_user['username'] . '</span>'),	
		'L_MAX_SIMUL_USERS'			=> sprintf(_t('Le nombre maximum d\'utilisateurs en ligne simultanément a été de %s le %s'), '<span class="highlight">' . $config['max_simult_user'] . '</span>', strTime($config['time_max_simult_user'], 8)),																	
		'L_WHO_IS_ONLINE'			=> _t('Qui est en ligne ?')
		)
	);
	
	return array(
		'users' 		=> $users,
		'total_user'	=> $total_user,
		'nb_anonymous'	=> $count_anonymous['nb_session'],
		'nb_invisible'	=> $nb_invisible,
		'nb_member'		=> $nb_member,
		'last_user'		=> $last_user['username'],
		'max_user'		=> $config['time_max_simult_user']
	);
}

function displayListGroup() {
	global $db, $template;
	$result = $db->query('SELECT group_id, group_name, group_color FROM ' . GROUPS . ' 
						  WHERE group_id != ' . GROUP_VISITOR_ID . ' AND group_id != ' . GROUP_MEMBER_ID);
	$i = 0;
	while ($data = $result->fetch_array()) {
			$grouprow = array(
					'U_VIEW_GROUP'				=>  makeUrl('memberlist.php', array('mode' => 'groups', 'g' => $data['group_id'])),
					'GROUP_COLOR'				=>  $data['group_color'],
					'GROUP_NAME'				=>  $data['group_name'],
					'SEPARATOR'					=>  displaySeparator($result, $i, ',  ')
				);
			$template->assignBlockVars('grouplist', $grouprow);
			$i++;
	}
}

function displaySeparator($result, $i, $str) {
	return $result->num_rows-1 == $i ? '' : $str;
}

function imgIcon($name) {
	return 'styles/' . STYLE . '/images/' . $name;
}

function etatSubmitConfirm($etat_confirm, $txt_confirm = array(), $url_retour = '', $reussi = true, $ajax = 0) {
	global $template;
	$str = !empty($txt_confirm) ? ($reussi ? $txt_confirm[0] :  $txt_confirm[1]) : '';
	$template->assignVars(array(
			'L_CONFIRM_TITLE'		=> _t('Confirmation'),
			'SUBMIT'				 => $etat_confirm,
			'L_CONFIRM'				 => $str,
			'L_RETOUR'				 => _t('Revenir'),
			'U_RETOUR'				 => $url_retour,
			'COLOR'				 	 => $reussi ? 'green' : 'red'
		)
	);
	if ($ajax == 2)
		echo $str;
}

function pagination($page, $vars, $nb_items, $per_page, $first_item) {
	global $template;
		$nb_pages  = ceil($nb_items / $per_page); 
		$echo = '';
		$id_page_current = $first_item/$per_page;
		$begin = $id_page_current - PAGE_DOT_DIFF;
		$end = $id_page_current+PAGE_DOT_DIFF;
		
		if ($nb_pages == 1) {
			return '';
		}
		
		if ($id_page_current > $nb_pages) {
			$first_item = $nb_pages-1;
		}

		if ($begin < 0) {
			$begin = 0;
		}
		if ($end > $nb_pages) {
			$end = $nb_pages;
		}
		
		if ($id_page_current != 0) {
			$vars['start'] = ($id_page_current-1) * $per_page;
			$echo = '<a href="' . makeUrl($page, $vars) . '" class="button">< ' ._t('Précédent') . '</a> ';
		}
			$j=0;
			for ($i=$begin; $i < $end ; $i++) {
				if ($id_page_current == $i) {
					$id_page_current = $i;
					$echo .= ' <strong class="button pagination_close" style="color: #ff2441;">' . ($i+1) . '</strong>';
				}
				else {
					$vars['start'] = $i*$per_page;
					$echo .= ' <a href="' . makeUrl($page, $vars) . '" class="button pagination">' . ($i+1) . '</a>';
				}
				$j++;
			}
		if ($id_page_current != $nb_pages-1) {
			$vars['start'] = ($id_page_current+1) * $per_page;
			$echo .= ' <a href="' . makeUrl($page, $vars) . '" class="button"> ' . _t('Suivant') . ' ></a> ';
		}
		if ($j < PAGE_DOT_DIFF) {
			$j = PAGE_DOT_DIFF;
		}
		
		return $echo;
}

/*
function pagination($page, $vars, $nb_items, $per_page, $first_item) {
		$echo = _t('Pages') . ' : ';
		$nb_pages  = ceil($nb_items / $per_page); 
		
		$id_page_current = $first_item/$per_page;
		
		if ($nb_pages > NB_PAGE_DISPLAY) {
			if ($id_page_current-PAGE_DOT_DIFF < 0) {
				$id_page_current += PAGE_DOT_DIFF;
			}
			elseif ($id_page_current+PAGE_DOT_DIFF >= $nb_pages) {
				$id_page_current -= PAGE_DOT_DIFF;
			}
			
			if ($id_page_current-PAGE_DOT_DIFF >= 1) {
				$echo .= '<a href="' . makeUrl($page, array('start' => 0)) . '" class="pagination">1</a> ... ';
			}
			for ($i=$id_page_current-PAGE_DOT_DIFF; $i < ($id_page_current-PAGE_DOT_DIFF) + (PAGE_DOT_DIFF*2+1) ; $i++) {
					$page_current = $first_item == $i*$per_page;
					if ($page_current) {
						$echo .= ' <strong class="pagination_close">' . ($i+1) . '</strong>';
					}
					else {
						$vars['start'] = $i*$per_page;
						$echo .= ' <a href="' . makeUrl($page, $vars) . '" class="pagination">' . ($i+1) . '</a>';
					}
					if ($i != $nb_pages-1)
						$echo .= ', ';
			}
			if ($id_page_current+PAGE_DOT_DIFF != $nb_pages-1) {
				$vars['start'] = ($nb_pages-1)*$per_page;
				$echo .= '... <a href="' . makeUrl($page, $vars) . '" class="pagination">' . $nb_pages . '</a>';
			}
		}
		else {
			for ($i=0; $i < $nb_pages ; $i++) {
				$page_current = $first_item == $i*$per_page;
				if ($page_current) {
					$id_page_current = $i;
					$echo .= ' <strong class="pagination_close">' . ($i+1) . '</strong>';
				}
				else {
					$vars['start'] = $i*$per_page;
					$echo .= ' <a href="' . makeUrl($page, $vars) . '" class="pagination">' . ($i+1) . '</a>';
				}
				if ($i != $nb_pages-1)
					$echo .= ', ';
			}
		}
		return $echo;
}
*/

function displayLang($prefix = '', $lang_test) {
	global $db, $template;
	$result = $db->select(LANGS);
	while ($data = $result->fetch_assoc()) {
		$row = array(
			'LONGID' 				=>  $data['lang_longid'],
			'NAME'					=>	$data['lang_name'],
			'EQUAL_LANG'			=>	$data['lang_longid'] == $lang_test
		
		);
		$template->assignBlockVars($prefix . 'lang', $row);	
	}
}

function displayGlobaleTextProfile() {
	return array(
		'L_LAST_VISIT'	=> _t('Dernière visite'),
		'L_NB_MESSAGE'	=> _t('Nombre de message')
	);
}

function displayGlobaleTextLegend() {
	return array(
		'L_NEW_POST'	=> _t('Nouveaux messages'),
		'L_NO_NEW_POST'	=> _t('Aucun nouveau message'),
		'L_POST_LOCK'	=> _t('Verrouillé - Impossible de répondre')
	);
}

function displayGlobaleTextHeader() {
	return array(
		'L_SUBJECT'		=> _t('Sujet'),
		'L_AUTHOR'		=> _t('Auteur'),
		'L_POLL'		=> _t('Sondage'),
		'L_DEST'		=> _t('Destinataire'),
		'L_POST'		=> _t('Message'),
		'L_LAST_POST'	=> _t('Dernier message'),
		'L_STATS'		=> _t('Statistique'),
		'L_POSTDATE'	=> _t('Envoyé le'),
		'L_CHECK'		=> _t('Cocher')
	);
}

function displayGlobaleButtons() {
	return array(
		'L_VALID'		=> _t('Valider'),
		'L_VIEW'		=> _t('Voir'),
		'L_SEND'		=> _t('Envoyer'),
		'L_VOTE'		=> _t('Voter'),
		'L_OK'			=> _t('Ok'),
		'L_NO'			=> _t('Non'),
		'L_YES'			=> _t('Oui'),
		'L_RETURN'		=> _t('Retour'),
		'L_CANCEL'		=> _t('Annuler')
	);
}

function displayReportHeader() {
	return array(
		'L_NEW_REPORT'		=> _t('Nouveaux rapports envoyés'),
		'L_VIEW_REPORT'		=> _t('Voir le rapport')
	);
}

function displayError() {
	global $template, $error;
	for ($i=0 ; $i < sizeof($error) ; $i++) {
		$txt_list_error = array(
				'TXT' => $error[$i]
		);
			$template->assignBlockVars('error', $txt_list_error);
	}
		$template->assignVars(array(
				'ERROR'				=> !empty($error),
				'ERROR_IMG'			=> image('error')
		));
}

?>