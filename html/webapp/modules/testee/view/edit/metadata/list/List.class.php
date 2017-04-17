<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Metadata_List extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;

    // バリデートによりセット
	var $mdb_obj = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;

	// 値をセットするため
	var $count = null;
	var $metadatas_layout = null;

	var $counts_data = null;			// 既存の件数設定情報 EddyK
	var $contents_count = null;			// 登録されているコンテンツ数

	var $group = null;					// グループ項目の選択肢情報（コード・名前）

    /**
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"testee_id" => intval($this->testee_id)
		);
		$this->count = $this->db->countExecute("testee_metadata", $params);
        if ($this->count === false) {
        	return 'error';
        }
		
		$this->metadatas_layout = $this->mdbView->getLayout($params);
    	if($this->metadatas_layout === false) {
    		return 'error';
    	}

		// 件数設定の既設定の取得 EddyK
		$this->counts_data = $this->mdbView->getCounts($this->testee_id);
    	if ($this->counts_data === false) {
    		return 'error';
    	}
		// 登録されているコンテンツ数を取得
		$this->contents_count = $this->db->countExecute("testee_content", $params);
        if ($this->contents_count === false) {
        	return 'error';
        }
		// グループ項目の選択肢情報を取得
		$result = $this->mdbView->getGroup($this->testee_id);
		if ($result === false) {
			return 'error';
		}
		if(count($result) > 0){
			$group_codes = explode("|", $result[0]["select_content_code"]);
			$groups = explode("|", $result[0]["select_content"]);
			for($i = 0; count($group_codes) > $i; $i++){
				$this->group[$group_codes[$i]] = $groups[$i];
			}
		}

		return 'success';
    }
}
?>
