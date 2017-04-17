<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * CSV出力アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Edit_Export extends Action
{
    // validatorから受け取るため
    var $questionnaire = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireView = null;
    var $csvMain = null;

    /**
     * CSV出力アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->questionnaireView->setCSV()) {
			return "error";
		}

		$this->csvMain->download($this->questionnaire["questionnaire_name"]);
    }
}
?>
