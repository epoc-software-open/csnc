<?php

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Allocation_List extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;

	// コンポーネントを受け取るため
	var $mdbView = null;

	// 値をセットするため
	var $allocation = null;
	var $adjust_metadata_item = null;
	var $selected_allocation = null;
	var $group_content = null;
	
	/**
	 * @access  public
	 */
	function execute()
	{
		$this->allocation = $this->mdbView->getAllocationContent( $this->testee_id );
		$this->adjust_metadata_item = $this->mdbView->getAllocationItem( $this->testee_id );
		$this->selected_allocation = $this->mdbView->getSelectedAllocationItem( $this->testee_id );
		$this->group_content = $this->mdbView->getGroupContent( $this->testee_id );
		$this->setting_history = $this->mdbView->getSettinghistory( $this->testee_id );
		return 'success';
    }
}
?>
