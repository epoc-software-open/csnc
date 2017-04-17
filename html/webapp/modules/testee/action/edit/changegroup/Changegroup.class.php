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
class Testee_Action_Edit_Changegroup extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;
	var $allocation_group_id = null;
	var $group_name = null;
	var $intervention = null;
	var $ratio = null;

	// コンポーネントを受け取るため
	var $mdbAction = null;
    /**
     * 割付設定を使用するか
     *
     * @access  public
     */
    function execute()
    {
		//test_log($this->allocation_group_id);
		$result = $this->mdbAction->changeGroup($this->allocation_group_id, $this->group_name, $this->intervention, $this->ratio );
		if($result === false) {
			return 'error';
		}
		return 'success';
    }
}
?>
