<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ登録権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_RatioCheck extends Validator
{
	/**
	 * コンテンツ登録権限チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr	 エラー文字列
	 * @param array $params	 オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */

	function validate($attributes, $errStr, $params)
	{
		//test_log($attributes);
			/*		Array
			(
				[0] => 1001		testee_id
				[1] => 90		ratio
				[2] => 10		allocation_group_id
			)*/
		//test_log($errStr);
		//test_log($params);
		
		$container =& DIContainerFactory::getContainer();
		$db =& $container->getComponent("DbObject");
		
		$testee_id = array($attributes[0], $attributes[2] );
		
		$sql = "SELECT SUM(ratio) 
				FROM {testee_allocation_group} 
				WHERE testee_id=?
				AND allocation_group_id !=? ";
		$total_ratio = $db->execute($sql, $testee_id);
		//test_log($attributes[1]);
		//test_log($total_ratio[0]['SUM(ratio)']);
		if(($attributes[1] + $total_ratio[0]['SUM(ratio)']) > 100 ){
			return $errStr;
		}
		return;
	}
}
?>