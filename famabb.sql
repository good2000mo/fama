-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- 主機: localhost
-- 建立日期: Jul 18, 2011, 02:47 PM
-- 伺服器版本: 5.0.51
-- PHP 版本: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- 資料庫: `xman3`
-- 

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_config`
-- 

CREATE TABLE `fbb_config` (
  `conf_name` varchar(255) NOT NULL default '',
  `conf_value` text,
  PRIMARY KEY  (`conf_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `fbb_config`
-- 

INSERT INTO `fbb_config` VALUES ('o_board_title', 'FamaBB');
INSERT INTO `fbb_config` VALUES ('o_board_desc', '<p><span>這是我們應該做的.</span></p>');
INSERT INTO `fbb_config` VALUES ('o_default_timezone', '8');
INSERT INTO `fbb_config` VALUES ('o_time_format', 'H:i:s');
INSERT INTO `fbb_config` VALUES ('o_date_format', 'Y-m-d');
INSERT INTO `fbb_config` VALUES ('o_timeout_visit', '1800');
INSERT INTO `fbb_config` VALUES ('o_timeout_online', '300');
INSERT INTO `fbb_config` VALUES ('o_redirect_delay', '1');
INSERT INTO `fbb_config` VALUES ('o_default_lang', 'Traditional_Chinese');
INSERT INTO `fbb_config` VALUES ('o_default_user_group', '4');
INSERT INTO `fbb_config` VALUES ('o_disp_topics_default', '30');
INSERT INTO `fbb_config` VALUES ('o_disp_posts_default', '25');
INSERT INTO `fbb_config` VALUES ('o_indent_num_spaces', '4');
INSERT INTO `fbb_config` VALUES ('o_quote_depth', '3');
INSERT INTO `fbb_config` VALUES ('o_gzip', '0');
INSERT INTO `fbb_config` VALUES ('o_admin_email', 'comus2010mo@hotmail.com');
INSERT INTO `fbb_config` VALUES ('o_webmaster_email', 'comus2010mo@hotmail.com');
INSERT INTO `fbb_config` VALUES ('o_smtp_host', NULL);
INSERT INTO `fbb_config` VALUES ('o_smtp_user', NULL);
INSERT INTO `fbb_config` VALUES ('o_smtp_pass', NULL);
INSERT INTO `fbb_config` VALUES ('o_smtp_ssl', '0');

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_forums`
-- 

CREATE TABLE `fbb_forums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `forum_name` varchar(80) NOT NULL default 'New forum',
  `forum_desc` text,
  `moderators` text,
  `num_topics` mediumint(8) unsigned NOT NULL default '0',
  `num_posts` mediumint(8) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `last_post_id` int(10) unsigned default NULL,
  `last_poster` varchar(200) default NULL,
  `sort_by` tinyint(1) NOT NULL default '0',
  `disp_position` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `fbb_forums`
-- 

INSERT INTO `fbb_forums` VALUES (1, '測試版塊', '這只是一個測試版塊', NULL, 1, 1, 1310283814, 1, 'admin', 0, 1);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_forum_perms`
-- 

CREATE TABLE `fbb_forum_perms` (
  `group_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  `read_forum` tinyint(1) NOT NULL default '1',
  `post_replies` tinyint(1) NOT NULL default '1',
  `post_topics` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`group_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `fbb_forum_perms`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_groups`
-- 

CREATE TABLE `fbb_groups` (
  `g_id` int(10) unsigned NOT NULL auto_increment,
  `g_title` varchar(50) NOT NULL default '',
  `g_user_title` varchar(50) default NULL,
  `g_moderator` tinyint(1) NOT NULL default '0',
  `g_mod_edit_users` tinyint(1) NOT NULL default '0',
  `g_mod_rename_users` tinyint(1) NOT NULL default '0',
  `g_mod_change_passwords` tinyint(1) NOT NULL default '0',
  `g_read_board` tinyint(1) NOT NULL default '1',
  `g_view_users` tinyint(1) NOT NULL default '1',
  `g_post_replies` tinyint(1) NOT NULL default '1',
  `g_post_topics` tinyint(1) NOT NULL default '1',
  `g_edit_posts` tinyint(1) NOT NULL default '1',
  `g_delete_posts` tinyint(1) NOT NULL default '1',
  `g_delete_topics` tinyint(1) NOT NULL default '1',
  `g_set_title` tinyint(1) NOT NULL default '1',
  `g_search` tinyint(1) NOT NULL default '1',
  `g_search_users` tinyint(1) NOT NULL default '1',
  `g_send_email` tinyint(1) NOT NULL default '1',
  `g_post_flood` smallint(6) NOT NULL default '30',
  `g_search_flood` smallint(6) NOT NULL default '30',
  `g_email_flood` smallint(6) NOT NULL default '60',
  PRIMARY KEY  (`g_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- 列出以下資料庫的數據： `fbb_groups`
-- 

INSERT INTO `fbb_groups` VALUES (1, '管理員', '管理員', 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0);
INSERT INTO `fbb_groups` VALUES (2, '版主', '版主', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0);
INSERT INTO `fbb_groups` VALUES (3, '訪客', NULL, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 60, 30, 0);
INSERT INTO `fbb_groups` VALUES (4, '用戶', NULL, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 60, 30, 60);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_online`
-- 

CREATE TABLE `fbb_online` (
  `user_id` int(10) unsigned NOT NULL default '1',
  `ident` varchar(200) NOT NULL default '',
  `logged` int(10) unsigned NOT NULL default '0',
  `idle` tinyint(1) NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `last_search` int(10) unsigned default NULL,
  UNIQUE KEY `fbb_online_user_id_ident_idx` (`user_id`,`ident`(25)),
  KEY `fbb_online_ident_idx` (`ident`(25)),
  KEY `fbb_online_logged_idx` (`logged`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `fbb_online`
-- 

INSERT INTO `fbb_online` VALUES (2, 'admin', 1310971556, 0, NULL, NULL);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_posts`
-- 

CREATE TABLE `fbb_posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poster` varchar(200) NOT NULL default '',
  `poster_id` int(10) unsigned NOT NULL default '1',
  `poster_ip` varchar(39) default NULL,
  `poster_email` varchar(80) default NULL,
  `message` mediumtext,
  `posted` int(10) unsigned NOT NULL default '0',
  `edited` int(10) unsigned default NULL,
  `edited_by` varchar(200) default NULL,
  `topic_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `fbb_posts_topic_id_idx` (`topic_id`),
  KEY `fbb_posts_multi_idx` (`poster_id`,`topic_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `fbb_posts`
-- 

INSERT INTO `fbb_posts` VALUES (1, 'admin', 2, '127.0.0.1', NULL, '如果您在閱讀這個文章(我猜正是如此)，安裝的 FluxBB 已經開始工作！現在您可以登入，並進入頂部的管理設置面板來配置您的公告板。', 1310283814, NULL, NULL, 1);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_search_cache`
-- 

CREATE TABLE `fbb_search_cache` (
  `id` int(10) unsigned NOT NULL default '0',
  `ident` varchar(200) NOT NULL default '',
  `search_data` mediumtext,
  PRIMARY KEY  (`id`),
  KEY `fbb_search_cache_ident_idx` (`ident`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `fbb_search_cache`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_search_matches`
-- 

CREATE TABLE `fbb_search_matches` (
  `post_id` int(10) unsigned NOT NULL default '0',
  `word_id` int(10) unsigned NOT NULL default '0',
  `subject_match` tinyint(1) NOT NULL default '0',
  KEY `fbb_search_matches_word_id_idx` (`word_id`),
  KEY `fbb_search_matches_post_id_idx` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `fbb_search_matches`
-- 

INSERT INTO `fbb_search_matches` VALUES (1, 1, 0);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_search_words`
-- 

CREATE TABLE `fbb_search_words` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `word` varchar(20) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`word`),
  KEY `fbb_search_words_id_idx` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `fbb_search_words`
-- 

INSERT INTO `fbb_search_words` VALUES (1, 0x666c75786262);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_topics`
-- 

CREATE TABLE `fbb_topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poster` varchar(200) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `posted` int(10) unsigned NOT NULL default '0',
  `first_post_id` int(10) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned NOT NULL default '0',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  `last_poster` varchar(200) default NULL,
  `num_replies` mediumint(8) unsigned NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `sticky` tinyint(1) NOT NULL default '0',
  `moved_to` int(10) unsigned default NULL,
  `forum_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `fbb_topics_forum_id_idx` (`forum_id`),
  KEY `fbb_topics_moved_to_idx` (`moved_to`),
  KEY `fbb_topics_last_post_idx` (`last_post`),
  KEY `fbb_topics_first_post_id_idx` (`first_post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `fbb_topics`
-- 

INSERT INTO `fbb_topics` VALUES (1, 'admin', '示例文章', 1310283814, 1, 1310283814, 1, 'admin', 0, 0, 0, NULL, 1);

-- --------------------------------------------------------

-- 
-- 資料表格式： `fbb_users`
-- 

CREATE TABLE `fbb_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '3',
  `username` varchar(200) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `email` varchar(80) NOT NULL default '',
  `title` varchar(50) default NULL,
  `language` varchar(25) NOT NULL default 'Traditional_Chinese',
  `num_posts` int(10) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `last_search` int(10) unsigned default NULL,
  `last_email_sent` int(10) unsigned default NULL,
  `registered` int(10) unsigned NOT NULL default '0',
  `registration_ip` varchar(39) NOT NULL default '0.0.0.0',
  `last_visit` int(10) unsigned NOT NULL default '0',
  `activate_string` varchar(80) default NULL,
  `activate_key` varchar(8) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fbb_users_username_idx` (`username`(25)),
  KEY `fbb_users_registered_idx` (`registered`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- 列出以下資料庫的數據： `fbb_users`
-- 

INSERT INTO `fbb_users` VALUES (1, 3, '訪客', '訪客', '訪客', NULL, 'Traditional_Chinese', 0, NULL, NULL, NULL, 0, '0.0.0.0', 0, NULL, NULL);
INSERT INTO `fbb_users` VALUES (2, 1, 'admin', 'c3d873d7fa9ac799e35693d4cc288d4a16ad3f96', 'comus2010mo@hotmail.com', NULL, 'Traditional_Chinese', 1, 1310283814, NULL, NULL, 1310283814, '127.0.0.1', 1310903718, NULL, NULL);
