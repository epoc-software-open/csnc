<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Addcontent extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;
	var $testee_id = null;
	var $content_id = null;
	var $upload_ids = null;
	var $temporary_flag = null;
	var $passwords = null;

	// バリデートによりセット
	var $mdb_obj = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $mdbView = null;
	var $request = null;
	var $uploadsAction = null;
	var $mdbAction = null;

	// バリデートによりセットするため;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{

		// 登録後の画面で表示するためのコンテンツID(初期値は空)
		$this->session->setParameter("testee_insert_content_id", "");
		$this->session->setParameter("testee_insert_testee_id", "");

		$datas =& $this->session->getParameter(array("testee_content", $this->block_id));
		if(!isset($datas) || $datas == null) {
			//セッションデータなし
			return 'error';
		}

		$_auth_id = $this->session->getParameter("_auth_id");

		if($this->temporary_flag == _ON) {
			if(!empty($this->content_id)) {
				$status = TESTEE_STATUS_TEMPORARY_VALUE;
			}else {
				$status = TESTEE_STATUS_BEFORE_RELEASED_VALUE;
			}
		}else {
			$status = TESTEE_STATUS_RELEASED_VALUE;
		}
		if($_auth_id < _AUTH_CHIEF && $this->mdb_obj['agree_flag'] == _ON) {
			$agree_flag = TESTEE_STATUS_WAIT_AGREE_VALUE;
		}else {
			$agree_flag = TESTEE_STATUS_AGREE_VALUE;
		}

		if(empty($this->content_id)) {
			// ---------- DBテーブルのDB毎連番の取得・更新 EddyK start. ----------
			$content_no = $this->db->maxExecute("testee", "content_no", array("testee_id" => $this->testee_id));
			$update_params = array(
				"content_no" => $content_no + 1
			);
			$result = $this->db->updateExecute("testee", $update_params, array("testee_id" => $this->testee_id));
			if ($result === false) {
				return 'error';
			}
			// ---------- DBテーブルのDB毎連番の取得・更新 EddyK end. ----------

			$display_sequence = $this->db->maxExecute("testee_content", "display_sequence", array("testee_id" => $this->testee_id));
			$insert_params = array(
				"testee_id" => $this->testee_id,
				"agree_flag" => $agree_flag,
				"temporary_flag" => $status,
				"display_sequence" => $display_sequence + 1,
				"content_no" => $content_no + 1				// DB毎連番の追加 EddyK
			);
			$content_id = $this->db->insertExecute("testee_content", $insert_params, true, "content_id");
			if ($content_id === false) {
				return 'error';
			}

			// 割付データ登録
			$result = $this->mdbAction->setContentAllocation( $this->testee_id, $content_id, $datas );
			if ($result === false) {
				return 'error';
			}
			// 登録後の画面で表示するためのコンテンツID
			$this->session->setParameter("testee_insert_content_id", $content_id);
			$this->session->setParameter("testee_insert_testee_id", $this->testee_id);

			// --- メール送信データ登録 ---
			if ($this->mdb_obj['mail_flag'] == _ON &&
					$status == TESTEE_STATUS_RELEASED_VALUE && $agree_flag == TESTEE_STATUS_AGREE_VALUE) {
				$this->session->setParameter("testee_mail_content_id", array("content_id" => $content_id, "agree_flag" => TESTEE_STATUS_AGREE_VALUE));
			}

			// TEDC連携結果更新用testee_id設定
			$this->session->setParameter("testee_tedclink", array("testee_id" => $this->testee_id, "content_id" => $content_id));

			//--URL短縮形関連 Start--
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->setAbbreviateUrl($this->testee_id, $content_id);
			if ($result === false) {
				return 'error';
			}
			//--URL短縮形関連 End--

		}else {
			// 変更前データ取得
			$content_before_update = $this->db->selectExecute("testee_content", array("content_id"=>$this->content_id, "testee_id" => $this->testee_id));
			if($content_before_update === false || empty($content_before_update[0])) {
				return "error";
			}

			// ステータスの更新
			if ($status == TESTEE_STATUS_TEMPORARY_VALUE && $content_before_update[0]['temporary_flag'] == TESTEE_STATUS_BEFORE_RELEASED_VALUE) {
				$status = TESTEE_STATUS_BEFORE_RELEASED_VALUE;
			}
			if ($status == TESTEE_STATUS_TEMPORARY_VALUE && $content_before_update[0]['agree_flag'] == TESTEE_STATUS_WAIT_AGREE_VALUE) {
				$agree_flag = TESTEE_STATUS_WAIT_AGREE_VALUE;
			}
			$update_params = array(
				"agree_flag" => $agree_flag,
				"temporary_flag" => $status
			);
			if ($status == TESTEE_STATUS_RELEASED_VALUE && $content_before_update[0]["temporary_flag"] == TESTEE_STATUS_BEFORE_RELEASED_VALUE) {
				$update_params["insert_time"] = timezone_date();
			}
			$where_params = array(
				"content_id" => $this->content_id,
				"testee_id" => $this->testee_id
			);
			$result = $this->db->updateExecute("testee_content", $update_params, $where_params, true);
			if ($result === false) {
				return 'error';
			}

			// メール送信データ登録
			if ($this->mdb_obj['mail_flag'] == _ON && $agree_flag == TESTEE_STATUS_AGREE_VALUE && $status == TESTEE_STATUS_RELEASED_VALUE
					&& ($content_before_update[0]['temporary_flag'] == TESTEE_STATUS_BEFORE_RELEASED_VALUE || $content_before_update[0]['agree_flag'] == TESTEE_STATUS_WAIT_AGREE_VALUE)) {
				$this->session->setParameter("testee_mail_content_id", array("content_id" => $this->content_id, "agree_flag" => TESTEE_STATUS_AGREE_VALUE));
			}

			// TEDC連携結果更新用content_id設定
			$this->session->setParameter("testee_tedclink", array("testee_id" => $this->testee_id, "content_id" => $this->content_id));

			if($content_before_update[0]['agree_flag'] == TESTEE_STATUS_WAIT_AGREE_VALUE &&
				$agree_flag == TESTEE_STATUS_AGREE_VALUE &&
				$this->mdb_obj['agree_mail_flag'] == _ON) {
				$this->session->setParameter("testee_confirm_mail_content_id", $this->content_id);
			}
		}

		$params = array(
				"testee_id" => $this->testee_id
		);
		$metadatas = $this->mdbView->getMetadatas($params);


		foreach($metadatas as $metadata_id => $metadata) {
			$data_value = "";
			$select_content_code = null;		// 選択肢コード値	EddyK
			if($datas[$metadata_id] != null) {
				if($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE) {
					//$data_value = $datas[$metadata_id]['upload_id'];
					//$data_value = "<a href='?action=".$datas[$metadata_id]['action_name']."&upload_id=".$datas[$metadata_id]['upload_id']."' target='_blank'>".$datas[$metadata_id]['file_name']."</a>";
					if($datas[$metadata_id] == _ON) {
						$data_value = "";
					}else {
						$data_value = "?action=".$datas[$metadata_id]['action_name']."&upload_id=".$datas[$metadata_id]['upload_id'];
					}
				//}else if($metadata['type'] == TESTEE_META_TYPE_IMAGE) {
					//$data_value = "<img src='?action=".$datas[$metadata_id]['action_name']."&upload_id=".$datas[$metadata_id]['upload_id']."' title='".$datas[$metadata_id]['file_name']."' alt='".$datas[$metadata_id]['file_name']."' style='height:".$height."px;width:".$width."px;'/>";

				} else {
					$data_value = $datas[$metadata_id];
					// 独自の選択式のタイプの場合、選択肢コード値を取得		EddyK
					if($metadata['type'] == TESTEE_META_TYPE_N_RADIO || 
						$metadata['type'] == TESTEE_META_TYPE_N_HOSPITAL || 
						$metadata['type'] == TESTEE_META_TYPE_N_SEX || 
						$metadata['type'] == TESTEE_META_TYPE_N_GROUP){
						$select_contents = explode("|", $metadata['select_content']);
						$select_content_codes = explode("|", $metadata['select_content_code']);
						$index = array_search($data_value, $select_contents);
						if($index !== false) $select_content_code = $select_content_codes[$index];
					}
				}
			}

			if(!empty($this->content_id)) {
				$where_params = array(
					"content_id" => $this->content_id,
					"metadata_id" => $metadata_id
				);
				$metadata_content = $this->db->selectExecute("testee_metadata_content", $where_params);
				if($metadata_content === false) {
					return 'error';
				}
				if ($metadata['type'] == TESTEE_META_TYPE_AUTONUM) {
					if ($status == TESTEE_STATUS_RELEASED_VALUE && empty($metadata_content[0]["content"])) {
						$data_value = $this->mdbView->getAutoNumber($metadata_id);
					} else {
						$data_value = $metadata_content[0]["content"];
					}
				}
				if(!(($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE)
					&& $datas[$metadata_id] != _ON && !is_array($datas[$metadata_id]))) {

					$params = array(
						"content" => $data_value,
						"select_content_code" => $select_content_code			// 選択肢コード値の編集		EddyK
					);

					if(!empty($metadata_content)) {
						$result = $this->db->updateExecute("testee_metadata_content", $params, $where_params, true);
						if ($result === false) {
							return 'error';
						}

						if($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE) {
							$where_params = array(
								"metadata_content_id" => $metadata_content[0]['metadata_content_id']
							);

							$metadata_file = $this->db->selectExecute("testee_file", $where_params);
							if($metadata_file === false) {
								return 'error';
							}

							if(!isset($metadata_file[0])) {
								$params = array(
									"metadata_content_id" => $metadata_content[0]['metadata_content_id'],
									"upload_id" => $datas[$metadata_id]['upload_id'],
									"file_name" => $datas[$metadata_id]['file_name'],
									"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:"",
									"physical_file_name" => $datas[$metadata_id]['physical_file_name'],
									"room_id" => $this->room_id
								);

								$result = $this->db->insertExecute("testee_file", $params);
								if ($result === false) {
									return 'error';
								}
							}else {
								$params = array(
									"upload_id" => $datas[$metadata_id]['upload_id'],
									"file_name" => $datas[$metadata_id]['file_name'],
									"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:"",
									"physical_file_name" => $datas[$metadata_id]['physical_file_name'],
									"room_id" => $this->room_id
								);
								$result = $this->db->updateExecute("testee_file", $params, $where_params);
								if ($result === false) {
									return 'error';
								}
							}
						}
					}else {
						if ($metadata['type'] == TESTEE_META_TYPE_AUTONUM && $status == TESTEE_STATUS_RELEASED_VALUE) {
							$data_value = $this->mdbView->getAutoNumber($metadata_id);
						}
//	選択肢コード値の編集追加	EddyK
//						if( $this->_insertContent($this->content_id, $metadata_id, $data_value, $metadata, $datas) === false ){
						if( $this->_insertContent($this->content_id, $metadata_id, $data_value, $metadata, $datas, $select_content_code) === false ){
							return 'error';	//_insertContent()の戻り値をみて、エラーなら'error'を返すよう修正 by AllCreator
						}
					}
				}

				if($datas[$metadata_id] === _ON && ($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE)) {
					if(!empty($metadata_content)) {
						$result = $this->mdbAction->deleteMetadataContent($metadata_content[0]['metadata_content_id']);
						if ($result === false) {
							return 'error';
						}
					}
				}else if($metadata['type'] == TESTEE_META_TYPE_FILE && (!empty($data_value) || !isset($metadata_file[0]))) {
					if(!empty($metadata_content)) {
						$where_params = array(
							"metadata_content_id" => $metadata_content[0]['metadata_content_id']
						);
						$metadata_file = $this->db->selectExecute("testee_file", $where_params);
						if($metadata_file === false) {
							return 'error';
						}
						$params = array(
							"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:""
						);
						$result = $this->db->updateExecute("testee_file", $params, $where_params);
						if ($result === false) {
							return 'error';
						}
					}
				}
			}else {
				if ($metadata['type'] == TESTEE_META_TYPE_AUTONUM && $status == TESTEE_STATUS_RELEASED_VALUE) {
					$data_value = $this->mdbView->getAutoNumber($metadata_id);
				}
//	選択肢コード値の編集追加	EddyK
//				if( $this->_insertContent($content_id, $metadata_id, $data_value, $metadata, $datas) === false ){
				if( $this->_insertContent($content_id, $metadata_id, $data_value, $metadata, $datas, $select_content_code) === false ){
					return 'error';	//_insertContent()の戻り値をみて、エラーなら'error'を返すよう修正 by AllCreator
				}
			}
		}

		//承認を付いた場合、管理者にメールで通知する
	 	if($this->mdb_obj['agree_flag'] == _ON && $status == TESTEE_STATUS_RELEASED_VALUE && $agree_flag == TESTEE_STATUS_WAIT_AGREE_VALUE) {
			$this->session->setParameter("testee_mail_content_id", array("content_id" => (empty($this->content_id) ? $content_id : $this->content_id), "agree_flag" => TESTEE_STATUS_WAIT_AGREE_VALUE));
		}

		//--新着情報関連 Start--
		$result = $this->mdbAction->setWhatsnew((empty($this->content_id) ? $content_id : $this->content_id));
		if ($result === false) {
			return 'error';
		}
		//--新着情報関連 End--

		// --- 投稿回数更新 ---
		$before_content = isset($content_before_update[0]) ? $content_before_update[0] : null;
		$edit_flag = (empty($this->content_id)) ? false : true;

		$result = $this->mdbAction->setMonthlynumber($edit_flag, $status, $agree_flag, $before_content);
		if ($result === false) {
			return 'error';
		}

		if(!empty($this->upload_ids)) {
			foreach($this->upload_ids as $key => $val) {
				$this->uploadsAction->updGarbageFlag($this->upload_ids[$key]);
			}
		}
		$this->session->removeParameter(array("testee_content", $this->block_id));

		return 'success';
	}

//	function _insertContent($content_id, $metadata_id, $data_value, $metadata, $datas) {	選択肢コード値の編集追加	EddyK
	function _insertContent($content_id, $metadata_id, $data_value, $metadata, $datas, $select_content_code) {
		$params = array(
			"metadata_id" => $metadata_id,
			"content_id" => $content_id,
			"content" => $data_value,
			"select_content_code" => $select_content_code			// 選択肢コード値の編集		EddyK
		);
		$metadata_content_id = $this->db->insertExecute("testee_metadata_content", $params, true, "metadata_content_id");
		if ($metadata_content_id === false) {
			return false;
		}

		if(($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE) && !empty($datas[$metadata_id])) {
			$params = array(
				"metadata_content_id" => $metadata_content_id,
				"upload_id" => $datas[$metadata_id]['upload_id'],
				"file_name" => $datas[$metadata_id]['file_name'],
				"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:"",
				"physical_file_name" => $datas[$metadata_id]['physical_file_name'],
				"room_id" => $this->room_id
			);
			$result = $this->db->insertExecute("testee_file", $params);
			if ($result === false) {
				return false;
			}
		}
		return true;
	}
}
?>