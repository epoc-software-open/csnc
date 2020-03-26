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
class Testee_Action_Edit_Tedcauthority extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    var $testee_id = null;
    var $user_id = null;
    var $tedc_authority = null;
    var $view_auth = null;

    // 使用コンポーネントを受け取るため
    var $mdbView = null;
    var $mdbAction = null;

    // 値をセットするため

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		$paramas = array(
			"testee_id" => $this->testee_id,
			"user_id" => $this->user_id,
			"tedc_authority" => $this->tedc_authority
		);

		$tedc_date = $this->mdbView->getTEDC($this->testee_id, $this->user_id);
		if($tedc_date === false) return 'error';

		$result = true;
		if($this->tedc_authority != "" && $this->tedc_authority != null){
			if($tedc_date){
				// 更新
				$result = $this->mdbAction->tedcauthorityUpdate($paramas);
			} else {
				// 新規登録
				$result = $this->mdbAction->tedcauthorityInsert($paramas);
			}
		} else {
			if($tedc_date){
				// 削除
				$result = $this->mdbAction->tedcauthorityDelete($paramas);
			}
		}
		if($result === false){
			return 'error';
		}
		
		// 割付参照権限情報変更
		$now_info = $this->mdbView->getAllocationViewUser( $this->testee_id, $this->user_id );
		if( $now_info === false ) return 'error';
		if( empty( $this->view_auth ) === true )
		{
			// 空の場合は削除（※データがあれば）
			if( count( $now_info ) > 0 && empty( $now_info[ 0 ]["viewuser_id"] ) === false )
			{
				$result = $this->mdbAction->deleteAllocationViewUser( $now_info[ 0 ]["viewuser_id"] );
				if( $result === false ) return 'error';
			}
		}
		else
		{
			// 値がある場合は登録（※データがなければ）
			if( count( $now_info ) <= 0 )
			{
				$result = $this->mdbAction->insertAllocationViewUser( $this->testee_id, $this->user_id );
				if( $result === false ) return 'error';
			}
		}
		
        return 'success';
    }
}
?>
