<?php

/**
 * メール送信アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Statusmail extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mailMain = null;
	var $mdbView = null;
	var $session = null;
	var $usersView = null;

	/**
	 * メール送信アクション
	 *
	 * @access  public
	 */
	function execute()
	{

		// 進捗を登録した際にセッションに保持しておいた内容を取得
		$status_mail = $this->session->getParameter("testee_status_mail");
		$content_id  = intval($status_mail['content_id']);
		$status_id   = intval($status_mail['status_id']);
		$testee_id   = intval($status_mail['testee_id']);
		$regist_kind = $status_mail['regist_kind'];
		if( array_key_exists( 'before_status_data', $status_mail ) == true )
		{
			$before_status_data = $status_mail['before_status_data'];
		}
		else
		{
			$before_status_data = null;
		}

		if ( empty($content_id) || empty($status_id) || empty($testee_id) ) {
			return 'success';
		}

		// データベースの取得(進捗番号をフッターで使用するため)
		$testee = $this->mdbView->getMdbById( $testee_id );

		// コンテンツデータの取得
		$content = $this->mdbView->getContent( $content_id );

		// コンテンツデータの施設の取得
		$content_hospital = $this->mdbView->getContentHospital( $testee_id, $content_id );

		// 進捗データの取得
		$content_status = $this->mdbView->getContentStatus( $content_id, $status_id );
		//test_log($content_status);

		// 進捗の組換え[進捗番号:status_id][コンテンツ番号:content_no]
		$statuses = array();
		foreach( $content_status as $status ) {

			// 画面で使うための配列
			$statuses[$status["status_id"]][$status["content_no"]] = $status;
		}
		//test_log($statuses);

		// メール送信有無確認
		if( $this->_checkSendMail( $regist_kind, $status_id, $before_status_data, $statuses ) == false )
		{
			// メール送信対象外
			return 'success';
		}

		// メール送信ユーザ(可能性の最大ユーザ)を取得
		$users = $this->usersView->getSendMailUsers($this->room_id, _AUTH_GUEST);

		// 登録者の施設情報（パイプ付）の取得
		$my_hospital = $this->mdbView->getHospital($this->session->getParameter("_user_id"));

		// TEDC権限が「管理者」「DM」は全て、以外は「作業補助者」は同一施設情報の会員のみ	EddyK
		$new_users = array();
		foreach ( $users as $user_record ) {
			$tedc_auth = $this->mdbView->getUserTEDC($user_record['user_id'], $testee_id);
			if($tedc_auth == TEDC_AUTHORITY_ADMIN || $tedc_auth == TEDC_AUTHORITY_DM){
				$new_users[] = $user_record;
			} else {
				if($tedc_auth == TEDC_AUTHORITY_ASSISTANT ){
					if($my_hospital == $this->mdbView->getHospital($user_record['user_id'])){
						$new_users[] = $user_record;
					}
				}
			}
		}

		// メール送信(件名)
		$subject = "";
		if ( $status_id == 2 ) {
			$subject = "マイクロRNA研究_血清検体受領、EDC入力依頼のお知らせ";
		}
		else if ( $status_id == 3 ) {
			$subject = "治療後の資材・伝票の送付";
		}


		// メール送信(本文)
		$body = "";
		if ( $status_id == 2 ) {

			$body .= "マイクロRNA研究に、平素よりご協力を賜り有り難うございます。\n";
			$body .= "\n";
			$body .= "登録番号：" . $content["content_no"] . "様\n";
			$body .= "（登録施設：" . $content_hospital . "、登録日：" . timezone_date($statuses[2][1]["insert_time"], false, "Y/m/d") . "）\n";
			$body .= "につきまして、\n";
			$body .= "以下の通り、血清検体を受領しましたので、お知らせします。\n";
			$body .= "\n";
			$body .= "検体採取日		：" . $statuses[2][1]["content_data"] . "\n";
			$body .= "検体受領日		：" . $statuses[2][2]["content_data"] . "\n";
			$body .= "受領した血清検体の本数	：" . $statuses[2][3]["content_data"] . "\n";
			$body .= "送付者氏名		：" . $statuses[2][4]["content_data"] . "\n";
			$body .= "\n";
			$body .= "※4～8週以内に、以下のCRFにつきまして、EDC入力をお願い申し上げます。\n";
			$body .= "「患者背景調査」\n";
			$body .= "「治療報告」\n";
			$body .= "「確定診断報告」\n";
			$body .= "\n";
			$body .= "\n";
			if ( defined( "TESTEE_KENTAI_MAIL_FOOTER_" . $testee["kentai_switch"] ) ) {
				$body .= constant( "TESTEE_KENTAI_MAIL_FOOTER_" . $testee["kentai_switch"] );
			}
		}
		else if ( $status_id == 3 ) {

			$body .= "マイクロRNA研究に、平素よりご協力を賜り有り難うございます。\n";
			$body .= "\n";
			$body .= "登録番号：" . $content["content_no"] . "様\n";
			$body .= "（登録施設：" . $content_hospital . "、登録日：" . timezone_date($statuses[3][2]["insert_time"], false, "Y/m/d") . "）\n";
			$body .= "につきまして、\n";
			$body .= "治療後採血用の資材・伝票を送付しましたので、\n";
			$body .= "血液採取のご準備をお願い申し上げます。\n";
			$body .= "\n";
			$body .= "資材・伝票送付日：" . $statuses[3][2]["content_data"] . "\n";
			$body .= "\n";
			$body .= "\n";
			if ( defined( "TESTEE_KENTAI_MAIL_FOOTER_" . $testee["kentai_switch"] ) ) {
				$body .= constant( "TESTEE_KENTAI_MAIL_FOOTER_" . $testee["kentai_switch"] );
			}
		}


		$this->mailMain->setSubject($subject);
		$this->mailMain->setBody($body);

		$this->mailMain->setToUsers($new_users);
		$this->mailMain->send();

		// セッションのクリア(メール送信受け渡しデータの掃除)
		$this->session->removeParameter("testee_status_mail");

		return 'success';
	}
	
	/**
	 * メール送信するかどうかチェックする
	 *
	 * @return	bool	true:送信する / false:送信しない
	 * @access  private
	 */
	private function _checkSendMail( $regist_kind, $status_id, $before_status_data, $statuses )
	{
		if( $regist_kind == "update" )
		{
			// ----- [更新]の場合 ----------------------------------------------
			
			// [治療後の資材・伝票の送付](status_id=3)以外の時はメール送信しない
			if( $status_id != 3 )
			{
				return false;
			}
			
			// 変更後の[確定診断病名](content_no=1)が[がん](content_data=1)以外の場合は、メール送信しない
			if( intval( $statuses[3][1]["content_data"] ) != 1 )
			{
				return false;
			}
			
			// 変更前の[確定診断病名](content_no=1)を取得
			$before_kakuteisindan = 1;		// ※もしcontent_dataが取得できなかった場合、メールを送信しないように"1"を設定しておく
			foreach( $before_status_data as $value )
			{
				if( intval( $value["content_no"] ) == 1 )
				{
					$before_kakuteisindan = intval( $value["content_data"] );
					break;
				}
			}
			
			// 変更前の[確定診断病名](content_no=1)が[がん](content_data=1)の場合、確定診断病名が変更されていないので、メール送信しない
			if( $before_kakuteisindan == 1 )
			{
				return false;
			}
			
			// 上記以外の場合は、メールする
			// ※上記以外＝[変更]で[治療後の資材・伝票の送付]で[確定診断病名]が、[その他]から[がん]に変更された場合
			return true;
		}
		else
		{
			// ----- [登録]の場合 ----------------------------------------------
			
			if( $status_id == 2 )
			{
				// [検体登録](status_id=2)の時は、メール送信する
				return true;
			}
			else if( $status_id != 3 )
			{
				// [治療後の資材・伝票の送付](status_id=3)以外の時は、メール送信しない
				return false;
			}
			
			// [治療後の資材・伝票の送付](status_id=3)の時
			if( intval( $statuses[3][1]["content_data"] ) == 1 )
			{
				// [確定診断病名](content_no=1)が[がん](content_data=1)の時は、メールを送信する
				return true;
			}
			
			// 上記以外の場合は、メール送信しない
			return false;
		}
	}
}
?>
