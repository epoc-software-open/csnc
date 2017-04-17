<?php

/**
 * DB取得共通クラス
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Styleviewer_Components_View
{

    /**
     * @var DBオブジェクトを保持
     *
     * @access    private
     */
    var $_db = null;

    /**
     * @var DIコンテナを保持
     *
     * @access    private
     */
    var $_container = null;

    /**
     * コンストラクター
     *
     * @access    public
     */
    function Styleviewer_Components_View() {
        $this->_container =& DIContainerFactory::getContainer();
        $this->_db =& $this->_container->getComponent("DbObject");
    }

    /**
     * セット用のスタイル定義定数を要素ごとの配列にする
     *
     * @access    public
     */
    function getStyleDefineValue( $set_name ) {

        // 定数名の割り出し
        $defile_file_name = 'STYLEVIEWER_' . strtoupper( $set_name );

        // 戻り値(キーと値のペア)
        $ret_styles = array();

        // 定数がある場合
        if ( defined( $defile_file_name ) ) {

            $style_kinds = explode( '|', constant( $defile_file_name ) );
            // print_r($style_kinds);

            foreach ( $style_kinds as $style_kind ) {
                $style_item = explode( ',', $style_kind );
                $ret_styles[$style_item[0]] = $style_item[1];
            }
            // print_r( $ret_styles );
        }
        return $ret_styles;
    }

    /**
     * 現在のスタイル設定を取得する。
     *
     * @access    public
     */
    function getCurrentStyle() {

        $sql = "SELECT style_location, style_type, style_color "
            .  "FROM {stylechange} ";

        $current_style = $this->_db->execute( $sql );
        //print_r( $sql );

        if ( $current_style === false ) {
            $this->_db->addError();
            return $current_style;
        }

        // 適用場所(モジュール)毎の配列に詰め直す
        $ret_array = array();
        foreach ( $current_style as $current_item ) {

            $ret_array[$current_item['style_location']] = $current_item;
        }

        return $ret_array;
    }

}
?>
