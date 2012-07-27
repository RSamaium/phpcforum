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
function profile_avatar($submit) {
	global $user, $db, $template, $permission, $error, $config;
	$user_permis = $permission['users'];
	
	$name_post = array();
	
	$tab_safe = array('PNG', 'JPG', 'JPEG', 'GIF');
	 
	$file = requestVarFile('user_avatar', '');
	
	etatSubmitConfirm(false);
	
	if ($submit) {
		$data_post = dataPost($name_post);
		if ($file != '') {
			$upload = new Upload($file, PATH_AVATAR);
			$img =  getimagesize($upload->uploadTmp());
			$width = $img[0];
			$height = $img[1];
			if ($width < $config['max_width_avatar'] && $height < $config['max_height_avatar']) {
					if ($upload->extensionAutorised($tab_safe)) {	
						$new_name = $user->data['user_id'] . '.' . strtolower($upload->uploadType());
						if ($upload->uploadMoveFile($new_name)) {	
							$sql = 'UPDATE ' . USERS . ' 
									SET user_avatar="' . $new_name . '"
									WHERE user_id=' . $user->data['user_id'];
							$success = $db->query($sql);
							$user->updateData();
							$url_retour = makeUrl('profile.php', array('mode' => 'avatar'));
							etatSubmitConfirm(true, array(_t('La modification de l\'avatar a bien été effectuée'), _t('La modification de l\'avatar a échoué')), $url_retour, $success);
							metaRefresh(3, $url_retour);
						}
						else {
							$error[] = _t('Chargement du fichier échoué');
						}
					}
					else {
						$error[] = _t('Extension du fichier non valide');
					}
				}
				else {
				$error[] = sprintf(_t('Les dimensions de l\'image sont supérieurs à %dpx*%dpx'), $config['max_width_avatar'], $config['max_height_avatar']);
			}		
		}
		else {
					$error[] = _t('Aucun fichier selectionné');
		}
			
	}
	$template->assignVars(array(
			'CURRENT_AVATAR'	=> PATH_AVATAR . '/' . $user->data['user_avatar'],
			'L_EDIT_AVATAR' 	=> _t('Editer l\'avatar'),
			'L_AVATAR_ALT'		=> _t('Avatar de l\'utilisateur'),
			'L_HELP_EXTERN_IMG'	=> _t('Indiquez le chemin vers une image de la façon suivante : \'http://unsite.com/monimage.png\''),
			'L_CHOISE_IMG'		=> sprintf(_t('Importer une image (Extension : GIF, PNG ou JPEG - Dimension maximum: %dpx*%dpx)'), $config['max_width_avatar'], $config['max_height_avatar']),
			'EXIST_AVATAR' 		=> $user->data['user_avatar'] != ''
	   )
	);
}


function profile_pm($submit, $pm_id = null, $action = 'list') {
	global $user, $db, $template, $permission, $config;
	$user_permis = $permission['users'];
	$start = requestVar('start', 0, 'unsigned int');
	etatSubmitConfirm(false);
	
	$limit = $start . ', ' . $config['max_msg_private'];
	$from = 'FROM ' . PM_TO . ' pt, ' . PM . ' pm, ' . USERS . ' u, ' . GROUPS . ' g 
			WHERE pt.pm_id=pm.pm_id AND pt.author_id=u.user_id AND u.group_id=g.group_id AND pt.user_id=' . $user->data['user_id'] . ' AND pm_deleted=0' . ($pm_id != null ? ' AND pm.pm_id=' . $pm_id : ' ORDER BY pm_time DESC');
	$result_count_pm = $db->query('SELECT COUNT(*) AS nb_pm ' . $from);
	
	if ($pm_id == null) {	
		
		$count_pm = $result_count_pm->fetch_array();
		
		$nb_total = 0;
		$nb_unread = 0;
		$size = $action == 'list' ? 2 : 1;
		for ($i=0 ; $i < $size ; $i++) {
			if ($action == 'list') {
				$result = $db->loadPm($user->data['user_id'], null, null, $i);
			}
			else {
				$result = $db->loadPmSend($user->data['user_id'], null, null);
			}
			$name_key = $i == 0 ? 'unread' : 'read';
			$pm_id_last = 0;
			while ($row_pm = $result->fetch_array()) {	
				if ($action != 'list' && $row_pm['pm_id'] == $pm_id_last) {
					$tab = $template->getBlockVar('pmrow_' . $name_key);
					$other_dest = end($tab);
					$value = $other_dest['PM_AUTHOR_NAME'] . '</span>, <span style="color:#' . $row_pm['group_color'] . '">' . $row_pm['username'];
					$template->setBlockVar('pmrow_' . $name_key, sizeof($tab)-1, 'PM_AUTHOR_NAME', $value);
				}
				else {
					$pmrow = dataPm($row_pm, $action);
					$template->assignBlockVars('pmrow_' . $name_key, $pmrow);
					$nb_total++;
				}
				if ($i == 0) {
					$nb_unread++;
				}
				
				$pm_id_last = $row_pm['pm_id'];
			}
		}
		if ($action == 'list') {
			$template->assignVars(array(
				'L_YOUR_PM'		=> _t('Vos messages privés'),
				'L_YOUR_PM_TOTAL'		=> sprintf(_nt('%d au total', '%d au total', $nb_total), $nb_total),
				'L_YOUR_PM_READ'		=> sprintf(_nt('%d lu', '%d lus', $nb_total-$nb_unread), $nb_total-$nb_unread),
				'L_YOUR_PM_UNREAD'		=> sprintf(_nt('%d non lu', '%d non lus', $nb_unread), $nb_unread),
				'IS_LIST'				=> true
				
			));
		}
		else {
			$template->assignVars(array(
				'L_YOUR_PM'		=> sprintf(_nt('Votre %d message privé envoyé', 'Vos %d messages privés envoyés', $nb_total), $nb_total),
				'IS_LIST'		=> false
			));
		}
		$template->assignVars(array(
			'CAN_NEW_PM' 			=> $user->isCan($user_permis['pm_send']),
			'U_NEW_PM'				=> makeUrl('profile.php', array('mode' => 'pm', 'i' => 'new')),
			'L_NEW_PM'				=> _t('Nouveau message privé'),
			'L_FILTER'				=> _t('Filtrer'),
			'L_ORDER_BY'			=> _t('Trier par'),
			'L_FILTER_ORDER'		=> _t('Filtrer et trier'),
			'L_ALL'					=> _t('Tout'),
			'L_PM_READ'				=> _t('Messages lues'),
			'L_PM_UNREAD'			=> _t('Messages non lues'),
			'L_TIME'				=> _t('Date'),
			'L_SUBJECT_NAME'		=> _t('Nom du sujet'),
			'L_MEMBERS'				=> _t('Membres'),
			'L_REVERSE'				=> _t('Inverser'),
			'PAGINATION' 			=> pagination('profile.php', array('mode' => 'pm'), $count_pm['nb_pm'], PER_PAGE_PM, $start)
			)
		);
	}
	else {
		if ($action == 'read') {
			$result = $db->loadPm($user->data['user_id'], $limit, $pm_id);
			$pm_row = $result->fetch_array();
			$pm = dataPm($pm_row);
			if ($pm_row['pm_read'] == 0) {
				$sql = 'UPDATE ' . PM_TO . ' 
						SET pm_read=1
						WHERE pm_id=' . $pm_id . ' AND user_id=' . $user->data['user_id'];
				$db->query($sql);
			}
		}
		elseif ($action == 'read_send') {
			$result = $db->loadPmSend($user->data['user_id'], $limit, $pm_id);
			$pm_row = $result->fetch_array();
			$pm = dataPm($pm_row);
		}
		
		$template->assignVars($pm);
		$template->assignVars(array(
				'CAN_REPLY_PM' 	=> $user->isCan($user_permis['pm_send']),
				'U_REPLY_PM'	=> makeUrl('profile.php', array('mode' => 'pm', 'i' => 'reply', 'p' => $pm_id)),
				'L_REPLY_PM'	=> _t('Répondre'),
				'L_READ_PM'		=> _t('Lire le message privé'),
				'L_REGDATE'		=> _t('Inscrit le'),
				'L_NB_POST'		=> _t('Nombre de message')
				)	
			);
		$template->assignVars(viewprofile(userData($pm_row['author_id'])));
	
	}
	
	
							
}

function optionDeletePm($submit, $tab_pm, $mode) {
	global $db, $user;
	etatSubmitConfirm(false);
	if ($submit) {
		$in = dataCheckBox($tab_pm);
		$size = sizeof($tab_pm);
		if ($mode == 'list') {
			$url_retour = makeUrl('profile.php', array('mode' => 'pm', 'i' => 'list'));
			$success = $db->deletePm($tab_pm, $in, $user->data['user_id']);
		}
		else {
			$url_retour = makeUrl('profile.php', array('mode' => 'pm', 'i' => 'send'));
			$success = $db->deleteSendPm($tab_pm, $in);
		}
		etatSubmitConfirm(true, array(sprintf(_t('%s messages privés supprimés'), $size), _t('La suppression des messages privés a échouée')), $url_retour, $success);
		//metaRefresh(3, $url_retour);
		
	}
	
}

function profile_register($submit) {
	global $db, $template, $error, $config;
	// SR
	$activ = $config['register_validation'];
	$input = array(
		'username'	=>	array(
							'type'		=> 'string',
							'required'	=> _t('Veuillez rentrer un nom d\'utilisateur'),
							'remote'	=> array('searchUser', 'this.value'),
							'omit'		=> '[\'"]',
							'minlength'	=> 3,
							'maxlength'	=> $config['maxlength_username']
						),
		'email'	=>	array(
							'type'		=> 'email',
							'required'	=> _t('Veuillez rentrer une adresse e-mail')
						),
		'confirm_email'	=>	array(
							'type'		=> 'email',
							'required'	=> _t('Veuillez rentrer une adresse e-mail'),
							'equalTo'	=> 'email'
				),
		'password'	=> array(
					'type'		=> 'string',
					'required'	=> _t('Un mot de passe est requis'),
					'minlength'	=> 5,
				),
		'confirm_password'	=> array(
					'type'		=> 'string',
					'required'	=> _t('Un mot de passe de confirmation est requis'),
					'equalTo'	=> 'password'
				),
		'captc'	=>	array(
				'type'		=> 'captcha'
		)
	);
	$input_error = array(
		'username'	=>	array(
							'type'			=> 	_t('Le type est invalide'),
							'remote' 		=> 	_t('Ce nom d\'utilisateur existe déjà. Veuillez en réessayer un autre'),
							'omit'			=>	_t('Veuillez ne pas mettre les caractères \' et " dans le nom d\'utilisateur'),
							'minlength'		=> 	sprintf(_t('Votre nom d\'utilisateur doit contenir minimum %d caractères'), 3)
						),
		'email'	=>	array(
							'type'		=> _t('Veuillez rentrer une adresse e-mail valide')
						),
		'confirm_email'	=>	array(
							'type'		=> _t('Veuillez rentrer une adresse e-mail valide'),
							'equalTo'	=> _t('L\'adresse e-mail n\'est pas équivalente à la précédente')
						),
		'password'	=> array(
					'minlength'	=> sprintf(_t('Votre mot de passe doit contenir %d caractères minimum'), 5),
				),
		'confirm_password'	=>	array(
					'equalTo'	=> _t('Le mot de passe n\'est pas équivalent au précédent')
			),
		'captc'	=>	array(
				'type'		=> _t('Vous êtes considérez comme un robot, votre enregistrement est impossible à effectuer')
		)
	);
	
	$forms = new Forms_Validate($input, $input_error);
	
	// $username =  htmlspecialchars(requestVarPost('username', ''));
	
	// $email =  htmlspecialchars(requestVarPost('email', ''));
	// $confirm_email =  htmlspecialchars(requestVarPost('confirm_email', ''));
	// $password =  htmlspecialchars(requestVarPost('email', ''));
	// $confirm_password =  htmlspecialchars(requestVarPost('confirm_email', ''));
	
	// if ($submit) {
		// if (searchUser($username) !== false)
			// $error[] = 'Ce nom d\'utilisateur existe déjà. Veuillez en réessayer un autre';
	// }

	$username = $forms->input['username']['value'];
	$email = $forms->input['email']['value'];
	$password = $forms->input['password']['value'];
	
	$template->assignVars(displayGlobaleButtons());
	$template->assignVars(array(
			'REG_USERNAME'					=> $username,
			'REG_EMAIL'				 		=> $email,
			'REG_CONFIRM_EMAIL'				=> $forms->input['confirm_email']['value'],
			'MAXLENGTH_USERNAME'			=> $config['maxlength_username'],
			'L_REG_HELP'					=> _t('L\'inscription prend seulement quelques secondes et vous offres de 
			nombreux avantages. Par exemple, l\'administrateur du forum peut accorder des permissions supplémentaires 
			aux utilisateurs inscrits. Avant de vous inscrire, assurez-vous d\'avoir pris connaissance de nos 
			conditions d\'utilisation et de notre politique de vie privée. Veuillez également vous assurer 
			d\'avoir consulté toutes les règles du forum.'),
			'L_REG'							=> _t('Inscription'),
			'L_USERNAME'					=> _t('Nom d\'utilisateur'),
			'L_HELP_USERNAME'				=> _t('Votre pseudonyme qui sera visible sur le forum par les autres membres.'),
			'L_EMAIL'						=> _t('Adresse e-mail'),
			'L_CONFIRM_EMAIL'				=> _t('Confirmer l\'adresse e-mail'),
			'L_PASSWORD'					=> _t('Mot de passe'),
			'L_HELP_PASSWORD'				=> _t('Mettez un mot de passe solide (caractères majuscules, minuscules, spéciaux et chiffres)'),
			'L_CONFIRM_PASSWORD'			=> _t('Confirmer le mot de passe'),
			'L_SUBMIT'						=> _t('S\'enregister et accepter les règles du forum'),
			'L_NO_FILL'						=> _t('A ne pas remplir'),
			
			'L_PASS_REQUIRED'				=> $forms->input['password']['required'],
			'L_PASS_MIN'					=> $forms->input_error['password']['minlength'],
			'L_PASS_EQUAL'					=> $forms->input_error['confirm_password']['equalTo'],
			'L_EMAIL_REQUIRED'				=> $forms->input['email']['required'],
			'L_EMAIL_TYPE'					=> $forms->input_error['email']['type'],
			'L_EMAIL_EQUAL'					=> $forms->input_error['confirm_email']['equalTo'],
			'L_USERNAME_REMOTE'				=> $forms->input_error['username']['remote'],
			'L_USERNAME_MIN'				=> $forms->input_error['username']['minlength'],
			'L_USERNAME_REQUIRED'			=> $forms->input['username']['required']
		)
	);

	$error = $forms->isValidate();

	
	if ($submit && empty($error)) {
		$url_retour = makeUrl('index.php');
		Plugins::flag('register', array(&$username, &$password, $email, &$activ));
		$valid_code = $db->newUser($username, $password, $email, $activ);
		$db->insert(USERS_GROUP, array(
			'group_id' 	=> GROUP_MEMBER_ID,
			'user_id'	=> $db->insert_id,
			'user_status'	=> 1,
			'user_date_joined'	=> time()
		
		));
		if ($activ == 1) {
			if ($valid_code !== false) {
				Plugins::flag('registerSendEmail', array($valid_code));
				$text = str_replace('{SITE_NAME}', $config['sitename'], $config['email_validation_text']);
				$text = str_replace('{U_VALID_REGISTER}', 'http://' . $_SERVER['HTTP_HOST'] . ROOT . '/index.php?v=' . $valid_code, $text);
				sendEmail($email, $text, $text, _t('Activation de votre compte'));
			}
			etatSubmitConfirm(true, array(_t('Un email de confirmation vous a été envoyé'), _t('L\'enregistrement a échoué')), $url_retour, $valid_code !== false);
		} 
		else {
			etatSubmitConfirm(true, array(_t('Vous vous êtes bien enregistré sur le forum'), _t('L\'enregistrement a échoué')), $url_retour, $valid_code !== false);
		}
		//metaRefresh(3, $url_retour);
	}
	else {
		etatSubmitConfirm(false);
	}
	
	return $error;
}


function profile_send_pm($submit, $mode, $pm_id = null) {
	global $user, $db, $template, $permission, $error, $config;
	$user_permis = $permission['users'];
	$time = time();	
	
	$post_text = requestVarPost('pm_text', '');
	$post_subject =  htmlspecialchars(requestVarPost('pm_subject', ''));
	$post_user =  htmlspecialchars(requestVarPost('pm_user', ''));

	$u =  requestVar('u', 0);
	
	$users = explode(';', $post_user);
	$users_id = array();
	
	if (idIsExist($u, 'user')) {
		$result = $db->query('SELECT username FROM ' . USERS . ' WHERE user_id=' . $u);
		$data = $result->fetch_array();
		$post_user = $data['username'];
	}
	
	$confirm = false;
	$txt_confirm = '';
	$max_destinataire = ($mode == 'reply' ? 1 : $config['max_destina_msg_private']);
	
	etatSubmitConfirm(false);
	
	if ($submit) {
		if ($post_text == '') 
			$error[] = _t('Le texte est vide');
		elseif (strlen($post_text) < $config['msg_min_char_post']) 
			$error[] = sprintf(_t('Le texte doit avoir minimum %d lettres'), $config['msg_min_char_post']);
		if ($post_subject == '')
			$error[] = _t('Veuillez indiquer le sujet');
		if ($users[0] == '')
			$error[] = _t('Veuillez indiquer un ou des destinataire(s)');
		elseif (sizeof($users) > $max_destinataire)
			$error[] =  sprintf(_t('Veuillez mettre %d destinataires au maximum',  $max_destinataire));
		else {
			$usernames = '';
			for ($i=0 ; $i < sizeof($users) ; $i++) {
				$usernames .= '"' . htmlspecialchars($users[$i]) . '",';
			}
			$usernames = preg_replace('#,$#', '', $usernames);
			$result = $db->select(USERS, 'username IN (' . $usernames . ') AND user_id != ' . ANONYMOUS_ID);
			while ($data = $result->fetch_assoc()) {
					if (!profileOptions($data, 'user_allow_contact_pm')) {
						$error[] = sprintf(_t('Le destinataire %s refuse les messages privés'), '<strong>' . $data['username'] . '</strong>');
					}
					$users_id[] = $data;
					/*if (empty($data['user_id']) || ANONYMOUS_ID == $data['user_id']) {
						$txt_false_destinataire = sprintf(_t('Le destinataire %s n\'existe pas'), '<strong>' . $data['username'] . '</strong>');
						if (!in_array($txt_false_destinataire, $error))
							$error[] = $txt_false_destinataire;
					}*/
					/*else {
						if (in_array($data['user_id'], $users_id)) {
							$txt_multiple_destinataire = sprintf(_t('Vous avez rentré plusieurs fois le destinataire %s'), '<strong>' . $data['username'] . '</strong>');
							if (!in_array($txt_multiple_destinataire, $error))
								$error[] = $txt_multiple_destinataire;
						}
						$users_id[] = $data['user_id'];
					}*/
			}
			if (empty($users_id)) {
				$error[] = _t('Le ou les destinataires sont incorrects');
			}
		}
				
	}

	
	if ($mode == 'new' ||$mode == 'reply') {
			if (empty($error) && $submit) {	
				$success = $db->insertPm($user, $time, $post_subject, $post_text);
				$last_pm_id = $db->insert_id;
		
				for ($i=0 ; $i < sizeof($users_id) ; $i++) {
					$success &= $db->insertPmTo($user, $last_pm_id, $users_id[$i]['user_id'], ($mode == 'reply' ? $pm_id : null));
					if (profileOptions($users_id[$i], 'new_pm_avert')) {
						$text = str_replace('{SITE_NAME}', $config['sitename'], $config['avert_new_mp_text']);
						$text = str_replace('{AUTHOR}', $user->data['username'], $text);
						$text = str_replace('{U_BOX_MP}', 'http://' . $_SERVER['HTTP_HOST'] . ROOT . '/profile.php?mode=pm', $text);
						sendEmail($users_id[$i]['user_email'], $text, $text, sprintf(_t('%s - Nouveau message privé'), $config['sitename']));
					}
				}
				 $url_retour = makeUrl('profile.php', array('mode' => 'pm'));
				 etatSubmitConfirm(true, array(_t('Le message privé a bien été envoyé'), _t('L\'envoie du message privé a échoué')), $url_retour, $success);
				metaRefresh(3, $url_retour);
			}
	}
	if ($mode == 'reply' && !$submit) {
			
		$sql = 'SELECT username, pm_subject, pm_id_replied FROM ' . PM . ' pm, ' . USERS . ' u, ' . PM_TO . ' pt
				WHERE  pt.pm_id=pm.pm_id AND pm.pm_id=' . $pm_id . ' AND pt.author_id=u.user_id';
		$result = $db->query($sql);
		$data = $result->fetch_array();
		
		$post_subject = 'Re: ' . $data['pm_subject'];
		$post_user = $data['username'];
		
		$pm_id_replied = $data['pm_id_replied'];
		while ($pm_id_replied != 0) {
			$sql = 'SELECT * FROM ' . PM_TO . ' pt, ' . PM . ' pm, ' . USERS . ' u, ' . GROUPS . ' g 
			WHERE pt.pm_id=pm.pm_id AND pt.author_id=u.user_id AND u.group_id=g.group_id AND pm.pm_id=' . $pm_id_replied;
			$result = $db->query($sql);
			$data = $result->fetch_array();
			$pm_id_replied = $data['pm_id_replied'];
			$list_pm_replied =	array(
				'TEXT'	=>  $data['pm_text']
			);
			$template->assignBlockVars('listreplied', $list_pm_replied);
		
		}
		
			/**/
		

			
		
					/*	if (!$post_submit){
							$result = $db->query('SELECT topic_title FROM ' . TOPICS . ' WHERE topic_id=' . $topic_id);
							$data = $result->fetch_array();
							$post_subject = 'Re: ' . $data['topic_title'];	
						}
						if (empty($error) && $post_submit) {
						$db->query('INSERT INTO ' . POSTS . ' VALUES(
								"", 
								' . $topic_id . ', 
								' . $forum_id . ',
								' . $user->data['user_id'] . ',
								0,
								"' . $user->current_ip . '",
								' . $time . ',
								"' . htmlspecialchars($post_subject) . '",
								"' . htmlspecialchars($post_text) . '",
								"",
								"",
								"",
								"")'
							);
		
						}
					}*/
	}
	/*elseif ($mode == 'edit') {
			if ($post_exist) {
				$result = $db->query('SELECT post_text, post_subject, poster_id, post_edit_count FROM ' . POSTS . ' WHERE post_id=' . $post_id);
				$data = $result->fetch_array();
				$can_posting = ($user->isCanForum($permission['forum']['msg_edit'], $forum_id) && $data['poster_id'] == $user->data['user_id']) || $user->isCanForum($permission['forum']['modo_msg_edit'], $forum_id);
			}
				if ($post_exist) {
					if (!$post_submit) {	
						$post_text = $data['post_text'];
						$post_subject = $data['post_subject'];
					}
					elseif (empty($error)) {
							$db->query('UPDATE ' . POSTS . ' SET 
											post_subject="' . $post_subject . '", 
											post_text="' . $post_text . '", 
											post_edit_time=' . $time . ',
											post_edit_reason="' . $post_edit_reason . '", 
											post_edit_user=' . $user->data['user_id'] . ', 
											post_edit_count=' . ($data['post_edit_count']+1) . ' 
										WHERE post_id=' . $post_id);
						
					}
				
				}
				else {
					$can_posting = false;
					$txt_not_posting = 'Le message n\'existe pas';
				}
			
			}*/


	$template->assignVars(array(
		'PM_TEXT'				=> $post_text,
		'PM_SUBJECT' 			=> $post_subject,
		'PM_USER' 				=> $post_user,
		'L_WRITE_PM'			=> _t('Ecrire un message privé'),
		'L_RECIPIENT'			=> _t('Destinataire'),
		'L_SUBJECT'				=> _t('Sujet'),
		'L_SEND'				=> _t('Envoyer')
		)
	);
	return $error;

}

function dataPm($row_pm, $action = 'list') {
	if ($action == 'send') {
		$row_pm['pm_read'] = 1;
		$url_read = makeUrl('profile.php', array('mode' => 'pm', 'i' => 'read_send', 'p' => $row_pm['pm_id']));
	}
	else {
		$url_read = makeUrl('profile.php', array('mode' => 'pm', 'i' => 'read', 'p' => $row_pm['pm_id']));
	}
	$pmrow = array(
			'PM_ID'				=>  $row_pm['pm_id'],
			'PM_SUBJECT'		=>  $row_pm['pm_subject'],
			'PM_AUTHOR_NAME'	=>  $row_pm['username'],
			'PM_AUTHOR_COLOR'	=>  $row_pm['group_color'],
			'PM_TIMESTAMP'		=>  $row_pm['pm_time'],
			'PM_DATE'			=>  sprintf(_t('Envoyé : %s'), strTime($row_pm['pm_time'], 8)),
			'PM_TEXT'			=>  $row_pm['pm_text'],
			'PM_READ'			=>  $row_pm['pm_read'] == 0 ? _t('Message non lu') : _t('Message lu'),
			'PM_READ_IMG'		=>	$row_pm['pm_read'] == 1 ? image('no_new_pm') : image('new_pm'),
			'U_PM_READ'			=>  $url_read		
	);
	return $pmrow;

}

function profile_info($submit , $ajax) {
	global $user, $db, $template, $permission, $error, $config;
	$user_permis = $permission['users'];
	
	$name_post = array('user_website', 'user_from', 'user_msn', 'user_yahoo', 'user_skype', 'user_facebook', 'user_twitter', 'user_hobbies', 'user_job', 'user_sexe', 'user_birthday');
	
	$input = array(
		'username'	=>	array(
					'type'		=> 'string',
					'required'	=> _t('Veuillez rentrer un nom d\'utilisateur'),
					'remote'	=> array('searchUser', 'this.value', $user->data['user_id']),
					'omit'		=> '[\'"]',
					'minlength'	=> 3,
					'maxlength'	=> 20,
					'disabled'	=> !$user->isCan($permission['users']['user_name'])
				),
		'user_email'	=>	array(
					'type'		=> 'email',
					'required'	=> _t('Veuillez rentrer une adresse e-mail'),
					'disabled'	=> !$user->isCan($permission['users']['user_email'])
				),
		'user_password'	=> array(
					'type'		=> 'string',
					'can_empty'	=> true,
					'minlength'	=> 5,
				),
		'confirm_user_password'	=> array(
					'type'		=> 'string',
					'equalTo'	=> 'user_password'
			)
	);
	$input_error = array(
		'username'	=>	array(
					'type'			=> 	_t('Le type est invalide'),
					'remote' 		=> 	_t('Ce nom d\'utilisateur existe déjà. Veuillez en réessayer un autre'),
					'omit'			=>	_t('Veuillez ne pas mettre les caractères \' et " dans le nom d\'utilisateur'),
					'minlength'		=> 	_t('Il faut minimum 3 caractères'),
					'maxlength'		=> 	_t('Il faut maximum 20 caractères')
			),
		'user_email'	=>	array(
					'type'		=> _t('L\'adresse email est invalide')
		),
		'user_password'	=> array(
					'minlength'	=> _t('Le mot de passe est trop court'),
				),
		'confirm_user_password'	=>	array(
					'equalTo'	=> _t('Le mot de passe de confirmation n\'est pas équivalent au précédent')
		)
	);
	
	$forms = new Forms_Validate($input, $input_error);
	
	$username = $forms->input['username']['value'];
	$email = $forms->input['user_email']['value'];
	$password = $forms->input['user_password']['value'];

	$error = $forms->isValidate();
	if ($submit) {
		if (isset($_POST['username']) && !$user->isCan($permission['users']['user_name']) && $user->data['username'] != $username) {
			$error[] = _t('Vous n\'avez pas la permission de changer votre pseudonyme');
		}
		if (isset($_POST['user_email']) && !$user->isCan($permission['users']['user_email']) && $user->data['user_email'] != $email) {
			$error[] = _t('Vous n\'avez pas la permission de changer votre adresse e-mail');
		}
	}
	
	
	if ($submit && empty($error)) {
		
		$update_global_profile = array();
		$success = true;
		if ($username != '') {
			$update_global_profile['username'] = $username;
		}
		if ($email != '') {
			$update_global_profile['user_email'] = $email;
		}
		if ($password != '') {
			$update_global_profile['user_password'] = md5($password);
		}
		if (!empty($update_global_profile)) {
			$success = $db->update(USERS, $update_global_profile, 'user_id=' . $user->data['user_id']);
		}
		$success &= $db->updateProfile($name_post, $user);
		
		$url_retour = makeUrl('profile.php', array('mode' => 'profile_info'));
		etatSubmitConfirm(true, array(_t('La modification du profil a bien été effectuée'), _t('La modification profil a échoué')), $url_retour, $success, $ajax);
		metaRefresh(3, $url_retour);
	}
	else {
		etatSubmitConfirm(false);
	}
	$template->assignVars(array(
			'PROFILE_USERNAME'		=> $user->data['username'],
			'PROFILE_EMAIL'			=> $user->data['user_email'],
			'CAN_CHANGE_USERNAME'	=> $user->isCan($permission['users']['user_name']),
			'CAN_CHANGE_EMAIL'		=> $user->isCan($permission['users']['user_email']),
			'L_WEBSITE' 			=> _t('Site Web'),
			'USER_WEBSITE'			=> $user->data['user_website'],
			'L_FROM' 				=> _t('Location'),
			'L_MSN' 				=> _t('Adresse MSN'),
			'L_YAHOO' 				=> _t('Adresse Yahoo!'),
			'L_SKYPE' 				=> _t('Adresse Skype'),
			'L_FACEBOOK' 			=> _t('Votre page Facebook'),
			'L_TWITTER' 			=> _t('Votre page Twitter'),
			'L_SKYPE' 				=> _t('Adresse Skype'),
			'L_HOBBIES' 			=> _t('Loisirs'),
			'L_EMPLOYMENT' 			=> _t('Emploi'),
			'L_SEX' 				=> _t('Sexe'),
			'L_BIRTHDAY'			=> _t('Date de naissance'),
			'L_EDIT_PROFILE_INFO'	=> _t('Editer les informations du profil'),	
			'L_MALE' 				=> _t('Masculin'),
			'L_WOMEN' 				=> _t('Féminin'),
			'NOT_SPECIFY'			=> _t('Ne pas spécifier'),
			'L_USERNAME' 			=> _t('Pseudonyme'),
			'L_TITLE_INFO'			=> _t('Informations générales'),
			'L_PASS'				=> _t('Mot de passe'),
			'L_HELP_PASS'			=> _t('Laissez le champ vide pour ne pas changer le mot de passe'),
			'L_EMAIL'				=> _t('Adresse Email'),
			'L_CONFIRM_PASS'		=> _t('Confirmer mot de passe'),
			'USER_FROM'				=> $user->data['user_from'],
			'USER_MSN'				=> $user->data['user_msn'],
			'USER_YAHOO'			=> $user->data['user_yahoo'],
			'USER_SKYPE'			=> $user->data['user_skype'],
			'USER_FACEBOOK'			=> $user->data['user_facebook'],
			'USER_TWITTER'			=> $user->data['user_twitter'],
			'USER_HOBBIES'			=> $user->data['user_hobbies'],
			'USER_JOB'				=> $user->data['user_job'],
			'USER_SEXE'				=> $user->data['user_sexe'],
			'USER_BIRTHDAY'			=> $user->data['user_birthday'],
			'LANG_DATEPICKER'		=> $config['lang_default']
	   )
	);
}

function timestamp($date) {
	if (preg_match('#([0-9]{4})-([0-9]{2})-([0-9]{2})#', $date, $match)) {
		return mktime(0, 0, 0, $match[2], $match[3], $match[1]);
	}

}


function profile_sig($submit) {
	global $user, $db, $template, $permission, $error, $global, $config;
	
	$post_sig = requestVarPost('user_sig', '');
	$name_post = array('user_sig');
	
	if ($submit) {
		if (strlen($post_sig) > $config['max_char_sign']) 
			$error[] = sprintf(_t('Le texte ne doit pas dépasser %d lettres'), $config['max_char_sign']);
	}
	if ($submit && empty($error)) {
		$etat = $db->updateProfile($name_post, $user);
		$url_retour = makeUrl('profile.php', array('mode' => 'profile_sig'));
		etatSubmitConfirm(true, array(_t('La modification de la signature a bien été effectuée'),_t('La modification de la signature a échoué')) , $url_retour, $etat);
		metaRefresh(3, $url_retour);
	}
	else {
		etatSubmitConfirm(false);
	}
	
	$template->assignVars(array(
			'USER_SIG' 		=> $post_sig == '' ? $user->data['user_sig'] : htmlspecialchars($post_sig),
			'L_EDIT_SIG'	=> _t('Editer la signature')
		
	   )
	);
	
}

function profile_options($submit, $mode) {
	global $user, $db, $template, $permission, $error;
	
	$name_post = array();
	switch($mode) {
		case 'profile_personal':
			$name_post = array('user_allow_contact_pm', 'user_mask_statut', 'new_pm_avert', 'new_pm_popup');
		break;
		case 'profile_post':
			$name_post = array('user_sig', 'user_reply_avert');
		break;
		case 'profile_view':
			$name_post = array('display_img_post', 'display_flash', 'display_smilies_img', 'display_sig', 'display_avatar');
		break;
	}
	
	if ($submit && empty($error)) {
		$success = updateOptions($name_post);
		$url_retour = makeUrl('profile.php', array('mode' => $mode));
		etatSubmitConfirm(true, array(_t('La modification des options a bien été effectuée'), _t('La modification des options a échoué')), $url_retour, $success);
		metaRefresh(3, $url_retour);
	}
	else {
		etatSubmitConfirm(false);
	}
	
	viewProfileOptions();
	$template->assignVars(array(
		'L_EDIT_GLOBAL_SETTING'		=> _t('Editer les réglages globaux'),
		'L_EDIT_POST_SETTING'		=> _t('Editer la publication par défaut'),
		'L_EDIT_DISPLAY_SETTING'	=> _t('Editer les options d\'affichage'),
		
		'L_USER_ALLOW_CONTACT_PM'	=> _t('Autoriser les utilisateurs à vous envoyer des messages privés'),
		'L_USER_MASK_STATUT'		=> _t('Masquer mon statut en ligne'),
		'L_NEW_PM_AVERT'			=> _t('M\'avertir lors de nouveaux messages privés'),
		'L_NEW_PM_POPUP'			=> _t('Afficher une pop-up lors d\'un nouveau message privé'),
		'L_USER_SIG'				=> _t('Insérer ma signature par défaut'),
		'L_USER_REPLY_AVERT'		=> _t('M\'avertir lors des réponses par défaut'),
		'L_DISPLAY_FLASH'			=> _t('Afficher les animations Flash'),
		'L_DISPLAY_SIG'				=> _t('Afficher les signatures'),
		'L_DISPLAY_IMG_POST'		=> _t('Afficher les images dans les messages'),
		'L_DISPLAY_SMILIES_IMG'		=> _t('Afficher les émoticônes comme des images'),
		'L_DISPLAY_AVATAR'			=> _t('Afficher les avatars'),
	));
	
}

function emailExist($value) {
	global $db;
	$data = $db->select(USERS, 'user_email="' . $value . '" AND user_id != ' . ANONYMOUS_ID, null, null, 'user_id', 'one');
	if (isset($data['user_id'])) {
		return false;
	}
	else {
		return true;
	}
}

function profile_forget_pass($submit) {
	global $template, $error, $db, $config;
	$input = array(
		'forget_email'	=>	array(
				'type'		=> 'email',
				'required'	=> _t('Veuillez rentrer une adresse e-mail'),
				'remote'	=> array('emailExist', 'this.value')
			)
	);
	$input_error = array(
		'forget_email'	=>	array(
				'type'		=> _t('L\'adresse email est invalide'),
				'remote'	=> _t('Cette adresse email est inconnue')
			)
	);
	
	$forms = new Forms_Validate($input, $input_error);
	
	$email = $forms->input['forget_email']['value'];
	$template->assignVars(array(
			'EMAIL'				=> $email,
			'L_FORGET_PASS'		=> _t('Oublie du mot de passe'),
			'L_REMEMBER_PASS'	=> _t('Rappeler mon mot de passe'),
			'L_HELP'			=> _t('Si vous avez oublié votre mot de passe, rentrez le champ ci-dessous. Un email vous sera alors envoyé à l\'adresse e-mail indiqué avec un nouveau mot de passe'),
			'L_HELP_EMAIL'		=> _t('Adresse e-mail associée à votre compte. Si vous ne l’avez pas modifiée via votre panneau d’utilisateur, il s’agit de l’adresse que vous avez fournie lors de votre inscription.'),
			'L_EMAIL'			=> _t('Adresse e-mail')
		)
	);
	$error = $forms->isValidate();
	if ($submit && empty($error)) {
		$url_retour = makeUrl('profile.php', array('mode' => 'forget_pass'));
		$new_pass = generateNewPass();
		$b = $db->update(USERS, array('user_password' => md5($new_pass)), 'user_email="' . $email . '"');
		if ($b) {
			Plugins::flag('sendPassword', array(&$new_pass));
			$text = str_replace('{SITE_NAME}', $config['sitename'], $config['forget_pass_text']);
			$text = str_replace('{NEW_PASS}', $new_pass, $text);
			$success = sendEmail($email, $text, $text, sprintf(_t('%s - Nouveau mot de passe'), $config['sitename']));
		}
		else {
			$success = false;
		}
		etatSubmitConfirm(true, array(_t('L\'email a bien été envoyé avec le nouveau mot de passe'), _t('L\'envoie de l\'email a échoué')), $url_retour, $success);
		metaRefresh(3, $url_retour);
	}
	else {
		etatSubmitConfirm(false);
	}
	
	return $error;
}

function updateOptions($name_post) {
	global $user, $db;
	$data_post = dataPost($name_post);
	foreach ($data_post as $name => $value) {
		if ($value && !$user->profileOptions($name))
			$user->addProfileOptions($name);
		elseif(!$value && $user->profileOptions($name))
			$user->removeProfileOptions($name);
	}
	$b = $user->registerProfileOptions();
	$user->updateData();
	return $b;

}

function dataPost($name_post) {
	$data_post = array();
	for ($i=0 ; $i < sizeof($name_post) ; $i++) {
		$value = requestVarPost($name_post[$i], '');
		if (is_array($value)) {
			$data_post[$name_post[$i]] = $value[0];
		}
		else 
			$data_post[$name_post[$i]] = htmlDoubleQuote($value);
	}
	return $data_post;
}

function profile_user_comment($submit, $user_id = null) {
	global $db, $error, $template;
	global $action;
	
	$username = requestVarPost('search_user', '');
	
	$user_comment = requestVarPost('user_comment', '');
	$edit_comment = isset($_POST['edit_comment']);
	etatSubmitConfirm(false);
	
	if ($edit_comment) {
		$user_id = requestVarPost('user_id', 0, 'unsigned int');
		$sql = 'UPDATE ' . USERS . ' SET user_comment="' . htmlspecialchars($user_comment) . '" WHERE user_id=' . $user_id;
		$etat = $db->query($sql);
		$url_retour = makeUrl('profile.php', array('mode' => 'comments', 'u' => $user_id));
		etatSubmitConfirm(true, array(_t('Le commentaire a été enregistré avec succès'), _t('L\'enregistrement du commentaire a échoué')), $url_retour, $etat);
		metaRefresh(3, $url_retour);		
	}
	elseif ($submit || $user_id != null) {
		if ($username != '' || $user_id != null) {
			if ($user_id == null)
				$user_id = searchUser($username);
			
			if ($user_id === false)
				$error[] = _t('Cet utilisateur n\'existe pas');
			else {
				$template->assignVars(array_merge(array(
					 	'COMMENT_RETURN' 		=> makeUrl('profile.php', array('mode' => 'comments'))
						
						
						
					),
					viewprofile(userData($user_id))
				));
				$action = 'user_comment';
			}
		}
		else
			$error[] = _t('Veuillez rentrer un nom d\'utilisateur');
		
	}
	
	$template->assignVars(array(
		'L_SEARCH_MEMBER'		=> _t('Chercher le commentaire d\'un membre'),
		'L_FIND_MEMBER'			=> _t('Trouver un membre'),
		'L_COMMENT_ABOUT'		=> _t('Commentaire sur'),
		'L_GLOBAL_INFO'			=> _t('Informations globales'),
		'L_REGDATE'				=> _t('Inscrit le'),
		'L_LAST_VISIT'			=> _t('Dernière visite'),
		'L_NB_POST'				=> _t('Nombre de messages'),
		'L_COMMENT'				=> _t('Commentaire'),
		'L_EDIT_COMMENT'		=> _t('Editer le commentaire'),
		'L_ACCESS_COMMENT'		=> _t('Accès aux commentaires')
	));
	
}

function profile_user_avert($submit, $user_id = null) {
	global $db, $error, $template, $user, $permission;
	global $action;
	
	$username = requestVarPost('search_user', '');
	$submit_avert = isset($_POST['submit_avert']);
	$time = time();

	etatSubmitConfirm(false);
	
	if ($submit_avert) {
		$mode = requestVarPost('mode', '');
		$user_id = requestVarPost('user_id', 0, 'unsigned int');
		$str = '';
		$etat = false;
		switch($mode) {
			case 'delete':
				$checkbox = requestVarPost('avert_id', array());
				$in = dataCheckBox($checkbox);
				$etat = $db->query('DELETE FROM ' . USERS_AVERTS . ' WHERE avert_id IN (' . $in . ') AND user_id='  . $user_id);
				synchronizedAvert($user_id);
				$str = array(_t('Les avertissements ont bien été supprimés'), _t('La suppression des avertissements ont échoués'));
			break;
			case 'syn':
				$etat = synchronizedAvert($user_id);
				$str = array(_t('La synchronisation a été faite avec succès'), _t('La synchronisation a échoué'));
			break;
			case 'avert':
				$avert_reason = requestVarPost('avert_reason', '');
				$avert_expire = requestVarPost('avert_expire', 0, 'unsigned int');
				$etat = $db->query('INSERT INTO ' . USERS_AVERTS . ' 
				VALUES("", ' . $user_id . ', "' . htmlspecialchars($avert_reason) . '", ' . $time . ' , ' . ($time+(3600*24*$avert_expire)) . ', ' . $user->data['user_id'] . ')');
				incrOrDecNbAvert($user_id, true, 1);
				$str = array(_t('L\'avertissement a bien été donné'), _t('L\'envoi de l\'avertissement a échoué'));
			break;
			case 'ban':
				$ban_reason = requestVarPost('ban_reason', '');
				$ban_expire = requestVarPost('ban_expire', 0, 'unsigned int');
				$result = $db->query('SELECT user_ip, user_email FROM ' . USERS . '
									  WHERE  user_id=' . $user_id);
				$data = $result->fetch_array();
				$etat = $db->query('INSERT INTO ' . USERS_BAN . ' 
				VALUES("", ' . $user_id . ', "' . $data['user_email'] . '", "' . $data['user_ip'] . '", ' . $time . ' , ' . ($time+(3600*24*$ban_expire)) . ', "' . htmlspecialchars($ban_reason) . '",  ' . $user->data['user_id'] . ')');
				incrementeUserBan($user_id);
				$str = array(_t('Cet utilisateur a été banni avec succès'), _t('L\'utilisateur n\'a pas été banni.'));
			break;
			case 'deban':
				$etat = deban($user_id);
				$str = array(_t('Cet utilisateur a été débanni avec succès'), _t('L\'utilisateur n\'a pas été débanni.'));
			break;
		}
	
		$url_retour = makeUrl('profile.php', array('mode' => 'averts', 'u' => $user_id));
		etatSubmitConfirm(true, $str, $url_retour, $etat);
		metaRefresh(3, $url_retour);		
	}
	elseif ($submit || $user_id != null) {
		if ($username != '' || $user_id != null) {
			if ($user_id == null)
				$user_id = searchUser($username);
			if ($user_id === false)
				$error[] = _t('Cet utilisateur n\'existe pas');
			else {
				$template->assignVars(array_merge(array(
					// 'U_CONFIRM_DEL_AVERT'  	=> makeUrl(WINDOWS_CONFIRM . '/confirm_averts.php', array('mode' => 'delete', 'u' => $user_id)),
					// 'U_CONFIRM_AVERT' 		=> makeUrl(WINDOWS_CONFIRM . '/confirm_averts.php', array('mode' => 'avert', 'u' => $user_id)),
					'CAN_USER_BAN'  		=> $user->isCan($permission['users']['user_ban']) && $user_id != FONDATOR_ID,
					// 'U_CONFIRM_BAN'  		=> makeUrl(WINDOWS_CONFIRM . '/confirm_averts.php', array('mode' => 'ban', 'u' => $user_id)),
					// 'U_CONFIRM_SYN'  		=> makeUrl(WINDOWS_CONFIRM . '/confirm_averts.php', array('mode' => 'syn', 'u' => $user_id)),
					// 'U_CONFIRM_DEBAN'  		=> makeUrl(WINDOWS_CONFIRM . '/confirm_averts.php', array('mode' => 'deban', 'u' => $user_id)),
					'U_AVERT_RETURN'  		=> makeUrl('profile.php', array('mode' => 'averts')),
					'U_VIEW_COMMENT_USER'	=> makeUrl('profile.php', array('mode' => 'comments', 'u' => $user_id)),
					
					'L_AVERT_SYN'			=> _t('Etes vous sûr de resynchroniser le nombre d\'avertissements ?'),
					'L_AVERT_SYN_HELP'		=> _t('Note : le nombre d\'avertissement est automatique synchronisé à la connexion de cet utilisateur.'),
					'L_WIN_CONFIRM'			=> _t('Confirmation'),
					'L_AVERT_DELETE'		=> _t('Etes vous sûr de supprimer les avertissements cochés ?'),
					'L_AVERT_DEBAN'			=> _t('Etes vous sûr de débannir cet utilisateur ?'),
					'L_AVERT_BAN'			=> _t('Etes vous sûr de bannir cet utilisateur ?'),
					'L_AVERT_AVERT'			=> _t('Etes vous sûr de lui donner un avertissement ?'),
					'L_EXPIRE'				=> _t('Expire dans'),
					'L_EMPTY_FOR_ILLI'		=> _t('Laissez vide pour une durée illimitée'),
					'L_DAYS'				=> _t('jours'),
					
					'USER_ID'				=> $user_id
					),
					viewprofile(userData($user_id))	
				));
				viewBan($user_id);
				$sql = 'SELECT * FROM ' . USERS_AVERTS . ' WHERE user_id=' . $user_id;
				$result = $db->query($sql);
				while ($row = $result->fetch_array()) {
					$template->assignBlockVars('avert', viewAverts($row));
				}
			
			$action = 'user_avert';
				
			}
		}
		else
			$error[] = _t('Veuillez rentrer un nom d\'utilisateur');
		
	}
	else {
		$sql = 'SELECT ua.user_id, username, group_color, user_avert, avert_date, avert_reason, avert_expire 
				FROM ' . USERS_AVERTS . ' ua, ' . USERS . ' u, ' . GROUPS . ' g 
				WHERE ua.user_id=u.user_id AND u.group_id=g.group_id
				ORDER BY avert_date DESC LIMIT 0, 5';
		$result = $db->query($sql);
		while ($row = $result->fetch_array()) {
			$is_ban = etatBan($row['user_id']);
			$last_avertrow = array(
				'USER_AVERT'			=>  	$row['username'],
				'USER_COLOR'			=>  	$row['group_color'],
				'USER_NB_AVERT'			=>  	$row['user_avert'],
				'AVERT_DATE'			=>  	strTime($row['avert_date'], 8),
				'AVERT_REASON'			=>  	$row['avert_reason'],
				'AVERT_EXPIRE'			=>  	strTime($row['avert_expire'], 8),
				'NO_EXPIRE'				=>  	$row['avert_date'] == $row['avert_expire'],
				'IS_BAN'				=>		isset($is_ban['ban_id']),
				'AVERT_EXPIRED'			=>		$time > $row['avert_expire'],
				'U_EDIT_AVERT'			=>		makeUrl('profile.php', array('mode' => 'averts', 'u' => $row['user_id']))
			);
			$template->assignBlockVars('last_averts', $last_avertrow);
		}
	
	
	}
	
	$template->assignVars(array(
		'L_MANAGE_AVERT'		=> _t('Gestion des avertissements'),
		'L_SEARCH_MEMBER'		=> _t('Chercher un membre'),
		'L_FIND_MEMBER'			=> _t('Trouver un membre'),
		'L_LAST_AVERT'			=> sprintf(_t('Les %d derniers avertissements'), 5),
		'L_USER'				=> _t('Utilisateur'),
		'L_TIME'				=> _t('Date'),
		'L_REASON'				=> _t('Raison'),
		'L_EXPIRE'				=> _t('Expire'),
		'L_GET_NB_AVERT'		=> _t('Nombre d\'avertissement possédé'),
		'L_NULL'				=> _t('Aucune'),
		'L_INDEFINITE'			=> _t('Indéfinie'),
		'L_EXPIRE_SINCE'		=> _t('Expiré depuis le'),
		'L_AVERT_OF'			=> _t('Avertissements de'),
		'L_GLOBAL_SETTING'		=> _t('Informations globales'),
		'L_REGDATE'				=> _t('Inscrit le'),
		'L_LASTVISIT'			=> _t('Dernière visite'),
		'L_NB_POST'				=> _t('Nombre de messages'),
		'L_NB_AVERT'			=> _t('Nombre d\'avertissement'),
		'L_STATUS'				=> _t('Statut'),
		'L_BAN'					=> _t('Banni'),
		'L_TIMES'				=> _t('fois'),
		'L_BAN_BY'				=> _t('Banni par'),
		'L_EXPIRE_THE'			=> _t('Expire le'),
		'L_VIEW_COMMENT'		=> _t('Voir les notes de cet utilisateur'),
		'L_GIVE_AVERT'			=> _t('Donner un avertissement'),
		'L_REMOVE_AVERT'		=> _t('Enlever les avertissements cochés'),
		'L_TO_BAN'				=> _t('Bannir'),
		'L_TO_DEBAN'			=> _t('Débannir'),
		'L_SYNCHRO'				=> _t('Resynchroniser le nombre d\'avertissement'),
		'L_GIVE_BY'				=> _t('Donné par'),
		'L_CHECK'				=> _t('Cocher'),
		'L_ACCESS_AVERT'		=> _t('Accès aux avertissements'),
		'L_INFO_OF_BAN'			=> _t('Information sur exclusion du membre')
	));		
	
}

function viewprofile($data) {
	 global $db, $user, $permission, $config;
	 
	 $str_activ_reason = $data['user_activ_reason'];
	 switch($data['user_activ_reason']) {
	 	case 0: 
	 		$str_activ_reason = _t('Le compte est nouvellement inscrit');
	 	break;
	 	case 1: 
	 		$str_activ_reason = _t('Le compte a été désactivé par un administrateur');
	 	break;
	 }
	 return array(
	 		'PROFILE_USER_ACTIV'			 => $data['user_activ'] == 1,
	 		'PROFILE_USER_ACTIV_REASON'		 => $str_activ_reason,
			'PROFILE_USERNAME'				 => $data['username'],
	 		'PROFILE_USER_MAIL'				 => $data['user_email'],
	 		'PROFILE_IS_FONDATOR'			 => $data['user_id'] == FONDATOR_ID,
	 		'PROFILE_IS_ANONYMOUS'			 => $data['user_id'] == ANONYMOUS_ID,
			'PROFILE_USER_ID'				 => $data['user_id'],
	 		'PROFILE_USER_IP'				 => $data['user_ip'],
			'PROFILE_USER_COLOR'			 => $data['group_color'],
	 		'PROFILE_GROUP_MANAGE'			 => $data['user_id'] == $data['group_founder_manage'],
			'PROFILE_GROUP_NAME'			 => $data['group_name'],
			'PROFILE_USER_JOB'				 => $data['user_job'],
			'PROFILE_USER_FROM'				 => $data['user_from'],
			'PROFILE_USER_WEBSITE'			 => $data['user_website'],
			'PROFILE_USER_HOBBIES'			 => $data['user_hobbies'],
			'PROFILE_USER_SEXE'				 => $data['user_sexe'] == 'i' ? _t('Non spécifié') : ($data['user_sexe'] == 'm' ? _t('Homme') : _t('Femme')),
			'PROFILE_USER_BIRTHDAY'			 => $data['user_birthday'],
			'PROFILE_USER_REGDATE'			 => strTime($data['user_regdate'], 8),
			'PROFILE_USER_LASTVISIT'		 => strTime($data['user_lastvisit'], 8),
			'PROFILE_USER_AVERT'			 => $data['user_avert'],
			'PROFILE_USER_BAN'			 	 => $data['user_ban'],
			'PROFILE_USER_NB_MESSAGE'		 => $data['user_nb_message'],
			'PROFILE_USER_SIG'		 		 => $data['user_sig'] == '' ? 'Aucune' : replaceOption(htmlDoubleQuoteRev($data['user_sig'])),
			'PROFILE_USER_AVATAR'		 	 => $data['user_avatar'] == '' ? image('avatar_default') : PATH_AVATAR . '/' . $data['user_avatar'],
			'PROFILE_USER_COMMENT'		 	 => $data['user_comment'] == '' ? 'Aucun' : nl2br($data['user_comment']),
	 		'PROFILE_USER_COMMENT_EDIT'		 => $data['user_comment'],
			'PROFILE_USER_EDIT_COMMENT'		 => $data['user_comment'],
			'PROFILE_USER_READ_COMMENT'		 => $user->isCan($permission['users']['user_read_comment']),
			'PROFILE_USER_YAHOO'		 	 => $data['user_yahoo'],
			'PROFILE_USER_MSN'		 	 	 => $data['user_msn'],
			'PROFILE_USER_SKYPE'		 	 => $data['user_skype'],
			'PROFILE_USER_TWITTER'		 	 => $data['user_twitter'],
			'PROFILE_USER_FACEBOOK'		 	 => $data['user_facebook'],
	 		'PROFILE_OPTION_SIG'		 	 => profileOptions($data, 'user_sig') && $user->profileOptions('display_sig'),
			'PROFILE_USER_PM'		 	 	 => makeUrl('profile.php', array('mode' => 'pm', 'i' => 'new', 'u' => $data['user_id'])),
	 		'U_VIEWPROFILE'					 => makeUrl('memberlist.php', array('mode' => 'viewprofile', 'u' => $data['user_id']))
		);
}

function viewProfileOptions($user_option = null) {
	global $template, $user;
	$options = array(
			'USER_ALLOW_CONTACT_EMAIL' 			=> 'user_allow_contact_email',
			'USER_ALLOW_CONTACT_PM' 			=> 'user_allow_contact_pm',
			'USER_MASK_STATUT'					=> 'user_mask_statut',
			'NEW_PM_AVERT' 						=> 'new_pm_avert',
			'NEW_PM_POPUP' 						=> 'new_pm_popup',
			'USER_SIG'							=> 'user_sig',
			'USER_REPLY_AVERT' 					=> 'user_reply_avert',
			'DISPLAY_IMG_POST' 					=> 'display_img_post',
			'DISPLAY_FLASH'						=> 'display_flash',
			'DISPLAY_SMILIES_IMG'				=> 'display_smilies_img',
			'DISPLAY_SIG'						=> 'display_sig',
			'DISPLAY_AVATAR'					=> 'display_avatar'
	);
	
	foreach ($options as $key => $value) {
		if (isset($user_option)) {
			$options[$key] = profileOptionsUser($value, $user_option);
		}
		else {
			$options[$key] = $user->profileOptions($value);
		}
	}
	$template->assignVars($options);
}

function profileOptionsUser($label_option, $user_option) {
	global $user;
	return ($user->getOption($label_option) & hexdec($user_option)) == $user->getOption($label_option);
}

function userProfileOptions($user_id) {
	global $db;
	$result = $db->query('SELECT user_options FROM ' . USERS . ' WHERE user_id=' . $user_id);
	$data = $result->fetch_array();

	return array(
		'OPTION_USER_SIG' => profileOptions($data, 'user_sig')
	);
}

function profileOptions($user_option, $label_option) {
	global $user;
	return ($user->getOption($label_option) & hexdec($user_option['user_options'])) == $user->getOption($label_option);
}

// incrémente ou décremente le nombre d'avertissement
function incrOrDecNbAvert($user_id, $etat, $nb) {
	global $db;
	$result = $db->query('SELECT user_avert FROM ' . USERS . ' 
						   WHERE user_id=' . $user_id);
	$data = $result->fetch_array();
	$new_avert = $etat ? $data['user_avert'] + $nb : $data['user_avert'] - $nb;
	return $db->query('UPDATE ' . USERS . ' SET user_avert=' . $new_avert . ' 
					   WHERE user_id=' . $user_id);
}

function viewAverts($row) {
	$data = userData($row['avert_give_user_id']);
	return array(
		'AVERT_ID'				=>  $row['avert_id'],
		'AVERT_REASON'			=>  $row['avert_reason'],
		'AVERT_DATE'			=>  strTime($row['avert_date'], 8),
		'AVERT_EXPIRE'			=>  strTime($row['avert_expire'], 8),
		'AVERT_REPORT_USERNAME'	=>  $data['username'],
		'AVERT_REPORT_COLOR'	=>  $data['group_color'],
		'NO_EXPIRE'				=>  $row['avert_date'] == $row['avert_expire'],
		'AVERT_EXPIRED'			=>  time() > $row['avert_expire']
	);
}

function etatBan($user_id) {
	global $db;
	$result = $db->query('SELECT * FROM ' . USERS_BAN . ' 
						  WHERE ban_user_id=' . $user_id);
	return $row = $result->fetch_array();
}

function viewBan($user_id) {
	global $template;
	$row = etatBan($user_id);
	$array_data = array(
		'USER_BAN'				=>  isset($row['ban_id'])
	);
	if (isset($row['ban_id'])) {
		$data = userData($row['ban_give_user_id']);
		$array_data = array_merge($array_data, array(
			'BAN_REASON'			=>  $row['ban_reason'],
			'BAN_DATE'				=>  strTime($row['ban_date'], 8),
			'BAN_EXPIRE'			=>  strTime($row['ban_expire'], 8),
			'BAN_REPORT_USERNAME'	=>  $data['username'],
			'BAN_REPORT_COLOR'		=>  $data['group_color'],
			'NO_EXPIRE'				=>  $row['ban_date'] == $row['ban_expire']
		));
		
		if (time() > $row['ban_expire'] && $row['ban_date'] != $row['ban_expire'])
			deban($user_id);
	}
	$template->assignVars($array_data);
}

function deban($user_id) {
	global $db;
	return $db->query('DELETE FROM ' . USERS_BAN . ' WHERE ban_user_id='  . $user_id);
}

function incrementeUserBan($user_id) {
	global $db;
	$result = $db->query('SELECT user_ban FROM ' . USERS . ' 
						   WHERE user_id=' . $user_id);
	$data = $result->fetch_array();
	return $db->query('UPDATE ' . USERS . ' SET user_ban=' . ($data['user_ban']+1) . ' 
					   WHERE user_id=' . $user_id);
}



function reportsData($where = null, $return_result = false) {
	global $db;
	$sql = 'SELECT * FROM ' . REPORTS . ' re, ' . REPORTS_REASONS . ' rr, ' . USERS . ' s, ' . POSTS . ' p 
			WHERE re.user_id = s.user_id AND p.post_id = re.post_id AND re.reason_id = rr.reason_id '  . ($where != null ? 'AND ' . $where : '');
	$result = $db->query($sql);
	if ($return_result)
		return $result;

	return $data = $result->fetch_array();
}

function viewReports($data) {
	return array(
		'REPORT_ID'						=>	$data['report_id'],
		'REASON_DESC'					=>	$data['reason_text'],
		'REPORT_CLOSED'					=>	$data['report_closed'] == 1,
		'REPORT_TIME'					=>	strTime($data['report_time'], 8),
		'REPORT_CLOSE_TIME'				=>  sprintf(_t('le %s'), strTime($data['report_close_time'], 8)),
		'REPORT_TEXT'					=>	$data['report_text']  != '' ? $data['report_text'] : 'Aucune autres informations',
		'U_READ_REPORT'					=>  makeUrl('report.php', array('mode' => 'read', 'r' => $data['report_id'])),
		'U_VIEWPOST_REPORT'				=>  makeUrl('viewtopic.php', array('f' => $data['forum_id'], 't' => $data['topic_id'])) . '#p' . $data['post_id'],
		'REPORT_USERNAME'				=>	sprintf(_t('Envoyé par %s'), $data['username']),
		'REPORT_POST_TIME'				=>	sprintf(_t('posté le %s'), strTime($data['post_time'], 8)),
		'REPORT_POST_SUBJECT'			=>	$data['post_subject'],
		'L_HAS_REPORT'					=> _t('Ce message a été rapporté'),
		'L_REPORT_CLOSED'				=> _t('Ce message a été rapporté et traité')
	);
}




function synchronizedAvert($user_id) {
	global $db;
	if ($user_id != null) {
		$result = $db->query('SELECT COUNT(*) AS nb_avert FROM ' . USERS_AVERTS . ' 
							  WHERE ((avert_date != avert_expire AND avert_expire > ' . time() . ') OR avert_date = avert_expire) AND user_id=' . $user_id);
		$data = $result->fetch_array();
		return $db->query('UPDATE ' . USERS . ' SET user_avert=' . $data['nb_avert'] . ' 
						   WHERE user_id=' . $user_id);
	}
}

function profile_post_details($post_id) {
	global $db, $template;
	$data = $db->select(POSTS . ' p,' . USERS . ' u', 'p.poster_id=u.user_id AND post_id=' . $post_id, null, null, '*', 'one');
	$nb_msg = $db->select(POSTS, 'poster_ip="' . $data['poster_ip'] . '" AND poster_id = ' . $data['poster_id'], null, null, 'COUNT(*) AS this_ip', 'one');
	$template->assignVars(array(
		'L_WHO_HAS_POSTED' 	=> sprintf(_t('%s a posté ce message le %s avec l\'adresse IP "%s"'), $data['username'], strTime($data['post_time'], 8), $data['poster_ip']),
		'L_POST_ID'			=> sprintf(_t('Identifiant du message : %d'), $data['post_id']),
		'L_NB_MSG_THIS_IP'	=> sprintf(_t('%s a posté %d  sur %d message(s) à partir de cette adresse IP'),  $data['username'], $data['user_nb_message'], $nb_msg['this_ip']),
		'L_USERNAME'		=> _t('Nom d\'utilisateur'),
		'L_POST_SUBJECT'	=> _t('Sujet du message'),
		'L_POST_TIME'		=> _t('Date du message')
	));
	//$template->assignVars(viewprofile($data));
	$result = $db->select(POSTS . ' p,' . USERS . ' u', 'p.poster_id=u.user_id AND poster_ip="' . $data['poster_ip'] .'" AND poster_id != ' . $data['poster_id'], 'post_time DESC' , null, '*');
	while ($data_other = $result->fetch_array()) {
		$row = array(
			'POST_TIME'			=> strTime($data_other['post_time'], 8),
			'POST_SUBJECT'		=> $data_other['post_subject'],
			'U_POST'			=> makeUrl('viewtopic.php', array('f' => $data_other['forum_id'], 't' => $data_other['topic_id'])) . '#p' . $data_other['post_id']
		
		);
		$template->assignBlockVars('other_poster', array_merge($row, viewprofile($data_other)));
	}
}


?>