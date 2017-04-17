<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設の追加の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_View_Edit_Location_Add extends Action
{
	// Filterから受け取るため
	var $room_arr = null;

    // 使用コンポーネントを受け取るため
	var $reservationView = null;

    // 値をセットするため
	var $location = null;
	var $select_rooms = array();
	var $category_list = null;
	var $timezone_list = null;
	var $week_list = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$this->location = $this->reservationView->getDefaultLocation();
		$this->category_list = $this->reservationView->getCategories();
     	if ($this->category_list === false) {
    		return 'error';
    	}
    	$this->timezone_list = explode("|", RESERVATION_DEF_TIMEZONE);
		$this->week_list = $this->reservationView->getLocationWeekArray();
       	return 'success';
    }
}
?>