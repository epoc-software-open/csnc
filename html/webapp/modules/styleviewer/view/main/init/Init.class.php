<?php

/**
 * 選択したスタイルでの表示
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Styleviewer_View_Main_Init extends Action
{

    // セッションオブジェクト
    var $session = null;

    // モジュール共通コンポーネント
    var $styleviewerView = null;

    // スタイル種類のディレクトリ
    var $dir_list = null;

    // 現在のスタイル設定
    var $current_style = null;

    // 選択されたスタイル
    var $new_style = null;

    /**
     * メイン処理
     *
     * @access  public
     */
    function execute()
    {

        // 選択可能なスタイルの種類
        $this->dir_list = explode( ',', STYLEVIEWER_SELECT_LIST );

        // -----------------------------------------------------------
        // - 現在設定されているスタイルを取得する
        // -----------------------------------------------------------
        $this->current_style = $this->styleviewerView->getCurrentStyle();
        //print_r($this->current_style);


//        // スタイルシートのベースディレクトリ
//        $css_base_dir = HTDOCS_DIR . '/design_plus/css';
//
//        // 現在のスタイルシートの設定読み込み
//        $handle = @fopen( $css_base_dir . "/design_plus.css", "r" );
//
//        // スタイルシートの1行目に現在のスタイル設定が書かれている
//        if ( $handle ) {
//            $css_line = fgets( $handle );
//            fclose( $handle );
//        }
//
//        // スタイル名称の部分のみに編集
//        $this->current_style = str_replace( '/*', '', $css_line );
//        $this->current_style = str_replace( '*/', '', $this->current_style );
//        $this->current_style = trim( $this->current_style );
//
//        // スタイル種類を読み込む
//        $this->dir_list = array();
//        $dir_handle = opendir( $css_base_dir . "/" );
//        while ( $entry = readdir( $dir_handle ) ) {
//
//            if ( is_dir( $css_base_dir . "/" . $entry ) && $entry != "." && $entry != ".." ) {
//                $this->dir_list[] = $entry;
//            }
//        }
//        closedir( $dir_handle );
//
//        // ディレクトリ名のソート
//        sort( $this->dir_list );

        return 'success';
    }
}
?>
