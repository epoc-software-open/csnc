<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック（項目追加-項目編集）条件式のチェック
 * 値の数値チェック
 * 組み合わせチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_ConditionValue2 extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
// 条件が入力されていない場合エラー
 		if($attributes["cond1_ew"] == "" && $attributes["cond2_ew"] == "" && 
			$attributes["cond1_fugo1"] == "" && $attributes["cond1_value1"] == "" && 
 			$attributes["cond1_fugo2"] == "" && $attributes["cond1_value2"] == "" && 
			$attributes["cond2_fugo1"] == "" && $attributes["cond2_value1"] == "" && 
 			$attributes["cond2_fugo2"] == "" && $attributes["cond2_value2"] == "" ){
			return "条件を入力して下さい。";
		}
// 値が数値以外の場合、エラー
 		if($attributes["cond1_value1"] != "") {
			if(!is_numeric($attributes["cond1_value1"])){
				return TESTEE_ERR_CONDITION_VALUE;
			}
		}
 		if($attributes["cond1_value2"] != "") {
			if(!is_numeric($attributes["cond1_value2"])){
				return TESTEE_ERR_CONDITION_VALUE;
			}
		}
 		if($attributes["cond2_value1"] != "") {
			if(!is_numeric($attributes["cond2_value1"])){
				return TESTEE_ERR_CONDITION_VALUE;
			}
		}
 		if($attributes["cond2_value2"] != "") {
			if(!is_numeric($attributes["cond2_value2"])){
				return TESTEE_ERR_CONDITION_VALUE;
			}
		}
// 組み合わせチェック
// 条件1で不等号2・値2は不等号1・値1を先に設定
 		if($attributes["cond1_fugo2"] != "" || $attributes["cond1_value2"] != "") {
			if($attributes["cond1_fugo1"] == "" || $attributes["cond1_value1"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION2;
			}
		}
// 条件1でいづれか入力されている場合、EW・不等号1・値1は全て必要
 		if($attributes["cond1_ew"] != "" || $attributes["cond1_fugo1"] != "" || 
		 $attributes["cond1_value1"] != "") {
			if($attributes["cond1_ew"] == "" || $attributes["cond1_fugo1"] == "" || 
			 $attributes["cond1_value1"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION1;
			}
		}
// 条件1で不等号2・値2は全て必要
 		if($attributes["cond1_fugo2"] != "" || $attributes["cond1_value2"] != "") {
			if($attributes["cond1_fugo2"] == "" || $attributes["cond1_value2"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION3;
			}
		}
// 条件2で不等号2・値2は不等号1・値1を先に設定
 		if($attributes["cond2_fugo2"] != "" || $attributes["cond2_value2"] != "") {
			if($attributes["cond2_fugo1"] == "" || $attributes["cond2_value1"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION2;
			}
		}
// 条件2でいづれか入力されている場合、EW・不等号1・値1は全て必要
 		if($attributes["cond2_ew"] != "" || $attributes["cond2_fugo1"] != "" || 
		 $attributes["cond2_value1"] != "") {
			if($attributes["cond2_ew"] == "" || $attributes["cond2_fugo1"] == "" || 
			 $attributes["cond2_value1"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION1;
			}
		}
// 条件2で不等号2・値2は全て必要
 		if($attributes["cond2_fugo2"] != "" || $attributes["cond2_value2"] != "") {
			if($attributes["cond2_fugo2"] == "" || $attributes["cond2_value2"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION3;
			}
		}
// 条件2の設定の場合、条件1の設定後とする
 		if($attributes["cond2_ew"] != "") {
			if($attributes["cond1_ew"] == ""){
				return TESTEE_ERR_CONDITION_COMBINATION4;
			}
		}

// 条件の符号指定の不整合性チェック
		// 指定できない場所に"＝"が指定されていないかどうかのチェック
		if( $attributes["cond1_fugo2"] == TESTEE_META_FUGO_EQ )
		{
			return "条件式１　不整合\n（右側には＝は指定できません）";
		}
		
		if( $attributes["cond2_fugo2"] == TESTEE_META_FUGO_EQ )
		{
			return "条件式２　不整合\n（右側には＝は指定できません）";
		}
		
		// 条件１：左側に"＝"が指定されている場合
		if( $attributes["cond1_fugo1"] == TESTEE_META_FUGO_EQ )
		{
			if( $attributes["cond1_ew"] == TESTEE_META_EW_E )
			{
				// 条件１がエラー条件で、条件１右側/条件２左側/条件２右側のどれかに指定がある場合、エラー終了
				if( $attributes["cond1_fugo2"] != "" || $attributes["cond2_fugo1"] != "" || $attributes["cond2_fugo2"] != "" )
				{
					return "条件式１　不整合\n（エラー条件の場合、＝を指定した場合は他の条件を指定できません）";
				}
			}
			else if( $attributes["cond1_ew"] == TESTEE_META_EW_W )
			{
				// 条件１が警告条件で、条件１右側に指定がある場合、エラー
				if( $attributes["cond1_fugo2"] != "" )
				{
					return "条件式１　不整合\n（＝を指定した場合、右側には条件を指定できません）";
				}
			}
		}
		
		// 条件２：左側に"＝"が指定されている場合
		if( $attributes["cond2_fugo1"] == TESTEE_META_FUGO_EQ )
		{
			if( $attributes["cond2_ew"] == TESTEE_META_EW_E )
			{
				// エラー条件の時は条件２に"＝"は指定できない（条件１の指定が無意味になってしまう為）
				return "条件式２　不整合\n（エラー条件の場合、＝を指定できません）";
			}
			else if( $attributes["cond2_ew"] == TESTEE_META_EW_W )
			{
				// 条件２が警告条件で、条件２右側に指定がある場合、エラー
				if( $attributes["cond2_fugo2"] != "" )
				{
					return "条件式２　不整合\n（＝を指定した場合、右側には条件を指定できません）";
				}
			}
		}
		
		$condition = null;
		$err_msg = null;

	// 不等号での値の指定チェック
		if(is_null($condition)) {
			if($attributes["cond1_value2"] != "") {
				if(substr($attributes["cond1_fugo1"],0,1) == "1") {
					if($attributes["cond1_value1"] >= $attributes["cond1_value2"]) {
						$condition = "条件式１";
						$err_msg = "（条件の値が同じか逆転しています）";
					}
				} elseif (substr($attributes["cond1_fugo1"],0,1) == "2") {
					if($attributes["cond1_value1"] <= $attributes["cond1_value2"]) {
						$condition = "条件式１";
						$err_msg = "（条件の値が同じか逆転しています）";
					}
				}
			}
		}
		if(is_null($condition)) {
			if($attributes["cond2_value1"] != "" && $attributes["cond2_value2"] != "") {
				if(substr($attributes["cond2_fugo1"],0,1) == "1") {
					if($attributes["cond2_value1"] >= $attributes["cond2_value2"]) {
						$condition = "条件式２";
						$err_msg = "（条件の値が同じか逆転しています）";
					}
				} elseif (substr($attributes["cond2_fugo1"],0,1) == "2") {
					if($attributes["cond2_value1"] <= $attributes["cond2_value2"]) {
						$condition = "条件式２";
						$err_msg = "（条件の値が同じか逆転しています）";
					}
				}
			}
		}

		if($condition) return $condition.TESTEE_ERR_CONDITION_COMBINATION5.$err_msg;

    	return;
    }
}
?>
