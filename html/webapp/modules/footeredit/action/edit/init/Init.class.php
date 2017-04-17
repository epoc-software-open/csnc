<?php

/**
 * フッタ設定の保存
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_Action_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	// 使用コンポーネントを受け取るため
	var $footereditView = null;
	var $footereditAction = null;

	// レイアウト
	var $layout = null;

	// 編集モード
	var $edit_type = null;

	// 住所
	var $address = null;

	// 電話番号
	var $tel = null;

	// FAX番号
	var $fax = null;

	// メールアドレス
	var $mail = null;

	// リンクエリアHTML
	var $link_html = null;

	// 埋め込みHTML
	var $embed_html = null;

	// 表示する項目(ロゴ)
	var $chk_logo = null;

	// 表示する項目(住所)
	var $chk_address = null;

	// 表示する項目(電話番号)
	var $chk_tel = null;

	// 表示する項目(FAX番号)
	var $chk_fax = null;

	// 表示する項目(メールアドレス)
	var $chk_mail = null;

	// 表示する項目(リンクエリアHTML)
	var $chk_link = null;

	// 表示する項目(コピーライト)
	var $copyright = null;

	// 表示する項目(Powered by NetCommons2)
	var $chk_nc = null;

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{

		// フッター項目を保存する
		$this->footereditAction->setFooter( $this->layout, $this->edit_type, $this->address, $this->tel, $this->fax, $this->mail, $this->link_html, $this->copyright,
		                                    $this->embed_html,
		                                    $this->chk_logo, $this->chk_address, $this->chk_tel, $this->chk_fax,
		                                    $this->chk_mail, $this->chk_link, $this->chk_nc );

		return 'success';
	}
}
?>
