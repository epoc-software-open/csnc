<?php
/**
 * モジュールアップデートクラス
 * テーブル項目追加
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_Update extends Action
{
	var $module_id = null;

	//使用コンポーネントを受け取るため
	var $db = null;
	var $modulesView = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."footeredit");
		if(!isset($metaColumns["COPYRIGHT"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."footeredit`
						ADD `copyright` TEXT DEFAULT NULL AFTER `link_html` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		if(!isset($metaColumns["EDIT_TYPE"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."footeredit`
						ADD `edit_type` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `logo_file_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		if(!isset($metaColumns["EMBED_HTML"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."footeredit`
						ADD `embed_html` text AFTER `copyright` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>
