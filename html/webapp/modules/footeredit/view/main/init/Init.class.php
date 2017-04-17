<?php

/**
 * 設定内容表示処理
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    // 使用コンポーネントを受け取るため
    var $footereditView = null;

    // フッター設定内容
    var $footer_data = null;

    /**
     * メイン処理
     *
     * @access  public
     */
    function execute()
    {

        // -----------------------------------------------------------
        // - 設定内容
        // -----------------------------------------------------------

        // 設定内容を取得する。
        $this->footer_data = $this->footereditView->getFooterData();
        //print_r( $this->footer_data );

        return 'success';
    }
}
?>
