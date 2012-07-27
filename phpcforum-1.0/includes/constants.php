<?php
//-------- Style ------ //

define('ADM_FORM_XML', 'forms.xml');
define('DIR_PLUGINS', 'plugins');
define('DIR_LANGS', 'languages');
define('DIR_PLUGINS_TEMPLATES', 'styles/templates');
define('DIR_JS',  'js');
define('DIR_STYLES',  'styles');
define('PRX_AJAX',  'ajax_');
define('PATH_AVATAR', 'images/avatars');

define('ADM_STYLE', 'default');
define('PATH_ADM', 'adm/styles/' . ADM_STYLE . '/templates');
define('COLOR_PICKER', '../../adm/styles/' . ADM_STYLE . '/images/colorpicker/');

// ---- Config. Templates ---- //
define('LIMIT_IMBRIQUE_BEGIN', 10);

// Tables de la base de données//
define('FORUMS', $prefix  . 'forums');
define('TOPICS', $prefix  . 'topics');
define('POSTS', $prefix  . 'posts');
define('USERS', $prefix  . 'users');
define('USERS_GROUP', $prefix  . 'users_group');
define('GROUPS', $prefix  . 'groups');
define('USERS_PERMISSION', $prefix  . 'users_permission');
define('GROUPS_PERMISSION', $prefix  . 'groups_permission');
define('SESSIONS', $prefix  . 'sessions');
define('PM', $prefix  . 'pm');
define('PM_TO', $prefix  . 'pm_to');
define('REPORTS', $prefix  . 'reports');
define('REPORTS_REASONS', $prefix  . 'reports_reasons');
define('USERS_AVERTS', $prefix  . 'users_averts');
define('USERS_BAN', $prefix  . 'users_ban');
define('POLL_VOTES', $prefix  . 'poll_votes');
define('POLL_OPTIONS', $prefix  . 'poll_options');
define('CONFIG', $prefix  . 'config');
define('SEARCH_TAGS', $prefix  . 'search_tags');
define('DESIGN', $prefix  . 'design');
define('PLUGINS', $prefix  . 'plugins');
define('LANGS', $prefix  . 'langs');
define('DESIGN_IMAGESET', $prefix  . 'design_imageset');
define('LOGS', $prefix  . 'logs');
define('TOPICS_READ', $prefix  . 'topics_read');
define('TEMPLATES', $prefix  . 'templates');
define('ICONS', $prefix  . 'icons');
define('ICONSET', $prefix  . 'iconset');

define('ANONYMOUS_ID', 1);
define('FONDATOR_ID', 2);
define('GROUP_ADMIN_ID', 1);
define('GROUP_VISITOR_ID', 2);
define('GROUP_MEMBER_ID', 3);
define('GROUP_MODO_ID', 4);
define('MIN_CHAR_POST', 10);
define('MAX_CHAR_SIG', 1500);
define('MAX_DESTINATAIRE_PM', 10);
define('PER_PAGE_PM', 25);
define('PER_PAGE_REPORTS', 25);
define('PER_PAGE_POSTS', 10);
define('PER_PAGE_TOPICS', 10);
define('MAX_CHAR_SPLIT', 20);
define('QK_PROFILE_NB', 6);
define('QK_PROFILE_WIDTH', 35);
define('PAGE_DOT_DIFF', 4);
define('NB_PAGE_DISPLAY', 4);
define('FIRST_WORDS', 25);
define('WORDS', 10);
define('PHPCFORUM', 'http://phpcforum.com');
define('VERSION', 0.97);
?>