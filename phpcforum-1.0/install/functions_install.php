<?php
function displayConfig($condition) {
	if ($condition) {
		echo '<img src="images/accept.png" alt="" />';
	}
	else {
		echo '<img src="images/exclamation.png" alt="" />';
	}
}

function displayRw($file) {
	$echo = '';
	$echo .= '<span style="color: ';
	if (is_readable($file)) {
		$echo .= 'green';
	}
	else {
		$echo .= 'red';
	}
	$echo .= '">' . _t('Lecture') . '</span>, ';
	
	$echo .= '<span style="color: ';
	if (is_writable($file)) {
		$echo .= 'green';
	}
	else {
		$echo .= 'red';
	}
	$echo .= '">' . _t('Ecriture') . '</span>';
	echo $echo;
}

function displayExtension($extension) {
	$echo = '';
	$echo .= '<span style="color: ';
	if (extension_loaded($extension)) {
		$echo .= 'green">' . _t('Oui');
	}
	else {
		$echo .= 'red">' . _t('Non');
	}
	$echo .= '</span>';

	echo $echo;
}

function displayErrorInstall($error) {
	if (empty($error)) return '';
	$echo = '';
	for ($i=0 ; $i < count($error) ; $i++) {
		$echo .= '<span style="color: red">' . $error[$i] . '</span><br />';
	}
	echo $echo;
}

function errorInstall() {
	global $page;
	$license = requestVarPost('license_ok', 0);
	$error = array();
	if ($page == 2 && $license != 1) {
		$page = 1;
		$error[] = _t('Vous devez accepter la licence pour continuer (cochez la case)');
	}
	return $error;
}

function errorData($data) {
	global $page;
	require_once('../includes/class/database.class.php');
	$db = new DataBase($data['db_login'], $data['db_pass'], $data['db_name'], $data['db_server']);
	$error = array();
	$code_error = $db->getErrno();

	if ($code_error == 2005) {
		$error[] = sprintf(_t('La base de données "%s"  ou le serveur "%s" n\'existe pas'), $data['db_name'],  $data['db_server']);
	}
	elseif ($code_error == 1045) {
		$error[] = sprintf(_t('Impossible d\'accéder la base de données avec le nom d\'utilisateur "%s" et le mot de passe'), $data['db_login']);
	}
	elseif ($code_error == 1046 || $code_error == 1049) {
		$error[] = sprintf(_t('Impossible d\'accéder la base de données "%s"'), $data['db_name']);
	}
	else {
		$error[] = $code_error;
	}
	if (empty($error)) {
		$page = 4;
	}
	else {
		$page = 3;
	}
	
	
	
	// array_walk_recursive($error, 'to_utf8');
	return array('error' => $error, 'code_error' => $code_error);
}

function displayRoot() {
	return preg_replace('#/install.*#', '', $_SERVER['PHP_SELF']);
}

function displayAjaxObject($data) {
	$echo = '';
	foreach ($data as $key => $value) {
		$echo .= $key . ': ' . '"' . $value . '",';
	}
	$echo = preg_replace('#,$#', '', $echo);
	return $echo;
}


function writeConfigFile($data) {
	$php = '<?php
	define(\'ROOT\', \'' . $data['root'] . '\');

	define(\'DB_MAIN\', \'' . $data['db_name'] . '\');
	define(\'DB_USER\', \'' . $data['db_login'] . '\');
	define(\'DB_PASS\', \'' . $data['db_pass'] . '\');
	define(\'DB_SERVER\', \'' . $data['db_server'] . '\');

	$prefix = \'' . $data['db_prefix'] . '\';

	define(\'INSTALL\', true);
	?>';
	$f =  fopen('../config.php','w'); 
	$b = fwrite($f, $php); 
	fclose($f);
	if ($b !== false) {
		return 1;
	}
	else {
		return 0;
	}
}

function insertData($data, $lang) {
	global $db;
	$prefix = $data['db_prefix'];
	$time = time();
	
	/*
		Insert Data Config
	*/
	
	$b = $db->insert($prefix . 'config', array(
		'config_name'	=> 'time_max_simult_user',
		'config_value' 	=> $time
	));

	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'lang_default',
		'config_value' 	=> $lang
	));
	
	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'time_max_simult_user',
		'config_value' 	=> $time
	));
	
	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'email_validation_text',
		'config_value' 	=> _t('Bienvenue sur le forum {SITE_NAME},  <br />Pour valider votre enregistrement, veuillez cliquer sur le lien ci-dessous ou copier ce code dans la barre URL de votre navigateur:    <br /> <br /> <a href=&quote;{U_VALID_REGISTER}&quote;>{U_VALID_REGISTER}</a>  <br /> <br />  Cordialement,<br /> L\'équipe de {SITE_NAME}')
	));
	
	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'forget_pass_text',
		'config_value' 	=> _t('Bonjour,  <br /><br /> Votre mot de passe a été changé. Voici le nouveau, notez-le soigneusement : <br /><br /> {NEW_PASS} <br /><br /> Cordialement,<br /> L\'équipe de {SITE_NAME}')
	));
	
	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'avert_new_mp_text',
		'config_value' 	=> _t('Bonjour,<br /> <br /> Vous avez reçu un nouveau message privé de la part de {AUTHOR}<br /> sur {SITE_NAME} et avez demandé à être averti dans ce cas.<br /> <br /> Vous pouvez voir votre nouveau message et y répondre en cliquant simplement sur le lien suivant :<br /> <a href=&quote;{U_BOX_MP}&quote;>{U_BOX_MP}</a><br /> <br /> Rappelez-vous que :<br /> <ul>        <li> Vous ne serez plus averti des nouveaux messages privés tant que vous ne vous serez pas connecté sur &quote;{SITE_NAME}&quote;</li>     <li> Vous pouvez choisir de ne plus être averti de l\'arrivée de nouveaux messages privés en modifiant l\'option appropriée dans votre profil.</li> </ul> <br /> Cordialement,<br /> L\'équipe de {SITE_NAME}<br />')
	));
	
	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'avert_new_reply_text',
		'config_value' 	=> _t('Bonjour,<br /> <br /> Vous avez reçu une réponse de la part de {AUTHOR}<br /> sur le sujet &quote;<strong>{TOPIC_NAME}</strong>&quote; et avez demandé à être averti dans ce cas.<br /> <br /> Vous pouvez voir la réponse et y répondre en cliquant simplement sur le lien suivant :<br /> <a href=&quote;{U_TOPIC}&quote;>{U_TOPIC}</a><br /> <br /> Rappelez-vous que :<br /> <ul>        <li> Vous ne serez plus averti des nouveaux messages privés tant que vous ne vous serez pas connecté sur &quote;{SITE_NAME}&quote;</li>        <li>Vous pouvez choisir de ne plus être averti de l\'arrivée des réponses en modifiant l\'option appropriée dans votre profil.</li> </ul> Cordialement,<br /> L\'équipe de {SITE_NAME} ')
	));

	$api_key = substr(md5(rand()), 4);
	$b &= $db->insert($prefix . 'config', array(
		'config_name'	=> 'api_key',
		'config_value' 	=> $api_key
	));
	
	/*
		Insert Data Post
	*/

	$b &= $db->insert($prefix . 'posts', array(
		'post_id'		=> 1,
		'topic_id' 		=> 1,
		'forum_id' 		=> 2,
		'poster_id' 	=> 2,
		'icon_id' 		=> 0,
		'poster_ip' 	=> $_SERVER["REMOTE_ADDR"],
		'post_time' 	=> $time,
		'post_subject' 	=> _t('Votre phpC Forum !'),
		'post_text' 	=> _t('<h1 style=&quot;text-align: center; &quot;>\r\n	Votre phpC Forum !</h1>\r\n<p>\r\n	Voici un test d&#39;un message. phpC Forum utilise l&#39;&eacute;diteur CKEditor afin de rendre le forum plus dynamique.</p>\r\n<p>\r\n	Pour commencer &agrave; administrer votre forum, veuillez cliquer sur &quot;Acc&egrave;s au panneau d&#39;administration&quot; dans votre profil et modifiez la configuration de votre forum.</p>\r\n<p>\r\n	Des mises &agrave; jour automatiques permettront de corriger des probl&egrave;mes sur le forum et d&#39;ajouter des nouvelles fonctionnalit&eacute;s. Les mises &agrave; jour interviennent lors d&#39;une connexion sur le panneau d&#39;administration.</p>\r\n<p>\r\n	Pour supprimer ce message, cliquez sur &quot;Supprimer&quot; en bas de ce message page.</p>\r\n<p>\r\n	N&#39;h&eacute;sitez pas &agrave; visiter le site officiel <a href=&quot;http://www.phpcforum.com&quot;>www.phpcforum.com</a> afin de recevoir de l&#39;aide, des tutoriels et s&#39;informer des nouveaut&eacute;s de phpC Forum.</p>\r\n<p>\r\n	Bonne administration&nbsp;<img alt=&quot;smiley&quot; src=&quot;images/smileys/regular_smile.gif&quot; title=&quot;smiley&quot; /></p>\r\n'),
		'post_checksum' 	=> '4e73746b2f5dc7712992819a34de1f1d',
		'post_edit_time' 	=> 0,
		'post_edit_reason' 	=> '',
		'post_edit_user' 	=> 0,
		'post_edit_count' 	=> 0
	));
	
	/*
		Insert Data Topic
	*/

	$b &= $db->insert($prefix . 'topics', array(
		'topic_id'				=> 1, 
		'forum_id'				=> 2, 
		'icon_id'				=> 0, 
		'topic_title'			=> _t('Votre phpC Forum !'), 
		'topic_poster'			=> 2, 
		'topic_time'			=> $time, 
		'topic_view'			=> 1, 
		'topic_replies'			=> 0, 
		'topic_status'			=> 0,
		'topic_type'			=> 0, 
		'topic_first_post_id'	=> 1, 
		'topic_last_post_id'	=> 1, 
		'poll_title'			=> '', 
		'poll_start'			=> 0, 
		'poll_length'			=> 0, 
		'poll_max_options'		=> 0,
		'poll_last_vote'		=> 0, 
		'poll_vote_change'		=> 0
	
	));

	/*
		Insert Data User
	*/
	
	$b &= $db->insert($prefix . 'users', array(
		'user_id'					=> 1, 
		'user_nb_message'			=> 0, 
		'user_type'					=> 1, 
		'group_id'					=> 2, 
		'user_permissions'			=> '', 
		'user_ip'					=> '', 
		'user_regdate'				=> $time-1,
		'username'					=> _t('Anonyme'), 
		'user_password'				=> '', 
		'user_email'				=> '', 
		'user_avatar'				=> '', 
		'user_website'				=> '', 
		'user_from'					=> '', 
		'user_birthday'				=> '', 
		'user_lastvisit'			=> $time, 
		'user_lastmark'				=> 0, 
		'user_lastpost_time'		=> 0,
		'user_avert'				=> 0, 
		'user_ban'					=> 0, 
		'user_last_avert'			=> 0, 
		'user_style'				=> '', 
		'user_rank'					=> 0, 
		'user_job'					=> '', 
		'user_msn'					=> '', 
		'user_hobbies'				=> '', 
		'user_sig'					=> '', 
		'user_yahoo'				=> '', 
		'user_skype'				=> '', 
		'user_facebook'				=> '', 
		'user_twitter'				=> '', 
		'user_sexe'					=> 'i', 
		'user_sig_options'			=> 0, 
		'user_options'				=> 'f81', 
		'user_language'				=> 0, 
		'user_comment'				=> '', 
		'user_activ'				=> 1, 
		'user_activ_id'				=> '', 
		'user_activ_reason'			=> 0
	
	));

	$b &= $db->insert($prefix . 'users', array(
		'user_id'					=> 2, 
		'user_nb_message'			=> 0, 
		'user_type'					=> 1, 
		'group_id'					=> 1, 
		'user_permissions'			=> '', 
		'user_ip'					=> $_SERVER["REMOTE_ADDR"], 
		'user_regdate'				=> $time,
		'username'					=> $data['admin_login'], 
		'user_password'				=> md5($data['admin_pass']), 
		'user_email'				=> $data['admin_email'], 
		'user_avatar'				=> '', 
		'user_website'				=> '', 
		'user_from'					=> '', 
		'user_birthday'				=> '0000-00-00', 
		'user_lastvisit'			=> $time, 
		'user_lastmark'				=> $time, 
		'user_lastpost_time'		=> $time,
		'user_avert'				=> 0, 
		'user_ban'					=> 0, 
		'user_last_avert'			=> 0, 
		'user_style'				=> '', 
		'user_rank'					=> 0, 
		'user_job'					=> '', 
		'user_msn'					=> '', 
		'user_hobbies'				=> '', 
		'user_sig'					=> '', 
		'user_yahoo'				=> '', 
		'user_skype'				=> '', 
		'user_facebook'				=> '', 
		'user_twitter'				=> '', 
		'user_sexe'					=> 'i', 
		'user_sig_options'			=> 0, 
		'user_options'				=> 'fb3', 
		'user_language'				=> 0, 
		'user_comment'				=> '', 
		'user_activ'				=> 1, 
		'user_activ_id'				=> '', 
		'user_activ_reason'			=> 0
	
	));
	
	/*
		Insert Data User Group
	*/

	$b &= $db->insert($prefix . 'users_group', array(
			'group_id'			=> 1, 
			'user_id'			=> 2, 
			'user_status'		=> 1, 
			'user_date_joined'  => $time
	
	));
	
	/*
		Insert Data Groups
	*/
	
	
	$b &= $db->insert($prefix . 'groups', array(
		'group_id'				=> 1,
		'group_founder_manage'	=> 2,
		'group_name'			=> _t('Administrateur'),
		'group_desc'			=> '',
		'group_rank'			=> 0,
		'group_color'			=> 'FF0000',
		'group_permissions'		=> '7fffff',
		'group_type'			=> 2
	));
	
	$b &= $db->insert($prefix . 'groups', array(
		'group_id'				=> 2,
		'group_founder_manage'	=> 0,
		'group_name'			=> _t('Visiteurs'),
		'group_desc'			=> '',
		'group_rank'			=> 0,
		'group_color'			=> '',
		'group_permissions'		=> '0',
		'group_type'			=> 0
	));
	
	$b &= $db->insert($prefix . 'groups', array(
		'group_id'				=> 3,
		'group_founder_manage'	=> 0,
		'group_name'			=> _t('Membres'),
		'group_desc'			=> '',
		'group_rank'			=> 0,
		'group_color'			=> '',
		'group_permissions'		=> 'd89',
		'group_type'			=> 0
	));
	
	$b &= $db->insert($prefix . 'groups', array(
		'group_id'				=> 4,
		'group_founder_manage'	=> 2,
		'group_name'			=> _t('Modérateur'),
		'group_desc'			=> '',
		'group_rank'			=> 0,
		'group_color'			=> '0000FF',
		'group_permissions'		=> '500dcd',
		'group_type'			=> 0
	));
	
	/*
		Insert Data Reports Reasons
	*/
	
	$b &= $db->insert($prefix . 'reports_reasons', array(
		'reason_id'				=> 1,
		'reason_title'			=> 'off_topic',
		'reason_text'			=> _t('Le message est hors sujet'),
		'reason_order'			=> 1
	));
	
	$b &= $db->insert($prefix . 'reports_reasons', array(
		'reason_id'				=> 2,
		'reason_title'			=> 'flood',
		'reason_text'			=> _t('Le message est du flood'),
		'reason_order'			=> 2
	));
	
	$b &= $db->insert($prefix . 'reports_reasons', array(
		'reason_id'				=> 3,
		'reason_title'			=> 'warez',
		'reason_text'			=> _t('Le message comporte des paroles ou des liens illégaux'),
		'reason_order'			=> 3
	));
	
	$b &= $db->insert($prefix . 'reports_reasons', array(
		'reason_id'				=> 4,
		'reason_title'			=> 'other',
		'reason_text'			=> _t('Autres types de problème.'),
		'reason_order'			=> 4
	));
	
	/*
		Insert Data Forums
	*/
	
	$b &= $db->insert($prefix . 'forums', array(
		'forum_id'				=> 1,
		'forum_name'			=> _t('Ma catégorie'),
		'forum_desc'			=> '',
		'forum_rules'			=> '',
		'forum_status'			=> 0,
		'forum_nb_subject'		=> 0,
		'forum_nb_post'			=> 0,
		'forum_last_post_id'	=> 0
	));
	
	$b &= $db->insert($prefix . 'forums', array(
		'forum_id'				=> 2,
		'forum_name'			=> _t('Mon forum'),
		'forum_desc'			=> _t('Un simple forum'),
		'forum_rules'			=> '',
		'forum_status'			=> 0,
		'forum_nb_subject'		=> 1,
		'forum_nb_post'			=> 1,
		'forum_last_post_id'	=> 1
	));
	
	$b &= $db->insert($prefix . 'forums', array(
		'forum_id'				=> 3,
		'forum_name'			=> _t('Corbeille'),
		'forum_desc'			=> '',
		'forum_rules'			=> '',
		'forum_status'			=> 1,
		'forum_nb_subject'		=> 0,
		'forum_nb_post'			=> 0,
		'forum_last_post_id'	=> 0
	));
	
	$b &= $db->insert($prefix . 'forums', array(
		'forum_id'				=> 4,
		'forum_name'			=> _t('Corbeille'),
		'forum_desc'			=> _t('Les sujets déplacés dans la corbeille ne sont pas visible par les visiteurs et les membres de votre forum.'),
		'forum_rules'			=> '',
		'forum_status'			=> 1,
		'forum_nb_subject'		=> 0,
		'forum_nb_post'			=> 0,
		'forum_last_post_id'	=> 0
	));
	
	$b &= executeQueryFile('schema/mysql_data.sql', $prefix);
	return $b;
}

function deleteFile($email, $login, $pass, $root) {
	$all_success = requestVarPost('all_success', 0);
	//$file = new File('.');
	//$b = $file->clear();
	$b = true;
	if ($all_success == 3 && $b) {
		$subjet = _t('Installation terminée de phpC Forum');
		$text = sprintf(_t('Félicitation %s, votre nouveau forum %s a été installé avec succès à l\'adresse :<br />
<br />
%s<br />
<br />
Vous pouvez vous connecter en tant qu\'administrateur avec les informations suivantes :<br />
<br />
Identifiant : %s<br />
Mot de passe : %s<br />
<br />
En cas de souci, n\'hésitez pas à communiquer votre problème sur le forum officiel de phpC Forum.<br />
<br />
Bonne continuation :)<br />
%s'), $login, 'phpC Forum Beta 1.7', '<a href="http://' . $_SERVER['HTTP_HOST']  . $root . '">http://' . $_SERVER['HTTP_HOST'] .  $root . '</a>', $login, $pass, '<a href="http://phpcforum.com">http://phpcforum.com</a>');
		sendEmail($email, $text, $text, $subjet);
	}
	return $b;
}

?>