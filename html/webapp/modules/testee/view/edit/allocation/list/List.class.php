<?php

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Allocation_List extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $room_id                      = null;	// ルームID 
	var $change_block_allocation_flag = null;	// 置換ブロック法 割付群/割付因子変更フラグ

	// コンポーネントを受け取るため
	var $mdbView = null;

	// 値をセットするため
	var $allocation = null;
	var $adjust_metadata_item = null;
	var $selected_allocation = null;
	var $group_content = null;
	var $testee_name = null;		// 臨床試験支援DB名
	
	var $setting_blockcount_flag = null;		// 置換ブロック法 ブロック数設定フラグ null or 0:非表示 / 1:表示
	var $block_warning_message   = null;		// 置換ブロック法 警告メッセージ
	var $conbination_header      = null;		// 置換ブロック法 割付因子層ヘッダ
	var $conbination_list        = null;		// 置換ブロック法 割付因子層リスト
	
	var $variable_block         = null;			// 置換ブロック法 可変ブロック情報
	var $variable_block_flag    = null;			// 置換ブロック法 可変ブロック使用フラグ null or 0 :使用しない / 1:使用する


	// 以下、クラス内変数
	private $allocation_conbination = null;		// 置換ブロック法 割付層情報
	private $factor_list            = null;		// 置換ブロック法 現在の割付因子の項目リスト
	
	/**
	 * @access  public
	 */
	function execute()
	{
		$this->allocation = $this->mdbView->getAllocationContent( $this->testee_id );
		$this->adjust_metadata_item = $this->mdbView->getAllocationItem( $this->testee_id );
		$this->selected_allocation = $this->mdbView->getSelectedAllocationItem( $this->testee_id );
		$this->group_content = $this->mdbView->getGroupContent( $this->testee_id );
		$this->setting_history = $this->mdbView->getSettinghistory( $this->testee_id );
		
		
		// 臨床試験支援DB名取得
		$this->testee_name = $this->getTesteeName( $this->testee_id );
		
		
		
		// +++++ 置換ブロック法用設定処理 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		// 置換ブロック法かどうかチェック
		if( $this->checkAllocationBlock() === false ) return 'success';
		
		// 割付層情報取得
		$this->allocation_conbination = $this->mdbView->getAllocationcConbination( $this->testee_id );
		if( $this->allocation_conbination === false || count( $this->allocation_conbination ) <= 0 ) return 'success';
		
		// 割付群の存在チェック
		if( $this->group_content === false || count( $this->group_content ) <= 0 ) return 'success';
		
		// 割付群が変更になっているかどうかチェックする
		if( $this->mdbView->changeAllocateGroupContents( $this->group_content ) === false ) return 'success';
		
		// 現在の割付因子の項目リスト取得
		$this->factor_list = $this->getFactorList();
		if( empty( $this->factor_list ) === true ) return 'success';
		
		// 割付因子が変更になっているかどうかチェックする
		if( $this->checkAllocationItem() === false ) return 'success';
		
		// 置換ブロック法 割付群/割付因子変更フラグ がONの場合は、割付群/因子を変更する為、ここで終了
		if( empty( $this->change_block_allocation_flag ) === false ) return 'success';
		
		// 割付因子層ヘッダ取得
		$this->conbination_header = $this->getConbinationHeader();
		
		// 割付因子層リスト取得
		$this->conbination_list = $this->getConbinationList();
		
		// 可変ブロック情報取得
		$this->variable_block = $this->mdbView->getAllocateVariableBlock( $this->testee_id );
		if( $this->variable_block === false )
		{
			test_log(date('Y/m/d H:i:s') . ":Testee_View_Edit_Allocation_List:可変ブロック情報取得失敗");
			return 'success';
		}
		
		if( count( $this->variable_block ) <= 0 )
		{
			// 値がない場合は可変ブロック使用なしに設定
			$this->variable_block_flag = 0;
		}
		else
		{
			// データがあるので可変ブロック使用するに設定
			$this->variable_block_flag = 1;
		}
		
		
		// ブロック数設定画面表示
		$this->setting_blockcount_flag = 1;
		
		
		return 'success';
	}
	
	
	/**
	 * 臨床試験支援DB名取得
	 *
	 * @param   string  $testee_id  臨床試験支援DBID
	 * @return  string  臨床試験支援DB名
	 * @access  private
	 */
	private function getTesteeName( $testee_id )
	{
		$testee_object = $this->mdbView->getMdbById( $testee_id );
		if( $testee_object !== false && count( $testee_object ) > 0 )
		{
			return $testee_object['testee_name'];
		}
		else
		{
			return "";
		}
	}
	
	
	/**
	 * 置換ブロック法かどうかチェックする処理
	 *
	 * @return  true:置換ブロック法 / false:それ以外
	 * @access  private
	 */
	private function checkAllocationBlock()
	{
		// 割付設定がfalse or 空、割付フラグ項目がない or 空 or 2以外 の場合はFALSE
		if( $this->allocation === false || count( $this->allocation ) <= 0 || isset( $this->allocation['allocation_flag'] ) === false || $this->allocation['allocation_flag'] != 2 )
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * 現在の割付因子の項目リスト取得作成
	 *
	 * @return  array  割付因子の項目リスト
	 * @access  private
	 */
	private function getFactorList()
	{
		if( $this->selected_allocation === false || count( $this->selected_allocation ) <= 0 )
		{
			return null;
		}
		
		// 現在の割付因子の各項目取得
		$allocate_factors = $this->mdbView->getSelectedAllocationFactors( $this->testee_id );
		if( $allocate_factors === false || count( $allocate_factors ) <= 0 )
		{
			return null;
		}
		
		// 取得した値を組み替え
		$factor_list = $this->mdbView->getAllocationItemList( $allocate_factors );
		
		return $factor_list;
	}
	
	/**
	 * 割付因子の内容チェック
	 *
	 * @return  true:一致 / false:不一致
	 * @access  private
	 */
	private function checkAllocationItem()
	{
		// 現在の割付因子の項目で、組合せパタン作成
		$now_pattern = $this->mdbView->getConbinationPattern( $this->factor_list );
		
		// 現在の組み合わせパターンと、既存層のパターンを比較
		$result = $this->mdbView->matchAllocationFactors( $now_pattern, $this->allocation_conbination );
		
		return $result;
	}
	
	/**
	 * 割付因子の層リストヘッダ取得
	 *
	 * @return  array  割付因子の層リストのヘッダ項目
	 * @access  private
	 */
	private function getConbinationHeader()
	{
		$result = array();
		foreach( $this->factor_list as $value )
		{
			$result[] = $value[ 'item_name' ];
		}
		
		return $result;
	}
	
	/**
	 * 割付因子の層リスト取得
	 *
	 * @return  array  割付因子の層リスト
	 * @access  private
	 */
	private function getConbinationList()
	{
		$result = array();
		foreach( $this->allocation_conbination as $value )
		{
			$factors = array();
			$factors[ 'conbination_id' ] = $value[ 'conbination_id' ];
			$factors[ 'factors' ]        = explode( '|', $value[ 'factor_contents' ] );
			$factors[ 'block_count' ]    = $value[ 'next_block_count' ];
			$factors[ 'exclude_count' ]  = $value[ 'exclude_count' ];
			
			$result[] = $factors;
		}
		
		return $result;
	}
	
}
?>
