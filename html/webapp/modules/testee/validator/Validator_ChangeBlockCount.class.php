<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 割付群の比率と割付層のブロック数の設定時チェック
 *
 * @package     jp.opensource-workshop
 * @author      makino@opensource-workshop.jp
 * @copyright   2017 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Testee_Validator_ChangeBlockCount extends Validator
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
		
		// 必要コンポーネント取得
		$container = DIContainerFactory::getContainer();
		$request   = $container->getComponent('Request');		// リクエスト・クラス
		$mdbView   = $container->getComponent('mdbView');		// 当モジュールViewコンポーネント
		
		// 入力データの取得
		$ratio_list          = $request->getParameter('ratio_list');			// 比率
		$block_count_list    = $request->getParameter('block_count_list');		// ブロック数
// 18.08.01 add start by makino@opensource-workshop.jp
		$exclude_count_list  = $request->getParameter('exclude_count_list');	// 除外連続数
		$variable_block_flag = $request->getParameter('variable_block_flag');	// 可変ブロック使用フラグ
		$variable_block      = $request->getParameter('variable_block');		// 可変ブロック用：基準症例数
// 18.08.01 add end by makino@opensource-workshop.jp
		
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_ChangeBlockCount:start" );
//test_log("ratio_list");
//test_log($ratio_list);
//test_log("block_count_list");
//test_log($block_count_list);
//test_log("exclude_count_list");
//test_log($exclude_count_list);
//test_log("variable_block_flag");
//test_log($variable_block_flag);
//test_log("variable_block");
//test_log($variable_block);
		
		// 数のチェック
		if( count( $ratio_list ) <= 0 )       return "比率が設定されていません。";
		if( count( $block_count_list ) <= 0 ) return "ブロック数が設定されていません。";
		
		// ----- 比率の入力データチェック --------------------------------------
		foreach( $ratio_list as $value )
		{
			$result = $this->checkValue( $value );
			if( $result !== true )
			{
				return "比率" . $result;
			}
			
// 18.08.01 add start by makino@opensource-workshop.jp
			// 0かどうかのチェック
			$check_value = intval( $value );
			if( $check_value == 0 )
			{
				return "比率には1以上の値を指定してください。";
			}
// 18.08.01 add end by makino@opensource-workshop.jp
		}
		
		// 比率の合計値取得
		$total_ratio = $this->getTotalRatio( $ratio_list );
		
		// 比率の合計値の範囲チェック
		if( $total_ratio < 2 || 10 < $total_ratio )
		{
			return "比率の合計値は２～１０になるように設定してください。";
		}
		
// 18.08.01 change start by makino@opensource-workshop.jp
//		// 割付層のブロック数のチェック
//		foreach( $block_count_list as $value )
//		{
//			// 値の基本チェック
//			$result = $this->checkValue( $value );
//			if( $result !== true )
//			{
//				return "ブロック数" . $result;
//			}
//			
//			// 値の範囲チェック
//			$block_count = intval( $value );
//			if( $block_count < 2 || 10 < $block_count )
//			{
//				return "ブロック数は２～１０で指定してください。";
//			}
//			
//			// 比率の合計値とのチェック
//			$result = $this->checkBlockCount( $total_ratio, intval( $value ) );
//			if( $result !== true ) return $result;
//		}
		
		if( empty( $variable_block_flag ) === true )
		{
			// ----- 可変ブロックなしの場合 ------------------------------------
			// ブロック数は単一入力のみとして、ブロック数をチェック
			foreach( $block_count_list as $value )
			{
				// ブロック数の基本チェック
				$result = $this->baseCheckBlockCount( $total_ratio, $value );
				if( empty( $result ) === false ) return $result;
			}
			
			// 除外連続数は単一入力のみとして、ブロック数をチェック
			foreach( $exclude_count_list as $key => $value )
			{
				// 空チェック。除外連続数は空 or 0 OK
				if( empty( $value ) === true )
				{
					continue;
				}
				
				// 基本チェック
				$result = $this->baseCheckExcludeCount( $value, $block_count_list[ $key ], count( $ratio_list ) );
				if( empty( $result ) === false ) return $result;
				
			}
		}
		else
		{
			// ----- 可変ブロックあり ------------------------------------------
			// ブロック数は、複数入力OKとして値をチェック
			$set_block_count_list = array();
			foreach( $block_count_list as $blocks )
			{
				// ","で分解
				$check_list = explode( ",", $blocks );
				
				// 指定された個数が5個を超える場合
				if( count( $check_list ) > 5 )
				{
					return "ブロック数には5個までしか値を指定できません。";
				}
				
				// 分解後のデータを基本チェック
				foreach( $check_list as $value )
				{
					// ブロック数の基本チェック
					$result = $this->baseCheckBlockCount( $total_ratio, $value );
					if( empty( $result ) === false ) return $result;
				}
				
				// 重複チェック
				if( $this->duplicateCheckBlockCount( $check_list ) === false )
				{
					return "ブロック数を複数指定する時、同じ値を指定する事はできません。";
				}
				
				
				$set_block_count_list[] = $check_list;
			}
			
			// 除外連続数には、複数入力OKとして値をチェック
			$i = 0;
			foreach( $exclude_count_list as $excludes )
			{
				// ","で分解
				$check_list = explode( ",", $excludes );
				
				// 除外連続数が空の場合はチェックしない
				if( count( $check_list ) == 1 )
				{
					if( strlen( trim( $check_list[0] ) ) == 0 )
					{
						$i++;
						continue;
					}
				}
				
				// ブロック数の個数と一致するか確認
				if( count( $set_block_count_list[ $i ] ) != count( $check_list ) )
				{
					return ($i+1) . "行目のブロック数と除外連続数の定義数が合いません。";
				}
				
				$j = 0;
				foreach( $check_list as $value )
				{
					// 空 or 0 の場合はチェックしない
					if( empty( $value ) === false )
					{
						// 基本チェック
						$result = $this->baseCheckExcludeCount( $value, $set_block_count_list[ $i ][ $j ], count( $ratio_list ) );
						if( empty( $result ) === false ) return ($i+1) . "行目の" . $result;
					}
					
					$j++;
				}
				
				$i++;
			}
			
			// 基準症例数の値チェック
			// 空かどうか
			if( empty( $variable_block ) === true )
			{
				return "可変ブロックを使用する場合は、基準症例数に10～1000の値を設定してください。";
			}
			
			// 数値かどうか
			if( is_numeric( $variable_block ) === false )
			{
				return "可変ブロックを使用する場合は、基準症例数に10～1000の値を設定してください。";
			}
			
			// 小数点チェック
			if( strpos( $variable_block, '.' ) !== false)
			{
				return "可変ブロックを使用する場合は、基準症例数に10～1000の整数値を設定してください。";
			}
			
			// 値チェック
			$check_value = intval( $variable_block );
			if( $check_value < 10 || 1000 < $check_value )
			{
				return "可変ブロックを使用する場合は、基準症例数に10～1000の整数値を設定してください。";
			}
			
		}
		
// 18.08.01 change end by makino@opensource-workshop.jp
		
		
//test_log( date('Y/m/d H:i:s') . ":Testee_Validator_ChangeBlockCount:end" );
		return;
	}
	
	/**
	 * 値チェック
	 *
	 * @param string $value チェックする値
	 * @return true or string エラー文字列(エラーの場合)
	 * @access private
	 */
	private function checkValue( $value )
	{
		// 空チェック
		if( strlen( trim( $value ) ) == 0 || empty( $value ) )
		{
			return "は必ず入力してください。";
		}
		
		// 数値チェック
		if( is_numeric( $value ) === false )
		{
			return "は数値で入力してください。";
		}
		if( strpos( $value, '.' ) !== false)
		{
			return "は整数値で入力してください。";
		}
		
		// 負数チェック
		$check_value = intval( $value );
// 18.08.01 change start by makino@opensource-workshop.jp
//		if( $check_value < 1 )
		if( $check_value < 0 )
// 18.08.01 change end by makino@opensource-workshop.jp
		{
			return "は正数で入力してください。";
		}
		
		return true;
	}
	
	/**
	 * 合計比率取得処理
	 *
	 * @param  array $ratio_list  割付比率リスト
	 * @return int   割付群の比率の合計値
	 * @access private
	 */
	private function getTotalRatio( $ratio_list )
	{
		$result = 0;
		foreach( $ratio_list as $value )
		{
			$result += intval( $value );
		}
		
		return $result;
	}
	
	/**
	 * 比率とブロック数の相関チェック
	 *
	 * @param  int $total_ratio  比率の合計値
	 : @param  int $block_count  ブロック数
	 * @return true or string  エラー文字列
	 * @access private
	 */
	private function checkBlockCount( $total_ratio, $block_count )
	{
		if( $total_ratio > $block_count )
		{
			return "ブロック数は比率の合計値以上を設定してください。";
		}
		
		$mod_value = $block_count % $total_ratio;
		if( $mod_value != 0 )
		{
			return "ブロック数は比率の合計値の倍数を指定してください。";
		}
		
		return true;
	}
	
// 18.08.01 add start by makino@opensource-workshop.jp
	// ブロック数の基本チェックを行う
	private function baseCheckBlockCount( $total_ratio, $value )
	{
		// 値の基本チェック
		$result = $this->checkValue( $value );
		if( $result !== true )
		{
			return "ブロック数" . $result;
		}
		
		// 値の範囲チェック
		$block_count = intval( $value );
		if( $block_count < 2 || 10 < $block_count )
		{
			return "ブロック数は２～１０で指定してください。";
		}
		
		// 比率の合計値とのチェック
		$result = $this->checkBlockCount( $total_ratio, intval( $value ) );
		if( $result !== true ) return $result;
		
		// 正常終了
		return "";
	}
	
	// ブロック数重複チェック
	private function duplicateCheckBlockCount( $list )
	{
		if( count( $list ) <= 1 ) return true;
		
		for( $i = 0; $i < count( $list ); $i++ )
		{
			$check_value = $list[$i];
			
			for( $j = $i+1; $j < count( $list ); $j++ )
			{
				if( $check_value == $list[$j] ) return false;
			}
		}
		
		return true;
	}
	
	
	// 除外連続数の基本チェックを行う
	private function baseCheckExcludeCount( $value, $block_count, $ratio_count )
	{
		// 数値チェック
		if( is_numeric( $value ) === false )
		{
			return "除外連続数は数値で入力してください。";
		}
		// 小数点チェック
		if( strpos( $value, '.' ) !== false)
		{
			return "除外連続数は整数値で入力してください。";
		}
		// 負数チェック
		$check_value = intval( $value );
		if( $check_value < 0 )
		{
			return "除外連続数は正数で入力してください。";
		}
		
		// 1の除外（1は除外しないと割付できなくなるから）
		if( $check_value == 1 )
		{
			return "連続除外数には１より大きな値を指定してください。";
		}
		
		// ブロック数より大きい値、ブロック数÷群数以下の値であるかチェックする
		if( $check_value < ( $block_count / $ratio_count ) )
		{
			return "連続除外数に[ブロック数÷群数]より小さな値が指定されています。";
		}
		
		
		// 正常終了
		return "";
	}
// 18.08.01 add end by makino@opensource-workshop.jp
	
}
?>