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
class Testee_View_Edit_Tedcauthority_Edit extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	var $testee_id = null;
	var $user_id = null;

    // 使用コンポーネントを受け取るため
	var $mdbView = null;

    // 値をセットするため
	var $user = null;


    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		// 会員情報を取得する
		$this->user = $this->mdbView->getUserINFO($this->room_id, $this->testee_id, $this->user_id);
		
		// 割付参照権限ユーザーリストを取得
		$view_user = $this->mdbView->getAllocationViewUser( $this->testee_id, $this->user_id );
		if( $view_user !== false && count( $view_user ) > 0 )
		{
			$this->user[ 0 ][ "allocation_view" ] = _ON;
		}
		
        return 'success';
    }
}
?>
