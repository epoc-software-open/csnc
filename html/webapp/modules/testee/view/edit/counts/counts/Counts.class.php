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
class Testee_View_Edit_Counts_Counts extends Action
{
    // リクエストパラメータを受け取るため
	var $testee_id = null;

    // バリデートによりセット
	var $mdb_obj = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;

	// 値をセットするため
	var $counts_matadatas = null;		// 存在するラジオボタンのメタ情報（件数設定できる項目）

	var $counts_data = null;			// 既存の件数設定情報

	var $options = null;				// 既存の件数設定されている項目の選択値
	var $option_counts = null;			// 既存の件数設定されている選択値別件数
	var $option_current_counts = null;	// 既存の件数設定されている選択値別既登録件数

	var $options_code = null;			// 選択値のコード
	var $contents_count = null;			// 登録されているコンテンツ数
    /**
     * 汎用データベース編集画面表示
     *
     * @access  public
     */
    function execute()
    {
		// 日付項目の取得
		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_GROUP);
		if ($result === false) {
			return 'error';
		}
		$this->counts_matadatas = $result;

		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_RADIO);
		if ($result === false) {
			return 'error';
		}
		$this->counts_matadatas = array_merge($this->counts_matadatas, $result);

		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_HOSPITAL);
		if ($result === false) {
			return 'error';
		}
		$this->counts_matadatas = array_merge($this->counts_matadatas, $result);

		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_SEX);
		if ($result === false) {
			return 'error';
		}
		$this->counts_matadatas = array_merge($this->counts_matadatas, $result);

		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_YESNO);
		if ($result === false) {
			return 'error';
		}
		$this->counts_matadatas = array_merge($this->counts_matadatas, $result);

		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_APPLICABLE);
		if ($result === false) {
			return 'error';
		}
		$this->counts_matadatas = array_merge($this->counts_matadatas, $result);

		// 登録されているコンテンツ数を取得
    	$params = array(
			"testee_id" => intval($this->testee_id)
		);
		$this->contents_count = $this->db->countExecute("testee_content", $params);
        if ($this->contents_count === false) {
        	return 'error';
        }
		// 件数設定の既設定の取得
		$this->counts_data = $this->mdbView->getCounts($this->testee_id);
    	if ($this->counts_data === false) {
    		return 'error';
    	}
		if($this->counts_data){
			// 項目の選択値を取得
			if($this->counts_data["counts_id"]){
				$result = $this->mdbView->getMetadataById($this->counts_data["counts_id"]);
				if ($result === false) {
					return 'error';
				}
				$this->options = explode("|", $result["select_content"]);
				$this->options_code = explode("|", $result["select_content_code"]);
			}
			// 選択値別件数を編集
			if($this->counts_data["option_counts"]){
				$option_counts = json_decode($this->counts_data["option_counts"],true);
				foreach($option_counts as $option_count){
					$this->option_counts[] = $option_count;
				}
			}
			// 選択値別登録件数を編集
			if($this->counts_data["counts_id"]){
				$result = $this->mdbView->getOptionCounts2($this->counts_data["counts_id"]);
				if ($result === false) {
					return 'error';
				}
				foreach($result as $codecount){
					$this->option_current_counts[] = $codecount["count"];
				}
			}
		}
    	return 'success';
    }
}
?>
