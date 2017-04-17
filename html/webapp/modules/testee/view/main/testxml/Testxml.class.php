<?php
/**
 * XMLレスポンス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Testee_View_Main_Testxml extends Action
{

	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	var $login_id = null;
	var $password = null;
	var $studyid = null;

	// 使用コンポーネントを受け取るため

	// 値をセットするため

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{
print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<result_code>
 <code>1</code>
<error_message>AE00081：患者情報はすでに取り込み済みです。</error_message>
</result_code>
EOF;
		exit;
	}
}
?>