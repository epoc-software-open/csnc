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
class Testee_Validator_ConditionValue extends Validator
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
    	if($attributes["type"] == TESTEE_META_TYPE_N_DATE 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_BIRTHDAY 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_NUMERIC 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_STATURE 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_WEIGHT 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_CREATININE 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_BSA 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_CCR) {
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
			$condition = null;
			$err_msg = null;
		// "＝" "≠"以外の場合、同種の不等号を指定の場合エラー
			if($attributes["cond1_fugo2"] != "") {
				if(substr($attributes["cond1_fugo1"],0,1) != "3" && 
					substr($attributes["cond1_fugo1"],0,1) == substr($attributes["cond1_fugo2"],0,1)) {
					$condition = "条件式１";
					$err_msg = "（ひとつの条件で同種の不等号が使用されています）";
				}
			}
			if(is_null($condition)) {
				if($attributes["cond2_fugo1"] != "" && $attributes["cond2_fugo2"] != "") {
					if(substr($attributes["cond2_fugo1"],0,1) != "3" && 
						substr($attributes["cond2_fugo1"],0,1) == substr($attributes["cond2_fugo2"],0,1)) {
						$condition = "条件式２";
						$err_msg = "（ひとつの条件で同種の不等号が使用されています）";
					}
				}
			}

		// "＝" はひとつのみ指定以外エラー
			if(is_null($condition)) {
		 		if($attributes["cond1_fugo1"] == TESTEE_META_FUGO_EQ) {
			 		if($attributes["cond1_fugo2"] != "" || $attributes["cond2_fugo1"] != "" || $attributes["cond2_fugo2"] != "") {
						$condition = "条件式";
						$err_msg = "（＝はひとつのみ使用可能です）";
					}
				}
			}
			if(is_null($condition)) {
		 		if($attributes["cond1_fugo2"] == TESTEE_META_FUGO_EQ) {
					$condition = "条件式";
					$err_msg = "（＝はひとつのみ使用可能です）";
				}
			}
			if(is_null($condition)) {
		 		if($attributes["cond2_fugo1"] == TESTEE_META_FUGO_EQ) {
					$condition = "条件式";
					$err_msg = "（＝はひとつのみ使用可能です）";
				}
			}
			if(is_null($condition)) {
		 		if($attributes["cond2_fugo2"] == TESTEE_META_FUGO_EQ) {
					$condition = "条件式";
					$err_msg = "（＝はひとつのみ使用可能です）";
				}
			}

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

	    }

    	return;
    }
}
?>
