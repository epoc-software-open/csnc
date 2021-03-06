<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 質問入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Edit_Question_Entry extends Action
{
    // validatorから受け取るため
    var $questionnaire = null;
    var $question = null;

	// 値をセットするため
    var $choiceCount = null;
    
    /**
     * 質問入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->choiceCount = count($this->question["choices"]);
		
		return "success";
    }
}
?>
