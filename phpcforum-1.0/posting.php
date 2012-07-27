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

//setLang('_body', $lang);

$forum_id = requestVar('f', 0, 'unsigned int');
$topic_id = requestVar('t', 0, 'unsigned int');
$post_id = requestVar('p', 0, 'unsigned int');
$mode = requestVar('mode', '');

$post_text = requestVarPost('text', '');
$post_subject = requestVarPost('subject', '');
$post_icon = requestVarPost('icon', null);
$post_edit_reason = requestVarPost('post_edit_reason', '');

$poll_title   = requestVarPost('poll_title', '');
$poll_options = requestVarPost('poll_options', '');
$edit_poll_options = requestVarPost('edit_poll_options', '');
$poll_max_options = requestVarPost('poll_max_options', 1, 'unsigned int');
$poll_length = requestVarPost('poll_length', 0, 'unsigned int');
$poll_vote_change = requestVarPost('poll_vote_change', 0);

$array_poll_options = array();
if ($poll_options != '') {
	$array_poll_options = explode('<br />', nl2br($poll_options));
}

$post_submit = isset($_POST['submit_post']);

$error = array();
$time = time();
$txt_not_posting  = '';
$can_posting = true;
$etat = true;
$nb_icons = 0;
etatSubmitConfirm(false);

$db->dataForums($user);

$poll = $mode != 'reply' && $user->isCanForum($permission['forum']['poll_create'], $forum_id);

if ($post_submit) {
		if ($post_text == '') 
			$error[] = _t('Le texte vide');
		elseif (strlen($post_text) < $config['msg_min_char_post']) 
			$error[] = _t('Le texte doit avoir minimum ' . $config['msg_min_char_post'] . ' lettres');
		if ($post_subject == '')
			$error[] = _t('Veuillez indiquer le sujet');
		if  ($poll_title == '' && $poll_options != '') {
			$error[] = _t('Le titre du sondage vide');
		}
		if ($poll_title != '' && sizeof($array_poll_options) < 2) {
			$error[] = sprintf(_t('Veuillez mettre au minimum %d options'), 2);
		}
		if ($poll_title != '' && ($poll_max_options < 1 || $poll_max_options > sizeof($array_poll_options))) {
			$error[] = _t('Le champ "Nombre de vote pouvant être coché" n\'est pas correct');
		}
		if ($poll_title != '' && $poll_length < 0) {
			$error[] = _t('La durée du sondage n\'est pas correct');
		}
		if ($poll_title != '' && $poll_length > $config['max_poll_options']) {
			$error[] = sprintf(_t('Veuillez mettre moins de %d options'), $config['max_poll_options']);
		}
			
}

if (idIsExist($forum_id, 'forum')) {
	
	$iconset = $db->forum[$forum_id]['forum_iconset_id'] != 0;
	if ($post_submit && $iconset && $post_icon == null) {
		$error[] = _t('Veuillez mettre une icône de sujet');
	}
	
	if ($mode == 'post') {
			postNewTopic($post_submit, $forum_id, $post_subject, $post_text, $poll_title, $array_poll_options, $poll_length, $poll_max_options, $poll_vote_change, $post_icon);
	}
	elseif ($mode == 'reply') {
			replyTopic($post_submit, $forum_id, $topic_id, $post_subject, $post_text, $post_icon);
	}
	elseif ($mode == 'edit') {
			$post_exist = idIsExist($post_id, 'post');
			if ($post_exist) {
				$result = $db->query('SELECT post_text, post_subject, poster_id, post_edit_count, poll_title, poll_vote_change, poll_max_options, poll_length, p.topic_id,  post_id, topic_first_post_id, p.icon_id
									  FROM ' . POSTS . ' p, ' . TOPICS . ' t
									  WHERE t.topic_id=p.topic_id AND p.post_id=' . $post_id);
				$data = $result->fetch_array();
				$can_posting = ($user->isCanForum($permission['forum']['msg_edit'], $forum_id) && $data['poster_id'] == $user->data['user_id']) || $user->isCanForum($permission['forum']['modo_msg_edit'], $forum_id);
			}

			if (!$user->isCanForum($permission['forum']['poll_edit'], $forum_id)) {
				$poll = false;
			}	
			if ($can_posting) {
				if ($post_exist) {
					$is_first_post = $data['post_id'] == $data['topic_first_post_id'];
					if (!$post_submit) {	
						$post_text = $data['post_text'];
						$post_subject = $data['post_subject'];
						$post_icon = $data['icon_id'];
						
						$poll &= $is_first_post;
						
						if ($poll) {
							$poll_title = $data['poll_title'];
							$poll_vote_change = $data['poll_vote_change'];
							$poll_max_options = $data['poll_max_options'];
							$poll_length = $data['poll_length'];
							if ($poll_title != '') { 
								$result = $db->query('SELECT poll_option_text
										  FROM ' . POLL_OPTIONS . '
										  WHERE topic_id = ' . $data['topic_id']);
								while ($data = $result->fetch_array()) {
									$poll_options .= $data['poll_option_text'];
								}
								$edit_poll_options = $poll_options;
							}
						}
					}
					elseif (empty($error)) {
							if ($poll_title != '' && $data['post_id'] == $data['topic_first_post_id']) {
								updatePoll($poll_title, $array_poll_options, $edit_poll_options != $poll_options, $data['topic_id'], $poll_length, $poll_max_options, $poll_vote_change);
								
							}
							$etat = $db->query('UPDATE ' . POSTS . ' SET 
											icon_id="' . $post_icon . '", 
											post_subject="' . htmlspecialchars($post_subject) . '", 
											post_text="' . htmlDoubleQuote($post_text) . '", 
											post_checksum="' . md5($post_subject.$post_text) . '", 
											post_edit_time=' . $time . ',
											post_edit_reason="' . htmlspecialchars($post_edit_reason) . '", 
											post_edit_user=' . $user->data['user_id'] . ', 
											post_edit_count=' . ($data['post_edit_count']+1) . ' 
										WHERE post_id=' . $post_id);
							if ($is_first_post) {
								$etat &= $db->update(TOPICS, array('topic_title' => htmlspecialchars($post_subject), 'icon_id' => $post_icon), 'topic_id=' . $data['topic_id']);
							}
							
							$url_retour = makeUrl('viewtopic.php', array('f' => $forum_id, 't' => $data['topic_id']));
							etatSubmitConfirm(true, array(_t('Le message a bien été édité'), _t('L\'édition du message a échoué')), $url_retour, $etat);
							metaRefresh(3, $url_retour);
						
					}
				
				}
				else {
					$txt_not_posting = _t('Le message n\'existe pas');
				}
			
			}
			else {
				$txt_not_posting = _t('Vous n\'avez pas la permission d\'éditer ce sujet');
			}
	}
	else {
		$txt_not_posting = _t('Ce mode n\'existe pas');

	}
	
	$iconset_mandatory = false;
	if ($iconset) {
		$result = $db->select(ICONS . ' i,' . ICONSET . ' iset', 'i.iconset_id=iset.iconset_id AND i.iconset_id=' . $db->forum[$forum_id]['forum_iconset_id'] . ' AND icon_display=1', 'icon_position');
		
		while ($data = $result->fetch_assoc()) {
			$row = array(
				'ID'		=>	$data['icon_id'],
				'NAME'		=>	$data['icon_name'],
				'PATH'		=>	'images/icons/' . $data['icon_path'],
				'WIDTH'		=>	$data['icon_width'],
				'HEIGHT'	=>	$data['icon_height'],
				'CHECKED'	=>  isset($post_icon) && $post_icon == $data['icon_id']
			);
			$template->assignBlockVars('icon', $row);
			$iconset_mandatory = $data['iconset_mandatory'];
			$nb_icons++;
		}
	}
	$iconset_mandatory =  $db->forum[$forum_id]['forum_icon_mandatory'] == 0 ? $iconset_mandatory : $db->forum[$forum_id]['forum_icon_mandatory'] == 2;
	$template->assignVars(array(
		'DISPLAY_ICONSET'		=> 	$iconset && $nb_icons > 0,
		'ICONSET_MANDATORY'		=>  $iconset_mandatory,
		'DEFAULT_CHECKED'		=>  !isset($post_icon) || (isset($post_icon) && $post_icon == 0)
	));
	
}
else {
	$txt_not_posting = _t('Ce forum n\'existe pas');
}

displayError();

$template->assignVars(displayGlobaleButtons());
$template->assignVars(array(
			'MODE'				 => $mode,
			'ERROR'				 => !empty($error),
			'TXT_NOT_POSTING'	 => $txt_not_posting,
			'TEXT'				 => htmlDoubleQuoteRev($post_text),
			'SUBJECT' 			 => $template->varExist('SUBJECT') ? $template->getVar('SUBJECT') : ($post_submit ? htmlspecialchars($post_subject) : $post_subject),
			'POST_EDIT_REASON'	 => $post_edit_reason,
			'POLL' 			 	 => $poll,
			'POLL_TITLE' 		 => $poll_title,
			'POLL_OPTIONS' 		 => $poll_options,
			'EDIT_POLL_OPTIONS'	 => $edit_poll_options,
			'POLL_MAX_OPTIONS'	 => $poll_max_options,
			'POLL_LENGTH'	 	 => $poll_length,
			'POLL_VOTE_CHANGE'	 => ($poll_vote_change != '' && $mode != 'edit') || $poll_vote_change == 1,
			'SUBJECT_MAXLENGTH'	 => $config['subject_maxlength'],

			'L_INFO'			=> _t('Information'),
			'L_POST'			=> _t('Poster un message'),
			'L_SUBJECT'			=> _t('Sujet'),
			'L_REASON'			=> _t('Raison'),
			'L_CREATE_POLL'		=> _t('Création d\'un sondage'),
			'L_QUESTION_POLL'	=> _t('Question du sondage'),
			'L_OPTIONS_POLL'	=> _t('Options du sondage'),
			'L_HELP_OPTION_POLL'	=> sprintf(_t('Veuillez placer chaque option sur une nouvelle ligne. Vous pouvez rentrer %d options au maximum'), $config['max_poll_options']),
			'L_HELP_EDIT_OPTION_POLL'	=> _('Attention, en modifiant les options, vous réinitialisez les votes à 0'),
			'L_NB_CHECK_OPTION'	=> _t('Nombre d\'options pouvant être coché'),
			'L_DURATION_POLL'	=> _t('Durée du sondage'),
			'L_HELP_DURATION_POLL'	=> _t('Laissez 0 pour une durée indéfinie'),
			'L_DAYS'			=> _t('jours'),
			'L_USER_CHANGE_VOTE'=> _t('L\'utilisateur peut changer son vote'),
			
			'L_ICONSET'			=> _t('Icône de sujet'),
			'L_NULL'			=> _t('Aucun'),
		
			
			
	)
);




$template->addJs('js', array(
	'ckeditor/ckeditor'
));
pageHeader($template, _t('Poster un message'));
$template->setTemplate('posting_body.html');
?> 
