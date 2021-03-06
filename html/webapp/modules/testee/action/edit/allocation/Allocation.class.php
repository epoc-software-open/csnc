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
class Testee_Action_Edit_Allocation extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;
	var $allocation_flag = null;
	
	// コンポーネントを受け取るため
	var $mdbAction = null;
    /**
     * 割付設定を使用するか
     *
     * @access  public
     */
    function execute()
    {
		//test_log($this->allocation_flag);
		$result = $this->mdbAction->setAllocationFlag( $this->testee_id, $this->allocation_flag );
		if($result === false) {
			return 'error';
		}
		return 'success';
    }
}
?>
