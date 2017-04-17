<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ詳細画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Testee_View_Main_Print extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $content_id = null;
	var $comment_id = null;
	var $clear_comment = null;
	var $block_id = null;
	var $html_flag = null;
	var $search = null;

	// バリデートによりセット
	var $mdb_obj = null;
	var $detail = null;

	// 使用コンポーネントを受け取るため
	var $abbreviateurlView = null; //--URL短縮形関連
	var $mobileView = null;
	var $mdbView = null;
	var $mdbAction = null;		// 参照ログ書き込みのため EddyK

	// 値をセットするため
	var $short_url = ""; //--URL短縮形関連
	var $comments = null;				// コメント EddyK

	var $intervention = null;   // 割付設定

	/**
	 * コンテンツ詳細画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		// ---------- 詳細データ参照のログ保存 EddyK start. ----------
		$action_keys = array( "testee_id" => $this->testee_id,
		                      "content_id" => $this->content_id,
		                      "block_id" => $this->block_id );
		$this->mdbAction->addDatabaseViewLog( "SELECT Action=Print", $action_keys );
		// ---------- 詳細データ参照のログ保存 EddyK end. ----------
		//--URL短縮形関連
		$this->short_url = $this->abbreviateurlView->getAbbreviateUrl($this->content_id);
		$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		$this->search = intval($this->search);

// コメントの取得　EddyK
		$this->comments = $this->mdbView->getCommentList($this->testee_id);
		if($this->comments === false) {
			return 'error';
		}


		// 割付のテスト
		//$container =& DIContainerFactory::getContainer();
		//$mdbAction = $container->getComponent("mdbAction");

		//$datas = array ( 1017 => "Ⅲ", 1018 => "高分化" );
		//$mdbAction->setContentAllocation( $this->testee_id, $datas );

		// 割付設定の取得
		$this->allocation = $this->mdbView->getAllocationContent( $this->testee_id );

		return 'success';
	}
}
?>