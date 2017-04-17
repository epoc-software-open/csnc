<?php

/**
 * ヘルプ画面（使い方説明）の表示
 *
 * @package     jp.opensource-workshop
 * @author      info@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_View_Main_Help extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	/**
	 * メイン処理
	 *
	 * @access  public
	 */
	function execute()
	{
		return 'success';
	}
}
?>
