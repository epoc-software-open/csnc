<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付状態チェック処理
 *   コンテンツ登録時に割付状態をチェックする為のValidatorを走らせる為のダミー処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Checkallocation extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id   = null;
	var $room_id    = null;
	var $testee_id  = null;
	var $content_id = null;

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
//test_log( date('Y/m/d H:i:s') . ":Testee_Action_Main_Checkallocation:execute" );
		return true;
	}
}
?>