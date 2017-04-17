<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限の値チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Validator_AuthorityValue extends Validator
{
    /**
     * 権限の値チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (!is_array($attributes["post_authority"]) ||
				!is_array($attributes["mail_authority"])) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		$post_authority = min(array_keys($attributes["post_authority"]));
		$request->setParameter("post_authority", $post_authority);

		$mail_authority = min(array_keys($attributes["mail_authority"]));
		$request->setParameter("mail_authority", $mail_authority);
    }
}
?>