-- 
-- テーブルの構造 `stylechange`
-- 
-- スタイル設定
CREATE TABLE `stylechange` (
  `style_location` varchar(255) DEFAULT NULL,
  `style_type` varchar(255) DEFAULT NULL,
  `style_color` varchar(255) DEFAULT NULL,
  `room_id` int(11) NOT NULL DEFAULT '0',
  `insert_time` varchar(14) NOT NULL DEFAULT '',
  `insert_site_id` varchar(40) NOT NULL DEFAULT '',
  `insert_user_id` varchar(40) NOT NULL DEFAULT '',
  `insert_user_name` varchar(255) NOT NULL DEFAULT '',
  `update_time` varchar(14) NOT NULL DEFAULT '',
  `update_site_id` varchar(40) NOT NULL DEFAULT '',
  `update_user_id` varchar(40) NOT NULL DEFAULT '',
  `update_user_name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- -
-- Table Structure `stylechange_override`
-- -

CREATE TABLE `stylechange_override` (
  `override_css` text DEFAULT NULL,
  `room_id` int(11) NOT NULL DEFAULT '0',
  `insert_time` varchar(14) NOT NULL DEFAULT '',
  `insert_site_id` varchar(40) NOT NULL DEFAULT '',
  `insert_user_id` varchar(40) NOT NULL DEFAULT '',
  `insert_user_name` varchar(255) NOT NULL DEFAULT '',
  `update_time` varchar(14) NOT NULL DEFAULT '',
  `update_site_id` varchar(40) NOT NULL DEFAULT '',
  `update_user_id` varchar(40) NOT NULL DEFAULT '',
  `update_user_name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
