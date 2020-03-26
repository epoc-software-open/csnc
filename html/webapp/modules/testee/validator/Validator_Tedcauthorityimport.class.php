<?php

/**
 *  CSV入力チェック
 *  リクエストパラメータ
 *  var $testee_id = null;
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_Tedcauthorityimport extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 *                  
	 * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{

		// container取得
		$container =& DIContainerFactory::getContainer();

		// component取得
		$mdbView =& $container->getComponent("mdbView");
		$mdbAction =& $container->getComponent("mdbAction");

		// アップロードファイル取得
		$uploadsAction =& $container->getComponent("uploadsAction");
		$file = $uploadsAction->uploads(1);

		// CSV ファイルを添付してください。
		if( empty($file) || strtolower($file[0]['extension']) != "csv" ) {
			return "CSV ファイルを添付してください。";
		}

		// NetCommons が検知するファイルアップロードのエラー
		if(isset($file[0]['error_mes']) && $file[0]['error_mes'] != "") {
			return $file[0]['error_mes'];
		}

		// CSVファイル読み込み
		$csv_file = FILEUPLOADS_DIR."testee/".$file[0]['physical_file_name'];
		$handle = fopen($csv_file, 'r');
		if($handle === false) {
			return "CSV ファイルが正しく読み取れませんでした。\nシステム管理者に連絡してください。";
		}

		// データチェック
		$first_flag = true;  // ヘッダーフラグ
		$row_datas = array(); // 取得したCSV：ハンドル名(handle),試験毎権限(tedc_authority_name)＋
		                      // 付加データ：user_id,tedc_authority＋
		                      // 既存データの存在確認：tedcauthority_tedc_authority の配列

		while (($data = $mdbAction->fgetcsv_reg($handle)) !== FALSE) {

			// ヘッダーチェック
			if ( $first_flag ) {
				if ( mb_convert_encoding($data[0], "UTF-8", "SJIS-win") != "ハンドル名" ) {
					return 'CSV ファイルのヘッダーに"ハンドル名"が存在しません。\nCSV ファイルを見直してください。';
				}
				if ( mb_convert_encoding($data[1], "UTF-8", "SJIS-win") != "試験毎権限" ) {
					return 'CSV ファイルのヘッダーに"試験毎権限"が存在しません。\nCSV ファイルを見直してください。';
				}
				if ( mb_convert_encoding($data[2], "UTF-8", "SJIS-win") != "割付参照権限" ) {
					return 'CSV ファイルのヘッダーに"割付参照権限"が存在しません。\nCSV ファイルを見直してください。';
				}
				$first_flag = false;
			}
			// データ部チェック
			else {

				$user = $mdbView->getUserIDbyHandle(mb_convert_encoding($data[0], "UTF-8", "SJIS-win"), $attributes["testee_id"]);
				$tedc_authority = $mdbView->getTedcAuthorityCode(mb_convert_encoding($data[1], "UTF-8", "SJIS-win"));
				$row_datas[] = array (
					"handle" => $data[0],
					"tedc_authority_name" => $data[1],
					"user_id" => $user["user_id"],
					"tedc_authority" => $tedc_authority,
					"tedcauthority_user_id" => $user["tedcauthority_user_id"],
					"allocation_view" => $data[2],
				);

				if ( empty( $user ) ) {
					return 'ハンドル名 "' . mb_convert_encoding($data[0], "UTF-8", "SJIS-win") . '" のユーザが見つかりませんでした。' . "\n" . 'CSV ファイルを見直してください。';
				}
				if ( $tedc_authority === false ) {
					return '試験毎権限 "' . mb_convert_encoding($data[1], "UTF-8", "SJIS-win") . '" が正しくありません。' . "\n" . 'CSV ファイルを見直してください。';
				}
			}
		}
		// test_log($row_datas);

		// 現在登録中の割付参照ユーザー情報を取得する
		$view_users = $mdbView->getAllocationViewUsers( $attributes["testee_id"] );

		// データ更新
		foreach( $row_datas as $row_data ) {

			$params = array( "testee_id" => $attributes["testee_id"],
			                 "user_id" => $row_data["user_id"],
			                 "tedc_authority" => $row_data["tedc_authority"]
			               );

			// 既存のデータがない場合
			if ( empty( $row_data["tedcauthority_user_id"] ) ) {

				// 試験毎権限が空の場合は何もしない。
				if ( empty( $row_data["tedc_authority_name"] ) ) {
				}
				// 権限レコードの追加
				else {
					$mdbAction->tedcauthorityInsert( $params );
				}
			}
			// 既存のデータがある場合
			else {

				// 試験毎権限が空の場合は削除する。
				if ( empty( $row_data["tedc_authority_name"] ) ) {
					$mdbAction->tedcauthorityDelete( $params );
				}
				// 権限レコードの更新
				else {
					$mdbAction->tedcauthorityUpdate( $params );
				}
			}
			
			// 割付参照権限の更新
			if( array_key_exists( $row_data["user_id"], $view_users ) === true )
			{
				// 割付参照権限ありの場合
				if( $row_data["allocation_view"] == "1" )
				{
					// 参照権限ありの場合は何もしない
				}
				else
				{
					// 参照権限なしの場合はデータを削除する
					$result = $mdbAction->deleteAllocationViewUser( $view_users[ $row_data["user_id"] ][ "viewuser_id" ] );
				}
			}
			else
			{
				// 割付参照権限なしの場合
				if( $row_data["allocation_view"] == "1" )
				{
					// 参照権限ありの場合は新規登録
					$result = $mdbAction->insertAllocationViewUser( $attributes["testee_id"], $row_data["user_id"] );
				}
				else
				{
					// 参照権限なしの場合は何もしない
				}
			}
		}




		// ヘッダーチェック
//		test_log($mdbAction->fgetcsv_reg($handle));

//		$row_data = array();
//		while (($data = $mdbAction->fgetcsv_reg($handle)) !== FALSE) {
//		    $num = count($data);
//		    for ($c=0; $c < $num; $c++) {
//		    	$data[$c] = mb_convert_encoding($data[$c], "UTF-8", "SJIS");
//		    }
//		    $row_data[] = $data;
//		}
//
//		fclose($handle); 
//		//データが件数を取得（ヘッダ部を除く）
//		$row_num = (count($row_data) - 1);
//		if($row_num <= 0) {
//    		return TESTEE_IMPORT_FILE_NO_DATA;
//		}
//		
//		$metadatas = $mdbView->getMetadatas(array("testee_id" => intval($attributes)));
//		if($metadatas === false) {
//    		return $errStr;
//    	}
//
//    	if(count($metadatas) != count($row_data[0])) {
//	    	return TESTEE_IMPORT_FILE_METADATA_NUM_ERROR;
//    	}else {
//    		$count = 0;
//	    	foreach(array_keys($metadatas) as $i) {
//    			if($metadatas[$i]['name'] != $row_data[0][$count]) {
//    				return $row_data[0][$count].":".TESTEE_IMPORT_FILE_METADATA_ERROR;
//    			}
//    			$count++;	
//	    	}
//    	}
//    	array_shift($row_data);
//    	
//    	$session =& $container->getComponent("Session");
//    	$session->setParameter(array("testee_csv_data", $attributes), $row_data);

		return;
	}
}
?>