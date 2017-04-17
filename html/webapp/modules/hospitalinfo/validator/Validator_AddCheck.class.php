<?php

/**
 *  登録チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Hospitalinfo_Validator_AddCheck extends Validator
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
		$hospital_name = $request->getParameter("hospital_name");
		$hospital_kana = $request->getParameter("hospital_kana");
		$hospital_code = $request->getParameter("hospital_code");

		// 施設名の重複チェック
		$hospitalinfo = $hospitalinfoView->getHospitalinfoFromName( trim( $hospital_name, '　' ) );
		if ( !empty( $hospitalinfo ) ) {
			return "エラー！\n指定された施設名は存在します。\n\nコード[" . $hospitalinfo[0]["hospital_code"] . "]";
		}

		// かなの重複チェック
		$hospitalinfo = $hospitalinfoView->getHospitalinfoFromKana( trim( $hospital_kana, '　' ) );
		if ( !empty( $hospitalinfo ) ) {
			return "エラー！\n指定された施設名（かな）は存在します。\n\nコード[" . $hospitalinfo[0]["hospital_code"] . "]";
		}

		// コードの重複チェック
		$hospitalinfo = $hospitalinfoView->getHospitalinfoFromCode( trim( $hospital_code, '　' ) );
		if ( !empty( $hospitalinfo ) ) {
			return "エラー！\n指定されたコードは存在します。\n\n施設名[" . $hospitalinfo[0]["hospital_name"] . "]";
		}

		return;
	}
}
?>