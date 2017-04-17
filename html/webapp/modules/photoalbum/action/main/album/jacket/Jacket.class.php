<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ジャケットアップロードアクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Main_Album_Jacket extends Action
{
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
    var $session = null;
	
	/**
     * ジャケットアップロードアクション
     *
     * @access  public
     */
    function execute()
    {
    	$files = $this->uploadsAction->uploads();
		$this->session->setParameter("photoalbum_jacket_upload_id", $files[0]["upload_id"]);
    	
    	return true;
    }
}
?>
