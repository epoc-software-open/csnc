-- --------------------------------------------------------

-- -
-- テーブルの構造`testee`
-- -

CREATE TABLE testee (
  `testee_id`   int(11) NOT NULL default '0',
  `room_id`	           int(11) NOT NULL default '0',
  `testee_name` varchar(255) NOT NULL default '',
  `active_flag`        tinyint(1) NOT NULL default '0',
  `mail_flag`          varchar(255) NOT NULL default '',
  `mail_authority`     tinyint(1) NOT NULL default '0',
  `mail_subject`       varchar(255) default NULL,
  `mail_body`          text,
  `contents_authority` tinyint(1) NOT NULL default '0',
  `new_period`         int(11) NOT NULL default '0',
  `vote_flag`          tinyint(1) NOT NULL default '0',
  `comment_flag`       tinyint(1) NOT NULL default '0',
  `agree_flag`         tinyint(1) NOT NULL default '0',
  `agree_mail_flag`    tinyint(1) NOT NULL default '0',
  `agree_mail_subject` varchar(255) default NULL,
  `agree_mail_body`    text,
  `title_metadata_id`  int(11) NOT NULL default '0',
  `content_no`	       int(11) NOT NULL default '0',
  `add_mail_send_metadata_id` int(11) NOT NULL default '0',
  `kentai_switch`      int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`testee_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_block`
-- -

CREATE TABLE testee_block (
  `block_id`           int(11) NOT NULL default '0',
  `testee_id`   int(11) NOT NULL default '0',
  `visible_item`       tinyint(1) NOT NULL default '0',
  `default_sort`       varchar(11) NOT NULL default '',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `testee_id` (`testee_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_metadata`
-- -

CREATE TABLE testee_metadata (
  `metadata_id`          int(11) NOT NULL default '0',
  `testee_id`            int(11) NOT NULL default '0',
  `name`                 varchar(255) default NULL,
  `varb_name`            varchar(255) default NULL,
  `type`                 tinyint(1) NOT NULL default '0',
  `display_pos`          tinyint(1) NOT NULL default '0',
  `select_content`       text,
  `select_content_code`  text,
  `correct`              text,
  `group_option`         text,
  `require_flag`         tinyint(1) NOT NULL default '0',
  `list_flag`            tinyint(1) NOT NULL default '0',
  `detail_flag`          tinyint(1) NOT NULL default '0',
  `search_flag`          tinyint(1) NOT NULL default '0',
  `name_flag`            tinyint(1) NOT NULL default '0',
  `sort_flag`            tinyint(1) NOT NULL default '0',
  `file_password_flag`   tinyint(1) NOT NULL default '0',
  `file_count_flag`      tinyint(1) NOT NULL default '0',
  `mobile_detail_flag`   tinyint(1) NOT NULL default '0',
  `display_sequence`     int(11) default NULL,
  `decoration_bold`      tinyint(1) NOT NULL default '0',
  `decoration_underline` tinyint(1) NOT NULL default '0',
  `decoration_red`       tinyint(1) NOT NULL default '0',
  `view_unit`            varchar(255) default NULL,
  `mail_send_days`       int(11) NOT NULL default '0',
  `mail_body`            text,
  `mail_send_target`     tinyint(1) NOT NULL default '0',
  `room_id`	             int(11) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metadata_id`),
  KEY `testee_id` (`testee_id`,`display_pos`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_metadata_condition`
-- -

CREATE TABLE testee_metadata_condition (
  `metadata_id`        int(11) NOT NULL default '0',
  `cond1_ew`           char(1) NOT NULL default '',
  `cond1_fugo1`        char(2) NOT NULL default '0',
  `cond1_value1`       char(6) NOT NULL default '0',
  `cond1_fugo2`        char(2) default NULL,
  `cond1_value2`       char(6) default NULL,
  `cond2_ew`           char(1) default NULL,
  `cond2_fugo1`        char(2) default NULL,
  `cond2_value1`       char(6) default NULL,
  `cond2_fugo2`        char(2) default NULL,
  `cond2_value2`       char(6) default NULL,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metadata_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_metadata_comment`
-- -

CREATE TABLE testee_metadata_comment (
  `metadata_id`        int(11) NOT NULL default '0',
  `comment`            text NOT NULL default '',
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metadata_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_content`
-- -

CREATE TABLE testee_content (
  `content_id`         int(11) NOT NULL default '0',
  `testee_id`   int(11) NOT NULL default '0',
  `vote`               text,
  `vote_count`         int(11) NOT NULL default '0',
  `agree_flag`         tinyint(1) NOT NULL default '0',
  `temporary_flag`     tinyint(1) NOT NULL default 0,
  `display_sequence`   int(11) default NULL,
  `content_no`	       int(11) NOT NULL default '0',
  `tedc_link_result`   char(1) default '',
  `tedc_link_result_message` text,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`content_id`),
  KEY `testee_id` (`testee_id`, `display_sequence`, `insert_time`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_metadata_content`
-- -

CREATE TABLE testee_metadata_content (
  `metadata_content_id` int(11) NOT NULL default '0',
  `metadata_id`         int(11) NOT NULL default '0',
  `content_id`          int(11) NOT NULL default '0',
  `content`	            text,
  `select_content_code` text,
  `room_id`	            int(11) NOT NULL default '0',
  `insert_time`         varchar(14) NOT NULL default '',
  `insert_site_id`      varchar(40) NOT NULL default '',
  `insert_user_id`      varchar(40) NOT NULL default '',
  `insert_user_name`    varchar(255) NOT NULL default '',
  `update_time`         varchar(14) NOT NULL default '',
  `update_site_id`      varchar(40) NOT NULL default '',
  `update_user_id`      varchar(40) NOT NULL default '',
  `update_user_name`    varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metadata_content_id`),
  KEY `metadata_id` (`metadata_id`,`content_id`),
  KEY `content_id` (`content_id`),
  KEY `room_id` (`room_id`)
 ,FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_comment`
-- -

CREATE TABLE testee_comment (
  `comment_id`         int(11) NOT NULL default '0',
  `content_id`         int(11) NOT NULL default '0',
  `comment_content`    text NOT NULL,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`comment_id`),
  KEY `content_id` (`content_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_file`
-- -

CREATE TABLE testee_file (
  `metadata_content_id`       int(11) NOT NULL default '0',
  `upload_id`                 int(11) NOT NULL default '0',
  `file_name`                 varchar(255) default NULL,
  `file_password`             varchar(255) default NULL,
  `download_count`            int(11) NOT NULL default '0',
  `physical_file_name`        varchar(255) default NULL,
  `room_id`                   int(11) NOT NULL default '0',
  PRIMARY KEY  (`metadata_content_id`),
  KEY `upload_id` (`upload_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_date_condition`
-- -

CREATE TABLE testee_date_condition (
  `condition_id`		int(11) NOT NULL default '0',
  `testee_id`			int(11) NOT NULL default '0',
  `cond1_ew`			char(1) NOT NULL default '',
  `cond1_fugo1`			char(2) NOT NULL default '0',
  `cond1_value1`		char(6) NOT NULL default '0',
  `cond1_fugo2`			char(2) default NULL,
  `cond1_value2`		char(6) default NULL,
  `cond2_ew`			char(1) default NULL,
  `cond2_fugo1`			char(2) default NULL,
  `cond2_value1`		char(6) default NULL,
  `cond2_fugo2`			char(2) default NULL,
  `cond2_value2`		char(6) default NULL,
  `date1_id`			int(11) NOT NULL default '0',
  `date2_id`			int(11) NOT NULL default '0',
  PRIMARY KEY  (`condition_id`),
  KEY `testee_id` (`testee_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_counts`
-- -

CREATE TABLE testee_counts (
  `testee_id`			int(11) NOT NULL default '0',
  `all_counts`			int(11) NOT NULL default '0',
  `counts_id`			int(11) NOT NULL default '0',
  `option_counts`		text,
  PRIMARY KEY  (`testee_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_log`
-- -

CREATE TABLE testee_log (
  `testee_log_id` 		int(11) NOT NULL AUTO_INCREMENT,
  `exec_sql` 			text,
  `param` 				text,
  `insert_time` 		varchar(14) NOT NULL,
  `insert_user_id` 		varchar(40) NOT NULL,
  `insert_user_name` 	varchar(255) NOT NULL,
  PRIMARY KEY (`testee_log_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- テーブルの構造 `tedcauthority`
-- 

CREATE TABLE `testee_tedcauthority` (
  `testee_id`		int(11) NOT NULL default '0',
  `user_id`		varchar(40) NOT NULL default '',
  `tedc_authority`	char(2) NOT NULL default '',
  `insert_time`		varchar(14) NOT NULL default '',
  `insert_site_id`	varchar(40) NOT NULL default '',
  `insert_user_id`	varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL,
  `update_time`		varchar(14) NOT NULL default '',
  `update_site_id`	varchar(40) NOT NULL default '',
  `update_user_id`	varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`testee_id`,`user_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造`testee_allocate`
-- -

CREATE TABLE testee_allocate (
  `testee_id`   int(11) NOT NULL default '0',
  `allocation_flag`         tinyint(1) default '0',
  `equal_ratio_flag`         tinyint(1) default '0',
  `force_allocation_flag`         tinyint(1) default '0',
  `group_differences`   int(11) NOT NULL default '0',
  `allocation_probability` float DEFAULT '0.0',
  `allocation_result_flag`         tinyint(1) default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`testee_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造`testee_adjustment`
-- -

CREATE TABLE testee_adjustment (
  `desplay_seq`   int(11) NOT NULL default '0',
  `metadata_id`         int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`desplay_seq`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造`testee_allocation_group`
-- -

CREATE TABLE testee_allocation_group (
  `allocation_group_id`   int(11) NOT NULL default '0',
  `testee_id`   int(11) NOT NULL default '0',
  `group_name`               varchar(255),
  `intervention`               varchar(255),
  `ratio`               varchar(255),
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`allocation_group_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造`testee_setting_history`
-- -

CREATE TABLE testee_setting_history (
  `allocation_view_id`   int(11) NOT NULL default '0',
  `testee_id`   int(11) NOT NULL default '0',
  `allocation_result_flag`         tinyint(1) default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`allocation_view_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_content_group`
-- -

CREATE TABLE testee_content_group (
  `testee_content_group_id` int(11) NOT NULL DEFAULT '0',
  `content_id`         int(11) NOT NULL DEFAULT '0',
  `testee_id`          int(11) NOT NULL DEFAULT '0',
  `allocation_group_id` int(11) NOT NULL DEFAULT '0',
  `insert_time`        varchar(14) NOT NULL DEFAULT '',
  `insert_site_id`     varchar(40) NOT NULL DEFAULT '',
  `insert_user_id`     varchar(40) NOT NULL DEFAULT '',
  `insert_user_name`   varchar(255) NOT NULL DEFAULT '',
  `update_time`        varchar(14) NOT NULL DEFAULT '',
  `update_site_id`     varchar(40) NOT NULL DEFAULT '',
  `update_user_id`     varchar(40) NOT NULL DEFAULT '',
  `update_user_name`   varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`testee_content_group_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_mail_users`
-- -

CREATE TABLE testee_mail_users (
  `testee_mail_users_id` int(11) NOT NULL default '0',
  `metadata_id`          int(11) NOT NULL default '0',
  `user_id`              varchar(40) NOT NULL default '',
  `room_id`	             int(11) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`testee_mail_users_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_mail_content_log`
-- -

CREATE TABLE testee_mail_content_log (
  `testee_mail_content_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id`           int(11) NOT NULL default '0',
  `metadata_id`          int(11) NOT NULL default '0',
  `room_id`	             int(11) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`testee_mail_content_log_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_mail_users_log`
-- -

CREATE TABLE testee_mail_users_log (
  `testee_mail_users_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id`           int(11) NOT NULL default '0',
  `metadata_id`          int(11) NOT NULL default '0',
  `user_id`              varchar(40) NOT NULL default '',
  `room_id`	             int(11) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`testee_mail_users_log_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `testee_content_status`
-- -

CREATE TABLE testee_content_status (
  `testee_content_status_id` int(11) NOT NULL default '0',
  `testee_id`            int(11) NOT NULL default '0',
  `content_id`           int(11) NOT NULL default '0',
  `status_id`            int(11) NOT NULL default '0',
  `unique_patient_cd`    varchar(255) NOT NULL default '',
  `content_no`           varchar(255) NOT NULL default '',
  `content_data`         varchar(255) NOT NULL default '',
  `room_id`	             int(11) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`testee_content_status_id`)
) ENGINE=MyISAM;
