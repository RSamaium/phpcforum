<?php
header('Content-type: text/html; charset=UTF-8'); 

require_once('../includes/functions.php');
require_once('../includes/class/database.class.php');
require_once('../includes/class/file.class.php');
require_once('../includes/libs/gettext.inc');
require_once('../includes/adm/adm_functions.php');
require_once('functions_install.php');


$action = requestVarPost('f', '');
$data = $_POST;

$domain = requestVarPost('lang', 'fr_FR');
T_setlocale(LC_MESSAGES, $domain);
bindtextdomain($domain, '../languages');
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);

switch($action) {
	case 'writeConfigFile':
		echo writeConfigFile($data);
	break;
	case 'createTable': 
		$db = new DataBase($data['db_login'], $data['db_pass'], $data['db_name'], $data['db_server']);
		echo executeQueryFile('schema/mysql.sql', $data['db_prefix']);
	break;
	case 'insertData':
		$db = new DataBase($data['db_login'], $data['db_pass'], $data['db_name'], $data['db_server']);
		echo insertData($data, $domain);
	break;
	case 'deleteFile':
		echo deleteFile($data['admin_email'], $data['admin_login'], $data['admin_pass'], $data['root']);
	break;
	case 'connectToDb':
		echo json_encode(errorData($data));
	break;
}

?>