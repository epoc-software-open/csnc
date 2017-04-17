<?php

/**
 * 回覧詳細画面表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Detail extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $circularAction = null;
	var $circularCommon = null;
	var $circularView = null;

	// 値をセットするため
	var $circular_id = null;
	var $period_class_name = null;
	var $circular_info = null;
	var $circular_users = null;
	var $count = null;
	var $postscripts = null;
	var $now_page = null;
	var $has_auth = null;
	var $has_create_auth = null;
	var $visible_row = null;
	var $visible_row_map = null;

	//ページ
    var $pager = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$circularInfo = $this->circularView->getCircularInfo();
		$this->circular_info = $circularInfo;

		$result = $this->circularAction->updateUserSeen('visit');
		if ($result === false) {
			return 'error';
		}

		if ($this->visible_row === null) {
			$this->visible_row = CIRCULAR_DEFAULT_VISIBLE_ROW;
		}

		$whereParams = array(
			'room_id'=>$this->room_id,
			'circular_id'=>$this->circular_id
		);
		$this->count = $this->db->countExecute('circular_user', $whereParams);
		$this->circularView->setPageInfo($this->pager, $this->count, $this->visible_row, $this->now_page);

		$circularUser = $this->circularView->getCircularUsers($circularInfo['reply_type'], true, $this->visible_row, $this->pager['disp_begin']);
		if ($circularUser === false) {
			return 'error';
		}
		$this->circular_users = $circularUser;

		$config = $this->circularView->getConfig();
		if ($config === false) {
			return 'error';
		}
		$authId = $this->session->getParameter('_auth_id');
		if ($authId >= $config['create_authority']) {
			$this->has_create_auth = _ON;
		} else {
			$this->has_create_auth = _OFF;
		}
		$this->has_auth = $this->circularView->hasAuthority($this->circular_id);

		$this->visible_row_map =& $this->circularCommon->getMap(CIRCULAR_VISIBLE_ROW);


		// --- 回覧板の添付ファイル参照履歴対応 by nagahara@opensource-workshop.jp

		// 見つけたupload_id の配列
		$body_upload_ids = array();

		// 回覧板の本文にある upload_id を抜き出す
		$search_pos = 0;

		// 無限ループが怖いので、一応 for で有限で回す
		for ( $i = 0; $i <= 100; $i++ ) {

			// upload_id= の開始場所
			$search_pos = strpos( $this->circular_info["circular_body"], "upload_id=", $search_pos );
			if ( $search_pos === false ) {
				// upload_id= がなければ、ループ終了
				break;
			}
			// upload_id= の終了場所
			$search_pos = $search_pos + 10;
			$search_pos_end = strpos( $this->circular_info["circular_body"], "\"", $search_pos );

			// 見つけたupload_id
			$body_upload_ids[] = substr( $this->circular_info["circular_body"], $search_pos, ( $search_pos_end - $search_pos ) );

			// 検索位置を進める
			$search_pos = $search_pos_end;
		}

		// ダウンロード履歴テーブルから、見つけたupload_id を取得する。(ファイル、ユーザ毎の最新)
		/*
			SELECT dl.file_name, MAX( dl.insert_time ) AS insert_time, dl.upload_id, dl.download_user_id, dl.download_user_name, dl.module_name
			FROM epoc_downloadlog dl
			WHERE dl.upload_id IN ( 2, 3 )
			GROUP BY dl.upload_id, dl.download_user_id
		*/

		$upload_id_in = implode( ",", $body_upload_ids );

		$downloadParams = array (
			'upload_id' => $upload_id_in
		);
		$sql  = "SELECT dl.file_name, MAX( dl.insert_time ) AS insert_time, dl.upload_id, dl.download_user_id, dl.download_user_name, dl.module_name ";
		$sql .= "FROM {downloadlog} dl ";
		$sql .= "WHERE dl.upload_id IN ( " . $upload_id_in . " ) ";
		$sql .= "GROUP BY dl.upload_id, dl.download_user_id ";
		$sql .= "ORDER BY dl.upload_id";

		$download_logs = $this->db->execute( $sql );
		if( $download_logs === false ) {
			return download_logs;
		}

		// 回覧ユーザをループ
		foreach ( $this->circular_users as &$circular_user ) {

			// 添付ファイルダウンロード履歴をループ
			foreach ( $download_logs as $download_log ) {

				// ユーザのダウンロード履歴があれば、情報を付与する
				if ( $circular_user["receive_user_id"] == $download_log["download_user_id"] ) {

					$circular_user["downloadlog"][$download_log["upload_id"]] = $download_log["file_name"] . timezone_date( $download_log["insert_time"], false, " (Y/m/d H:i)" );
				}
			}
		}
		//test_log($this->circular_users);

		// --- /回覧板の添付ファイル参照履歴対応 by nagahara@opensource-workshop.jp

		return 'success';
	}
}
?>
