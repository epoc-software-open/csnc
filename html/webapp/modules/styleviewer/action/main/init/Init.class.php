<?php

/**
 * スタイルの選択、セッションへの保持
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Styleviewer_Action_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $page_id = null;
    var $room_id = null;

    // モジュール共通コンポーネント
    var $styleviewerView = null;

    // セッションオブジェクト
    var $session = null;

    // 新しいスタイル
    var $new_style = null;

	var $style_size = null;
	var $style_color = null;

    /**
     * メイン処理
     *
     * @access  public
     */
    function execute()
    {

        // 空の場合はセッションから削除
        if ( empty( $this->new_style ) ) {

            // セッションから新しいCSS 名を削除
            $this->session->removeParameter( "_new_style" );
        }
        // 選択されたら、セッションにスタイル名を保持
        else {

            $ret_styles = $this->styleviewerView->getStyleDefineValue( $this->new_style );
            if ( $ret_styles ) {
                $new_globalmenu_type   = $ret_styles['globalmenu_type'];
                $new_globalmenu_color  = $ret_styles['globalmenu_color'];
                $new_submenu_type      = $ret_styles['submenu_type'];
                $new_submenu_color     = $ret_styles['submenu_color'];
                $new_titleaccent_type  = $ret_styles['titleaccent_type'];
                $new_titleaccent_color = $ret_styles['titleaccent_color'];

                // CSS 文字列
                $css_str  = '';
                $css_str .= '<link href="' . '/design_plus/css/globalmenu/' . $new_globalmenu_type . '/' . $new_globalmenu_color . '/' .
                        'globalmenu_' . $new_globalmenu_type . '_' . $new_globalmenu_color . '.css" rel="stylesheet" type="text/css" />' . "\n";

                $css_str .= '<link href="' . '/design_plus/css/submenu/' . $new_submenu_type . '/' . $new_submenu_color . '/' .
                            'submenu_' . $new_submenu_type . '_' . $new_submenu_color . '.css" rel="stylesheet" type="text/css" />' . "\n";

                $css_str .= '<link href="' . '/design_plus/css/titleaccent/' . $new_titleaccent_type . '/' . $new_titleaccent_color . '/' .
                            'titleaccent_' . $new_titleaccent_type . '_' . $new_titleaccent_color . '.css" rel="stylesheet" type="text/css" />' . "\n";

                $css_str .= '<link href="' . '/design_plus/css/slide/default/white/slide_default_white.css" rel="stylesheet" type="text/css" />' . "\n";
                $css_str .= '<link href="' . '/design_plus/css/override/override.css" rel="stylesheet" type="text/css" />' . "\n";
                $css_str .= '<link href="' . '/design_plus/css/user/user_override.css" rel="stylesheet" type="text/css" />' . "\n";

                //$css_str  = '';
                //$css_str .= '<link href="' . BASE_URL . '/design_plus/css/globalmenu/' . $new_globalmenu_type . '/' . $new_globalmenu_color . '/' .
                //        'globalmenu_' . $new_globalmenu_type . '_' . $new_globalmenu_color . '.css" rel="stylesheet" type="text/css" />' . "\n";

                //$css_str .= '<link href="' . BASE_URL . '/design_plus/css/submenu/' . $new_submenu_type . '/' . $new_submenu_color . '/' .
                //            'submenu_' . $new_submenu_type . '_' . $new_submenu_color . '.css" rel="stylesheet" type="text/css" />' . "\n";

                //$css_str .= '<link href="' . BASE_URL . '/design_plus/css/titleaccent/' . $new_titleaccent_type . '/' . $new_titleaccent_color . '/' .
                //            'titleaccent_' . $new_titleaccent_type . '_' . $new_titleaccent_color . '.css" rel="stylesheet" type="text/css" />' . "\n";

                //$css_str .= '<link href="' . BASE_URL . '/design_plus/css/slide/default/white/slide_default_white.css" rel="stylesheet" type="text/css" />' . "\n";
                //$css_str .= '<link href="' . BASE_URL . '/design_plus/css/override/override.css" rel="stylesheet" type="text/css" />' . "\n";
                //$css_str .= '<link href="' . BASE_URL . '/design_plus/css/user/user_override.css" rel="stylesheet" type="text/css" />' . "\n";

                // セッションに新しいCSS 定義を保持
                $this->session->setParameter( "_new_style_value", $css_str );

                // セッションに新しいCSS 名を保持
                $this->session->setParameter( "_new_style", $this->new_style );
            }
        }

		// 文字の大きさ

		// 空の場合はセッションから削除
		if ( empty( $this->style_size ) ) {

			// セッションから新しいCSS 名を削除
			$this->session->removeParameter( "style_size" );
			$this->session->removeParameter( "_style_size" );
		}
		// 選択されたら、セッションにスタイル名を保持
		else {

			$this->session->setParameter( "style_size", $this->style_size );
			$this->session->setParameter( "_style_size", '<link href="' . '/design_plus/css/_style_size/style_size_' . $this->style_size . '.css" rel="stylesheet" type="text/css" />' );
		}

		// 表示色

		// 空の場合はセッションから削除
		if ( empty( $this->style_color ) ) {

			// セッションから新しいCSS 名を削除
			$this->session->removeParameter( "style_color" );
			$this->session->removeParameter( "_style_color" );
		}
		// 選択されたら、セッションにスタイル名を保持
		else {

			$this->session->setParameter( "style_color", $this->style_color );
			$this->session->setParameter( "_style_color", '<link href="' . '/design_plus/css/_style_color/style_color_' . $this->style_color . '.css" rel="stylesheet" type="text/css" />' );
		}

        return 'success';
    }
}
?>
