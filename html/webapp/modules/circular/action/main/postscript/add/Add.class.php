<?php

/**
 * 回覧追記処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Action_Main_Postscript_Add extends Action
{
	// リクエストパラメータを受け取るため

	// 使用コンポーネントを受け取るため
	var $circularAction = null;

	// 値をセットするため

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$circularId = $this->circularAction->addPostscript();
		if ($circularId === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
