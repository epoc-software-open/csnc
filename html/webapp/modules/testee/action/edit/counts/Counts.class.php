<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Counts extends Action
{
    // リクエストパラメータを受け取るため
    var $group = null;
    var $block_id = null;

	var $testee_id = null;
	var $all_counts = null;
	var $counts_id = null;
	var $option_counts = null;

	// バリデートによりセット
	var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		// 件数設定の既設定の取得
		$counts_data = $this->mdbView->getCounts($this->testee_id);
    	if ($counts_data === false) {
    		return 'error';
    	}

		// 選択値別件数の編集
		$set_counts = "";
		if(isset($this->option_counts)) {
			foreach($this->option_counts as $key => $counts) {
				$set_counts[$key] = $counts;
			}
			$set_counts = json_encode($set_counts);
		}

		$result = null;
		if(!is_null($counts_data)){
			if($this->all_counts){
				// 既設定を更新
		    	$params = array(
					"testee_id" => $this->testee_id,
					"counts_id" => $this->counts_id,
					"all_counts" => $this->all_counts,
					"option_counts" => $set_counts
				);
				$result = $this->db->updateExecute("testee_counts", $params, array("testee_id" => $this->testee_id));
			} else {
				// 既設定の取消による削除
				$result = $this->db->deleteExecute("testee_counts", array("testee_id" => $this->testee_id));
			}
		} else {
			if($this->all_counts){
				// 新規登録
		    	$params = array(
					"testee_id" => $this->testee_id,
					"counts_id" => $this->counts_id,
					"all_counts" => $this->all_counts,
					"option_counts" => $set_counts
				);
				$result = $this->db->insertExecute("testee_counts", $params);
			}
		}

    	if ($result === false) {
    		return 'error';
    	}
        return 'success';
    }
}
?>