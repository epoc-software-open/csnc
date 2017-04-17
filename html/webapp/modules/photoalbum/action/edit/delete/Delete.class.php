<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Action_Edit_Delete extends Action
{
    // 使用コンポーネントを受け取るため
    var $photoalbumAction = null;

    /**
     * フォトアルバム削除アクション
     *
     * @access  public
     */
    function execute()
    {
        if (!$this->photoalbumAction->deletePhotoalbum()) {
        	return "error";
        }

		return "success";
    }
}
?>
