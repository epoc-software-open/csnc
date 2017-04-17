<?php

/**
 * 割付結果をJavaScript の画面に表示するためのValidator
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_Allocationview extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{

		// コンポーネント
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$mdbView =& $container->getComponent("mdbView");

		// 登録時にセッションに保持した値
		$insert_content_id =& $session->getParameter("testee_insert_content_id");
		$insert_testee_id =& $session->getParameter("testee_insert_testee_id");

		// メタデータ取得
		$params = array(
			"testee_id" => $insert_testee_id
		);
		$metadatas = $mdbView->getMetadatas($params);
		if(empty($metadatas)) {
			return $errStr;
		}

		// メタデータをリクエストにセット
		$request =& $container->getComponent("Request");
		$request->setParameter("metadatas", $metadatas);

		// 割付設定取得
		$result_allocation = $mdbView->getAllocationContent($insert_testee_id);
		if (empty($result_allocation)) {
			return "症例登録が完了しました。";
		}

		// 割付使用＆表示の場合のみ、割付結果を表示
		if ( $result_allocation["allocation_flag"] == 1 && $result_allocation["allocation_result_flag"] == 1 ) {
		}
		else {
			return "症例登録が完了しました。";
		}

		// 詳細データ取得
		$result = $mdbView->getMDBDetail($insert_content_id, $metadatas);
		if (empty($result)) {
			return "";
		}

		// 割付結果
		$group_name = $result["value"]["group_name"];

		if ( !empty( $group_name ) ) {
			return "症例登録が完了しました。\n割付結果は" . $group_name . "です。";
		}

		return "症例登録が完了しました。";
	}
}
?>
