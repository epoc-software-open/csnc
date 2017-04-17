<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
class Crfif_View_Metalist extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	var $loginid = null;	// ログインID
	var $password = null;	// パスワード
	var $multidatabase_id = null;	// データベースID

	// 使用コンポーネントを受け取るため
	var $crfifView = null;

	// 値をセットするため

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{

		// 認証＆結果レスポンス
		echo $this->crfifView->get_metalist( $this->loginid, $this->password, $this->multidatabase_id );
		exit;
	}
}
?>
