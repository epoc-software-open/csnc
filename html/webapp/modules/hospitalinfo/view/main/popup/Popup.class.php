<?php

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Hospitalinfo_View_Main_Popup extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	var $hospitalinfo_id = null;

	// 使用コンポーネントを受け取るため
	var $hospitalinfoView = null;

	// 値をセットするため
	var $hospitalinfo = null;

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{

		// 施設情報データの取得
		$this->hospitalinfo = $this->hospitalinfoView->getHospitalinfo( $this->hospitalinfo_id );
		//print_r($this->hospitalinfo);

		return 'success';
	}
}
?>
