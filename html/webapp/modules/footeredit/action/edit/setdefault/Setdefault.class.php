<?php

/**
 * フッタを初期値に戻す
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_Action_Edit_Setdefault extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	// 使用コンポーネントを受け取るため
	var $footereditView = null;
	var $footereditAction = null;

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{

		// フッター項目を保存する
		$this->footereditAction->setDefaultFooter();

		return 'success';
	}
}
?>
