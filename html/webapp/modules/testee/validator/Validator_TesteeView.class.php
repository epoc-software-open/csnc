<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_TesteeView extends Validator
{
    /**
     * 汎用データベース参照権限チェックバリデータ
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

		$session =& $container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$request =& $container->getComponent("Request");
		$prefix_id_name = $request->getParameter("prefix_id_name");

		if ($auth_id < _AUTH_CHIEF &&
				$prefix_id_name == TESTEE_REFERENCE_PREFIX_NAME.$attributes['testee_id']) {
			return $errStr;
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($prefix_id_name) &&	($actionName == "testee_view_main_init"
							|| $actionName == "testee_view_main_detail"
							|| $actionName == "testee_view_main_search_init" || $actionName == "testee_view_main_search_result")) {
			$request =& $container->getComponent("Request");
			$request->setParameter("theme_name", "system");
		}

        $mdbView =& $container->getComponent("mdbView");
		if (empty($attributes['testee_id'])) {
			$mdb_obj = $mdbView->getDefaultMdb();
		} elseif ( $actionName == 'testee_view_edit_style' ) {
			// 表示方法変更の場合は、現在設定されている表示件数や表示順を取得したいため、getCurrentMdb()を呼ぶ。 by nagahara@opensource-workshop.jp
			$mdb_obj = $mdbView->getCurrentMdb();
		} elseif ($prefix_id_name == TESTEE_REFERENCE_PREFIX_NAME.$attributes['testee_id']
//					|| !strncmp($actionName, 'testee_view_edit', 23)) {		モジュール名変更により比較文字列数変更
					|| !strncmp($actionName, 'testee_view_edit', 16)) {
			$mdb_obj = $mdbView->getMdb();
		} else {
			$mdb_obj = $mdbView->getCurrentMdb();
		}

		if (empty($mdb_obj)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("mdb_obj", $mdb_obj);

        return;
    }
}
?>
