<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付シミュレーション：症例ファイル削除処理
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Action_Edit_Delcasefile extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id   = null;
	var $block_id  = null;
	var $testee_id = null;

	// コンポーネントを受け取るため
	var $mdbAction     = null;		// 当Actionコンポーネント
	var $mdbView       = null;		// 当Viewコンポーネント
	var $uploadsAction = null;		// uploadActionコンポーネント
	
	/**
	 * 割付シミュレーション：症例ファイル削除処理
	 *
	 * @access  public
	 */
	function execute()
	{
//test_log(date('Y/m/d H:i:s') . ":Testee_Action_Edit_Delcasefile:Start");
//test_log("testee_id");
//test_log($this->testee_id);
		
		// 割付シミュレーション情報取得
		$simu_setting = $this->mdbView->getAllocationSimsetting( $this->testee_id );
//test_log("simu_setting");
//test_log($simu_setting);
		if( $simu_setting === false || count( $simu_setting ) <= 0 ) return 'error';
		
		// upload_idが空なら正常終了
		if( empty( $simu_setting[ "upload_id" ] ) === true ) return 'success';
		
		// 取得したupload_idのデータ削除（※0でupdate）
		$result = $this->mdbAction->updateAllocationSimsettingUploadID( $this->testee_id, 0 );
//test_log("DBからID削除結果");
//test_log($result);
		
		// ファイル削除
		$this->uploadsAction->delUploadsById( $simu_setting[ "upload_id" ] );
		
//test_log(date('Y/m/d H:i:s') . ":Testee_Action_Edit_Delcasefile:End");
		return 'success';
	}
	
	
}
?>
