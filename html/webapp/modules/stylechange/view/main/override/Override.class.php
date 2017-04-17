<?php

/**
 * スタイルチェンジャー(上書き用CSS 表示)
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Stylechange_View_Main_Override extends Action
{

    // 使用コンポーネントを受け取るため
    var $stylechangeView = null;

    // --- 画面に渡す値

    // 上書き用のCSS
    var $css_text = null;

    /**
     * 現在のスタイル、選択可能なスタイルの表示
     *
     * @access  public
     */
    function execute()
    {

        // 選択可能なスタイルの種類
        $this->css_text = $this->stylechangeView->getOverrideCSS();

        return 'success';
    }
}
?>
