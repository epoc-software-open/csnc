<?php

/**
 * 設定内容(スタイル)の保存
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Stylechange_Action_Edit_Init extends Action
{
    // 使用コンポーネントを受け取るため
    var $stylechangeView = null;
    var $stylechangeAction = null;

    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    // --- リクエストパラメータ

    // 新しく設定するグローバルメニューの型
    var $new_globalmenu_type = null;

    // 新しく設定するグローバルメニューの色
    var $new_globalmenu_color = null;

    // 新しく設定するサブメニューの型
    var $new_submenu_type = null;

    // 新しく設定するサブメニューの色
    var $new_submenu_color = null;

    // 新しく設定するタイトルアクセントの型
    var $new_titleaccent_type = null;

    // 新しく設定するタイトルアクセントの色
    var $new_titleaccent_color = null;


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
        $this->stylechangeAction->setStyles( $this->new_globalmenu_type,  $this->new_globalmenu_color,
                                             $this->new_submenu_type,     $this->new_submenu_color,
                                             $this->new_titleaccent_type, $this->new_titleaccent_color );

        // CSS 文字列
        $css_str = '';
        $css_str .= '@import url("./globalmenu/'
                 . $this->new_globalmenu_type . '/'
                 . $this->new_globalmenu_color . '/'
                 . 'globalmenu_' . $this->new_globalmenu_type . '_' . $this->new_globalmenu_color . ".css\");\n";

        $css_str .= '@import url("./submenu/'
                 . $this->new_submenu_type . '/'
                 . $this->new_submenu_color . '/'
                 . 'submenu_' . $this->new_submenu_type . '_' . $this->new_submenu_color . ".css\");\n";

        $css_str .= '@import url("./titleaccent/'
                 . $this->new_titleaccent_type . '/'
                 . $this->new_titleaccent_color . '/'
                 . 'titleaccent_' . $this->new_titleaccent_type . '_' . $this->new_titleaccent_color . ".css\");\n";


        $css_str .= '@import url("./slide/default/white/slide_default_white.css");' . "\n";
        $css_str .= '@import url("./override/override.css");' . "\n";
        $css_str .= '@import url("./user/user_override.css");' . "\n";

        // スタイルシートのベースディレクトリ
        $css_base_dir = HTDOCS_DIR . '/design_plus/css';

        // CSS 出力
        file_put_contents( $css_base_dir . "/design_plus.css", $css_str );

        return 'success';
    }
}
?>
