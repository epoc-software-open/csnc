<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目一覧参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_MetadataListView extends Validator
{
    /**
     * メタデータ一覧参照権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$container =& DIContainerFactory::getContainer();
        $mdbView =& $container->getComponent("mdbView");
        
		$request =& $container->getComponent("Request");
		$testee_id = $request->getParameter("testee_id");
		$metadatas = $mdbView->getMetadatas(array("testee_id" => intval($testee_id)));
		if ($metadatas === false) {
			return $errStr;
		}

		$request =& $container->getComponent("Request");
    	$request->setParameter("metadatas", $metadatas);

        return;
    }
}
?>