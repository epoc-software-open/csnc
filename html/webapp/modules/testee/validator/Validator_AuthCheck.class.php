<?php

/**
 * コンテンツ権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_AuthCheck extends Validator
{
	/**
	 * コンテンツ権限チェックバリデータ
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{

		// コンテナ取得
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$mdbView =& $container->getComponent("mdbView");

		// content_id (変更時に入ってくる)
		$content_id = $request->getparameter("content_id");

		// アクションチェック
		$action = $request->getParameter("action");

		// 削除
		if ( $action == "testee_action_main_delcontent" ) {
			// OK
		}
		// 変更
		else if ( $action == "testee_action_main_addcontent" ) {
			// OK
		}
		else {
			return "プログラムの想定外処理がありました。\nシステム管理者に連絡してください。";
		}

		// 削除処理
		if ( $action == "testee_action_main_delcontent" ) {
			if ( $mdbView->_hasDeleteAuthority(null) === false ) {
				return "削除を行う権限がありません。";
			}
		}
		// 変更処理
		else {
			if ( !empty( $content_id ) && $mdbView->_hasEditAuthority(null) === false ) {
				return "変更を行う権限がありません。";
			}
		}

		return;
	}
}
?>