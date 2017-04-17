<?php

/**
 * ログ出力
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2015 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Downloadlog_View_Edit_Export extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	// 選択している年月
	var $ym_select = null;

	// 使用コンポーネントを受け取るため
	var $downloadlogView = null;
	var $csvMain = null;
	var $select_ym = null;

	// 画面に渡す値

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute() {

		ini_set('memory_limit', '-1');
		// -----------------------------------------------------------
		// - アクセスログ
		// -----------------------------------------------------------

		// データ取得
		$downloadlogs = $this->downloadlogView->getLogList( $this->ym_select );

		// ファイル名
		$filename = 'ダウンロードログ-' . $this->ym_select;
		$filename = mb_convert_encoding( $filename, _CLIENT_OS_CHARSET, "UTF-8" ) . '.csv';

		// HTTPヘッダー出力
		header("HTTP/1.1 200 OK");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-disposition: inline; filename=\"".$filename."\"");
		header("Content-type: document/unknown");

		// CSVヘッダー出力
		$csv_title = '"ダウンロード日時","ファイル名","モジュール名","ルーム名","ダウンロードユーザ","ファイル有無","upload_id"';
		echo( mb_convert_encoding( $csv_title, "SJIS-win", "UTF-8" ) . "\n" );

		// CSVデータ出力
		foreach( $downloadlogs as &$log_item ) {
			$line =
				'"'. timezone_date( $log_item['insert_time'], false, 'Y-m-d H:i:s' ) . '",' .
				'"'. $log_item['file_name'] . '",' .
				'"'. $log_item['module_name'] . '",' .
				'"'. $log_item['room_name'] . '",' .
				'"'. $log_item['download_user_name'] . '",';

				if ( $log_item['uploads_upload_id'] ) {
					$line .= '"有",';
				}
				else {
					$line .= '"無",';
				}

				$line .= '"'. $log_item['upload_id'] . '",' .
				"\n";
			echo mb_convert_encoding($line, _CLIENT_OS_CHARSET, "UTF-8");
		}

		exit;
	}
}
?>
