<?php

/**
 * ダウンロードログ初期表示画面
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2015 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Downloadlog_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;
	var $now_page = null;

	// 選択している年月
	var $ym_select = null;

	// 1ページでの表示行数
	var $visible_item = null;

	// 年月のリスト
	var $ym_list = null;

	// 使用コンポーネントを受け取るため
	var $downloadlogView = null;
	var $session = null;
	var $modules = null;

	// ログの存在月分一覧
	var $categories = null;

	// 一覧表示用ログ情報
	var $downloadlogs = null;

	// ページ
	var $pager = null;

	// ログ件数
	var $logs_count = null;

	/**
	 * [メイン処理]
	 *
	 * @access  public
	 */
	function execute() {

		// -----------------------------------------------------------
		// - ダウンロードログ
		// -----------------------------------------------------------

		// 月分一覧を取得する
		$this->ym_list = $this->downloadlogView->getYm();
		if( $this->ym_list === false ) {
			return 'error';
		}
		// print_r( $this->ym_list );

		// 表示件数指定による制御

		// 初期表示件数を設定
		if ( empty( $this->visible_item ) ) {
			$this->visible_item = DOWNLOADLOG_DEFAULT_VISIBLE_ITEM;
		}

		// --- ページャ

		// セッションの現在ページ(一つ前のアクションでのページ)
		$now_page = $this->session->getParameter( array( "downloadlog", "now_page" ) );

		// セッションでも画面でもページ数指定がない場合は、初期ページ
		if ( empty( $now_page ) && empty( $this->now_page ) ) {
			$this->now_page = 1;
		}

		// 画面でページが指定された場合は、そのページが現在ページ
		if ( !empty( $this->now_page ) ) {
			$this->session->setParameter( array( "downloadlog", "now_page" ), $this->now_page );

		// 画面でページが指定されていない場合は、セッションのページが現在ページ
		}else if ( !empty( $now_page ) ) {
			$this->now_page = $now_page;
		}

		// 対象の件数を取得
		$this->logs_count = $this->downloadlogView->getLogCount( $this->ym_select );

		// ページャの作成
		$this->downloadlogView->setPageInfo( $this->pager, $this->logs_count, $this->visible_item, $this->now_page );
		//print_r( $this->pager );

		// ログ情報を取得する(ページ対応した情報を取得)
		$this->downloadlogs = $this->downloadlogView->getLogList( $this->ym_select, $this->visible_item, $this->pager['disp_begin'] );
		//print_r( $this->downloadlogs );

		if ( $this->downloadlogs === false ) {
			return 'error';
		}

		return 'success';
	}
}
?>
