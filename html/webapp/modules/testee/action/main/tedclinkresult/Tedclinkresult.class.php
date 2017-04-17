<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ登録TEDC連携アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Tedclinkresult extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
 	var $session = null;
 	var $mdbView = null;
 	var $mdbAction = null;
 	var $db = null;

	// validatorから受け取るため

    /**
     * コンテンツ登録TEDC連携アクション
     *
     * @access  public
     */
    function execute()
    {
//		連携URL	https://localhost/import_confinfo_call
//					?loginid=user
//					&password=ee11cbb19052e40b07aac0ca060c23ee
//					&studyid=20

		$testee_tedclink = $this->session->getParameter("testee_tedclink");
		$testee_id = intval($testee_tedclink["testee_id"]);
		$content_id = intval($testee_tedclink["content_id"]);

		$user_id = $this->session->getParameter("_user_id");
		$params = array("user_id" => $user_id);
		$result = $this->db->selectExecute ("users", $params);
		if ($result === false) {
			return 'error';
		}
		$login_id = $result[0]["login_id"];
		$password = $result[0]["password"];

		$url = TEDC_LINK_HTTP."://".TEDC_LINK_URL."?loginid=".$login_id."&password=".$password."&studyid=".$testee_id;
		//$url = TEDC_LINK_HTTP."://".TEDC_LINK_URL;

		$context = stream_context_create(array(
		     TEDC_LINK_HTTP => array(
				'ignore_errors' => true,
				'timeout' => 10
			)
		));
		$response = @file_get_contents( $url, false, stream_context_create( array( 'http' => array( 'timeout' => 10 ) ) ) );
		//$response = @file_get_contents($url, false, $context);

		$code = "";
		$message = "";

		preg_match('/HTTP\/1\.[0|1|x] ([0-9]{3})/', $http_response_header[0], $matches);
		$status_code = $matches[1];
		switch ($status_code) {
		    case '200':
		        // 200の場合

				//$result_code = @simplexml_load_string($response);
				//if($result_code){
				//	$code = $result_code->code[0];
				//	$message = $result_code->error_message[0];
				//} else {
				//	$code = "X";		// XMLパースエラー
				//}

				// 環境依存のせいか、XML パースができないために、手動でパース
				$code = substr( $response, strpos( $response, "<code>" ) + 6, 1 );
				$error_message_start = strpos( $response, "<error_message>" ) + 15;
				$error_message_end = strpos( $response, "</error_message>" );
				$message = substr( $response, $error_message_start, $error_message_end - $error_message_start );

		        break;
		    case '404':
		        // 404の場合
				$code = "8";			// URLリクエスト404エラー
		        break;
		    default:
				$code = "9";			// URLリクエストエラー
		        break;
		}
		// TEDC連携結果の更新
		$result = $this->mdbAction->tedcLinkResult($content_id, $code, $message);

		$this->session->removeParameter("testee_tedclink");
		return 'success';
    }
}
?>
