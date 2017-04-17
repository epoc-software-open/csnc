<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_TesteeExists extends Validator
{
    /**
     * 汎用データベース存在チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$container =& DIContainerFactory::getContainer();

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();		
		if (empty($attributes["testee_id"]) &&
				($actionName == "testee_view_edit_create" ||
					$actionName == "testee_action_edit_create")) {
			return;
		}

        $mdbView =& $container->getComponent("mdbView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["testee_id"])) {
        	$attributes['testee_id'] = $mdbView->getCurrentMdbId();
        	$request->setParameter("testee_id", $attributes['testee_id']);
		}

		if (empty($attributes['testee_id'])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $mdbView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$mdbView->mdbExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>