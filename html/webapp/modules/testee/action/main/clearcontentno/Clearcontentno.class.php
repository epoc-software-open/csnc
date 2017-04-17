<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録番号クリア処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Clearcontentno extends Action
{

	// 値の受け取り
	var $testee_id = null;

	var $session = null;
	var $mdbView = null;
	var $mdbAction = null;

	/**
	 * 登録番号クリア・アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		// 対象データベースのレコードカウント確認
		$result = $this->mdbView->getNID( $this->testee_id );
		// レコードがない場合のみ、処理を続行
		if (count($result) == 0) {
			// レコードカウントクリア
			$this->mdbAction->clearContentNo( $this->testee_id );
		}
		return 'success';
	}
}
?>
