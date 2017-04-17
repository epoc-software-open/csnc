<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付関連チェックの日付項目のチェック　同じ項目の場合、エラー
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_DatecheckAdd extends Validator
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
// 同じ日付項目を指定していないか
    	if($attributes["date1_id"] == $attributes["date2_id"]) {
			return "同じ日付項目が指定されています。異なる日付項目を指定してください。";
	    }
// 同じ日付項目の関連日付条件を設定していないか確認
    	// condition_id取得
    	$condition_id = intval($attributes["condition_id"]);
		// 追加の場合のみチェック
    	if($condition_id == 0) {
			// container取得
			$container =& DIContainerFactory::getContainer();
			$mdbView =& $container->getComponent("mdbView");

			// 既存の日付関連チェックを取得
			$datechecks = null;
			$result =& $mdbView->getDateCheck($attributes['testee_id']);
			if($result === false) {
				return "日付関連チェック取得エラー";
			}
			if(count($result) > 0) {
				$datechecks = $result;
			}

			if(!is_null($datechecks)) {
				foreach($datechecks as $datecheck){
			    	$key = array_search($attributes["date1_id"], $datecheck);
					if(!($key === false)){		// $attributes["date1_id"] が既に存在
						if($key == "date1_id"){
							if($datecheck["date2_id"] == $attributes["date2_id"]){
								return "既に同じ日付項目の関連日付条件が設定されています。";
							}
						} else {		// $datecheck["date2_id"] に $attributes["date1_id"]に存在
							if($datecheck["date1_id"] == $attributes["date2_id"]){
								return "既に同じ日付項目の関連日付条件が設定されています。";
							}
						}
					}
				}
		    }
		}
    	return;
    }
}
?>
