<?php
/**
 * モジュールアップデートクラス
 * 　　css_filesにカラム追加
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Update extends Action
{
	var $module_id = null;
	//使用コンポーネントを受け取るため
	var $db = null;
	var $modulesView = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_block");
		if(!isset($metaColumns["DEFAULT_SORT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_block`
						ADD `default_sort` VARCHAR( 11 ) NOT NULL DEFAULT '' AFTER `visible_item` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_metadata");
		if(!isset($metaColumns["FILE_PASSWORD_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata`
						ADD `file_password_flag` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `sort_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_metadata");
		if(!isset($metaColumns["FILE_COUNT_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata`
						ADD `file_count_flag` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `file_password_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_file");
		if(!isset($metaColumns["FILE_PASSWORD"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_file`
						ADD `file_password` VARCHAR( 255 ) DEFAULT NULL AFTER `file_name` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_file");
		if(!isset($metaColumns["DOWNLOAD_COUNT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_file`
						ADD `download_count` INT( 11 ) DEFAULT '0' AFTER `file_password` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$sql = "UPDATE `".$this->db->getPrefix()."uploads`
						SET `action_name` = 'common_download_main' WHERE `action_name` = 'testee_action_main_filedownload' AND `module_id` = '".$this->module_id."' ;";
		$result = $this->db->execute($sql);
		if($result === false) return false;

		$sql = "SELECT metadata_content_id, content FROM `".$this->db->getPrefix()."testee_metadata_content`,
						`".$this->db->getPrefix()."testee_metadata`
						WHERE `".$this->db->getPrefix()."testee_metadata_content`.`content` LIKE '%?action=common_download_main%'
						AND `".$this->db->getPrefix()."testee_metadata_content`.`metadata_id` = `".$this->db->getPrefix()."testee_metadata`.`metadata_id`
						AND ( `".$this->db->getPrefix()."testee_metadata`.`type`=5 OR `".$this->db->getPrefix()."testee_metadata`.`type`=0 ) ;";
		$contents = $this->db->execute($sql);
		if($contents === false) return false;
		if(!empty($contents)) {
			foreach($contents as $content) {
				$str_arr = explode("&", $content['content']);
				$param = array("content" => "?action=testee_action_main_filedownload&".$str_arr[1]);
				$where_param = array("metadata_content_id" => $content['metadata_content_id']);
				$result = $this->db->updateExecute("testee_metadata_content", $param, $where_param, false);
				if($result === false) return false;
			}
		}

		$sql = "SELECT content FROM `".$this->db->getPrefix()."testee_metadata_content`,
						`".$this->db->getPrefix()."testee_metadata`
						WHERE `".$this->db->getPrefix()."testee_metadata_content`.`metadata_id` = `".$this->db->getPrefix()."testee_metadata`.`metadata_id`
						AND ( `".$this->db->getPrefix()."testee_metadata`.`type`=5 OR `".$this->db->getPrefix()."testee_metadata`.`type`=0 ) ;";
		$contents = $this->db->execute($sql);
		if(!empty($contents)) {
			foreach($contents as $content) {
				if(!empty($content['content'])) {
					$str_arr = explode("&", $content['content']);
					if(!empty($str_arr[1]) && strpos($str_arr[1], "upload_id=") !== false) {
						$upload_id = intval(str_replace("upload_id=","", $str_arr[1]));
						if(empty($upload_id)) {
							continue;
						}
						$param = array("action_name" => "testee_action_main_filedownload");
						$where_param = array(
							"upload_id" => $upload_id,
							"module_id" => $this->module_id,
							"action_name" => "common_download_main"
						);
						$result = $this->db->updateExecute("uploads", $param, $where_param, false);
						if($result === false) return false;
					}
				}
			}
		}

		// testeeにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// testee_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_block_id_flag = true;
		$alter_table_testee_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "PRIMARY") {
				$alter_table_block_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "testee_id") {
				$alter_table_testee_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_block_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_block` ADD PRIMARY KEY ( `block_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_testee_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_block` ADD INDEX ( `testee_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// testee_commentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee_comment` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_content_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "content_id") {
				$alter_table_content_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_content_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_comment` ADD INDEX ( `content_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_comment` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// testee_contentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee_content` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_testee_id_flag = true;
		$alter_table_room_id_flag = true;
		$alter_table_insert_time_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "testee_id") {
				$alter_table_testee_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "testee_id" && $result['Column_name'] == "insert_time") {
				$alter_table_insert_time_flag = false;
			}
		}
		if($alter_table_testee_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content` ADD INDEX ( `testee_id`, `display_sequence` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if ($alter_table_insert_time_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content` DROP INDEX `testee_id`," .
					" ADD INDEX `testee_id` ( `testee_id` , `display_sequence` , `insert_time` )";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// testee_fileにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee_file` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_upload_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "upload_id") {
				$alter_table_upload_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_upload_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_file` ADD INDEX ( `upload_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_file` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// testee_metadataにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee_metadata` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_testee_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "testee_id") {
				$alter_table_testee_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_testee_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata` ADD INDEX ( `testee_id`, `display_pos` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// testee_metadata_contentにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."testee_metadata_content` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_content_id_flag = true;
		$alter_table_content_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "content_id") {
				$alter_table_content_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "content") {
				$alter_table_content_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_content_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata_content` ADD INDEX ( `content_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_content_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata_content` ADD FULLTEXT ( `content`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata_content` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// 件数表示で全てを選択されている場合、100件にする
		$sql = "UPDATE `".$this->db->getPrefix()."testee_block` SET visible_item = 100 WHERE visible_item = 0";
		$result = $this->db->execute($sql);
		if($result === false) return false;

		// 日付タイプのメタデータは検索対象としないようにする
		$sql = "UPDATE `".$this->db->getPrefix()."testee_metadata` SET `search_flag` = 0 WHERE `type` = ".TESTEE_META_TYPE_DATE;
		$result = $this->db->execute($sql);
		if($result === false) return false;

		// 日付タイプのメタデータは検索対象としないようにする 追加メタタイプのうち日付タイプのものも同様
		$sql = "UPDATE `".$this->db->getPrefix()."testee_metadata` SET `search_flag` = 0 WHERE `type` = ".TESTEE_META_TYPE_N_DATE;
		$result = $this->db->execute($sql);
		if($result === false) return false;
		$sql = "UPDATE `".$this->db->getPrefix()."testee_metadata` SET `search_flag` = 0 WHERE `type` = ".TESTEE_META_TYPE_N_BIRTHDAY;
		$result = $this->db->execute($sql);
		if($result === false) return false;

		// メタデータ定義に表示単位がない場合、追加する
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_metadata");
		if(!isset($metaColumns["VIEW_UNIT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_metadata`
						ADD `view_unit` varchar( 255 ) default NULL AFTER `decoration_red` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// データベース定義に検体進捗機能のスイッチがない場合、追加する
		$testeeColumns = $adodb->MetaColumns($this->db->getPrefix()."testee");
		if(!isset($testeeColumns["KENTAI_SWITCH"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee`
						ADD `kentai_switch` INT( 11 ) NOT NULL DEFAULT '0' AFTER `add_mail_send_metadata_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// ステータステーブルの定義確認
		$statusColumns = $adodb->MetaColumns($this->db->getPrefix()."testee_content_status");

		// データベースID がなければ追加
		if(!isset($statusColumns["TESTEE_ID"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content_status`
						ADD `testee_id` INT( 11 ) NOT NULL DEFAULT '0' AFTER `testee_content_status_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// 施設患者番号がなければ追加
		if(!isset($statusColumns["UNIQUE_PATIENT_CD"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content_status`
						ADD `unique_patient_cd` varchar(255) NOT NULL default '' AFTER `status_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// コンテンツ番号がなければ追加
		if(!isset($statusColumns["CONTENT_NO"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content_status`
						ADD `content_no` varchar(255) NOT NULL default '' AFTER `unique_patient_cd` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// コンテンツがなければ追加
		if(!isset($statusColumns["CONTENT_DATA"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."testee_content_status`
						ADD `content_data` varchar(255) NOT NULL default '' AFTER `content_no` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>
