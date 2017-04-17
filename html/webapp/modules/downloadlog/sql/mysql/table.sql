-- 
-- テーブルの構造 `downloadlog`
-- 
-- 
CREATE TABLE `downloadlog` (
  `downloadlog_id`     int(11) unsigned NOT NULL DEFAULT '0',
  `upload_id`          int(11) unsigned NOT NULL DEFAULT '0',
  `room_id`            int(11) NOT NULL DEFAULT '0',
  `module_id`          int(11) NOT NULL DEFAULT '0',
  `unique_id`          varchar(40) NOT NULL DEFAULT '',
  `file_name`          text NOT NULL,
  `physical_file_name` text NOT NULL,
  `file_path`          text NOT NULL,
  `action_name`        varchar(255) NOT NULL DEFAULT '',
  `file_size`          bigint(20) NOT NULL DEFAULT '0',
  `mimetype`           varchar(255) NOT NULL DEFAULT '',
  `extension`          varchar(255) NOT NULL DEFAULT '',
  `garbage_flag`       tinyint(1) NOT NULL DEFAULT '0',
  `download_user_name` varchar(255) DEFAULT NULL,
  `download_user_id`   varchar(40) DEFAULT '',
  `download_ym_local`  varchar(6) NOT NULL DEFAULT '',
  `module_name`        varchar(255) DEFAULT NULL,
  `room_name`          varchar(255) DEFAULT NULL,
  `insert_time`        varchar(14) DEFAULT '',
  `insert_site_id`     varchar(40) DEFAULT '',
  `insert_user_id`     varchar(40) DEFAULT '',
  `insert_user_name`   varchar(255) DEFAULT NULL,
  `update_time`        varchar(14) DEFAULT '',
  `update_site_id`     varchar(40) DEFAULT '',
  `update_user_id`     varchar(40) DEFAULT '',
  `update_user_name`   varchar(255) DEFAULT NULL,
  PRIMARY KEY (`downloadlog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
