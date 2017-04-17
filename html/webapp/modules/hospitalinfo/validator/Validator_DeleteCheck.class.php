<?php

/**
 *  削除チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Hospitalinfo_Validator_DeleteCheck extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 *                  
	 * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		// container取得
		$container = DIContainerFactory::getContainer();
		$hospitalinfoView = $container->getComponent("hospitalinfoView");

		// 画面内容の取得
		$request = $container->getComponent("Request");
		$hospitalinfo_id = $request->getParameter("hospitalinfo_id");

		// 施設名の重複チェック
		$hospitalinfo = $hospitalinfoView->getHospitalUser( $hospitalinfo_id );
		if ( !empty( $hospitalinfo ) ) {
			return "エラー！\n\n削除しようとしている施設のユーザが存在するため、削除できません。";
		}

		return;
	}
}
?>