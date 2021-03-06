<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アルバム登録権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_AlbumEntry extends Validator
{
    /**
     * アルバム登録権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        if (empty($attributes["album_id"])) {
        	$photoalbum = $attributes["photoalbum"];
        	if (empty($photoalbum["album_authority"])) {
        		return $errStr;
        	}
        } else {
	        $album = $attributes["album"];
	        if (empty($album["edit_authority"])) {
	        	return $errStr;
	        }
        }

        return;
    }
}
?>