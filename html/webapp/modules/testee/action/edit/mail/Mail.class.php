<?php

/**
 * メール設定の編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Mail extends Action
{

	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $testee_name = null;

	// 送信するmetadata_id
	var $metadata_id = null;

	// 送信するユーザ
	var $mail_users = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $mdbAction = null;

	/**
	 * 汎用データベース編集アクション
	 *
	 * @access  public
	 */
	function execute()
	{

		$this->mdbAction->updateMailUsers( $this->metadata_id, $this->mail_users );

		return 'success';
	}
}
?>
