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

function postNewTopic($post_submit, $forum_id, $post_subject, $post_text, $poll_title, $array_poll_options, $poll_length, $poll_max_options, $poll_vote_change, $icon_id) {
	global $db, $user, $txt_not_posting, $error;
	$can_posting = $user->isCanForum($permission['forum']['topic_new'], $forum_id);
	$etat = false;
	if ($can_posting) {
		if (empty($error) && $post_submit) {
			if (!postExist(md5($post_subject.$post_text), $user->current_ip)) {
		
			$topic_id = createNewTopic($forum_id, $post_subject, $user->data['user_id'], $icon_id);
			
			if ($poll_title != '')
				createNewPoll($poll_title, $array_poll_options, $topic_id, $poll_length, $poll_max_options, $poll_vote_change);
			
			$etat = createNewPost($topic_id, $forum_id, $user->data['user_id'], $icon_id, $user->current_ip, $post_subject, $post_text);
			/*$result = $db->query('SELECT post_id FROM ' . POSTS . ' 
								  WHERE forum_id=' . $forum_id . ' AND topic_id=' . $topic_id . ' ORDER BY post_id DESC');
			$data = $result->fetch_array();*/
			$post_id = $db->insert_id;
			$db->updateCreatePostInTopic($topic_id, $post_id);
			$db->updateLastPostForum($forum_id, $post_id);
			
			$db->totalChange('total_post');
			$db->totalChange('total_topic');
			incrementeForumStats($forum_id, 'forum_nb_subject');
			incrementeForumStats($forum_id, 'forum_nb_post');
			$user->incrementeMsg();
			$url_retour = makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id));
			etatSubmitConfirm(true, array(_t('Le message a été envoyée avec succès'), _t('L\'envoi du message a échoué')), $url_retour, $etat);
			metaRefresh(3, $url_retour);
			}
			$txt_not_posting = _t('Ce sujet existe déjà. Vous ne pouvez pas le dupliquer !');
		}
	}
	else {
		$txt_not_posting = _t('Vous n\'avez pas la permission de créer un nouveau sujet');
	}
	return $txt_not_posting;
}

function replyTopic($post_submit, $forum_id, $topic_id, $post_subject, $post_text, $icon_id) {
		global $db, $user, $txt_not_posting, $template, $error;
		$can_posting = $user->isCanForum($permission['forum']['topic_reply'], $forum_id);
		if ($can_posting) {
			if (idIsExist($topic_id, 'topic')) {
				if (idIsExist($forum_id, 'forum')) {
					$data = $db->select(TOPICS . ' t,' . USERS . ' u', 't.topic_poster=u.user_id AND t.topic_id=' . $topic_id, null, null, '*', 'one');
					
					if (!$post_submit){
						$post_subject = _t('Re') . ': ' . $data['topic_title'];	
						$template->assignVars(array('SUBJECT' => $post_subject));
						if (!$user->isCanForum($permission['forum']['modo_post_lock'], $forum_id) && $data['topic_status'] == 1)
							$txt_not_posting = _t('Le sujet est verrouillé. Vous n\'avez pas la permission de poster');
					}
					elseif (empty($error) && $post_submit) {
					if (!postExist(md5($post_subject.$post_text), $user->current_ip)) {
						
						Plugins::flag('replyPost', array(&$post_subject, &$post_text, $topic_id, $forum_id));
						$etat = createNewPost($topic_id, $forum_id, $user->data['user_id'], $icon_id, $user->current_ip, $post_subject, $post_text);
						$post_id = $db->insert_id;
						$db->updateReplyPostInTopic($topic_id, $post_id);
						$db->updateLastPostForum($forum_id, $post_id);
						$db->totalChange('total_post');
						incrementeTopicReply($topic_id);
						incrementeForumStats($forum_id, 'forum_nb_post');
						$user->incrementeMsg();
						
						if (profileOptions($data, 'user_reply_avert') && $data['topic_poster'] != $user->data['user_id']) {
							$text = str_replace('{SITE_NAME}', $config['sitename'], $config['avert_new_reply_text']);
							$text = str_replace('{AUTHOR}', $user->data['username'], $text);
							$text = str_replace('{TOPIC_NAME}', $data['topic_title'], $text);
							$text = str_replace('{U_TOPIC}', 'http://' . $_SERVER['HTTP_HOST'] . ROOT . '/viewtopic.php?f=' . $forum_id . '&t=' . $topic_id, $text);
							sendEmail($data['user_email'], $text, $text, sprintf(_t('%s - Réponse à votre sujet'), $config['sitename']));
						}
					
						$db->update(TOPICS_READ, array('state' => 0), 'topic_id=' . $topic_id . ' AND user_id !=' . $user->data['user_id']);
						$db->deleteTopicRead($topic_id, $user->data['user_id']);
						
						$url_retour = makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $topic_id));
						etatSubmitConfirm(true, array(_t('Le message a été envoyée avec succès'), _t('L\'envoi du message a échoué')), $url_retour, $etat);
						metaRefresh(3, $url_retour);
						}
						else
							$txt_not_posting = _t('Ce message existe déjà. Vous ne pouvez pas le dupliquer !');
					}
					
				}
				else
					$txt_not_posting = _t('Le forum n\'existe pas');
			
			}
			else
				$txt_not_posting = _t('Le sujet n\'existe pas');
		
		}
		else {
			$txt_not_posting = _t('Vous n\'avez pas la permission de répondre au sujet');
		}
		return $txt_not_posting;
}

function createNewTopic($forum_id, $post_subject, $user_id, $icon_id, $first_post_id = 0, $last_post_id = 0) {
	global $db;
	$db->insert(TOPICS, array(
		'forum_id'				=>	$forum_id,
		'icon_id'				=> 	$icon_id,
		'topic_title'			=> htmlspecialchars($post_subject),
		'topic_poster'			=> $user_id,
		'topic_time'			=> time(),
		'topic_first_post_id'	=> $first_post_id,
		'topic_last_post_id'	=> $last_post_id
	));
	return $db->insert_id;
}

function postExist($md5_post, $poster_ip) {
	global $db;
	$sql = 'SELECT post_id FROM ' . POSTS . ' WHERE poster_ip="' . $poster_ip . '" AND post_checksum="' . $md5_post . '"';
	$result = $db->query($sql);
	$data = $result->fetch_array();
	return !empty($data['post_id']);
}

function createNewPost($topic_id, $forum_id, $user_id, $icon_id, $user_ip, $post_subject, $post_text) {
	global $db;
	$post_checksum = md5($post_subject.$post_text);
	return $db->insert(POSTS, array(
		'topic_id' 		=> 	$topic_id,
		'forum_id'		=>	$forum_id,
		'poster_id'		=>	$user_id,
		'icon_id'		=> 	$icon_id,
		'poster_ip'		=> $user_ip,
		'post_time'		=> time(),
		'post_subject'	=>  htmlspecialchars($post_subject),
		'post_text'		=>	htmlDoubleQuote($post_text),
		'post_checksum'	=>	$post_checksum
	));
}

function createNewPoll($poll_title, $poll_options, $topic_id, $poll_length = 0, $poll_max_options = 1, $poll_vote_change = 0, $poll_options_changed = true) {
	global $db;
	 $db->query('UPDATE ' . TOPICS . ' SET 
					poll_title="' . htmlspecialchars($poll_title) . '",
					poll_start=' . time() . ',
					poll_length=' . htmlspecialchars($poll_length) . ',
					poll_max_options=' . htmlspecialchars($poll_max_options) . ',
					poll_vote_change=' . ($poll_vote_change != '' ? 1 : 0) . '
				 WHERE topic_id=' . $topic_id);
	if ($poll_options_changed) {
		$sql = 'INSERT INTO ' . POLL_OPTIONS . ' VALUES';
		$sizeof = sizeof($poll_options);
		for ($i=0 ; $i < $sizeof ; $i++) {
			$sql .= '("", ' . $topic_id . ', "' . htmlspecialchars(str_replace('<br />', '', $poll_options[$i])) . '", 0)' . ($i == $sizeof-1 ? ';' : ', ');
		}
		$db->query($sql);
	}
}

function updatePoll($poll_title, $poll_options, $poll_options_changed, $topic_id, $poll_length, $poll_max_options, $poll_vote_change) {
	global $db;
	if ($poll_options_changed) {
		$db->query('DELETE FROM ' . POLL_VOTES . ' WHERE topic_id=' . $topic_id);
		$db->query('DELETE FROM ' . POLL_OPTIONS . ' WHERE topic_id=' . $topic_id);
	}
	createNewPoll($poll_title, $poll_options, $topic_id, $poll_length, $poll_max_options, $poll_vote_change, $poll_options_changed);
}

function reiniPoll($topic_id) {
	global $db;
	$etat = $db->query('DELETE FROM ' . POLL_VOTES . ' WHERE topic_id=' . $topic_id);
	$etat &= $db->query('UPDATE ' . POLL_OPTIONS . ' SET 
							poll_option_total=0,
						WHERE topic_id=' . $topic_id);
	$etat &= $db->query('UPDATE ' . TOPICS . ' SET 
							poll_last_vote=0,
						WHERE topic_id=' . $topic_id);
  return $etat;
}

function deletePoll($topic_id) {
	global $db;
	$etat = $db->query('DELETE FROM ' . POLL_VOTES . ' WHERE topic_id=' . $topic_id);
	$etat &= $db->query('DELETE FROM ' . POLL_OPTIONS . ' WHERE topic_id=' . $topic_id);
	$etat &= $db->query('UPDATE ' . TOPICS . ' SET 
							poll_title="",
							poll_start=0,
							poll_length=0,
							poll_max_options=1,
							poll_vote_change=0,
							poll_last_vote=0
					   WHERE topic_id=' . $topic_id);
	return $etat;
}

function createNewVote($topic_id, $poll_options, $vote_user_id, $vote_user_ip) {
	global $db;
	 $db->query('UPDATE ' . TOPICS . ' SET 
					poll_last_vote=' . time() . '
				 WHERE topic_id=' . $topic_id);
	$sql = 'INSERT INTO ' . POLL_VOTES . ' VALUES';
	$sizeof = sizeof($poll_options);
	for ($i=0 ; $i < $sizeof ; $i++) {
		$db->changePollOptionTotal(true, $poll_options[$i]);
		$sql .= '(' . $topic_id . ', "' . htmlspecialchars($poll_options[$i]) . '", ' . $vote_user_id . ', "' . $vote_user_ip . '")' . ($i == $sizeof-1 ? ';' : ', ');
	}
	return $db->query($sql);
}

function updateVote($topic_id, $poll_options, $user_vote, $vote_user_id, $vote_user_ip) {
	global $db;
	for ($i=0 ; $i < sizeof($user_vote) ; $i++) {
		$db->changePollOptionTotal(false, $user_vote[$i]);
	}
	$db->query('DELETE FROM ' . POLL_VOTES . ' WHERE topic_id=' . $topic_id . ' AND vote_user_id=' . $vote_user_id);
	return createNewVote($topic_id, $poll_options, $vote_user_id, $vote_user_ip);
}


function userVote($user_id, $topic_id) {
	global $db;
	$poll_option_vote = array();
	$sql = 'SELECT poll_option_id  FROM ' . POLL_VOTES . ' WHERE vote_user_id=' . $user_id . ' AND topic_id=' . $topic_id;
	$result = $db->query($sql);
	while ($row = $result->fetch_array()) {
		$poll_option_vote[] = $row['poll_option_id'];
	}
	return $poll_option_vote;
}

function changePollOptionTotal($poll_option_id, $type = true) {
	global $db;
	/*$result = $db->query('SELECT poll_option_total FROM ' . POLL_OPTIONS . ' 
						  WHERE poll_option_id=' . $poll_option_id);
	$data = $result->fetch_array();*/
	return $db->changePollOptionTotal($type, $poll_option_id);
}

function synchronizedTopic($topic_id) { 
	global $db;
	$first_post = $db->firstPost($topic_id);
	$last_post = $db->lastPost($topic_id);
	return $db->synchronizedPostInTopic($topic_id, $first_post, $last_post['post_id']);
	/*$result_reply = $db->query('SELECT COUNT(*) nb_topic_reply FROM ' . POSTS . ' 
						  WHERE topic_id=' . $topic_id);
	$data_reply = $result_reply->fetch_array();
	$result	= $db->query('SELECT * FROM ' . POSTS . ' 
						  WHERE topic_id=' . $topic_id . ' ORDER BY post_time');
	$data   = $result->fetch_array();
	$db->query('UPDATE ' . TOPICS . ' 
				SET topic_first_post_id=' . $data['post_id'] . ', 
					topic_replies=' . ($data_reply['nb_topic_reply']-1) . ',
					icon_id=' . $data['icon_id'] . ', 
					topic_poster=' . $data['poster_id'] . ', 
					topic_time=' . $data['post_time'] . ',
					topic_title=' . $data['post_subject'] . '
				WHERE topic_id=' . $topic_id);
	*/
}

function incrementeTopicReply($topic_id) {
	global $db;
	/*$result = $db->query('SELECT topic_replies FROM ' . TOPICS . ' 
						   WHERE topic_id=' . $topic_id);
	$data = $result->fetch_array();*/
	return $db->query('UPDATE ' . TOPICS . ' SET topic_replies=topic_replies+1
					   WHERE topic_id=' . $topic_id);
}

function permissionPost($forum_id, $poster_id, $name_permission_global, $name_permission) {
	global $permission, $user;
	$global_can = $user->isCanForum($permission['forum'][$name_permission_global], $forum_id);
	return $global_can ? $global_can : ($user->isCanForum($permission['forum'][$name_permission], $forum_id) ? $user->data['user_id'] == $poster_id : false);
}

function synchronizedForum($forum_id) { 
	global $db;
	$result_topic = $db->query('SELECT COUNT(*) nb_topic FROM ' . TOPICS . ' 
						  WHERE forum_id=' . $forum_id);
	$data_topic = $result_topic->fetch_array();
	$result_post = $db->query('SELECT COUNT(*) nb_post FROM ' . POSTS . ' 
						  WHERE forum_id=' . $forum_id);
	$data_post = $result_post->fetch_array();

	$result	= $db->query('SELECT post_id FROM ' . POSTS . ' 
						  WHERE forum_id=' . $forum_id . ' ORDER BY post_time DESC');
	$data   = $result->fetch_array();
	$db->update(FORUMS, array(
					'forum_nb_subject'			=> $data_topic['nb_topic'], 
					'forum_nb_post' 			=> $data_post['nb_post'],
					'forum_last_post_id' 		=> isset($data['post_id']) ? $data['post_id'] : 0
				),'forum_id=' . $forum_id);
}

function deletePost($forum_id, $topic_id, $post_id, $redirect = true) {
	global $db;
	$delete_topic = false;
	$b = $db->delete(POSTS, array('post_id' => ' IN (' . $post_id . ')',  'forum_id' => '=' . $forum_id, 'topic_id' => '=' . $topic_id), true, true);
	$data = $db->select(POSTS, 'forum_id=' . $forum_id . ' AND topic_id=' . $topic_id, null, null, 'COUNT(*) AS nb_post', 'one');
	if ($data['nb_post'] == 0) {
		$b &= $db->delete(TOPICS, array('topic_id' => $topic_id, 'forum_id' => $forum_id));
		$delete_topic = true;
	}
	else {
		synchronizedTopic($topic_id);
	}
	
	synchronizedForum($forum_id);	
	synchronizedGlobalStats();
	
	if ($redirect) {
		$array_back = array('f' => $forum_id);
		if (!$delete_topic) {
			$array_back['t'] = $topic_id;
		}
		$array_back['start'] = $start;
		
		$url_retour = makeUrl('viewtopic.php', $array_back );
		etatSubmitConfirm($b, array(_t('Les messages ont bien été supprimés'), _t('La suppression des messages a échoué')), $url_retour);
		metaRefresh(3, $url_retour);
	}
	else {
		return $b;
	}
	
}
?>