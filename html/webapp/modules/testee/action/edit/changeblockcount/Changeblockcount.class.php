<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付群比率と割付層ブロック数の更新処理
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Action_Edit_Changeblockcount extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	
	var $ratio_list       = null;		// 比率のリスト
	var $block_count_list = null;		// 割付層のブロック数のリスト

// 18.08.01 add start by makino@opensource-workshop.jp
	var $exclude_count_list  = null;		// 除外連続数のリスト
	var $variable_block_flag = null;		// 可変ブロック使用フラグ
	var $variable_block      = null;		// 基準症例数
// 18.08.01 add end by makino@opensource-workshop.jp


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
//test_log(date('Y/m/d H:i:s') . ":Testee_Action_Edit_Changeblockcount:Start");
//test_log("exclude_count_list");
//test_log($this->exclude_count_list);
//test_log("variable_block_flag");
//test_log($this->variable_block_flag);
//test_log("variable_block");
//test_log($this->variable_block);
		// 割付群の更新
		foreach( $this->ratio_list as $key => $value )
		{
			$result = $this->mdbAction->updateGroupRatioBlock( $key, $value );
			if( $result === false ) return 'error';
		}
		
		
// 18.10.05 add start by makino@opensource-workshop.jp
		// シード値を取得して初期値として設定
		$seed = $this->mdbView->getAllocationSeed();
		mt_srand( $seed );
		
		// シードを利用したかどうかのフラグ
		$use_seed = false;
// 18.10.05 add end by makino@opensource-workshop.jp
		
		
		// 割付層ブロック数の更新
// 18.08.01 change start by makino@opensource-workshop.jp
//		foreach( $this->block_count_list as $key => $value )
//		{
//			$result = $this->mdbAction->updateAllocationConbination( $key, $value );
//			if( $result === false ) return 'error';
//		}
		foreach( $this->block_count_list as $conbination_id => $block_count )
		{
			$block_count_list = explode( ",", $block_count );
			
			if( count( $block_count_list ) <= 1 )
			{
				// ブロック数が単一定義の場合、現在ブロック数には次回ブロック数をそのまま設定する
				$now_block_count = $block_count;
			}
			else
			{
				// ブロック数が複数指定されている場合
				
				// 現在ブロック数に指定するブロック数のindexをランダムで取得
				$index = $this->mdbView->randBlockCountIndex( $block_count );
				
				// フラグをON		18.10.05 add start by makino@opensource-workshop.jp
				$use_seed = true;
				
				// 現在ブロック数に、ランダムで決められたブロック数を指定する
				$now_block_count = $block_count_list[ $index ];
			}
			
			
			$result = $this->mdbAction->updateAllocationConbination( $conbination_id, $block_count, $this->exclude_count_list[$conbination_id], $now_block_count );
			if( $result === false ) return 'error';
		}
		
		
// 18.10.05 add start by makino@opensource-workshop.jp
		// 割付シードを利用した場合はシード値を登録する
		if( $use_seed === true )
		{
			// 現在の症例数取得
			$content_count = $this->mdbView->getContentCount( $this->testee_id );
			
			// シード値登録
			$result = $this->mdbAction->insertAllocationSeed( $this->testee_id, 0, $seed, 0, $content_count );
		}
// 18.10.05 add end by makino@opensource-workshop.jp
		
		
		// 可変ブロックテーブルへの更新処理
		$allocation_variable_block = $this->mdbView->getAllocateVariableBlock( $this->testee_id );
//test_log("allocation_variable_block");
//test_log($allocation_variable_block);
		if( $allocation_variable_block === false )
		{
			test_log("Testee_Action_Edit_Changeblockcount:可変ブロックテーブル情報取得失敗");
			return 'error';
		}
		
		if( count( $allocation_variable_block ) <= 0 )
		{
			// 可変ブロック情報なし
			if( empty( $this->variable_block_flag ) === true )
			{
				// 可変ブロック不使用でデータなしの場合は何もしない
			}
			else
			{
				// 可変ブロック使用で、データなしなので新しくInsertする
				$result = $this->mdbAction->insertAllocationVariableBlock( $this->testee_id, trim( $this->variable_block ) );
				if( $result === false )
				{
					test_log("Testee_Action_Edit_Changeblockcount:可変ブロックテーブル登録失敗");
					return 'error';
				}
			}
		}
		else
		{
			// 可変ブロック情報あり
			if( empty( $this->variable_block_flag ) === true )
			{
				// 可変ブロック不使用の場合は登録済みデータを削除
				$result = $this->mdbAction->deleteAllocationVariableBlock( $allocation_variable_block["allocation_variable_block_id"] );
			}
			else
			{
				// 可変ブロック使用の場合はUPDATE
				$result = $this->mdbAction->updateAllocationVariableBlock( $allocation_variable_block["allocation_variable_block_id"], trim( $this->variable_block ) );
			}
		}
// 18.08.01 change start by makino@opensource-workshop.jp
		
//test_log(date('Y/m/d H:i:s') . ":Testee_Action_Edit_Changeblockcount:End");
		return 'success';
	}
	
	
}
?>
