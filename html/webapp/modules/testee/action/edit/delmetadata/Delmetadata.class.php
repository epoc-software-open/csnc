<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Delmetadata extends Action
{
	// リクエストパラメータを受け取るため
	var $metadata_id = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $mdbAction = null;
	
	// バリデートによりセットするため
	var $metadata = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{	

		// 関連日付チェック設定で使用されているメタの場合、該当のチェックを削除する EddyK
		$datecheck_datas = $this->mdbView->getDateCheck($this->metadata['testee_id']);
		if ($datecheck_datas === false) {
			return  'error';
		}
		if ($datecheck_datas) {
			foreach($datecheck_datas as $datecheck_data){
				if($datecheck_data["date1_id"] == $this->metadata_id || $datecheck_data["date2_id"] == $this->metadata_id){
					$where_params = array(
						"condition_id" => $datecheck_data["condition_id"]
					);
					$result = $this->db->deleteExecute("testee_date_condition", $where_params);
					if ($result === false) {
						return 'error';
					}
				}
			}
		}

		$result = $this->mdbAction->deleteMetadata($this->metadata_id);
		if ($result === false) {
			return 'error';
		}
		
		// 表示順-前詰め処理
		$params = array(
			"testee_id" => $this->metadata['testee_id'],
			"display_pos" => $this->metadata['display_pos']
		);
		$sequence_param = array(
			"display_sequence" => $this->metadata['display_sequence']
		);
    	$result = $this->db->seqExecute("testee_metadata", $params, $sequence_param);
		if ($result === false) {
			return 'error';
		}

		// グループタイプの場合、既にグループ設定されている内容をクリアする EddyK
		if($this->metadata['type'] == TESTEE_META_TYPE_N_GROUP){
			$where_params = array("testee_id" => $this->metadata['testee_id']);
			$param = array(
				"group_option" => null
			);
			$result = $this->db->updateExecute("testee_metadata", $param, $where_params, true);
			if ($result === false) {
				return 'error';
			}
		}
		// メタが件数制限設定されているメタの場合、件数設定の内容をクリアする EddyK
		$where_params = array(
			"testee_id" => $this->metadata['testee_id'],
			"counts_id" => $this->metadata_id
		);
		$param = array(
			"counts_id" => null,
			"option_counts" => null
		);
		$result = $this->db->updateExecute("testee_counts", $param, $where_params, true);
		if ($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
