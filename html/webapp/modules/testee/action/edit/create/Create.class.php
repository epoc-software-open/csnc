<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Create extends Action
{
    // リクエストパラメータを受け取るため
    var $room_id = null;
	var $module_id = null;
    var $block_id = null;
    var $testee_name = null;
	var $contents_authority = null;
	var $vote_flag = null;
	var $comment_flag = null;
	var $mail_flag = null;
	var $mail_authority = null;
	var $mail_subject = null;
	var $mail_body = null;
	var $new_period = null;
	var $agree_flag = null;
	var $agree_mail_flag = null;
	var $agree_mail_subject = null;
	var $agree_mail_body = null;
	var $old_use = null;
	var $old_testee_id = null;
	var $kentai_switch = null;

	// バリデートによりセット
	var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $request = null;
	var $mdbView = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"testee_name" => $this->testee_name,
			"active_flag" => _ON,
			"contents_authority" => intval($this->contents_authority),
			"vote_flag" => intval($this->vote_flag),
			"comment_flag" => intval($this->comment_flag),
			"mail_flag" => intval($this->mail_flag),
			"mail_authority" => intval($this->mail_authority),
			"mail_subject" => $this->mail_subject,
			"mail_body" => $this->mail_body,
			"new_period" => intval($this->new_period),
			"agree_flag" => intval($this->agree_flag),
			"agree_mail_flag" => intval($this->agree_mail_flag),
			"agree_mail_subject" => $this->agree_mail_subject,
			"agree_mail_body" => $this->agree_mail_body,
			"kentai_switch" => $this->kentai_switch
		);
		$testee_id = $this->db->insertExecute("testee", $params, true, "testee_id");
    	if ($testee_id === false) {
    		return 'error';
    	}

    	if ($this->old_use == _ON) {
			$params = array($this->room_id, $this->old_testee_id);
			$sql = "SELECT title_metadata_id ".
					" FROM {testee} ".
					" WHERE room_id = ? ".
					" AND testee_id = ? ";
			$result = $this->db->execute($sql, $params);
			if ($result == false) {
				$this->db->addError();
				return 'error';
			}
			$title_metadata_id = $result[0]["title_metadata_id"];

			$metadatas =& $this->db->selectExecute("testee_metadata", array("testee_id"=>intval($this->old_testee_id)));
			if($metadatas === false) {
				$this->db->addError();
				return 'error';
			}

			// 日付関連チェック情報を取得	EddyK
			$date_condition =& $this->db->selectExecute("testee_date_condition", array("testee_id"=>intval($this->old_testee_id)));
			if($date_condition === false) {
				$this->db->addError();
				return 'error';
			}
			// 件数設定情報を取得			EddyK
			$counts =& $this->db->selectExecute("testee_counts", array("testee_id"=>intval($this->old_testee_id)));
			if($counts === false) {
				$this->db->addError();
				return 'error';
			}
    	} else {
			$metadatas = split("/", TESTEE_METADATA_PATTERN);
			$title_metadata_id = null;
    	}

		// 過去のデータベースを利用する場合、情報を複写	EddyK
		$datecond_meta_id = array();
		$old_counts_id = null;
		if ($this->old_use == _ON) {
			// 日付関連チェック情報を複写	EddyK
			if(count($date_condition) > 0){
				foreach($date_condition as $date_cond){
					$param = array(
						"testee_id" => $testee_id,
						"cond1_ew" => $date_cond["cond1_ew"],
						"cond1_fugo1" => $date_cond["cond1_fugo1"],
						"cond1_value1" => $date_cond["cond1_value1"],
						"cond1_fugo2" => $date_cond["cond1_fugo2"],
						"cond1_value2" => $date_cond["cond1_value2"],
						"cond2_ew" => $date_cond["cond2_ew"],
						"cond2_fugo1" => $date_cond["cond2_fugo1"],
						"cond2_value1" => $date_cond["cond2_value1"],
						"cond2_fugo2" => $date_cond["cond2_fugo2"],
						"cond2_value2" => $date_cond["cond2_value2"],
						"date1_id" => 0,
						"date2_id" => 0
					);
					// 追加
					$condition_id = $this->db->insertExecute("testee_date_condition", $param, false, "condition_id");
					if ($condition_id === false) {
						return 'error';
					}
					$datecond_meta_id[$date_cond["date1_id"]]["cond_id"] = $condition_id;
					$datecond_meta_id[$date_cond["date1_id"]]["date_no"] = 1;
					$datecond_meta_id[$date_cond["date2_id"]]["cond_id"] = $condition_id;
					$datecond_meta_id[$date_cond["date2_id"]]["date_no"] = 2;
				}
			}
			// 件数設定情報を複写	EddyK
			if(count($counts) > 0){
		    	$params = array(
					"testee_id" => $testee_id,
					"counts_id" => 0,
					"all_counts" => $counts[0]["all_counts"],
					"option_counts" => $counts[0]["option_counts"]
				);
				$result = $this->db->insertExecute("testee_counts", $params);
		    	if ($result === false) {
		    		return 'error';
		    	}
				$old_counts_id = $counts[0]["counts_id"];
			}
		}

        if ($this->old_use == _OFF){
            $hospitals = $this->mdbView->getRoomHospital($this->room_id);
        }
		foreach ($metadatas as $key => $metadata) {
			if ($this->old_use == _ON) {
				$params = array(
					"testee_id" => $testee_id,
					"name" => $metadata["name"],
					"varb_name" => $metadata["varb_name"],						// 変数名追加　EddyK
					"type" =>$metadata["type"],
					"require_flag" => $metadata["require_flag"],
					"list_flag" => $metadata["list_flag"],
					"detail_flag" => $metadata["detail_flag"],
					"name_flag" => $metadata["name_flag"],
					"search_flag" => $metadata["search_flag"],
					"sort_flag" => $metadata["sort_flag"],
					"file_password_flag" => $metadata["file_password_flag"],
					"file_count_flag" => $metadata["file_count_flag"],
					"display_pos" => $metadata["display_pos"],
					"display_sequence" => $metadata["display_sequence"],
					"select_content" => $metadata["select_content"],
					"select_content_code" => $metadata["select_content_code"],	// 選択肢コード値　EddyK
					"correct" => $metadata["correct"],							// 正解　EddyK
					"group_option" => $metadata["group_option"]					// グループ設定値　EddyK
				);
				$old_metadata_id = $metadata["metadata_id"];
			} else {
				$items = split(",", $metadata);
				// 初期設定のメタタイプが選択式の場合選択肢の設定を行う		EddyK
				$select_item = false;
				if($items[1] == TESTEE_META_TYPE_N_HOSPITAL || $items[1] == TESTEE_META_TYPE_N_SEX){
					$select_item = true;
				}
				$params = array(
					"testee_id" => $testee_id,
					"name" => $items[0],
					"type" => $items[1],
					"require_flag" => $items[2],
					"list_flag" => $items[2],
					"detail_flag" => $items[3],
					"name_flag" => $items[4],
					"search_flag" => $items[5],
					"sort_flag" => $items[6],
					"file_password_flag" => $items[1]==TESTEE_META_TYPE_FILE?$items[9]:0,
					"file_count_flag" => $items[1]==TESTEE_META_TYPE_FILE?1:0,
					"display_pos" => $items[7],
					"display_sequence" => $items[8],
//					"select_content" => $items[1]==TESTEE_META_TYPE_SECTION?$items[9]:""	// 標準では選択式（択一）の場合のみ
					"select_content" => $select_item==true?$items[9]:"",					// 選択肢の設定	EddyK
					"select_content_code" => $select_item==true?$items[10]:"",				// 選択肢コード値の設定	EddyK
					"varb_name" => $items[11]												// 変数名の設定	EddyK
				);
				// 施設名の場合、ルーム参加者の施設情報より選択肢を作成
                if($items[1] == TESTEE_META_TYPE_N_HOSPITAL && $hospitals){
                    $select_content = null;
                    $select_content_code = null;
                    // 設定するコード値を単純連番から施設コードに変更　※どう影響するのか忘れた
//                    $code = 0;
                    foreach ($hospitals as $row) {
						if($row["hospital"] != "|" && !is_null($row["hospital"])){
//							$code++;
	//                        $hospital=str_replace("|","",$row["hospital"]);
	//                        $select_content .= $hospital . "|";
	//                        $select_content_code .= $code . "|";
							$select_content .= $row["hospital"];
//							$select_content_code .= $code . "|";
							$select_content_code .= $row["hospital_code"] . "|";
						}
					}	
					$params["select_content"] = substr($select_content, 0, -1);
					$params["select_content_code"] = substr($select_content_code, 0, -1);
				}
				$old_metadata_id = -1;
			}

			$metadata_id = $this->db->insertExecute("testee_metadata", $params, true, "metadata_id");
			if ($metadata_id === false) {
	    		return 'error';
	    	}
	    	if(!isset($title_metadata_id) && $key == 0 || $title_metadata_id == $old_metadata_id) {
	    		$update_params = array(
	    			"title_metadata_id" => $metadata_id
	    		);
	    		$result = $this->db->updateExecute("testee", $update_params, array("testee_id" => $testee_id));
	    		if ($result === false) {
		    		return 'error';
		    	}
	    	}

			// メタチェック条件情報を複写	EddyK
			$meta_condition =& $this->db->selectExecute("testee_metadata_condition", array("metadata_id"=>$old_metadata_id));
			if($meta_condition === false) {
				$this->db->addError();
				return 'error';
			}
			if($meta_condition){
				$param = array(
					"metadata_id" => $metadata_id,
					"cond1_ew" => $meta_condition[0]["cond1_ew"],
					"cond1_fugo1" => $meta_condition[0]["cond1_fugo1"],
					"cond1_value1" => $meta_condition[0]["cond1_value1"],
					"cond1_fugo2" => $meta_condition[0]["cond1_fugo2"],
					"cond1_value2" => $meta_condition[0]["cond1_value2"],
					"cond2_ew" => $meta_condition[0]["cond2_ew"],
					"cond2_fugo1" => $meta_condition[0]["cond2_fugo1"],
					"cond2_value1" => $meta_condition[0]["cond2_value1"],
					"cond2_fugo2" => $meta_condition[0]["cond2_fugo2"],
					"cond2_value2" => $meta_condition[0]["cond2_value2"]
				);
				$result = $this->db->insertExecute("testee_metadata_condition", $param, true);
				if ($result === false) {
					return 'error';
				}
			}

			// コメントを複写	EddyK
			$meta_comment =& $this->db->selectExecute("testee_metadata_comment", array("metadata_id"=>$old_metadata_id));
			if($meta_comment === false) {
				$this->db->addError();
				return 'error';
			}
			if($meta_comment){
				$param = array(
					"metadata_id" => $metadata_id,
					"comment" => $meta_comment[0]["comment"]
				);
				$result = $this->db->insertExecute("testee_metadata_comment", $param, true);
				if ($result === false) {
					return 'error';
				}
			}

			// 日付関連チェック情報の日付項目IDの更新	EddyK
	    	if($this->old_use == _ON && count($datecond_meta_id) > 0 ) {
		    	if(array_key_exists($old_metadata_id, $datecond_meta_id)) {
					if($datecond_meta_id[$old_metadata_id]["date_no"] == 1){
						$field_name = "date1_id";
					} else {
						$field_name = "date2_id";
					}
		    		$update_params = array(
		    			$field_name => $metadata_id
		    		);
		    		$result = $this->db->updateExecute("testee_date_condition", $update_params, array("condition_id" => $datecond_meta_id[$old_metadata_id]["cond_id"]));
		    		if ($result === false) {
			    		return 'error';
			    	}
		    	}
	    	}
			// 件数設定情報の件数指定選択項目IDの更新	EddyK
	    	if($this->old_use == _ON && $old_counts_id) {
		    	if($old_counts_id == $old_metadata_id) {
		    		$update_params = array(
		    			"counts_id" => $metadata_id
		    		);
		    		$result = $this->db->updateExecute("testee_counts", $update_params, array("testee_id" => $testee_id));
		    		if ($result === false) {
			    		return 'error';
			    	}
		    	}
	    	}
		}

		$count = $this->db->countExecute("testee_block", array("block_id"=>$this->block_id));
    	if($count === false) {
    		return 'error';
    	}

    	$params = array(
			"testee_id" => $testee_id,
			"visible_item" => $this->mdb_obj['visible_item'],
    		"default_sort" => $this->mdb_obj['default_sort']
		);
		if ($count == 0) {
	    	$result = $this->db->insertExecute("testee_block", array_merge(array("block_id" => $this->block_id), $params), true);
		}else {
	    	$result = $this->db->updateExecute("testee_block", $params,  array("block_id"=>$this->block_id), true);
    	}
    	if ($result === false) {
    		return 'error';
    	}
    	$this->request->setParameter("testee_id", $testee_id);
        return 'success';
    }
}
?>