<?php

/**
 * 割付結果をJavaScript の画面に表示するためのAction
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Allocationview extends Action
{
	/**
	 * 割付結果をJavaScript の画面に表示するためのAction
	 *
	 * @access  public
	 */
	function execute()
	{
		// 実際の処理はValidator で行い、ここは使わない。
		return 'success';
	}
}
?>
