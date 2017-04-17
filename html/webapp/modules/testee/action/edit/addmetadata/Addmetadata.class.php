<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Addmetadata extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $metadata_id = null;
	var $varb_name = null;				// 変数名				EddyK
	var $name = null;
	var $type = null;

	var $title_metadata_flag = null;
	var $require_flag = null;
	var $list_flag = null;
	var $detail_flag = null;
	var $search_flag = null;
	var $name_flag = null;
	var $sort_flag = null;
	var $file_password_flag = null;
	var $file_count_flag = null;

	var $options = null;

	var $select_content_code = null;	// 選択肢コード値		EddyK
	var $correct = null;				// ラジオボタンの正解	EddyK
	var $group = null;					// グループ				EddyK

	var $decoration_bold = null;        // 太字
	var $decoration_underline = null;   // 下線
	var $decoration_red = null;         // 赤字

	var $view_unit = null;              // 表示単位

	var $mail_send_days = null;         // メール日付
	var $mail_body = null;              // メール本文
	var $mail_send_target = null;       // メール送信先
	var $add_mail_send_metadata_id = null;  // 登録時メールのmetadata_id


// エラーチェック用条件　EddyK
	var $cond1_ew     = null;
	var $cond1_fugo1  = null;
	var $cond1_value1 = null;
	var $cond1_fugo2  = null;
	var $cond1_value2 = null;
	var $cond2_ew     = null;
	var $cond2_fugo1  = null;
	var $cond2_value1 = null;
	var $cond2_fugo2  = null;
	var $cond2_value2 = null;

	var $comment = null;				// コメント				EddyK

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $mdbAction = null;

	// バリデートによりセットするため
	var $metadata = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{

		// 装飾系パラメータの初期設定(チェックボックスのため、チェックしないと空になっているので、0 にする)
		if ( $this->decoration_bold == "" ) {
			$this->decoration_bold = 0;
		}
		if ( $this->decoration_underline == "" ) {
			$this->decoration_underline = 0;
		}
		if ( $this->decoration_red == "" ) {
			$this->decoration_red = 0;
		}

		$metadata_id = intval($this->metadata_id);
		$set_options = "";
		$set_correct = "";
		$set_select_content_code = "";
		if(isset($this->options[1]) 
			&& ($this->type == TESTEE_META_TYPE_SECTION 
				|| $this->type == TESTEE_META_TYPE_MULTIPLE 
				|| $this->type == TESTEE_META_TYPE_N_RADIO	 	// 追加メタタイプ EddyK
				|| $this->type == TESTEE_META_TYPE_N_GROUP	 	// 追加メタタイプ EddyK
				|| $this->type == TESTEE_META_TYPE_N_HOSPITAL 	// 追加メタタイプ EddyK
				|| $this->type == TESTEE_META_TYPE_N_SEX )) {	// 追加メタタイプ EddyK
			foreach($this->options[1] as $key => $options) {
				$set_options .= $options."|";
			}
			$set_options = substr($set_options, 0, -1);
		}

// 選択肢の正解の編集	EddyK
		if(isset($this->options[1]) 
			&& $this->type == TESTEE_META_TYPE_N_RADIO) {
			foreach($this->options[1] as $key => $options) {
				if(isset($this->correct[1])) {
					if(array_key_exists ($key , $this->correct[1])){
						$set_correct .= $this->correct[1][$key];
					}
					$set_correct .= "|";
				}
			}
			if(isset($this->correct[1])) {
				$set_correct = substr($set_correct, 0, -1);
			}
		}

// 選択肢コード値の編集		EddyK
		if(isset($this->select_content_code[1]) 
			&& ($this->type == TESTEE_META_TYPE_N_RADIO
				|| $this->type == TESTEE_META_TYPE_N_GROUP
				|| $this->type == TESTEE_META_TYPE_N_HOSPITAL
				|| $this->type == TESTEE_META_TYPE_N_SEX )) {
			foreach($this->select_content_code[1] as $key => $select_content_code) {
				$set_select_content_code .= $select_content_code."|";
			}
			$set_select_content_code = substr($set_select_content_code, 0, -1);
		}

// はい・いいえタイプの選択肢・正解・コード値の編集	EddyK
		if(isset($this->options[2]) 
			&& $this->type == TESTEE_META_TYPE_N_YESNO) {
			foreach($this->options[2] as $key => $options) {
				$set_options .= $options."|";							// 選択肢
				if(isset($this->correct[2])) {
					if(array_key_exists ($key , $this->correct[2])){
						$set_correct .= $this->correct[2][$key];		// 正解
					}
					$set_correct .= "|";
				}
			}
			$set_options = substr($set_options, 0, -1);
			if(isset($this->correct[2])) {
				$set_correct = substr($set_correct, 0, -1);
			}
			foreach($this->select_content_code[2] as $key => $select_content_code) {
				$set_select_content_code .= $select_content_code."|";	// コード値
			}
			$set_select_content_code = substr($set_select_content_code, 0, -1);
		}

// 該当せず・該当タイプの選択肢・正解・コード値の編集	EddyK
		if(isset($this->options[3]) 
			&& $this->type == TESTEE_META_TYPE_N_APPLICABLE) {
			foreach($this->options[3] as $key => $options) {
				$set_options .= $options."|";							// 選択肢
				if(isset($this->correct[3])) {
					if(array_key_exists ($key , $this->correct[3])){
						$set_correct .= $this->correct[3][$key];		// 正解
					}
					$set_correct .= "|";
				}
			}
			$set_options = substr($set_options, 0, -1);
			if(isset($this->correct[3])) {
				$set_correct = substr($set_correct, 0, -1);
			}
			foreach($this->select_content_code[3] as $key => $select_content_code) {
				$set_select_content_code .= $select_content_code."|";	// コード値
			}
			$set_select_content_code = substr($set_select_content_code, 0, -1);
		}

		if($this->title_metadata_flag == _ON) {
			$this->require_flag = _ON;
		}
		if($this->list_flag != _ON) {
			$this->sort_flag = _OFF;
		}
		if($this->type != TESTEE_META_TYPE_FILE) {
			$this->file_password_flag = _OFF;
			$this->file_count_flag = _OFF;
		}
		if($this->type == TESTEE_META_TYPE_AUTONUM || $this->type == TESTEE_META_TYPE_INSERT_TIME || $this->type == TESTEE_META_TYPE_UPDATE_TIME) {
			$this->require_flag = _OFF;
		}
		if ($this->type == TESTEE_META_TYPE_IMAGE) {
			$this->name_flag = _OFF;
		}
		if($this->type == TESTEE_META_TYPE_FILE || $this->type == TESTEE_META_TYPE_IMAGE ||
				$this->type == TESTEE_META_TYPE_DATE || $this->type == TESTEE_META_TYPE_INSERT_TIME || $this->type == TESTEE_META_TYPE_UPDATE_TIME || 
			// 追加メタタイプの追加 EddyK
				$this->type == TESTEE_META_TYPE_N_NUMERIC || $this->type == TESTEE_META_TYPE_N_DATE || $this->type == TESTEE_META_TYPE_N_COMMENT || 
				$this->type == TESTEE_META_TYPE_N_BIRTHDAY || $this->type == TESTEE_META_TYPE_N_STATURE || $this->type == TESTEE_META_TYPE_N_WEIGHT || 
				$this->type == TESTEE_META_TYPE_N_CREATININE || $this->type == TESTEE_META_TYPE_N_BSA || $this->type == TESTEE_META_TYPE_N_CCR) {
			$this->search_flag = _OFF;
		}
		// グループ設定されているメタは、必須にしない EddyK
		if($this->group != "" && $this->group != null) {
			$this->require_flag = _OFF;
		}

		$beforeType = null;
		$afterType = $this->type;
		if (!empty($metadata_id)) {
			// 編集
			$where_params = array("metadata_id" => $metadata_id);
			$metadata_before_update = $this->db->selectExecute("testee_metadata", $where_params);
			if($metadata_before_update === false || !isset($metadata_before_update[0])) {
				return 'error';
			}
			$beforeType = $metadata_before_update[0]['type'];

			if ($beforeType != TESTEE_META_TYPE_WYSIWYG
					&& $afterType == TESTEE_META_TYPE_WYSIWYG) {
				$sql = "SELECT metadata_content_id, "
							. "content "
						. "FROM {testee_metadata_content} "
						. "WHERE metadata_id = ?";
				$result = $this->db->execute($sql, $where_params, null, null, true, array($this, '_escapeHtml'));
				if ($result === false) {
					return 'error';
				}
			}

			if ($beforeType != TESTEE_META_TYPE_DATE
					&& $afterType == TESTEE_META_TYPE_DATE) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}

			//　グループタイプの場合、選択肢コード値を保存しておく
			if ($beforeType == TESTEE_META_TYPE_N_GROUP) {
				$result = $this->mdbView->getGroup($this->testee_id);
				if ($result === false) {
					return 'error';
				}
				if(count($result) > 0){
					$group_codes_old = explode("|", $result[0]["select_content_code"]);
				}
			}
// 新しいメタタイプは、タイプ変更あった場合、データをクリアする 開始 EddyK
			if ($beforeType != TESTEE_META_TYPE_N_RADIO
					&& $afterType == TESTEE_META_TYPE_N_RADIO) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_YESNO
					&& $afterType == TESTEE_META_TYPE_N_YESNO) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_APPLICABLE
					&& $afterType == TESTEE_META_TYPE_N_APPLICABLE) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_NUMERIC
					&& $afterType == TESTEE_META_TYPE_N_NUMERIC) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_DATE
					&& $afterType == TESTEE_META_TYPE_N_DATE) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_COMMENT
					&& $afterType == TESTEE_META_TYPE_N_COMMENT) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_HOSPITAL
					&& $afterType == TESTEE_META_TYPE_N_HOSPITAL) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_BIRTHDAY
					&& $afterType == TESTEE_META_TYPE_N_BIRTHDAY) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_SEX
					&& $afterType == TESTEE_META_TYPE_N_SEX) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_STATURE
					&& $afterType == TESTEE_META_TYPE_N_STATURE) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_WEIGHT
					&& $afterType == TESTEE_META_TYPE_N_WEIGHT) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_CREATININE
					&& $afterType == TESTEE_META_TYPE_N_CREATININE) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_BSA
					&& $afterType == TESTEE_META_TYPE_N_BSA) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_CCR
					&& $afterType == TESTEE_META_TYPE_N_CCR) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
			if ($beforeType != TESTEE_META_TYPE_N_GROUP
					&& $afterType == TESTEE_META_TYPE_N_GROUP) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('testee_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}
// 新しいメタタイプは、タイプ変更あった場合、データをクリアする 終了 EddyK

			$param = array(
				"varb_name" => $this->varb_name,						//	変数名			EddyK
				"name" => $this->name,
				"type" => $this->type,
				"select_content" => $set_options,
				"select_content_code" => $set_select_content_code,		//	選択肢コード値	EddyK
				"correct" => $set_correct,								//	正解			EddyK
				"group_option" => $this->group,							//	グループ設定	EddyK
				"require_flag" => intval($this->require_flag),
				"list_flag" => intval($this->list_flag),
				"detail_flag" => intval($this->detail_flag),
				"search_flag" => intval($this->search_flag),
				"name_flag" => intval($this->name_flag),
				"sort_flag" => intval($this->sort_flag),
				"file_password_flag" => intval($this->file_password_flag),
				"file_count_flag" => intval($this->file_count_flag),
				"decoration_bold" => intval($this->decoration_bold),
				"decoration_underline" => intval($this->decoration_underline),
				"decoration_red" => intval($this->decoration_red),
				"view_unit" => $this->view_unit,
				"mail_send_days" => intval($this->mail_send_days),
				"mail_body" => $this->mail_body,
				"mail_send_target" => intval($this->mail_send_target)
			);
			//更新
			$result = $this->db->updateExecute("testee_metadata", $param, $where_params, true);
			if ($result === false) {
				return 'error';
			}
			// 更新前がグループタイプの場合、変更により選択肢コードが無くなったものをグループ設定されている内容をクリアする EddyK
			if($beforeType == TESTEE_META_TYPE_N_GROUP){
				$group_codes_new = explode("|", $set_select_content_code);
				foreach($group_codes_old as $old_code){
					if(array_search($old_code, $group_codes_new) === false){
						$where_params = array("testee_id" => $this->testee_id, "group_option" => $old_code);
						$param = array(
							"group_option" => null
						);
						$result = $this->db->updateExecute("testee_metadata", $param, $where_params, true);
						if ($result === false) {
							return 'error';
						}
					}
				}
			}
			// 件数制限設定で使用されているメタの場合、変更により選択肢コードが無くなったものを件数設定の内容をクリアする EddyK
			$counts_data = $this->mdbView->getCounts($this->testee_id);
			if ($counts_data === false) {
				return  'error';
			}
			if ($counts_data) {
				if($counts_data["counts_id"] == $metadata_id){
					if($set_select_content_code){
						$option_codes = explode("|", $set_select_content_code);
						if($counts_data["option_counts"]){
							$counts_option = json_decode($counts_data["option_counts"],true);
						}
						$counts_array = array();
						foreach($option_codes as $code){
							$code_exist = array_key_exists($code, $counts_option);
							if($code_exist){
								$count_array[$code] = $counts_option[$code];
							} else {
								$count_array[$code] = "";
							}
						}
						$set_counts = json_encode($count_array);
						$where_counts_params = array(
							"testee_id" => $this->testee_id,
							"counts_id" => $metadata_id
						);
						$param = array(
							"option_counts" => $set_counts
						);
						$result = $this->db->updateExecute("testee_counts", $param, $where_counts_params, true);
						if ($result === false) {
							return 'error';
						}
					} else {
						// タイプ変更で選択式でなくなったとき件数制限設定の選択肢別件数をクリアする
						$where_counts_params = array(
							"testee_id" => $this->testee_id,
							"counts_id" => $metadata_id
						);
						$param = array(
							"counts_id" => null,
							"option_counts" => null
						);
						$result = $this->db->updateExecute("testee_counts", $param, $where_counts_params, true);
						if ($result === false) {
							return 'error';
						}
					}
				}
			}
			// 変更により日付タイプでなくなった場合、関連日付チェック設定で使用されているメタの場合、該当のチェックを削除する EddyK
			if (($beforeType == TESTEE_META_TYPE_DATE 
				|| $beforeType == TESTEE_META_TYPE_N_BIRTHDAY 
				|| $beforeType == TESTEE_META_TYPE_N_DATE)
					&& ($afterType != TESTEE_META_TYPE_DATE 
						&& $afterType != TESTEE_META_TYPE_N_BIRTHDAY 
						&& $afterType != TESTEE_META_TYPE_N_DATE)) {
				$datecheck_datas = $this->mdbView->getDateCheck($this->testee_id);
				if ($datecheck_datas === false) {
					return  'error';
				}
				if ($datecheck_datas) {
					foreach($datecheck_datas as $datecheck_data){
						if($datecheck_data["date1_id"] == $metadata_id || $datecheck_data["date2_id"] == $metadata_id){
							$where_params = array(
								"condition_id" => $datecheck_data["condition_id"]
							);
							$result = $this->db->deleteExecute("testee_date_condition", $where_params);
							if ($result === false) {
								return 'error';
							}
						}
					}
				}
			}
		} else {
			$display_sequence = $this->db->maxExecute("testee_metadata", "display_sequence", array("testee_id"=>intval($this->testee_id)));
			$param = array(
				"testee_id" => $this->testee_id,
				"varb_name" => $this->varb_name,						//	変数名			EddyK
				"name" => $this->name,
				"type" => $this->type,
				"display_pos" => TESTEE_DEFAULT_DISPLAY_POSITION,
				"select_content" => $set_options,
				"select_content_code" => $set_select_content_code,		//	選択肢コード値	EddyK
				"correct" => $set_correct,								//	正解			EddyK
				"group_option" => $this->group,							//	グループ設定	EddyK
				"require_flag" => intval($this->require_flag),
				"list_flag" => intval($this->list_flag),
				"detail_flag" => intval($this->detail_flag),
				"search_flag" => intval($this->search_flag),
				"name_flag" => intval($this->name_flag),
				"sort_flag" => intval($this->sort_flag),
				"file_password_flag" => intval($this->file_password_flag),
				"file_count_flag" => intval($this->file_count_flag),
				"decoration_bold" => intval($this->decoration_bold),
				"decoration_underline" => intval($this->decoration_underline),
				"decoration_red" => intval($this->decoration_red),
				"view_unit" => $this->view_unit,
				"mail_send_days" => $this->mail_send_days,
				"mail_body" => $this->mail_body,
				"mail_send_target" => $this->mail_send_target,
				"display_sequence" => $display_sequence + 1
			);
			// 追加
			$metadata_id = $this->db->insertExecute("testee_metadata", $param, true, "metadata_id");
			if ($metadata_id === false) {
				return 'error';
			}
		}

		if ($beforeType != TESTEE_META_TYPE_AUTONUM
				&& $afterType == TESTEE_META_TYPE_AUTONUM) {
			$sql = "SELECT MC.content_id AS content_id, ".$metadata_id." AS metadata_id, MMC.metadata_content_id AS metadata_content_id, MC.temporary_flag, MC.agree_flag " .
					" FROM {testee_content} MC" .
					" LEFT JOIN {testee_metadata_content} MMC" .
						" ON (MC.content_id = MMC.content_id AND MMC.metadata_id = ?)" .
					" WHERE MC.testee_id = ?".
					" ORDER BY MC.insert_time";

			$whereParams = array(
				'metadata_id' => $metadata_id,
				'testee_id' => $this->testee_id,
			);
			$callbackFunc = array($this, "_fetchCallback");
			$result = $this->db->execute($sql, $whereParams, null, null, true, $callbackFunc);
			if ($result === false) {
				return 'error';
			}
		}

		if($this->title_metadata_flag == _ON) {
			$update_params = array(
				"title_metadata_id" => $metadata_id
			);
			$result = $this->db->updateExecute("testee", $update_params, array("testee_id" => $this->testee_id), true);
			if ($result === false) {
				return 'error';
			}
		}

		// 登録時メールの更新 by nagahara@opensource-workshop.jp
		$testee_obj = $this->mdbView->getMdbById( $this->testee_id );

		// もともと、自分のメタデータが指定されていた場合は、チェックボックスの内容で更新(空ならクリア)
		if ( $testee_obj["add_mail_send_metadata_id"] == $metadata_id ) {

			$update_params = array(
				"add_mail_send_metadata_id" => $this->add_mail_send_metadata_id
			);
			$result = $this->db->updateExecute("testee", $update_params, array("testee_id" => $this->testee_id), true);
			if ($result === false) {
				return 'error';
			}
		}
		// それ以外なら、チェックボックスの内容が入っていたら更新
		else if ( !empty( $this->add_mail_send_metadata_id ) ) {

			$update_params = array(
				"add_mail_send_metadata_id" => $this->add_mail_send_metadata_id
			);
			$result = $this->db->updateExecute("testee", $update_params, array("testee_id" => $this->testee_id), true);
			if ($result === false) {
				return 'error';
			}
		}

		// メール送信先が「試験参加のユーザ全員」なら、個別設定を削除 by nagahara@opensource-workshop.jp
		if ( $this->mail_send_target == "0" ) {
			$this->mdbAction->deleteMailUsers( $metadata_id );
		}


// メタ設定での条件式の更新 EddyK
		$old_condition = false;
		$result = $this->mdbView->getCondition($metadata_id);
		if ($result === false) {
			return 'error';
		}
		if(count($result) > 0) $old_condition = true;

		if($this->cond1_ew != ""){
		// 条件式の入力あり
			$param = array(
				"cond1_ew" => $this->cond1_ew,
				"cond1_fugo1" => $this->cond1_fugo1,
				"cond1_value1" => $this->cond1_value1,
				"cond1_fugo2" => $this->cond1_fugo2,
				"cond1_value2" => $this->cond1_value2,
				"cond2_ew" => $this->cond2_ew,
				"cond2_fugo1" => $this->cond2_fugo1,
				"cond2_value1" => $this->cond2_value1,
				"cond2_fugo2" => $this->cond2_fugo2,
				"cond2_value2" => $this->cond2_value2
			);
			if($old_condition){
			// 既存の条件式設定あり
				//更新
				$result = $this->db->updateExecute("testee_metadata_condition", $param, $where_params, true);
			} else {
			// 既存の条件式設定なし
				// 追加
				$param["metadata_id"] = $metadata_id;
				$result = $this->db->insertExecute("testee_metadata_condition", $param, true);
			}
			if ($result === false) {
				return 'error';
			}
		} else {
		// 条件式の入力なし
			if($old_condition){
			// 既存の条件式設定あり
				$result = $this->db->deleteExecute("testee_metadata_condition", $where_params);
				if ($result === false) {
					return 'error';
				}
			}
		}

		if($old_condition){
		// 条件式が必要ないタイプの場合は、既存の条件式設定を削除する
			if($afterType != TESTEE_META_TYPE_N_DATE && $afterType != TESTEE_META_TYPE_N_BIRTHDAY &&
				$afterType != TESTEE_META_TYPE_N_NUMERIC && $afterType != TESTEE_META_TYPE_N_STATURE &&
				$afterType != TESTEE_META_TYPE_N_WEIGHT && $afterType != TESTEE_META_TYPE_N_CREATININE &&
				$afterType != TESTEE_META_TYPE_N_BSA && $afterType != TESTEE_META_TYPE_N_CCR ){
				$result = $this->db->deleteExecute("testee_metadata_condition", $where_params);
					if ($result === false) {
						return 'error';
					}
			}
		}

// メタ設定でのコメントの更新 EddyK
		$old_comment = false;
		$result = $this->mdbView->getComment($metadata_id);
		if ($result === false) {
			return 'error';
		}
		if(count($result) > 0) $old_comment = true;

		if($this->comment != ""){
		// コメントの入力あり
			$param = array(
				"comment" => $this->comment
			);
			if($old_comment){
			// 既存のコメントあり
				//更新
				$result = $this->db->updateExecute("testee_metadata_comment", $param, $where_params, true);
			} else {
			// 既存のコメントなし
				// 追加
				$param["metadata_id"] = $metadata_id;
				$result = $this->db->insertExecute("testee_metadata_comment", $param, true);
			}
			if ($result === false) {
				return 'error';
			}
		} else {
		// コメントの入力なし
			if($old_comment){
			// 既存のコメントあり
				$result = $this->db->deleteExecute("testee_metadata_comment", $where_params);
				if ($result === false) {
					return 'error';
				}
			}
		}

		if($old_comment){
		// コメントが必要ないタイプの場合は、既存のコメントを削除する
			if($afterType != TESTEE_META_TYPE_N_COMMENT){
				$result = $this->db->deleteExecute("testee_metadata_comment", $where_params);
					if ($result === false) {
						return 'error';
					}
			}
		}

		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function _fetchCallback(&$recordSet)
	{
		$i = 0;
		while ($row = $recordSet->fetchRow()) {
			if ($row["temporary_flag"] == TESTEE_STATUS_BEFORE_RELEASED_VALUE) {
				$param = array(
					"metadata_id" => $row["metadata_id"],
					"content_id" => $row["content_id"],
					"content" => ""
				);
			} else {
				$i++;
				$param = array(
					"metadata_id" => $row["metadata_id"],
					"content_id" => $row["content_id"],
					"content" => sprintf(TESTEE_META_AUTONUM_FORMAT, $i)
				);
			}

			if (!empty($row["metadata_content_id"])) {
				//更新
				$whereParams = array(
					'metadata_content_id' => $row["metadata_content_id"]
				);
				$result = $this->db->updateExecute("testee_metadata_content", $param, $whereParams, true);
				if ($result === false) {
					return false;
				}
			} else {
				// 追加
				$result = $this->db->insertExecute("testee_metadata_content", $param, true, "metadata_content_id");
				if ($result === false) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 既存コンテンツをHTMLエスケープする
	 *
	 * @param array $recordSet コンテンツデータADORecordSet
	 * @return boolean true or false
	 * @access public
	 */
	function _escapeHtml(&$recordSet)
	{
		$sql = "UPDATE {testee_metadata_content} "
				. "SET "
				. "content = ? "
				. "WHERE metadata_content_id = ?";

		while ($row = $recordSet->fetchRow()) {
			$bindValues = array(
					htmlspecialchars($row['content']),
					$row['metadata_content_id']
				);
			$result = $this->db->execute($sql, $bindValues);
			if ($result === false) {
				return false;
			}
		}

		return true;
	}
}
?>
