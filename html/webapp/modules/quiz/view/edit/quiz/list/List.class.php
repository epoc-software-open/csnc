<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Edit_Quiz_List extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
    var $block_id = null;
	var $scroll = null;
	var $quiz_id = null;
	var $summary_id = null;

    // 使用コンポーネントを受け取るため
    var $quizView = null;
    var $configView = null;
    var $request = null;
    var $session = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $quizCount = null;
	
	// 値をセットするため
    var $visibleRows = null;
    var $quizzes = null;
    var $currentQuizID = null;

    /**
     * 小テスト一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "quiz_list_row_count");
			if ($config === false) {
	        	return "error";
	        }
	        
	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);

			$this->currentQuizID = $this->quizView->getCurrentQuizID();
	        if ($this->currentQuizID === false) {
	        	return "error";
	        }

	        $this->session->setParameter("quiz_edit". $this->block_id, _ON);
		}
		
		$this->quizzes = $this->quizView->getQuizzes();
		if (empty($this->quizzes)) {
        	return "error";
        }
    	
        if ($this->scroll == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", 0);
        	
        	return "scroll";
        }

        return "screen";
    }
}
?>
