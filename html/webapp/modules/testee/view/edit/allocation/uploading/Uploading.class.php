<?php

/**
 * アップロード・処理中表示処理
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2014 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_View_Edit_Allocation_Uploading extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id  = null;
	var $room_id  = null;

	/**
	 * アップロード・処理中表示処理
	 *
	 * @access  public
	 */
	function execute() {

		return "success";
	}
}
?>
