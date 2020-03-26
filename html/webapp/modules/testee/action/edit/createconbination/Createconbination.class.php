<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付層作成処理
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Action_Edit_Createconbination extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;

	// コンポーネントを受け取るため
	var $mdbAction = null;
	var $mdbView   = null;
	
	/**
	 * 割付設定を使用するか
	 *
	 * @access  public
	 */
	function execute()
	{
//test_log( date('Y/m/d H:i:s') . ":Testee_Action_Edit_Createconbination" );
		// ----- 作成前チェック --------------------------------------------------------------------
		
		// 割付群を取得
		$group_contents = $this->mdbView->getGroupContent( $this->testee_id );
//test_log("group_contents");
//test_log($group_contents);
		if( $group_contents === false || count( $group_contents ) <= 0 ) return 'error';
		
		// 割付因子の各項目取得
		$allocate_factors = $this->mdbView->getSelectedAllocationFactors( $this->testee_id );
//test_log("allocate_factors");
//test_log($allocate_factors);
		if( $allocate_factors === false || count( $allocate_factors ) <= 0 ) return 'error';
		
		// 今登録されている割付層があれば取得する
		$now_conbination_list = $this->mdbView->getAllocationcConbination( $this->testee_id );
//test_log("now_conbination_list");
//test_log($now_conbination_list);
		if( $now_conbination_list === false ) return 'error';
		
		
		// ----- 割付群のUpdate --------------------------------------------------------------------
		
		// 割付群に比率の初期値をUpdateする
		if( $this->updateGroupRatioBlock( $this->testee_id, $group_contents ) === false ) return 'error';
		
		
		// ----- 割付層の作成 ----------------------------------------------------------------------
		// 比率の合計値取得
		$total_ratio = $this->getTotalRatio( $group_contents );
//test_log("total_ratio[" . $total_ratio . "]");
		
		// 選択済み割付因子リストの情報を組み替え
		$factor_list = $this->mdbView->getAllocationItemList( $allocate_factors );
//test_log("factor_list");
//test_log($factor_list);
		
		// 現在の割付因子で層組合せパターンを作成する
		$pattern_list = $this->mdbView->getConbinationPattern( $factor_list );
//test_log("pattern_list");
//test_log($pattern_list);
		
		if( count( $now_conbination_list ) <= 0 )
		{
			// ----- 割付層のデータがまだない場合 ------------------------------
			
			// 割付層INSERT処理
			$result = $this->insertAllocationConbinations( $this->testee_id, $pattern_list, $total_ratio );
			if( $result === false ) return 'error';
		}
		else
		{
			// 割付層のデータが既にある場合
			
			// 作成したパターンと層を比較
			if( $this->mdbView->matchAllocationFactors( $pattern_list, $now_conbination_list ) === false )
			{
				// 一致していないので、DELTE/INSERT
				
				// 既存割付層データ削除
				$result = $this->mdbAction->deleteAllocationConbination( $this->testee_id );
				if( $result === false ) return 'error';
				
				// 新しい割付因子パターンにてINSERT
				$result = $this->insertAllocationConbinations( $this->testee_id, $pattern_list, $total_ratio );
				if( $result === false ) return 'error';
			}
			
			// 一致している場合は何もしない
			
		}
		
		
		return 'success';
	}
	
	
	// 割付群の比率をUpdateする ※既に比率が設定されている場合はUpdateしない
	private function updateGroupRatioBlock( $testee_id, $group_contents )
	{
		foreach( $group_contents as $value )
		{
			// 比率が空 or 0の場合のみUpdate
			if( empty( $value[ 'ratio_block' ] ) === true )
			{
				$result = $this->mdbAction->updateGroupRatioBlock( $value[ 'allocation_group_id' ], "1" );
				if( $result === false ) return false;
			}
		}
		
		return true;
	}
	
	// 比率の合計値取得
	private function getTotalRatio( $group_contents )
	{
		$result = 0;
		foreach( $group_contents as $value )
		{
			$result += ( empty( $value[ 'ratio_block' ] ) ? 1 : intval( $value[ 'ratio_block' ] ) );
		}
		
		return $result;
	}
	
	// 割付層INSERT処理
	private function insertAllocationConbinations( $testee_id, $pattern_list, $total_ratio )
	{
		foreach( $pattern_list as $value )
		{
			// パタン文字列作成
			$pattern_string = implode( '|', $value );
			
			// insert実施
// 18.08.01 change start by makino@opensource-workshop.jp
//			$result = $this->mdbAction->insertAllocationConbination( $testee_id, $pattern_string, $total_ratio );
			$result = $this->mdbAction->insertAllocationConbination( $testee_id, $pattern_string, $total_ratio, $total_ratio );
// 18.08.01 change end by makino@opensource-workshop.jp
			if( $result === false ) return false;
		}
		
		return true;
	}
}
?>
