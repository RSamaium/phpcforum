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
//----- Chemin ------ //
$time_start = microtime(true);
header('Content-type: text/html; charset=UTF-8'); 


require_once('config.php');

if (!INSTALL) {
	header('Location: install');
}

if (!isset($ajax)) {
	$ajax = false;
}


session_start();

require_once('includes/constants.php');
require_once('includes/functions.php');

 error_reporting(E_ERROR | E_WARNING | E_PARSE);
// error_reporting(0);
// set_error_handler("onError");

require_once('includes/class/file.class.php');

//require_once(ROOT . 'includes/class/xml.class.php');
require_once('includes/class/image.class.php');
require_once('includes/class/miniature.class.php');
require_once('includes/class/upload.class.php');


require_once('includes/class/database.class.php');

require_once('includes/class/user.class.php');

include('includes/class/global_plugins.class.php');
include('includes/class/adm_plugins.class.php');
include('includes/class/plugins.class.php');
require_once('includes/class/templates.class.php');

require_once('includes/class/forms_validate.class.php');

require_once('includes/functions_posts.php');
require_once('includes/functions_profile.php');
require_once('includes/functions_user.php');
require_once('includes/functions_display.php');

require_once('includes/libs/gettext.inc');


$template = new Templates();
$db = new DataBase(DB_USER, DB_PASS, DB_MAIN, DB_SERVER);
$db->setResourceDb($db);

$user = new User($db, $_SESSION);
$permission = globalPermission();

$config = array();
$result = $db->query('SELECT * FROM ' . CONFIG);
while ($data = $result->fetch_array()) {
	$value = $data['config_value'];
	if (in_array($value, array('email_validation_text', 'forget_pass_text', 'avert_new_mp_text', 'avert_new_reply_text'))) {
		$value = htmlDoubleQuoteRev($value);
	}
	$config[$data['config_name']] = $value;
}

define('STYLE', $config['style']);
define('WINDOWS_CONFIRM', 'styles/' . STYLE . '/templates/windows_confirm');
define('PATH_IMG', 'styles/' . STYLE . '/images/');

$db->loadImageSet();

$result = $db->plugins();
while ($plugin = $result->fetch_array()) {
	$template->setPlugin($plugin['plugin_filename'], $ajax);
}

$domain = $config['lang_default'];
T_setlocale(LC_MESSAGES, $domain);
bindtextdomain($domain, ($user->pageIsAdm() ? '../' : '') .  'languages');
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);


$template->addJs('js', array(
	'jquery/jquery/jquery-1.4.3.min',
	'jquery.jqDock.min',
	'jquery/jquery/jquery.tools.min',
	'jquery.autocomplete-min',
));
?>