<?php

/**
 * 設定内容(スタイル)の条件変更。表示処理でPOST を使いたいためのアクションチェーン
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Stylechange_Action_View_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    /**
     * メイン処理
     *
     * @access  public
     */
    function execute()
    {

        // 設定内容(スタイル)の条件変更。表示処理でPOST を使いたいためのアクションチェーン
        // そのため、何もせずに次に進む。

        return 'success';
    }
}
?>
