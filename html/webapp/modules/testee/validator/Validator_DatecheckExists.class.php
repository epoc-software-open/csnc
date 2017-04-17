<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メタデータテーブルの存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_DatecheckExists extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数(items,item_id=0を許すかどうか)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$mdbView =& $container->getComponent("mdbView");
		
    	// condition_id取得
    	$testee_id = intval($attributes["testee_id"]);
    	$condition_id = intval($attributes["condition_id"]);

 		$datecheck =& $mdbView->getDateCheck($testee_id, $condition_id);
 		if($datecheck === false) return $errStr;
 		if(count($datecheck) > 0) return;

    	return $errStr;
    }
}
?>
