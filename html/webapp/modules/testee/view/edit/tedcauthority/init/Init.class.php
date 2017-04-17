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
class Testee_View_Edit_Tedcauthority_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;
	var $testee_id = null;

    // 使用コンポーネントを受け取るため
	var $mdbView = null;
	var $session = null;

    // 値をセットするため
	var $users = null;

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		// ルーム参加の会員情報（会員情報のベース権限、試験毎権限付）を取得する
		$this->users = $this->mdbView->getRoomUsers($this->room_id, $this->testee_id);

		//if( $this->session->getParameter( "_role_auth_id" ) == _ROLE_AUTH_ADMIN ) {
		if( $this->session->getParameter( "_user_auth_id" ) == _AUTH_ADMIN ) {
	        return 'success';
		} else {
	        return 'success_notadmin';
		}
    }
}
?>
