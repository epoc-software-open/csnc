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
		$mdbView = $this->_container->getComponent("mdbView");

		// 割付設定の取得
		$allocation = $mdbView->getAllocationContent( $testee_id );
		//print_r( $allocation );

		// 割付設定を使用するに設定されているかのチェック
		if ( empty( $allocation ) || ( count( $allocation ) < 1 ) || $allocation["allocation_flag"] != 1 ) {
			return true;
		}

		// 群の設定の取得
		$group_content = $this->getGroupContent( $testee_id, $allocation );
		//print_r( $group_content );
		//test_log( $group_content );

		// 群が存在しない場合は、割付設定なしとみなして戻る
		if ( empty( $group_content ) || count( $group_content ) == 0 ) {
			return true;
		}

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
			$params = array (
						"content_id" => $content_id,
						"testee_id" => $testee_id,
						"allocation_group_id" => $ans
					);
			//print_r($params);
			$result = $this->_db->insertExecute('testee_content_group',
												$params,
												true,
												'testee_content_group_id');
			if ($result === false) {
				$this->_db->addError();
				return $result;
			}

			return true;
		}

		// --- 以下は強制割付

		// 割付調整因子毎のデータを取得
		$alloc_table = $this->getAllocTable( $testee_id, $datas, $group_content );
		//print_r( $alloc_table );

		// 割付可能なグループの算出
		$can_group = $this->getAllocCanGroup( $alloc_table, $allocation["group_differences"], $group_content );
		//print_r( $can_group );
		//test_log("--- can_group");
		//test_log( $can_group );
		//test_log("--- alloc_table");
		//test_log( $alloc_table );
		//test_log("--- group_differences");
		//test_log( $allocation["group_differences"] );
		//test_log("--- group_content");
		//test_log( $group_content );

		// 条件を満たす群がなかった場合(全て、許容できない群間差になるなど)
		if ( empty( $can_group ) ) {

			// 群間差が一番大きい割付調整因子を特定
			$max_differences_metadata_id = $this->getMaxDifferences( $alloc_table );
			//print_r( $max_differences_metadata_id );

			// 割付可能なグループの算出
			$max_alloc_table = array( $max_differences_metadata_id => $alloc_table[$max_differences_metadata_id] );
			$can_group = $this->getAllocCanGroup( $max_alloc_table, $allocation["group_differences"], $group_content );
			//print_r( $can_group );
		}

		// 群を決定
		$allocation_group_id = $this->rndAnswer( $can_group );
		//print_r( $allocation_group_id );

		// 100回テスト
		//$this->loopTest( $can_group );

		// 群テーブルに登録
		$params = array (
					"content_id" => $content_id,
					"testee_id" => $testee_id,
					"allocation_group_id" => $allocation_group_id
				);
		//print_r($params);
		$result = $this->_db->insertExecute('testee_content_group',
											$params,
											true,
											'testee_content_group_id');
		if ($result === false) {
			//test_log($this->_db->ErrorMsg());
			$this->_db->addError();
			
			return $result;
		}

		return true;
	}

	/**
	 * 群の設定の取得
	 * 
	 * @access	public
	 */
	function getGroupContent( $testee_id, $allocation )
	{

		$mdbView = $this->_container->getComponent("mdbView");

		// 群の設定(各グループ)の取得
		$group_content = $mdbView->getGroupContent( $testee_id );
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
	 * 割付調整因子毎のデータ取得
	 * 
	 * @access	public
	 */
	 // 
	function getAllocTable( $testee_id, $datas, $group_content )
	{

		// 割付グループの取得
		$params = array( "testee_id" => $testee_id );
		$sql =  "SELECT a.metadata_id, m.name " .
				"FROM {testee_adjustment} a " .
				" INNER JOIN {testee_metadata} m ON m.metadata_id = a.metadata_id AND m.testee_id = ? " .
				"ORDER BY a.desplay_seq";

		$result_metadatas = $this->_db->execute( $sql, $params );

		/*
			-- 割付調整因子として設定されている項目の取得
			SELECT a.metadata_id, m.name
			FROM netcommons2_testee_adjustment a
			  INNER JOIN netcommons2_testee_metadata m ON m.metadata_id = a.metadata_id AND m.testee_id = 1001
			ORDER BY a.desplay_seq

			metadata_id	name
			1017		病期
			1018		分化度

			-- 割付調整因子毎の登録されている群の取得
			SELECT alloc_group.allocation_group_id, COUNT(alloc_group.content_id) AS record_count
			FROM (
				SELECT mc.content_id, cg.allocation_group_id
				FROM netcommons2_testee_metadata_content mc
				  LEFT JOIN netcommons2_testee_content_group cg ON cg.content_id = mc.content_id
				WHERE mc.metadata_id = 1018 AND content = "高分化"
			) alloc_group
			GROUP BY alloc_group.allocation_group_id
			ORDER BY alloc_group.allocation_group_id
		*/

		// 割付調整因子として設定されている項目の取得
		$params = array( "testee_id" => $testee_id );
		$sql =  "SELECT a.metadata_id, m.name " .
				"FROM {testee_adjustment} a " .
				" INNER JOIN {testee_metadata} m ON m.metadata_id = a.metadata_id AND m.testee_id = ? " .
				"ORDER BY a.desplay_seq";

		$result_adjustment = $this->_db->execute( $sql, $params );

		if ($result_adjustment === false) {
			$this->_db->addError();
			return false;
		}

		// 割付調整因子毎の登録されている群
		$group_table = array();
		foreach( $result_metadatas as $result_metadata ) {
			foreach( $group_content as $group_key => $group_item ) {
				$group_table[$result_metadata["metadata_id"]][$group_key] = 0;
			}
		}
		//print_r($group_table);

		// 割付調整因子毎の登録されている群の取得
		foreach( $result_adjustment as $result_item ) {

			$params = array( "metadata_id" => $result_item["metadata_id"], $datas[$result_item["metadata_id"]] );
			$sql =  "SELECT alloc_group.allocation_group_id, COUNT(alloc_group.content_id) AS record_count " .
					"FROM ( " .
					  "SELECT mc.content_id, cg.allocation_group_id " .
					  "FROM {testee_metadata_content} mc " .
					    "LEFT JOIN {testee_content_group} cg ON cg.content_id = mc.content_id " .
					  "WHERE mc.metadata_id = ? AND content = ? " .
					  ") alloc_group " .
					"GROUP BY alloc_group.allocation_group_id " .
					"ORDER BY alloc_group.allocation_group_id";

			$result_alloc_group = $this->_db->execute( $sql, $params );
			if ($result_alloc_group === false) {
				$this->_db->addError();
				return false;
			}

			//print_r($result_alloc_group);
			foreach ( $result_alloc_group as $result_alloc_item ) {
				// 通常の運用ではないかもしれないが、割付設定なしでデータ登録 -> 割付設定ありでデータ登録にした場合、空のグループを取得してしまい、
				// データ登録時にエラーになるため、空のグループは使わないようにする。
				if ( !empty( $result_alloc_item["allocation_group_id"] ) ) {
					$group_table[$result_item["metadata_id"]][$result_alloc_item["allocation_group_id"]] = $result_alloc_item["record_count"];
				}
			}

		}
		//print_r($group_table);

		return $group_table;
	}

	/**
	 * 連想配列中の最大値を返す
	 * 
	 * @access	public
	 */
	function getMax( $param_array ) {

		$return_max = 0;
		foreach ( $param_array as $param_item ) {
			if ( $return_max < $param_item ) {
				$return_max = $param_item;
			}
		}
		return $return_max;
	}


	/**
	 * 連想配列中の最小値を返す
	 * 
	 * @access	public
	 */
	function getMin( $param_array ) {

		// 初期値は最大値で、それより小さいものを探す。
		$return_min = $this->getMax( $param_array );
		foreach ( $param_array as $param_item ) {
			if ( $return_min > $param_item ) {
				$return_min = $param_item;
			}
		}
		return $return_min;
	}

	/**
	 * 割付可能な群を返す
	 * 
	 * @access	public
	 */
	function getAllocCanGroup( $alloc_table, $group_differences, $group_content ) {
		//test_log("--- getAllocCanGroup");
		// 返す配列を作っておく。(最初はすべての割付けグループID を入れておく)
		// 割付可能な群をmetadata_id でループし、割付けグループID があるか確認。なければ、返す配列から削除。
		// これで、割付可能な群のみ残る

		$return_groups = $group_content;

		foreach( $return_groups as $allocation_group_id => $ratio ) {

			foreach ( $alloc_table as $metadata_id => $alloc_table_record ) {

				$max_count = $this->getMin( $alloc_table_record ) + $group_differences;

				foreach ( $alloc_table_record as $alloc_key => $alloc_item ) {

					// ここで +1 すると、許容する群間差が 1 で実際の差が 1 場合、全てが登録できるグループがなくなる
					//if ( $alloc_key == $allocation_group_id && ( $alloc_item + 1 ) >= $max_count ) {
					if ( $alloc_key == $allocation_group_id && ( $alloc_item ) >= $max_count ) {
						//test_log("// allocation_group_id");
						//test_log($allocation_group_id);
						//test_log("// alloc_item");
						//test_log($alloc_item);
						//test_log("// max_count");
						//test_log($max_count);
						unset( $return_groups[$allocation_group_id] );
					}
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
	 * 群間差が一番大きい割付調整因子を特定
	 * 
	 * @access	public
	 */
	function getMaxDifferences( $alloc_table ) {

		$max_differences_metadata_id = null;
		$max_differences = 0;

		foreach ( $alloc_table as $metadata_id => $alloc_table_record ) {

			if ( $max_differences < $this->getMax( $alloc_table_record ) - $this->getMin( $alloc_table_record ) ) {

				$max_differences_metadata_id = $metadata_id;
				$max_differences = $this->getMax( $alloc_table_record ) - $this->getMin( $alloc_table_record );
			}
		}
		return $max_differences_metadata_id;
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
}
?>
