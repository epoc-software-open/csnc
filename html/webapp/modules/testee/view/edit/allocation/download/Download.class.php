<?php

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Allocation_Download extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id   = null;	// ルームID 
	var $block_id  = null;
	var $testee_id = null;

	// コンポーネントを受け取るため
	var $mdbView = null;


	/**
	 * @access  public
	 */
	function execute()
	{
//test_log( date('Y/m/d H:i:s') . ":Testee_View_Edit_Allocation_Download:Start");
		
		// ***** 前処理 ********************************************************
		// 割付シミュレーション情報取得
		$simu_setting = $this->mdbView->getAllocationSimsetting( $this->testee_id );
		if( $simu_setting === false || count( $simu_setting ) <= 0 ) exit;
		
		// CSVがない場合はそのまま終了
		if( empty( $simu_setting[ "output_csv" ] ) === true ) exit;
		
		// ファイルの存在チェック
		if( file_exists( FILEUPLOADS_DIR . "testee/" . $simu_setting[ "output_csv" ] ) === false ) exit;
		
		
//		// ***** CSVファイルをZIP化する ****************************************
//		// インスタンス生成
//		$zip = new ZipArchive();
//		
//		// ZIPファイル名
//		$zip_filename = FILEUPLOADS_DIR . "testee/" . $simu_setting[ "output_csv" ] . ".zip";
//		
//		// ZIPファイルをオープン
//		$res = $zip->open( $zip_filename, ZipArchive::CREATE);
//
//		// zipファイルのオープンに成功した場合
//		if ($res === true)
//		{
//			$zip->addFile( FILEUPLOADS_DIR . "testee/" . $simu_setting[ "output_csv" ], $simu_setting[ "output_csv" ] );
//			
//			// ZIPファイルをクローズ
//			$zip->close();
//		}
		
		
//		// 出力ヘッダ
//		// クライアントの文字コードを取得
//		$clientEncode = $this->mdbView->getClientCharSet();
//		
//		// HTTPヘッダー出力
//		header("HTTP/1.1 200 OK");
//		header("Cache-Control: no-store, no-cache, must-revalidate");
//		header("Pragma: no-cache");
//		header("Content-disposition: inline; filename=\"". $simu_setting[ "output_csv" ] ."\"");
//		header("Content-type: document/unknown");
//		
//		// 実ファイルOPEN
//		$path = FILEUPLOADS_DIR . "testee/" . $simu_setting[ "output_csv" ];
//		$handle = fopen($path, 'r');
//		if($handle === false) return exit;
//		
//		while( feof( $handle ) === false )
//		{
//			// 1行読み込み
//			$record = trim( mb_convert_encoding( fgets( $handle ), "UTF-8", "auto" ) );
//			
//			echo mb_convert_encoding($record . "\n", $clientEncode, "UTF-8");
//		}
//		
//		fclose( $handle );
		
		$zip_filename = FILEUPLOADS_DIR . "testee/" . $simu_setting[ "output_csv" ];
		
		// HTTPヘッダー出力
		header("HTTP/1.1 200 OK");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
//		header("Content-disposition: inline; filename=\"". $simu_setting[ "output_csv" ] . ".zip" ."\"");
		header("Content-disposition: inline; filename=\"". $simu_setting[ "output_csv" ] ."\"");
		header("Content-length: ". filesize( $zip_filename ) );
		header("Content-type: application/zip");
		
		// ファイルダウンロード
		$handle = fopen( $zip_filename, 'rb');
		while (!feof($handle)) {
			echo fread($handle, 1 * (1024 * 1024));
			ob_flush();
			flush();
		}
		fclose($handle);
		
		// 不要になったZIPファイルを削除
		unlink( $zip_filename );
		
		
//test_log( date('Y/m/d H:i:s') . ":Testee_View_Edit_Allocation_Download:End");
		exit;
	}
	
}
?>
