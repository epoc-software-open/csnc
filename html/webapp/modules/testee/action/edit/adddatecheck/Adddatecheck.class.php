<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 関連日付条件設定-関連日付条件追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Adddatecheck extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $condition_id = null;

// 日付項目
	var $date1_id  = null;
	var $date2_id  = null;

// エラーチェック用条件
	var $cond1_ew     = null;
	var $cond1_fugo1  = null;
	var $cond1_value1 = null;
	var $cond1_fugo2  = null;
	var $cond1_value2 = null;
	var $cond2_ew     = null;
	var $cond2_fugo1  = null;
	var $cond2_value1 = null;
	var $cond2_fugo2  = null;
	var $cond2_value2 = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$condition_id = intval($this->condition_id);

		if (!empty($condition_id)) {
			// 編集
			$where_params = array("condition_id" => $condition_id);

			$param = array(
				"cond1_ew" => $this->cond1_ew,
				"cond1_fugo1" => $this->cond1_fugo1,
				"cond1_value1" => $this->cond1_value1,
				"cond1_fugo2" => $this->cond1_fugo2,
				"cond1_value2" => $this->cond1_value2,
				"cond2_ew" => $this->cond2_ew,
				"cond2_fugo1" => $this->cond2_fugo1,
				"cond2_value1" => $this->cond2_value1,
				"cond2_fugo2" => $this->cond2_fugo2,
				"cond2_value2" => $this->cond2_value2,
				"date1_id" => $this->date1_id,
				"date2_id" => $this->date2_id
			);
			//更新
			$result = $this->db->updateExecute("testee_date_condition", $param, $where_params, false);
			if ($result === false) {
				return 'error';
			}
		} else {
			$param = array(
				"testee_id" => $this->testee_id,
				"cond1_ew" => $this->cond1_ew,
				"cond1_fugo1" => $this->cond1_fugo1,
				"cond1_value1" => $this->cond1_value1,
				"cond1_fugo2" => $this->cond1_fugo2,
				"cond1_value2" => $this->cond1_value2,
				"cond2_ew" => $this->cond2_ew,
				"cond2_fugo1" => $this->cond2_fugo1,
				"cond2_value1" => $this->cond2_value1,
				"cond2_fugo2" => $this->cond2_fugo2,
				"cond2_value2" => $this->cond2_value2,
				"date1_id" => $this->date1_id,
				"date2_id" => $this->date2_id
			);
			// 追加
			$condition_id = $this->db->insertExecute("testee_date_condition", $param, false, "condition_id");
			if ($condition_id === false) {
				return 'error';
			}
		}

		return 'success';
	}
}
?>
