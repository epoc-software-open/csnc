<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付層作成時チェック処理
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Validator_CreateConbination extends Validator
{
	/**
	 * 割付層作成時チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr	 エラー文字列
	 * @param array $params	 オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */

	function validate($attributes, $errStr, $params)
	{
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_CreateConbination:Start" );
		
		// 必要コンポーネント取得
		$container = DIContainerFactory::getContainer();
		$mdbView   = $container->getComponent("mdbView");	// 当モジュールViewコンポーネント
		
		// 臨床試験支援DB ID
		$testee_id = $attributes;
		
		// 割付情報取得と確認
		$allocation = $mdbView->getAllocationContent( $testee_id );
		if( $allocation === false || count( $allocation ) <= 0 )
		{
			return "割付情報が取得できない為、正しく処理できませんでした。";
		}
		else if( $allocation[ 'allocation_flag' ] != 2 )
		{
			return "置換ブロック法ではない為、正しく処理できませんでした。";
		}
		
		// 割付群情報取得と確認
		$group_contents = $mdbView->getGroupContent( $testee_id );
		if( $group_contents === false )
		{
			return "割付群情報が取得できなかった為、正しく処理できませんでした。";
		}
		else if( count( $group_contents ) <= 0 )
		{
			return "割付群が登録されていない為、割付比率と割付層の作成はできません。";
		}
		
		// 割付因子の各項目取得
		$allocate_factors = $mdbView->getSelectedAllocationFactors( $testee_id );
		if( $allocate_factors === false )
		{
			return "割付因子情報が取得できなかった為、正しく処理できませんでした。";
		}
		else if( count( $allocate_factors ) <= 0 )
		{
			return "正しい割付因子が登録されていない為、割付比率の割付層の作成はできません。";
		}
		
		
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_CreateConbination:End" );
		return;
	}
}
?>