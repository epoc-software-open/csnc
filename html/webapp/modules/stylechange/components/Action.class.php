<?php

/**
 * DB更新共通クラス
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Stylechange_Components_Action
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
    function Stylechange_Components_Action() 
    {
        $this->_container =& DIContainerFactory::getContainer();
        $this->_db =& $this->_container->getComponent("DbObject");
    }

    /**
     * スライダー設定
     *
     * @access    public
     */
    function setStyles( $new_globalmenu_type,  $new_globalmenu_color, 
                        $new_submenu_type,     $new_submenu_color,
                        $new_titleaccent_type, $new_titleaccent_color ) {

        // 現在のスタイル設定の取得
        $sql = "SELECT style_location, style_type, style_color "
            .  "FROM {stylechange} ";

        $style_result = $this->_db->execute( $sql );

        if ( $style_result === false ) {
            $this->_db->addError();
            return $style_result;
        }

        // 適用場所(モジュール)毎の配列に詰め直す
        $current_style = array();
        foreach ( $style_result as $style_rec ) {

            $current_style[$style_rec['style_location']] = $style_rec;
        }

        // スタイル設定の保存

        // グローバルメニューの存在チェック
        if ( $current_style['globalmenu'] ) {

            // --- 更新処理
            $params = array(
                "style_type"       => $new_globalmenu_type,
                "style_color"      => $new_globalmenu_color,
            );

            $where_params = array(
                "style_location"   => "globalmenu"
            );

            $result = $this->_db->updateExecute( "stylechange", $params, $where_params, true );
            if($result === false) {
                return "false";
            }
        }
        else {

            // --- 追加処理
            $params = array( 
                "style_location"   => "globalmenu",
                "style_type"       => $new_globalmenu_type,
                "style_color"      => $new_globalmenu_color,
            );

            $result = $this->_db->insertExecute( 'stylechange', $params, true );
        }

        // サブメニューの存在チェック
        if ( $current_style['submenu'] ) {

            // --- 更新処理
            $params = array(
                "style_type"       => $new_submenu_type,
                "style_color"      => $new_submenu_color,
            );

            $where_params = array(
                "style_location"   => "submenu"
            );

            $result = $this->_db->updateExecute( "stylechange", $params, $where_params, true );
            if($result === false) {
                return "false";
            }
        }
        else {

            // --- 追加処理
            $params = array( 
                "style_location"   => "submenu",
                "style_type"       => $new_submenu_type,
                "style_color"      => $new_submenu_color,
            );

            $result = $this->_db->insertExecute( 'stylechange', $params, true );
        }

        // タイトルアクセントの存在チェック
        if ( $current_style['titleaccent'] ) {

            // --- 更新処理
            $params = array(
                "style_type"       => $new_titleaccent_type,
                "style_color"      => $new_titleaccent_color,
            );

            $where_params = array(
                "style_location"   => "titleaccent"
            );

            $result = $this->_db->updateExecute( "stylechange", $params, $where_params, true );
            if($result === false) {
                return "false";
            }
        }
        else {

            // --- 追加処理
            $params = array( 
                "style_location"   => "titleaccent",
                "style_type"       => $new_titleaccent_type,
                "style_color"      => $new_titleaccent_color,
            );

            $result = $this->_db->insertExecute( 'stylechange', $params, true );
        }

    }

    /**
     * 上書き CSS設定
     *
     * @access    public
     */
    function setCsstext( $csstext ) {

        // 現在の上書き CSS テキストの取得
        $sql = "SELECT * "
            .  "FROM {stylechange_override} ";

        $css_result = $this->_db->execute( $sql );

        if ( $css_result === false ) {
            $this->_db->addError();
            return $css_result;
        }

        // 上書き CSS テキストの保存

        // 存在チェック
        if ( $css_result ) {

            // --- 更新処理
            $params = array( "override_css" => $csstext );
            $where_params = array();

            $result = $this->_db->updateExecute( "stylechange_override", $params, $where_params, true );
            if($result === false) {
                return "false";
            }
        }
        else {

            // --- 追加処理
            $params = array( "override_css" => $csstext );

            $result = $this->_db->insertExecute( 'stylechange_override', $params, true );
        }
    }
}
?>
