<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Addcontentvalidate extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;
	var $testee_id = null;
	var $content_id = null;
//	var $upload_ids = null;
	var $temporary_flag = null;
	var $passwords = null;

	// バリデートによりセット
	var $mdb_obj = null;

	// 使用コンポーネントを受け取るため

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// Validator を走らせるためだけのアクション
		return true;
	}
}
?>