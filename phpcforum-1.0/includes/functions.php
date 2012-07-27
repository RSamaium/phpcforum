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

function _t($text) {
	//$text = utf8_encode($text);
	return gettext($text);
}

function _nt($text1, $text2, $int) {
	global $config;
	if ($config['lang_default'] == 'fr_FR' && $int == 0) {
		$int = 1;
	}
	return ngettext($text1, $text2, $int);
}


function _tp($text) {
	return _t($text);
}

function to_utf8(&$item, $key) {
	$item = utf8_encode($item);
}

function pageHeader($template, $page_title, $rediret = true) {
	global $db, $user, $permission, $config;
	$change_design = requestVar('cd', 0, 'unsigned int');
	$change_design = $change_design != 0 && $user->isCan($permission['users']['admin_style']);
	if ($change_design) {
		admChangeDesign();
	}
	
	$activ_code = requestVar('v', '');
	if ($activ_code != '') {
		$user_activ = activCompte($activ_code);
	}
	else {
		$user_activ = false;
	}
	
	loadCssDesign($change_design);
	
	$is_anonymous = $user->data['user_id'] == ANONYMOUS_ID;
	$time = time();
	
	$pm_count = $db->pmCount($user->data['user_id']);
	$last_pm =  $db->pmLast($user->data['user_id'], $user->data['user_lastvisit']);
	
	growlPm($last_pm);
	
	if (!$user->isRegister())
		login();
	
	$new_report = false;
	if ($user->isCan($permission['users']['read_reports'])) {
		$result = reportsData('report_time > ' . $user->data['user_lastvisit'] . ' AND report_closed=0', true);
		$reports = 0;
		while ($data = $result->fetch_array()) {
			$template->assignBlockVars('newreports', viewReports($data));
			$reports++;
		}
		if ($reports > 0) {
			$new_report = true;
		}
		displayReportHeader();
		
	}

	if ($user->isRegister()) {
		$template->addJs('js', array(
			'jquery.gritter.min'
		));
	}
	
	$template->addJs('js', array(
			'jquery.lightbox-0.5.min'
		));

	
	$template->addCss('css', array(
			'styles'
	));
	
	
	
	$profile_width = 0;
	$is_can = array('read_reports', 'user_read_comment', 'user_avert');
	$size = sizeof($is_can);
	for ($i=0 ; $i < $size ; $i++) {
		if ($user->isCan($permission['users'][$is_can[$i]])) {
			$profile_width++;
		}
	}
	if ($user->isAdmin($permission)) {
		$profile_width++;
	}

	$template->assignVars(array(
		'CHANGE_DESIGN'			=> $change_design,
		'SITENAME'				=> $config['sitename'],
		'PATH_IMG'				=> PATH_IMG,
		'L_TITLE'				=> $page_title,
		'HEAD_DESCRIPTION'		=> $config['head_description'],
		'HEAD_KEYWORDS'			=> $config['keywords'],
		'HEAD_SUBJECT'			=> $config['head_subject'],
		'HEAD_AUTHOR'			=> $config['author'],
		'META_REFRESH'			=> $template->varExist('META_REFRESH') ? $template->getVar('META_REFRESH') :  '',
		'QUICK_MOD_TOOLS'		=> $template->varExist('QUICK_MOD_TOOLS') ? $template->getVar('QUICK_MOD_TOOLS') : '',
		'HAS_RSS'				=> $template->varExist('HAS_RSS') ? $template->getVar('HAS_RSS') : false,
		'LANG'					=> $config['lang'],
		'ROOT'					=> ROOT,
		'STYLE'					=> STYLE,
		'USER_LOGIN'			=> !$is_anonymous,
		'L_USER_MP'				=>  sprintf($pm_count['nb_pm'] > 1 ? _t('(%s) Nouveaux messages') : _t('(%s) Nouveau message'),  '<span class="highlight">' . $pm_count['nb_pm'] . '</span>'),
		'USERNAME'				=> $user->data['username'],
		'L_PROFILE'				=> 	_t('Profil'),
		'L_LOGIN'				=> 'Connexion',
		'L_DECONNEXION'			=>  _t('Déconnexion'),
		'L_INDEX'				=> _t('Index'),
		'L_REGISTER'			=> _t('S\'inscrire'),
		'L_MEMBERS_LIST'		=> 	_t('Membres'),
		'L_SEARCH'				=> 	_t('Rechercher'),
		'U_USER_MP'				=>	makeUrl('profile.php', array('mode' => 'pm')),
		'U_PROFILE'				=>  makeUrl('profile.php'),
		'U_LOGIN'				=>  makeUrl('profile.php', array('mode' => 'login')),
		'U_DECONNEXION'			=>  makeUrl('profile.php', array('mode' => 'dc')),
		'U_INDEX'				=> 	makeUrl('.'),
		'U_REGISTER'			=>	makeUrl('profile.php', array('mode' => 'register')),
		'U_MEMBERS_LIST'		=>  makeUrl('memberlist.php'),
		'U_SEARCH'				=>  makeUrl('search.php'),
		'U_FORGET_PASS'			=>  makeUrl('profile.php', array('mode' => 'forget_pass')),
		'CURRENT_DATE'			=> strTime(time(), 9),
		'NEW_REPORTS'			=> $new_report,
		'AUTO_COMPLETION'		=> $config['activ_auto_completion'],
		
		'U_PANEL_AVATAR' 				=> makeUrl('profile.php', array('mode' => 'avatar')),
		'L_PANEL_AVATAR' 				=> _t('Votre l\'avatar'),
		'U_PANEL_PROFILE_INFO' 			=> makeUrl('profile.php', array('mode' => 'profile_info')),
		'L_PANEL_PROFILE_INFO' 			=> _t('Editer les informations du profil'),
		'U_PANEL_PROFILE_SIG' 			=> makeUrl('profile.php', array('mode' => 'profile_sig')),
		'U_PANEL_PROFILE_PERSONAL' 		=> makeUrl('profile.php', array('mode' => 'profile_personal')),
		'U_PANEL_PROFILE_VIEW' 			=> makeUrl('profile.php', array('mode' => 'profile_view')),
		'U_PANEL_PROFILE_POST' 			=> makeUrl('profile.php', array('mode' => 'profile_post')),
		'U_PANEL_REPORT' 				=> makeUrl('report.php'),
		'U_PANEL_COMMENT' 				=> makeUrl('profile.php', array('mode' => 'comments')),
		'U_PANEL_AVERT' 				=> makeUrl('profile.php', array('mode' => 'averts')),
		'U_PANEL_PM' 					=> makeUrl('profile.php', array('mode' => 'pm')),
		'U_PANEL_PM_NEW' 				=> makeUrl('profile.php', array('mode' => 'pm', 'i' => 'new')),
		'L_PANEL_PROFILE_SIG'					=> _t('Votre signature'),
		'L_PANEL_PROFILE_PERSONAL'		=> _t('Éditer les réglages globaux'),
		'L_PANEL_PROFILE_POST' 			=> _t('Éditer la publication par défaut'),
		'L_PANEL_PROFILE_VIEW' 			=> _t('Éditer les options d’affichage'),
		'L_PANEL_REPORT'				=> _t('Rapports'),
		'L_PANEL_COMMENT' 				=> _t('Commentaires sur un utilisateur'),
		'L_PANEL_AVERT' 				=> _t('Avertissements'),
		'U_PANEL_ADMIN' 				=> makeUrl('adm', array()),
		'L_PANEL_ADMIN' 				=> _t('Accès au panneau d\'administration'),
	
		'I_ICON_AVATAR'					=> $db->image_set['icon_avatar']['filename'],
		'I_ICON_SIG'					=> $db->image_set['icon_sign']['filename'],
		'I_ICON_INFO'					=> $db->image_set['icon_setting_info']['filename'],
		'I_ICON_PERSONAL'				=> $db->image_set['icon_setting_personal']['filename'],
		'I_ICON_POST'					=> $db->image_set['icon_setting_post']['filename'],
		'I_ICON_VIEW'					=> $db->image_set['icon_setting_view']['filename'],
		'I_ICON_REPORT'					=> $db->image_set['icon_report']['filename'],
		'I_ICON_COMMENT'				=> $db->image_set['icon_comment']['filename'],
		'I_ICON_AVERT'					=> $db->image_set['icon_avert']['filename'],
		'I_ICON_ADMIN'					=> $db->image_set['icon_admin']['filename'],
	
		'I_LIGTH_BOX_LOADING'			=> $db->image_set['lightbox_loading']['filename'],
		'I_LIGTH_BOX_CLOSE'				=> $db->image_set['lightbox_close']['filename'],
		'I_LIGTH_BOX_PREV'				=> $db->image_set['lightbox_prev']['filename'],
		'I_LIGTH_BOX_NEXT'				=> $db->image_set['lightbox_next']['filename'],
		
		'QUICK_PROFILE_WIDTH'			=> QK_PROFILE_WIDTH * ($profile_width + QK_PROFILE_NB) + 50,
		'DISPLAY_QUICK_PROFILE'			=> $config['display_quick_profile'],
	
		'CAN_READ_REPORT' 				=> $user->isCan($permission['users']['read_reports']),
		'CAN_READ_COMMENT' 				=> $user->isCan($permission['users']['user_read_comment']),
		'CAN_READ_AVERT' 				=> $user->isCan($permission['users']['user_avert']),
		'CAN_CHANGE_SIGN'				=> $user->isCan($permission['users']['profile_sig']),
		'CAN_CHANGE_AVATAR'				=> $user->isCan($permission['users']['user_avatar']),
		'USER_IS_ADMIN'					=> $user->isAdmin($permission),
		'USER_ACTIV'					=> $user_activ,
	
		'L_FORGET_PASS'					=> _t('Mot de passe oublié ?'),
		'L_CONNECT_PERMANENT'			=> _t('Connexion permanente'),
		'L_CONNECT_INVISIBLE'			=> _t('Connexion invisible'),
		'L_USER'						=> _t('Nom d\'utilisateur'),
		'L_PASSWORD'					=> _t('Mot de passe'),
		'L_LOGIN_SUBMIT'				=> _t('Se connecter'),
	
		'L_ACTIV_TITLE'					=> _t('Activation de votre compte'),
		'L_IS_ACTIV'					=> _t('Votre compte est désormais activé !'),
		'L_CLOSE'						=> _t('Fermer'),
		'L_VERSION'						=> sprintf(_t('Version %s'), VERSION),
		'L_VIEW_REPORT'					=> _t('Voir le rapport'),
		'L_NEW_REPORT'					=> _t('Nouveaux rapports'),
		'VERSION'						=> VERSION,
		'U_PHPCFORUM'					=> PHPCFORUM
	
		)
	);
	
	$data = $user->dataBan();
	if ($rediret && !empty($data['ban_id']))
		if ($time > $data['ban_expire'] && $data['ban_date'] != $data['ban_expire'])
			deban($user->data['user_id']);
		else 
			redirect('desallowforum.php');
	$db->purgeTopicRead($config['interval_days_purge_topic_read']);		
	$db->update(USERS, array(
		'user_lastvisit' 	=> $time	
	), 'user_id=' . $user->data['user_id']);
	
	Plugins::flag('header');

}

function admChangeDesign() {
	global $template, $db;
	$template->addJs('js', array(
			'colorpicker',
			'jquery-ui',
			'adm_graphics'
		
		));
		$template->addCss('css', array(
			'adm/styles/default/jquery-ui-1.8.4.custom',
			'adm/styles/default/colorpicker',
			'adm/styles/default/editor'
		), true);
		$result = $db->select(DESIGN);
		$selector_type = array(
			0 => array(),
			1 => array()
		);
		while ($data = $result->fetch_array()) {
			$selector_type[$data['selector_type']][] = $data;
			
		}
		
		$name = array(
			'L_BODY'			=> _t('Fond'),
			'L_FOOTER'			=> _t('Bas de page'),
			'L_CATROW'			=> _t('Catégorie'),
			'L_HEADER_TITLE'	=> _t('Barre en entête'),
			'L_BANNER'			=> _t('Bannière'),
			'L_GLOBAL_CONTENT'	=> _t('Contenu globale'),
			'L_GLOBAL'			=> _t('Globalité'),
			'L_GLOBALBOX'		=> _t('Contenu niveau 1'),
			'L_GLOBALBOX_L2'	=> _t('Contenu niveau 2'),
			'L_TOOLTIP'			=> _t('Info bulle'),
			'L_TITLE'			=> _t('Titre'),
			'L_ARIANA'			=> _t('Fils d\'ariane'),
			'L_MEMBERLIST'		=> _t('Conteneur dans la liste des membres'),
			'L_CONFIRM'			=> _t('Fenêtre de confirmation'),
			'L_BUTTON_HEADER'	=> _t('Boutons en entête'),
			'L_REPORT_POPUP'	=> _t('Fenêtre des rapports'),
			'L_BODY_ID'			=> _t('Corps du forum'),
			'L_QUICK_MENU'		=> _t('Profil/Connexion rapide'),
			'L_POSTBOX'			=> _t('Messages d\'un sujet'),
			'L_BUTTON'			=> _t('Bouton'),
			'L_HEADER'			=> _t('Entête'),
			'L_HIGHTLIGHT'		=> _t('Exergue'),
			'L_LINK'			=> _t('Liens'),
			'L_SELECT'			=> _t('Sélecteur'),
			'L_BODY_L2'			=> _t('Corps du forum niveau 2'),
			'L_ABOVE'			=> _t('Entête du corps du forum niveau 2'),
			'L_PM_GRID'			=> _t('Liste des messages privés'),
			'L_PM_GRID_TITLE'	=> _t('Entête de la liste des messages privés')
		);

		foreach	($selector_type as $type => $data) {
			for ($i = 0 ; $i < count($data) ; $i++) {
				$row = array(
					'NAME' 		=> $name[$data[$i]['name']],
					'SELECTOR'	=> $data[$i]['selector_name'],
					'EXCEPTION'	=> $data[$i]['exception'] . ($data[$i]['selector_type'] == 0 ? ',activ' : ''),
					'ACTIV'		=> $data[$i]['selector_activ']
				);
				$name_var = $type == 0 ? 'global' : 'specific';
				$template->assignBlockVars('selector_' . $name_var, $row);	
			}
		}
		$template->assignVars(array(
			'L_ACTIV'				=> _t('Activer'),
			'L_WIN_TITLE'			=> _t('Editeur graphique'),
			'L_CONTAINERS'			=> _t('Conteneurs'),
			'L_ACTIV_CONTAINER'		=> _t('Activer la spécificité  du conteneur'),
			'L_BACKGROUND'			=> _t('Fond'),
			'L_COLOR'				=> _t('Couleur'),
			'L_NO'					=> _t('Aucune'),
			'L_PATH_IMG'			=> _t('URL de l\'image'),
			'L_REPEAT_X'			=> _t('Se répète sur X'),
			'L_REPEAT_Y'			=> _t('Se répète sur Y'),
			'L_NO_REPEAT'			=> _t('Ne se répète pas'),
			'L_TEXT'				=> _t('Texte'),
			'L_SIZE'				=> _t('Taille'),
			'L_PX'					=> sprintf(_t('%dpx'), 0),
			'L_OPACITY'				=>_t('Opacité'),
			'L_HEIGHT_CONTAINER'	=> _t('Hauteur du conteneur'),
			'L_WIDTH_CONTAINER'		=> _t('Largeur du conteneur'),
			'L_BORDER'				=> _t('Bordure'),
			'L_CONTAINER_GLOBAL'	=> _t('Conteneur global'),
			'L_CONTAINER_SPECIFIC'	=> _t('Conteneur spécifique')
			
		));
}

function loadCssDesign($cd) {
	global $db, $template;
	
	
	$str = $cd ? "_m" : "";
	$css =  "/**
	Design Auto Generate
*/
";
	$result = $db->selector();
	$selector_type = array(
		0 => array(),
		1 => array()
	);
	while ($data = $result->fetch_array()) {
		$selector_type[$data['selector_type']][] = $data;
		
	}

	foreach	($selector_type as $type => $row) {
		for ($i = 0 ; $i < count($row) ; $i++) {
			$data = $row[$i];
			if ($data['selector_activ']) {
					if ($data['color' . $str] != "") {
						$css .=  $data['selector_name'] . ' > span {';
							$css .= ' color: #' . $data['color' . $str] . ';';
						$css .= '} ';
						$css .=  $data['selector_name'] . ' > a {';
							$css .= ' color: #' . $data['color' . $str] . ';';
						$css .= '} ';
						
					}
					if ($data['selector_name'] == '.button_header') {
						$css .=  $data['selector_name'] . ' a {';
							if ($data['color' . $str] != "") {
								$css .= ' color: #' . $data['color' . $str] . ';';
							}
							if ($data['fontSize' . $str] > 0) {
								$css .= ' font-size:  ' . $data['fontSize' . $str] . 'px;';
							}
						$css .= '} ';
					}
					
					$css .=  $data['selector_name'] . '{';
					$css .= 'background-color:  ' . ($data['backgroundColor' . $str] == "" ? 'transparent' : '#' . $data['backgroundColor' . $str]) . ';';
					$bg_img = $data['backgroundImage' . $str];
					if ($bg_img != "") {
						$css .= 'background-image:  ' . ($bg_img == 'none' ? 'none' : 'url(' . $bg_img . ')') . ';';
					}
					if ($data['backgroundRepeat' . $str] != "") {
						$css .= ' background-repeat:  ' . $data['backgroundRepeat' . $str] . ';';
					}
					$css .= 'border:   ' . $data['borderSize' . $str] . 'px solid ' . ($data['borderColor' . $str] != '' ? '#' . $data['borderColor' . $str] : '') . ';';
					if ($data['color' . $str] != "") {
						$css .= ' color: #' . $data['color' . $str] . ';';
					}
					if ($data['fontSize' . $str] > 0) {
						$css .= ' font-size:  ' . $data['fontSize' . $str] . 'px;';
					}
					if ($data['opacity' . $str] > 0) {
						$css .= 'opacity:  ' . ($data['opacity' . $str] / 100) . ';';
					}
					if ($data['height' . $str] > 0) {
						$css .= 'height:  ' . $data['height' . $str] . 'px;';
					}
					if ($data['width' . $str] > 0) {
						$css .= 'width:  ' . $data['width' . $str] . 'px;';
					}
				$css .= '	
				}
				';	
			}
		}
	}
	
	
	$template->assignVars(array(
		'STYLE_GENERATE' => $css
	));
}

function growlPm($result) {
	global $template, $user;
	if (!$user->profileOptions('new_pm_popup')) {
		return;
	}
	while($data = $result->fetch_array()) {
		$text = strip_tags(strRaccourci($data['pm_text'], 140));
		$text = str_replace("\n", "", $text);
		$text = str_replace("\r", "", $text);
		$rowgrowl = array(
			'TITLE'		=> sprintf(_t('Nouveau MP - %s'), $data['pm_subject']),
			'TEXT'		=> $text,
			'AUTHOR'	=> $data['username']
		);
		$template->assignBlockVars('growl', $rowgrowl);
	}
}

function metaRefresh($nb_sec, $url) {
	global $template;
	$template->assignVars(array(
		'META_REFRESH' => '<meta http-equiv="refresh" content="' . $nb_sec . '; url=' . $url . '" />'
	));

}

function requestVar($get_name, $default, $type = null) {
	if (isset($_GET[$get_name]) and !empty($_GET[$get_name])) {
		$value = $_GET[$get_name];
		if (verifieValue($value, $type)) {
			return  $value;
		}
	}
	
	return $default;
}

function requestVarPost($post_name, $default, $type = null) {
	if (isset($_POST[$post_name])) {
		$value = $_POST[$post_name];
		if (is_string($value)) {
			//$value = utf8_decode($value);
		}
		if (verifieValue($value, $type))
			return $value;
	}
	return $default;
}

function requestVarFile($post_name, $default) {
	if (isset($_FILES[$post_name]['name']) and !empty($_FILES[$post_name]['name'])) {
		return $_FILES[$post_name];
	}
	return $default;
}

function verifieValue($value, $type) {
		if ($type == 'unsigned int' && $value > 0) 			
			return true;
		elseif ($type == 'int' && preg_match('#[0-9]+#', $value))
			return true;
		elseif ($type == null)
			return true;
		else
			return false;
}

function redirect($url) {
	header('Location: ' . $url);
}

function date_default($horaire = 'Europe/Paris') {
	 date_default_timezone_set($horaire);
}

function strRaccourci($str, $nb_carac){ 
	$carac_titre = strlen($str); // Retourne le nb de caractère
	if ($carac_titre > $nb_carac)
		$raccourci = substr($str, 0, $nb_carac) . '...';
	else
		$raccourci = $str;
			
	return $raccourci;		
}

function strTime($date, $option = null) {
	 date_default();
 
 $mois_array = array(_t('Janvier'), _t('Février'), _t('Mars'), _t('Avril'), 
 _t('Mai'), _t('Juin'), _t('Juillet'), _t('Août'), _t('Septembre'),
 _t('Octobre'), _t('Novembre'), _t('Décembre'));
		$mois = date('m', $date) - 1;
		$dates = date('d ', $date);
		$annees = date('Y ', $date);
		$heure = date('H\h i\m\i\n s\s', $date);
		$heure_minute = date('H\h i', $date);
 
	 switch ($option) {
	 case 1:  $echo = $dates . ' ' . $mois_array[$mois] . ' ' . $annees; break; // 25 Janvier 2008 
	 case 2:  $echo = $mois_array[$mois] . ' ' . $annees; break;  //Janvier 2008 
	 case 3:  $echo = $annees; break;  // 2008 
	 
	 case 4:  $echo = $dates . ' ' . $mois_array[$mois]; break;  // 25 Janvier
	 case 5:  $echo = $mois_array[$mois]; break;  // Janvier
	 
	 case 6 : $echo = $dates . ' ' . $mois_array[$mois] . ' ' . $annees . ' à ' . $heure; break; // 25 Janvier 2008 à 18h 06min 45s
	 
	 case 7 : $echo = $dates . '/' . ($mois + 1) . '/' . $annees . ' - ' . $heure_minute; break; // 25/12/2008 - 18h 06
	 
	 case 8 : $echo = $dates . ' ' . $mois_array[$mois] . ' ' . $annees . ' à ' . $heure_minute; break; // 25 Janvier 2008 à 18h 06
	 
	 case 9 : $echo = $dates . ' ' . $mois_array[$mois] . ' ' . $annees . ' - ' . $heure_minute; break; // 25 Janvier 2008 - 18h 06min
	 
	 
	 default: $echo = $dates . '/' . ($mois + 1) . '/' . $annees; // 25/12/2008
	 }
	 
 return $echo;
}

function onError($errno, $errmsg, $filename, $linenum, $vars) {
	 date_default();
	// Date et heure de l'erreur
    $dt = date("Y-m-d H:i:s (T)");
    $errortype = array (
                E_ERROR              => 'Erreur',
                E_WARNING            => 'Alerte',
                E_PARSE              => 'Erreur d\'analyse',
                E_NOTICE             => 'Note',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'Erreur spécifique',
                E_USER_WARNING       => 'Alerte spécifique',
                E_USER_NOTICE        => 'Note spécifique',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
                );
   
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
    
    $err = "<errorentry>\n";
    $err .= "\t<datetime>" . $dt . "</datetime>\n";
    $err .= "\t<errornum>" . $errno . "</errornum>\n";
    $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
    $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
    $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
    $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

    if (in_array($errno, $user_errors)) {
        $err .= "\t<vartrace>".wddx_serialize_value($vars,"Variables")."</vartrace>\n";
    }
    $err .= "</errorentry>\n\n";

	//error_log($err, 3, ROOT . 'logs/log_error.xml');
	die($errmsg . '<br />' . $filename . '<br />' . $linenum);

}

function globalPermission() {
	return  array(
			'users' => array(								// Peut ...
				'profile_sig'			=> 0x1,				// éditer sa signature
				'user_ban'				=> 0x2,				// bannir un utilisateur
				'user_avert'			=> 0x4,				// avertir un utilisateur
				'user_avatar'			=> 0x8,				// changer son avatar
				'user_email'			=> 0x10,			// éditer son adresse email
				'user_name'				=> 0x20,			// éditer son pseudo
				'user_password'			=> 0x40,			// changer de mot de passe
				'user_viewprofile'		=> 0x80,			// voir les profils
				'user_mask_status'		=> 0x100,			// masquer son statut
				'pm_edit'				=> 0x200,			// editer ses messages privés
				'pm_read'				=> 0x400,			// lire ses messages privés
				'pm_send'				=> 0x800,			// envoyer des messages privés
				'admin_permission'		=> 0x1000,			// éditer les permissions des membres
				'admin_style'			=> 0x2000,			// éditer le design
				'admin_group'			=> 0x4000,  		// créer/modifier des groupes
				'admin_delete_user'		=> 0x8000,			// supprimer un utilisateur
				'admin_add_forum'		=> 0x10000,			// ajouter des forums
				'admin_del_forum'		=> 0x20000, 		// supprimer des forums
				'admin_edit_forum'		=> 0x40000,			// editer des forums
				'user_min_avatar'		=> 0x80000,			// créer une miniature pour son avatar
				'user_read_comment'		=> 0x100000,		// lire les commentaires sur les membres
				'add_del_user_group'	=> 0x200000, 		// ajouter/supprimer des membres d'un groupe
				'read_reports'			=> 0x400000 		// lire les rapports envoyés

				
			),
			'forum' => array(								// Peut sur le forum en question ...
				'forum_view'			=> 0x1,				// voir le forum
				'topic_new'				=> 0x2,				// créer un nouveau sujet
				'topic_read'			=> 0x4,				// lire un sujet
				'topic_reply'			=> 0x8,				// répondre à un sujet
				'topic_icon'			=> 0x10,			// assigner une icone à un sujet
				'msg_delete'			=> 0x20,			// supprimer ses messages
				'msg_edit'				=> 0x40,			// éditer ses messages
				'modo_msg_report'		=> 0x80,			// envoyer un rapport
				'modo_topic_lock'		=> 0x100,			// verrouiller le sujet
				'modo_msg_edit'			=> 0x200,			// éditer un message
				'modo_msg_delete'		=> 0x400,			// supprimer un message
				'flood_ignore'			=> 0x800,			// ignorer la limite de flood
				'poll_create'			=> 0x1000,			// créer un sondage
				'poll_vote'				=> 0x2000,			// voter un à sondage
				'poll_edit'				=> 0x4000,			// éditer son sondage
				'modo_poll_edit'		=> 0x8000,			// éditer les sondages
				'modo_msg_move'			=> 0x10000,			// déplacer un message du sujet
				'modo_topic_merge'		=> 0x20000,			// fusionner le sujets
				'modo_topic_split'		=> 0x40000,			// diviser le sujet
				'modo_topic_delete'  	=> 0x80000,			// supprimer un sujet
				'modo_topic_move'  		=> 0x100000,		// déplacer un sujet
				'modo_topic_sticky'  	=> 0x200000,		// mettre le sujet en post-it
				'modo_topic_annonce'  	=> 0x400000,		// mettre le sujet en annonce
				'modo_topic_global'  	=> 0x800000,		// mettre le sujet en normal
				'modo_view_logs'  		=> 0x1000000,		// voir les logs
				'modo_topic_copy' 		=> 0x2000000,		// copier le sujet
				'modo_move_corbeile' 	=> 0x4000000,		// déplacer le sujet à la corbeille
				'modo_msg_information' 	=> 0x8000000,		// voir les informations sur le message
				'modo_post_lock' 		=> 0x10000000		// Poster sur un sujet malgré le verrouillage
			)
				
		);
	}

function makeUrl($page, $vars = null, $rewrite = false) {
	$cd = requestVar('cd', 0);
	$test = requestVar('test', null);
	$url = '';
	$i = 0;
	if (isset($vars)) {
		foreach ($vars as $key => $value) {
			$url .= ($i == 0 ? '' : '&') . $key . '=' . $value;
			$i++;
		}
	}
	$str = $page;
	if ($vars != null || $cd) {
		$str .= '?';
		$str .= $url;
		if ($vars != null && $cd) {
			$str .= '&cd=1';
		}
		elseif ($vars == null && $cd) {
			$str .= 'cd=1';
		}
	}
	if ($test == 'templates') {
		$str .= (isset($vars) ? '&' : '?' ) . 'test=templates';
	}
	return $str;
}

function idIsExist(&$id, $type) {
	global $db;
	$table = FORUMS;
	switch($type) {
		case 'topic': $table = TOPICS; break;
		case 'forum': $table = FORUMS; break;
		case 'post': $table = POSTS; break;
		case 'user': $table = USERS; break;
		case 'group': $table = GROUPS; break;
		case 'report': $table = REPORTS; break;
	}
	
	if ($type == 'user' && $id == ANONYMOUS_ID) 
		return false;

	$result = $db->query('SELECT ' . $type . '_id FROM ' . $table. ' WHERE ' . $type . '_id="' . htmlspecialchars($id) . '"');
	$data = $result->fetch_array();
	if (!empty($data[$type . '_id'])) {
		$id = $data[$type . '_id'];
		return true;
	}
	return false;
}




function dataCheckBox($checkbox) {
	$in = '';
	for ($i=0 ; $i < sizeof($checkbox) ; $i++) {
		$in .= $checkbox[$i] . (sizeof($checkbox)-1 == $i ? '' : ', ');
	}
	return $in;
}

function logout() {
	global $user;
	$user->session_delete();
	$user->removeCookie();
	redirect('.');
}

function login() {
	global $db, $user, $error, $template;
	$username = requestVarPost('username', '');
	$password = requestVarPost('password', '');
	$viewonline = requestVarPost('user_viewonline', '');
	$autologin = requestVarPost('user_autologin', '');
	$submit = isset($_POST['submit_login']);
	
   $input = array(
		'username'	=>	array(
				'type'		=> 'string',
				'required'	=> _t('Veuillez un nom d\'utilisateur')
			),
		'password' => array(
			'type'		=> 'string',
			'required'	=> _t('Veuillez rentrer un mot de passe')
		)
	);
	$input_error = array(
		
	);
	
	$forms = new Forms_Validate($input, $input_error);
	$error = $forms->isValidate();

	if ($submit && empty($error)) {
			$data = $db->login($username, $password);
			if (isset($data['user_id'])) {
				//----- Vérifier les avertissements expirés --//
				synchronizedAvert($data['user_id']);
				//--------------------------------------------
				
				Plugins::flag('login', array($data));
				
				$db->update(USERS, array(
					'user_lastmark' => $data['user_lastvisit']	
				), 'user_id=' . $data['user_id']);
				
				$db->purgeTopicReadUser($data['user_id']);
				
				if ($viewonline != '') {
					$user->updateViewOnline(0);
				}
				if ($autologin != '') {
					$b = $user->updateAutoLogin(1);
				}
				$_SESSION['user_id'] = $data['user_id'];
				$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
				redirect('.');
			}
			else {
				$error[] = _t('Identifiant ou mot de passe incorrect');
			}
			
	}
	
	$template->assignVars(array(
		'LOGIN_USERNAME'			=> $username,
		'L_HELP_USERNAME'			=> _t('Votre nom d\'utilisateur rentré lors de l\'inscription'),
		'L_HELP_PASSWORD'			=> _t('Votre mot de passe rentré lors de l\'inscription'),
		'L_HELP_CONNECT_PERMANENT'	=> _t('La connexion est automatique dès l\'arrivée sur le forum. Ceci n\'est pas recommandé pour les ordinateurs partagés.'),
		'L_HELP_CONNECT_INVISIBLE'	=> _t('Vous ne serez pas ajouter à la liste des utilisateurs actifs.'),
		'L_HELP_SUBMIT'				=> _t('Connectez vous !'),
		'L_OPTIONS'					=> _t('Options')
		)
	);
	
	return $error;
	
}



function permissionForum($type, $group_id, $forum_id) {
	global $db;
	$table = '';
	switch($type) {
		case 'user' : 
			$table = USERS_PERMISSION;
		break;
		case 'group' : 
			$table = GROUPS_PERMISSION;
		break;
	}
	$result = $db->query('SELECT ' . $type . '_permission FROM . ' . $table . '
						  WHERE ' . $type . '_id=' . $group_id . ' AND forum_id=' . $forum_id);
	return $data = $result->fetch_array();
}


/*function synchronizedForum($forum_id) { 
	global $db;
	$result_topic = $db->query('SELECT COUNT(*) nb_topic FROM ' . TOPICS . ' 
						  WHERE forum_id=' . $forum_id);
	$data_topic = $result_topic->fetch_array();
	$result_post = $db->query('SELECT COUNT(*) nb_post FROM ' . POSTS . ' 
						  WHERE forum_id=' . $forum_id);
	$data_post = $result_post->fetch_array();
	
	$result	= $db->query('SELECT post_time, poster_id FROM ' . POSTS . ' 
						  WHERE forum_id=' . $forum_id . ' ORDER BY post_time DESC');
	$data   = $result->fetch_array();
	$db->query('UPDATE ' . FORUMS . ' 
				SET forum_nb_subject=' . $data_topic['nb_topic'] . ', 
					forum_nb_post=' . $data_post['nb_post'] . ',
					forum_lastpost_time=' . $data['post_time'] . ', 
					forum_lastpost_user_id=' . $data['poster_id'] . '
				WHERE forum_id=' . $forum_id);
}*/

function viewQuickModTools($submit, $mod_post, $mod_tools, $checkbox) {
	global $db, $template, $user, $permission, $config;
	global $forum_id, $topic_id, $mod, $start;
			
			$mod = $mod_post != '' ? $mod_post : $mod;
			$confirm_text = $sql = '';
			$query = true;
			$execute_sql = true;
			
			$result_lock = $db->query('SELECT topic_status FROM ' . TOPICS . ' 
										  WHERE topic_id=' . $topic_id);
			$data_lock = $result_lock->fetch_array();
			
			if ($mod == 'msg_delete' && $user->isCanForum($permission['forum'][$mod], $forum_id)) {	
				$post_id = $checkbox;
				$data = $db->select(POSTS, 'post_id="' . htmlspecialchars($post_id) . '" AND poster_id=' . $user->data['user_id'], null, null, 'poster_id', 'one');
				if (isset($data['poster_id'])) {
					deletePost($forum_id, $topic_id, $post_id);
					
				}
			}
		
		
			if (array_key_exists($mod, $mod_tools) && $user->isCanForum($permission['forum'][$mod], $forum_id)) {	
					if ($submit)
						if ($topic_id > 0) {
							$where 	= ' WHERE topic_id=' . $topic_id . '
										AND forum_id='  . $forum_id;
						}
						else {
							$in = dataCheckBox($checkbox);
							$where 	= ' WHERE topic_id IN (' . $in . ') 
										AND forum_id='  . $forum_id;
						}

					$update =  'UPDATE ' . TOPICS;
					
					switch($mod) {
						case 'modo_topic_lock':
							if ($data_lock['topic_status'] == 0) {
								if ($submit) {
									$sql = $update . ' SET topic_status=1 ' . $where;
									
								}
							}
						   else {
							  if ($submit) {	
								$sql = $update . ' SET topic_status=0 ' . $where;
								
								}
						   }
						break;
						case 'modo_topic_merge':
							if ($submit) {
								if ($checkbox != '') {
									$merge_topic_id =  requestVarPost('modo_topic_merge', '');
									if (idIsExist($merge_topic_id , 'topic')) {
										$in = dataCheckBox($checkbox);
										$db->changeTopicId_post($merge_topic_id, $in);
										synchronizedTopic($topic_id);
										synchronizedTopic($merge_topic_id);
										$data_merge = $db->select(TOPICS, 'topic_id=' . htmlspecialchars($merge_topic_id), null, null, 'forum_id', 'one');
										if ($forum_id != $data_merge['forum_id']) {
											$db->update(POSTS, array('forum_id' => $data_merge['forum_id']), 'post_id IN (' . $in . ')');
											synchronizedForum($data_merge['forum_id']);
										}
										synchronizedForum($forum_id);
										
										$execute_sql = false;
									}
									else
										$query = false;
								}
								else
									$query = false;
							}
						break;
						case 'modo_topic_split':
							if ($submit) {
								if ($checkbox != '') {
									$in = dataCheckBox($checkbox);
									$result_split = $db->query('SELECT post_subject, poster_id, icon_id 
																FROM ' . POSTS . ' 
																WHERE post_id=' . $checkbox[0]);
									$data_split = $result_split->fetch_array();
									$split_topic_id = createNewTopic($forum_id, $data_split['post_subject'], $data_split['poster_id'], $data_split['icon_id'], $checkbox[0], end($checkbox));
									$db->changeTopicId_post($split_topic_id, $in);
									$db->synchronizedTopicReply($split_topic_id);
									synchronizedTopic($topic_id);
									/*$first_post = $db->firstPost($topic_id);
									$last_post = $db->lastPost($topic_id);
									$db->synchronizedPostInTopic($topic_id, $first_post, $last_post['post_id']);*/
									$execute_sql = false;
								}
								else
									$query = false;
							}
						break;
						case 'modo_topic_delete':
							if ($submit) {
								$sql = 'DELETE FROM ' . TOPICS . $where;

							}
						break;
						case 'modo_topic_sticky':
							if ($submit) {
								$sql = $update . ' SET topic_type=1 ' . $where;
							}
						break;
						case 'modo_topic_annonce':
							if ($submit) {
								$sql = $update . ' SET topic_type=2 ' . $where;
							}
						break;
						case 'modo_topic_global':
							if ($submit) {
								$sql = $update . ' SET topic_type=0 ' . $where;
							}
						break;
						case 'modo_move_corbeile':
						case 'modo_topic_move':
							if ($submit) {
								$forum_id_move =  $mod == 'modo_topic_move' ? requestVarPost('forumlist', 0) : $config['forum_corbeille'];
								if ($forum_id_move > 0) {
									$sql = $update . ' SET forum_id=' . $forum_id_move . ' ' . $where;
									if ($topic_id > 0) {
										$in = $topic_id;
									}
									else {
										$in = dataCheckBox($checkbox);
									}
									$db->changeForumId_post($forum_id_move, $in);
								}
								else {
									$query = false;
								}
							}
						break;
						case 'modo_msg_delete':
							$in = dataCheckBox($checkbox);
							deletePost($forum_id, $topic_id, $in);
							return;
						break;

					}
					
					if ($submit && $query) {
					
						if ($execute_sql) {
							$db->query($sql);
						}
						if (isset($forum_id_move)) {
							$u_forum_id = $forum_id_move;
						}
						else {
							$u_forum_id = $forum_id;
						}
						
						switch ($mod) {
							case 'modo_topic_delete':
								$db->query('DELETE FROM ' . POSTS . $where);
								$db->query('DELETE FROM ' . TOPICS_READ . $where);
								synchronizedForum($forum_id);	
								synchronizedGlobalStats();
							break;
							case 'modo_move_corbeile':
							case 'modo_topic_move':
								synchronizedForum($forum_id);
								synchronizedForum($forum_id_move);
							break;
							case 'modo_topic_split':
								synchronizedForum($forum_id);
							break;
						}
						
						if ($topic_id > 0) {
							$result_count = $db->query('SELECT COUNT(*) AS nb_post_present 
														FROM ' . POSTS . ' 
														WHERE topic_id=' . $topic_id);
							$data_count = $result_count->fetch_array();
							if ($data_count['nb_post_present'] == 0) {
									$db->query('DELETE FROM ' . TOPICS . $where);
									$db->query('DELETE FROM ' . TOPICS_READ . $where);
									$db->deleteTopicRead($topic_id);
							}
						$url_retour = makeUrl('viewtopic.php', array('f' => $u_forum_id, 't' => $topic_id, 'start' => $start));
						}
						else {
							$url_retour = makeUrl('viewtopic.php', array('f' => $u_forum_id, 'start' => $start));
						}
						etatSubmitConfirm(true, array(_t('La modification du sujet a bien été effectuée'), _t('La modification du sujet a échouée')), $url_retour);
						metaRefresh(3, $url_retour);
					}
				
					if (!$submit)
						$template->assignVars(array(
								'MOD_TOOLS'		=> $mod,
								'CONFIRM_TEXT'	=> $confirm_text
							)
						);
			}
			
			$can_user_mod_tool = false;
			$nb_tools = 0;
			
			
			if ($data_lock['topic_status'] == 1) {
				$mod_tools['modo_topic_lock'] = _t('Déverrouiller le sujet');
			}
			
			foreach ($mod_tools as $key => $value) {
				if ($user->isCanForum($permission['forum'][$key], $forum_id)) {
					$can_user_mod_tool = true;
					$nb_tools++;
				}
				else {
					$can_user_mod_tool = false;
				}

				$quick_mod_tools = array(
					'CAN_USE_MOD_TOOL'		=>  $can_user_mod_tool,
					'TOOL_NAME'				=>  $value,
					'OPTION_VALUE'			=>  $key
				);
				$template->assignBlockVars('quick_mod_tools', $quick_mod_tools);
			}
			
			displayListForum($config['order_forums']);
			$template->assignVars(array(
				'L_SUBJECT_MODO'		=> _t('Modération sur ce sujet'),
				'L_NUM_SUBJECT'			=> _t('Numéro du sujet'),
				'QUICK_MOD_TOOLS'		=> 	$nb_tools > 0,
				'L_MODO_LOCK'			=> $data_lock['topic_status'] == 1 ? _t('Etes vous sûr de vouloir déverrouiller ce sujet ?') : _t('Etes vous sûr verrouiller ce sujet ?'),
				'L_MODO_MSG_DELETE'		=> _t('Etes vous sûr de supprimer ce(s) message(s) ?'),
				'L_MODO_MOVE'			=> _t('Où voulez vous déplacer ce sujet ?'),
				'L_MODO_SPLIT'			=> _t('Etes vous sûr de vouloir diviser le sujet ?'),
				'L_MODO_STICKY'			=> _t('Etes vous sûr de mettre ce sujet en post-it ?'),
				'L_MODO_ANNONCE'		=> _t('Etes vous sûr de mettre ce sujet en  annonce ?'),
				'L_MODO_GLOBAL'			=> _t('Etes vous sûr de mettre ce sujet en normal ?'),
				'L_MODO_DELETE'			=> _t('Etes vous sûr supprimer ce sujet ?'),
				'L_MODO_MERGE'			=> _t('Etes vous sûr de fusionner les messages cochés avec un autre sujet ?'),
				'L_MODO_MOVE_CORBEILLE'	=> _t('Etes vous sûr de déplacer ce sujet vers la corbeille ?'),
				'L_TITLE_QUICK_MODO'	=> _t('Outils de modération')
				)
			);

}

function topicRow($row, $last_post_data, $topic) {
	global $user, $db, $config;
	//$topic_read = $user->getValueSession('topic_read');
	//$topic_state = $row['post_time'] <= $user->session_data['session_start'] || isset($topic_read[$row['post_id']]) ? 0 : 1;
	if ($user->isRegister()) {
		$topic_state = 	$row['post_time'] < $user->data['user_lastmark'] ? 0 : 
						($row['poster_id'] == $user->data['user_id'] ? 0 :
						 (isset($topic[$row['topic_id']]) && $topic[$row['topic_id']] == 1 ? 0 :
						1)); 
	}
	else {
		$topic_state = 0;
	}
					
	if ($row['topic_status'] == 1) {
		$topic_state = 2;
	}
	$icon = $db->icons[$row['topic_icon_id']];
	
	return array(
		'POSTER_NAME'			=>  $row['username'],
		'POSTER_IS_ANONYMOUS'	=>  $row['user_id'] == ANONYMOUS_ID,
		'U_POSTER'				=>  makeUrl('memberlist.php', array('mode'	=> 'viewprofile', 'u' => $row['user_id'])),
		'READ'					=>  $topic_state == 0 ? _t('Aucune nouveau message') : ($topic_state == 1 ? _t('Nouveau message') : _t('Sujet verrouillé')),
		'READ_IMG'				=>  $topic_state == 0 ? image('no_new_post') : ($topic_state == 1 ? image('new_post') : image('lock')),
		'GROUP_COLOR'			=>  $row['group_color'],
		'LAST_POST_TIME' 		=> 	strTime($row['post_time'], 8),
		'LAST_POST_AUTHOR'		=>  $last_post_data['username'],
		'LAST_POST_GROUP_COLOR'	=>  $last_post_data['group_color'],
		'LAST_POST_TEXT'		=>  $last_post_data['post_text'],
		'U_LAST_POST_POSTER'	=>  makeUrl('memberlist.php', array('mode'	=> 'viewprofile', 'u' => $last_post_data['user_id'])),
		'TITLE'					=>  $row['topic_title'],
		'TOPIC_ID'				=>  $row['topic_id'],
		'IS_POLL'				=>  $row['poll_title'] != '' && $config['display_label_poll'],
		'TIME'					=>  sprintf(_t('Publié le %s'), strTime($row['topic_time'], 8)),
		'NB_VIEW'				=>	sprintf(_nt('%d vue', '%d vues', $row['topic_view']), $row['topic_view']),
		'NB_REPLIES'			=>	sprintf(_nt('%d réponse','%d réponses', $row['topic_replies']), $row['topic_replies']),
		'U_LINK'				=>  makeUrl('viewtopic.php', array('f' => $row['forum_id'], 't' => $row['topic_id'])),	
		'ICON_EXIST'			=>  isset($icon),
		'ICON_NAME'				=> 	$icon['icon_name'],
		'ICON_PATH'				=> 	'images/icons/' . $icon['icon_path'],
		'ICON_WIDTH'			=> 	$icon['icon_width'],
		'ICON_HEIGHT'			=> 	$icon['icon_height']
		);
}

function strEtat($etat, $str_true, $str_false) {
	return $etat ? $str_true : $str_false;
}



function incrementeForumStats($forum_id, $type) {
	global $db;
	$result = $db->query('SELECT ' . $type . ' FROM ' . FORUMS . ' 
						   WHERE forum_id=' . $forum_id);
	$data = $result->fetch_array();
	return $db->query('UPDATE ' . FORUMS . ' SET ' . $type . '=' . ($data[$type]+1) . ' 
					   WHERE forum_id=' . $forum_id);
}

function htmlDoubleQuote($text) {
	return str_replace('"', '&quot;', $text);
}

function htmlDoubleQuoteRev($text) {
	return str_replace('&quot;', '"', $text);
}

function sendEmail($email, $html, $text, $subjet) {
	global $config;
	if (isset($config['email_default'])) {
		$from = $config['email_default'];
	}
	else {
		$from = $email;
	}
	//$from = $nom." <".$from.">";
	$limite = "_----------=_parties_".md5(uniqid (rand()));
	
	$header  = "Reply-to: ".$from."\n";
	$header .= "From: ".$from."\n";
	$header .= "X-Sender: <".$email.">\n";
	$header .= "X-Mailer: PHP\n";
	$header .= "X-auth-smtp-user: ".$from." \n";
	$header .= "X-abuse-contact: ".$from." \n";
	$header .= "Date: ".date("D, j M Y G:i:s O")."\n";
	$header .= "MIME-Version: 1.0\n";
	$header .= "Content-Type: multipart/alternative; boundary=\"".$limite."\"";

	$message = "";

	$message .= "--".$limite."\n";
	$message .= "Content-Type: text/plain\n";
	$message .= "charset=\"iso-8859-1\"\n";
	$message .= "Content-Transfer-Encoding: 8bit\n\n";
	$message .= $text;

	$message .= "\n\n--".$limite."\n";
	$message .= "Content-Type: text/html; ";
	$message .= "charset=\"iso-8859-1\"; ";
	$message .= "Content-Transfer-Encoding: 8bit;\n\n";
	$message .= $html; 


	$message .= "\n--".$limite."--";
	return mail($email, $subjet, $message, $header);
}

function pr($var, $name_var = '') {
	echo '<b>' . microtime() . ' - ' . $name_var . '</b>
	<pre>';
	print_r($var);
	echo '</pre>';
}

/*function var_name (&$iVar, &$aDefinedVars)
    {
    foreach ($aDefinedVars as $k=>$v)
        $aDefinedVars_0[$k] = $v;
 
    $iVarSave = $iVar;
    $iVar     =!$iVar;
 
    $aDiffKeys = array_keys (array_diff_assoc ($aDefinedVars_0, $aDefinedVars));
    $iVar      = $iVarSave;
 
    return $aDiffKeys[0];
    }*/
	
function threadAriana($forum_id, $thread) {
	global $config, $template, $db;
	$order = $config['order_forums'];
	$pos = 0;
	$test_pos = array('{', ';', '}');
	$find_str = false;
	$accolade = 0;
	$str_forum_id = '';
	
	for ($i=0 ; $i < sizeof($test_pos) ; $i++) {
		$val = strrpos($order, ':' . $forum_id . $test_pos[$i]);		
		if ($val !== false) {
			$pos = $val;
			break;
		}
	}
	
	while (!$find_str && $pos >= 0) {
		$pos--;
		if ($pos >= 0) {
			$c = $order[$pos];
		}
		if ($c == '}')
			$accolade++;
			
		if ($accolade == 0 && $c == '{') {
			if ($pos != 0) {
				while ($c != ':') {
					$pos--;
					$c = $order[$pos];
					$str_forum_id .= ($c != ':' ? $c : '');
				}
				$find_str = true;
				$str_forum_id = strrev($str_forum_id);
				$thread[] = $str_forum_id;
				$thread = threadAriana($str_forum_id, $thread);
			}
		}
		
		if ($c == '{')
			$accolade--;
	}
	
	if (!empty($thread)) {
		$thread = array_reverse($thread);
		return $thread;
	}

}

function strHighlight($str, $str_keywords) {
	$color_tag = array('FFFF88', 'C0FC8B', '99FDFB', 'C997FF', 'FE989A', 'E2E4B1', 'CDCDDA', 'FDC5AA');
	$keywords = preg_split("#[\s,;]+#", $str_keywords);
	$size = sizeof($keywords);
	$j = 0;
	for ($i=0 ; $i < $size ; $i++) {
		if ($j >= sizeof($color_tag)) {
			$j = 0;
		}
		$str = str_ireplace($keywords[$i], '<span style="background:#' . $color_tag[$j] . '">' . $keywords[$i] . '</span>', $str);
		$j++;
	}
	return $str;
}

function striptags($str) {
	$str = strip_tags($str, '<strong><u><i>');
	return $str;
}

function strExtract($str, $str_keywords) {
	$keywords = preg_split("#[\s,;]+#", $str_keywords);
	$size = sizeof($keywords);
	
	$str = striptags($str);
	$tab_word = extractWords($str);
	$size_words = sizeof($tab_word);
	
	$i_first = 0;
	$i_last = 0;
	for ($j=0 ; $j < $size ; $j++) {
		
		for ($i=0 ; $i < $size_words ; $i++) {
			if (!strcasecmp($tab_word[$i], $keywords[$j])) {
				$i_first = $i-WORDS;
				if ($i_first < 0) $i_first = 0;
				$i_last = $i+WORDS;
				if ($i_last > $size_words) $i_last = $size_words;
			}
		}
		
		if ($i_first == 0 && $i_last == 0) {
			return strExtractFirstWords($str);
		}

		$str = "";
		//pr($tab_word);
		for ($i=$i_first ; $i <= $i_last ; $i++) {
			$str .= $tab_word[$i] . " ";
		}
	}
	
	return '...' . $str . '...';
}

function extractWords($str) {    
    $replace = array(",",":","!","?","(",")","[","]","{","}","\"", " ");
    $separator = "[ ]+";
    $words = split($separator,trim(str_replace($replace, " ", $str))); 
    return $words;
}

function strExtractFirstWords($str) {
	preg_match("#([^\s]+(\s[^\s]+){1," . FIRST_WORDS . "})#", $str, $match);
	$str = $match[0] . ' ...';
	return $str;
}

function image($name) {
	global $db;
	return $db->image_set[$name]['filename'];
}

function activCompte($activ_code) {
	global $db;
	$activ_cod = htmlspecialchars($activ_code);
	$condition = 'user_activ_id="' . $activ_code . '" AND user_activ=0';
	$data = $db->select(USERS, $condition, null, null, 'user_id', 'one');
	if (isset($data['user_id'])) {
		$b = $db->update(USERS, array('user_activ' => 1), $condition);
		if ($b) {
			$db->totalChange('total_users');
		}
		return $b;
	}
	else {
		return false;
	}
}

function generateNewPass() {
	$str = 'abcdefghijklmnopkrstuvwxyz';
	$str_special = '&#%$:?!.,<>[{}]_+=*';
	$new_pass = '';
	for ($i=0 ; $i < 12 ; $i++) {
		$type_char = rand(0, 5);
		switch ($type_char) {
			// Number
			case 0:
			case 1:
				$char = rand(0, 9);
			break;
			// Special Char
			case 2:
				$char = $str_special[rand(0, strlen($str_special)-1)];
			break;
			// Char (Upper case, Lowercase)
			default:
				$char = $str[rand(0, strlen($str)-1)];
				if (rand(0, 1) == 1) {
					$char = strtoupper($char);
				}
		}
		$new_pass .= $char;
	}
	return $new_pass;
}

function addLog($operation, $type = 0, $data = '', $forum_id=0, $topic_id=0) {
	global $db, $user;
	return $db->addLog($operation, $data, $user, $type, $forum_id, $topic_id);
}

function replaceOption($text) {
	global $user, $config;
	if (!$user->profileOptions('display_img_post')) {
		$text = preg_replace('#<img.*?>#', '', $text);
	}
	else {
		$text = preg_replace('#<img.*?src="(.*?)".*?>#', '<a href="\1" class="lightbox">\0</a>', $text);
	}
	if (!$user->profileOptions('display_smilies_img')) {
		$text = preg_replace('#<img.*?images/smileys.*?>#', '', $text);
	}
	if (!$config['msg_flash']) {
		$text = preg_replace('#<object.*?</object>#', '', $text);
	}
	else {
		if (!$user->profileOptions('display_flash')) {
			$text = preg_replace('#<object.*?</object>#', '', $text);
		}
	}
	
	$text = preg_replace('#<script.*?</script>#', '', $text);
	return $text;
}

function synchronizedGlobalStats() {
	global $db;
	$data_post = $db->select(POSTS, null, null, null, 'COUNT(*) AS tpost', 'one');
	$data_topic = $db->select(TOPICS, null, null, null, 'COUNT(*) AS ttopic', 'one');
	$db->update(CONFIG, array(
		'config_value' 	=>	$data_post['tpost']
	), 'config_name="total_post"');
	$db->update(CONFIG, array(
		'config_value' 	=>	$data_topic['ttopic']
	), 'config_name="total_topic"');
}


function synchronizedAllForum() {

}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
	$url = 'http://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}





?>