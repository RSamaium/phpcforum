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
include('../includes/libs/pclzip.lib.php');

define('ICON_WIDTH', 100);
define('ICON_HEIGHT', 100);

function login_adm() {
	global $db, $user, $template, $permission;
	$submit = isset($_POST['admin_submit']);
	$username = requestVarPost('admin_login', $user->data['username']);
	$password = requestVarPost('admin_password', '');
	$error = '';
	if ($username != '' && $password != '') {
			$sql = 'SELECT user_id, group_id FROM ' . USERS . ' 
					WHERE username="' . htmlspecialchars($username) . '" AND user_password="' . md5($password) . '"';
			$result = $db->query($sql);
			$data = $result->fetch_array();
			if (isset($data['user_id'])) {
				$_SESSION['adm_user_ip'] = $_SERVER['REMOTE_ADDR'];
				addLog('LOG_ADMIN_AUTH_SUCCESS');
				redirect('.');
			}
			else
			  $error = _t('Identifiant ou mot de passe incorrect');
	}
	elseif ($submit) {
		$error = _t('Veuillez remplir les champs ci-dessous pour accéder au panneau d\'administration');
	}
	if ($submit && $error != '') {
		addLog('LOG_ADMIN_AUTH_FAIL');
	}
	$template->assignVars(array(
			'ADM_LOGIN'			=> $username,
			'ADM_PASSWORD'		=> $password,
			'ERROR'				=> $error,
			'L_CONNECT_TITLE'			=> _t('Connexion à l\'administration du forum'),
			'L_CONNECT_HELP'			=> _t('Veuillez utiliser un identifiant et un mot de passe valide pour accéder à l\'administration.'),
			'L_CONNECTION'				=> _t('Connexion'),
			'L_USERNAME'				=> _t('Identifiant'),
			'L_PASSWORD'				=> _t('Mot de passe'),
			'L_CONNECT'					=> _t('Se connecter')
		)
	);
}

function userIsAdmin() {
	global $user, $permission;
	if ($user->data['group_id'] == GROUP_ADMIN_ID || $user->isAdmin($permission))
		return true;
	redirect('../');
}



function panelUsers() {
	global $template;
	$template->assignVars(array(
		'L_CONFIRM_DELETE_INACTIVE'	=> _t('Etes vous sûr de vouloir supprimer ces comptes inactifs ?'),
		'L_USERS'					=> _t('Utilisateurs'),
		'L_MANAGE_USERS'			=> _t('Gérer utilisateur'),
		'L_INACTIVE'				=> _t('Inactifs'),
		'L_BAN'						=> _t('Bannis'),
		'L_AVERT'					=> _t('Avertissements'),
		'L_USERS_HELP'				=> _t('Vous pouvez modifier ici les informations de vos utilisateurs ainsi que certaines options qui leur sont spécifiques.'),
		'L_VALID'					=> _t('Valider'),
		'L_INACTIVE_HELP'			=> _t('Ceci est la liste des utilisateurs qui se sont inscrits mais dont le compte est actuellement inactif. Si vous le souhaitez, vous pouvez activer, supprimer ces utilisateurs.'),
		'L_USER'					=> _t('Utilisateur'),
		'L_REGISTER_DATE'			=> _t('Date d\'enregistrement'),
		'L_REASON'					=> _t('Raison'),
		'L_LAST_VISIT'				=> _t('Dernière visite'),
		'L_CHECK'					=> _t('Cocher'),
		'L_ACTIV_INACTIVE_BTN'		=> _t('Activer les comptes cochés'),
		'L_DELETE_INACTIVE_BTN'		=> _t('Supprimer les comptes cochés'),
		'L_BAN_TO'					=> _t('Banni le'),
		'L_EXPIRE_TO'				=> _t('Expire le'),
		'L_INTERACTION'				=> _t('Interaction'),
		'L_ADMIN'					=> _t('Administrer'),
		'L_NB_AVERT'				=> _t('Nombre d\'avertissement possédé'),
		'L_AVERT_TO'				=> _t('Averti le'),
		'L_DELETE_INACTIVE'			=> _t('Les comptes des utilisateurs inactifs ont bien été supprimés'),
		'L_NOT_DELETE_INACTIVE'		=> _t('La suppression des comptes des utilisateurs inactifs a échoué'),
		'L_ACTIV_INACTIVE'			=> _t('Les comptes des utilisateurs inactifs ont bien été activés'),
		'L_NOT_ACTIV_INACTIVE'		=> _t('L\'activation des comptes des utilisateurs inactifs a échoué')
	));
}

function panelForumCrud($mode, $submit = false) {
	global $db, $template, $config;
	
	$nb_iconset = 0;
	
	$forum_id = requestVar('forum_id', '');
	if ($forum_id == '') {
		$forum_id = requestVarPost('forum_id', 0);
	}
	
	
	$input = array(
		'forum_name' => array(
			'type'		=>	'string'	
		),
		'forum_desc'	=> array(
			'type'		=>	'string'	
		),
		'forum_rules'	=> array(
			'type'		=>	'string'	
		),
		'forum_share_facebook'	=> array(
			'type'		=>	'string'	
		),
		'forum_share_twitter'	=> array(
			'type'		=>	'string'	
		),
		'forum_image'	=> array(
			'type'		=>	'string'	
		),
		'forum_status' => array(
			'type'		=>	'string'	
		),
		'iconset_id' => array(
			'type'		=>	'string'	
		),
		'forum_icon_mandatory'	=> array(
			'type'		=>	'string'	
		)
	);
	

	$where = 'forum_id=' . $forum_id;
	$data = $db->select(FORUMS, $where, null, null, '*', 'one');
	
	$b = completeFields($input, $submit, array('table' => FORUMS, 'where' => $where, 'fetch' => $data), 'edit');
	echo $b;
	
	$result = $db->select(ICONSET);
	while ($data_icon = $result->fetch_assoc()) {
		$row = array(
			'NAME' 				=>  $data_icon['iconset_name'],
			'ID'				=>	$data_icon['iconset_id'],
			'SELECTED'			=>  $data['iconset_id'] == $data_icon['iconset_id'],
		);
		$template->assignBlockVars('iconset', $row);
		$nb_iconset++;
	}
	
	$template->assignVars(array(
		'L_FORUM_NAME'				=> _t('Nom du forum'),
		'L_FORUM_DESC'				=> _t('Description du forum'),
		'L_FORUM_RULES'				=> _t('Règle du forum'),
		'L_FORUM_STATUS'			=> _t('Statut du forum'),
		'L_FORUM_SHARE_FACEBOOK'	=> _t('Permettre de partager les sujets sur Facebook'),
		'L_FORUM_SHARE_TWITTER'		=> _t('Permettre de partager les sujets sur Twitter'),
		'L_FORUM_IMAGE'				=> _t('Image du forum'),
		'L_YES'						=> _t('Oui'),
		'L_NO'						=> _t('Non'),
		'L_DEFAULT'					=> _t('Défaut'),
		'L_LOCKED'					=> _t('Vérouillé'),
		'L_TAB_EDIT'				=> _t('Editer le forum'),
		'L_TAB_ADD'					=> _t('Ajouter un forum'),
		'L_OPEN'					=> _t('Ouvert'),
		'L_BACK'					=> _t('Retour'),
		'L_VALID'					=> _t('Valider'),
		'L_SETTING_FORUM'			=> _t('Réglage du forum'),
		'L_SETTING_ICON'			=> _t('Réglage des icônes du forum'),
		'L_ICONSET'					=> _t('Ensemble d\'icônes'),
		'L_NULL'					=> _t('Aucune'),
		'L_ICONSET_MANDATORY'		=> _t('Icône obligatoire'),

		'L_FORUM_CREATE'			=> _t('Création du nouveau forum réussi'),
		'L_FORUM_NOT_CREATE'		=> _t('La création du nouveau forum a échoué'),
		'L_FORUM_EDIT'				=> _t('Le forum a été édité avec succès'),
		'L_FORUM_NOT_EDIT'			=> _t('L\'édition du forum a échoué'),
		
		'MODE'						=> $mode,
		'FORUM_ID'					=> $forum_id,
		'NB_ICONSET'				=> $nb_iconset

	));
	

}

function panelGroupCrud($mode, $submit = false) {
	global $db, $template;
	
	$group_id = requestVar('group_id', '');
	if ($group_id == '') {
		$group_id = requestVarPost('group_id', 0);
	}
	
	if ($submit) {
		$group_founder_manage = requestVarPost('group_founder_manage', '');
		$group_manage = $db->select(USERS, 'username="' . $group_founder_manage . '"', null, null, 'user_id', 'one');
		$group_manage = $group_manage['user_id'];
	}
	else {
		$group_manage = $db->select(USERS . ' u,' . GROUPS . ' g', 'u.user_id=g.group_founder_manage AND g.group_id=' . $group_id , null, null, 'username', 'one');
		$group_manage = $group_manage['username'];
	}
	
	$input = array(
		'group_name' => array(
			'type'		=>	'string'	
		),
		'group_desc'	=> array(
			'type'		=>	'string'	
		),
		'group_type'	=> array(
			'type'		=>	'string'	
		),
		'group_founder_manage'	=> array(
			'type'		=>	'string',
			'const'		=> 	$group_manage
		),
		'group_color'	=> array(
			'type'		=>	'string'	
		)
	);
	
	$where = 'group_id=' . $group_id;
	$data = $db->select(GROUPS, $where, null, null, '*', 'one');
	
	$b = completeFields($input, $submit, array('table' => GROUPS, 'where' => $where, 'fetch' => $data), $mode);
	echo $b;
	
	$template->assignVars(array(
		'L_GROUP_NAME'				=> _t('Nom du groupe'),
		'L_GROUP_DESC'				=> _t('Description du groupe'),
		'L_GROUP_TYPE'				=> _t('Etat'),
		'L_GROUP_FOUNDER_MANAGE'	=> _t('Responsable du groupe'),
		'L_GROUP_COLOR'				=> _t('Couleur'),
		'L_CLOSE'					=> _t('Fermé'),
		'L_OPEN_WAIT'				=> _t('Ouvert avec attente d\'approbation'),
		'L_OPEN'					=> _t('Ouvert'),
		'L_TAB_EDIT'				=> _t('Editer le groupe'),
		'L_TAB_ADD'					=> _t('Ajouter un groupe'),
		'L_BACK'					=> _t('Retour'),
		'L_VALID'					=> _t('Valider'),
		
		'L_GROUP_CREATE'			=> _t('Création du nouveau groupe réussi'),
		'L_GROUP_NOT_CREATE'		=> _t('La création du nouveau groupe a échoué'),
		'L_GROUP_EDIT'				=> _t('Le groupe a été édité avec succès'),
		'L_GROUP_NOT_EDIT'			=> _t('L\'édition du groupe a échoué'),
		
		'MODE'						=> $mode,
		'GROUP_ID'					=> $group_id

	));
}

function panelCrud($getcrud) {
	global $db, $template, $config;
	$mode = requestVar('mode', '');
	$where = requestVar('crud_where', '');
	$num = requestVar('num', '');
	$data = null;
	if ($where != '') {
		if ($mode == "edit") {
			switch($getcrud) {
				case 'group':
					$data = $db->select(GROUPS, $where, null, null, '*', 'one');
				break;
				case 'setting_forums':
					$data = $db->select(FORUMS, $where, null, null, '*', 'one');
					
				break;
			}
		}
		elseif ($mode == 'add') {
			/*$str = '';
			displayListAdmForum($config['order_forums'], $str);
			$template->assignVars(array(
				'LIST_FORUMS'	=> $str
				)
			);*/
		}
	}
	$template->assignVars(array(
		'NUM'	=> $num,
		'PAGE'	=> $getcrud
	));
	crud($data);
}

function panelDesign() {
	global $db, $template;
	
	$template->assignVars(array(
		'U_CHANGE_DESIGN'	=> makeUrl('../index.php', array('cd' => 1))
		
	));
}



function panelDesignImageSet() {
	global $db, $template;
	$result = $db->select(DESIGN_IMAGESET);
	while ($data = $result->fetch_assoc()) {
		$row = array(
			'NAME' 				=>  $data['image_name'],
			'U_ABS_FILENAME'	=>	'../' . PATH_IMG . $data['image_filename'],
			'U_FILENAME'		=>	$data['image_filename'],
			'LANG'				=>	$data['image_lang'],
			'HEIGHT'			=>	$data['image_height'],
			'WIDTH'				=>	$data['image_width'],
			'ID'				=>	$data['image_id']
		
		);
		$template->assignBlockVars('image', $row);	
		
		displayLang('image.', $data['image_lang']);
		
	}
	
	$template->assignVars(array(
		'L_DESIGN'			=>  _t('Design'),
		'L_PX'				=>  _t('px'),
		'L_BTN_SAVE'		=>  _t('Enregistrer'),
		'L_TAB_IMG'			=>  _t('Images du forum'),
		'L_TAB_EDITOR'		=>  _t('Editeur graphique'),
		'L_ID_IMG'			=>  _t('Identifiant'),
		'L_OTHER_SETTING'	=>  _t('Autres réglages'),
		'L_LANG'			=>  _t('Langue'),
		'L_DEFAULT_FR'		=>  _t('Défaut / Français'),
		'L_HEIGHT'			=>  _t('Hauteur'),
		'L_WIDTH'			=>  _t('Largeur'),
		'L_EDITOR_HELP'		=> 	_t('L\'éditeur graphique vous permet de personnaliser le design du forum sans passer par le CSS. En modifiant le graphisque via ce système, vous créez seulement un design temporaire où vous pouvez revenir dessus à n\'importe quel moment sans que cela est une incidence sur le design du forum actuel. Ensuite, cliquez sur le bouton "Appliquer le design temporaire sur le forum" pour officialiser le design effectué sur le forum'),
		'L_INTERACTION'		=> _t('Interaction'),
		'L_BTN_EDITOR'		=> _t('Editeur graphique'),
		'L_BTN_SUBMIT'		=> _t('Appliquer le design temporaire sur le forum'),
		'L_BTN_INIT'		=> _t('Réinitialiser tout le design temporaire'),
		'L_IMG_CHANGED'		=> _t('Les images ont bien été changés'),
		'L_IMG_NOT_CHANGED'	=> _t('Le changement des images a échoué'),
		'L_SUBMIT_DESIGN'	=> _t('Le design temporaire a bien été appliqué sur le forum'),
		'L_NOT_SUBMIT_DESIGN'	=> _t('L\'application du design temporaire sur le forum a échoué'),
		'L_INIT_DESIGN'		=> _t('La réinitialisation du design temporaire a été effectuée avec succès'),
		'L_NOT_INIT_DESIGN'	=> _t('La réinitialisation du design temporaire a échoué'),
		'L_CONFIRM_INIT'	=> _t('Etes vous sûr de vouloir réinitialiser le design temporaire ?'),
		'L_CONFIRM_SUBMIT'	=> _t('Etes vous sûr d\'appliquer le design temporaire sur le forum ?'),
		'L_CONFIRM'			=> _t('Confirmation')
		
	));
	
	
}

function panelGroups() {
	global $template;
	displayListGroupsPermission();
	$template->assignVars(array(
		'L_CONFIRM_DELETE' 	=>  _t('Etes vous sûr de vouloir supprimer ce groupe ?'),
		'L_GROUPS' 			=>  _t('Groupes'),
		'L_MANAGE_GROUPS' 	=>  _t('Gérer Groupes'),
		'L_NAME' 			=>  _t('Nom du groupe'),
		'L_DESC' 			=>  _t('Description'),
		'L_CHECK' 			=>  _t('Cocher'),
		'L_NEW_GROUP' 		=>  _t('Créer un nouveau groupe'),
		'L_MANAGE' 			=>  _t('Administrer'),
		'L_EDIT' 			=>  _t('Editer'),
		'L_DELETE' 			=>  _t('Supprimer'),
		'L_GROUP_DELETE' 	=>  _t('Le groupe a été supprimé avec succès'),
		'L_GROUP_NOT_DELETE' 	=>  _t('La suppression du groupe a échoué'),
	));
}



function panelSettingUser($submit = false) {
	global $db, $crud, $template;
	$username =  requestVarPost('username', '');
	$user_id =  requestVarPost('user_id', null);
	if (!isset($user_id)) {
		$user_id = searchUser($username);
	}
	if ($user_id !== false) {
		$data = $db->select(USERS, 'user_id=' . $user_id, null, null, '*', 'one');
		viewProfileOptions($data['user_options']);
		userGroups($user_id);
		displayListGroupsPermission();
		$template->assignVars(viewprofile($data));
		
		// crud($data);
		
		$input = array(
		'user_msn' => array(
			'type'		=>	'string'	
		),
		'user_facebook'	=> array(
			'type'		=>	'string'	
		),
		'user_twitter'	=> array(
			'type'		=>	'string'	
		),
		'user_skype'	=> array(
			'type'		=>	'string'	
		),
		'user_hobbies'	=> array(
			'type'		=>	'string'	
		),
		'user_job'	=> array(
			'type'		=>	'string'	
		),
		'user_from' => array(
			'type'		=>	'string'	
		),
		'user_yahoo' => array(
			'type'		=>	'string'	
		),
		'user_website' => array(
			'type'		=>	'string'	
		),
		'user_sexe' => array(
			'type'		=>	'string'	
		),
		'user_birthday' => array(
			'type'		=>	'string'	
		)
	);
		$b = completeFields($input, $submit, array('table' => USERS, 'where' => 'user_id=' . $user_id, 'fetch' => $data));
		if ($submit) {
			echo $b;
		}
		
	}
	$template->assignVars(array(
		'IS_USER' 					=>  $user_id !== false,
		'L_GLOBAL'					=> _t('Général'),
		'L_MANAGE_USER'				=> _t('Gestion de l\'utilisateur'),
		'L_BACK'					=> _t('Retour'),
		'L_MOVE_ALL_MSG'			=> _t('Où voulez vous déplacer les messages de cet utilisateur ?'),
		'L_DELETE_ALL_MSG'			=> _t('Etes vous sûr de supprimer tous les messages des utilisateurs ? Attention l\'action est irreversible'),
		'L_DELETE_USER'				=> _t('Etes vous sûr de supprimer ce compte ? Attention l\'action est irreversible'),
		'L_PROFILE'					=> _t('Profil'),
		'L_PREF'					=> _t('Préférence'),
		'L_AVATAR'					=> _t('Avatar'),
		'L_SIG'						=> _t('Signature'),
		'L_GROUP'					=> _t('Groupe'),
		'L_COMMENT'					=> _t('Commentaire'),
		'L_PROFILE_USER_UNACTIV'	=> _t('Le compte est actuellement désactivé'),
		'L_REASON'					=> _t('Raison'),
		'L_INFO'					=> _t('Information'),
		'L_NB_MSG'					=> _t('Nombre de messages'),
		'L_REGISTER_DATE'			=> _t('Inscrit le'),
		'L_LAST_VISIT'				=> _t('Dernière visite'),
		'L_IP'						=> _t('IP'),
		'L_INTERACTION'				=> _t('Interaction'),
		'L_ACTIV'					=> _t('Activer'),
		'L_UNACTIV'					=> _t('Désactiver'),
		'L_EDIT_PERMISSION'			=> _t('Editer ses permissions'),
		'L_DELETE_USER_BTN'			=> _t('Supprimer le compte'),
		'L_ID'						=> _t('Identifiants'),
		'L_PASS_HELP'				=> _t('Laissez les champs "Mot de passe" vide pour garder le mot de passe actuel'),
		'L_USERNAME'				=> _t('Pseudonyme'),
		'L_PASSWORD'				=> _t('Mot de passe'),
		'L_CONFIRM_PASSWORD'		=> _t('Confirmation du mot de passe'),
		'L_EMAIL'					=> _t('Adresse email'),
		'L_VALID'					=> _t('Valider'),
		
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
		
		'L_GROUP_NAME'				=> _t('Nom du groupe'),
		'L_DEFAULT'					=> _t('Par défaut'),
		'L_RESPONSIBLE'				=> _t('Responsable'),
		'L_YES'						=> _t('Oui'),
		'L_NO'						=> _t('Non'),
		'L_GROUP_DEFAULT'			=> _t('Groupe par défaut'),
		'L_GROUP_DELETE'			=> _t('Supprimer du groupe'),
		'L_GROUP_ADD_USER'			=> _t('Ajouter le membre dans un groupe'),
		'L_GROUP_ADD_USER'			=> _t('Ajouter le membre dans un groupe'),
		'L_USER_NO_EXIST'			=> _t('Cet utilisateur n\'existe pas'),
		'L_PROFILE_MODIFIED'		=> _t('Le profil de l\'utilisateur a été édité avec succès'),
		'L_PROFILE_NOT_MODIFIED'	=> _t('L\'édition du profil de l\'utilisateur a échoué'),
		'L_USERNAME_REQUIRED'		=> _t('Veuillez rentrer un nom d\'utilisateur'),
		'L_USERNAME_MINLENGTH'		=> sprintf(_t('Votre nom d\'utilisateur doit contenir minimum %d caractères'), 3),
		'L_USERNAME_EXIST'			=> _t('Ce nom d\'utilisateur existe déjà. Veuillez en réessayer un autre'),
		'L_PASS_REQUIRED'			=> _t('Veuillez rentrer un mot de passe'),
		'L_PASS_MINLENGTH'			=> sprintf(_t('Le mot de passe doit contenir %d caractères minimum'), 5),
		'L_PASS_NOT_EQUAL'			=> _t('Le mot de passe n\'est pas équivalent au précédent'),
		'L_EMAIL_REQUIRED'			=> _t('Veuillez rentrer une adresse e-mail'),
		'L_EMAIL_VALID_REQUIRED'	=> _t('Veuillez rentrer une adresse e-mail valide'),
		'L_WEBSITE' 			=> _t('Site Web'),
		// 'USER_WEBSITE'			=> $user->data['user_website'],
		
		'L_FROM' 				=> _t('Localisation'),
		'L_MSN' 				=> _t('Adresse MSN'),
		'L_YAHOO' 				=> _t('Adresse Yahoo!'),
		'L_SKYPE' 				=> _t('Adresse Skype'),
		'L_FACEBOOK' 			=> _t('Votre page Facebook'),
		'L_TWITTER' 			=> _t('Votre page Twitter'),
		'L_HOBBIES' 			=> _t('Loisirs'),
		'L_EMPLOYMENT' 			=> _t('Emploi'),
		'L_SEX' 				=> _t('Sexe'),
		'L_BIRTHDAY'			=> _t('Date de naissance'),
		'L_MALE' 				=> _t('Masculin'),
		'L_WOMEN' 				=> _t('Féminin'),
		'NOT_SPECIFY'			=> _t('Ne pas spécifier')
		// 'USER_FROM'				=> $user->data['user_from'],
		// 'USER_MSN'				=> $user->data['user_msn'],
		// 'USER_YAHOO'			=> $user->data['user_yahoo'],
		// 'USER_SKYPE'			=> $user->data['user_skype'],
		// 'USER_FACEBOOK'			=> $user->data['user_facebook'],
		// 'USER_TWITTER'			=> $user->data['user_twitter'],
		// 'USER_HOBBIES'			=> $user->data['user_hobbies'],
		// 'USER_JOB'				=> $user->data['user_job'],
		// 'USER_SEXE'				=> $user->data['user_sexe'],
		// 'USER_BIRTHDAY'			=> $user->data['user_birthday'],
		// 'LANG_DATEPICKER'		=> $config['lang_default']					

	));
	
	
	
}

function userGlobalMoveAllMsg($user_id, $forum_id) {
	global $db;
	$b = $db->update(TOPICS, array('forum_id' => $forum_id), 'topic_poster=' . $user_id);
	synchronizedAllForum();
	return $b;
}

function userGlobalDeleteAllMsg($user_id) {
	global $db;
	$b = $db->update(USERS, array('user_nb_message' => 0), 'user_id=' . $user_id);
	$b &= $db->delete(POSTS, array('poster_id' => $user_id));
	synchronizedAllForum();
	synchronizedGlobalStats();
	return $b;
	
}




function userGlobalDeleteUser($user_id) {
	global $db;
	$b = $db->delete(USERS, array('user_id' => $user_id));
	$db->delete(USERS_GROUP, array('user_id' => $user_id));
	$db->delete(USERS_AVERTS, array('user_id' => $user_id));
	$db->delete(TOPICS_READ, array('user_id' => $user_id));
	synchronizedMembers();
	changeUserToAnonymous($user_id);
	return $b;
}


function settingUserPref($user_id) {
	//$options = requestVarPost('options', array());
	global $user, $db;
	$options = 0x0;
	foreach ($_POST as $key => $value) {
		if ($key != 'userPref' && $key != 'user_id' && $value[0] == 1) {
			$options += $user->getOption($key);
		}
	}
	return $db->update(USERS, array("user_options" => dechex($options)), 'user_id=' . $user_id);
	
	
}

function panelUsersBan() {
	global $db, $template;
	$result = $db->select(USERS . ' u,' . USERS_BAN . ' ub', 'u.user_id=ub.ban_user_id', 'ban_date DESC');
	while($data = $result->fetch_array()) {
			$userrow = array(
				'U_PANEL_BAN'				=> makeUrl('../profile.php', array('mode' => 'averts', 'u' => $data['user_id'])),
				'USERNAME'					=> $data['username'],
				'BAN_DATE'					=> strTime($data['ban_date'], 8),
				'BAN_EXPIRE'				=> $data['ban_expire'] == $data['ban_date'] ? _t('Indéfinie') : strTime($data['ban_expire'], 8),
				'BAN_REASON'				=> $data['ban_reason']
			);
		$template->assignBlockVars('user_ban', $userrow);	
	}

}

function panelUsersAvert() {
	global $db, $template;
	$result = $db->select(USERS . ' u,' . USERS_AVERTS . ' ua', 'u.user_id=ua.user_id', 'avert_date DESC');
	while($data = $result->fetch_array()) {
			$userrow = array(
				'U_PANEL_AVERT'				=> makeUrl('../profile.php', array('mode' => 'averts', 'u' => $data['user_id'])),
				'USERNAME'					=> $data['username'],
				'USER_AVERT'				=> $data['user_avert'],
				'AVERT_DATE'				=> strTime($data['avert_date'], 8),
				'AVERT_EXPIRE'				=> $data['avert_expire'] == $data['avert_date'] ? _t('Indéfinie') : strTime($data['avert_expire'], 8),
				'AVERT_REASON'				=> $data['avert_reason']
			);
		$template->assignBlockVars('user_avert', $userrow);	
	}

}

function panelUserInactive() {
	global $db, $template;
	$result = $db->select(USERS, 'user_activ=0', 'user_regdate DESC');
	while($data = $result->fetch_array()) {
			$reason = $data['user_activ_reason'];
			switch ($reason) {
				case 0:
					$text = _t('Le compte est nouvellement inscrit');
				break;
				case 1:
					$text = _t('Le compte a été désactivé par un administrateur');
				break;
			}
			$userrow = array(
				'USERNAME'					=> $data['username'],
				'USER_ID'					=> $data['user_id'],
				'REGDATE'					=> strTime($data['user_regdate'], 8),
				'REASON'					=> $text,
				'LAST_VISIT'				=> $data['user_lastvisit'] == 0 ? _t('Jamais') : strTime($data['user_lastvisit'], 8)
			);
		$template->assignBlockVars('inactive', $userrow);	
	}

}

function userGroups($user_id) {
	global $db, $template;
$result = $db->select(GROUPS . ' g,' . USERS . ' u,' . USERS_GROUP . ' ug', 'ug.group_id=g.group_id AND ug.user_id=u.user_id AND user_status=1 AND u.user_id=' . $user_id, null, null, '*, u.group_id AS user_group_id');
	$i=0;
	while($data = $result->fetch_array()) {
			$grouprow = array(
				'GROUP_NAME'				=> $data['group_name'],
				'GROUP_ID'					=> $data['group_id'],
				'MY_GROUP_ID'				=> $data['user_group_id'] == $data['group_id'],
				'GROUP_COLOR'				=> $data['group_color'],
				'FONDATOR'					=> FONDATOR_ID == $user_id,
				'IS_MANAGE'					=> $data['group_founder_manage'] == $user_id,
				'NUM'						=> $i
			);
			$template->assignBlockVars('user_group', $grouprow);	
		$i++;
	}
}


function uploadAvatar($user_id) {
	global $db;
	$tab_safe = array('PNG', 'JPG', 'JPEG', 'GIF');
	$file = requestVarFile('Filedata', '');
	$upload = new Upload($file, '../images/avatars');
	if(in_array($upload->type(), $tab_safe)) {
		$new_name = $user_id . '.' . $upload->type(true);
		if ($upload->uploadMoveFile($new_name)) {	
			if ($db->update(USERS, array('user_avatar' => $new_name), 'user_id=' . $user_id)) {
				return $new_name;	
			}
		}
	}
	return "";
	
}

function panelForums() {
	global $config, $user, $db, $template;
	$str = '';
	$db->dataForums($user);
	$order = $config['order_forums'];
	displayListAdmForum($order, $str);
	displayListForum($order);
	$template->assignVars(array(
		'LIST_FORUMS'		=> $str,
		'L_FORUM'			=> _t('Forums'),
		'L_CONFIRM_DELETE'	=> _t('Etes vous sûr de supprimer ce forum ?'),
		'L_CONFIRM_MOVE_POSTS'	=> _t('Veuillez déplacer les messages dans un autre forum'),
		'L_TAB_FORUM'		=> _t('Gestion des forums'),
		'L_HELP_FORUM'		=> _t('- Chaque forum peut contenir un nombre illimité de sous-forums. Pour changer les forums de position, cliquez sur un forum et déplacer le en maintenant votre clic. Lorsque vous êtes satisfait des positions des forums, cliquez sur le bouton "Valider le déplacement" pour appliquer les nouvelles positions sur le forum. <br />- Un forum créé à partir de la racine deviendra une catégorie. <br /> - Pour supprimer un forum ayant des sous forums, veuillez d\'abord supprimer ses sous-forums. C\'est une mesure de sécurité.'),
		'L_BTN_CREATE'			=> _t('Créer un nouveau forum'),
		'L_BTN_CREATE_CAT'		=> _t('Créer une catégorie'),
		'L_BTN_EDIT'		=> _t('Editer le forum selectionné'),
		'L_BTN_REMOVE'		=> _t('Supprimer le forum selectionné'),
		'L_LEGEND_INTERACTION'	=> _t('Création, Edition, Suppression'),
		'L_LEGEND_OTHER'		=> _t('Permission, Déplacement'),
		'L_BTN_EDIT_PERMISSION'	=> _t('Editer les permissions du forum pour les groupes'),
		'L_BTN_SUBMIT_MOVE'		=> _t('Valider le déplacement'),
		'L_ARBO'				=> _t('Arborescence des forums'),
		'L_ROOT'				=> _t('Racine'),
		'L_FORUM_DELETE'		=> _t('Suppression du forum réussi'),
		'L_FORUM_NOT_DELETE'	=> _t('La suppression du forum a échoué'),
		)
	);
	
	
	
}

function displayListAdmForum($order, &$str, $depth = 0) {
	global $db;
	
	$depth = 0;
	$i = 0;
	$next_is_id = false;
	$test_pos = array('{', ';', '}');
	$str = "";
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
			$row_forum = $db->forum[$_forums_id];	
			$str .= '<li data-id="' . $_forums_id . '"><a href="javascript://">' . $row_forum['forum_name'] . '</a>';
			if ($c != '{') {
				$str .= '</li>';
			}
			$next_is_id = false;
		}
		switch ($c) {
			case '{':
				$depth++;
				$str .= '<ul>';
			break;
			case '}':
				$depth--;
				$str .= '</ul></li>';
			break;
			case ':':
				$next_is_id = true;
			break;
		}
		$i++;
	}
	
	/*if (preg_match_all('#([0-9]+)(-[0-9]+)?:([0-9]+)(\\{(.*?)\\})?(;|\\}|\\{)#', $order, $matches)) {
		$forums_id = $matches[3];
		$forum_order = $matches[1];
		$subforum_order = $matches[4];
		$nb_forum = sizeof($forums_id);
		
		$str .= '<ul>';
		for ($i=0 ; $i < $nb_forum ; $i++) {
			$row_forum = $db->forum[$forums_id[$i]];	
			$str .= '<li data-id="' . $forums_id[$i] . '"><a href="javascript://">' . $row_forum['forum_name'] . '</a>';
			 
			/*$forumrow = array(
						'ID'		=>  $forums_id[$i],
						'NAME'		=>  $row_forum['forum_name'],
						'INDENT'	=>	$indent . '>&nbsp;',
						'IS_CAT'	=> $depth == 0
			);
			if (!empty($subforum_order[$i])) {
				displayListAdmForum($subforum_order[$i] . '}', $str, $depth+1);
			}
			
			$str .= '</li>';
		}		
		$str .= '</ul>';					
	}*/

}

function panelPermission($mode) {
	global $config, $db, $user, $template;
	
	$left =  requestVarPost('left', array());
	$right =  requestVarPost('right', array());
	$left = implode(',', $left);
	$right = implode(',', $right);
	
	$db->dataForums($user);
	
	if ($mode == "all_forum_group") {
		$left = '';
		$result = $db->select(GROUPS);
		while($data = $result->fetch_array()) {
			$left .= $data['group_id'] . ',';
		}
		$left = preg_replace('#,$#', '', $left);
		$mode = "forum_group";
	}
	
	if ($left == "" && $right == "") {
		displayListGroupsPermission();
		displayListForum($config['order_forums']);
	}
	else {
		displayPermission($left, $right, $mode);
	}
	
	$template->assignVars(array(
		'L_PERMISSION' 			=> _t('Permissions'),
		'L_USERS' 				=> _t('Utilisateurs'),
		'L_GROUPS' 				=> _t('Groupes'),
		'L_USERS_FOR_FORUM' 	=> _t('Utilisateurs pour un/des forums'),
		'L_GROUPS_FOR_FORUM' 	=> _t('Groupes pour un/des forums'),
		'L_ADD_MEMBER' 			=> _t('Ajouter un membre'),
		'L_ADD' 				=> _t('Ajouter'),
		'L_MEMBER_ADDED' 		=> _t('Membres ajoutés'),
		'L_EDIT_PERMISSION' 	=> _t('Modifier les permissions'),
		'L_REMOVE_USER_SELECTED' => _t('Enlever les utilisateurs sélectionnés'),
		'L_FORUMS' 				=> _t('Forums'),
		'L_MULTISELECT_HELP' 	=> _t('Choisissez un ou plusieurs groupes (maintenez la touche CTRL) ainsi que les forums pour détermininer les permissions des groupes aux forums sélectionnés'),
	));
	
}

function panelLangs() {
	global $template, $db, $config;
	$result = $db->select(LANGS);
	$default = false;
	$fr_default = true;
	while ($data = $result->fetch_assoc()) {
		$default = $config['lang_default'] ==  $data['lang_longid'];
		if ($default) {
			$fr_default = false;
		}
		$row = array(
			'NAME'				=>  $data['lang_name'],
			'AUTHOR'			=>  $data['lang_author'],
			'VERSION'			=>  $data['lang_version'],
			'INSTALL_TIME'		=>  strTime($data['lang_install_time'], 8),
			'ID'				=>  $data['lang_id'],
			'IS_DEFAULT'		=>  $default,
			'LONGID'			=>  $data['lang_longid']
		);
		$template->assignBlockVars('lang', $row);	
	}
	
	$template->assignVars(array(
		'DEFAULT' 			=> $fr_default,
		'LONGID_DEFAULT'	=> 'fr_FR'
	));
	
}

function crud($data = null) {
	global $template, $db, $crud;
	$template->assignVars(array(
		'DISPLAY' 			=> $crud->display($data),
		'DISPLAY_HEADER'	=> $crud->displayHeader()
	
	));
}

function panelGlobalSetting($submit) {
	global $template;
	$template->assignVars(array(
			'L_NEW_LANG'			=> _t('Les nouvelles langues suivantes ont été installées'),
			'L_UNINSTALL_LANG'		=> _t('Les langues suivantes ont été désinstallées'),
			'L_MAIN'				=> _t('Général'),
			'L_TAB_SETTING'			=> _t('Réglage'),
			'L_TAB_LANG'			=> _t('Langues installées'),
			'L_HELP_SETTING'		=> _t('Vous pouvez effectuer ici les réglages de base de votre forum, lui attribuer un nom et une description, régler son fuseau horaire ou encore définir sa langue par défaut.'),
			'L_SETTING_FORUM'		=> _t('Réglage du forum'),
			'L_SITENAME'			=> _t('Nom du forum'),
			'L_SITE_DESC'			=> _t('Description du forum'),
			'L_EMAIL_DEFAULT'		=> _t('Adresse email utilisée pour envoyer les emails automatiques'),
			'L_ACTIV_AUTO_COMPLETION' => _t('Activer l\'auto-completion'),
			'L_DISPLAY_QUICK_PROFILE'	=> _t('Afficher le profil/connexion rapide'),
			'L_FORUM_CORBEILLE'			=> _t('Identifiant du forum qui sert de corbeille'),
			'L_INTERVAL_DAYS_PURGE_TOPIC_READ'	=> _t('Intervalle de jours où les sujets non lus sont marqués comme lus'),
			'L_DAYS'				=> _t('jour(s)'),
			'L_SETTING_AVATAR'		=> _t('Réglages des avatars'),
			'L_MAX_HEIGHT_AVATAR'	=> _t('Hauteur maximale d\'un avatar'),
			'L_MAX_WIDTH_AVATAR'	=> _t('Largeur maximale d\'un avatar'),
			'L_YES'					=> _t('Oui'),
			'L_NO'					=> _t('Non'),
			'L_PIXEL'				=> _t('px'),
			'L_SUBMIT_GlOBAL'		=> _t('Enregistrer'),
			
			'L_NAME'				=> _t('Nom'),
			'L_AUTHOR'				=> _t('Auteur'),
			'L_VERSION'				=> _t('Version'),
			'L_INSTALLED'			=> _t('Installé le'),
			'L_LANG_DEFAULT'		=> _t('Langue par défaut'),
			'L_LANG_FR'				=> _t('Français'),
			'L_SUBMIT_LANG_DEFAULT' => _t('Appliquer la langue par défaut'),
			'L_SETTING_CHANGED'		=> _t('Les réglages ont été modifiés avec succès'),
			'L_SETTING_NOT_CHANGED'	=> _t('La modification des réglages a échoué'),
			'L_TITLE_INSTALL_LANG'	=> _t('Installation/Désinstallation Langues'),
			'L_LANG_CHANGED'		=> _t('L\'assignation de la langue par défaut a réussi'),
			'L_LANG_NOT_CHANGED'	=> _t('L\'assignation de la langue par défaut a échoué')
		)
	);
	
	$input = array(
		'sitename' => array(
			'type'		=>	'string'	
		),
		'site_desc'	=> array(
			'type'		=>	'string'	
		),
		'email_default'	=> array(
			'type'		=>	'string'	
		),
		'activ_auto_completion'	=> array(
			'type'		=>	'string'	
		),
		'display_quick_profile'	=> array(
			'type'		=>	'string'	
		),
		'forum_corbeille'	=> array(
			'type'		=>	'string'	
		),
		'interval_days_purge_topic_read' => array(
			'type'		=>	'string'	
		),
		'max_width_avatar' => array(
			'type'		=>	'string'	
		),
		'max_height_avatar' => array(
			'type'		=>	'string'	
		)
	);
	completeFields($input, $submit);
}

function panelIcons() {
	global $db, $template;
	
	$template->assignVars(array(
		'L_TAB_ICON_HELP'	=> _t("Un ensemble d'icônes peuvent être attribué à un forum. Ainsi, chaques forums peuvent avoir ses propres icônes. Vous pouvez ajouter, supprimer ou éditer ici les icônes que les utilisateurs peuvent ajouter à leurs sujets ou messages. Ces icônes sont généralement affichées à côté des titres des sujets sur la liste des forums, ou à côté des titres des messages sur la liste des sujets."),
		'L_CREATE_ICON_SET'	=> _t("Créer un ensemble d'îcone"),
		'L_EDIT_ICON_SET'	=> _t("Editer l'ensemble d'îcones"),
		'L_DELETE_ICON_SET'	=> _t("Supprimer l'ensemble d'icônes"),
		'L_BTN_DELETE_ICON'		=> _t("Supprimer l'icône"),		
		'L_ICONSET_MANDATORY' => _t('Obligatoire dans tous les forums par défaut'),
		'L_ICONSET_NAME'	=> _t('Nom'),
		'L_ICON_NAME'		=> _t('Nom'),
		'L_ICON_WIDTH'		=> _t('Largeur (facultatif)'),
		'L_ICON_HEIGHT'		=> _t('Hauteur (facultatif)'),
		'L_ICON_DISPLAY'	=> _t("Afficher l'icône"),
		'L_ADD_ICON'		=> _t("Ajouter une icône"),
		'L_EDIT_ICON'		=> _t("Editer l'icône selectionnée"),
		'L_ICON_SETTING'	=> _t('Icônes'),
		'L_ICONSET_SETTING'	=> _t("Ensemble d'icônes"),
		'L_DELETE_ICON'		=> _t("Supprimer l'icône selectionnée"),
		'L_OK'				=> _t('Valider'),
		'L_CANCEL'			=> _t('Annuler'),
		'L_PX'				=> _t('px'),
		
		'L_POS_CHANGED'		=> _t('Les positions des icônes ont été modifiées avec succès'),
		'L_NOT_POS_CHANGED'	=> _t('La modification des positions des icônes a échoué'),
		
		'L_ICON_ADD'		=> _t('L\'icône a été ajoutée avec succès'),
		'L_ICON_NOT_ADD'	=> _t('L\'ajout de l\'icône a échoué'),
		'L_ICON_EDIT'		=> _t('L\'icône a été éditée avec succès'),
		'L_ICON_NOT_EDIT'	=> _t('L\'édition de l\'icône a échoué'),
		'L_ICON_DELETE'		=> _t('L\'icône a été supprimée avec succès'),
		'L_ICON_NOT_DELETE'	=> _t('La suppression de l\'icône a échoué'),
		'L_ICONSET_ADD'		=> _t('L\'ensemble d\'icônes a été ajoutée avec succès'),
		'L_ICONSET_NOT_ADD'	=> _t('L\'ajout de l\'ensemble d\'icônes a échoué'),
		'L_ICONSET_EDIT'		=> _t('L\'ensemble d\'icônes a été éditée avec succès'),
		'L_ICONSET_NOT_EDIT'	=> _t('L\'édition de l\'ensemble d\'icônes a échoué'),
		'L_ICONSET_DELETE'		=> _t('L\'ensemble d\'icônes a été supprimée avec succès'),
		'L_ICONSET_NOT_DELETE'	=> _t('La suppression de l\'ensemble d\'icônes a échoué'),
		
		'L_DELETE_ICONSET_'	=> _t('Voulez vous supprimer cet ensemble de icône ?'),
		'L_DELETE_ICON'		=> _t('Voulez vous supprimer cette icône ?'),
		'L_SELECT_IMG'		=> _t('Choisir une image')
		
	));
	
	$result = $db->select(ICONS, null, 'icon_position ASC');
	while($data = $result->fetch_array()) {
		if (!isset($icons[$data['iconset_id']])) {
			$icons[$data['iconset_id']] = array();
		}
		$icons[$data['iconset_id']][] = $data;
	}
	
	$result = $db->select(ICONSET);
	while($data = $result->fetch_array()) {
			$iconset = $icons[$data['iconset_id']];
			$row = array(
					'NAME'				=>  $data['iconset_name'],
					'ID'				=>  $data['iconset_id'],
					'MANDARY'			=>  $data['iconset_mandary'],
					'NB_ICONS'			=> sizeof($iconset)
					
			);
			$template->assignBlockVars('iconset', $row);
			for ($i = 0 ; $i < sizeof($iconset) ; $i++) {
				$icon_path = '../images/icons/' . $iconset[$i]['icon_path'];
				$icon_path = str_replace("\n","", $icon_path);
				$img = new Image($icon_path);
				$row = array(
					'ID'				=>  $iconset[$i]['icon_id'],
					'NAME'				=>  $iconset[$i]['icon_name'],
					'PATH'				=>  $icon_path,
					'WIDTH'				=>  $iconset[$i]['icon_width'],
					'HEIGHT'			=>  $iconset[$i]['icon_height'],
					'REAL_WIDTH'		=>  $img->width(),
					'REAL_HEIGHT'		=>  $img->height(),
					'MIN'				=>  $img->height() > ICON_HEIGHT || $img->width() > ICON_WIDTH,
					'DISPLAY'			=>  $iconset[$i]['icon_display']
				);
				$template->assignBlockVars('iconset.icon', $row);
			}
		$i++;
	}
}

function panelMessages($submit) {
	global $template, $config, $db;
	$template->assignVars(array(
		'L_MESSAGES'		=> _t('Messages'),
		'L_TAB_FORUM'		=> _t('Messages du forum'),
		'L_TAB_PM'			=> _t('Messages privés'),
		'L_TAB_EMAIL'		=> _t('Messages des emails envoyés'),
		'L_TAB_ICONS'		=> _t('Icônes de sujet'),
		'L_TAB_FORUM_HELP'	=> _t('Vous pouvez effectuer ici tous les réglages par défaut concernant la publication.'),
		'L_TAB_PM_HELP'		=> _t('Vous pouvez effectuer ici tous les réglages par défaut de la messagerie privée.'),
	
		'L_CONFIG_MSG'		=> _t('La configuration des messages a réussi'),
		'L_NOT_CONFIG_MSG'	=> _t('La configuration des messages a échoué'),
		
		'L_DISPLAY_LABEL_POLL'	=> _t('Afficher l\'étiquette \'[Sondage]\' avant le nom du sujet si ce dernier est un sondage'),
		'L_YES'					=> _t('Oui'),
		'L_NO'					=> _t('Non'),
		'L_NB_SUBJECT'			=> _t('Nombre de sujets par page'),
		'L_LIST_SUBJECT'		=> _t('Liste des sujets'),
		'L_SETTING_MSG'			=> _t('Réglage des messages'),
		'L_NB_MSG'				=> _t('Nombre de messages par page'),
		'L_MIN_CHAR_POST'		=> _t('Nombre minimum de caractères requis'),
		'L_MAX_POLL_OPIONS'		=> _t('Nombre maximum d\'option dans les sondages'),
		'L_SUBJECT_MAXLENGTH'	=> _t('Nombre de caractères maximum dans le titre d\'un message'),
		'L_SUBMIT_MSG'			=> _t('Enregistrer les changements'),
		'L_SETTING_PM'			=> _t('Réglage du message privé'),
		'L_NB_DEST'				=> _t('Nombre maximum de destinaires'),
		
		'L_EMAIL_VALIDATION_TEXT'	=> _t('Email de validation'),
		'L_FORGET_PASS_TEXT'		=> _t('Email en cas d\'oublie de mot de passe'),
		'L_AVERT_NEW_MP_TEXT'		=> _t('Email avertissant un nouveau message privé'),
		'L_AVERT_NEW_REPLY_TEXT'	=> _t('Email avertissant une nouvelle réponse à son sujet'),
		'L_EMAIL_VALIDATION_TEXT_HELP'	=> sprintf(_t('Les éléments suivants seront remplacés lors de l\'envoi de l\'email : %s : Nom du site %s : Le lien d\'activation du compte'), '<br /> - {SITE_NAME}', '<br /> - {U_VALID_REGISTER}'),
		'L_FORGET_PASS_TEXT_HELP'		=> sprintf(_t('Les éléments suivants seront remplacés lors de l\'envoi de l\'email : %s : Nom du site %s : Le nouveau mot de passe'), '<br /> - {SITE_NAME}', '<br /> - {NEW_PASS}'),
		'L_AVERT_NEW_MP_TEXT_HELP'		=> sprintf(_t('Les éléments suivants seront remplacés lors de l\'envoi de l\'email : %s : Nom du site %s : L\'auteur du message privé %s : Le lien direct vers le message privé'), '<br /> - {SITE_NAME}', '<br /> - {AUTHOR}',  '<br /> - {U_BOX_MP}'),
		'L_AVERT_NEW_REPLY_TEXT_HELP'	=> sprintf(_t('Les éléments suivants seront remplacés lors de l\'envoi de l\'email : %s : Nom du site %s : Le sujet du message %s : Le lien direct vers le sujet'), '<br /> - {SITE_NAME}', '<br /> - {TOPIC_NAME}', '<br /> - {U_TOPIC}'),
		'L_HELP'						=> _t('Aide')
		
	
	));
	$input = array(
		'display_label_poll' => array(
			'type'		=>	'string'	
		),
		'subject_per_page'	=> array(
			'type'		=>	'string'	
		),
		'post_per_page'	=> array(
			'type'		=>	'string'	
		),
		'msg_min_char_post'	=> array(
			'type'		=>	'string'	
		),
		'max_poll_options'	=> array(
			'type'		=>	'string'	
		),
		'subject_maxlength'	=> array(
			'type'		=>	'string'	
		),
		'max_destina_msg_private' => array(
			'type'		=>	'string'	
		),
		'email_validation_text' => array(
			'type'		=>	'string'	
		),
		'forget_pass_text' => array(
			'type'		=>	'string'	
		),
		'avert_new_mp_text' => array(
			'type'		=>	'string'	
		),
		'avert_new_reply_text' => array(
			'type'		=>	'string'	
		)
	);
	completeFields($input, $submit);
}

function completeFields($input, $submit, $options = null, $mode = 'edit') {
	global $template, $db, $config;
	$tpl_value = array();
	foreach ($input as $key => $value) {	
		if ($mode == 'edit') {
			if (isset($value['const'])) {
				$tpl_value['VAL_' . strtoupper($key)] = $value['const'];
			}
			else {
				$tpl_value['VAL_' . strtoupper($key)] = isset($options) ? $options['fetch'][$key] : $config[$key];
			}
		}
		else {
			$tpl_value['VAL_' . strtoupper($key)] = requestVarPost($key, '');
		}
	}
	$template->assignVars($tpl_value);
	
	$form = new Forms_Validate($input);
	$v = $form->isValidate();
	$json = array('success' => false);
	if ($submit) {
		if (empty($v)) {
			$b = true;
			if ($mode == 'edit') {
				if (isset($options)) {
					$form->updateDb($db, $options['table'], $options['where']);
				}
				else {
					foreach ($input as $key => $value) {	
						$b &= $db->update(CONFIG, array('config_value' => htmlDoubleQuote($form->input[$key]['value'])), 'config_name="' . $key . '"');
					}
				}
			}
			else {
				$ret = $form->insertDb($db, $options['table']);
				$b = $ret['success'];
			}
			$json['success'] = $b;
		}
		echo json_encode($json);
	}
}


function displayListGroupsPermission() {
	global $db, $template;
	$result = $db->select(GROUPS);
	$i = 0;
	while($data = $result->fetch_array()) {
			$grouprow = array(
					'NAME'				=>  $data['group_name'],
					'ID'				=>  $data['group_id'],
					'COLOR'				=> $data['group_color'],
					'DESC'				=> strRaccourci($data['group_desc'], 200),
					'NO_DELETE'			=>  $data['group_id'] == GROUP_ADMIN_ID ||
											$data['group_id'] == GROUP_VISITOR_ID ||
											$data['group_id'] == GROUP_MEMBER_ID,
					'NO_ADMINIST'		=>  $data['group_id'] == GROUP_VISITOR_ID || 
											$data['group_id'] == GROUP_MEMBER_ID,
					'NUM'				=>  $i
					
			);
			$template->assignBlockVars('group', $grouprow);	
		$i++;
	}
	$template->assignVars(array(
		'U_GROUP'			=> makeUrl('../memberlist.php', array('mode' => 'groups', ))
	));
	
}

function displayPermission($left, $right, $mode) {
	global $template, $permission, $db;
	
	switch($mode) {
		case 'forum_group':
			$type_permission = 'forum';
			$type_row = 'group_permission';
			if ($left == "" || $right == "") {
				return;
			}
		break;
		case 'forum_user':
			$type_permission = 'forum';
			$type_row = 'user_permission';
			if ($left == "" || $right == "") {
				return;
			}
		break;
		case 'group':
			$type_permission = 'users';
			$type_row = 'group_permissions';
			if ($left == "") {
				return;
			}
		break;
		case 'user':
			$type_permission = 'users';
			$type_row = 'user_permissions';
			if ($left == "") {
				return;
			}
		break;
	}
	$text_permission =  typePermission($type_permission);
	
	foreach ($text_permission as $key => $value_array) {
		//$template->assignBlockVars('cat_permis', array('CAT' => $key));	
		foreach ($value_array as $type => $name) {
			$template->assignBlockVars('name_permis', array('NAME' => $name));	
		}	
	}
	
	if ($mode == 'forum_user' || $mode == 'user') {
		$type_cat_permis = 'user';
	}	
	if ($mode == 'forum_group') {
		$result = $db->select(GROUPS . ' g,' . GROUPS_PERMISSION . ' gp', 'g.group_id=gp.group_id 
						  AND gp.forum_id IN (' . $right . ') AND gp.group_id IN (' . $left . ')');
		$result_display = $db->select(GROUPS, 'group_id IN (' . $left . ')');
		$result_fdisplay = $db->select(FORUMS, 'forum_id IN (' . $right . ')');
	}
	elseif ($mode == 'forum_user') {
		$result = $db->select(USERS . ' u,' . USERS_PERMISSION . ' up', 'u.user_id=up.user_id 
						 AND up.forum_id IN (' . $right . ') AND up.user_id IN (' . $left . ')');
		$result_display = $db->select(USERS, 'user_id IN (' . $left . ')');
		$result_fdisplay = $db->select(FORUMS, 'forum_id IN (' . $right . ')');
	}
	elseif ($mode == 'group') {
		$result = $db->select(GROUPS, 'group_id IN (' . $left . ')');
	}
	
	elseif ($mode == 'user') {
		$result = $db->select(USERS, 'user_id IN (' . $left . ')');
	}
	
	if ($type_permission == 'forum') {
		$permis_forum = array();
		while ($data = $result->fetch_array()) {
			if ($mode == 'forum_group') {
				$id_name = 'group';
			}
			else {
				$id_name = 'user';
			}
			$permis_forum[] = array(
				'left_id' 		=> $data[$id_name . '_id'],
				'forum_id' 		=> $data['forum_id'],
				'permission'	=> $data[$type_row]
			);
		}
		
		$a_left = array();
		while ($row_left = $result_display->fetch_array()) {
			if ($type_cat_permis == 'user') {
					$left_id = $row_left['user_id'];
					$a_left[] = array(	
						'name' 		=> $row_left['username'], 
						'color' 	=> '',
						'id' 		=> $left_id
					);	
					
				}
			  else {
					$left_id = $row_left['group_id'];
					$a_left[] = array(	
						'name' 		=> $row_left['group_name'], 
						'color' 	=> $row_left['group_color'],
						'id' 		=> $left_id
					);	
			  }
		}
		$a_right = array();
		while ($row_right = $result_fdisplay->fetch_array()) {	
			$a_right[] = array(
				'forum_name' 	=> $row_right['forum_name'],
				'forum_id'		=> $row_right['forum_id']
			);
			
		}	
		
		$k = 0;
		for ($i=0 ; $i < sizeof($a_left) ; $i++) {
			for ($j=0 ; $j < sizeof($a_right) ; $j++) {		
					$permission_forum = searchPermissionForum($permis_forum, $a_left[$i]['id'], $a_right[$j]['forum_id']);
					$perow = array(
						'NAME' 			=> $a_left[$i]['name'],
						'COLOR' 		=> $a_left[$i]['color'],
						'ID' 			=> $a_left[$i]['id'],
						'FORUM_NAME' 	=> $a_right[$j]['forum_name'],
						'FORUM_ID' 		=> $a_right[$j]['forum_id'],
						'NUM'			=> $k
					);
					$template->assignBlockVars('permis', $perow);
					foreach ($text_permission as $key => $value_array) {
						foreach ($value_array as $type => $name) {
							$checked = userCan($permission[$type_permission][$type], $permission_forum);
							$template->assignBlockVars('permis.type', array(
								'TYPE' 		=> $type, 
								'CHECKED'	=> $checked
							));	
						}
					}	
				$k++;	
			}
		}
	}
	else {
		$k = 0;
		while ($row = $result->fetch_array()) {
			if ($type_cat_permis == 'user') {
				$perow = array(	
					'NAME' 		=> $row['username'], 
					'COLOR' 	=> '',
					'ID' 		=> $row['user_id'],
					'NUM'		=> $k
				);	
			}
			else {
				$perow = array(	
					'NAME' 		=> $row['group_name'], 
					'COLOR' 	=> $row['group_color'],
					'ID' 		=> $row['group_id'],
					'NUM'		=> $k
				);	
			}	
	
			$template->assignBlockVars('permis', $perow);
			foreach ($text_permission as $key => $value_array) {
				foreach ($value_array as $type => $name) {
					$checked = userCan($permission[$type_permission][$type], $row[$type_row]);
					$template->assignBlockVars('permis.type', array('TYPE' => $type, 'CHECKED' => $checked));	
				}
				
			}		
		//	$i++;	
			$k++;
		}
	}
	
	$template->assignVars(array(
		'MODE' 					=> $mode,
		'FORUMS'				=> $type_permission == 'forum',
		'L_MANAGE_PERMISSION'	=> _t('Gestion des permissions'),
		'L_PREVIOUS'			=> _t('Précédent'),
		'L_INTERACTION'			=> _t('Interaction'),
		'L_ALL_CHECK'			=> _t('Tout cocher'),
		'L_ALL_UNCHECK'			=> _t('Tout décocher'),
		'L_MODO'				=> _t('Modérateur'),
		'L_ROLE'				=> _t('Rôle'),
		'L_MEMBER_WITHOUT_POLL'	=> _t('Membre [Sans sondage]'),
		'L_MEMBER'				=> _t('Membre'),
		'L_VISITOR'				=> _t('Visiteur'),
		'L_VALID'				=> _t('Valider')
		
	));

	
}

function searchPermissionForum($array, $left_id, $forum_id) {
	for ($i=0 ; $i < sizeof($array) ; $i++) {
		if ($array[$i]['left_id'] == $left_id && $array[$i]['forum_id'] == $forum_id) {
			return $array[$i]['permission'];
		}
	}
	return 0;
}

function typePermission($type_permission) {
	if ($type_permission == 'users') {
	$text_permission = array(
		'Message' => array(
				'profile_sig'			=>  _t('Editer sa signature'),
				'user_email'			=> 	_t('Editer son adresse email'),
				'user_name'				=> 	 _t('Editer son pseudonyme'),
				'user_password'			=> 	 _t('Changer de mot de passe')
				),
		'Profil' => array(
				'user_avatar'			=> _t('Changer son avatar'),
				//'user_viewprofile'		=> _t('Voir les profils'),
				'user_mask_status'		=> _t('Masquer son statut'),
				//'user_min_avatar'		=> _t(' créer une miniature pour son avatar')
			),
		'Message privé' => array(
				//'pm_edit'				=> _t('Editer ses messages privés'),
				'pm_read'				=> _t('Lire ses messages privés'),
				'pm_send'				=> _t('Envoyer des messages privés')
			),
		'Modération' => array(
			'user_ban'				=> _t('Bannir un utilisateur'),
			'user_avert'			=> _t('Avertir un utilisateur'),
			'user_read_comment'		=> _t('Lire les commentaires sur les membres'),
			'add_del_user_group'	=> _t('Ajouter/Supprimer des membres d\'un groupe'),
			'read_reports'			=> _t('Lire les rapports envoyés')
			),
		'Administration' => array(
				// 'admin_permission'		=> _t('éditer les permissions des membres'),
				 'admin_style'			=> _t('Editer le design')
				// 'admin_group'			=> _t('créer/modifier des groupes'),
				// 'admin_delete_user'		=> _t('supprimer un utilisateur'),
				// 'admin_add_forum'		=> _t('ajouter des forums'),
				// 'admin_del_forum'		=> _t('supprimer des forums'),
				// 'admin_edit_forum'		=> _t('editer des forums')
			)	
		);
	}
	elseif ($type_permission == 'forum') {
		$text_permission = array(
		'Global' => array(
			'forum_view'			=> _t('Voir le forum'),
			'topic_new'				=> _t('Créer un nouveau sujet'),
			'topic_read'			=> _t('Lire un sujet'),
			'topic_reply'			=> _t('Répondre à un sujet'),
			//'topic_icon'			=> _t('Assigner une icône à un sujet'),
			'msg_delete'			=> _t('Supprimer ses messages'),
			'msg_edit'				=> _t('Editer ses messages'),
			'poll_create'			=> _t('Créer un sondage'),
			'poll_vote'				=> _t('Voter un à sondage'),
			'poll_edit'				=> _t('Editer son sondage')
		),
		'Modération' => array(
			'modo_msg_report'		=> _t('Envoyer un rapport'),
			'modo_topic_lock'		=> _t('Verrouiller le sujet'),
			'modo_msg_edit'			=> _t('Editer un message'),
			'modo_msg_delete'		=> _t('Supprimer un message'),
			'modo_poll_edit'		=> _t('Editer les sondages'),
			'modo_topic_merge'		=> _t('Fusionner le sujets'),
			'modo_topic_split'		=> _t('Diviser le sujet'),
			'modo_topic_delete'  	=> _t('Supprimer un sujet'),
			'modo_topic_move'  		=> _t('Déplacer un sujet'),
			'modo_topic_sticky'  	=> _t('Mettre le sujet en post-it'),
			'modo_topic_annonce'  	=> _t('Mettre le sujet en annonce'),
			'modo_topic_global'  	=> _t('Mettre le sujet en normal'),
			// 'modo_view_logs'  		=> _t('Voir les logs'),
			// 'modo_topic_copy' 		=> _t('Copier le sujet'),
			'modo_move_corbeile' 	=> _t('Déplacer le sujet à la corbeille'),
			'modo_msg_information' 	=> _t('Voir les informations sur le message'),
			'modo_post_lock' 		=> _t('Poster sur un sujet malgré le verrouillage')
		));
	}
	return $text_permission;
	
}

function panelPlugin() {
	global $template, $db;
	$result = $db->select(PLUGINS);
	$i=0;
	while ($data = $result->fetch_array()) {
		$row = array(	
			'NAME' 		=> $data['plugin_name'], 
			'DESC' 		=> strRaccourci($data['plugin_desc'], 100),
			'AUTHOR'	=> $data['plugin_author'], 	
			'VERSION'	=> $data['plugin_version'], 
			'ID'		=> $data['plugin_id'],
			'ID_UPDATE'	=> $data['plugin_id_update'],
			'POSITION'	=> $i,
			'FILENAME'	=> $data['plugin_filename'],
			'AUTOLOAD'	=> $data['plugin_autoload'],
			'IS_ACTIV'	=> $data['plugin_activ'] == 1
		);	
		$template->assignBlockVars('plugins', $row);
		$i++;
	}
	
	$template->assignVars(array(
		'L_PLUGINS'			=>	_t('Extensions'),
		'L_INSTALL_PLUGIN'	=>	_t('Plugin en cours de téléchargement d\'installation. Veuillez patienter.'),
		'L_CONFIRM_DELETE'	=>	_t('Etes vous sûr de supprimer cet extension ?'),
		'L_YOUR_PLUGINS'	=>	_t('Vos extensions'),
		'L_INSTALL_NEW_PLUGINS'	=> _t('Installer de nouvelles extensions'),
		'L_INSTALL_HELP'		=> _t('La liste des entensions installées. Pour activer une extension, vous avez deux démarches différentes : <br />- Vous cliquez sur "Activer"<br />- Vous suivez les instructions après le clic sur le bouton "Comment afficher ?". Le code collé dans le template activera automatiquement l\'extension<br />Pour désactiver, procéder l\'inverse de ci-dessus.'),
		'L_NAME'				=> _t('Nom'),
		'L_DESC'				=> _t('Description'),
		'L_AUTHOR'				=> _t('Auteur'),
		'L_VERSION'				=> _t('Version'),
		'L_INTERACTION'			=> _t('Interaction'),
		'L_UNACTIV'				=> _t('Désactiver'),
		'L_ACTIV'				=> _t('Activer'),
		'L_UNACTIVED'			=> _t('Désactivé'),
		'L_HOW_DISPLAY'			=> _t('Comment afficher ?'),
		'L_UPDATE'				=> _t('Mettre à jour'),
		'L_DELETE'				=> _t('Supprimer'),
		'L_HOW_DISPLAY_TITLE'	=> _t('Instruction pour afficher l\'extension sur le forum'),
		'L_HOW_DISPLAY_TITLE'	=> _t('Instruction pour afficher l\'extension sur le forum'),
		'L_INSTALL_TITLE'		=> _t('Installation d\'une nouvelle extension'),
		'L_DELETE_TITLE'		=> _t('Suppression de l\'extension'),
		'L_OK'					=> _t('Ok'),
		'L_CANCEL'				=> _t('Annuler'),
		'L_CLOSE'				=> _t('Fermer'),
		'L_INSTALL'				=> _t('Installer'),
		'L_PLUGIN_ACTIV'		=> _t('L\'extension a été activé avec succès'),
		'L_PLUGIN_NO_ACTIV'		=> _t('L\'activation de l\'extension a échouée'),
		'L_PLUGIN_UNACTIV'		=> _t('L\'extension a été désactivé avec succès'),
		'L_PLUGIN_NO_UNACTIV'	=> _t('La désactivation de l\'extension a échouée')
		
		
	));
	
}

function menuPlugins() {
	global $template, $db;
	$result = $db->select(PLUGINS, 'plugin_activ=1 OR plugin_autoload=0');
	$i = 0;
	while ($data = $result->fetch_array()) {
		$row = array(	
			'TITLE' 		=> $data['plugin_name'], 
			'NAME'			=> $data['plugin_filename']
		);	
		$template->assignBlockVars('menu_plugins', $row);
		$i++;
	}
	
	$template->assignVars(array(
		'NB_PLUGINS'			=> $i
	));
	
	
}

function userCan($permission, $user_permission) {
		return ($permission & hexdec($user_permission)) == $permission;
}

$manifest = '';
function intallPlugin($plugin_name) {
  global $db, $manifest;
  $f = fopen(PHPCFORUM . '/plugin_files/' . $plugin_name . '.zip','r'); 
  $f2 = fopen('../plugins/' . $plugin_name . '.zip', 'a'); 
   while ($r=fread($f,8192) ) { 
     fwrite($f2,$r); 
   } 
   fclose($f);
   fclose($f2);
   
   
   dezip('../plugins/' . $plugin_name . '.zip', '../plugins/');
   
   $filesql = '../plugins/' . $plugin_name . '/install.sql';
   if (file_exists($filesql)) {
		executeQueryFile($filesql);
		unlink($filesql);
   }
   
    $parser =  xml_parser_create();
  	xml_set_character_data_handler($parser, "pluginManifestData");
  	$str = file_get_contents('../plugins/' . $plugin_name . '/manifest.xml');
  	xml_parse($parser, $str);
  	xml_parser_free($parser);
  	$manifest = preg_replace("#\n#", "", $manifest);
  	$manifest = preg_replace("#,$#", "", $manifest);
  	$manifest = $manifest;
 	$db->query('INSERT INTO ' . PLUGINS . ' (plugin_name, plugin_desc, plugin_author, plugin_version, plugin_autoload, plugin_filename) VALUES ('. $manifest . ', "' . $plugin_name . '")');
   
  	
 		

   
}

function dezip($filepath, $dest) {
   $filezip = $filepath;
   $zip = new PclZip($filezip);
   $list = $zip->extract(PCLZIP_OPT_PATH, $dest);
   unlink($filezip);
}



function pluginManifestData($parser, $data) {
	global $manifest;
	$udata = urlencode($data);
	if ($udata != "%0A%09" && $udata != "%0A") {
		$manifest .=  '"' . utf8_decode($data) . '",';
	}
}

function displayHowInstallPlugin($plugin_name) {
	$file_txt = '../plugins/' . $plugin_name . '/how_install.txt';
	if (file_exists($file_txt)) {
		$str = nl2br(htmlspecialchars(utf8_encode(file_get_contents('../plugins/' . $plugin_name . '/how_install.txt'))));
		return $str;
    }
}

function deletePlugin($plugin_name) {
	global $db;
	$dir = '../plugins/' . $plugin_name;
	if (is_dir($dir)) {
		$file = new File($dir);
		$file_uninstall = '../plugins/' . $plugin_name . '/uninstall.sql';
		if (file_exists($file_uninstall)) {
			executeQueryFile('../plugins/' . $plugin_name . '/uninstall.sql');
		}
		$db->delete(PLUGINS, array('plugin_filename' => $plugin_name), true);
		$file->clear();
		return 1;
	}
	return 0;
}

function executeQueryFile($filesql, $prefix = 'phpc_') {
	global $db;
	$query = file_get_contents($filesql);
	$array = explode(";\n", $query);
	$b = 1;
	for ($i=0; $i < count($array) ; $i++) {
		$str = $array[$i];
		$str = preg_replace('#{prefix_}#', $prefix, $str);
		if ($str != '') {
			$str .= ';';
			$b &= $db->query($str);	
		}	
	}
	
	return $b;
}

function submitTmpDesign() {
	return changeTmpDesign(false);
}

function initTmpDesign() {
	return changeTmpDesign(true);
}

function changeTmpDesign($ini) {
	global $db;
	$selector = array('backgroundColor', 'backgroundImage', 'borderColor', 'color', 'backgroundRepeat', 'fontSize', 'height', 'width', 'borderSize', 'opacity');
	$array = array();
	for ($i=0 ; $i < sizeof($selector) ; $i++) {
		$array[$selector[$i] . ($ini ? '_m' : '')] = '=' . $selector[$i] . ($ini ? '' : '_m');
	}
	$result = $db->selector();
	$b = true;
	while ($data = $result->fetch_array()) {
		$b &= $db->update(DESIGN, $array, 'selector_name="' . $data['selector_name'] . '"', true);
		
	}
	return $b;

}

function updateLangs() {
	global $db, $template, $manifest;
	
	
	
	$open = @opendir('../' . DIR_LANGS);
	$dirs = array();
	while($file = readdir($open)) {
		if ($file != '.' && $file != '..') {
			if (is_dir('../' . DIR_LANGS . '/' . $file)) {
				$dirs[] = $file;
			}
		}
	}
	
	closedir($open);
	
	$result = $db->select(LANGS, null, null, null);
	$lang = array();
	$remove_lang = array();
	$j = 0;
	while($data = $result->fetch_assoc()) {
		if (!is_dir('../' . DIR_LANGS . '/' . $data['lang_longid'])) {
			$b = $db->delete(LANGS, array('lang_id' => $data['lang_id']));
			if ($b) {
				$remove_lang[] = $data['lang_longid'];
				$template->assignBlockVars('lang_remove', array('NAME' => $data['lang_longid']));
			}
		}
		else {
			$lang[] = $data['lang_longid'];
		}
		$j++;
	}
	
	$add_lang = array();
	for ($i=0 ; $i < count($dirs) ; $i++) {
		if(!in_array($dirs[$i], $lang)) {
			 $parser =  xml_parser_create();
			 xml_set_character_data_handler($parser, "manifestXml");
			 $str = file_get_contents('../' . DIR_LANGS . '/' . $dirs[$i] . '/' . $dirs[$i] . '.xml');
			 xml_parse($parser, $str);
			 xml_parser_free($parser);
			 $manifest = preg_replace("#\n#", "", $manifest);
			 $manifest = preg_replace("#,$#", "", $manifest);
			 $manifest = utf8_decode($manifest);
			 $b = $db->query('INSERT INTO ' . LANGS . ' (lang_longid, lang_name, lang_author, lang_version, lang_install_time) VALUES ("' . $dirs[$i] . '", ' . $manifest . ', ' . time() . ')');
			 if ($b) {
			 	$add_lang[] = $dirs[$i];
			 	$template->assignBlockVars('lang_add', array('NAME' => $dirs[$i]));
			 }
		}
	}
	
	
	$nb_real_dir = count($dirs);
	$n_db_dir = $j;
	$update = $nb_real_dir != $n_db_dir;
	
	$template->assignVars(array(
		'UPDATE_LANG'	=> $update,
		'ADD_LANG'		=> !empty($add_lang),
		'REMOVE_LANG'	=> !empty($remove_lang)
		)
	);
	
}

function manifestXml($parser, $data) {
	global $manifest;
	$udata = urlencode($data);
	if ($udata != "%0A%09" && $udata != "%0A") {
		$manifest .=  '"' . $data . '",';
	}
}

function update($last_version) {
	global $db;
	// Création du dossier de mise à jour si inexistant
	$dir_update = '../update';
	if (!file_exists($dir_update)) {
		mkdir($dir_update);
	}
	// Téléchargement de la mise à jour
	$path_file = '../update/phpcforum-' . $last_version . '.zip';
	$f = fopen(PHPCFORUM . '/update/phpcforum-' . $last_version . '.zip','r'); 
	$f2 = fopen($path_file, 'a'); 
	while ($r=fread($f,8192) ) { 
	   fwrite($f2,$r); 
	} 
	fclose($f);
	fclose($f2);
	// Dezip du fichier dans le dossier de mise à jour
	dezip($path_file, $dir_update . '/');
	// Déplacement et écrasement des fichiers par les nouveaux fichiers
	$dir = new File($dir_update);
	$dir->movePath('..', array('update-sql', 'exec-update.php'));
	$dirsql = $dir_update . '/update-sql';
	$open = @opendir($dirsql);
	// Recherche des derniers fichiers SQL
	$filesql = array();
	while($file = readdir($open)) {
		if ($file == '.' || $file == '..') continue;
		if (preg_match('#-([0-9]+(\\.[0-9]+)?)#', $file, $match)) {
			$filesql[$dirsql . '/' . $file] = floatval($match[1]);
		}
	}
	asort($filesql);
	//pr($filesql);
	// Exécution des fichiers SQL
	foreach ($filesql as $path => $version) {
		if (VERSION < $version) {
			executeQueryFile($path);	
		}
	}
	closedir($open);
	// Exécution d'un script de mise à jour si besoin
	if (file_exists($dir_update . '/exec-update.php')) {
		include($dir_update . '/exec-update.php');
	}
	// Effacement des restes de la mises à jour.
	$dir->clear(false);
	
}


function panelDashBoard() {
	global $config, $db, $template;
	$template->assignVars(array(
		'ADM_NOTE' 				=> $config['admin_note'],
		'DIR_INSTALL_EXIST'		=> file_exists('../install') ? _t('Important : Le dossier "install" est toujours présent sur votre forum. Veuillez le supprimer !') : '',
		'L_UPDATE'				=> _t('La mise à jour de phpC Forum est en train de s\'effectuer. Veuillez patienter.'),
		'L_NO_QUIT'				=> _t('Ne quittez pas cette page avant le rechargement automatique du panneau d\'administration !'),
		'L_DEL_DIR_INSTALL'		=> _t('Supprimer le dossier "install"'),
		'L_DASHBOARD'			=> _t('Tableau de bord'),
		'L_NEWS_PHPCFORUM'		=> _t('Actualité de phpC Forum'),
		'L_SHARE_NOTE'			=> _t('Notes partagés entre administrateur'),
		'L_SAVE'				=> _t('Enregistrer'),
		'L_UPDATE_PHPCFORUM'	=> _t('Mise à jour de phpC Forum'),
		'L_DELETE_DIR_INSTALL'	=> _t('Le dossier "install" a été supprimé avec succès'),
		'L_NOT_DELETE_DIR_INSTALL'	=> _t('la suppression du dossier "install" a échoué'),
		'L_EDIT_NOTE'			=> _t('Les notes ont été changées avec succès'),
		'L_NOT_EDIT_NOTE'		=> _t('La modification des notes a échoué'),
		'L_WRITE_BY'			=> _t('Rédigé par')
	));
}

function changeUserToAnonymous($user_id) {
	global $db;
	$db->update(TOPICS, array(
		'topic_poster' 	=> ANONYMOUS_ID
	), 'topic_poster="' . $user_id . '"');
	$db->update(POSTS, array(
		'poster_id' 	=> ANONYMOUS_ID
	), 'poster_id="' . $user_id . '"');
	$db->update(PM, array(
		'author_id' 	=> ANONYMOUS_ID
	), 'author_id="' . $user_id . '"');
	$db->update(PM_TO, array(
		'user_id' 	=> ANONYMOUS_ID
	), 'user_id="' . $user_id . '"');
	$db->update(PM_TO, array(
		'author_id' 	=> ANONYMOUS_ID
	), 'author_id="' . $user_id . '"');
	
}


function deleteInstall() {
	$b = 0;
	if (file_exists('../install')) {
		$file = new File('../install');
		$b = $file->clear();
	}
	return $b;
}

function panelTemplates() {
	global $template, $db;
	
	$result = $db->select(TEMPLATES);
	while($data = $result->fetch_assoc()) {
		$tpl_title = $data['template_title'];
		$templates_title = array(
			'L_TPL_CONFIRM'				=> _t('Confirmation'),
			'L_TPL_FORUM_LIST'			=> _t('Liste des forums'),
			'L_TPL_VIEWTOPIC'			=> _t('Voir liste des sujets ou un sujet'),
			'L_TPL_HEADER'				=> _t('Entête'),
			'L_TPL_FOOTER'				=> _t('Bas de page'),
			'L_TPL_ERROR'				=> _t('Erreur'),
			'L_TPL_FORUM_DESALLOW'		=> _t('Forum désactivé'),
			'L_TPL_FORUM_JUMP'			=> _t('Choisir un forum'),
			'L_TPL_INDEX'				=> _t('Index'),
			'L_TPL_MEMBERS'				=> _t('Membres'),
			'L_TPL_POSTING'				=> _t('Poster un message'),
			'L_TPL_AVERTS'				=> _t('Gestion des avertissements'),
			'L_TPL_PROFILE'				=> _t('Profil'),
			'L_TPL_COMMENTS'			=> _t('Gestion des commentaires'),
			'L_TPL_FORGET_PASS'			=> _t('Oublie de mot de passe'),
			'L_TPL_LOGIN'				=> _t('Se connecter'),
			'L_TPL_MANAGE_PROFILE'		=> _t('Gestion du profil'),
			'L_TPL_PM'					=> _t('Message privé'),
			'L_TPL_POST_DETAILS'		=> _t('Détail d\'un message'),
			'L_TPL_REGISTER'			=> _t('Inscription'),
			'L_TPL_REPORT'				=> _t('Reporter'),
			'L_TPL_RSS'					=> _t('Flux RSS'),
			'L_TPL_SEARCH'				=> _t('Rechercher'),
			'L_TPL_VIEWFORUM'			=> _t('Voir les forums')
		);
		$row = array(
			'TITLE' 		=> $templates_title[$tpl_title],
			'ID'		 	=> $data['template_id']
		);
		$template->assignBlockVars('template', $row);
		
	}
	
	$template->assignVars(array(
		'U_TEST_TPL'				=> makeUrl('../index.php', array('test' => 'templates')),
		'L_TPL_MODIFIED'			=>  _t('Le Template a été modifié avec succès'),
		'L_TPL_NOT_MODIFIED'		=>  _t('La modification du Template a échoué'),
		'L_TPL_SUBMIT'				=>	_t('Les Templates ont été appliqués sur le forum'),
		'L_TPL_NOT_SUBMIT'			=>	_t('Les Templates n\'ont pas été appliqués sur le forum'),
		'L_TEMPLATES'				=>	_t('Templates'),
		'L_INTERACTION'				=>	_t('Interaction'),
		'L_SUBMIT_TPL'				=>	_t('Appliquer la modification des Templates sur le forum'),
		'L_TEST'					=>	_t('Tester sur le forum'),
		'L_MANAGE_TPL'				=>	_t('Gestion des templates')	
	));
}

function selectTemplate($tpl_id) {
	global $db;
	$data = $db->select(TEMPLATES, 'template_id=' . $tpl_id, null, null, '*', 'one');
	if ($data['template_content'] == '') {
		$data['template_content'] = htmlDoubleQuote(file_get_contents('../styles/' . STYLE . '/templates/' . $data['template_filename']));
		$db->update(TEMPLATES, array(
			'template_content'	=> $data['template_content']
		), 'template_id=' . $tpl_id);
	}
	array_walk_recursive($data, 'tplEncode');
	return json_encode($data);
}

function tplEncode(&$item, $key) {
	$item = htmlDoubleQuoteRev($item);
}

function updateTemplate($tpl_id, $content) {
	global $db;
	return $db->update(TEMPLATES, array(
		'template_content' 			=> '="' . htmlDoubleQuote($content) . '"',
		'template_date_modified'	=> '=' . time(),
		'template_nb_modified'		=> '=template_nb_modified+1',
	), 'template_id=' . $tpl_id, true);
}

function changeTemplates() {
	global $config, $db;
	$result = $db->select(TEMPLATES, 'template_date_modified > ' . $config['templates_changed']);
	$b = true;
	while($data = $result->fetch_assoc()) {
		// PHP 5
		$b = file_put_contents('../styles/' . STYLE . '/templates/' . $data['template_filename'], htmlDoubleQuoteRev($data['template_content']));
	}
	$db->update(CONFIG, array(
			'config_value'	=> time()
	), 'config_name="templates_changed"');
	if ($b === false) {
		return 0;
	}
	return 1;
}

function settingIconset($mode) {
	global $db;
	$json = array();
	$input = array(
		'iconset_name' => array(
			'type'		=>	'string'	
		),
		'iconset_mandatory'	=> array(
			'type'		=>	'int'	
		),
	);
	$form = new Forms_Validate($input);
	$v = $form->isValidate();
	if ($v) {
		if ($mode == 'add') {
			$json = $form->insertDb($db, ICONSET);
		}
		else {
			$iconset_id = requestVarPost('iconset_id', 0);
			$b = $form->updateDb($db, ICONSET, 'iconset_id=' . $iconset_id);
			$json = array('success' => $b);
		}
	}
	return $json;
}

function deleteIconset($iconset_id) {
	global $db;
	$b = $db->delete(ICONSET, array('iconset_id' => $iconset_id));
	$b &= $db->delete(ICONS, array('iconset_id' => $iconset_id));
	return array('success' => $b);
}

function deleteIcon($icon_id) {
	global $db;
	$b = $db->delete(ICONS, array('icon_id' => $icon_id));
	return array('success' => $b);
}

function uploadIcon() {
	global $db;
	$tab_safe = array('PNG', 'JPG', 'JPEG', 'GIF');
	$file = requestVarFile('Filedata', '');
	$dir_icon = '../images/icons';
	if (!file_exists($dir_icon)) {
		mkdir($dir_icon);
	}
	$upload = new Upload($file, $dir_icon);
	if(in_array($upload->type(), $tab_safe)) {
		$new_name = md5(microtime()) . '.' . $upload->type(true);
		if ($upload->uploadMoveFile($new_name)) {	
			return $new_name;	
		}
	}
	return "";
}

function settingIcon($mode) {
	global $db;
	$json = array();
	$iconset_id = requestVarPost('setting_iconset_id', 0);
	$input = array(
		'icon_name' => array(
			'type'		=>	'string'	
		),
		'icon_width'	=> array(
			'type'		=>	'int'	
		),
		'icon_height'	=> array(
			'type'		=>	'int'	
		),
		'icon_display' => array(
			'type'		=>	'int'	
		)
	);
	if ($mode == 'add') {
		$data = $db->select(ICONS,'iconset_id=' . $iconset_id, 'icon_position DESC', null, 'icon_position', 'one');
		$input_add = array(
			'icon_path' => array(
				'type'		=>	'string'	
			),
			'icon_position' => array(
				'type'		=>	'int'
			),
			'iconset_id' => array(
				'type'		=>	'string',	
				'const'		=>  $iconset_id
			)
		);
		$input_add['icon_position']['const'] = $data['icon_position']+1;
		$input = array_merge($input, $input_add);
	}
	
	$form = new Forms_Validate($input);
	$v = $form->isValidate();
	if ($v) {
		if ($mode == 'add') {	
			$json = $form->insertDb($db, ICONS);
			$path = '../images/icons/' .  $form->input['icon_path']['value'];
			$path = str_replace("\n","", $path);
			$json['icon_path'] = $path;
			$img = new Image($path);
			$json['icon_min'] = $img->height() > ICON_HEIGHT || $img->width() > ICON_WIDTH;
			$json['icon_name'] = $form->input['icon_name']['value'];
			$json['iconset_id'] = $iconset_id;
		}
		else {
			$icon_id = requestVarPost('setting_icon_id', 0);
			$b = $form->updateDb($db, ICONS, 'icon_id=' . $icon_id);
			$json = array('success' => $b);
		}
	}
	return $json;
}

function positionIcons($icon) {
	global $db;
	$b = 1;
	for ($i=0 ; $i < count($icon) ; $i++) {
		$b &= $db->update(ICONS, array('icon_position' => ($i+1)), 'icon_id=' . $icon[$i]);
	}
	return array('success' => $b);
}
?>