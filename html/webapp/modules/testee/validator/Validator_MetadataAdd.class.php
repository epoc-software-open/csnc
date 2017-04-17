<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック（項目追加-項目編集）maple.ini->key指定すること
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_MetadataAdd extends Validator
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
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$mdbView =& $container->getComponent("mdbView");
		
    	// metadata_id取得
    	$metadata_id = intval($attributes["metadata_id"]);
    	if($metadata_id != 0) {
    		// 項目編集
    		$metadata =& $mdbView->getMetadataById($metadata_id);
 			if($metadata === false) return $errStr;
 				
 			//
	 		// Actionにデータセット
	 		//
	
			// actionChain取得
			$actionChain =& $container->getComponent("ActionChain");
			$action =& $actionChain->getCurAction();
			if(isset($params[0])) {
				BeanUtils::setAttributes($action, array($params[0]=>$metadata));
			} else {
				BeanUtils::setAttributes($action, array("items"=>$metadata));
			}
 		
    	}
    	if($attributes["type"] == TESTEE_META_TYPE_SECTION 
    		|| $attributes["type"] == TESTEE_META_TYPE_MULTIPLE) {
    		//
    		// 選択式
    		//
			$attr_options = $attributes["options"][1];
    		if(!isset($attr_options) || count($attr_options) == 0) {
    			//選択肢に1項目も指定していない
    			return TESTEE_ERR_NONEEXISTS_OPTIONS;
    		}
    		$select_count = 0;
    		$option_arr = array();
    		foreach($attr_options as $key => $options) {	// 標準の選択肢
    			if (!strlen(trim($options))) {
    				return TESTEE_ERR_OPTION_EMPTY_NAME;
    			}
    			if(in_array($options, $option_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_OPTIONS;
    			}
    			array_push($option_arr, $options);
    			if(preg_match("/\|/", $options)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_OPTIONS;
    			}
	    	}
    	}

// 追加メタタイプのチェック　上記選択と同じ
    	if($attributes["type"] == TESTEE_META_TYPE_N_RADIO 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_GROUP 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_HOSPITAL 
    		|| $attributes["type"] == TESTEE_META_TYPE_N_SEX) {
    		//
    		// 選択式
    		//
			// 選択式を0にして決定した時のワーニング回避の修正
			$attr_options = array();
			if ( array_key_exists( 1, $attributes["options"] ) ) {
				$attr_options = $attributes["options"][1];
			}
    		if(!isset($attr_options) || count($attr_options) == 0) {
    			//選択肢に1項目も指定していない
    			return TESTEE_ERR_NONEEXISTS_OPTIONS;
    		}
    		$select_count = 0;
    		$option_arr = array();
    		foreach($attr_options as $key => $options) {
    			if (!strlen(trim($options))) {
    				return TESTEE_ERR_OPTION_EMPTY_NAME;
    			}
    			if(in_array($options, $option_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_OPTIONS;
    			}
    			array_push($option_arr, $options);
    			if(preg_match("/\|/", $options)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_OPTIONS;
    			}
	    	}
			// 選択肢コード値のチェック追加		EddyK
    		$content_code_arr = array();
    		foreach($attributes["select_content_code"][1] as $key => $content_code) {
    			if (!strlen(trim($content_code))) {
    				return TESTEE_ERR_CONTENT_CODE_EMPTY_NAME;
    			}
    			if(in_array($content_code, $content_code_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_CONTENT_CODE;
    			}
    			array_push($content_code_arr, $content_code);
    			if(preg_match("/\|/", $content_code)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_CONTENT_CODE;
    			}
	    	}
    	}

// はい・いいえタイプのチェック　上記選択と同じ
    	if($attributes["type"] == TESTEE_META_TYPE_N_YESNO) {
    		//
    		// 選択式
    		//
			$attr_options = $attributes["options"][2];
    		if(!isset($attr_options) || count($attr_options) == 0) {
    			//選択肢に1項目も指定していない
    			return TESTEE_ERR_NONEEXISTS_OPTIONS;
    		}
    		$select_count = 0;
    		$option_arr = array();
    		foreach($attr_options as $key => $options) {
    			if (!strlen(trim($options))) {
    				return TESTEE_ERR_OPTION_EMPTY_NAME;
    			}
    			if(in_array($options, $option_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_OPTIONS;
    			}
    			array_push($option_arr, $options);
    			if(preg_match("/\|/", $options)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_OPTIONS;
    			}
	    	}
			// 選択肢コード値のチェック追加		EddyK
			$attr_content_code = $attributes["select_content_code"][2];
    		$content_code_arr = array();
    		foreach($attr_content_code as $key => $content_code) {
    			if (!strlen(trim($content_code))) {
    				return TESTEE_ERR_CONTENT_CODE_EMPTY_NAME;
    			}
    			if(in_array($content_code, $content_code_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_CONTENT_CODE;
    			}
    			array_push($content_code_arr, $content_code);
    			if(preg_match("/\|/", $content_code)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_CONTENT_CODE;
    			}
	    	}
    	}

// 該当せず・該当タイプのチェック　上記選択と同じ
    	if($attributes["type"] == TESTEE_META_TYPE_N_APPLICABLE) {
    		//
    		// 選択式
    		//
			$attr_options = $attributes["options"][3];
    		if(!isset($attr_options) || count($attr_options) == 0) {
    			//選択肢に1項目も指定していない
    			return TESTEE_ERR_NONEEXISTS_OPTIONS;
    		}
    		$select_count = 0;
    		$option_arr = array();
    		foreach($attr_options as $key => $options) {
    			if (!strlen(trim($options))) {
    				return TESTEE_ERR_OPTION_EMPTY_NAME;
    			}
    			if(in_array($options, $option_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_OPTIONS;
    			}
    			array_push($option_arr, $options);
    			if(preg_match("/\|/", $options)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_OPTIONS;
    			}
	    	}
			// 選択肢コード値のチェック追加		EddyK
			$attr_content_code = $attributes["select_content_code"][3];
    		$content_code_arr = array();
    		foreach($attr_content_code as $key => $content_code) {
    			if (!strlen(trim($content_code))) {
    				return TESTEE_ERR_CONTENT_CODE_EMPTY_NAME;
    			}
    			if(in_array($content_code, $content_code_arr, true)) {
    				//同じ選択肢値が存在する
    				return TESTEE_ERR_DUPLICATION_CHAR_CONTENT_CODE;
    			}
    			array_push($content_code_arr, $content_code);
    			if(preg_match("/\|/", $content_code)) {
    				//禁止文字「|」
    				return TESTEE_ERR_PROHIBITION_CHAR_CONTENT_CODE;
    			}
	    	}
    	}
    	return;
    }
}
?>
