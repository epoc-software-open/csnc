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
class Testee_Action_Edit_Deldatecheck extends Action
{
	// リクエストパラメータを受け取るため
	var $condition_id = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbAction = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{	
		$result = $this->mdbAction->deleteDatecheck($this->condition_id);
		if ($result === false) {
			return 'error';
		}

		return 'success';
	}
}
?>
