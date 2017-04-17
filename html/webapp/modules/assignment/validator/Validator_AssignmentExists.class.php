<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 課題データ存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_AssignmentExists extends Validator
{
    /**
     * validate処理
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

        $assignmentView =& $container->getComponent("assignmentView");
		$request =& $container->getComponent("Request");

		if (empty($attributes["assignment_id"])) {
        	$attributes["assignment_id"] = $assignmentView->getCurrentAssignmentID();
        	$request->setParameter("assignment_id", $attributes["assignment_id"]);
		}

		if (empty($attributes["assignment_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
			return $errStr;
		}

        if (!$assignmentView->assignmentExists()) {
			return $errStr;
		}

        return;
    }
}
?>