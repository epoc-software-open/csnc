<?php

/**
 * スタイルチェンジャー
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Stylechange_View_Main_Init extends Action
{

    // 使用コンポーネントを受け取るため
    var $stylechangeView = null;

    // --- 画面から受け取る＆渡す値

    // 新しく設定するセット
    var $new_set_style = null;

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


    // --- 画面に渡す値

    // グローバルメニューのスタイル種類(型)のディレクトリ
    var $globalmenu_type_dir_list = null;

    // グローバルメニューの色のディレクトリ
    var $globalmenu_color_dir_list = null;

    // サブメニューのスタイル種類(型)のディレクトリ
    var $submenu_type_dir_list = null;

    // サブメニューの色のディレクトリ
    var $submenu_color_dir_list = null;

    // タイトルアクセントのスタイル種類(型)のディレクトリ
    var $titleaccent_type_dir_list = null;

    // タイトルアクセントの色のディレクトリ
    var $titleaccent_color_dir_list = null;

    // 現在設定されているスタイル情報(DB より)
    var $current_style = null;

    // セットのスタイル種類
    var $select_list = null;

    /**
     * 現在のスタイル、選択可能なスタイルの表示
     *
     * @access  public
     */
    function execute()
    {

        // 選択可能なスタイルの種類
        $this->select_list = explode( ',', STYLECHANGE_SELECT_LIST );

        // -----------------------------------------------------------
        // - セットが選択された場合
        // -----------------------------------------------------------
        if ( $this->new_set_style ) {

            $ret_styles = $this->stylechangeView->getStyleDefineValue( $this->new_set_style );

            if ( $ret_styles ) {
                $this->new_globalmenu_type   = $ret_styles['globalmenu_type'];
                $this->new_globalmenu_color  = $ret_styles['globalmenu_color'];
                $this->new_submenu_type      = $ret_styles['submenu_type'];
                $this->new_submenu_color     = $ret_styles['submenu_color'];
                $this->new_titleaccent_type  = $ret_styles['titleaccent_type'];
                $this->new_titleaccent_color = $ret_styles['titleaccent_color'];
            }
        }

        // -----------------------------------------------------------
        // - 現在設定されているスタイルを取得する
        // -----------------------------------------------------------
        $this->current_style = $this->stylechangeView->getCurrentStyle();

        // -----------------------------------------------------------
        // - 画面で選択されていない場合は、初期値としてDB の内容を設定する
        // -----------------------------------------------------------
        if ( empty( $this->new_globalmenu_type ) ) {
            $this->new_globalmenu_type = $this->current_style['globalmenu']['style_type'];
        }
        if ( empty( $this->new_globalmenu_color ) ) {
            $this->new_globalmenu_color = $this->current_style['globalmenu']['style_color'];
        }
        if ( empty( $this->new_submenu_type ) ) {
            $this->new_submenu_type = $this->current_style['submenu']['style_type'];
        }
        if ( empty( $this->new_submenu_color ) ) {
            $this->new_submenu_color = $this->current_style['submenu']['style_color'];
        }
        if ( empty( $this->new_titleaccent_type ) ) {
            $this->new_titleaccent_type = $this->current_style['titleaccent']['style_type'];
        }
        if ( empty( $this->new_titleaccent_color ) ) {
            $this->new_titleaccent_color = $this->current_style['titleaccent']['style_color'];
        }

        // -----------------------------------------------------------
        // - グローバルメニューの型
        // -----------------------------------------------------------

        // グローバルメニューの型のベースディレクトリ
        $globalmenu_type_base_dir = HTDOCS_DIR . '/design_plus/css/globalmenu';

        // グローバルメニューの型のリストの取得
        $this->globalmenu_type_dir_list = $this->getDirList( $globalmenu_type_base_dir );

        // 現在のグローバルメニューの型が選択されていない場合は、最初の値を初期値とする。
        if ( !$this->new_globalmenu_type ) {
            $this->new_globalmenu_type = $this->globalmenu_type_dir_list[0];
        }

        // -----------------------------------------------------------
        // - グローバルメニューの色
        // -----------------------------------------------------------

        // グローバルメニューの色のベースディレクトリ
        $globalmenu_color_base_dir = HTDOCS_DIR . '/design_plus/css/globalmenu/' . $this->new_globalmenu_type;

        // グローバルメニューの色のリストの取得
        $this->globalmenu_color_dir_list = $this->getDirList( $globalmenu_color_base_dir );

        // 現在のグローバルメニューの色が選択されていない場合は、最初の値を初期値とする。
        if ( !$this->new_globalmenu_color ) {
            $this->new_globalmenu_color = $this->globalmenu_color_dir_list[0];
        }

        // -----------------------------------------------------------
        // - サブメニューの型
        // -----------------------------------------------------------

        // サブメニューの型のベースディレクトリ
        $submenu_type_base_dir = HTDOCS_DIR . '/design_plus/css/submenu';

        // サブメニューの型のリストの取得
        $this->submenu_type_dir_list = $this->getDirList( $submenu_type_base_dir );

        // 現在のサブメニューの型が選択されていない場合は、最初の値を初期値とする。
        if ( !$this->new_submenu_type ) {
            $this->new_submenu_type = $this->submenu_type_dir_list[0];
        }

        // -----------------------------------------------------------
        // - サブメニューの色
        // -----------------------------------------------------------

        // サブメニューの色のベースディレクトリ
        $submenu_color_base_dir = HTDOCS_DIR . '/design_plus/css/submenu/' . $this->new_submenu_type;

        // サブメニューの色のリストの取得
        $this->submenu_color_dir_list = $this->getDirList( $submenu_color_base_dir );

        // 現在のサブメニューの色が選択されていない場合は、最初の値を初期値とする。
        if ( !$this->new_submenu_color ) {
            $this->new_submenu_color = $this->submenu_color_dir_list[0];
        }

        // -----------------------------------------------------------
        // - タイトルアクセントの型
        // -----------------------------------------------------------

        // タイトルアクセントの型のベースディレクトリ
        $titleaccent_type_base_dir = HTDOCS_DIR . '/design_plus/css/titleaccent';

        // タイトルアクセントの型のリストの取得
        $this->titleaccent_type_dir_list = $this->getDirList( $titleaccent_type_base_dir );

        // 現在のタイトルアクセントの型が選択されていない場合は、最初の値を初期値とする。
        if ( !$this->new_titleaccent_type ) {
            $this->new_titleaccent_type = $this->titleaccent_type_dir_list[0];
        }

        // -----------------------------------------------------------
        // - タイトルアクセントの色
        // -----------------------------------------------------------

        // タイトルアクセントの色のベースディレクトリ
        $titleaccent_color_base_dir = HTDOCS_DIR . '/design_plus/css/titleaccent/' . $this->new_titleaccent_type;

        // タイトルアクセントの色のリストの取得
        $this->titleaccent_color_dir_list = $this->getDirList( $titleaccent_color_base_dir );

        // 現在のタイトルアクセントの色が選択されていない場合は、最初の値を初期値とする。
        if ( !$this->new_titleaccent_color ) {
            $this->new_titleaccent_color = $this->titleaccent_color_dir_list[0];
        }


        return 'success';
    }

    /**
     * 特定のディレクトリの下位ディレクトリの取得
     *
     * @access  public
     */
    function getDirList( $base_dir )
    {

        // 特定のディレクトリの下位ディレクトリを読み込む
        $dir_list = array();
        $dir_handle = opendir( $base_dir . "/" );
        while ( $entry = readdir( $dir_handle ) ) {

            if ( is_dir( $base_dir . "/" . $entry ) && $entry != "." && $entry != ".." ) {
                $dir_list[] = $entry;
            }
        }
        closedir( $dir_handle );

        // ディレクトリ名のソート
        sort( $dir_list );

        // ディレクトリリストの返却
        return $dir_list;
    }
}
?>
