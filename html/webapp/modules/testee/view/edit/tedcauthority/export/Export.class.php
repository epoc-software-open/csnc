<?php

/**
 * エクスポート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Testee_View_Edit_Tedcauthority_Export extends Action
{
	// リクエストパラメータをセットするため
	var $testee_id = null;
	var $block_id = null;
	var $room_id = null;

	// 使用コンポーネントを受け取るため
	var $csvMain = null;
	var $mdbView = null;

	// バリデートによりセット
	var $mdb_obj = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$metadatas = $this->mdbView->getMetadatas(array("testee_id" => intval($this->testee_id)));
		if($metadatas === false) {
			return 'error';
		}

		// ルーム参加の会員情報（会員情報のベース権限、試験毎権限付）を取得する
		$users = $this->mdbView->getRoomUsers($this->room_id, $this->testee_id);

		// CSV ヘッダー
		$header = array( "ハンドル名", "試験毎権限" );
		$this->csvMain->add($header);

		// CSV データ
		foreach($users as $user) {

			// 権限名称の編集(空の場合がある)
			$tedc_authority_name = "";
			if ( array_key_exists( "tedc_authority_name", $user ) && !empty( $user["tedc_authority_name"] ) ) {
				$tedc_authority_name = $user["tedc_authority_name"];
			}
			$this->csvMain->add( array( $user["handle"], $tedc_authority_name ) );
		}
		$this->csvMain->download($this->mdb_obj['testee_name']);

		exit;
	}
}
?>