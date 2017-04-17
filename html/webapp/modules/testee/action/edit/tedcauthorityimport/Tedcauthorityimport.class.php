<?php

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Tedcauthorityimport extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $session = null;

	// 値をセットするため

	/**
	 * csvインポート
	 *
	 * @access  public
	 */
	function execute()
	{


		return 'success';
	}

}
?>