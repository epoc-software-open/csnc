<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 問題参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_QuestionView extends Validator
{
    /**
     * 問題参照権限チェックバリデータ
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
        $quizView =& $container->getComponent("quizView");

		if (empty($attributes["question_id"])) {
			$question = $quizView->getDefaultQuestion();
		} else {
			$question = $quizView->getQuestion();
		}
		if (empty($question)) {
        	return $errStr;
        }
        
        if (!empty($attributes["question_id"])
        		&& $question["quiz_id"] != $attributes["quiz_id"]) {
        	return $errStr;
        }
		        
		$request =& $container->getComponent("Request");
    	$request->setParameter("question", $question);

        return;
    }
}
?>