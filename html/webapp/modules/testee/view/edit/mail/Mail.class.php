<?php

/**
 * メール設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Mail extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;

	var $metadata_id = null;
	var $testee_id = null;

	// 使用コンポーネントを受け取るため
	var $mdbView = null;
	var $db = null;

	// 画面に渡す値
	var $users = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{

		// ルームのユーザとメール送信ユーザを取得
		$this->users = $this->mdbView->getRoomMailUser( $this->room_id, $this->metadata_id );
		//print_r($this->users);

		return 'success';
	}
}
?>
