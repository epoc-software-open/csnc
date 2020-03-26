<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var 当モジュールView
	 *
	 * @access	private
	 */
	var $_mdbView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Testee_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$commonMain =& $this->_container->getComponent("commonMain");
		$this->_uploads =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$this->_mdbView = $this->_container->getComponent("mdbView");		// 18.10.05 add by makino@opensource-workshop.jp
	}

	function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
        $_csv_line = preg_replace('/(?:\r\n|[\r\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
            $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }

	/**
	 * 汎用データベース削除処理
	 * @param  int    testee_id
	 * @return boolean
	 * @access public
	 */
	function deleteMdb($testee_id) {
		if (empty($testee_id)) {
    		return false;
    	}
    	$params = array(
			"testee_id" => $testee_id
		);

    	$result = $this->_db->selectExecute("testee", $params);
    	if($result === false) {
    		return false;
    	}

    	if(isset($result[0])) {
		// コンテンツの削除
    		$contents = $this->_db->selectExecute("testee_content", $params);
    		if($contents === false) {
    			return false;
    		}
    		if(!empty($contents)) {
    			foreach($contents as $key => $val) {
    				$result = $this->deleteContent($val['content_id']);
    				if ($result === false) {
    					return false;
    				}
    			}
    		}

		// 割付層テーブルの削除
			$result = $this->_db->deleteExecute("testee_allocation_conbination", $params);
			if($result === false) {
				return false;
			}
		// 割付ブロックデータテーブルの削除
			$result = $this->_db->deleteExecute("testee_allocation_block", $params);
			if($result === false) {
				return false;
			}
		// 割付シードテーブルの削除
			$result = $this->_db->deleteExecute("testee_allocation_seed", $params);
			if($result === false) {
				return false;
			}
		// 割付情報参照ユーザーテーブルの削除
			$result = $this->_db->deleteExecute("testee_allocation_viewuser", $params);
			if($result === false) {
				return false;
			}
		// 割付可変ブロックテーブルの削除
			$result = $this->_db->deleteExecute("testee_allocation_variable_block", $params);
			if($result === false) {
				return false;
			}
		// 割付因子テーブルの削除
			$testee_adjustments = $this->_mdbView->getSelectedAllocationItem( $testee_id );
			if( $testee_adjustments !== false && count( $testee_adjustments ) > 0 )
			{
				foreach( $testee_adjustments as $record )
				{
					$result = $this->delAllocation( $record[ "metadata_id" ] );
					if( $result === false ) return false;
				}
			}
		// 割付情報テーブルの削除
			$result = $this->_db->deleteExecute("testee_allocate", $params);
			if($result === false) {
				return false;
			}
		// 割付群テーブルの削除
			$result = $this->_db->deleteExecute("testee_allocation_group", $params);
			if($result === false) {
				return false;
			}
		// 割付シミュレーション結果テーブルの削除
			$result = $this->deleteAllocationSimResult( $testee_id );
			if( $result === false ) return false;
		// 割付シミュレーション設定テーブルの削除
			$result = $this->deleteAllocationSimsetting( $testee_id );
			if( $result === false ) return false;
		// 


		// 日付関連チェックの削除	EddyK
    		$result = $this->_db->deleteExecute("testee_date_condition", $params);
	    	if($result === false) {
	    		return false;
	    	}
		// 件数設定の削除	EddyK
    		$result = $this->_db->deleteExecute("testee_counts", $params);
	    	if($result === false) {
	    		return false;
	    	}
		// 試験毎権限設定の削除		EddyK
    		$result = $this->_db->deleteExecute("testee_tedcauthority", $params);
	    	if($result === false) {
	    		return false;
	    	}
		// メタ条件・メタコメントの削除		EddyK
	    	$metadatas = $this->_db->selectExecute("testee_metadata", $params);
	    	if($metadatas === false) {
	    		return false;
	    	}
    		if(!empty($metadatas)) {
    			foreach($metadatas as $key => $metadata) {
			     	$meta_params = array(
						"metadata_id" => $metadata['metadata_id']
					);
					// メタ条件の削除		EddyK
			    	$result = $this->_db->deleteExecute("testee_metadata_condition", $meta_params);
			    	if($result === false) {
			    		return false;
			    	}
	    			if($metadata['type'] == TESTEE_META_TYPE_N_COMMENT) {
						// メタコメントの削除		EddyK
	    				$result = $this->_db->deleteExecute("testee_metadata_comment", $meta_params);
						if($result === false) {
		    				return false;
		    			}
	    			}
    			}
    		}
    	
		// メタデータの削除
    		$result = $this->_db->deleteExecute("testee_metadata", $params);
	    	if($result === false) {
	    		return false;
	    	}

		// 汎用DBの削除
	    	$result = $this->_db->deleteExecute("testee", $params);
	    	if($result === false) {
	    		return false;
	    	}

			//--URL短縮形関連 Start--
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->deleteUrlByContents($testee_id);
			if ($result === false) {
				return false;
			}
			//--URL短縮形関連 End--
    	}

    	return true;
	}

	/**
	 * メタデータ削除処理
	 * @param  int    metadata_id
	 * @return boolean
	 * @access public
	 */
	function deleteMetadata($metadata_id) {
		if (empty($metadata_id)) {
    		return false;
    	}
    	$params = array(
			"metadata_id" => $metadata_id
		);

    	$metadata = $this->_db->selectExecute("testee_metadata", $params);
    	if($metadata === false) {
    		return false;
    	}

    	if(isset($metadata[0])) {
    		$metadata_contents = $this->_db->selectExecute("testee_metadata_content", $params);
    		if($metadata_contents === false) {
    			return false;
    		}
    		if(!empty($metadata_contents)) {
    			foreach($metadata_contents as $key => $val) {
    				$result = $this->deleteMetadataContent($val['metadata_content_id']);
    				if ($result === false) {
    					return false;
    				}
    			}
    		}

	    	$result = $this->_db->deleteExecute("testee_metadata", $params);
	    	if($result === false) {
	    		return false;
	    	}
    	}

    	return true;
	}

	/**
	 * コンテンツ削除処理
	 * @param  int    content_id
	 * @return boolean
	 * @access public
	 */
	function deleteContent($content_id) {
		if (empty($content_id)) {
    		return false;
    	}
    	$params = array(
			"content_id" => $content_id
		);

    	$result = $this->_db->selectExecute("testee_content", $params);
    	if($result === false) {
    		return false;
    	}

    	if(isset($result[0])) {
    		$metadata_contents = $this->_db->selectExecute("testee_metadata_content", $params);
    		if($metadata_contents === false) {
    			return false;
    		}
    		if(!empty($metadata_contents)) {
    			foreach($metadata_contents as $key => $val) {
    				$result = $this->deleteMetadataContent($val['metadata_content_id']);
    				if ($result === false) {
    					return false;
    				}
    			}
    		}

	    	$result = $this->_db->deleteExecute("testee_content", $params);
	    	if($result === false) {
	    		return false;
	    	}

	    	$result = $this->_db->deleteExecute("testee_comment", $params);
	    	if($result === false) {
	    		return false;
	    	}

			// 割付データも削除
			$result = $this->_db->deleteExecute("testee_content_group", $params);
			if($result === false) {
				return false;
			}

			// 進捗データも削除
			$result = $this->_db->deleteExecute("testee_content_status", $params);
			if($result === false) {
				return false;
			}
    	}

		//--新着情報関連 Start--
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($content_id);
    	if ($result === false) {
			return false;
		}
		//--新着情報関連 End--

    	return true;
	}

	/**
	 * コンテンツのデータ削除処理
	 * @param  int    metadata_content_id
	 * @return boolean
	 * @access public
	 */
	function deleteMetadataContent($metadata_content_id) {
		if (empty($metadata_content_id)) {
    		return false;
    	}
    	$params = array(
			"metadata_content_id" => $metadata_content_id
		);

    	$data = $this->_db->selectExecute("testee_metadata_content", $params);
    	if($data === false) {
    		return false;
    	}

    	if(isset($data[0])) {
    		$metadata= $this->_db->selectExecute("testee_metadata", array("metadata_id" => $data[0]['metadata_id']));
    		if($metadata === false) {
    			return false;
    		}

    		if(isset($metadata[0])) {
    			if($metadata[0]['type'] == TESTEE_META_TYPE_FILE || $metadata[0]['type'] == TESTEE_META_TYPE_IMAGE) {
    				$result = $this->_db->deleteExecute("testee_file", $params);
					if($result === false) {
	    				return false;
	    			}

    				//画像とファイル削除
					if(!empty($data[0]['content'])) {
						$pathList = explode("&", $data[0]['content']);
						if(isset($pathList[1])) {
							$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
							if(!empty($upload_id)) {
								$result = $this->_uploads->delUploadsById($upload_id);
								if ($result === false) {
									return false;
								}
							}
						}
					}
    			}
    		}

	    	$result = $this->_db->deleteExecute("testee_metadata_content", $params);
	    	if($result === false) {
	    		return false;
	    	}
    	}

    	return true;
	}

	/**
	 * コンテンツ番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateContentSequence() {
		$request =& $this->_container->getComponent("Request");

		$dragSequence = $request->getParameter("drag_sequence");
		$dropSequence = $request->getParameter("drop_sequence");

		$params = array(
			$request->getParameter("testee_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {testee_content} ".
					"SET display_sequence = display_sequence + 1 ".
					"WHERE testee_id = ? ".
					"AND display_sequence < ? ".
					"AND display_sequence > ?";
        } else {
        	$sql = "UPDATE {testee_content} ".
					"SET display_sequence = display_sequence - 1 ".
					"WHERE testee_id = ? ".
					"AND display_sequence > ? ".
					"AND display_sequence <= ?";
        }

		$result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		if ($dragSequence > $dropSequence) {
			$dropSequence++;
		}
		$params = array(
			$dropSequence,
			$request->getParameter("drag_content_id")
		);

    	$sql = "UPDATE {testee_content} ".
				"SET display_sequence = ? ".
				"WHERE content_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 新着情報にセットする
	 *
     * @return bool
	 * @access	public
	 */
	function setWhatsnew($content_id) {
		$params = array("content_id" => $content_id);
    	$content = $this->_db->selectExecute("testee_content", $params);
		if (empty($content)) {
			return false;
		}
		
		$testee = $this->_db->selectExecute("testee", array("testee_id" => $content[0]['testee_id']));
		if (empty($testee)) {
			return false;
		}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");

		if ($content[0]["temporary_flag"] == TESTEE_STATUS_RELEASED_VALUE && $content[0]["agree_flag"] == TESTEE_STATUS_AGREE_VALUE) {
			$result = $this->getWhatsnewTitle($content_id, $testee[0]['title_metadata_id']);
			if ($result === false) {
				return false;
			}

			$whatsnew = array(
				"unique_id" => $content_id,
				"title" => $result["title"],
				"description" => $result["description"],
				"action_name" => "testee_view_main_detail",
				"parameters" => "content_id=". $content_id . "&testee_id=" . $content[0]["testee_id"],
				"insert_time" => $content[0]["insert_time"],
				"insert_user_id" => $content[0]["insert_user_id"],
				"insert_user_name" => $content[0]["insert_user_name"]
			);

			$result = $whatsnewAction->auto($whatsnew);
			if ($result === false) {
				return false;
			}
		} else {
			$result = $whatsnewAction->delete($content_id);
			if($result === false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 投稿回数をセットする
	 *
     * @return bool
	 * @access	public
	 */
	function setMonthlynumber($edit_flag, $status, $agree_flag=null, $before_post=null) {
		$monthlynumberAction =& $this->_container->getComponent("monthlynumberAction");
		$session =& $this->_container->getComponent("Session");

		// --- 投稿回数更新 ---
		if ($status == TESTEE_STATUS_RELEASED_VALUE  && $agree_flag == TESTEE_STATUS_AGREE_VALUE
				&& (!$edit_flag
					|| $before_post['temporary_flag'] == TESTEE_STATUS_BEFORE_RELEASED_VALUE
					|| $before_post['agree_flag'] == TESTEE_STATUS_WAIT_AGREE_VALUE)) {
			if (!$edit_flag) {
				$params = array(
					"user_id" => $session->getParameter("_user_id")
				);
			} else {
				$params = array(
					"user_id" => $before_post['insert_user_id']
				);
			}
			if (!$monthlynumberAction->incrementMonthlynumber($params)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 新着情報のタイトル・詳細を取得
	 *
	 * @access	public
	 */
	function getWhatsnewTitle($content_id, $title_metadata_id) {
		$sql = "SELECT meta.metadata_id, meta.type, meta_con.content," .
						" meta_con.insert_time, meta_con.update_time, file.file_name" .
				" FROM {testee_metadata_content} meta_con" .
				" INNER JOIN {testee_metadata} meta ON (meta_con.metadata_id = meta.metadata_id)" .
				" LEFT JOIN {testee_file} file ON (meta_con.metadata_content_id=file.metadata_content_id)" .
				" WHERE meta_con.content_id=?".
				" AND (meta.metadata_id=? OR meta.type=".TESTEE_META_TYPE_WYSIWYG." OR meta.type=".TESTEE_META_TYPE_TEXTAREA.")" .
				" ORDER BY meta.display_pos, meta.display_sequence";

		$params = array("content_id" => $content_id, "metadata_id" => $title_metadata_id);
		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackWhatsnewTitle"), $title_metadata_id);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 新着情報のコールバック関数
	 *
	 * @access	private
	 */
	function _callbackWhatsnewTitle(&$recordSet, $title_metadata_id) {
		$result = array("title"=>null, "description"=>null);
		while ($row = $recordSet->fetchRow()) {
			if ($row['metadata_id'] == $title_metadata_id && !isset($result["title"])) {
				switch ($row["type"]) {
					case TESTEE_META_TYPE_FILE:
					case TESTEE_META_TYPE_IMAGE:
						if (empty($row["file_name"])) {
							$title = TESTEE_NOTITLE;
						} else {
							$title = $row["file_name"];
						}
						$result["title"] = $title;
						break;
					case TESTEE_META_TYPE_WYSIWYG:
						$container =& DIContainerFactory::getContainer();
						$convertHtml =& $container->getComponent("convertHtml");
			    		$title = $convertHtml->convertHtmlToText($row["content"]);
			    		$title = preg_replace("/\\\n/", " ", $title);
						$title = mb_substr($title, 0, _SEARCH_CONTENTS_LEN + 1, INTERNAL_CODE);
						$result["title"] = $title;
						break;
					case TESTEE_META_TYPE_AUTONUM:
						$title = intval($row["content"]);
						$result["title"] = $title;
						break;
					case TESTEE_META_TYPE_DATE:
					case TESTEE_META_TYPE_N_DATE:			// 追加メタタイプ
					case TESTEE_META_TYPE_N_BIRTHDAY:		// 追加メタタイプ
						if (empty($row["content"])) {
							$title = TESTEE_NOTITLE;
						} else {
							$title = timezone_date_format($row["content"], _DATE_FORMAT);
						}
						$result["title"] = $title;
						break;
					case TESTEE_META_TYPE_INSERT_TIME:
						$title = timezone_date_format($row["insert_time"], _FULL_DATE_FORMAT);
						$result["title"] = $title;
						break;
					case TESTEE_META_TYPE_UPDATE_TIME:
						$title = timezone_date_format($row["update_time"], _FULL_DATE_FORMAT);
						$result["title"] = $title;
						break;
					case TESTEE_META_TYPE_MULTIPLE:
						if (empty($row["content"])) {
							$title = TESTEE_NOTITLE;
						} else {
							$multipleArr = explode("|",$row["content"]);
							$title = $multipleArr[0];
						}
						$result["title"] = $title;
						break;
					default:
						$result["title"] = $row["content"];
						break;
				}
				continue;
			}
			if ($row['metadata_id'] != $title_metadata_id && !isset($result["description"])) {
				$result["description"] = $row["content"];
				continue;
			}

			if (isset($result["title"]) && isset($result["description"])) { break; }
		}
		return $result;
	}
	
	/**
	 * ダウンロード回数記入
	 *
	 * @access	private
	 */
	function setDownloadCount($upload_id) {
		if(empty($upload_id)) {
			return false;
		}
		
		$params = array(
			"upload_id" => $upload_id
		);
		$sql = "UPDATE {testee_file} ".
				"SET download_count = download_count + 1 ".
				"WHERE upload_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		
		return true;
	}

	/**
	 * メタデータ削除処理
	 * @param  int    metadata_id
	 * @return boolean
	 * @access public
	 */
	function deleteDatecheck($condition_id) {
		if (empty($condition_id)) {
    		return false;
    	}
    	$params = array(
			"condition_id" => $condition_id
		);

    	$result = $this->_db->deleteExecute("testee_date_condition", $params);
    	if($result === false) {
    		return false;
    	}

    	return true;
	}

	/**
	 * 登録番号のクリア
	 *
	 * @param int testee_id
	 * @return boolean 
	 * @access public
	 */
	function clearContentNo( $testee_id ) {
		// 対象のDB の登録番号をクリアする
		$params = array( $testee_id );
    	$sql = "UPDATE {testee} ".
				"SET content_no = 0 ".
				"WHERE testee_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}
		return true;
	}

	/**
	 * データベース参照ログの記録
	 *
	 * @access	private
	 */
	function addDatabaseViewLog( $action_name, $action_keys ) {
		$params_log = array( "exec_sql" => $action_name,
		                     "param" => print_r($action_keys, true),
		                     "insert_time" => date("YmdHis"),
		                     "insert_user_id" => $_SESSION['_user_id'],
		                     "insert_user_name" => $_SESSION['_handle'] );
		$sql_log  = "INSERT INTO ";
		$sql_log .= "{testee_log} ";
		$sql_log .= "(exec_sql,param,insert_time,insert_user_id,insert_user_name) ";
		$sql_log .= "VALUES(?,?,?,?,?)";
		$result = $this->_db->execute($sql_log, $params_log);
		return;
	}

	/**
	 * TEDC連携結果の更新
	 *
	 * @access public
	 */
	function tedcLinkResult($content_id, $code, $message) {
		// 対象のコンテンツのTEDC連携結果の更新
		$params = array(
			"tedc_link_result" => $code,
			"tedc_link_result_message" => $message,
			"content_id" => intval($content_id)
		);
    	$sql = "UPDATE {testee_content} ".
				"SET tedc_link_result = ?, tedc_link_result_message = ? ".
				"WHERE content_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}
		return true;
	}

// TEDC権限設定用Function

	/**
	 * TEDC権限情報を登録する
	 *
     * @return boolean	true:正常、false:異常
	 * @access	public
	 */
	function tedcauthorityInsert($params) {
    	$result = $this->_db->insertExecute("testee_tedcauthority", $params, true);
    	if($result === false) {
			$this->_db->addError();
			return $result;
    	}
		return true;
	}

	/**
	 * TEDC権限情報を更新する
	 *
     * @return boolean	true:正常、false:異常
	 * @access	public
	 */
	function tedcauthorityUpdate($params) {
		$where_params = $params;
		unset($where_params['tedc_authority']);
    	$result = $this->_db->updateExecute("testee_tedcauthority", $params, $where_params, true);
    	if($result === false) {
			$this->_db->addError();
			return $result;
    	}
		return true;
	}

	/**
	 * TEDC権限情報を削除する
	 *
     * @return boolean	true:正常、false:異常
	 * @access	public
	 */
	function tedcauthorityDelete($params) {
		unset($params['tedc_authority']);
		$result = $this->_db->deleteExecute("testee_tedcauthority", $params);
    	if($result === false) {
			$this->_db->addError();
			return $result;
    	}
		return true;
	}
	
	/**
	 * 割付設定の登録
	 *
     * 
	 * @access	public
	 */
	function setAllocationFlag( $testee_id, $allocation_flag )
	{
		if ( empty( $testee_id ) ) {
			return;
		}
		$params = array($testee_id);

		$sql = "SELECT * "
			.  "FROM {testee_allocate} "
			.  "WHERE testee_id = ? ";
		
		$testee_setting = $this->_db->execute($sql, $params);

		if ($testee_setting === false) {
			$this->_db->addError();
			
			return $testee_setting;
		}
		
		if (count($testee_setting) === 0 ) {
					$params = array( 
								"testee_id" => $testee_id,
								"allocation_flag" => $allocation_flag
								 );
		//print_r($params);
		$result = $this->_db->insertExecute('testee_allocate',
											$params,
											true,
											'testee_id');
		}
		
		else {

			$params = array( "allocation_flag" => $allocation_flag );

			$result = $this->_db->updateExecute('testee_allocate',
															$params,
															array( "testee_id"=> $testee_id ),
															true);
		}
	}
	
	/**
	 * 割付調整因子の表示項目登録
	 *
     * 
	 * @access	public
	 */
	function addAllocation( $metadata_id )
	{
//test_log($metadata_id);
		$params = array($metadata_id);

		$sql = "SELECT * "
			.  "FROM {testee_adjustment} "
			.  "WHERE metadata_id = ? ";
		
		$allocation_selected = $this->_db->execute($sql, $params);
//test_log($allocation_selected);
		if ($allocation_selected === false) {
			$this->_db->addError();
			
			return $allocation_selected;
		}
		if (count($allocation_selected) === 0 ) {
			$params = array( 
			"metadata_id" => $metadata_id
			 );
		//print_r($params);
		$result = $this->_db->insertExecute('testee_adjustment',
														$params,
														true,
														'desplay_seq');
		}
	}

	/**
	 * @割付調整因子の表示項目削除
	 * @access	public
	 */
	 // 
	function delAllocation( $metadata_id )
	{
		if ( empty( $metadata_id ) ) {
			return;
		}
		
		$params = array(
				'metadata_id' => $metadata_id
		);
		if (!$this->_db->deleteExecute('testee_adjustment', $params)) {
			return false;
		}
		return true;
	}


	/**
	 * 割付因子情報：シミュレーション時の自動生成時の比率の設定
	 * @access	public
	 */
	public function updateAdjustmentFactorRatio( $metadata_id, $factor_ratio )
	{
		$where_params  = array( "metadata_id"  => $metadata_id );
		$update_params = array( "factor_ratio" => $factor_ratio );
		
		$result = $this->_db->updateExecute("testee_adjustment", $update_params, $where_params, true);
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}


	/**
	 * 均等比率の登録
	 *
     * 
	 * @access	public
	 */
	function changeEqual( $testee_id, $equal_ratio_flag )
	{
		if ( empty( $testee_id ) ) {
			return;
		}
//test_log($testee_id);
		$params = array($testee_id);

		$sql = "SELECT * "
			.  "FROM {testee_allocate} "
			.  "WHERE testee_id = ? ";
		
		$testee_setting = $this->_db->execute($sql, $params);

		if ($testee_setting === false) {
			$this->_db->addError();
			
			return $testee_setting;
		}
		
		if (count($testee_setting) === 0 ) {
					$params = array( 
								"testee_id" => $testee_id,
								"equal_ratio_flag" => $equal_ratio_flag
								 );
		//print_r($params);
		$result = $this->_db->insertExecute('testee_allocate',
											$params,
											true,
											'testee_id');
		}
		
		else {

			$params = array( "equal_ratio_flag" => $equal_ratio_flag );

			$result = $this->_db->updateExecute('testee_allocate',
															$params,
															array( "testee_id"=> $testee_id ),
															true);
		}
	}

	/**
	 * 群の設定の追加登録
	 *
     * 
	 * @access	public
	 */
	function addGroup( $testee_id, $new_group_name, $new_intervention, $new_ratio )
	{
		$params = array( 
			"testee_id" => $testee_id,
			"group_name" => $new_group_name,
			"intervention" => $new_intervention,
			"ratio" => $new_ratio
			 );
		//test_log($params);
		$result = $this->_db->insertExecute('testee_allocation_group',
											$params,
											true,
											'allocation_group_id');
	}

	/**
	 * @群の設定項目削除
	 * @access	public
	 */
	 // 
	function delGroup( $allocation_group_id )
	{
//test_log($allocation_group_id);
		if ( empty( $allocation_group_id ) ) {
			return;
		}

		$params = array(
				'allocation_group_id' => $allocation_group_id
		);
		if (!$this->_db->deleteExecute('testee_allocation_group', $params)) {
			return false;
		}
		return true;
	}

	/**
	 * @群の設定項目変更
	 * @access	public
	 */
	 // 
	function changeGroup( $allocation_group_id, $group_name, $intervention, $ratio )
	{
		if ( empty( $allocation_group_id )) {
			return;
		}
		$params = array( "group_name" => $group_name,
							"intervention" => $intervention,
							"ratio" => $ratio
							 );

		$result = $this->_db->updateExecute('testee_allocation_group',
														$params,
														array( "allocation_group_id"=> $allocation_group_id ),
														true);
	}
	

	/**
	 * 割付群　置換ブロック法用比率のUPDATE処理
	 *
	 * @params	allocation_group_id		割付群ID
	 * @params	ratio_block				置換ブロック法用比率
	 * @return	false or array [record rows][testee_allocation_group columns]
	 * @access	public
	 */
	public function updateGroupRatioBlock( $allocation_group_id, $ratio_block )
	{
		// update項目設定
		$update_params = array( "ratio_block" => $ratio_block );
		
		// 条件項目設定
		$where_params = array( "allocation_group_id" => $allocation_group_id );
		
		// SQL実施
		$result = $this->_db->updateExecute('testee_allocation_group', $update_params, $where_params, true );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}


	/**
	 * @群の設定項目変更
	 * @access	public
	 */
	 // 
	function setForceallocation($testee_id, $group_differences, $allocation_probability, $force_allocation_flag)
	{
		if ( empty( $testee_id )) {
			return;
		}
		$params = array( "group_differences" => $group_differences,
							"allocation_probability" => $allocation_probability,
							"force_allocation_flag" => $force_allocation_flag
							 );

		$result = $this->_db->updateExecute('testee_allocate',
														$params,
														array( "testee_id"=> $testee_id ),
														true);
	}
	
	
	/**
	 * 出力設定の登録
	 *
     * 
	 * @access	public
	 */
	function setAllocationresult( $testee_id, $allocation_result_flag )
	{
		if ( empty( $testee_id ) ) {
			return;
		}
		$params = array($testee_id);

		$sql = "SELECT * "
			.  "FROM {testee_allocate} "
			.  "WHERE testee_id = ? ";
		
		$testee_setting = $this->_db->execute($sql, $params);

		if ($testee_setting === false) {
			$this->_db->addError();
			
			return $testee_setting;
		}
		
		if (count($testee_setting) === 0 ) {
					$params = array( 
								"testee_id" => $testee_id,
								"allocation_result_flag" => $allocation_result_flag
								 );
		//print_r($params);
		$result = $this->_db->insertExecute('testee_allocate',
											$params,
											true,
											'testee_id');
		}
		
		else {

			$params = array( "allocation_result_flag" => $allocation_result_flag );

			$result = $this->_db->updateExecute('testee_allocate',
															$params,
															array( "testee_id"=> $testee_id ),
															true);
		}
	}

	/**
	 * 出力設定履歴の登録
	 *
	 * 
	 * @access	public
	 */
	function setSettinghistory( $testee_id, $allocation_result_flag )
	{
		if ( empty( $testee_id ) ) {
			return;
		}
		
		$params = array( 
					"allocation_result_flag" => $allocation_result_flag,
					"testee_id" => $testee_id
					 );
		//print_r($params);
		$result = $this->_db->insertExecute('testee_setting_history',
											$params,
											true,
											'allocation_view_id');
	}

	/**
	 * 割付データの登録
	 * 
	 * @access	public
	 */
	function setContentAllocation( $testee_id, $content_id, $datas )
	{

		// 割付設定の取得
		$allocation = $this->_mdbView->getAllocationContent( $testee_id );
		//print_r( $allocation );

		// 割付設定を使用するに設定されているかのチェック
		if ( empty( $allocation ) || ( count( $allocation ) < 1 ) ) {
			return true;
		}
		else if( $allocation["allocation_flag"] == 1 )
		{
			// 最小化法
			$result = $this->setContentAllocationMinimization( $allocation, $testee_id, $content_id, $datas );
			return $result;
		}
		else if( $allocation["allocation_flag"] == 2 )
		{
			// 置換ブロック法
			$result = $this->setContentAllocationBlock( $allocation, $testee_id, $content_id, $datas );
			return $result;
		}
		else
		{
			// 上記以外
			return true;
		}
		
	}

	/**
	 * 割付データ登録：最小化法用
	 *
	 * @params	allocation	array[testee_allocate]		割付情報
	 * @params	testee_id								臨床試験支援DBのID（行ID）
	 * @params	datas		array[???]					登録データ
	 * @return	boolean		false:異常終了 / true:正常終了
	 * @access	public
	 */
	private function setContentAllocationMinimization( $allocation, $testee_id, $content_id, $datas )
	{
		// 群の設定の取得
		// $group_content│比率
		// ───────┼──
		// A群のgroup_id │60
		// B群のgroup_id │40
		$group_content = $this->getGroupContent( $testee_id, $allocation );
		//print_r( $group_content );
		//test_log( $group_content );

		// 群が存在しない場合は、割付設定なしとみなして戻る
		if ( empty( $group_content ) || count( $group_content ) == 0 ) {
			return true;
		}
		
		// 割付シード値取得
		$allocate_seed = $this->_mdbView->getAllocationSeed();
		
		// シードをもとに乱数初期化
		mt_srand( $allocate_seed );
		
		
		// 割付確率
		$force_probability_flag = false;            // 強制割付する確率
		$allocation["allocation_probability"];      // 割付確率
		$force_probability = mt_rand(0, 99);        // 割付する乱数

		// 発生した乱数が割付確率より小さければ、強制割付
		if ( $force_probability < $allocation["allocation_probability"] ) {
			$force_probability_flag = true;
		}

		// 強制割付するにチェック＆割付確率で割付になった場合
		if ( $allocation["force_allocation_flag"] == 1 && $force_probability_flag ) {
			// 後で強制割付処理
		}
		// ランダム割付
		else {
			$ans = $this->rndAnswer( $group_content );
			//print_r( $ans );

			// 群テーブルに登録
			$result = $this->insertContentGroup( $content_id, $testee_id, $ans, $allocate_seed );
			if ($result === false) {
				return $result;
			}

			return true;
		}

		// --- 以下は強制割付


		// 登録データと同じ設定値を持つデータの各割付群の件数をカウントする
		$group_alloc_counts = $this->getGroupCountByAdjustment( $testee_id, $datas, $group_content );
		if( $group_alloc_counts === false || count( $group_alloc_counts ) <= 0 ) return false;
//		test_log("--- group_alloc_counts");
//		test_log($group_alloc_counts);
		
		// 割付可能な群を特定する
		$can_group = $this->getAllocCanGroup( $group_alloc_counts, $allocation["group_differences"], $group_content );
//		test_log("--- can_group");
//		test_log( $can_group );


		// 群を決定
		$allocation_group_id = $this->rndAnswer( $can_group );
		//print_r( $allocation_group_id );

		// 100回テスト
		//$this->loopTest( $can_group );

		// 群テーブルに登録
		$result = $this->insertContentGroup( $content_id, $testee_id, $allocation_group_id, $allocate_seed );
		if ($result === false) {
			//test_log($this->_db->ErrorMsg());
			$this->_db->addError();
			
			return $result;
		}

		return true;
	}
	
	
	/**
	 * 割付データ登録：置換ブロック法
	 *
	 * @params	allocation	array[testee_allocate]		割付情報
	 * @params	testee_id								臨床試験支援DBのID（行ID）
	 * @params	datas		array[???]					登録データ
	 * @return	boolean		false:異常終了 / true:正常終了
	 * @access	public
	 */
	private function setContentAllocationBlock( $allocation, $testee_id, $content_id, $datas )
	{
//test_log(date('Y/m/d H:i:s') . ":setContentAllocationBlock:start");
		// +++++ 割り付けるかどうかの事前チェック ++++++++++++++++++++++++++++++++++++++++++++++++++
		
		// 割付群が存在しない場合は割付ないので正常終了
		$group_contents = $this->_mdbView->getGroupContent( $testee_id );
		if( $group_contents === false || count( $group_contents ) <= 0 ) return true;
//test_log("割付群");
//test_log($group_contents);
		
		// 割付群が変更になっている場合は割付しないので正常終了
		if( $this->_mdbView->changeAllocateGroupContents( $group_contents ) === false ) return true;
		
		// 割付因子の各内容取得。取得できない場合は割付ないので正常終了
		$allocate_factors = $this->_mdbView->getSelectedAllocationFactors( $testee_id );
		if( $allocate_factors === false || count( $allocate_factors ) <= 0 ) return true;
//test_log("割付因子の各内容");
//test_log($allocate_factors);
		
		// 割付層情報取得。取得できない場合は割付ないので正常終了
		$allocation_conbinations = $this->_mdbView->getAllocationcConbination( $testee_id );
		if( $allocation_conbinations === false || count( $allocation_conbinations ) <= 0 ) return true;
//test_log("割付層情報");
//test_log($allocation_conbinations);
		
		// 取得した割付因子項目を組み替え
		$factor_list = $this->_mdbView->getAllocationItemList( $allocate_factors );
//test_log("割付因子項目組み換え後");
//test_log($factor_list);
		
		// 現在の割付因子の項目で、組合せパタン作成
		$now_pattern = $this->_mdbView->getConbinationPattern( $factor_list );
//test_log("現在の割付因子での組合せパタン");
//test_log($now_pattern);
		
		// 現在の組み合わせパターンと、既存層のパターンを比較し、一致しなければ割付ないので正常終了
		if( $this->_mdbView->matchAllocationFactors( $now_pattern, $allocation_conbinations ) === false ) return true;
		
		// 比率の合計値取得
		$total_ratio = $this->_mdbView->getTotalRatio( $group_contents );
		
		// 比率合計値と割付層のブロック数の比較
		if( $this->_mdbView->checkRatioBlockCount( $allocation_conbinations, $total_ratio ) === false ) return true;
		
		// 可変ブロック情報取得。取得失敗時には可変なしとして処理続行
		$variable_block = $this->_mdbView->getAllocateVariableBlock( $testee_id );
//test_log("可変ブロック情報");
//test_log($variable_block);
		
		// 現在登録数を取得（※この件数には、今割付をしようとしている症例数も含まれている）
		$content_count = $this->_mdbView->getContentCount( $testee_id );
//test_log("現在患者情報[" . $content_count . "]");
		
		
		if( $variable_block === false || count( $variable_block ) <= 0 || empty( $variable_block[ "case_count" ] ) === true )
		{
			// 可変ブロック情報取得失敗、データがない、基準症例数が空、の場合は、ブロック数を変更しないので何もしない
//test_log("可変ブロック情報取得失敗、データがない、基準症例数が空、の場合は、ブロック数を変更しないので何もしない");
		}
		else
		{
			// 上記以外の場合は、ブロック数を変更させる可能性あり
//test_log("ブロック数を変更させる可能性あり");
			
			// 現在登録数÷基準症例数の余り＝0…ブロック数変更
			$surplus = $content_count % intval( $variable_block[ "case_count" ] );
			
			// 余りが0の時
			if( $surplus == 0 )
			{
//test_log("ブロック数をランダムで変更する");
//test_log("変更前割付層");
//test_log($allocation_conbinations);
				
				// シード値取得して初期値として設定
				$variable_seed = $this->_mdbView->getAllocationSeed();
				mt_srand( $variable_seed );
				
				// シードを利用したかどうかのフラグ
				$use_seed = false;
				
				
				// 割付層情報ループ
				foreach( $allocation_conbinations as &$list )
				{
					// 分解
					$block_count_list = explode( ",", $list[ "next_block_count" ] );
					
					if( count( $block_count_list ) <= 1 )
					{
						// 単一定義なので何もしない
					}
					else
					{
						// 複数定義なので、使用するブロック数をランダムで決定する
						$index = $this->_mdbView->randBlockCountIndex( $list[ "next_block_count" ] );
						
						$use_seed = true;		// 18.10.05 add by makino@opensource-workshop.jp
						
						// 決定したブロック数で「現在ブロック数」を更新する
						$list[ "now_block_count" ] = $block_count_list[ $index ];
						$result = $this->updateAllocationConbinationNowBlockCount( $list[ "conbination_id" ], $list[ "now_block_count" ] );
					}
				}
//test_log("変更後割付層");
//test_log($allocation_conbinations);
				
				// 割付シードを利用した場合は、そのシード値を登録する
				if( $use_seed === true )
				{
					$result = $this->insertAllocationSeed( $testee_id, 0, $variable_seed, 0, $content_count );
				}
				
				
			}
		}
		
		
		
		// +++++ 割付処理実施 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		// 割付因子をもとにコンテンツ登録情報から組合せ情報を取得する
		$content_factors = array();
		foreach( $allocate_factors as $metadata )
		{
			if( isset( $datas[ $metadata[ 'metadata_id' ] ] ) === true )
			{
				$content_factors[] = $datas[ $metadata[ 'metadata_id' ] ];
			}
		}
		$content_factors_string = implode( '|', $content_factors );
//test_log("患者情報をもとに因子組み合わせ文字列作成[" . $content_factors_string . "]");
		
		// 一致する割付層を取得する
		$allocation_conbination = "";
		foreach( $allocation_conbinations as $value )
		{
			if( $value[ 'factor_contents' ] == $content_factors_string )
			{
				$allocation_conbination = $value;
				break;
			}
		}
		// 一致する層を取得できなかった場合、次回ブロック数が0の場合は割付ないので正常終了
		if( empty( $allocation_conbination ) === true || $allocation_conbination[ 'next_block_count' ] == 0 ) return true;
		
		$conbination_id   = $allocation_conbination[ 'conbination_id' ];
		$next_block_count = $allocation_conbination[ 'next_block_count' ];
		$now_block_count  = $allocation_conbination[ 'now_block_count' ];
		$exclude_count    = $allocation_conbination[ 'exclude_count' ];
//test_log("割付層ID:" . $conbination_id);
//test_log("次回ブロック数:" . $next_block_count);
//test_log("現在ブロック数:" . $now_block_count);
//test_log("除外連続数:" . $exclude_count);
		
		// 層IDをもとに紐づくブロックデータを取得する（※まだ割り付けていないデータ）
		$blockdata_list = $this->_mdbView->getAllocationBlockUnused( $testee_id, $conbination_id );
		if( $blockdata_list === false ) return false;
//test_log("層IDをもとに取得した紐づくブロックデータ（未使用分）");
//test_log($blockdata_list);
		
		// 層IDをもとに紐づくブロックデータの連番最大値を取得する
		$result = $this->_mdbView->getAllocationBlockMaxSeqNo( $testee_id, $conbination_id );
		if( $result === false ) return false;
//test_log("層IDをもとに取得した紐づくブロックデータの最大連番");
//test_log($result);
		
		// 最大連番取得
		$max_seq_no = ( count( $result ) <= 0 ) ? 0 : $result[0][ 'sequence_no' ];
//test_log("使用する最大連番:" . $max_seq_no);
		
		
		if( count( $blockdata_list ) <= 0 )
		{
//test_log("まだ１件もブロックデータがないので新規作成して割り当て");
			// ----- 1件もない場合、新しいブロックデータを作成して割り当てる -----------------------
			
			// 現在ブロック数が空の場合は、次回ブロック数から取得して設定/更新する
			// ※可変ブロック処理実装前の試験データの為の処理
			if( empty( $now_block_count ) === true )
			{
//test_log("現在ブロック数が空なのでランダムで現在ブロック数を決める。");
				$block_count_list = explode( ",", $next_block_count );
				if( count( $block_count_list ) <= 1 )
				{
					$now_block_count = $next_block_count;
				}
				else
				{
					// シード値取得して初期値として設定
					$variable_seed = $this->_mdbView->getAllocationSeed();
					mt_srand( $variable_seed );
					
					// 使用するシード値を登録しておく
					$result = $this->insertAllocationSeed( $testee_id, 0, $variable_seed, 0, $content_count );
					
					$index = $this->_mdbView->randBlockCountIndex( $next_block_count );
					$now_block_count = $block_count_list[ $index ];
				}
//test_log("決定した現在ブロック数[" . $now_block_count . "]");
				
				// 割付層に現在ブロック数をUPDATEする
				$result = $this->updateAllocationConbinationNowBlockCount( $conbination_id, $now_block_count );
			}
			
			// 除外連続数をリストから取得しておく
			$set_exclude_count = 0;
			if( empty( $exclude_count ) === false )
			{
//test_log("除外連続数に値あり");
				$exclude_count_list = explode( ",", $exclude_count );
				if( count( $exclude_count_list ) <= 1 )
				{
//test_log("単一定義の為、設定された値をそのまま使う");
					// 単一定義の場合
					$set_exclude_count = intval( $exclude_count );
				}
				else
				{
					// 複数定義あり
					$index = $this->_mdbView->getBlockCountIndex( $next_block_count, $now_block_count );
					if( $index < 0 )
					{
						// 正しく取得できなかった場合は何も設定しない
					}
					else
					{
						if( empty( $exclude_count_list[ $index ] ) === false )
						{
							$set_exclude_count = intval( $exclude_count_list[ $index ] );
						}
						else
						{
							// 空の場合は何も設定しない
						}
					}
				}
			}
//test_log("設定除外連続数[" . $set_exclude_count . "]");
			
			
			// 新しく割付パタン作成
			$allocate_block_patterns = $this->_mdbView->getAllocateBlockPattern( $group_contents, $now_block_count, $set_exclude_count );
			if( $allocate_block_patterns === false ) return true;
//test_log("新しく作成した割付パタン");
//test_log($allocate_block_patterns);
			
			// 割付シードを生成してデータを登録する
			$seed = $this->_mdbView->getAllocationSeed();
			$allocation_seed_id = $this->insertAllocationSeed( $testee_id, $conbination_id, $seed, $now_block_count );
			if( $allocation_seed_id === false ) return true;
//test_log("シード:" . $seed);
//test_log("割付シードID" . $allocation_seed_id);
			
			// シードをもとに乱数初期化
			mt_srand( $seed );
			
			// 乱数取得
			$allocate_block_pattern_index = mt_rand( 0, ( count( $allocate_block_patterns ) -1 ) );
//test_log("乱数設定INDEX:" . $allocate_block_pattern_index);
			
			// 使用する割付パタン取得
			$use_allocate_block_pattern = $allocate_block_patterns[ $allocate_block_pattern_index ];
//test_log("使用する割付パタン");
//test_log($use_allocate_block_pattern);
			
			// 1件目のブロックデータを登録
			$result = $this->insertAllocationBlock( $testee_id, $conbination_id, $allocation_seed_id, $max_seq_no +1, $use_allocate_block_pattern[ 0 ], 1 );
			if( $result === false ) return false;
//test_log("新しく登録したブロックデータID:" . $result);
			
			// 1件目を行群テーブルに登録
			$result = $this->insertContentGroup( $content_id, $testee_id, $use_allocate_block_pattern[ 0 ] );
			if( $result === false ) return false;
//test_log("1件目の行群テーブルID:" . $result);
			
			// 2件目以降の未使用ブロックデータを登録
			for( $i = 2; $i <= $now_block_count; $i++ )
			{
				$result = $this->insertAllocationBlock( $testee_id, $conbination_id, $allocation_seed_id, $max_seq_no +$i, $use_allocate_block_pattern[ ($i-1) ], 0 );
				if( $result === false ) return false;
//test_log($i . "件目のブロックテーブルID:" . $result);
			}
		}
		else
		{
//test_log("１件以上あるので取得したブロックデータを割り付ける");
			// ----- 1件以上ある場合、取得したデータを割り当てる -----------------------------------
			
			// ブロックデータを使用済みに変更する
			$result = $this->updateAllocationBlockUsed( $blockdata_list[ 0 ][ 'allocation_block_id' ] );
			if( $result === false ) return false;
			
			// 割付行群データに登録する
			$result = $this->insertContentGroup( $content_id, $testee_id, $blockdata_list[ 0 ][ 'allocation_group_id' ] );
			if( $result === false ) return false;
//test_log("登録した行群ID:" . $result);
		}
		
		// 正常終了
//test_log("setContentAllocationBlock:end");
		return true;
	}
	
	
	/**
	 * 群の設定の取得
	 * 
	 * @access	public
	 * @return	array 組み替えた群情報を返却する（array[ 群ID ] = 比率）
	 */
	function getGroupContent( $testee_id, $allocation )
	{

		// 群の設定(各グループ)の取得
		$group_content = $this->_mdbView->getGroupContent( $testee_id );
		//print_r( $group_content );

		// 均等比率の場合は各グループの比率を強制的に均等にする。
		if ( $allocation["equal_ratio_flag"] == 1 ) {

			$equality_percent = floor( 100 / count( $group_content ) );

			foreach ( $group_content as &$group_item ) {
				$group_item["ratio"] = $equality_percent;
			}
		}
		unset( $group_item );

		// 群の設定(各グループ)を [allocation_group_id] => [ratio] に変換する。
		$ret_group_content = array();
		foreach( $group_content as $group_item ) {
			$ret_group_content[ $group_item["allocation_group_id"] ] = $group_item["ratio"];
		}

		return $ret_group_content;
	}

	/**
	 * 割付
	 * 
	 * @access	public
	 */
	function rndAnswer( $rndArray ) {
		$rndNum = mt_rand(1, array_sum( $rndArray ) ); // 1 ～合計 の中で乱数を発生

		$subTotal = 0;
		foreach( $rndArray as $key => $num ) {
			$subTotal += $num;
			if ( $subTotal >= $rndNum ) {
				return $key;
			}
		}
	}

	/**
	 * 100回割付テスト
	 * 
	 * @access	public
	 */
	function loopTest( $random_array ) {

		print "<hr />\n";
		print "RANDOM TEST<br>\n";
		print '<table border="1">'."\n";
		foreach( $random_array as $key => $num ) {
			$ansCount[$key] = 0;
		}

		for( $i = 0 ; $i < 10 ; $i++ ) {
		print "<tr>\n";
			for( $j = 0 ; $j < 10 ; $j++ ) {
				$ans = $this->rndAnswer( $random_array );
				$ansCount[$ans]++;
				print "<td>$ans</td>\n";
				print "</td>\n";
			}
		print "</tr>\n";
		}
		print "</table>\n";

		// キー順にソート
		ksort( $ansCount );

		// 回答の回数を表示
		foreach( $random_array as $key => $num ) {
			print "$key({$num})... {$ansCount[$key]}回<br>\n";
		}
	}


	/**
	 * 登録データと同じ調整因子の組み合わせ毎の件数を取得する
	 * 
	 * @access	public
	 * @return	array[ allocation_group_id ] = count
	 */
	public function getGroupCountByAdjustment( $testee_id, $datas, $group_content )
	{
		// 割付因子の各項目取得
		$allocate_factors = $this->_mdbView->getSelectedAllocationFactors( $testee_id );
		if( $allocate_factors === false || count( $allocate_factors ) <= 0 )
		{
			return false;
		}
		
		// 登録情報から各因子に設定された値を取得する
		$entry_values = array();
		foreach( $allocate_factors as $allocate_factor )
		{
			$metadata_id = $allocate_factor["metadata_id"];
			$entry_values[ $metadata_id ] = $datas[ $metadata_id ];
		}
		
		// 取得した登録値を条件として、割付済み群の数を取得する
		// 【SQL構文】
		// SELECT tmc1.metadata_id as tmc1_id, 
		//        tmc2.metadata_id as tmc2_id, 
		//        ……（因子数分）
		//        tcg.allocation_group_id, count( tc.content_id ) as count
		// FROM testee_content tc 
		// INNER JOIN testee_content_group    tcg  ON tcg.content_id   = tc.content_id 
		// INNER JOIN testee_metadata_content tmc1 ON tmc1.metadata_id = '因子1のmetadata_id' AND tmc1.content_id = tc.content_id AND tmc1.content = '値1'
		// INNER JOIN testee_metadata_content tmc2 ON tmc2.metadata_id = '因子2のmetadata_id' AND tmc2.content_id = tc.content_id AND tmc2.content = '値2'
		// ……（因子数分）
		// WHERE tc.testee_id = '試験のID'
		// GROUP BY tmc1.metadata_id, tmc2.metadata_id, ……（因子数分）, tcg.allocation_group_id

		// SQLの構築
		$select_array  = array();
		$groupby_array = array();
		$where_array   = array();
		$where_params  = array();
		$index = 1;
		foreach( $entry_values as $metadata_id => $value )
		{
			$table_name = "tmc" . $index;
			$select_array[]  = $table_name . ".metadata_id";
			$groupby_array[] = $table_name . ".metadata_id";
			$where_array[]   = "INNER JOIN {testee_metadata_content} " . $table_name . " ON " . $table_name . ".metadata_id = ? AND " . $table_name . ".content_id = tc.content_id AND " . $table_name . ".content = ? ";
			$where_params[]  = $metadata_id;
			$where_params[]  = $value;
			
			$index++;
		}
		
		// SQL文の作成
		$sql  = "SELECT " . implode( ",", $select_array ) . ", tcg.allocation_group_id, count( tc.content_id ) as count ";
		$sql .= "FROM {testee_content} tc ";
		$sql .= "INNER JOIN {testee_content_group} tcg ON tcg.content_id = tc.content_id ";
		$sql .= implode( " ", $where_array );
		$sql .= "WHERE tc.testee_id = ? ";
		$sql .= "GROUP BY " . implode( ",", $groupby_array ) . ", tcg.allocation_group_id ";
		
		
		// 条件値の追加
		$where_params[] = $testee_id;
		
		// SQLの実施
		$result = $this->_db->execute( $sql, $where_params );
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		
		// 返却値の作成
		$result_array = array();
		foreach( $group_content as $key => $ratio )
		{
			$result_array[ $key ] = 0;
		}
		foreach( $result as $record )
		{
			$result_array[ $record[ "allocation_group_id" ] ] = $record[ "count" ];
		}
		
		return $result_array;
	}


	/**
	 * 割付可能な群を返す
	 * 
	 * @access	public
	 */
	public function getAllocCanGroup( $group_alloc_counts, $group_differences, $group_content )
	{
		// 全群から、許容される群間差を超えた群を削っていき、残った群を割付可として返却する
		$return_groups = $group_content;

		foreach( $return_groups as $allocation_group_id => $ratio )
		{
			// 最小値＋許容される群間差＝上限値
			$max_count = $this->_mdbView->getMin( $group_alloc_counts ) + $group_differences;

			foreach ( $group_alloc_counts as $key => $count )
			{
				// 上限値以上の場合は、割付不可としてその群を削る
				if ( $key == $allocation_group_id && $count >= $max_count )
				{
					unset( $return_groups[ $allocation_group_id ] );
				}
			}
		}

		// 計算して、割付可能なグループがない場合は、全てに割付可能とする。
		if ( empty( $return_groups ) || count( $return_groups ) == 0 ) {
			$return_groups = $group_content;
		}

		return $return_groups;
	}


	/**
	 * metadata_id 毎のメール配信ユーザの登録
	 * 
	 * @access	public
	 */
	function updateMailUsers( $metadata_id, $mail_users ) {

		// metadata_id がない場合はエラーで戻る。
		if ( empty( $metadata_id ) ) {
			return;
		}

		// metadata_id のメール送信ユーザを一旦クリア
		$params = array( 'metadata_id' => $metadata_id );
		if ( !$this->_db->deleteExecute('testee_mail_users', $params) ) {
			return false;
		}

		// ユーザがある場合は、メタデータテーブルも更新
		$mail_send_target = 0;
		if ( !empty( $mail_users ) ) {
			$mail_send_target = 1;
		}
		$params = array( "mail_send_target" => $mail_send_target );
		$result = $this->_db->updateExecute('testee_metadata',
											 $params,
											 array( "metadata_id"=> $metadata_id ),
											 true);

		// metadata_id のメール送信ユーザを登録
		foreach ( $mail_users as $mail_user ) {
			$params = array (
						"metadata_id" => $metadata_id,
						"user_id" => $mail_user
					);
			$result = $this->_db->insertExecute( 'testee_mail_users', $params, true, 'testee_mail_users_id' );
			if ($result === false) {
				$this->_db->addError();
				return $result;
			}
		}
	}

	/**
	 * metadata_id 毎のメール配信ユーザの削除
	 * 
	 * @access	public
	 */
	function deleteMailUsers( $metadata_id ) {

		// metadata_id がない場合はエラーで戻る。
		if ( empty( $metadata_id ) ) {
			return;
		}

		// metadata_id のメール送信ユーザをクリア
		$params = array( 'metadata_id' => $metadata_id );
		if ( !$this->_db->deleteExecute('testee_mail_users', $params) ) {
			return false;
		}
	}


	/**
	 * 割付行群テーブルへの登録
	 * 
	 * @params	content_id				患者ID（行ID）
	 * @params	testee_id				臨床試験支援DBのID
	 * @params	allocation_group_id		割付群ID
	 * @params	allocate_seed			割付シード値
	 * @return	false or testee_content_group_id
	 * @access	public
	 */
	public function insertContentGroup( $content_id, $testee_id, $allocation_group_id, $allocate_seed = 0 )
	{
		// 登録値設定
		$insert_params = array( "content_id"          => $content_id,
								"testee_id"           => $testee_id,
								"allocation_group_id" => $allocation_group_id,
								"allocate_seed"       => $allocate_seed );
		
		// 登録
		$result = $this->_db->insertExecute('testee_content_group', $insert_params, true, 'testee_content_group_id');
		if ($result === false)
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付層テーブルへの登録
	 * 
	 * @params	testee_id				臨床試験支援DBのID
	 * @params	factor_contents			割付因子組み合わせ（例：男|すい癌）
	 * @params	next_block_count		次回ブロック数
	 * @oarams	now_block_count			現在ブロック数
	 * @return	false or conbination_id
	 * @access	public
	 */
	public function insertAllocationConbination( $testee_id, $factor_contents, $next_block_count, $now_block_count )
	{
		// 登録値設定
		$insert_params = array( "testee_id"        => $testee_id,
								"factor_contents"  => $factor_contents,
								"next_block_count" => $next_block_count,
								"now_block_count"  => $now_block_count );
		
		// SQL実施
		$result = $this->_db->insertExecute( 'testee_allocation_conbination', $insert_params, true, 'conbination_id' );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付層テーブルのデータ削除処理（※削除する時は、臨床試験支援DBのIDの条件で削除なので、複数行削除）
	 * 
	 * @params	testee_id				臨床試験支援DBのID
	 * @return	false or deleteCount
	 * @access	public
	 */
	public function deleteAllocationConbination( $testee_id )
	{
		// 条件項目作成
		$where_params = array( "testee_id" => $testee_id );
		
		// SQL実施
		$result = $this->_db->deleteExecute( 'testee_allocation_conbination', $where_params );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付層テーブルのデータ更新処理
	 * 
	 * @params	conbination_id			当TBLシーケンス値
	 * @params	next_block_count		次回ブロック数
	 * @params	exclude_count			除外連続数
	 * @params	now_block_count			現在ブロック数
	 * @return	false or updateCount
	 * @access	public
	 */
	public function updateAllocationConbination( $conbination_id, $next_block_count, $exclude_count, $now_block_count )
	{
		// update項目作成
		$update_params = array( "next_block_count" => $next_block_count,
								"exclude_count"    => $exclude_count,
								"now_block_count"  => $now_block_count );
		
		// 条件項目作成
		$where_params = array( "conbination_id" => $conbination_id );
		
		$result = $this->_db->updateExecute( 'testee_allocation_conbination', $update_params, $where_params, true );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付層テーブルのデータ更新処理（現在ブロック数のみ）
	 * 
	 * @params	conbination_id		当TBLシーケンス値
	 * @params	now_block_count		次回ブロック数
	 * @return	false or updateCount
	 * @access	public
	 */
	public function updateAllocationConbinationNowBlockCount( $conbination_id, $now_block_count )
	{
		// 条件値/更新値設定
		$where_params  = array( "conbination_id"  => $conbination_id );
		$update_params = array( "now_block_count" => $now_block_count );
		
		$result = $this->_db->updateExecute( 'testee_allocation_conbination', $update_params, $where_params, true );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付シードデータ登録
	 * 
	 * @params	testee_id			臨床試験支援DBのID
	 * @params	conbination_id		割付層ID
	 * @params	seed				シード値
	 * @params	block_count			ブロック数
	 * @params	content_count		可変ブロック時症例数
	 * @return	false or allocation_seed_id
	 * @access	public
	 */
	public function insertAllocationSeed( $testee_id, $conbination_id, $seed, $block_count, $content_count = 0 )
	{
		// 登録値設定
		$insert_params = array( "testee_id"      => $testee_id,
								"conbination_id" => $conbination_id,
								"seed"           => $seed,
								"block_count"    => $block_count,
								"content_count"  => $content_count );
		
		// SQL実施
		$result = $this->_db->insertExecute( 'testee_allocation_seed', $insert_params, true, 'allocation_seed_id' );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付ブロックデータ登録
	 * 
	 * @params	testee_id			臨床試験支援DBのID
	 * @params	conbination_id		割付層ID
	 * @params	allocation_seed_id	割付シードID
	 * @params	sequence_no			連番
	 * @params	allocation_group_id	割付群ID
	 * @params	allocation_flag		割付済みフラグ
	 * @return	false or allocation_block_id
	 * @access	public
	 */
	public function insertAllocationBlock( $testee_id, $conbination_id, $allocation_seed_id, $sequence_no, $allocation_group_id, $allocation_flag )
	{
		// 登録値設定
		$insert_params = array( "testee_id"           => $testee_id,
								"conbination_id"      => $conbination_id,
								"allocation_seed_id"  => $allocation_seed_id,
								"sequence_no"         => $sequence_no,
								"allocation_group_id" => $allocation_group_id,
								"allocation_flag"     => $allocation_flag );
		
		// SQL実施
		$result = $this->_db->insertExecute( 'testee_allocation_block', $insert_params, true, 'allocation_block_id' );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付ブロックデータを使用済みに変更する処理
	 * 
	 * @params	allocation_block_id			割付ブロックID
	 * @return	false or updateCount
	 * @access	public
	 */
	public function updateAllocationBlockUsed( $allocation_block_id )
	{
		// 更新値設定
		$update_params = array( "allocation_flag" => 1 );
		
		// 条件値設定
		$where_params = array( "allocation_block_id" => $allocation_block_id );
		
		// SQL実施
		$result = $this->_db->updateExecute( 'testee_allocation_block', $update_params, $where_params, true );
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付参照権限削除処理
	 * 
	 * @params	viewuser_id			割付参照権限シーケンスID
	 * @return	false or deleteCount
	 * @access	public
	 */
	public function deleteAllocationViewUser( $viewuser_id )
	{
		$where_params = array( "viewuser_id" => $viewuser_id );
		
		$result = $this->_db->deleteExecute("testee_allocation_viewuser", $where_params);
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	/**
	 * 割付参照権限登録処理
	 * 
	 * @params	testee_id			臨床試験支援システムDBのID
	 * @params	user_id				ユーザーID
	 * @return	false or viewuser_id
	 * @access	public
	 */
	public function insertAllocationViewUser( $testee_id, $user_id )
	{
		$insert_params = array( "testee_id" => $testee_id,
								"user_id"   => $user_id );
		
		$result = $this->_db->insertExecute("testee_allocation_viewuser", $insert_params, true, "viewuser_id");
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付情報割付可変ブロックテーブル登録処理
	 * 
	 * @params	testee_id			臨床試験支援システムDBのID
	 * @params	case_count			基準症例数
	 * @return	false or allocation_variable_block_id
	 * @access	public
	 */
	public function insertAllocationVariableBlock( $testee_id, $case_count )
	{
		$insert_params = array( "testee_id"  => $testee_id,
								"case_count" => $case_count );
		
		$result = $this->_db->insertExecute("testee_allocation_variable_block", $insert_params, true, "allocation_variable_block_id");
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	/**
	 * 割付情報割付可変ブロックテーブル削除処理
	 * 
	 * @params	allocation_variable_block_id	テーブルSEQID
	 * @return	false or 削除件数
	 * @access	public
	 */
	public function deleteAllocationVariableBlock( $allocation_variable_block_id )
	{
		$where_params = array( "allocation_variable_block_id" => $allocation_variable_block_id );
		
		$result = $this->_db->deleteExecute("testee_allocation_variable_block", $where_params);
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	/**
	 * 割付情報割付可変ブロックテーブル更新処理
	 * 
	 * @params	allocation_variable_block_id	テーブルSEQID
	 * @params	case_count						基準症例数
	 * @return	false or 更新件数
	 * @access	public
	 */
	public function updateAllocationVariableBlock( $allocation_variable_block_id, $case_count )
	{
		$where_params = array( "allocation_variable_block_id"  => $allocation_variable_block_id );
		
		$update_params = array( "case_count" => $case_count );
		
		$result = $this->_db->updateExecute("testee_allocation_variable_block", $update_params, $where_params, true);
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付シミュレーション設定登録
	 * 
	 * @params	testee_id		臨床試験支援ID
	 * @params	input_type		入力症例種類
	 * @params	case_cont		症例数
	 * @params	upload_id		uploadID
	 * @params	repeat_count	繰返し数
	 * @params	allocate_seed	割付用シード値
	 * @params	case_seed		症例用シード値
	 * @return	false or simsetting_id
	 * @access	public
	 */
	public function insertAllocationSimsetting( $testee_id, $input_type, $case_count, $upload_id, $repeat_count, $allocate_seed, $case_seed )
	{
		$insert_params = array( "testee_id"     => $testee_id,
								"input_type"    => $input_type,
								"case_count"    => $case_count,
								"upload_id"     => $upload_id,
								"repeat_count"  => $repeat_count,
								"allocate_seed" => $allocate_seed,
								"case_seed"     => $case_seed );
		
		$result = $this->_db->insertExecute('testee_allocation_simsetting', $insert_params, true, 'simsetting_id');
		if($result === false)
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付シミュレーション設定削除
	 * 
	 * @params	testee_id		臨床試験支援ID
	 * @return	false or 削除件数
	 * @access	public
	 */
	public function deleteAllocationSimsetting( $testee_id )
	{
		$where_params = array( "testee_id"  => $testee_id );
		
		$result = $this->_db->deleteExecute("testee_allocation_simsetting", $where_params);
		if($result === false)
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	/**
	 * 割付シミュレーション設定：upload_id更新
	 * 
	 * @params	testee_id		臨床試験支援ID
	 * @return	false or 削除件数
	 * @access	public
	 */
	public function updateAllocationSimsettingUploadID( $testee_id, $upload_id )
	{
		$where_params = array( "testee_id"  => $testee_id );
		
		$update_params = array( "upload_id" => $upload_id );
		
		$result = $this->_db->updateExecute("testee_allocation_simsetting", $update_params, $where_params, true);
    	if($result === false)
    	{
			$this->_db->addError();
    	}
    	
		return $result;
	}
	
	
	/**
	 * 割付シミュレーション設定：output_csv更新
	 * 
	 * @params	testee_id		臨床試験支援ID
	 * @return	false or 削除件数
	 * @access	public
	 */
	public function updateAllocationSimsettingOutputCSV( $testee_id, $output_csv )
	{
		$where_params = array( "testee_id"  => $testee_id );
		
		$update_params = array( "output_csv" => $output_csv );
		
		$result = $this->_db->updateExecute("testee_allocation_simsetting", $update_params, $where_params, true);
    	if($result === false)
    	{
			$this->_db->addError();
    	}
    	
		return $result;
	}
	
	
	public function updateAllocationSimsettingResultFlag( $testee_id, $result_flag )
	{
		$where_params = array( "testee_id"  => $testee_id );
		
		$update_params = array( "result_flag" => $result_flag );
		
		$result = $this->_db->updateExecute("testee_allocation_simsetting", $update_params, $where_params, true);
    	if($result === false)
    	{
			$this->_db->addError();
    	}
    	
		return $result;
	}
	
	
	public function insertAllocationSimResult( $testee_id, $factor_key, $counts )
	{
		$insert_params = array( "testee_id"  => $testee_id,
								"factor_key" => $factor_key,
								"counts"     => $counts );
	
		$result = $this->_db->insertExecute('testee_allocation_simresult', $insert_params, true, 'simresult_id');
		if($result === false)
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
	public function deleteAllocationSimResult( $testee_id )
	{
		$where_params = array( "testee_id" => $testee_id );
		
		$result = $this->_db->deleteExecute("testee_allocation_simresult", $where_params);
		if( $result === false )
		{
			$this->_db->addError();
		}
		
		return $result;
	}
	
	
}
?>
