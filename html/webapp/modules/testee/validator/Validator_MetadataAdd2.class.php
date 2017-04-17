<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック（項目追加-項目編集）施設名などのタイプ重複のチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_MetadataAdd2 extends Validator
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
// ひとつかどうかのチェック
    	if($attributes["type"] == TESTEE_META_TYPE_N_GROUP 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_HOSPITAL 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_BIRTHDAY 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_SEX 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_STATURE 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_WEIGHT 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_CREATININE 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_BSA 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_CCR) {
	    	// container取得
			$container =& DIContainerFactory::getContainer();
			$mdbView =& $container->getComponent("mdbView");
			
			$new = true;
	    	// metadata_id取得
	    	$metadata_id = intval($attributes["metadata_id"]);
	    	if($metadata_id != 0) {
	    		// 項目編集
				$new = false;
	    	}
		    $testee_id = intval($attributes["testee_id"]);
		    $result = $mdbView->getMetadataIDNAME($testee_id, $attributes["type"]);
	 		if($result === false) return $errStr;
			if(count($result) > 0){
				if($new) {
					return TESTEE_ERR_DUPLICATION_TYPE;
				} else {
					if($result[0]["metadata_id"] != $metadata_id){
	    				return TESTEE_ERR_DUPLICATION_TYPE;
					}
				}
			}
	    }

    	return;
    }
}
?>
