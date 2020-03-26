<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付シミュレーション実施時チェック処理
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Validator_AllocationSimulate extends Validator
{
	/**
	 * 割付シミュレーション実施時チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr	 エラー文字列
	 * @param array $params	 オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */

	function validate($attributes, $errStr, $params)
	{
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_AllocationSimulate:Start" );
//$time_start = microtime(true);
		
		// 必要コンポーネント取得
		$container      = DIContainerFactory::getContainer();		// コンテナ
		$request        = $container->getComponent('Request');		// リクエスト・クラス
		$mdbView        = $container->getComponent('mdbView');		// 当モジュールViewコンポーネント
		$mdbAction      = $container->getComponent('mdbAction');	// 当モジュールActionコンポーネント
		$fileUpload     = $container->getComponent('FileUpload');	// ファイルUploadコンポーネント
		$commonMain     = $container->getComponent('commonMain');	// commonMainコンポーネント
		$uploadsAction  = $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");		// ファイルUploadアクションコンポーネント
		
		
		// 臨床試験支援DB ID
		$testee_id = $attributes;
		
		// 入力データの取得
		$input_type    = $request->getParameter('input_type');		// 症例数入力タイプ
		$case_count    = $request->getParameter('case_count');		// 症例数
		$repeat_count  = $request->getParameter('repeat_count');	// 繰り返し数
		$factor_ratio  = $request->getParameter('factor_ratio');	// 割付因子の自動生成時のデータ割合
		$allocate_seed = $request->getParameter('allocate_seed');	// 割付用シード値
		$case_seed     = $request->getParameter('case_seed');		// 症例用シード値
//test_log("input_type[" . $input_type . "]");
//test_log("case_count[" . $case_count . "]");
//test_log("repeat_count[" . $repeat_count . "]");
//test_log($factor_ratio);
//test_log("allocate_seed[" . $allocate_seed . "]");
//test_log("case_seed[" . $case_seed . "]");

		
		
		// ファイルuploadコンポーネントからエラーメッセージ取得
		$errormes = $fileUpload->getErrorMes();
//test_log("errormes");
//test_log($errormes);
		
		
		
		// 前の登録情報を取得する
		$simu_setting = $mdbView->getAllocationSimsetting( $testee_id );
//test_log("変更前シミュ設定");
//test_log( $simu_setting );
		if( $simu_setting === false ) return "割付シミュレーション設定情報が取得できませんでした。";
		
		
		// 割付情報取得と確認
		$allocation = $mdbView->getAllocationContent( $testee_id );
		if( $allocation === false || count( $allocation ) <= 0 )
		{
			return "割付情報が取得できない為、シミュレーションは実施できませんでした。";
		}
		else if( $allocation[ 'allocation_flag' ] != 1 && $allocation[ 'allocation_flag' ] != 2 )
		{
			return "割付を使用しない設定になっている為、シミュレーションは実施できませんでした。";
		}
		
		
// 19.03.22 add start by makino@opensource-workshop.jp  最小化法で強制割付なしでも、症例データを自動生成するように変更する
		// 割付因子情報取得
		$allocate_factors = $mdbView->getSelectedAllocationFactors( $testee_id );
// 19.03.22 add end
		
		
		// ***** 入力チェック **************************************************
		// ----- 症例入力タイプ ----------------------------
		$result = $this->checkInputType( $input_type );
		if( empty( $result ) === false ) return $result;
		$set_input_type = trim( $input_type );
		
		
		// ----- 割付用シード値 ----------------------------
		$result = $this->checkSeed( $allocate_seed );
		if( empty( $result ) === false ) return "割付用のシード値" . $result;
		
		if( empty( $allocate_seed ) === true )
		{
			$set_allocate_seed = $mdbView->getAllocationSeed();
		}
		else
		{
			$set_allocate_seed = intval( $allocate_seed );
		}
		
		
		// ----- 自動生成の時の繰り返し数 ------------------
		$set_repeat_count = 0;
		if( $input_type == 0 )
		{
			$result = $this->checkRepeatCount( $repeat_count );
			if( empty( $result ) === false ) return $result;
			$set_repeat_count = intval( $repeat_count );
		}
		
		// ----- 自動生成の時の症例数 ----------------------
		$set_case_count = 0;
		if( $input_type == 0 )
		{
			$result = $this->checkCaseCount( $case_count );
			if( empty( $result ) === false ) return $result;
			$set_case_count = intval( $case_count );
		}
		
		
		// ----- 自動生成時のシード値 ----------------------
		$set_case_seed = 0;
		if( $input_type == 0 )
		{
// 19.03.22 change start by makino@opensource-workshop.jp  最小化法で強制割付なしでも、症例データを自動生成するように変更する
//			if( $allocation[ "allocation_flag" ] == 2 ||
//			  ( $allocation[ "allocation_flag" ] == 1 && $allocation[ "force_allocation_flag" ] == 1 ) )
//			{
//				// 置換ブロック法、もしくは、最小化法で強制割付を行う場合
			if( count( $allocate_factors ) > 0 )
			{
// 19.03.22 change end
				// 割付因子が設定されている場合
				$result = $this->checkSeed( $case_seed );
				if( empty( $result ) === false ) return "症例用のシード値" . $result;
				
				if( empty( $case_seed ) === true )
				{
					$set_case_seed = $mdbView->getAllocationSeed();
				}
				else
				{
					$set_case_seed = intval( $case_seed );
				}
			}
		}
		
		
		
		// ファイルinputエラー[ファイルが選択されていません]のチェック。不要な場合は事前に削除
		if( $input_type == 0 )
		{
			// 自動生成の時はメッセージ削除
			$this->deleteErrorMessage( $errormes );
		}
		else
		{
			// ファイルからの入力の場合
			if( count( $simu_setting ) > 0 && empty( $simu_setting[ "upload_id" ] ) === false )
			{
				// 既に登録がある場合はメッセージ削除
				$this->deleteErrorMessage( $errormes );
			}
			else
			{
				// まだ登録がない場合、エラーメッセージがある場合はエラー
				if ( empty( $errormes ) === false && is_array( $errormes ) && count( $errormes ) > 0 )
				{
					$error_str = "";
					foreach ( $errormes as $errorme ) {
						$error_str = $error_str . $errorme . "<br />";
					}
					return $error_str;
				}
			}
		}
		
		
		// ***** 設定済み割付情報のチェック ************************************
		// 割付群情報取得と確認
		$group_contents = $mdbView->getGroupContent( $testee_id );
		if( $group_contents === false || count( $group_contents ) <= 0 ) return "割付群情報が取得できない為、シミュレーションは実施できませんでした。";
		
		if( $allocation["allocation_flag"] == "1" )
		{
			// ----- 最小化法 --------------------------------------------------
			
			// 割付群の比率のチェック（※最小化法の方は何故か0が入れられてしまうから）
			if( $allocation[ "equal_ratio_flag" ] != 1 )
			{
				foreach( $group_contents as $group )
				{
					if( empty( $group[ "ratio" ] ) === true )
					{
						return "割付群の確率に[0]がある為、正常にシミュレーションが実施できません。";
					}
				}
			}
			
			if( $allocation["force_allocation_flag"] == "1" )
			{
				// 強制割付ありの場合
				
				// 割付因子の各項目取得
// 19.03.22 del start by makino@opensource-workshop.jp  最小化法で強制割付なしでも、症例データを自動生成するように変更する
//				$allocate_factors = $mdbView->getSelectedAllocationFactors( $testee_id );
// 19.03.22 del end
				if( $allocate_factors === false || count( $allocate_factors ) <= 0 ) return "割付因子情報が取得できなかった為、シミュレーションは実施できませんでした。";
				
				// 取得した割付因子項目を組み替え
				$factor_list = $mdbView->getAllocationItemList( $allocate_factors );
			}
// 19.03.22 add start by makino@opensource-workshop.jp 最小化法で強制割付なしでも、症例データを自動生成するように変更する
			else
			{
				// 強制割付なしの場合
				if( $allocate_factors !== false && count( $allocate_factors ) > 0 )
				{
					// 調整因子ありの場合は、取得した割付因子項目を組み替え
					$factor_list = $mdbView->getAllocationItemList( $allocate_factors );
				}
			}
// 19.03.22 add end
		}
		else
		{
			// ----- 置換ブロック法 --------------------------------------------
			// 割付群が変更されているかどうかチェックする
			if( $mdbView->changeAllocateGroupContents( $group_contents ) === false ) return "割付群が変更後正しく再設定されていない為、シミュレーションは実施できませんでした。";
			
			// 割付因子の各項目取得
// 19.03.22 del by makino@opensource-workshop.jp  最小化法で強制割付なしでも、症例データを自動生成するように変更する
//			$allocate_factors = $mdbView->getSelectedAllocationFactors( $testee_id );
// 19.03.22 del end
			if( $allocate_factors === false || count( $allocate_factors ) <= 0 ) return "割付因子情報が取得できなかった為、シミュレーションは実施できませんでした。";
			
			// 割付層情報取得
			$allocation_conbinations = $mdbView->getAllocationcConbination( $testee_id );
			if( $allocation_conbinations === false || count( $allocation_conbinations ) <= 0 ) return "割付層情報が取得できなかった為、シミュレーションは実施できませんでした。";
			
			// 取得した割付因子項目を組み替え
			$factor_list = $mdbView->getAllocationItemList( $allocate_factors );
			
			// 現在の割付因子の項目で、組合せパタン作成
			$now_pattern = $mdbView->getConbinationPattern( $factor_list );
			
			// 現在の組み合わせパターンと、既存層のパターンを比較
			if( $mdbView->matchAllocationFactors( $now_pattern, $allocation_conbinations ) === false ) return "割付因子が変更後正しく再設定されていない為、シミュレーションは実施できませんでした。";
			
			
			// 割付群の比率の合計値取得
			$total_ratio = $mdbView->getTotalRatio( $group_contents );
			
			// 割付層の各ブロック数が正しいかどうかチェックする
			if( $mdbView->checkRatioBlockCount( $allocation_conbinations, $total_ratio ) === false ) return "割付層のブロック数に正しくない値が設定されている為、シミュレーションは実施できませんでした。";
			
		}
		
		
		// ***** 入力ファイルのチェック ****************************************
		$set_upload_id = 0;
		if( $input_type == 1 )
		{
//test_log("_FILES");
//test_log($_FILES);
			if( empty( $_FILES["attachment"]["name"]["casefile"] ) === false )
			{
				// ----- ファイルが指定されている場合 --------------------------
				$result = $this->checkCSVInfo( $mdbView );
				if( empty( $result ) === false ) return $result;
				
				// ファイルアップロード
				$files = $uploadsAction->uploads();
//test_log("file情報");
//test_log($files);
				
				// 取込エラーの確認
				if( isset($files["casefile"]['error_mes']) && $files["casefile"]['error_mes'] != "" ) return $files["casefile"]['error_mes'];
				
				// 内容チェック
				$error_list = $this->checkCSVValues( $mdbView, $files["casefile"], $factor_list, $set_repeat_count );
//test_log("error_list");
//test_log($error_list);
				
				if( count( $error_list ) > 0 )
				{
					// アップしたファイルを削除する
					$uploadsAction->delUploadsById( $files["casefile"]["upload_id"] );
					
					$error_string = "";
					foreach( $error_list as $value )
					{
						$error_string .= $value;
					}
					return $error_string;
				}
				
				$set_upload_id = $files["casefile"]["upload_id"];
			}
			else
			{
				// ----- ファイルが指定されていない場合 ------------------------
				if( count( $simu_setting ) <= 0 || empty( $simu_setting[ "upload_id" ] ) === true )
				{
					// 既存データにもファイルが指定されていない場合にはエラー
					return $files["casefile"]['error_mes'];
				}
				else
				{
// 19.03.22 add start by makino@opensource-workshop.jp ファイル読込設定で２回目以降の実施時、繰り返し数が0になってしまうバグ修正
					$set_repeat_count = $simu_setting[ "repeat_count" ];
// 19.03.22 add end 
					$set_upload_id = $simu_setting[ "upload_id" ];
				}
			}
		}
		
		
		// ***** 入力ファイルの状態が変更になった場合 **************************
		if( count( $simu_setting ) > 0 && empty( $simu_setting[ "upload_id" ] ) === false && $simu_setting[ "upload_id" ] != $set_upload_id )
		{
			// 登録済みで、ファイルが変更になった場合、既存のファイルを削除する
			$uploadsAction->delUploadsById( $simu_setting[ "upload_id" ] );
		}
		
		
		// ***** 前症例データがある場合は削除する ******************************
		if( count( $simu_setting ) > 0 && empty( $simu_setting[ "output_csv" ] ) === false )
		{
			$uploads_dir = FILEUPLOADS_DIR . "testee/";
			$del_file_path = $uploads_dir . $simu_setting[ "output_csv" ];
			if( file_exists( $del_file_path ) === true )
			{
				unlink( $del_file_path );
			}
		}
		
		
		// ***** 割付シミュレーション設定の登録/変更 ***************************
		// 割付シミュレーション情報を、以前のデータを削除して登録する
		$result = $mdbAction->deleteAllocationSimsetting( $testee_id );
		$result = $mdbAction->insertAllocationSimsetting( $testee_id, $set_input_type, $set_case_count, $set_upload_id, $set_repeat_count, $set_allocate_seed, $set_case_seed );
		if( $result === false ) return "割付シミュレーション設定の登録に失敗しました。";
		
		
// 19.03.22 change startby makino@opensource-workshop.jp  最小化法で強制割付なしでも、症例データを自動生成するように変更する
//		// 自動生成の時、置換ブロック法か最小化法で強制割付ありの場合、割付因子情報に比率を設定する
//		if( $input_type == 0 )
//		{
//			if( $allocation[ "allocation_flag" ] == 2 ||
//			  ( $allocation[ "allocation_flag" ] == 1 && $allocation[ "force_allocation_flag" ] == 1 ) )
		// 自動生成の時、割付因子が指定されている場合、割付因子情報に比率を設定する
		if( $input_type == 0 )
		{
			if( count( $allocate_factors ) > 0 )
			{
// 19.03.22 change end
				foreach( $factor_ratio as $metadata_id => $ratio_list )
				{
					// 念の為空白除去
					foreach( $ratio_list as &$value )
					{
						$value = trim( $value );
					}
					
					// 結合
					$set_value = implode( "|", $ratio_list );
					
					// 登録
					$result = $mdbAction->updateAdjustmentFactorRatio( $metadata_id, $set_value );
				}
			}
		}
		
//$time = round( microtime(true) - $time_start, 2);
//test_log("[" . $time . "秒]");
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_AllocationSimulate:End" );
		return;
	}
	
	
	// *********************************************************************************************
	//  以下Private関数定義
	// *********************************************************************************************
	
	/**
	 * 入力数値項目チェック処理
	 *
	 * @param  string $value チェックする値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkInputNumeric( $value )
	{
		// 空チェック
		if( strlen( trim( $value ) ) == 0 )
		{
			return "は必ず入力してください。";
		}
		
		// 数値チェック
		if( is_numeric( $value ) === false )
		{
			return "は数値で入力してください。";
		}
		if( strpos( $value, '.' ) !== false)
		{
			return "は整数値で入力してください。";
		}
		
		// 負数チェック
		$check_value = intval( $value );
		if( $check_value < 0 )
		{
			return "は正数で入力してください。";
		}
		
		return "";
	}
	
	
	/**
	 * 症例入力タイプのチェック
	 *
	 * @param  string $input_type チェックする値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkInputType( $input_type )
	{
		if( $input_type !== "0" && $input_type !== "1" )
		{
			return "症例数の入力タイプが規定外です。";
		}
		
		return "";
	}
	
	
	/**
	 * シード値のチェック
	 *
	 * @param  string $seed チェックする値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkSeed( $seed )
	{
		// 空チェック（※シード値は空or0の場合は何もせず終了 )
		if( empty( $seed ) === true ) return "";
		
		// 数値チェック
		if( is_numeric( $seed ) === false )
		{
			return "は数値で入力してください。";
		}
		if( strpos( $seed, '.' ) !== false)
		{
			return "は整数値で入力してください。";
		}
		
		// 負数チェック
		$check_value = intval( $seed );
		if( $check_value < 0 )
		{
			return "は正数で入力してください。";
		}
		
		// 最大値チェック
		if( $seed > PHP_INT_MAX )
		{
			return "は[" . PHP_INT_MAX . "]までの値で指定してください。";
		}
		
		return "";
	}
	
	
	/**
	 * 繰り返し数のチェック
	 *
	 * @param  string $repeat_count チェックする値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkRepeatCount( $repeat_count )
	{
		// 基本チェック
		$result = $this->checkInputNumeric( $repeat_count );
		if( empty( $result ) === false )
		{
			return "繰返し数" . $result;
		}
		
		// 閾値チェック
		$check_value = intval( $repeat_count );
		if( $check_value < 1 || 10000 < $check_value )
		{
			return "繰り返し数は1～10000までの値で指定してください。";
		}
		
		return "";
	}
	
	
	/**
	 * 症例数のチェック
	 *
	 * @param  string $case_count チェックする値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkCaseCount( $case_count )
	{
		// 基本チェック
		$result = $this->checkInputNumeric( $case_count );
		if( empty( $result ) === false )
		{
			return "症例数" . $result;
		}
		
		// 閾値チェック
		$check_value = intval( $case_count );
		if( $check_value < 1 || 10000 < $check_value )
		{
			return "症例数は1～10000までの値で指定してください。";
		}
		
		return "";
	}
	
	
	/**
	 * 割付因子の比率のチェック
	 *
	 * @param  string $factor_ratio チェックする値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkFactorRatio( $factor_ratio )
	{
		$i = 1;
		foreach( $factor_ratio as $metadata_id => $ratio_list )
		{
			$j = 1;
			foreach( $ratio_list as $ratio )
			{
				$result = $this->checkInputNumeric( $ratio );
				if( empty( $result ) === false )
				{
					return $i . "個目の因子、" . $j . "番目の因子の比率" . $result;
				}
				// 閾値チェック
				$check_value = intval( $ratio );
				if( $check_value < 1 || 99 < $check_value )
				{
					return $i . "個目の因子、" . $j . "番目の因子の比率には1～99の値で入力してください。";
				}
				
				$j++;
			}
			
			$i++;
		}
		
		return "";
	}
	
	
	/**
	 * ファイルエラーメッセージから[ファイルを指定してください]を削除する
	 *
	 * @param  array(string) &$errormes エラーメッセージ
	 * @access private
	 */
	private function deleteErrorMessage( &$errormes )
	{
		$nofile_error_key = array_search( _FILE_UPLOAD_ERR_UPLOAD_NOFILE, $errormes );
		if ( $nofile_error_key !== false )
		{
			unset( $errormes[$nofile_error_key] );
		}
	}
	
	
	/**
	 * 入力ファイルの基本チェック
	 *
	 * @param  string $mdbView VIEWコンポーネント
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkCSVInfo( $mdbView )
	{
//test_log("FILE情報");
//test_log($_FILES["attachment"]);
			
		// ファイル名の取得
		$file_name = $_FILES["attachment"]["name"]["casefile"];
		$extension = strtolower( $mdbView->getExtension( $file_name ) );
		
		// 拡張子のチェック
		if( $extension != "csv" && $extension != "zip" )
		{
			return "ファイルはCSV、またはZIPを指定してください。";
		}
		
		
		return "";
	}
	
	
	/**
	 * CSVの内容チェック
	 *
	 * @param  array $file          入力ファイル情報
	 * @param  array $factor_list   割付因子リスト
	 * @param  int   &$repeat_count 繰り返し回数（返却領域）
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkCSVValues( $mdbView, $file, $factor_list, &$repeat_count )
	{
		// エラーメッセージ返却用リスト
		$error_list = array();
		
		// ファイルパス取得
		$open_file_path = FILEUPLOADS_DIR . "testee/" . $file["physical_file_name"];
		
		// 割付因子の数取得
		$fuctor_count = count( $factor_list );
		
		
		// ----- 読み込みファイルのストリームを開く ----------------------------
		$handle = null;
		$zip    = null;
		$result = $mdbView->openFile( $open_file_path, $handle, $zip );
		if( $result === -1 )
		{
			$error_list[] = "CSVファイルが正しく読み取れませんでした。";
			return $error_list;
		}
		else if( $result === -10 )
		{
			$error_list[] = "zipファイルが開けませんでした。";
			return $error_list;
		}
		else if( $result === -11 )
		{
			$error_list[] = "ZIPファイルの中にCSVファイルがありませんでした。";
			return $error_list;
		}
		
		
		$index = 0;
		$id_list = array();
		while( feof( $handle ) === false )
		{
			// 1行読み込み
			$record = trim( mb_convert_encoding( fgets( $handle ), "UTF-8", "auto" ) );
			
			// 空の場合は何もせず読み飛ばし
			if( empty( $record ) === true )
			{
				$index++;
				continue;
			}
			
			// 分解
			$record_list = explode( ",", $record );
			
			// 項目数が合わない場合はエラーに記入して読み飛ばし
			if( count( $record_list ) != ($fuctor_count +1) )
			{
				$error_list[] = ($index+1) . "行目の項目数が" . ($fuctor_count +1) . "個ではありません。\r\n";
				$index++;
				continue;
			}
			
			// IDチェック
			$result = $this->checkID( $record_list[0] );
			if( empty( $result ) === false )
			{
				$error_list[] = ($index+1) . "行目の" . $result;
				$index++;
				continue;
			}
			
			// ID重複チェック
			$split_id = explode( "-", $record_list[0] );
			$key01 = intval( $split_id[0] );
			$key02 = intval( $split_id[1] );
			if( array_key_exists( $key01, $id_list ) === true )
			{
				if( in_array( $key02, $id_list[ $key01 ] ) === true )
				{
					$error_list[] = ($index+1) . "行目のIDが重複しています。";
					$index++;
					continue;
				}
				$id_list[ $key01 ][] = $key02;
			}
			else
			{
				$id_list[ $key01 ] = array();
				$id_list[ $key01 ][] = $key02;
			}
			
			
			$i = 1;		// １項目目(index0)はIDなので、因子のチェックは2項目目(index1)から開始
			foreach( $factor_list as $factor )
			{
				$check_value = trim( $record_list[ $i ] );
				$check_value = str_replace( '"', '', $check_value );
				
				$flag = false;
				foreach( $factor[ "select_content" ] as $value )
				{
					if( $check_value == $value )
					{
						$flag = true;
						break;
					}
				}
				if( $flag === false )
				{
					$error_list[] = ($index+1) . "行目の" . ($i+1) . "個目の項目が割付因子の内容と合いません。\r\n";
				}
				
				$i++;
			}
			
			
			$index++;
		}
		
		// ファイル閉じる
		fclose( $handle );
		
		// zipファイルの場合はzipファイルの方も閉じる
		if( is_null( $zip ) === false )
		{
			$zip->close();
		}
		
		// 件数確認
		if( count( $id_list ) <= 0 )
		{
			$error_list[] = "CSVファイルに利用可能なレコードがありません。";
		}
		
		
		// 繰り返し件数を返却
		$repeat_count = count( $id_list );
		
		// エラー情報を返却して終了
		return $error_list;
	}
	
	
	/**
	 * CSVのIDチェック
	 *
	 * @param  string $id_value ID値
	 * @return string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkID( $id_value )
	{
		// 規定フォーマット  XXXXX-YYYYY
		// XXXXX、YYYYY ともに"00001"から始まる
		
		if( empty( $id_value ) === true )
		{
			return "IDが空です。\r\n";
		}
		
		// "-"があるかどうかチェック
		if( strpos( $id_value, "-" ) === false )
		{
			return "IDのフォーマットが正しくありません。\r\n";
		}
		
		// 分解
		$id_array = explode( "-", $id_value );
		
		// 個数確認
		if( count( $id_array ) !== 2 ) return "IDのフォーマットが正しくありません。\r\n";
		
		// 1つ目の値チェック
		if( empty( $id_array[0] ) === true ) return "IDのフォーマットが正しくありません。\r\n";
		
		// 2つ目の値チェック
		if( empty( $id_array[1] ) === true ) return "IDのフォーマットが正しくありません。\r\n";
		
		foreach( $id_array as $value )
		{
			// 数値チェック
			if( is_numeric( $value ) === false ) return "IDのフォーマットが正しくありません。\r\n";
			
			// 小数点チェック
			if( strpos( $value, '.' ) !== false) return "IDのフォーマットが正しくありません。\r\n";
		}
		
		return "";
	}
}
?>