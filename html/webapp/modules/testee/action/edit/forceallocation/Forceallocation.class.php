<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 動作フラグ更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Forceallocation extends Action
{
    // リクエストパラメータを受け取るため
	var $testee_id = null;
	var $force_allocation_flag = null;
	var $group_differences = null;
	var $allocation_probability = null;

	// コンポーネントを受け取るため
	var $mdbAction = null;
    /**
     * 割付設定を使用するか
     *
     * @access  public
     */
    function execute()
    {
		//test_log($this->allocation_probability);
		
		$result = $this->mdbAction->setForceallocation($this->testee_id, $this->group_differences, $this->allocation_probability, $this->force_allocation_flag);
		if($result === false) {
			return 'error';
		}
		return 'success';
    }
}
?>
