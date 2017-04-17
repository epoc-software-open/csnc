<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック
 * 必須チェック
 *  リクエストパラメータ
 *  var $registration_id = null;
 *  var $items = null;
 *
 * @package	 NetCommons.validator
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Testee_Validator_MetadataInputNcch extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 *
	 * @param   string  $errStr	 エラー文字列(未使用：エラーメッセージ固定)
	 * @param   array   $params	 オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 * $attributes['testee_id']
	 * $attributes['content_insert_date']
	 * $attributes['datas']
	 */
	function validate($attributes, $errStr, $params)
	{

		$error_massage = array();			// 独自分エラーメッセージ
		$warning_massage = array();			// 独自分ワーニングメッセージ
		$system_error_massage = array();	// システム・エラーメッセージ

		$stature = 0;		// 身長
		$weight = 0;		// 体重
		$sex = 0;			// 性別( 0:未選択, 1:男, 2:女 )
		$creatinine = 0;	// 血清クレアチニン値

		// - エラーメッセージ固定分の定義
		$error_massage_prefix   = '・';
		// ワーニングメッセージ固定分の定義
		$warning_massage_footer = "この内容でよろしいでしょうか？\n";

		if(!isset($attributes['testee_id'])) {
			return $errStr;
		}

		// container取得
		$container =& DIContainerFactory::getContainer();

		$request =& $container->getComponent("Request");
		$warning_ok = $request->getParameter('warning_ok');
		$confirm_ok = $request->getParameter('confirm_ok');

		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$mdbView =& $container->getComponent("mdbView");
		$escapeText =& $container->getComponent("escapeText");

		// グループ項目の選択肢情報を取得
		$result = $mdbView->getGroup($attributes['testee_id']);
		if ($result === false) {
			return $errStr;
		}
		if(count($result) > 0){
			$group_codes = explode("|", $result[0]["select_content_code"]);
			$groups = explode("|", $result[0]["select_content"]);
			for($i = 0; count($group_codes) > $i; $i++){
				$group_data[$group_codes[$i]] = $groups[$i];
			}
		}

		$order_params = array(
				"display_pos" => "ASC",
				"display_sequence" => "ASC"
		);
		$show_metadatas =& $mdbView->getMetadatas(array("testee_id"=>$attributes['testee_id']), $order_params);
		if($show_metadatas === false) {
			$errStr = $smartyAssign->getLang("_invalid_input");
			return $errStr;
		}

		$datas = $attributes['datas'];
		$files = $attributes['files'];
		$content_insert_date = $attributes['content_insert_date'];

		$datevalues = null;				// 日付タイプの値保持（[metadata_id] => $datas[$metadata_id] ）
		$datechecks = null;				// 関連日付チェック情報
		$result =& $mdbView->getDateCheck($attributes['testee_id']);
		if($result === false) {
			return "日付関連チェック取得エラー";
		}
		if(count($result) > 0) {
			$datechecks = $result;
		}

		$errors = array();

		$group_name = null;
		$group_option = null;
		$group_option_name = null;

		foreach($show_metadatas as $metadata) {
			$metadata_id = $metadata['metadata_id'];
			$in_name = $metadata['name'];

/* 新しく追加したメタタイプ
日付タイプ
TESTEE_META_TYPE_N_DATE
TESTEE_META_TYPE_N_BIRTHDAY
テキストタイプ
TESTEE_META_TYPE_N_NUMERIC
TESTEE_META_TYPE_N_STATURE
TESTEE_META_TYPE_N_WEIGHT
TESTEE_META_TYPE_N_CREATININE
TESTEE_META_TYPE_N_BSA
TESTEE_META_TYPE_N_CCR
TESTEE_META_TYPE_N_COMMENT（入力無）
選択タイプ
TESTEE_META_TYPE_N_RADIO
TESTEE_META_TYPE_N_YESNO
TESTEE_META_TYPE_N_APPLICABLE
TESTEE_META_TYPE_N_HOSPITAL
TESTEE_META_TYPE_N_SEX
グループ
TESTEE_META_TYPE_N_GROUP
*/
			// 数値のチェック（身長・体重・クレアチニン値・チェック付数値・チェック付日付・生年月日・BSA・CCR）
			if(($metadata['type'] == TESTEE_META_TYPE_N_STATURE || 
				$metadata['type'] == TESTEE_META_TYPE_N_WEIGHT || 
				$metadata['type'] == TESTEE_META_TYPE_N_CREATININE || 
				$metadata['type'] == TESTEE_META_TYPE_N_NUMERIC || 
				$metadata['type'] == TESTEE_META_TYPE_N_BSA || 
				$metadata['type'] == TESTEE_META_TYPE_N_CCR || 
				$metadata['type'] == TESTEE_META_TYPE_N_DATE || 
				$metadata['type'] == TESTEE_META_TYPE_N_BIRTHDAY ) && (strlen(trim($datas[$metadata_id],"\t \n\0　")) > 0)){
				// 条件式の取得
				$result = $mdbView->getCondition($metadata_id);
				if ($result === false) {
					return 'error';
				}
				// 条件式がある場合のみ条件式によるチェックを行う
				if (count($result) > 0) {
					// エラーチェック用条件
					$cond1_ew     = $result[0]["cond1_ew"];
					$cond1_fugo1  = $result[0]["cond1_fugo1"];
					$cond1_value1 = $result[0]["cond1_value1"];
					$cond1_fugo2  = $result[0]["cond1_fugo2"];
					$cond1_value2 = $result[0]["cond1_value2"];
					$cond2_ew     = $result[0]["cond2_ew"];
					$cond2_fugo1  = $result[0]["cond2_fugo1"];
					$cond2_value1 = $result[0]["cond2_value1"];
					$cond2_fugo2  = $result[0]["cond2_fugo2"];
					$cond2_value2 = $result[0]["cond2_value2"];

					switch($metadata['type']){
						case TESTEE_META_TYPE_N_STATURE:
							$text_value = floatval($escapeText->escapeText($datas[$metadata_id]));
							$stature = $text_value;
							break;
						case TESTEE_META_TYPE_N_WEIGHT:
							$text_value = floatval($escapeText->escapeText($datas[$metadata_id]));
							$weight = $text_value;
							break;
						case TESTEE_META_TYPE_N_CREATININE:
							$text_value = floatval($escapeText->escapeText($datas[$metadata_id]));
							$creatinine = $text_value;
							break;
						case TESTEE_META_TYPE_N_NUMERIC:
						case TESTEE_META_TYPE_N_BSA:
						case TESTEE_META_TYPE_N_CCR:
							$text_value = floatval($escapeText->escapeText($datas[$metadata_id]));
							break;
						case TESTEE_META_TYPE_N_DATE:
							// 日数の計算（入力日 － 登録日で日数を取得）
							$text_value = $this->_countdays($content_insert_date, $datas[$metadata_id]);
							break;
						case TESTEE_META_TYPE_N_BIRTHDAY:
							// 年齢の取得
							$age = $this->_calcAge_impl($datas[$metadata_id], $content_insert_date);
							$text_value = $age;
							break;
					}

					// -----------------------------------------------
					// - 1つ目の条件チェック
					// -----------------------------------------------
					$check_kind  = $cond1_ew;
					$cond_fugo1  = $cond1_fugo1;
					$cond_value1 = $cond1_value1;
					$cond_fugo2  = $cond1_fugo2;
					$cond_value2 = $cond1_value2;

					// 入力の値と条件式1・2のチェック
					if($check_kind != ""){
						list($system_error_massage,$error_massage,$warning_massage) = $this->_chkCondtion($in_name,$text_value,1,$check_kind,$cond_fugo1,$cond_value1,$cond_fugo2,$cond_value2,$system_error_massage,$error_massage,$warning_massage);
					}

					// -----------------------------------------------
					// - 2つ目の条件チェック
					// -----------------------------------------------
					$check_kind  = $cond2_ew;
					$cond_fugo1  = $cond2_fugo1;
					$cond_value1 = $cond2_value1;
					$cond_fugo2  = $cond2_fugo2;
					$cond_value2 = $cond2_value2;

					// 入力の値と条件式3・4のチェック
					if($check_kind != ""){
						list($system_error_massage,$error_massage,$warning_massage) = $this->_chkCondtion($in_name,$text_value,2,$check_kind,$cond_fugo1,$cond_value1,$cond_fugo2,$cond_value2,$system_error_massage,$error_massage,$warning_massage);
					}
				}
			}

			// ラジオボタンの正解のチェック
			if(($metadata['type'] == TESTEE_META_TYPE_N_RADIO || $metadata['type'] == TESTEE_META_TYPE_N_YESNO || $metadata['type'] == TESTEE_META_TYPE_N_APPLICABLE) && 
				($metadata['correct'] != "" && !is_null($metadata['correct'])) && !empty($datas[$metadata_id])) {
	    		$options = explode("|", $metadata['select_content']);
	    		$correct = explode("|", $metadata['correct']);
				foreach($options as $key => $option){
					if($datas[$metadata_id] == $option){
						if($correct[$key] != "1"){
							array_push($error_massage, $error_massage_prefix . $in_name . "　選択が正しくありません" );
						}
					}
				}
			}

			// グループ設定での登録有無チェック
			if($metadata['type'] == TESTEE_META_TYPE_N_GROUP){
				// グループ項目の登録内容を保存
				if(empty($datas[$metadata_id])) {
					array_push($error_massage, $error_massage_prefix . $in_name . "　グループ設定項目は必須です。");
				} else {
				// グループ項目の項目名・登録内容を保存
					$group_neme = $in_name;
					$group_option = array_search($datas[$metadata_id], $group_data);
					$group_option_name = $datas[$metadata_id];
				}
			}

			// 関連日付チェックが設定されている場合、日付タイプの値を関連チェックのため保持
			if(!is_null($datechecks)){
				if($metadata['type'] == TESTEE_META_TYPE_N_DATE || 
					$metadata['type'] == TESTEE_META_TYPE_N_BIRTHDAY || 
					$metadata['type'] == TESTEE_META_TYPE_DATE){
					$datevalues[$metadata_id] = $datas[$metadata_id];
				}
			}
		}

		// 関連日付チェック
		if(!is_null($datechecks)){
			foreach($datechecks as $datecheck) {
				// 1つめの日付の入力項目の値を取得
				$date_from_str = $datevalues[$datecheck["date1_id"]];
				// 2つめの日付の入力項目の値を取得
				$date_to_str = $datevalues[$datecheck["date2_id"]];

				if(strlen($date_from_str) || strlen($date_to_str)){	// どちらかが入力されている場合のみチェック
					// 日数の計算（to日 － from日で日数を取得）
					$check_count = $this->_countdays($date_from_str, $date_to_str);

					$in_name = $datecheck["date1"] . "と" . $datecheck["date2"] . "関連";
					$text_value = intval( $check_count );

					// -----------------------------------------------
					// - 1つ目の条件チェック
					// -----------------------------------------------
					$check_kind  = $datecheck["cond1_ew"];
					$cond_fugo1  = $datecheck["cond1_fugo1"];
					$cond_value1 = $datecheck["cond1_value1"];
					$cond_fugo2  = $datecheck["cond1_fugo2"];
					$cond_value2 = $datecheck["cond1_value2"];

					// 入力の値と条件式1・2のチェック
					list($system_error_massage,$error_massage,$warning_massage) = $this->_chkCondtion($in_name,$text_value,1,$check_kind,$cond_fugo1,$cond_value1,$cond_fugo2,$cond_value2,$system_error_massage,$error_massage,$warning_massage);

					// -----------------------------------------------
					// - 2つ目の条件チェック
					// -----------------------------------------------
					$check_kind  = $datecheck["cond2_ew"];
					$cond_fugo1  = $datecheck["cond2_fugo1"];
					$cond_value1 = $datecheck["cond2_value1"];
					$cond_fugo2  = $datecheck["cond2_fugo2"];
					$cond_value2 = $datecheck["cond2_value2"];

					// 入力の値と条件式3・4のチェック
					list($system_error_massage,$error_massage,$warning_massage) = $this->_chkCondtion($in_name,$text_value,2,$check_kind,$cond_fugo1,$cond_value1,$cond_fugo2,$cond_value2,$system_error_massage,$error_massage,$warning_massage);
				}
			}
		}

		// 2回目のメタタイプ別ループでのチェック（グループとメタグループ指定）
		foreach($show_metadatas as $metadata) {
			$metadata_id = $metadata['metadata_id'];
			$in_name = $metadata['name'];
			// グループ設定の内容と登録の有無をチェック
			if(($metadata['group_option'] != "" || $metadata['group_option'] != null) && !is_null($group_option)) {
				if($metadata['group_option'] == $group_option){
					if($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE) {
						if(empty($files[$metadata_id])){
							array_push($error_massage, $error_massage_prefix . $in_name . "　". $group_neme . "が". $group_option_name . "の場合、登録が必要です。");
						}
					} else {
						if(empty($datas[$metadata_id])){
							array_push($error_massage, $error_massage_prefix . $in_name . "　". $group_neme . "が". $group_option_name . "の場合、登録が必要です。");
						}
					}
				} else {
					if($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE) {
						if(!empty($files[$metadata_id])){
							array_push($error_massage, $error_massage_prefix . $in_name . "　". $group_neme . "が". $group_option_name . "の場合、登録は不要です。");
						}
					} else {
						if(!empty($datas[$metadata_id])){
							array_push($error_massage, $error_massage_prefix . $in_name . "　". $group_neme . "が". $group_option_name . "の場合、登録は不要です。");
						}
					}
				}
			}

		}

		// エラー処理
		if (!empty($system_error_massage)) {
			$errStr = "システムの設定にエラーがあります。<br />システム管理者に以下の内容を連絡してください。<br/ ><br/ >";

			foreach($system_error_massage as $key => $val) {
				$errStr .= $val."<br />";
			}
			return $errStr;
		}
		if (!empty($error_massage)) {
			$errStr = "次の項目にエラーがあります。<br />";

			foreach($error_massage as $key => $val) {
				$errStr .= $val."<br />";
			}
			return $errStr;
		}
		// ワーニング処理
		if (!empty($warning_massage) && $warning_ok != '1') {
			$errStr  = '[warning]';
			$errStr .= "次の項目にワーニングがあります。<br />";

			foreach($warning_massage as $key => $val) {
				$errStr .= $val."<br />";
			}
			$errStr .= $warning_massage_footer;

			return $errStr;
		}

		// ここから下は、ワーニング、エラーもない状態で実行される場所
		// 確認処理
		if ( $confirm_ok != '1' ) {
			return '[confirm]';
		}
		// 正常処理
		return;
	}

// -------------------------------------- //
// 共通関数								  //
// -------------------------------------- //

	function _chkCondtion($in_name,$text_value,$cond_no,$check_kind,$cond_fugo1,$cond_value1,$cond_fugo2,$cond_value2,$system_error_massage,$error_massage,$warning_massage) {

		// - エラーメッセージ固定分の定義
		$error_massage_prefix   = '・';
		$warning_massage_prefix   = '・';

		// 条件1
		if ( $this->_check_num( $text_value, $cond_fugo1, $cond_value1 ) ) {
			// 条件1がOKの場合、条件2を判定
			if ( $cond_value2 != "" ) {
				if ( !$this->_check_num( $text_value, $cond_fugo2, $cond_value2 ) ) {
					// 条件2でエラー or ワーニング
					if ( $check_kind == TESTEE_META_EW_E ) {
						array_push($error_massage, $error_massage_prefix . $in_name . "が範囲外です。" );
					} else {
						array_push($warning_massage, $warning_massage_prefix . $in_name . "が範囲外です。" );
					}
				}
			}
		} else {
			// 条件1でエラー or ワーニング
			if ( $check_kind == TESTEE_META_EW_E ) {
				array_push($error_massage, $error_massage_prefix . $in_name . "が範囲外です。");
			} else {
				array_push($warning_massage, $warning_massage_prefix . $in_name . "が範囲外です。" );
			}
		}

		return array($system_error_massage,$error_massage,$warning_massage);

	}

	function _check_num($text_value, $cond_fugo, $cond_value) {
		$check_value = floatval($cond_value);
		// 条件
		switch($cond_fugo){
			case TESTEE_META_FUGO_LE:
				if ( ( $text_value <= $check_value ) == false ) {
					return false;
				}
				break;
			case TESTEE_META_FUGO_L:
				if ( ( $text_value < $check_value ) == false ) {
					return false;
				}
				break;
			case TESTEE_META_FUGO_GE:
				if ( ( $text_value >= $check_value ) == false ) {
					return false;
				}
				break;
			case TESTEE_META_FUGO_G:
				if ( ( $text_value > $check_value ) == false ) {
					return false;
				}
				break;
			case TESTEE_META_FUGO_EQ:
				if ( ( $text_value == $check_value ) == false ) {
					return false;
				}
				break;
			case TESTEE_META_FUGO_NE:
				if ( ( $text_value != $check_value ) == false ) {
					return false;
				}
				break;
		}
		return true;
	}

	function _countdays($source_day , $to_day) {
		if(empty($source_day) || empty($to_day) ){
			return 0;
		}
		$s = mktime(0,0,0,substr($to_day,5,2),substr($to_day,8,2),substr($to_day,0,4)) - mktime(0,0,0,substr($source_day,5,2),substr($source_day,8,2),substr($source_day,0,4));
		$days = $s/60/60/24;
		return round($days,1);
	}

	function _calcAge_impl($input_date, $insert_date) {
		$birthday = intval(str_replace("/", "", $input_date));
		// 計算当日の設定
		$today = intval(str_replace("/", "", $insert_date));
		// 年齢計算
		$age = (int)(($today - $birthday)/10000); 
		return $age;
	}

}
?>