-- 
-- テーブルの構造 `footeredit`
-- 
-- 使用するテーブルのCreate文をここに記述
CREATE TABLE `footeredit` (
  `logo_file_id` int(11) DEFAULT NULL,
  `edit_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `layout` int(11) DEFAULT NULL,
  `address` text,
  `tel` text,
  `fax` text,
  `mail` text,
  `link_html` text NOT NULL,
  `copyright` text DEFAULT NULL,
  `embed_html` text,
  `chk_logo` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chk_address` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chk_tel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chk_fax` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chk_mail` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chk_link` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chk_nc` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `room_id` int(11) NOT NULL DEFAULT '0',
  `insert_time` varchar(14) NOT NULL DEFAULT '',
  `insert_site_id` varchar(40) NOT NULL DEFAULT '',
  `insert_user_id` varchar(40) NOT NULL DEFAULT '',
  `insert_user_name` varchar(255) NOT NULL,
  `update_time` varchar(14) NOT NULL DEFAULT '',
  `update_site_id` varchar(40) NOT NULL DEFAULT '',
  `update_user_id` varchar(40) NOT NULL DEFAULT '',
  `update_user_name` varchar(255) NOT NULL
) ENGINE=MyISAM;