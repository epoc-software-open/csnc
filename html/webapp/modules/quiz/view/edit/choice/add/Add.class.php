<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 選択肢追加要素表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Edit_Choice_Add extends Action
{
    // リクエストパラメータを受け取るため
	var $choice_iteration = null;
	var $type = null;

	// 使用コンポーネントを受け取るため
    var $quizView = null;

    // validatorから受け取るため
    var $quiz = null;

    // 値をセットするため
    var $choice = array();

    /**
     * 選択肢追加要素表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$choiceConfig = $this->quizView->getChoiceConfig();
		if (!$choiceConfig) {
        	return "error";
        }

        $this->choice = $this->quizView->getDefaultChoice($this->choice_iteration, $choiceConfig["choice_label"], $choiceConfig["graph_color"]);
		if ($this->choice === false) {
        	return "error";
        }

		$this->choice_iteration++;

		return "success";
    }
}
?>
