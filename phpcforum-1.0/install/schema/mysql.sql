CREATE TABLE IF NOT EXISTS `{prefix_}config` (
  `config_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `config_value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}design` (
  `selector_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `selector_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `selector_type` tinyint(1) NOT NULL,
  `selector_activ` tinyint(1) NOT NULL,
  `name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `backgroundColor` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `backgroundColor_m` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `exception` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `backgroundImage` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `backgroundImage_m` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `borderColor` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `borderColor_m` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `color` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `color_m` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `backgroundRepeat` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `backgroundRepeat_m` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fontSize` tinyint(3) unsigned NOT NULL,
  `fontSize_m` tinyint(3) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `width_m` smallint(5) unsigned NOT NULL,
  `height_m` smallint(5) unsigned NOT NULL,
  `borderSize` tinyint(3) unsigned NOT NULL,
  `borderSize_m` tinyint(3) unsigned NOT NULL,
  `opacity` tinyint(3) unsigned NOT NULL,
  `opacity_m` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`selector_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}design_imageset` (
  `image_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `image_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `image_filename` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `image_lang` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_height` smallint(5) unsigned DEFAULT NULL,
  `image_width` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=MyISAM  ;

CREATE TABLE IF NOT EXISTS `{prefix_}forums` (
  `forum_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `forum_desc` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `forum_rules` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `forum_share_facebook` tinyint(1) NOT NULL DEFAULT '1',
  `forum_share_twitter` tinyint(1) NOT NULL DEFAULT '1',
  `forum_image` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `forum_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `forum_nb_subject` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_nb_post` int(10) unsigned NOT NULL DEFAULT '0',
  `forum_last_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `iconset_id` int(10) unsigned NOT NULL,
  `forum_icon_mandatory` tinyint(4) NOT NULL,
  PRIMARY KEY (`forum_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `{prefix_}groups` (
  `group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_founder_manage` int(10) unsigned NOT NULL,
  `group_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group_desc` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group_rank` smallint(5) unsigned NOT NULL,
  `group_color` char(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group_permissions` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group_type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  ;


CREATE TABLE IF NOT EXISTS `{prefix_}groups_permission` (
  `group_id` int(11) NOT NULL,
  `forum_id` smallint(6) NOT NULL,
  `group_permission` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`group_id`,`forum_id`)
) ENGINE=MyISAM ;


CREATE TABLE IF NOT EXISTS `{prefix_}langs` (
  `lang_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `lang_longid` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lang_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lang_author` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lang_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lang_install_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=MyISAM ;



CREATE TABLE IF NOT EXISTS `{prefix_}logs` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` tinyint(3) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `forum_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `log_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `log_time` int(10) unsigned NOT NULL,
  `log_operation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `log_data` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}plugins` (
  `plugin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plugin_desc` varchar(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plugin_author` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plugin_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plugin_autoload` tinyint(1) NOT NULL DEFAULT '0',
  `plugin_filename` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_activ` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}pm` (
  `pm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `icon_id` smallint(5) unsigned NOT NULL,
  `author_ip` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pm_time` int(10) unsigned NOT NULL,
  `pm_subject` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pm_text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pm_send_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `pm_edit_reason` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `pm_edit_user` int(10) unsigned DEFAULT NULL,
  `pm_edit_time` int(10) unsigned DEFAULT NULL,
  `pm_edit_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pm_id`)
) ENGINE=MyISAM ;



CREATE TABLE IF NOT EXISTS `{prefix_}pm_to` (
  `pm_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `author_id` int(11) unsigned NOT NULL,
  `pm_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pm_read` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pm_id_replied` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}poll_options` (
  `poll_option_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` mediumint(8) unsigned NOT NULL,
  `poll_option_text` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `poll_option_total` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_option_id`)
) ENGINE=MyISAM ;



CREATE TABLE IF NOT EXISTS `{prefix_}poll_votes` (
  `topic_id` mediumint(8) unsigned NOT NULL,
  `poll_option_id` mediumint(8) unsigned NOT NULL,
  `vote_user_id` int(10) unsigned NOT NULL,
  `vote_user_ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `{prefix_}posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL,
  `forum_id` mediumint(8) unsigned NOT NULL,
  `poster_id` int(10) unsigned NOT NULL,
  `icon_id` smallint(5) unsigned NOT NULL,
  `poster_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `post_time` int(10) unsigned NOT NULL,
  `post_subject` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `post_text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `post_checksum` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `post_edit_time` int(10) unsigned NOT NULL DEFAULT '0',
  `post_edit_reason` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `post_edit_user` int(10) unsigned NOT NULL DEFAULT '0',
  `post_edit_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`),
  FULLTEXT KEY `post_subject` (`post_subject`,`post_text`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `{prefix_}reports` (
  `report_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `reason_id` smallint(5) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `user_notify` tinyint(1) NOT NULL,
  `report_closed` tinyint(1) NOT NULL,
  `report_time` int(10) unsigned NOT NULL,
  `report_text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `report_close_time` int(10) unsigned DEFAULT NULL,
  `report_close_user_id` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`report_id`)
) ENGINE=MyISAM  ;


CREATE TABLE IF NOT EXISTS `{prefix_}reports_reasons` (
  `reason_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `reason_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `reason_text` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `reason_order` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`reason_id`)
) ENGINE=MyISAM  ;

CREATE TABLE IF NOT EXISTS `{prefix_}search_tags` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(70) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tag_date` int(11) NOT NULL,
  `tag_author` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}sessions` (
  `session_id` char(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `session_user_id` int(10) unsigned NOT NULL,
  `session_real_user_id` int(10) unsigned NOT NULL,
  `session_start` int(10) unsigned NOT NULL,
  `session_time` int(10) unsigned NOT NULL,
  `session_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `session_browser` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `session_page` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `session_viewonline` tinyint(1) NOT NULL DEFAULT '1',
  `session_autologin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}topics` (
  `topic_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` mediumint(8) unsigned NOT NULL,
  `icon_id` smallint(5) unsigned NOT NULL,
  `topic_title` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `topic_poster` int(10) unsigned NOT NULL,
  `topic_time` int(10) unsigned NOT NULL,
  `topic_view` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_replies` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `topic_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `topic_first_post_id` int(10) unsigned NOT NULL,
  `topic_last_post_id` int(10) unsigned NOT NULL,
  `poll_title` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `poll_start` int(10) unsigned NOT NULL,
  `poll_length` int(10) unsigned NOT NULL,
  `poll_max_options` tinyint(3) unsigned NOT NULL,
  `poll_last_vote` int(10) unsigned NOT NULL,
  `poll_vote_change` tinyint(1) NOT NULL,
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM  ;

CREATE TABLE IF NOT EXISTS `{prefix_}topics_read` (
  `user_id` int(10) unsigned NOT NULL,
  `forum_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `state` tinyint(1) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`topic_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_nb_message` int(11) NOT NULL DEFAULT '0',
  `user_type` smallint(5) unsigned NOT NULL,
  `group_id` smallint(5) unsigned NOT NULL,
  `user_permissions` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_regdate` int(10) unsigned NOT NULL,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_password` char(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_email` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_avatar` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_website` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_from` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_birthday` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_lastvisit` int(10) unsigned NOT NULL,
  `user_lastmark` int(10) unsigned NOT NULL,
  `user_lastpost_time` int(10) unsigned NOT NULL,
  `user_avert` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_ban` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_last_avert` int(10) unsigned DEFAULT NULL,
  `user_style` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_rank` int(10) unsigned NOT NULL,
  `user_job` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_msn` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_hobbies` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_sig` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_yahoo` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_skype` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_facebook` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_twitter` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_sexe` enum('m','f','i') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'i',
  `user_sig_options` int(10) unsigned NOT NULL,
  `user_options` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_language` int(10) unsigned NOT NULL,
  `user_comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_activ` tinyint(1) NOT NULL,
  `user_activ_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_activ_reason` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  ;

CREATE TABLE IF NOT EXISTS `{prefix_}users_averts` (
  `avert_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `avert_reason` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `avert_date` int(10) unsigned NOT NULL,
  `avert_expire` int(10) unsigned NOT NULL,
  `avert_give_user_id` mediumint(10) unsigned NOT NULL,
  PRIMARY KEY (`avert_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}users_ban` (
  `ban_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ban_user_id` mediumint(8) unsigned NOT NULL,
  `ban_email` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ban_ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ban_date` int(10) unsigned NOT NULL,
  `ban_expire` int(10) unsigned NOT NULL,
  `ban_reason` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ban_give_user_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`ban_id`,`ban_user_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}users_group` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `user_status` tinyint(4) NOT NULL,
  `user_date_joined` int(10) unsigned NOT NULL
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}users_permission` (
  `user_id` int(11) NOT NULL,
  `forum_id` smallint(6) NOT NULL,
  `user_permission` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`forum_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}templates` (
  `template_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `template_title` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `template_filename` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `template_content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `template_date_modified` int(10) unsigned NOT NULL DEFAULT '0',
  `template_nb_modified` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25;

CREATE TABLE IF NOT EXISTS `{prefix_}icons` (
  `icon_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iconset_id` int(10) unsigned NOT NULL,
  `icon_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `icon_path` varchar(50) NOT NULL,
  `icon_position` mediumint(8) unsigned NOT NULL,
  `icon_width` smallint(5) unsigned DEFAULT NULL,
  `icon_height` smallint(5) unsigned DEFAULT NULL,
  `icon_display` tinyint(1) NOT NULL,
  PRIMARY KEY (`icon_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `{prefix_}iconset` (
  `iconset_id` int(11) NOT NULL AUTO_INCREMENT,
  `iconset_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `iconset_mandatory` tinyint(1) NOT NULL,
  PRIMARY KEY (`iconset_id`)
) ENGINE=MyISAM;
