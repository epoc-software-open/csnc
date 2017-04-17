-- 
-- テーブルの構造 `hospitalinfo`
-- 
-- 使用するテーブルのCreate文をここに記述
CREATE TABLE `hospitalinfo` (
  `hospitalinfo_id` int unsigned NOT NULL,
  `hospital_name` text NOT NULL,
  `hospital_kana` text,
  `hospital_code` varchar(255),
  `room_id` int(11) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL,
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`hospitalinfo_id`)
) ENGINE=MyISAM;