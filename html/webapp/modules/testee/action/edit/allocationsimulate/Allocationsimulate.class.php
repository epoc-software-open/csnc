<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付シミュレーション実施処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Allocationsimulate extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id   = null;
	var $block_id  = null;
	var $testee_id = null;

	// コンポーネントを受け取るため
	var $mdbView   = null;
	var $mdbAction = null;
	
	var $init_flag = null;
	
	
	/**
	 * 割付シミュレーション実施処理
	 *
	 * @access  public
	 */
	function execute()
	{
//test_log( date('Y/m/d H:i:s') . ":Testee_Action_Edit_Allocationsimulate:Start" );
//$time_start = microtime(true);
		
		// *****************************************************************************************
		// 前処理
		// *****************************************************************************************
		
		// 一時テーブルに保管された自動生成したデータを削除する
		$result = $this->mdbAction->deleteAllocationSimResult( $this->testee_id );
		
		// シミュレーション設定を呼び出す（Validatorで登録してる）
		$simu_setting = $this->mdbView->getAllocationSimsetting( $this->testee_id );
		
		// 割付情報取得と確認
		$allocation = $this->mdbView->getAllocationContent( $this->testee_id );
		if( $allocation === false || count( $allocation ) <= 0 ) return 'error';
		
		// 割付群情報取得と確認
		$group_contents = $this->mdbView->getGroupContent( $this->testee_id );
		if( $group_contents === false || count( $group_contents ) <= 0 ) return 'error';
		
		// 割付群名リスト取得
		$group_names = $this->getGroupNameList( $group_contents );
		
		
		if( $allocation["allocation_flag"] == 1 )
		{
			// 最小化法用に組み替えられた割付群取得
			$min_group_contents = $this->getMinGroupContent( $allocation, $group_contents );
			if( $min_group_contents === false || count( $min_group_contents ) <= 0 ) return 'error';
			
			// ----- 最小化法 --------------------------------------------------
// 19.03.22 change start by makino@opensource-workshop.jp 因子情報は、強制割付の有無に関係なく取得するように変更
//			if( $allocation["force_allocation_flag"] == 1 )
//			{
//				// 強制割付の場合
//				
//				// 割付因子の各項目取得
//				$allocate_factors = $this->mdbView->getSelectedAllocationFactors( $this->testee_id );
//				if( $allocate_factors === false || count( $allocate_factors ) <= 0 ) return 'error';
//				
//				// 取得した割付因子項目を組み替え
//				$factor_list = $this->mdbView->getAllocationItemList( $allocate_factors );
//				
//				// 現在の割付因子の項目で、組合せパタン作成
//				$now_pattern = $this->mdbView->getConbinationPattern( $factor_list );
//			}
//			else
//			{
//				// 強制割付なしの場合
//				
//				$allocate_factors = array();
//				$factor_list      = array();
//				$now_pattern      = array();
//			}
			
			// 割付因子の各項目取得
			$allocate_factors = $this->mdbView->getSelectedAllocationFactors( $this->testee_id );
			if( $allocate_factors === false || count( $allocate_factors ) <= 0 )
			{
				// 調整因子を使ってない
				$allocate_factors = array();
				$factor_list      = array();
				$now_pattern      = array();
			}
			else
			{
				// 調整因子を使ってる
				
				// 取得した割付因子項目を組み替え
				//                   │"item_name" │"select_content"                  │"factor_ratio"
				// ─────────┼──────┼─────────────────┼───────
				// 性別のmetadata_id │"性別"      │array("男","女")                  │array(50,50)
				// がん種のmetadta_id│"がん種"    │array("肺がん","胃がん","膵がん") │array(40,30,30)
				
				$factor_list = $this->mdbView->getAllocationItemList( $allocate_factors );
				
				// 現在の割付因子の項目で、組合せパタン作成
				//   │0   │1 
				// ─┼──┼────
				// 0 │"男"│"肺がん"
				// 1 │"男"│"胃がん"
				$now_pattern = $this->mdbView->getConbinationPattern( $factor_list );
			}
// 19.03.22 change end
		}
		else
		{
			// ----- 置換ブロック法 --------------------------------------------
			// 割付群が変更されているかどうかチェックする
			if( $this->mdbView->changeAllocateGroupContents( $group_contents ) === false ) return 'error';
			
			// 割付因子の各項目取得
			$allocate_factors = $this->mdbView->getSelectedAllocationFactors( $this->testee_id );
			if( $allocate_factors === false || count( $allocate_factors ) <= 0 ) return 'error';
			
			// 割付層情報取得
			$allocation_conbinations = $this->mdbView->getAllocationcConbination( $this->testee_id );
			if( $allocation_conbinations === false || count( $allocation_conbinations ) <= 0 ) return 'error';
			
			// 取得した割付因子項目を組み替え
			//                   │"item_name" │"select_content"                  │"factor_ratio"
			// ─────────┼──────┼─────────────────┼───────
			// 性別のmetadata_id │"性別"      │array("男","女")                  │array(50,50)
			// がん種のmetadta_id│"がん種"    │array("肺がん","胃がん","膵がん") │array(40,30,30)
			$factor_list = $this->mdbView->getAllocationItemList( $allocate_factors );
			
			// 現在の割付因子の項目で、組合せパタン作成
			//   │0   │1 
			// ─┼──┼────
			// 0 │"男"│"肺がん"
			// 1 │"男"│"胃がん"
			$now_pattern = $this->mdbView->getConbinationPattern( $factor_list );
			
			// 現在の組み合わせパターンと、既存層のパターンを比較
			if( $this->mdbView->matchAllocationFactors( $now_pattern, $allocation_conbinations ) === false ) return 'error';
			
			// 割付群の比率の合計値取得
			$total_ratio = $this->mdbView->getTotalRatio( $group_contents );
			
			// 割付層の各ブロック数が正しいかどうかチェックする
			if( $this->mdbView->checkRatioBlockCount( $allocation_conbinations, $total_ratio ) === false ) return 'error';
			
			// 可変ブロック情報取得
			$variable_block = $this->mdbView->getAllocateVariableBlock( $this->testee_id );
			if( $variable_block === false ) return 'error';
			
			// 必要となる割付パタンを事前に作成しておく（※繰り返しの中で作成すると恐ろしく時間がかかる為）
			$allocate_block_patterns = array();
			// 各割付層をループ
			foreach( $allocation_conbinations as $record )
			{
				// ブロック数を分割
				$next_block_count_list = explode( ",", $record[ "next_block_count" ] );
				
				// 除外ブロック数を分割
				if( empty( $record[ "exclude_count" ] ) === true )
				{
					$exclude_count_list = "";
				}
				else
				{
					$exclude_count_list = explode( ",", $record[ "exclude_count" ] );
				}
				
				$i = 0;
				for( $i = 0; $i < count( $next_block_count_list ); $i++ )
				{
					// 設定除外連続数
					$set_exclude_count = ( empty( $exclude_count_list ) === true ) ? 0 : $exclude_count_list[ $i ];
					
					if( array_key_exists( $next_block_count_list[ $i ], $allocate_block_patterns ) === false )
					{
						// まだ指定されたパターン数の層がない
						$allocate_block_patterns[ $next_block_count_list[ $i ] ] = array();
						$allocate_block_patterns[ $next_block_count_list[ $i ] ][ $set_exclude_count ] = $this->mdbView->getAllocateBlockPattern( $group_contents, $next_block_count_list[ $i ], $set_exclude_count );
					}
					else
					{
						// 指定されたパターン数の層が既にある
						if( array_key_exists( $set_exclude_count, $allocate_block_patterns[ $next_block_count_list[ $i ] ] ) === false )
						{
							// まだない場合だけ新規作成
							$allocate_block_patterns[ $next_block_count_list[ $i ] ][ $set_exclude_count ] = $this->mdbView->getAllocateBlockPattern( $group_contents, $next_block_count_list[ $i ], $set_exclude_count );
						}
					}
				}
			}
//test_log("必要となる割付パタンを事前に作成しておく");
//test_log($allocate_block_patterns);
		}
		
		
		
		// *****************************************************************************************
		// 割付シミュレーション処理
		// *****************************************************************************************
		
		// 実行時間を10800秒(3時間)に設定
		set_time_limit(10800);
		
		
		// テスト用データ取得
		$file_path = "";
		if( $simu_setting[ "input_type" ] == 0 )
		{
			// ----- 自動生成 --------------------------------------------------
			
// 19.03.22 change start by makino@opensource-workshop.jp [自動生成]ONの場合は割付法や強制割付設定有無ではなく、因子の指定があるかないかで判定する
//			if( $allocation["allocation_flag"] == 2 || 
//			  ( $allocation["allocation_flag"] == 1 && $allocation["force_allocation_flag"] == 1 ) )
//			{
			if( count( $factor_list ) > 0 )
			{
// 19.03.22 change end
				// 置換ブロック法、または、最小化法で強制割付ありの場合は、割付因子の比率通りにデータを作成する
				$file_path = $this->createCaseData( $simu_setting, $factor_list );
				if( $file_path === false ) return 'error';
			}
			
		}
		else
		{
			// ----- ファイルから読み込み --------------------------------------
			$file_info = $this->mdbView->getCaseFileInfo( $simu_setting[ "upload_id" ] );
			
			// ファイルパス取得
			$file_path = FILEUPLOADS_DIR . "testee/" . $file_info["physical_file_name"];
			
		}
		
		
		// ***** 指定された数分、割付シミュレーションを繰り返す ****************
		// 作成されるデータは以下の形式
		//           │A群 │B群
		// ─────┼──┼───
		// 男｜肺がん│10  │2
		// 男｜胃がん│4   │6
		// 女｜肺がん│9   │1
		// 女｜胃がん│1   │10
		$base_layer_array = array();
		$group_list = array();
		foreach( $group_contents as $record )
		{
			$group_list[ $record[ "allocation_group_id" ] ] = 0;
		}
		
// 19.03.22 change start by makino@opensource-workshop.jp 調整因子が設定されているか否かの判定に変更
//		if( $allocation["allocation_flag"] == 2 || 
//		  ( $allocation["allocation_flag"] == 1 && $allocation["force_allocation_flag"] == 1 ) )
//		{
		if( count( $now_pattern ) > 0 )
		{
// 19.03.22 change end
			// 割付因子を使用する場合は、パターンをキーとして作成してベースに設定する
			foreach( $now_pattern as $pattern )
			{
				$create_key = implode( "|", $pattern );
				$base_layer_array[ $create_key ] = $group_list;
			}
		}
		else
		{
			// 割付因子を使用しない場合は、キーなしでベースに設定する
			$base_layer_array[] = $group_list;
		}
		
		
		if( $allocation[ "allocation_flag" ] == 1 )
		{
			// ----- 最小化法 --------------------------------------------------

			// 最小化法での総合計件数を取得する
			$total_data = $this->simurationMinimizate( $file_path, $allocation, $simu_setting, $factor_list, $min_group_contents, $group_names, $allocate_factors, $base_layer_array );
			if( $total_data === false ) return 'error';
		}
		else
		{
			// ----- 置換ブロック法 --------------------------------------------
			
			// 置換ブロック法で総合計件数を取得する
			$total_data = $this->simurationBlock( $file_path, $simu_setting, $group_contents, $group_names, $allocate_factors, $factor_list, $allocation_conbinations, $variable_block, $base_layer_array, $allocate_block_patterns );
			if( $total_data === false ) return 'error';
		}
		
		
		// ***** 使い終わった自動生成CSVファイルを削除する *********************
		if( $simu_setting[ "input_type" ] == 0 )
		{
			unlink( $file_path );
		}
		
		
		// ***** 結果をDBに一時登録 ********************************************
		foreach( $total_data as $key => $record )
		{
			$content_list = array();
			foreach( $record as $group_id => $count )
			{
				$content_list[] = $group_id . "|" .  $count;
			}
			
			$content = implode( ",", $content_list );
			
			// 登録
			$result = $this->mdbAction->insertAllocationSimResult( $this->testee_id, $key, $content );
			
			$this->init_flag = 0;
		}
		
		
//$time = microtime(true) - $time_start;
//test_log("実測[" . round( $time, 2 ) . "]");
//test_log( date('Y/m/d H:i:s') . ":Testee_Action_Edit_Allocationsimulate:End" );
		return 'success';
	}
	
	
	//**********************************************************************************************
	// 以下、内部関数定義
	//**********************************************************************************************
	
	/**
	 * 症例データ自動生成処理
	 *
	 * 症例数、繰り返し数によってはメモリに持ちきれないので、作成したらCSVへ都度出力する。
	 * 登録済み各因子の件数をカウントしておき、いちいち登録済みデータを検索しなくて済むようにする
	 *
	 * @param  array simu_setting シミュレーション設定
	 * @param  array factor_list  因子リスト
	 * @return string パス付のCSVファイル名
	 * @access  private
	 */
	private function createCaseData( $simu_setting, $factor_list )
	{
		// 登録済み件数を保持する配列の構成
		// 性別のmetadata_id   │array( "男" => 10, "女" => 15 )
		// がん種のmetadata_id │array( "肺がん" => 8, "胃がん" => 9, "膵がん" => 8 )
		
		
		// 登録数リストのベースを作成する
		$base_entry_count_list = array();
		foreach( $factor_list as $metadata_id => $factor )
		{
			$base_entry_count_list[ $metadata_id ] = array();
			foreach( $factor[ "select_content" ] as $content )
			{
				$base_entry_count_list[ $metadata_id ][ $content ] = 0;
			}
		}
		
		
		// 出力するファイル用ディレクトリパス取得（ディレクトリがなければ作る）
		// ファイルを作成するuploadsディレクトリパス
		$uploads_dir = FILEUPLOADS_DIR . "testee/";
		
		// ディレクトリ作成
		if( file_exists( $uploads_dir ) === false )
		{
			if( mkdir( $uploads_dir ) === false ) return false;
		}
		
		// ファイル名
		$file_name_base = "testee_simcase_tmp_" . date('YmdHis');
		$file_name_csv  = $file_name_base . ".csv";
		$file_name_zip  = $file_name_base . ".zip";
		
		// 自動生成用シードを設定する
		mt_srand( $simu_setting[ "case_seed" ] );
		
		// ***** CSVファイルへ書き出し *************************************************************
		$handle = fopen($uploads_dir . $file_name_csv, 'w');
		
		// 繰り返し数
		for( $repeat_index = 1; $repeat_index <= $simu_setting[ "repeat_count" ]; $repeat_index++ )
		{
			// 登録済み件数
			$entry_count_list = $base_entry_count_list;
			
			// 症例数分繰り返し
			for( $entry_index = 1; $entry_index <= $simu_setting[ "case_count" ]; $entry_index++ )
			{
				$record = array();
				
				$id = sprintf( "%05d-%05d", $repeat_index, $entry_index );
				$record[ "id" ] = $id;
				
				// 因子毎に回す
				foreach( $factor_list as $metadata_id => $factor )
				{
					// 比率のリストを取得する
					$can_factor = array();
					foreach( $factor[ "factor_ratio" ] as $key => $value )
					{
						$can_factor[ $factor[ "select_content" ][ $key ] ] = $value;
					}
					
					$set_value = $this->mdbAction->rndAnswer( $can_factor );
					
					// レコードに設定
					$record[ $metadata_id ] = $set_value;
					
					// 件数カウントアップ
					if( array_key_exists( $metadata_id, $entry_count_list ) === false )
					{
						$entry_count_list[ $metadata_id ] = array();
					}
					if( array_key_exists( $set_value, $entry_count_list[ $metadata_id ] ) === false )
					{
						$entry_count_list[ $metadata_id ][ $set_value ] = 0;
					}
					$entry_count_list[ $metadata_id ][ $set_value ] += 1;
				}
				
				// 結合する
				$output_line = implode( ",", $record );
				
				// 文字コード変換
				$output_line = mb_convert_encoding($output_line . "\n", 'SJIS-win', _CHARSET);
				
				// CSVファイルに出力する
				fwrite( $handle, $output_line );
			}
		}
		
		// ファイルCLOSE
		fclose($handle);
		
		
		// ***** 作成したファイルをZIP圧縮する *****************************************************
		$this->createZipFile( $uploads_dir, $file_name_zip, $file_name_csv );
		
		
		// ***** 割付シミュレーション情報更新 ******************************************************
		// ※この時作成したZIPファイルはTMP扱いなので、割付シミュ設定情報には書き込まない。
		// 　ただし、情報が残ってても困るので一度消す
		$result = $this->mdbAction->updateAllocationSimsettingOutputCSV( $this->testee_id, "" );
		
		// 以前のファイルを消す
		if( empty( $simu_setting[ "output_csv" ] ) === false )
		{
			$uploads_dir = FILEUPLOADS_DIR . "testee/";
			$result = unlink( $uploads_dir . $simu_setting[ "output_csv" ] );
		}
		
		// パス付のファイル名を返却する
		return $uploads_dir . $file_name_zip;
		
	}
	
	
	/**
	 * 因子値につき設定する件数を取得する
	 *
	 * ( 症例数 ÷ 比率の合計値 ) × 当該因子値の比率
	 * 
	 * @param  int   case_count 症例件数
	 * @param  array factor     因子情報
	 * @return array 自動生成した症例データ
	 * @access  private
	 */
	private function getSetCounts( $case_count, $factor )
	{
		$summary = array_sum( $factor[ "factor_ratio" ] );
		
		$shou = floor( $case_count / $summary);
		
		$result_list = array();
		foreach( $factor[ "select_content" ] as $key => $value )
		{
			$result_list[ $value ] = $shou * $factor[ "factor_ratio" ][ $key ];
		}
		
		// 戻り値の形式
		// 配列[ "男" ] = 男性の設定数
		// 配列[ "女" ] = 女性の設定数
		
		return $result_list;
	}
	
	
	/**
	 * 最小化法での総件数を取得する
	 *
	 * @param  string file_path          症例CSVファイルパス
	 * @param  array  allocation         割付設定
	 * @param  array  simu_setting       シミュレーション設定
	 * @param  array  factor_list        
	 * @param  array  min_group_contents 最小化法用に組み替えた割付群リスト
	 * @param  array  group_names        割付群名リスト
	 * @param  array  allocate_factors   
	 * @param  array  base_layer_array   
	 * @return array  総合計数リスト
	 * @access  private
	 */
	private function simurationMinimizate( $file_path, $allocation, $simu_setting, $factor_list, $min_group_contents, $group_names, $allocate_factors, $base_layer_array )
	{
		// 割付キーのシード設定
		mt_srand( $simu_setting[ "allocate_seed" ] );
		
		$set_allocation_count_list = array();
		
		
// 19.03.22 change start by makino@opensource-workshop.jp 強制割付設定有無ではなく、調整因子設定有無に判定変更
//		if( $allocation["force_allocation_flag"] == 1 )
//		{
//			// ----- 強制割付あり ----------------------------------------------
		if( count( $factor_list ) > 0 )
		{
			// ----- 割付調整因子設定あり --------------------------------------
// 19.03.22 change end
			
			// 出力用ファイル関連の設定
			$uploads_dir = FILEUPLOADS_DIR . "testee/";
			$output_file_base = "testee_simulation_casefile_" . date('YmdHis');
			$output_file_csv  = $output_file_base . ".csv";
			$output_file_zip  = $output_file_base . ".zip";
			
			// 出力ファイルOPEN
			$output_handle = fopen($uploads_dir . $output_file_csv, 'w');
			
			
			// ファイルOPEN
			$handle = null;
			$zip    = null;
			$result = $this->mdbView->openFile( $file_path, $handle, $zip );
			if( $result !== 0 ) return false;
			
			// 再読み込み用ラベル
			start :
			
			while( feof( $handle ) === false )
			{
				// 1件読み込み
				$record = trim( mb_convert_encoding( fgets( $handle ), "UTF-8", "auto" ) );
				
				// 空白行読み飛ばし
				if( empty( $record ) === true ) goto start;
				
				// 分解
				$record_list = explode( ",", $record );
				
				// ID分解
				$id_list = explode( "-", $record_list[0] );
				
				// 設定キー取得
				$repeat_key = intval( $id_list[0] );
				
				if( array_key_exists( $repeat_key, $set_allocation_count_list ) === false )
				{
					$set_allocation_count_list[ $repeat_key ] = $base_layer_array;
				}
				
				
				// 値にmetadata_idを設定する
				$index = 1;
				$factor_record = array();
				foreach( $factor_list as $metadata_id => $factor )
				{
					$factor_record[ $metadata_id ] = $record_list[ $index ];
					$index++;
				}
				
				// 割付群取得
				$set_group_id = $this->allocMinimizate( $allocation, $factor_record, $min_group_contents, $allocate_factors, $set_allocation_count_list[ $repeat_key ] );
				
				// 層キー作成
				$conbination_key = implode( "|", $factor_record );
				
				// 群カウントアップ
				$set_allocation_count_list[ $repeat_key ][ $conbination_key ][ $set_group_id ] += 1;
				
				// 読み込んだレコードの末尾に割り付け群名を追記して出力する
				$output_line = str_replace(array("\r\n", "\r", "\n"), '', $record) . "," . $group_names[ $set_group_id ];
				$output_line = mb_convert_encoding($output_line . "\n", 'SJIS-win', _CHARSET);
				fwrite( $output_handle, $output_line );
			}
			
			// ファイルCLOSE
			fclose( $handle );
			
			// ZIPCLOSE
			if( is_null( $zip ) === false ) $zip->close();
			
			// 出力CSVを閉じる
			fclose( $output_handle );
			
			// 作成した出力CSVをZIP圧縮して不要なCSVを削除する
			$this->createZipFile( $uploads_dir, $output_file_zip, $output_file_csv );
			
			// 割付シミュ設定情報をUPDATE
			$result = $this->mdbAction->updateAllocationSimsettingOutputCSV( $this->testee_id, $output_file_zip );
			
		}
		else
		{
// 19.03.22 change start by makino@opensource-workshop.jp 強制割付設定有無ではなく、調整因子設定有無に判定変更
//			// ----- 強制割付なし ----------------------------------------------
			// ----- 割付調整因子設定なし --------------------------------------
// 19.03.22 change end
			for( $repeat_index = 0; $repeat_index < $simu_setting[ "repeat_count" ]; $repeat_index++ )
			{
				$set_allocation_count_list[ $repeat_index ] = $base_layer_array;
				
				// ----- 因子なし -----
				for( $case_index = 0; $case_index < $simu_setting[ "case_count" ]; $case_index++ )
				{
					// 割付群取得
					$set_group_id = $this->allocMinimizate( $allocation, array(), $min_group_contents, array(), $set_allocation_count_list[ $repeat_index ] );
					
					// 群カウントアップ
					$set_allocation_count_list[ $repeat_index ][ 0 ][ $set_group_id ] += 1;
					
				}
			}
		}
		
		
		// 返却用総合計データに全部の合計値を設定
		$total_data = $base_layer_array;
		foreach( $set_allocation_count_list as $alloc_counts )
		{
			foreach( $alloc_counts as $conbination_key => $group_counts )
			{
				foreach( $group_counts as $group_id => $count )
				{
					$total_data[ $conbination_key ][ $group_id ] += $count;
				}
			}
		}
		
		// 合計した件数を返却する
		return $total_data;
		
	}
	
	
	/**
	 * 置換ブロック法での総件数を取得する
	 *
	 * @param  string file_path               症例CSVファイルパス
	 * @param  array  simu_setting            シミュレーション設定
	 * @param  array  group_contents          割付群リスト
	 * @param  array  group_names             割付群名リスト
	 * @param  array  allocate_factors        
	 * @param  array  factor_list             
	 * @param  array  allocation_conbinations 
	 * @param  array  variable_block          
	 * @param  array  base_layer_array        
	 * @return array  総合計数リスト
	 * @access  private
	 */
	private function simurationBlock( $file_path, $simu_setting, $group_contents, $group_names, $allocate_factors, $factor_list, $allocation_conbinations, $variable_block, $base_layer_array, $allocate_block_patterns )
	{
		// 割付キーのシード設定
		mt_srand( $simu_setting[ "allocate_seed" ] );
		
		$set_allocation_count_list = array();
		$content_count_list        = array();
		$total_block_list          = array();
		
		// 出力用ファイル関連の設定
		$uploads_dir = FILEUPLOADS_DIR . "testee/";
		$output_file_base = "testee_simulation_casefile_" . date('YmdHis');
		$output_file_csv  = $output_file_base . ".csv";
		$output_file_zip  = $output_file_base . ".zip";
		
		// 出力ファイルOPEN
		$output_handle = fopen($uploads_dir . $output_file_csv, 'w');
		
		
		// ファイルOPEN
		$handle = null;
		$zip    = null;
		$result = $this->mdbView->openFile( $file_path, $handle, $zip );
		if( $result !== 0 ) return false;
		
		// 再読み込み用ラベル
		start :
		
		while( feof( $handle ) === false )
		{
			// 1件読み込み
			$record = trim( mb_convert_encoding( fgets( $handle ), "UTF-8", "auto" ) );
			
			// 空白行読み飛ばし
			if( empty( $record ) === true ) goto start;
			
			// 分解
			$record_list = explode( ",", $record );
			
			// ID分解
			$id_list = explode( "-", $record_list[0] );
			
			// 設定キー取得
			$repeat_key = intval( $id_list[0] );
			
			if( array_key_exists( $repeat_key, $set_allocation_count_list ) === false )
			{
				$set_allocation_count_list[ $repeat_key ] = $base_layer_array;
			}
			$layer_array = $set_allocation_count_list[ $repeat_key ];
			
			if( array_key_exists( $repeat_key, $content_count_list ) === false )
			{
				$content_count_list[ $repeat_key ] = 1;
			}
			if( array_key_exists( $repeat_key, $total_block_list ) === false )
			{
				foreach( $allocation_conbinations as $conbination )
				{
					$total_block_list[ $repeat_key ][ $conbination[ "factor_contents" ] ] = array();
				}
			}
			
			// 値にmetadata_idを設定する
			$index = 1;
			$factor_record = array();
			foreach( $factor_list as $metadata_id => $factor )
			{
				$factor_record[ $metadata_id ] = $record_list[ $index ];
				$index++;
			}
			
			// 割付群取得
			$set_group_id = $this->allocBlock( $group_contents, $allocate_factors, $allocation_conbinations, $variable_block, $content_count_list[ $repeat_key ], $total_block_list[ $repeat_key ], $factor_record, $allocate_block_patterns );
			
			// 層キー作成
			$conbination_key = implode( "|", $factor_record );
			
			// 群カウントアップ
			$set_allocation_count_list[ $repeat_key ][ $conbination_key ][ $set_group_id ] += 1;
			
			$content_count_list[ $repeat_key ] += 1;
			
			// 読み込んだレコードの末尾に割り付け群名を追記して出力する
			$output_line = str_replace(array("\r\n", "\r", "\n"), '', $record) . "," . $group_names[ $set_group_id ];
			$output_line = mb_convert_encoding($output_line . "\n", 'SJIS-win', _CHARSET);
			fwrite( $output_handle, $output_line );
		}
		
		// ファイルCLOSE
		fclose( $handle );
		
		// ZIPCLOSE
		if( is_null( $zip ) === false ) $zip->close();
		
		
		// 出力CSVを閉じる
		fclose( $output_handle );
		
		// 作成した出力CSVをZIP圧縮して不要なCSVを削除する
		$this->createZipFile( $uploads_dir, $output_file_zip, $output_file_csv );
		
		// 割付シミュ設定情報をUPDATE
		$result = $this->mdbAction->updateAllocationSimsettingOutputCSV( $this->testee_id, $output_file_zip );
		
		
		// 返却用総合計データに全部の合計値を設定
		$total_data = $base_layer_array;
		foreach( $set_allocation_count_list as $alloc_counts )
		{
			foreach( $alloc_counts as $conbination_key => $group_counts )
			{
				foreach( $group_counts as $group_id => $count )
				{
					$total_data[ $conbination_key ][ $group_id ] += $count;
				}
			}
		}
		
		// 合計した件数を返却する
		return $total_data;
		
	}
	
	
	
	/**
	 * 最小化法で割付
	 *
	 * @param  array allocation         割付設定
	 * @param  array datas              登録症例データ
	 * @param  array min_group_contents 最小化法の割付群リスト
	 * @param  array allocate_factors   割付因子リスト
	 * @param  array layer_array        
	 * @return 
	 * @access  private
	 */
	private function allocMinimizate( $allocation, $datas, $min_group_contents, $allocate_factors, $layer_array )
	{
		// 割付確率
		$force_probability_flag = false;            // 強制割付する確率
		$allocation["allocation_probability"];      // 割付確率
		$force_probability = mt_rand(0, 99);        // 割付する乱数

		// 発生した乱数が割付確率より小さければ、強制割付
		if ( $force_probability < $allocation["allocation_probability"] ) {
			$force_probability_flag = true;
		}

		// 強制割付するにチェック＆割付確率で割付になった場合
		if ( $allocation["force_allocation_flag"] == 1 && $force_probability_flag )
		{
			// 後で強制割付処理
		}
		// ランダム割付
		else 
		{
			$ans = $this->mdbAction->rndAnswer( $min_group_contents );
			return $ans;
		}

		// --- 以下は強制割付

// 19.03.22 change start by makino@opensource-workshop.jp 調整因子毎の群件数の取得方法の変更
//		// 割付調整因子毎のデータを取得
//		$alloc_table = $this->getAllocTable( $datas, $min_group_contents, $allocate_factors, $layer_array );
//
//		// 割付可能なグループの算出
//		$can_group = $this->getAllocCanGroup( $alloc_table, $allocation["group_differences"], $min_group_contents );
//		// 条件を満たす群がなかった場合(全て、許容できない群間差になるなど)
//		if ( empty( $can_group ) )
//		{
//			// 群間差が一番大きい割付調整因子を特定
//			$max_differences_metadata_id = $this->getMaxDifferences( $alloc_table );
//
//			// 割付可能なグループの算出
//			$max_alloc_table = array( $max_differences_metadata_id => $alloc_table[$max_differences_metadata_id] );
//			$can_group = $this->getAllocCanGroup( $max_alloc_table, $allocation["group_differences"], $min_group_contents );
//		}

		// 登録データと同じ設定値を持つデータの各割付群の件数をカウントする
		$group_alloc_counts = $this->getGroupCountByAdjustment( $datas, $layer_array );
//test_log("datas");
//test_log($datas);
//test_log("group_alloc_counts");
//test_log($group_alloc_counts);
		
		// 割付可能な群を特定する
		$can_group = $this->mdbAction->getAllocCanGroup( $group_alloc_counts, $allocation["group_differences"], $min_group_contents );
//test_log("can_group");
//test_log($can_group);
// 19.03.22 change end

		// 群を決定
		$allocation_group_id = $this->mdbAction->rndAnswer( $can_group );
		
		return $allocation_group_id;
	}
	
	
	/**
	 * 群の設定の取得
	 * 
	 * @access	public
	 */
	private function getMinGroupContent( $allocation, $group_contents )
	{
		// 均等比率の場合は各グループの比率を強制的に均等にする。
		if ( $allocation["equal_ratio_flag"] == 1 ) {

			$equality_percent = floor( 100 / count( $group_contents ) );

			foreach ( $group_contents as &$group_item ) {
				$group_item["ratio"] = $equality_percent;
			}
		}
		unset( $group_item );

		// 群の設定(各グループ)を [allocation_group_id] => [ratio] に変換する。
		$ret_group_contents = array();
		foreach( $group_contents as $group_item ) {
			$ret_group_contents[ $group_item["allocation_group_id"] ] = $group_item["ratio"];
		}

		return $ret_group_contents;
	}
	
	
// 19.03.22 del start by makino@opensource-workshop.jp 割付判定の変更対応
//	/**
//	 * 割付調整因子毎のデータ取得
//	 * 
//	 * @access	public
//	 */
//	 // 
//	function getAllocTable( $datas, $min_group_contents, $allocate_factors, $layer_array )
//	{
//		// 割付調整因子毎の登録されている群
//		// テーブルを作成している。例えば……
//		// 　　　│A群 │B群
//		// ───┼──┼──
//		// 性別　│0　 │0
//		// がん種│0　 │0
//		$group_table = array();
//		foreach( $allocate_factors as $result_metadata )
//		{
//			foreach( $min_group_contents as $group_key => $group_item )
//			{
//				$group_table[ $result_metadata[ "metadata_id" ] ][ $group_key ] = 0;
//			}
//		}
//
//		// 割付調整因子毎の登録されている群の取得
//		// 入力データと同じ因子ごとの既に割り付けられた群の数を取得している
//		foreach( $allocate_factors as $result_item )
//		{
//			$result_alloc_group = $this->getAllocateCount( $datas, $layer_array, $result_item["metadata_id"] );
//			
//			foreach ( $result_alloc_group as $result_alloc_item ) {
//				// 通常の運用ではないかもしれないが、割付設定なしでデータ登録 -> 割付設定ありでデータ登録にした場合、空のグループを取得してしまい、
//				// データ登録時にエラーになるため、空のグループは使わないようにする。
//				if ( !empty( $result_alloc_item["allocation_group_id"] ) ) {
//					$group_table[$result_item["metadata_id"]][$result_alloc_item["allocation_group_id"]] = $result_alloc_item["record_count"];
//				}
//			}
//
//		}
//		
//		return $group_table;
//	}
//	
//	
//	/**
//	 * 
//	 *
//	 * @param  array datas       登録症例データ
//	 * @param  array layer_array 
//	 * @param  int   metadata_id 割付因子のmetadata_id
//	 * @return 
//	 * @access  private
//	 */
//	private function getAllocateCount( $datas, $layer_array, $metadata_id )
//	{
//		if( count( $datas ) <= 0 )
//		{
//			// ----- 因子なし --------------------------------------------------
//			$count_list = array();
//			foreach( $layer_array as $record )
//			{
//				foreach( $record as $group_id => $count )
//				{
//					if( array_key_exists( $group_id, $count_list ) === false )
//					{
//						$count_list[ $group_id ] = $count;
//					}
//					else
//					{
//						$count_list[ $group_id ] = $count_list[ $group_id ] + $count;
//					}
//				}
//			}
//			$result_list = array();
//			foreach( $count_list as $group_id => $count )
//			{
//				$result_list[] = array( "allocation_group_id" => $group_id,
//										"record_count"        => $count );
//			}
//		}
//		else
//		{
//			// ----- 因子あり --------------------------------------------------
//			// 患者情報の因子を取得する
//			$index = 0;
//			$content = "";
//			foreach( $datas as $key => $value )
//			{
//				if( $key == $metadata_id )
//				{
//					$content = $value;
//					break;
//				}
//				$index++;
//			}
//			
//			
//			$count_list = array();
//			foreach( $layer_array as $key_string => $record )
//			{
//				$key_list = explode( "|", $key_string );
//				
//				if( $key_list[ $index ] != $content ) continue;
//				
//				foreach( $record as $group_id => $count )
//				{
//					if( array_key_exists( $group_id, $count_list ) === false )
//					{
//						$count_list[ $group_id ] = $count;
//					}
//					else
//					{
//						$count_list[ $group_id ] = $count_list[ $group_id ] + $count;
//					}
//				}
//			}
//			
//			$result_list = array();
//			foreach( $count_list as $group_id => $count )
//			{
//				$result_list[] = array( "allocation_group_id" => $group_id,
//										"record_count"        => $count );
//			}
//		}
//		
//		return $result_list;
//	}
//	
//	
//	/**
//	 * 割付可能な群を返す
//	 * 
//	 * @access	public
//	 */
//	function getAllocCanGroup( $alloc_table, $group_differences, $group_content )
//	{
//		// 返す配列を作っておく。(最初はすべての割付けグループID を入れておく)
//		// 割付可能な群をmetadata_id でループし、割付けグループID があるか確認。なければ、返す配列から削除。
//		// これで、割付可能な群のみ残る
//
//		$return_groups = $group_content;
//
//		foreach( $return_groups as $allocation_group_id => $ratio ) {
//			foreach ( $alloc_table as $metadata_id => $alloc_table_record ) {
//
//				$max_count = $this->getMin( $alloc_table_record ) + $group_differences;
//
//				foreach ( $alloc_table_record as $alloc_key => $alloc_item ) {
//
//					// ここで +1 すると、許容する群間差が 1 で実際の差が 1 場合、全てが登録できるグループがなくなる
//					//if ( $alloc_key == $allocation_group_id && ( $alloc_item + 1 ) >= $max_count ) {
//					if ( $alloc_key == $allocation_group_id && ( $alloc_item ) >= $max_count ) {
//						unset( $return_groups[$allocation_group_id] );
//					}
//				}
//			}
//		}
//
//		// 計算して、割付可能なグループがない場合は、全てに割付可能とする。
//		if ( empty( $return_groups ) || count( $return_groups ) == 0 ) {
//			$return_groups = $group_content;
//		}
//
//		return $return_groups;
//	}
//	
//	
//	/**
//	 * 群間差が一番大きい割付調整因子を特定
//	 * 
//	 * @access	public
//	 */
//	function getMaxDifferences( $alloc_table ) {
//
//		$max_differences_metadata_id = null;
//		$max_differences = 0;
//
//		foreach ( $alloc_table as $metadata_id => $alloc_table_record ) {
//
//			if ( $max_differences < $this->getMax( $alloc_table_record ) - $this->getMin( $alloc_table_record ) ) {
//
//				$max_differences_metadata_id = $metadata_id;
//				$max_differences = $this->getMax( $alloc_table_record ) - $this->getMin( $alloc_table_record );
//			}
//		}
//		return $max_differences_metadata_id;
//	}
//	
//	
//	/**
//	 * 連想配列中の最小値を返す
//	 * 
//	 * @access	public
//	 */
//	function getMin( $param_array ) {
//
//		// 初期値は最大値で、それより小さいものを探す。
//		$return_min = $this->getMax( $param_array );
//		foreach ( $param_array as $param_item ) {
//			if ( $return_min > $param_item ) {
//				$return_min = $param_item;
//			}
//		}
//		return $return_min;
//	}
//	
//	
//	/**
//	 * 連想配列中の最大値を返す
//	 * 
//	 * @access	public
//	 */
//	function getMax( $param_array ) {
//
//		$return_max = 0;
//		foreach ( $param_array as $param_item ) {
//			if ( $return_max < $param_item ) {
//				$return_max = $param_item;
//			}
//		}
//		return $return_max;
//	}
// 19.03.22 del start by makino@opensource-workshop.jp 割付判定の変更対応
	
	
// 19.03.22 add start by makino@opensource-workshop.jp 割付判定の変更対応
	/**
	 * 登録データと同じ調整因子の組み合わせ毎の件数を取得する
	 * 
	 * @access	public
	 * @return	array[ allocation_group_id ] = count
	 */
	private function getGroupCountByAdjustment( $datas, $layer_array )
	{
		$key = implode( "|", $datas );
		
		$result_array = $layer_array[ $key ];
		
		return $result_array;
	}
// 19.03.22 add end
	
	
	/**
	 * 置換ブロック法による割付
	 *
	 * @param  array  group_contents          割付群リスト
	 * @param  array  allocate_factors        割付因子リスト
	 * @param  array  allocation_conbinations 割付層リスト
	 * @param  string variable_block          可変ブロック
	 * @param  int    content_count           
	 * @param  array  block_list              
	 * @param  array  datas                   登録症例データ
	 * @return 
	 * @access  private
	 */
	private function allocBlock( $group_contents, $allocate_factors, $allocation_conbinations, $variable_block, $content_count, &$block_list, $datas, $allocate_block_patterns )
	{
		// +++++ 割り付けるかどうかの事前チェック ++++++++++++++++++++++++++++++++++++++++++++++++++
		
		
		if( $variable_block === false || count( $variable_block ) <= 0 || empty( $variable_block[ "case_count" ] ) === true )
		{
			// 可変ブロック情報取得失敗、データがない、基準症例数が空、の場合は、ブロック数を変更しないので何もしない
		}
		else
		{
			// 上記以外の場合は、ブロック数を変更させる可能性あり
			
			// 現在登録数÷基準症例数の余り＝0…ブロック数変更
			$surplus = $content_count % intval( $variable_block[ "case_count" ] );
			
			// 余りが0の時
			if( $surplus == 0 )
			{
				// 割付層情報ループ
				foreach( $allocation_conbinations as &$list )
				{
					// 分解
					$block_count_list = explode( ",", $list[ "next_block_count" ] );
					
					if( count( $block_count_list ) <= 1 )
					{
						// 単一定義なので何もしない
					}
					else
					{
						// 複数定義なので、使用するブロック数をランダムで決定する
						$index = $this->mdbView->randBlockCountIndex( $list[ "next_block_count" ] );
						
						// 決定したブロック数で「現在ブロック数」を更新する
						$list[ "now_block_count" ] = $block_count_list[ $index ];
					}
				}
			}
		}
		
		
		
		// +++++ 割付処理実施 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		// 割付因子をもとにコンテンツ登録情報から組合せ情報を取得する
		$content_factors = array();
		foreach( $allocate_factors as $metadata )
		{
			if( isset( $datas[ $metadata[ 'metadata_id' ] ] ) === true )
			{
				$content_factors[] = $datas[ $metadata[ 'metadata_id' ] ];
			}
		}
		$content_factors_string = implode( '|', $content_factors );
		
		// 一致する割付層を取得する
		$allocation_conbination = "";
		foreach( $allocation_conbinations as $value )
		{
			if( $value[ 'factor_contents' ] == $content_factors_string )
			{
				$allocation_conbination = $value;
				break;
			}
		}
		// 一致する層を取得できなかった場合、次回ブロック数が0の場合は割付ないので正常終了
		if( empty( $allocation_conbination ) === true || $allocation_conbination[ 'next_block_count' ] == 0 ) return true;
		
		$conbination_id   = $allocation_conbination[ 'conbination_id' ];
		$next_block_count = $allocation_conbination[ 'next_block_count' ];
		$now_block_count  = $allocation_conbination[ 'now_block_count' ];
		$exclude_count    = $allocation_conbination[ 'exclude_count' ];
		
		// 層IDをもとに紐づくブロックデータを取得する（※まだ割り付けていないデータ）
		$blockdata_list = $block_list[ $content_factors_string ];
		
		
		if( count( $blockdata_list ) <= 0 )
		{
			// ----- 1件もない場合、新しいブロックデータを作成して割り当てる -----------------------
			
			// 現在ブロック数が空の場合は、次回ブロック数から取得して設定/更新する
			// ※可変ブロック処理実装前の試験データの為の処理
			if( empty( $now_block_count ) === true )
			{
				$block_count_list = explode( ",", $next_block_count );
				if( count( $block_count_list ) <= 1 )
				{
					$now_block_count = $next_block_count;
				}
				else
				{
					$index = $this->mdbView->randBlockCountIndex( $next_block_count );
					$now_block_count = $block_count_list[ $index ];
				}
				
			}
			
			// 除外連続数をリストから取得しておく
			$set_exclude_count = 0;
			if( empty( $exclude_count ) === false )
			{
				$exclude_count_list = explode( ",", $exclude_count );
				if( count( $exclude_count_list ) <= 1 )
				{
					// 単一定義の場合
					$set_exclude_count = intval( $exclude_count );
				}
				else
				{
					// 複数定義あり
					$index = $this->mdbView->getBlockCountIndex( $next_block_count, $now_block_count );
					if( $index < 0 )
					{
						// 正しく取得できなかった場合は何も設定しない
					}
					else
					{
						if( empty( $exclude_count_list[ $index ] ) === false )
						{
							$set_exclude_count = intval( $exclude_count_list[ $index ] );
						}
						else
						{
							// 空の場合は何も設定しない
						}
					}
				}
			}
			
			
			// 新しく割付パタン取得 ※シミュレーションでは事前に割付パターンを作成してあるのでそこから選択する
			$allocate_block_patterns = $allocate_block_patterns[ $now_block_count ][ $set_exclude_count ];
			
			// 乱数取得 ※シミュレーションでは当関数を呼ぶ前にシードを指定しているのでここでは指定しない
			$allocate_block_pattern_index = mt_rand( 0, ( count( $allocate_block_patterns ) -1 ) );
			
			// 使用する割付パタン取得
			$use_allocate_block_pattern = $allocate_block_patterns[ $allocate_block_pattern_index ];
			
			// 返却する群ID
			$result_group_id = $use_allocate_block_pattern[ 0 ];
			
			// 使用したので削除する
			unset( $use_allocate_block_pattern[ 0 ] );
			
			// indexを詰める
			$use_allocate_block_pattern = array_values( $use_allocate_block_pattern );
			
			$block_list[ $content_factors_string ] = $use_allocate_block_pattern;
			
		}
		else
		{
			// ----- 1件以上ある場合、取得したデータを割り当てる -----------------------------------
			
			// 返却する群ID
			$result_group_id = $blockdata_list[ 0 ];
			
			// 使用したので削除する
			unset( $blockdata_list[ 0 ] );
			
			// indexを詰める
			$blockdata_list = array_values( $blockdata_list );
			
			$block_list[ $content_factors_string ] = $blockdata_list;
			
		}
		
		// 正常終了
		return $result_group_id;
	}
	
	
	/**
	 * 割付群名リスト取得処理
	 *
	 * @param  array  group_contents 割付群リスト
	 * @return array  key:group_id / value:group_name
	 * @access  private
	 */
	private function getGroupNameList( $group_contents )
	{
		$result_list = array();
		
		foreach( $group_contents as $record )
		{
			$result_list[ $record["allocation_group_id"] ] = $record[ "group_name" ];
		}
		
		return $result_list;
	}
	
	
	/**
	 * 割付群名リスト取得処理
	 *
	 * @param  string  uploads_dir   アップデートディレクトリパス
	 * @param  string  file_name_zip ZIPファイル名
	 * @param  string  file_name_csv CSVファイル名
	 * @return nothing
	 * @access  private
	 */
	private function createZipFile( $uploads_dir, $file_name_zip, $file_name_csv )
	{
		// インスタンス生成
		$zip = new ZipArchive();
		
		// ZIPファイル名
		$zip_filename = $uploads_dir . $file_name_zip;
		
		// ZIPファイルをオープン
		$res = $zip->open( $zip_filename, ZipArchive::CREATE);

		// zipファイルのオープンに成功した場合
		if ($res === true)
		{
			$zip->addFile( $uploads_dir . $file_name_csv, $file_name_csv );
			
			// ZIPファイルをクローズ
			$zip->close();
		}
		
		// 作り終わったCSVファイルは削除する
		$result = unlink( $uploads_dir . $file_name_csv );
		
		return;
	}

}
?>
