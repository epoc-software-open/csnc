<?php

/**
 * フッターエディタインストール時アクション
 * HTML埋め込みの初期値(サンプルコード)を登録する
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_Action_Admin_Install extends Action
{
	// 使用コンポーネントを受け取るため
	var $footereditView = null;
	var $footereditAction = null;

	function execute()
	{

		// HTML埋め込みの初期値(サンプルコード)を登録する
		$layout = 1;
		$edit_type = 1;
		$address = "";
		$tel = "";
		$fax = "";
		$mail = "";
		$link_html = "";
		$copyright = "";
		$embed_html = '<div class="footer_add">
    <div class="footer_add_cont">
        <h3>
            サンプルサイト フッター
        </h3>
        <p>
            Sample site footer.
        </p>
        <p class="ja_copy">
            Copyright &copy; Sample site All right reserved.
        </p>
    </div>
</div>
';
		$chk_logo = 0;
		$chk_address = 0;
		$chk_tel = 0;
		$chk_fax = 0;
		$chk_mail = 0;
		$chk_link = 0;
		$chk_nc = 1;

		// フッター項目を保存する
		$result = $this->footereditAction->setFooter( $layout, $edit_type, $address, $tel, $fax, $mail, $link_html, $copyright,
		                                    $embed_html,
		                                    $chk_logo, $chk_address, $chk_tel, $chk_fax,
		                                    $chk_mail, $chk_link, $chk_nc );

		if( $result === false ) {
			return "false";
		}

		return "true";
	}
}
?>