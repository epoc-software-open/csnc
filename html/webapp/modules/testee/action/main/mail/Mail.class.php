<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
class Testee_Action_Main_Mail extends Action
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

	// validatorから受け取るため
	var $mail = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		$content_mail = $this->session->getParameter("testee_mail_content_id");
		$content_id = intval($content_mail['content_id']);

		if (empty($content_id)) {
			return 'success';
		}
		$params = array(
			"content_id" => $content_id
		);
		$contents = $this->db->selectExecute("testee_content", $params);
		if($contents === false || !isset($contents[0])) {
			return 'error';
		}

		$testee_id = $contents[0]['testee_id'];
		$metadatas = $this->mdbView->getMetadatas(array("testee_id" => $testee_id));
    	if($metadatas === false) {
    		return 'error';
    	}

		$mail = $this->mdbView->getMail($content_id, $metadatas);
		if ($mail === false) {
			return 'error';
		}
		$data = "";
		foreach (array_keys($metadatas) as $i) {
/* メール送信内容を施設名、グループのみとする　EddyK
			$data .= htmlspecialchars($metadatas[$i]['name']) . ':';
			if ($metadatas[$i]['type'] == TESTEE_META_TYPE_IMAGE || $metadatas[$i]['type'] == TESTEE_META_TYPE_FILE) {
				$data .= $this->mdbView->getFileLink($mail['content'.$metadatas[$i]['metadata_id']],
														$mail['file_name'.$metadatas[$i]['metadata_id']],
														$mail['physical_file_name'.$metadatas[$i]['metadata_id']],
														$metadatas[$i], BASE_URL.INDEX_FILE_NAME);

			} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_WYSIWYG) {
				$data .= $mail['content'.$metadatas[$i]['metadata_id']];
			} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_DATE && !empty($mail['content'.$metadatas[$i]['metadata_id']])) {
				$data .= timezone_date_format($mail['content'.$metadatas[$i]['metadata_id']], _DATE_FORMAT);
			} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_INSERT_TIME) {
				$data .= timezone_date_format($mail['insert_time'], _FULL_DATE_FORMAT);
			} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_UPDATE_TIME) {
				$data .= timezone_date_format($mail['update_time'], _FULL_DATE_FORMAT);
			} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_AUTONUM) {
				$data .= intval($mail['content'.$metadatas[$i]['metadata_id']]);
			} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_TEXTAREA) {
				$data .= str_replace("\n", '<br />', htmlspecialchars($mail['content'.$metadatas[$i]['metadata_id']]));
			} else {
				$data .= htmlspecialchars($mail['content'.$metadatas[$i]['metadata_id']]);
			}
			$data .= '<br />';
*/
			if($metadatas[$i]['type'] == TESTEE_META_TYPE_N_HOSPITAL || $metadatas[$i]['type'] == TESTEE_META_TYPE_N_GROUP) {
				$data .= htmlspecialchars($metadatas[$i]['name']) . ':';
				$data .= htmlspecialchars($mail['content'.$metadatas[$i]['metadata_id']]);
				$data .= '　　　';
				//$data .= '<br />';
			}
		}


		// 割付設定取得
		$result_allocation = $this->mdbView->getAllocationContent( $testee_id );
		if ( !empty( $result_allocation ) ) {
			// 割付使用＆表示の場合のみ、割付結果を表示
			if ( ( $result_allocation["allocation_flag"] == 1 || $result_allocation["allocation_flag"] == 2 ) && 
			     $result_allocation["allocation_result_flag"] == 1 ) {


				$tags['X-ARMCD'] = $mail["group_name"];
				$tags['X-ARM'] = $mail["intervention"];
			}
		}


		$this->mailMain->setSubject($mail['mail_subject']);
		$this->mailMain->setBody($mail['mail_body']);

		$tags['X-MDB_NAME'] = htmlspecialchars($mail['testee_name']);

		$tags['X-CONTENT-NO'] = $contents[0]['content_no'];		// 登録番号を追加 EddyK
		$tags['X-HOSPITAL_NAME'] = $this->mdbView->getContentHospital( $testee_id, $content_id );	// 登録施設

		$tags['X-DATA'] = $data;
		$tags['X-USER'] = htmlspecialchars($mail['insert_user_name']);
		$tags['X-TO_DATE'] = $mail['insert_time'];
		$tags['X-URL'] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=testee_view_main_detail".
							"&content_id=". $content_id.
							"&testee_id=". $testee_id.
							"&block_id=". $this->block_id.
							"#". $this->session->getParameter("_id");
		$this->mailMain->assign($tags);

		if($content_mail['agree_flag'] == TESTEE_STATUS_WAIT_AGREE_VALUE) {
			$users = $this->usersView->getSendMailUsers($this->room_id, _AUTH_CHIEF);
		}else if($content_mail['agree_flag'] == TESTEE_STATUS_AGREE_VALUE) {
			$users = $this->usersView->getSendMailUsers($this->room_id, $mail['mail_authority']);
		}

		// 登録者の施設情報（パイプ付）の取得 EddyK
		$my_hospital = $this->mdbView->getHospital($this->session->getParameter("_user_id"));

		// TEDC権限が「研究事務局」以上は全て、以外は「作業補助者」以上は同一施設情報の会員のみ	EddyK
		$new_users = array();
		foreach ( $users as $user_record ) {
			$tedc_auth = $this->mdbView->getUserTEDC($user_record['user_id'], $testee_id);
			if($tedc_auth >= TEDC_AUTHORITY_SECRETARY ){
				$new_users[] = $user_record;
			} else {
				if($tedc_auth >= TEDC_AUTHORITY_ASSISTANT ){
					if($my_hospital == $this->mdbView->getHospital($user_record['user_id'])){
						$new_users[] = $user_record;
					}
				}
			}
		}
		//test_log($new_users);

		// 試験毎にメール送信ユーザが設定されている場合に、取得する。
		$target_users = $this->mdbView->getMailUser4Testee( $testee_id );
		//test_log($target_users);

		// 送信するユーザを確定する。
		$send_users = array();
		if ( empty( $target_users ) ) {
			$send_users = $new_users;
		}
		else {
			// グループ(施設)までで絞ったユーザをループし、その中から、試験毎に設定されたメール送信ユーザで絞り込む
			foreach( $new_users as $new_user ) {
				// 試験毎に設定されたメール送信ユーザ
				foreach( $target_users as $target_user ) {
					if ( $new_user["user_id"] == $target_user["user_id"] ) {
						$send_users[] = $new_user;
						break;
					}
				}
			}
		}
		//test_log($send_users);

		// 上記TEDC権限で判断したユーザーを対象にメール送信する		EddyK
		$this->mailMain->setToUsers($send_users);
		$this->mailMain->send();
		$this->session->removeParameter("testee_mail_content_id");

		return 'success';
    }
}
?>
