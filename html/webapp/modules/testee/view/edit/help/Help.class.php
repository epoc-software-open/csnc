<?php

/**
 * ヘルプ画面（使い方説明）の表示
 *
 * @package     NetCommons
 * @author      Atsushi Nagahara ( nagahara@opensource-workshop.jp )
 * @copyright   2012 http://opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons add on module, supported by OpenSource-WorkShop inc.
 * @access      public
 */
class Testee_View_Edit_Help extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    // 使用コンポーネントを受け取るため
    var $edbView = null;

    /**
     * メイン処理
     *
     * @access  public
     */
    function execute()
    {
        return 'success';
    }
}
?>
