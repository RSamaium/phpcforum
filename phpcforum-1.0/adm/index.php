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
require_once('../includes/adm/adm_commons.php');
$page = requestVar('page', 'index_body');
$mode = requestVar('mode', '');
$tab = requestVar('t', 0);

$post_submit = requestVarPost('submit', '');
$submit = false;
$submit_name = array('submit_msg', 'submit_global', 'submit_forum');
if (in_array($post_submit, $submit_name )) {
	$submit = true;
}


if ($user->in_admin_panel) {

	menuPlugins();
	
	switch($page) {
		case 'permission':
			panelPermission($mode);
			$tpl_name = $page;
		break;
		case 'permission_list':
			panelPermission($mode);
			$tpl_name = $page;
		break;
		case 'messages':
			$getcrud = $page;
			$tpl_name = $page;
		break;
		case 'users':
			$getcrud = $page;
			$tpl_name = $page;
		break;
		case 'groups':
			$getcrud = $page;
			$tpl_name = $page;
		break;
		case 'forums':
			panelForums();
			$tpl_name = $page;
		break;
		case 'portail':
			$tpl_name = $page;
			panelDashBoard();
		break;
		case 'setting_forums':
			$getcrud = $page;
			$tpl_name = 'crud_forums';
		break;
		case 'plugins':
			panelPlugin();
			$tpl_name = $page;
		break;
		case 'templates':
			panelTemplates();
			$tpl_name = $page;
		break;
		case 'design':
			panelDesign();
			panelDesignImageSet();
			$tpl_name = $page;
		break;
		case 'group':
			$getcrud = $page;
			$tpl_name = 'crud_groups';
		break;
		case 'setting_user':
			$getcrud = $page;
			$tpl_name = $page;
		break;
		case 'forumlist_jump':
			$db->dataForums($user);
			$tpl_name = $page;
			$forum_selected = requestVarPost('forum_selected', '');
			if ($forum_selected == '') {
				$array = array();
			}
			else {
				/*if (is_string($forum_selected)) {
					$forum_selected = array($forum_selected);
				}*/
				$array = array($forum_selected);
			}
			$order = $config['order_forums'];
			displayListForum($order, $array);
		break;
		case 'global_setting':
			updateLangs();
			panelLangs();
			$tpl_name = $page;
			$getcrud = 'global_setting';
		break;
		default: 
			$getcrud = 'global_setting';
			$tpl_name = $page == 'global_setting' ? $page : 'index_body';
		break;
	}
}

switch ($getcrud) {
	case 'global_setting':
		panelGlobalSetting($submit);
	break;
	case 'messages':
		panelMessages($submit);
		panelIcons();
	break;
	case 'users':
		panelUsers();
		panelUserInactive();
		panelUsersBan();
		panelUsersAvert();
	break;
	case 'groups': 
		panelGroups();
	break;
	case 'setting_forums':
		 panelForumCrud($mode, $submit);
	break;
	case 'group':
		 panelGroupCrud($mode, $submit);
	break;
	case 'setting_user':
		panelSettingUser();
	break;
}

pageHeader($template, 'Panneau d\'administration');
$theme = $user->loadCookie('theme', 'phpcforum_adm');
$template->assignVars(array(
		'MODE' 							=> $mode,
		'API_KEY'						=> $config['api_key'],
		'THEME'							=> isset($theme) ? $theme : 'smoothness',
		'U_DECONNEXION'					=> makeUrl('../profile.php', array('mode'	=> 'dc')),
		'TAB'							=> $tab,
		'L_TABLE_DISPLAY_NB_DATA'		=>	sprintf(_t('Afficher %s données par page'), '_MENU_'),
		'L_TABLE_NO_DATA'				=>	_t('Aucune donnée'),
		'L_TABLE_INFO'					=>	sprintf(_t('Affichage %s à %s sur %s données au total'), '_START_', '_END_', '_TOTAL_'),
		'L_TABLE_INFO_EMPTY'			=>	_t('Aucune donnée à afficher'),
		'L_TABLE_INFO_FILTERED'			=>	sprintf(_t('(filtered from  %s total records)'), '_MAX_'),
		'L_TABLE_SEARCH'				=>	_t('Rechercher'),
		'L_TABLE_FIRST'					=>	_t('Premier'),
		'L_TABLE_LAST'					=>	_t('Dernier'),
		'L_TABLE_NEXT'					=>	_t('Suivant'),
		'L_TABLE_PREVIOUS'				=>	_t('Précédent'),
		'L_LOADING'						=>	_t('Chargement ...'),
		'L_DASHBOARD'					=>	_t('Tableau de bord'),
		'L_GLOBAL'						=>	_t('Général'),
		'L_FORUMS'						=>	_t('Forums'),
		'L_MESSAGES'					=>	_t('Messages'),
		'L_USERS'						=>	_t('Utilisateurs'),
		'L_GROUPS'						=>	_t('Groupes'),
		'L_PERMISSIONS'					=>	_t('Permissions'),
		'L_DESIGN'						=>	_t('Design'),
		'L_TEMPLATES'					=>	_t('Templates'),
		'L_EXTENSIONS'					=>	_t('Extensions'),
		'L_YOUR_EXTENSIONS'				=>	_t('Vos extensions'),
		'L_YOUR_THEMES'					=>	_t('Thème actuel'),
		'L_API_KEY'						=>	_t('Clé API'),
		'L_DEFAULT'						=>	_t('défaut')
		
		
	)
);

if (!$submit) {
	$template->setTemplate('adm_' . $tpl_name . '.html');
}
?> 
