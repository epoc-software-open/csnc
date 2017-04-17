<?php

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Hospitalinfo_Action_Main_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	// 画面の入力値
	var $hospitalinfo_id = null;

	// 使用コンポーネントを受け取るため
	var $hospitalinfoView = null;
	var $hospitalinfoAction = null;

	// 値をセットするため

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{

		// データベースから削除
		$this->hospitalinfoAction->deleteHospitalinfo( $this->hospitalinfo_id );

		return 'success';
	}
}
?>
