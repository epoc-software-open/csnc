<?php

/**
 * 関連日付チェックの条件設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Allocation_detail extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $condition_id = null;
	var $testee_id = null;

	// 使用コンポーネントを受け取るため

	// バリデートによりセット
	var $mdb_obj = null;

	// 使用コンポーネントを受け取るため
	var $mdbView = null;

	// 値をセットするため
	var $dates = null;		// 日付項目

	// 現在のエラーチェック内容
	var $current_check = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// 日付項目の取得
/*
		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_BIRTHDAY);
		if ($result === false) {
			return 'error';
		}
		$this->dates = $result;
		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_N_DATE);
		if ($result === false) {
			return 'error';
		}
		$this->dates = array_merge($this->dates, $result);
		$result = $this->mdbView->getMetadataIDNAME($this->testee_id, TESTEE_META_TYPE_DATE);
		if ($result === false) {
			return 'error';
		}
		$this->dates = array_merge($this->dates, $result);
*/
//		if($this->condition_id == 0) {
//			$this->current_check = null;						//初期化
//		}
//		else {
//			// 条件式
//			$result = $this->mdbView->getDateCheck($this->testee_id, $this->condition_id);
//			if ($result === false) {
//				return 'error';
//			}
//			if (count($result) > 0) $this->current_check = $result[0];
//		}
		return 'success';
	}
}
?>
