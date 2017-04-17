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
class Testee_Action_Edit_Delallocation extends Action
{
    // リクエストパラメータを受け取るため
	var $metadata_id = null;
	var $testee_id = null;
	
	// コンポーネントを受け取るため
	var $mdbAction = null;
    /**
     * 割付設定を使用するか
     *
     * @access  public
     */
    function execute()
    {
		//test_log($this->testee_id);
		$result = $this->mdbAction->delAllocation($this->metadata_id);
		if($result === false) {
			return 'error';
		}
		return 'success';
    }
}
?>
