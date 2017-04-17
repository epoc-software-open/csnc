<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Validator_Numeric.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * 件数設定情報の設定時チェック
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Testee_Validator_CountsAdd extends Validator
{
    /**
     * 件数設定情報の設定時チェック
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
		$counts_exist = false;
		// 選択値別件数の登録有無を確認
		if(count($attributes["option_counts"]) > 0){
			foreach($attributes["option_counts"] as $counts){
				if($counts){
					$counts_exist = true;
				}
			}
		}

		// 全て未入力時、OK（既登録の場合、削除）
		if(empty($attributes["all_counts"]) && empty($attributes["counts_id"]) && !$counts_exist){
			return;
		}
        if (empty($attributes["all_counts"])) {
			return "登録時は、全体件数は必須です。";
        } else {
	        if (!is_numeric($attributes["all_counts"])) {
				return "全体件数は数値で入力してください。";
	        }
        }

		if(!empty($attributes["counts_id"]) && !$counts_exist){
			return "件数指定項目を指定の場合、いづれかの選択値別件数を入力してください。";
		}

		// 選択値別件数の登録内容を確認
		if($counts_exist){
			foreach($attributes["option_counts"] as $counts){
				if($counts){
			        if (!is_numeric($counts)) {
						return "選択値別件数は数値で入力してください。";
			        }
			        if ($counts > $attributes["all_counts"]) {
						return "選択値別件数は全体件数以下で入力してください。";
			        }
				}
			}
		}

		return;
    }
}
?>
