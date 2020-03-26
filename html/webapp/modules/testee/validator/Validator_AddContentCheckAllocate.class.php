<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 患者情報登録時割付情報チェック処理
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Validator_AddContentCheckAllocate extends Validator
{
	/**
	 * 患者情報登録時割付情報チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr	 エラー文字列
	 * @param array $params	 オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */

	function validate($attributes, $errStr, $params)
	{
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_AddContentCheckAllocate:Start" );
		
		// 必要コンポーネント取得
		$container = DIContainerFactory::getContainer();
		$mdbView   = $container->getComponent('mdbView');		// 当モジュールViewコンポーネント
		$request   = $container->getComponent("Request");		// リクエストコンポーネント
		
		// 入力データ
		$testee_id  = $request->getParameter('testee_id');		// 臨床試験支援DBのID
		$warning_ok = $request->getParameter('warning_ok');		// 警告OKフラグ 0:チェックする / 0以外:チェックしない
		
		
		// 警告OKフラグがONの場合はチェックしないので正常終了
		if( $warning_ok != 0 ) return;
		
		
		// 割付情報取得と確認
		$allocation = $mdbView->getAllocationContent( $testee_id );
		if( $allocation === false || count( $allocation ) <= 0 || $allocation[ 'allocation_flag' ] != 2 )
		{
			// 割付情報がない or 取得できない or 置換ブロック法ではない場合は割付情報はチェックしないので正常終了
			return;
		}
		
		
		// 割付群情報取得と確認
		$group_contents = $mdbView->getGroupContent( $testee_id );
		if( $group_contents === false || count( $group_contents ) <= 0 )
		{
			return "割付群情報が取得できない為、割付を行う事が出来ません。<br />このまま登録しますか？";
		}
		
		// 割付群が変更されているかどうかチェックする
		if( $mdbView->changeAllocateGroupContents( $group_contents ) === false )
		{
			return "割付群が変更後正しく再設定されていない為、割付を行う事が出来ません。<br />このまま登録しますか？";
		}
		
		
		// 割付因子の各項目取得
		$allocate_factors = $mdbView->getSelectedAllocationFactors( $testee_id );
		if( $allocate_factors === false || count( $allocate_factors ) <= 0 )
		{
			return "割付因子情報が取得できなかった為、割付を行う事が出来ません。<br />このまま登録しますか？";
		}
		
		
		// 割付層情報取得
		$allocation_conbinations = $mdbView->getAllocationcConbination( $testee_id );
		if( $allocation_conbinations === false || count( $allocation_conbinations ) <= 0 )
		{
			return "割付層情報が取得できなかった為、割付を行う事が出来ません。<br />このまま登録しますか？";
		}
		
		
		// 取得した割付因子項目を組み替え
		$factor_list = $mdbView->getAllocationItemList( $allocate_factors );
		
		// 現在の割付因子の項目で、組合せパタン作成
		$now_pattern = $mdbView->getConbinationPattern( $factor_list );
		
		// 現在の組み合わせパターンと、既存層のパターンを比較
		if( $mdbView->matchAllocationFactors( $now_pattern, $allocation_conbinations ) === false )
		{
			return "割付因子が変更後正しく再設定されていない為、割付を行う事ができません。<br />このまま登録しますか？";
		}
		
		
		// 割付群の比率の合計値取得
		$total_ratio = $mdbView->getTotalRatio( $group_contents );
		
		// 割付層の各ブロック数が正しいかどうかチェックする
		
		if( $mdbView->checkRatioBlockCount( $allocation_conbinations, $total_ratio ) === false )
		{
			return "割付層のブロック数に正しくない値が設定されている為、割付を行う事ができません。<br/>このまま登録しますか？";
		}
		
		
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_AddContentCheckAllocate:End" );
		return;
	}
	

}
?>