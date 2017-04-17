<?php

/**
 * 設定内容(上書きCSS)の保存
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Stylechange_Action_Edit_Override extends Action
{
    // 使用コンポーネントを受け取るため
    var $stylechangeView = null;
    var $stylechangeAction = null;

    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    // --- リクエストパラメータ

    // 新しく設定するCSS テキスト
    var $csstext = null;


    /**
     * メイン処理
     *
     * @access  public
     */
    function execute()
    {

        // -----------------------------------------------------------
        // - 画面の内容をDB に保存
        // -----------------------------------------------------------
        $this->stylechangeAction->setCsstext( $this->csstext );

        // スタイルシートのベースディレクトリ
        $css_base_dir = HTDOCS_DIR . '/design_plus/css';

        // スタイルシートのベースディレクトリ
        $css_base_dir = HTDOCS_DIR . '/design_plus/css';

        // CSS 出力
        file_put_contents( $css_base_dir . "/user/user_override.css", $this->csstext );

        return 'success';
    }
}
?>
