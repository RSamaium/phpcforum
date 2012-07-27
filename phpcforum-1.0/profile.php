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
require_once('includes/class/upload.class.php');


$mode = requestVar('mode', '');
$ajax = requestVar('aj', 0);

$array_mode = array('avatar', 'profile_info', 'profile_sig', 'profile_personal', 'profile_post', 'profile_view');
$submit_post = isset($_POST['submit_profile']);
$submit_delete = isset($_POST['submit_delete_pm']);

//$error = false;
$error = array();
$permission_error = false;
$txt_error = '';

switch($mode) {
	case 'register':
		if ($user->isRegister()) {
			redirect(makeUrl('profile.php'));
		}
		else {
			profile_register($submit_post);
			$template->addJs('js', array(
				'jquery.validate.min'
			));
		}
	break;
	case 'pm':
		if (!$user->isRegister())
			$mode = 'login';
		else {
			
			$action = requestVar('i', '');
			$pm_id = requestVar('p', 0, 'unsigned int');
			$pm_autorized =  $db->pmReadAutorized($pm_id,  $user->data['user_id'], $action);
			$pm_read_autorized = !empty($pm_autorized['pm_id']);
			switch($action) {
				case 'read_send':
				case 'read':
					if ($pm_id > 0 && !$pm_read_autorized) {
						$pm_id = null;
						$action = 'list';
					}
					if ($user->isCan($permission['users']['pm_read']))
						profile_pm($submit_post, $pm_id, $action);
					else {
						$permission_error = true;
						$txt_error = _t('Vous n\'avez pas le droit de lire les messages privés');
					}
				break;
				case 'new':
						if ($user->isCan($permission['users']['pm_send'])) {
							$list_error = profile_send_pm($submit_post, $action);
							$template->addJs('js', array(
								'ckeditor/ckeditor'
							));
						}
						else {
							$permission_error = true;
							$txt_error = _t('Vous n\'avez pas le droit d\'envoyer de message privé');
						}
				break;
				case 'edit':
					if ($pm_id > 0 && $pm_read_autorized) {
						if ($user->isCan($permission['users']['pm_edit'])) {
							$list_error = profile_send_pm($submit_post, $action, $pm_id);
							$template->addJs('js', array(
								'ckeditor/ckeditor'
							));
						}
						else {
							$permission_error = true;
							$txt_error = _t('Vous n\'avez pas le droit d\'éditer ce message privé');
						}
					}
					else
						$action = 'list';
					
				break;
				case 'reply':
					if ($pm_id > 0 && $pm_read_autorized) {
						if ($user->isCan($permission['users']['pm_send'])) {
							$list_error = profile_send_pm($submit_post, $action, $pm_id);
							$action = 'reply';
							$template->addJs('js', array(
								'ckeditor/ckeditor'
							));
						}
						else {
							$permission_error = true;
							$txt_error = _t('Vous n\'avez pas le droit d\'envoyer de message privé');
						}
					}
					else
						$action = 'list';
					
				break;
				case 'send':
				break;
				default:
					$action = 'list';
				break;
			}
				if ($action == 'list' || $action == 'send') {
					profile_pm($submit_post, null, $action);
					$template->addJs('js', array(
						'jquery.quicksand',
						'jquery.easing.1.3',
						'pm'
					));
					$template->assignVars(array_merge(displayGlobaleTextHeader(), array(
						 'L_DELETE_PM'					=> _t('Supprimer les messages privés cochés'),
						 'L_CONFIRM_TITLE'				=> _t('Confirmation'),
						 'L_CONFIRM_DEL'				=> _t('Etes vous sûr de supprimer les messages privés suivant'),
						 'L_CONFIRM_DEL_NO_CHECKED'		=> _t('Veuillez cocher un ou plusieurs messages privés'),
						 'L_VIEW_SEND_PM'				=> _t('Voir les messages envoyés'),
						 'U_VIEW_SEND_PM'				=> makeUrl('profile.php', array('mode' => 'pm', 'i' => 'send')),
						 'L_VIEW_RECEIVE_PM'			=> _t('Voir les messages reçus'),
						 'U_VIEW_RECEIVE_PM'			=> makeUrl('profile.php', array('mode' => 'pm', 'i' => 'list'))
					)));
					
					$tab_pm = requestVarPost('pm', array());
					optionDeletePm($submit_delete, $tab_pm, $action);
					
				}
				$template->assignVars(array(
				 'LIST_PM' 				=> $action == 'list' || $action == 'send',
				 'VIEW_SEND_PM'			=> $action == 'send',
				 'READ_PM' 				=> $action == 'read' || $action == 'read_send',
				 'READ_SEND_PM' 		=> $action == 'read_send',
				 'SEND_PM' 				=> $action == 'new' || $action == 'reply',
				 'REPLY_PM'				=> $action == 'reply',
				 'NOT_PERMISSION'		=> $permission_error,
				 'L_INFO'				=> _t('Information'),
				 'TXT_NOT_PERMISSION'	=> $txt_error
				)
			);
		}
	break;
	case 'dc':
		logout();
	break;
	case 'forget_pass':
		if (!$user->isRegister()) {
			profile_forget_pass($submit_post);
		}
	break;
	case 'post_details':
		$forum_id = requestVar('f', 0, 'unsigned int');
		$post_id = requestVar('p', 0, 'unsigned int');
		if (idIsExist($forum_id, 'forum')) {
			if (idIsExist($post_id, 'post')) {
				if ($user->isCanForum($permission['forum']['modo_msg_information'], $forum_id)) {
					profile_post_details($post_id);
				}
				else {
					$permission_error = _t('Vous n\'avez pas la permission d\'accéder au information du message');
				}
			}
			else {
				$permission_error = _t('Ce message n\'existe pas');
			}
		}
		else {
			$permission_error = _t('Ce forum n\'existe pas');
		}
			
		$template->assignVars(array(
			'L_POST_DETAILS'	=> _t('Information sur le message'),
			'L_TITLE_ERROR'		=> _t('Accès au information du message'),
			'L_TITLE_OTHER_IP'	=> _t('Autres utilisateurs ayant postés avec cette IP'),
			'L_TITLE_INFO_IP'	=> _t('Information sur l\'IP de ce message'),
			'PERMISSION'		=> $permission_error
		));
		
	break;
	case 'comments':
	if (!$user->isRegister())
			$mode = 'login';
	else {
		$user_id = requestVar('u', 0, 'unsigned int');
		$action = 'search_user';
		
		if (idIsExist($user_id, 'user'))
			$action = 'comment_user';
			
		if ($user->isCan($permission['users']['user_read_comment'])) {
			switch($action) {
				case 'search_user':
					profile_user_comment($submit_post);
				break;
				case 'comment_user':
					profile_user_comment($submit_post, $user_id);
				break;
			}
		}
		else
			$permission_error = _t('Vous n\'avez pas la permission de lire les commentaires sur les utilisateurs');
			
		$template->assignVars(array(
					'MODE' 				=> $action,
					'PERMISSION'		=> $permission_error
		));
	}
	break;
	case 'averts':
	if (!$user->isRegister())
			$mode = 'login';
	else {
		$user_id = requestVar('u', 0, 'unsigned int');
		$action = 'search_user';
		if (idIsExist($user_id, 'user'))
			$action = 'user_avert';
		
		if ($user->isCan($permission['users']['user_avert'])) {
			switch($action) {
				case 'search_user':
					profile_user_avert($submit_post);
				break;
				case 'user_avert':
					profile_user_avert($submit_post, $user_id);				
				break;
			}
		}
		else
			$permission_error = _t('Vous n\'avez pas la permission de voir les avertissements données');
			
		$template->assignVars(array(
					'MODE' 				=> $action,
					'PERMISSION'		=> $permission_error
		));
	}
	break;
	default:
		if (!$user->isRegister())
			$mode = 'login';
		else {
			$modo_tools = $user->isCan($permission['users']['read_reports']) || 
						  $user->isCan($permission['users']['user_read_comment']) || 
						  $user->isCan($permission['users']['user_avert']);
			$template->assignVars(array(
				'L_YOUR_PROFILE'				=> _t('Votre profil'),
				'L_PROFILE'						=> _t('Profil'),
				'L_PROFILE_PREFERENCE'			=> _t('Préférence du forum'),
				'L_MODO_TOOLS'					=> _t('Outils de modération'),
				'L_ADMINISTRATION'				=> _t('Administration'),
			
				'L_DESC_AVATAR'					=> _t('Les avatars sont généralement de petites images uniques qu\'un utilisateur peut associer à sa personnalité.'),
				'L_DESC_SIG'					=> _t('La signature est un petit texte qui sera ajouté à tous les messages que vous rédigez.'),
				'L_DESC_INFO'					=> _t('Renseigner vos informations (e-mail, votre compte Facebook, changer votre mot de passe ...)'),
				'L_DESC_PERSONAL'				=> _t('Différents réglages concernant votre profil (masquer votre statut, avertir lors d\'un nouveau message privé...)'),
				'L_DESC_POST'					=> _t('Paramétrez les options de publication d\'un message'),
				'L_DESC_VIEW'					=> _t('Décidez de l\'affichage ou non de certain élément du forum.'),
				'L_DESC_REPORT'					=> _t('Liste des rapports envoyés par les membres.'),
				'L_DESC_COMMENT'				=> _t('Mettre un commentaire sur un membre vous permettra de rappelez de l\'attitude du membre à vous et votre équipe de modération'),
				'L_DESC_AVERT'					=> _t('Donnez des avertissements aux membres ou bannisez dans des cas plus grave !'),
				'L_DESC_ADMIN'					=> _t('Accédez au panneau d\'administration et configurez le forum. Changez son design, installez des extensions, ajoutez des forums...'),
				'PROFILE_MODO' 					=> $modo_tools
				)
			);
			if (!in_array($mode, $array_mode))
				$mode = 'body';
		}
	break;
}

if (in_array($mode, $array_mode)) {
	 $permission_error = '';
	if ($user->isRegister()) {
		$template->assignVars(array(
			 'MODE' 				=> $mode 
			)
		);
		switch($mode) {
			case 'avatar': 
				if ($user->isCan($permission['users']['profile_sig'])) {
					profile_avatar($submit_post); 
					$colspan = 2;
				}
				else {
					$permission_error = _t('Vous n\'avez pas la permission de changer votre avatar');
				}
			break;
			case 'profile_info': 
				profile_info($submit_post, $ajax); 
				$template->addJs('js', array(
					'js/i18n/jquery.ui.datepicker-' . $config['lang_default']
				), true);
				$template->addJs('js', array(
					'jquery.ui.core.min',
					'jquery.ui.datepicker.min'
				));
				$template->addCss('css', array(
					'jquery-ui-datepicker'
				));
				$colspan = 2;
			break;
			case 'profile_sig': 
				if ($user->isCan($permission['users']['profile_sig'])) {
					profile_sig($submit_post); 
					$colspan = 1;
					$template->addJs('js', array(
						'ckeditor/ckeditor'
					));
				}
				else {
					$permission_error = _t('Vous n\'avez pas la permission de changer votre signature');
				}	
			break;
			case 'profile_personal': 
				profile_options($submit_post, $mode); 
				$colspan = 2;
			break;
			case 'profile_view': 
				profile_options($submit_post, $mode); 
				$colspan = 2;
			break;
			case 'profile_post': 
				profile_options($submit_post, $mode); 
				$colspan = 2;
			break;
			case 'profile_global': 
				
				profile_avatar($submit_post); 
				profile_info($submit_post, $ajax); 
				profile_sig($submit_post); 
				profile_options($submit_post, 'profile_personal'); 
				profile_options($submit_post, 'profile_view'); 
				profile_options($submit_post, 'profile_post'); 
			break;
		}
		$mode = 'main';
	}
	else
		$mode = 'login';
		
		
		
	$template->assignVars(array(
		 'COLSPAN' 			=> $colspan,
		 'PERMISSION'		=> $permission_error,
		 'L_ACCESS_PROFILE'	=> _t('Accès au profil')
		)
	);
}



if ($mode == 'login') {
	if (!$user->isRegister())
			login();
		
		$template->assignVars(array(
			'USER_NOT_REGISTER' 		=> !$user->isRegister(),
			'TXT_USER_NOT_REGISTER' 	=> _t('Vous êtes déjà connecté'),
			'L_CONNECT'					=> _t('Connexion')
			)
		);
}

$template->assignVars(displayGlobaleButtons());

displayError();

pageHeader($template, 'Index');
$template->setTemplate('profile_' . $mode . '.html');
?> 
