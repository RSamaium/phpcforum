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

$forum_id = requestVar('f', 0, 'unsigned int');
$topic_id = requestVar('t', 0, 'unsigned int');
$start = requestVar('start', 0, 'unsigned int');
$mod = requestVar('mod', '');
$view = requestVar('view', '');

$submit = isset($_POST['submit_mod_tools']);
$submit_poll = isset($_POST['submit_poll']);
$mod_post = requestVarPost('mod', '');

$forum_exist = idIsExist($forum_id, 'forum');
$topic_exist = idIsExist($topic_id, 'topic');

$rss = requestVar('rss', 0) && $topic_exist;

$db->dataForums($user);
$db->dataIcons();

$access  = '';
$h1_name = '';
$error = array();

etatSubmitConfirm(false);

if ($forum_exist) {

	if ($user->isCanForum($permission['forum']['forum_view'], $forum_id)) {	
		if ($topic_exist) {
			
			Plugins::flag('viewposts', array(&$start));

			$checkbox_post =  requestVarPost('post_id', '');
			
			$sql = 'SELECT * FROM ' . POSTS . '
					WHERE forum_id=' . $forum_id . ' AND topic_id=' . $topic_id . ' 
					LIMIT ' . $start . ', ' . $config['post_per_page'];
			$result = $db->query($sql);
			
			$sql = 'SELECT COUNT(*) AS nb_posts FROM ' . POSTS . ' 
					WHERE forum_id=' . $forum_id . ' AND topic_id=' . $topic_id;
			$result_count = $db->query($sql);
			$post_count = $result_count->fetch_array();
			
			
			 // $post_count['nb_posts'], $config['post_per_page'], $start
			
			// $result_topic = $db->query('SELECT topic_view, topic_title, topic_status, poll_title, poll_max_options, poll_vote_change, poll_length, poll_start
										// FROM ' . TOPICS . ' WHERE topic_id=' . $topic_id);
			$topic = $db->dataTopic($topic_id);
			
			
			
			// $result_topic->fetch_array();
			
			$h1_name = $topic['topic_title'];
			
			displayAriana($forum_id, $topic_id, $topic['topic_title']);
			// $db->query('UPDATE ' . TOPICS . ' SET topic_view=' . ($topic['topic_view']+1) . ' WHERE topic_id=' . $topic_id);
			
			$db->update(TOPICS, array(
				'topic_view'	=> $topic['topic_view']+1
			), 'topic_id=' . $topic_id);
			
			/*if ($topic['post_time'] > $user->session_data['session_start'])  {
				$topic_read = $user->getValueSession('topic_read');
				if (!isset($topic_read[$topic['topic_last_post_id']])) {
					$user->addValueSession('topic_read', 1, $topic['topic_last_post_id']);
					$forum_read = $user->getValueSession('forum_read');
					$forums = threadAriana($forum_id, array());
					$forums[] = $forum_id;
					foreach($forums as $key => $value) {
						$user->addValueSession('forum_read', $forum_read[$value]+1, $value);
					}
				}
			}*/
			
			if ($user->isRegister() && $topic['poster_id'] != $user->data['user_id'] && $topic['post_time'] > $user->data['user_lastmark']) {
				$data_read = $db->select(TOPICS_READ, 'user_id =' . $user->data['user_id'] . ' AND topic_id =' . $topic_id, null, null, 'state', 'one');
				if (isset($data_read['state'])) {
					if ($data_read['state'] == 0) {
						$db->update(TOPICS_READ, array(
							'state'		=> 1,
							'time'		=> time()
						), 'user_id =' . $user->data['user_id'] . ' AND topic_id =' . $topic_id);
					}
				}
				else {
					$db->insert(TOPICS_READ, array(
						'user_id' 	=> $user->data['user_id'],
						'forum_id'	=> $forum_id,
						'topic_id'	=> $topic_id,
						'state'		=> 1,
						'time'		=> time()
					));
				}
			}
			
			//$forum_read[$forum_id]
			
			//$user->addValueSession('forum_read', 1, $forum_id);
		
			$header_title = 0;
			while ($row = $result->fetch_array()) {
				$icon = $db->icons[$row['icon_id']];
				$postrow = array(
					'POST_ID'				=>  $row['post_id'],
					'SUBJECT'				=>  $row['post_subject'],
					'TEXT'					=>  replaceOption(htmlDoubleQuoteRev($row['post_text'])),
					'TEXT_RSS'				=>  strip_tags(htmlDoubleQuoteRev($row['post_text'])),
					'POST_TIME'				=>  sprintf(_t('posté le %s'), strTime($row['post_time'], 8)),
					'POST_TIME_RSS'			=>  date('D, d M Y g:i:s \\G\\M\\T', $row['post_time']),
					'THIS_LINK_RSS'			=>  str_replace('&', '&amp;', $_SERVER['HTTP_HOST'] . ROOT . '/' . makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id))),
					'CAN_EDIT_MSG'			=>  permissionPost($forum_id, $row['poster_id'], 'modo_msg_edit', 'msg_edit'),
					'CAN_DELETE_MSG'		=>  permissionPost($forum_id, $row['poster_id'], 'modo_msg_delete', 'msg_delete'),
					'U_POST_EDIT'			=>  makeUrl('posting.php', array('mode'	=> 'edit', 'p'	=> $row['post_id'], 'f'	=> $forum_id)),
					'U_POST_DETAILS'		=>  makeUrl('profile.php', array('mode' => 'post_details', 'f' => $forum_id, 'p' => $row['post_id'])), 
					'U_POST_REPORT'			=>  makeUrl('report.php', array('mode'	=> 'send', 'p'	=> $row['post_id'], 'f'	=> $forum_id)),
					'HEADER_TITLE'			=>  $header_title == 0,
					'POST_EDIT'				=> $row['post_edit_count'] > 0,
					'ICON_NAME'				=> $icon['icon_name'],
					'ICON_WIDTH'			=> $icon['icon_width'],
					'ICON_HEIGHT'			=> $icon['icon_height'],
					'ICON_PATH'				=> 'images/icons/' . $icon['icon_path'],
					'ICON_EXIST'			=> isset($icon)
					
				);
				
				if ($row['post_edit_count'] > 0) {
					$user_edit = userData($row['post_edit_user']);
					$editrow = array(
						'L_POST_EDIT'			=> sprintf(_t('Dernière édition par %s le %s, édité %d fois au total.'), '<span style="color: #' . $user_edit['group_color'] . '">' . $user_edit['username'] . '</span>', strTime($row['post_edit_time'], 8), $row['post_edit_count']),
						// 'POST_EDIT_TIME'		=>  strTime($row['post_edit_time'], 8),
						'POST_EDIT_REASON'		=>  $row['post_edit_reason'],
						/*'POST_EDIT_COUNT'		=>  $row['post_edit_count'],
						'POST_EDIT_USERNAME'	=> $user_edit['username'],
						'POST_EDIT_USER_COLOR'	=> $user_edit['group_color']*/
					
					);
					$postrow = array_merge($postrow, $editrow);
				}
				$header_title++;
				$postrow = array_merge($postrow, viewprofile(userData($row['poster_id'])));
				//$postrow = array_merge($postrow, userProfileOptions($row['poster_id']));
				$postrow = array_merge($postrow, viewReports(reportsData('re.post_id=' . $row['post_id'])));
				$template->assignBlockVars('post', $postrow);
			}
			$user_vote = array();
			$poll_total = '';

			if ($topic['poll_title'] != '') {
				$user_vote = userVote($user->data['user_id'], $topic_id);		
				if ($submit_poll) {

					$poll_options =  requestVarPost('poll_option_id', array());
					 if ($user->isCanForum($permission['forum']['poll_vote'], $forum_id)) {
						if (!empty($poll_options))
							if ($topic['poll_max_options'] >= sizeof($poll_options)) {
								if (!empty($user_vote) && $topic['poll_vote_change']) {
									$etat = updateVote($topic_id, $poll_options, $user_vote, $user->data['user_id'], $user->current_ip);
								}
								else {
									$etat = createNewVote($topic_id, $poll_options, $user->data['user_id'], $user->current_ip);
								}
								
								$url_retour = makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id));
								etatSubmitConfirm(true, array(_t('Le vote a été envoyée avec succès'), _t('L\'envoi du vote a échoué')), $url_retour, $etat);
								metaRefresh(3, $url_retour);
							}
							else {
								$error[] = sprintf(_t('Vous ne pouvez pas choisir plus de %d options'), $topic['poll_max_options']);
							}
						else
							$error[] = _t('Aucune option cochée');
					}
					else
						$error[] = _t('Vous n\'avez pas la permission de voter');
				
				}
				
				$sql = 'SELECT * FROM ' . POLL_OPTIONS . ' WHERE topic_id=' . $topic_id;
				$result_poll = $db->query($sql);
				
				$result_poll_total = $db->query('SELECT SUM(poll_option_total) AS poll_total FROM ' . POLL_OPTIONS . ' WHERE topic_id=' . $topic_id);
				$data_poll_total = $result_poll_total->fetch_array();
				$poll_total = $data_poll_total['poll_total'];
				
				while ($row = $result_poll->fetch_array()) {
					$pollrow = array(
						'POLL_OPTION_TEXT'	=> $row['poll_option_text'],
						'POLL_OPTION_ID'	=> $row['poll_option_id'],
						'POLL_OPTION_TOTAL'	=> sprintf(_t('%d votes'), $row['poll_option_total']),
						'POLLBAR_WIDTH'		=> $poll_total == 0 ? 0 : round($row['poll_option_total'] * 100 / $poll_total),
						'POLL_USER_VOTE'	=> in_array($row['poll_option_id'], $user_vote)
					);
					$template->assignBlockVars('poll', $pollrow);
				}
			}
			
			
			$pagination = 	pagination('viewtopic.php', array('f' =>  $forum_id, 't' => $topic_id), $post_count['nb_posts'], $config['post_per_page'], $start);
			
			$mod_tools = array(
				'modo_topic_merge'		=>	_t('Fusionner les messages cochés'), 
				'modo_topic_split' 		=> 	_t('Diviser les messages cochés'),
				'modo_msg_delete'		=>	_t('Supprimer les messages cochés'),
				'modo_topic_lock'		=>  _t('Verrouiller le sujet'),				
				'modo_topic_delete' 	=>	_t('Supprimer le sujet'),
				'modo_topic_move' 		=>	_t('Déplacer le sujet'),
				'modo_topic_sticky'		=>	_t('Mettre le sujet en post-it'),
				'modo_topic_annonce' 	=>	_t('Mettre le sujet en annonce'),
				'modo_topic_global' 	=> 	_t('Mettre le sujet en global'),
				// 'modo_view_logs' 		=>	_t('Voir les logs'),
				// 'modo_topic_copy'		=>	_t('Copier le sujet'),
				'modo_move_corbeile'	=>	_t('Déplacer le sujet vers la corbeille')
			);
			$delete_post =  requestVarPost('delete_post_id', 0);
			if ($delete_post != 0) {
				$checkbox_post = $delete_post;
			}
			

			viewQuickModTools($submit, $mod_post, $mod_tools, $checkbox_post);
			
			$poll_expire = time() > $topic['poll_start'] + (3600*24*$topic['poll_length']) && $topic['poll_length'] != 0;
			$poll_view = $view == 'viewpoll' || !empty($user_vote) || $poll_expire || !$user->isCanForum($permission['forum']['poll_vote'], $forum_id);
			$template->assignVars(displayGlobaleTextHeader());
			$template->assignVars(array(
				'CAN_ALERT_MSG'			=>  $user->isCanForum($permission['forum']['modo_msg_report'], $forum_id),
				'CAN_INFORMATION_MSG'	=>  $user->isCanForum($permission['forum']['modo_msg_information'], $forum_id),
				'CAN_READ_REPORTS'		=>  $user->isCan($permission['users']['read_reports']),
				'CAN_REPLY_TOPIC'		=>  $user->isCanForum($permission['forum']['topic_reply'], $forum_id),
				'TOPIC_LOCK'			=>  $topic['topic_status'] == 1,
				'U_REPLY_TOPIC'			=>  makeUrl('posting.php', array('mode' => 'reply', 'f' => $forum_id, 't' => $topic_id)),
				'L_LOCK'				=> _t('Verrouillé'),
				'L_REPLY'				=> _t('Répondre'),
				'POLL'					=> $topic['poll_title'] != '',
				'POLL_TITLE'			=> $topic['poll_title'],
				'POLL_MAX_OPTIONS'		=> sprintf(_t('Vous pouvez selectionner %d options'), $topic['poll_max_options']),
				'POLL_TOTAL'			=> sprintf(_t('%d votes au total'), $poll_total),
				'U_POLL_VIEW'			=> makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id, 'view' => 'viewpoll')),
				'POLL_VIEW'				=> $poll_view,
				'POLL_OPTION'			=> !$poll_view || $topic['poll_vote_change'],
				'L_EDIT'				=> _t('Editer'),
				'L_DELETE'				=> _t('Supprimer'),
				'L_REPORT'				=> _t('Alerter'),
				'L_INFOS'				=> _t('Informations'),
				'L_PM'					=> _t('Message privé'),
				'L_REASON'				=> _t('Raison'),
				'L_REGDATE'				=> _t('Inscrit le'),
				'L_NB_POSTS'			=> _t('Messages'),
				'L_POLL_VIEW_RESULT'	=> _t('Voir les résultats'),
				'L_CHOISE_THIS_OPTION'	=> _t('Vous avez choisi cette option'),
				'L_MSG_DELETE'			=> _t('Etes vous sûr de vouloir supprimer ce message ?'),
				'I_FACEBOOK'			=> image('facebook'),
				'I_TWITTER'				=> image('twitter'),
				'I_RSS'					=> image('rss'),
				'L_RSS'					=> _t('Flux RSS pour ce sujet.'),
				'U_RSS'					=> makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id, 'rss' => 1)),
				'HAS_RSS'				=> true,
				'TITLE_RSS'				=> $config['sitename'] . ' - ' . $h1_name,
				'DESC_RSS'				=> $config['site_desc'],
				'LINK_RSS'				=> $_SERVER['HTTP_HOST'] . ROOT,
				'L_SHARE'				=> _t('Partager'),
				'DISPLAY_SHARE_FACEBOOK'	=> $db->forum[$forum_id]['forum_share_facebook'],
				'DISPLAY_SHARE_TWITTER'		=> $db->forum[$forum_id]['forum_share_twitter'],
				'LANG_SHARE_TWITTER'	=> preg_replace('#_.*?$#', '', $config['lang_default'])
				)
			);
			viewProfileOptions();

		}
		else {
			
			Plugins::flag('viewtopics', array(&$start));
			
			displayAriana($forum_id);
			
			$checkbox_topic =  requestVarPost('topic_id', '');	
						
			$sql = 'SELECT COUNT(*) AS nb_topic FROM ' . TOPICS . ', ' . USERS . ' u, ' . GROUPS . ' g 
					WHERE forum_id=' . $forum_id . ' AND topic_poster=user_id AND u.group_id=g.group_id AND topic_type = 0';
			$result_count = $db->query($sql);
			$post_count = $result_count->fetch_array();
			
			$result = $db->select(TOPICS_READ, 'user_id=' . $user->data['user_id'] . ' AND forum_id=' . $forum_id);
			$topic_read = array();
			while ($data = $result->fetch_assoc()) {
				$topic_read[$data['topic_id']] = $data['state'];
			}

			$h1_name = $db->forum[$forum_id]['forum_name'];
		
			$forum_type = array('topic'	=> '=', 'topic_header' => '>');
			$nb_sticky = 0;
			foreach ($forum_type as $key => $value) {
				/*$sql = 'SELECT * FROM ' . TOPICS . ', ' . USERS . ' u, ' . GROUPS . ' g 
						WHERE forum_id=' . $forum_id . ' AND topic_poster=user_id AND u.group_id=g.group_id AND topic_type ' . $value . ' 0
						ORDER BY topic_type DESC, topic_time DESC ' . ($key == 'topic' ? 'LIMIT ' . $start . ', ' . $config['subject_per_page'] : '');*/
				$result = $db->topic($forum_id, $value, $key, $start, $config['subject_per_page']);
				
				$array_topics_id = array();
				$data_topic = array();
				while ($row = $result->fetch_array()) {
					$array_topics_id[] = $row['topic_id'];
					$data_topic[] = $row;
				}
				
				if (!empty($array_topics_id)) {
					$result = $db->select(TOPICS . ' t,' . POSTS . ' p,' . USERS . ' u,' . GROUPS . ' g', 
					't.topic_last_post_id=p.post_id AND p.poster_id=u.user_id AND g.group_id=u.group_id AND t.topic_id IN (' . implode(',', $array_topics_id) . ')', 'post_time DESC');
					$i = 0;
					while ($last_post_data = $result->fetch_array()) {
						$topicrow = topicRow($data_topic[$i], $last_post_data, $topic_read);
						if ($key == 'topic_header')
							if ($data_topic[$i]['topic_type'] == 1 && $start != 0)
								$nb_sticky = 0;
							else
								$nb_sticky++;					
						$template->assignBlockVars($key, $topicrow);
						$i++;
					}
				}
				
			}
			
			$pagination = 	pagination('viewtopic.php', array('f' =>  $forum_id), $post_count['nb_topic'], $config['subject_per_page'], $start);
		
			$mod_tools = array(
				'modo_topic_lock'		=>  _t('Verrouiller les sujets'), 
				'modo_topic_delete' 	=>	_t('Supprimer les sujets'),
				'modo_topic_move' 		=>	_t('Déplacer les sujets'),
				'modo_topic_sticky'		=>	_t('Mettre les sujet en post-it'),
				'modo_topic_annonce' 	=>	_t('Mettre les sujet en annonce'),
				'modo_topic_global' 	=> 	_t('Mettre les sujet en global'),
				// 'modo_view_logs' 		=>	_t('Voir les logs'),
				// 'modo_topic_copy'		=>	_t('Copier les sujets'),
				'modo_move_corbeile'	=>	_t('Déplacer les sujets vers la corbeille')
			);
			viewQuickModTools($submit, $mod_post, $mod_tools, $checkbox_topic);
		
			$template->assignVars(array(
				'TOPIC_HEADER' 	=>	$nb_sticky > 0,
				'ICON_DISPLAY'	=>  $db->forum[$forum_id]['forum_iconset_id'] != 0
				
			)
		);
		}
		
		$can_new_topic = $user->isCanForum($permission['forum']['topic_new'], $forum_id);
		$template->assignVars(displayGlobaleTextHeader());
		$template->assignVars(array(
				'H1_NAME'		=>	$h1_name,
				'LIST_TOPIC'	=>  $topic_exist,
				'CAN_NEW_TOPIC'	=>  $can_new_topic,
				'U_NEW_TOPIC'	=>  makeUrl('posting.php', array('mode' => 'post', 'f' => $forum_id)),
				'L_NEW_SUBJECT'	=> _t('Nouveau sujet'),
				'PAGINATION' 	=> 	$pagination,
				'L_STICKY'		=>  _t('Annonces et post-it')
			)
		);
	}
	else {
		$access = _t('Vous n\'avez pas la permission de voir ce forum');
	}

	
}
else {
	$access = _t('Ce forum n\'existe pas');
}

displayError();

$template->assignVars(displayGlobaleButtons());
$template->assignVars(array(
				'L_INFOS'	=> _t('Information'),
				'ACCESS'	=> $access,
				'IS_RSS'	=> $rss,
				'MOD_TOOLS'	=> $template->varExist('MOD_TOOLS') ? $template->getVar('MOD_TOOLS') : ''
		)
	);

if ($rss) {
	header("content-type: text/xml");
	$template->setTemplate('rss.html');
}
else {
	pageHeader($template, $config['sitename'] . ' - ' . $h1_name);
	$template->setTemplate('viewtopic_body.html');
}

?> 
