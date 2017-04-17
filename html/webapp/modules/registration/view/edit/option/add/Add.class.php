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
class Registration_View_Edit_Option_Add extends Action
{
    // リクエストパラメータを受け取るため
	var $option_iteration = null;
	
    /**
     * 選択肢追加要素表示アクション
     *
     * @access  public
     */
    function execute()
    {
		return "success";
    }
}
?>