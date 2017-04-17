<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルアップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Upload_File extends Action
{
	// リクエストパラメータを受け取るため
	//var $upload_callback = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
	
	//値をセットするため
	//var $filelist = array();
	/**
     * アップロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	$filelist = $this->uploadsAction->uploads();
    	
    	return true;
    }
}
?>
