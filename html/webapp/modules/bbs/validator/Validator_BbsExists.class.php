<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 掲示板存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Validator_BbsExists extends Validator
{
    /**
     * 掲示板存在チェックバリデータ
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
		if (empty($attributes["bbs_id"]) &&
				($actionName == "bbs_view_edit_entry" ||
					$actionName == "bbs_action_edit_entry")) {
			return;
		}

        $bbsView =& $container->getComponent("bbsView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["bbs_id"])) {
        	$attributes["bbs_id"] = $bbsView->getCurrentBbsID();
        	$request->setParameter("bbs_id", $attributes["bbs_id"]);
		}

		if (empty($attributes["bbs_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $bbsView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$bbsView->bbsExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>