<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Components_View
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
	 * @var ページ
	 *
	 * @access	private
	 */


	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Testee_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 権限判断用のSQL文FROM句を取得する
	 *
     * @return string	権限判断用のSQL文FROM句
	 * @access	public
	 */
	function &_getAuthorityFromSQL() {
		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$sql = "";
		if ($auth_id >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "LEFT JOIN {pages_users_link} PU ".
					"ON {testee_content}.insert_user_id = PU.user_id ".
					"AND {testee_content}.room_id = PU.room_id ";
		$sql .= "LEFT JOIN {authorities} A ".
					"ON A.role_authority_id = PU.role_authority_id ";
		return $sql;
	}

	/**
	 * 権限判断用のSQL文WHERE句を取得する
	 * パラメータ用配列に必要な値を追加する
	 *
	 * @param	array	$params	パラメータ用配列
     * @return string	権限判断用のSQL文WHERE句
	 * @access	public
	 */
	function &_getAuthorityWhereSQL(&$params) {

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");

		$sql = "";
		if ($auth_id >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "AND (({testee_content}.temporary_flag = ? AND {testee_content}.agree_flag = ?) OR A.hierarchy < ? OR {testee_content}.insert_user_id = ?";

		$defaultEntry = $session->getParameter("_default_entry_flag");

		$hierarchy = $session->getParameter("_hierarchy");

		if ($defaultEntry == _ON && $hierarchy > $session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR A.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		//$request =& $this->_container->getComponent("Request");
		//$params[] = $request->getParameter("room_id");
		$params[] = TESTEE_STATUS_RELEASED_VALUE;
		$params[] = TESTEE_STATUS_AGREE_VALUE;
		$params[] = $hierarchy;
		$params[] = $session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * 汎用データベースが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock() {
		$request =& $this->_container->getComponent("Request");
		$params = array($request->getParameter("testee_id"));
		$sql = "SELECT M.room_id, B.block_id ".
				"FROM {testee} M ".
				"INNER JOIN {testee_block} B ".
				"ON M.testee_id = B.testee_id ".
				"WHERE M.testee_id = ? ".
				"ORDER BY B.block_id";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0];
	}

	/**
	 * 在配置されている汎用データベースIDを取得する
	 *
     * @return string	配置されている汎用データベースID
	 * @access	public
	 */
	function &getCurrentMdbId() {
		$request =& $this->_container->getComponent("Request");
		$params = array($request->getParameter("block_id"));
		$sql = "SELECT testee_id ".
				"FROM {testee_block} ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0]["testee_id"];
	}

	/**
	 * 汎用データベースが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function mdbExists() {
		$request =& $this->_container->getComponent("Request");
		$params = array(
			$request->getParameter("testee_id"),
			$request->getParameter("room_id")
		);
		$sql = "SELECT testee_id ".
				"FROM {testee} ".
				"WHERE testee_id = ? ".
				"AND room_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if (count($result) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 汎用データベース用デフォルトデータを取得する
	 *
     * @return array	汎用データベース用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultMdb() {
		$configView =& $this->_container->getComponent("configView");
		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
        	return $config;
        }

		$mdb = array(
			"active_flag" => constant($config["active_flag"]["conf_value"]),
			"mail_flag" => constant($config["mail_flag"]["conf_value"]),
			"contents_authority" => constant($config["contents_authority"]["conf_value"]),
			"new_period" => $config["new_period"]["conf_value"],
			"vote_flag" => constant($config["vote_flag"]["conf_value"]),
			"comment_flag" => constant($config["comment_flag"]["conf_value"]),
			"agree_flag" => constant($config["agree_flag"]["conf_value"]),
			"agree_mail_flag" => constant($config["agree_mail_flag"]["conf_value"]),
			"visible_item" => $config["visible_item"]["conf_value"],
			"default_sort" => constant($config["default_sort"]["conf_value"])
		);

		return $mdb;
	}

	/**
	 * 汎用データベースデータを取得する
	 *
     * @return array	汎用データベースデータ配列
	 * @access	public
	 */
	function &getMdb() {
		$request =& $this->_container->getComponent("Request");
		$configView =& $this->_container->getComponent("configView");

		$sql = "SELECT testee_id, testee_name, ";
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "testee_view_edit_create" ||
				$actionName == "testee_action_edit_create" || $actionName == "testee_view_edit_modify") {
			$sql .= "active_flag, mail_flag, mail_authority, mail_subject, mail_body, contents_authority, new_period, vote_flag, ".
							"comment_flag, agree_flag, agree_mail_flag, agree_mail_subject, agree_mail_body, title_metadata_id, kentai_switch ";
		} else {
			$prefix_id_name = $request->getParameter("prefix_id_name");
			if ($prefix_id_name == TESTEE_REFERENCE_PREFIX_NAME.$request->getParameter("testee_id")) {
				$sql .= _OFF . " AS active_flag";
			} else {
				$sql .= "active_flag";
			}
			$sql .= ", mail_flag, mail_authority, vote_flag, comment_flag, new_period, agree_flag, agree_mail_flag, title_metadata_id ";
		}

		$params = array($request->getParameter("testee_id"));
		$sql .=	"FROM {testee} ".
				"WHERE testee_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
        	return $config;
        }

        $result[0]['visible_item'] = $config["visible_item"]["conf_value"];
        $result[0]['default_sort'] = constant($config["default_sort"]["conf_value"]);

		return $result[0];
	}

	/**
	 * 現在配置されている汎用データベースデータを取得する
	 *
     * @return array	配置されている汎用データベースデータ配列
	 * @access	public
	 */
	function &getCurrentMdb() {
		$request =& $this->_container->getComponent("Request");

		$params = array($request->getParameter("block_id"));
		$sql = "SELECT B.block_id, B.testee_id, B.visible_item, B.default_sort, ".
					"M.testee_name, M.active_flag, M.contents_authority, M.new_period, M.mail_flag, M.mail_authority, ".
					"M.mail_subject, M.mail_body, M.vote_flag, M.comment_flag, M.agree_flag, M.agree_mail_flag, ".
					"M.agree_mail_subject, M.agree_mail_body,M.title_metadata_id,M.kentai_switch ".
				"FROM {testee_block} B ".
				"INNER JOIN {testee} M ".
				"ON B.testee_id = M.testee_id ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$result[0]['post_auth'] = $this->_hasPostAuthority($result[0]);
		$result[0]['new_period_time'] = $this->_getNewPeriodTime($result[0]["new_period"]);

		return $result[0];
	}

	/**
	 * コンテンツ投稿権限を取得する
	 *
	 * @param	array	$bbs	汎用データベース状態、表示方法、コンテンツ投稿権限の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasPostAuthority($mdb) {
		if ($mdb["active_flag"] != _ON) {
			return false;
		}

		$session =& $this->_container->getComponent("Session");
		$auth_id = $session->getParameter("_auth_id");
		if ($auth_id >= $mdb["contents_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDの汎用データベース件数を取得する
	 *
     * @return string	汎用データベース件数
	 * @access	public
	 */
	function getMdbCount() {
		$request =& $this->_container->getComponent("Request");
    	$params["room_id"] = $request->getParameter("room_id");
    	$count = $this->_db->countExecute("testee", $params);

		return $count;
	}

	/**
	 * 汎用データベース一覧データを取得する
	 *
     * @return array	汎用データベース一覧データ配列
	 * @access	public
	 */
	function &getMdbs() {
		$request =& $this->_container->getComponent("Request");
		$limit = $request->getParameter("limit");
		$offset = $request->getParameter("offset");

		$sortColumn = $request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "testee_id";
		}
		$sortDirection = $request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($request->getParameter("room_id"));
		$sql = "SELECT testee_id, testee_name, active_flag, insert_time, insert_user_id, insert_user_name ".
				"FROM {testee} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$result = $this->_db->execute($sql, $params, $limit, $offset);
		if ($result === false) {
			$this->_db->addError();
		}

		return $result;
	}

    /**
	 * metadataを取得する
	 *
	 * @param   int   $metadata_id  項目ID
	 * @return array
	 * @access	public
	 */
	function &getMetadataById($metadata_id)	{
		$result =& $this->_db->selectExecute("testee_metadata", array("metadata_id"=>intval($metadata_id)), null, 1, 0);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0];
	}

	/**
	 * 汎用データベースobjectを取得する
	 *
	 * @param   int   $testee_id
	 * @return array
	 * @access	public
	 */
	function &getMdbById($testee_id)
	{
		$result =& $this->_db->selectExecute("testee", array("testee_id"=>intval($testee_id)));
		if($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result[0];
	}

	/**
	 *
	 * メタデータ情報取得取得
	 * @param array params
	 * @param array $order_params
	 * @return array
	 */
	function &getMetadatas($params, $order_params = null) {
		if(!isset($params)) {
			return false;
		}

		if(!isset($order_params)) {
			$order_params = array(
				"display_pos" => "ASC",
	        	"display_sequence" => "ASC"
	        );
		}

		$result = $this->_db->selectExecute("testee_metadata", $params, $order_params, null, null, array($this,"_getMetadatasFetchcallback"));
		if ( $result === false ) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getMetadatasFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if($row['type'] == TESTEE_META_TYPE_SECTION 
				|| $row['type'] == TESTEE_META_TYPE_MULTIPLE 
				|| $row['type'] == TESTEE_META_TYPE_N_GROUP 		// 追加メタタイプ
				|| $row['type'] == TESTEE_META_TYPE_N_HOSPITAL 		// 追加メタタイプ
				|| $row['type'] == TESTEE_META_TYPE_N_SEX 			// 追加メタタイプ
				|| $row['type'] == TESTEE_META_TYPE_N_RADIO			// 追加メタタイプ
				|| $row['type'] == TESTEE_META_TYPE_N_YESNO			// 追加メタタイプ
				|| $row['type'] == TESTEE_META_TYPE_N_APPLICABLE) {	// 追加メタタイプ
				$options = explode("|", $row['select_content']);
				$count = 0;
				foreach($options as $option) {
					$row['select_content_array'][$count] = $option;
					$count++;
				}
			}
			$ret[$row['metadata_id']] = $row;
		}
		return $ret;
	}

	function &getLayout($params) {
		if(!is_array($params)) {
			return false;
		}

		$result = array();
		$pos_1 = $this->getMetadatas(array_merge($params, array("display_pos" => 1)));
    	if($pos_1 === false) {
    		$this->_db->addError();
			return $pos_1;
    	}
		$pos_2 = $this->getMetadatas(array_merge($params, array("display_pos" => 2)));
    	if($pos_2 === false) {
    		$this->_db->addError();
			return $pos_2;
    	}
    	$pos_3 = $this->getMetadatas(array_merge($params, array("display_pos" => 3)));
    	if($pos_3 === false) {
    		$this->_db->addError();
			return $pos_3;
    	}
    	$pos_4 = $this->getMetadatas(array_merge($params, array("display_pos" => 4)));
    	if($pos_4 === false) {
    		$this->_db->addError();
			return $pos_4;
    	}

    	$result[1] = $pos_1;
    	$result[2] = $pos_2;
    	$result[3] = $pos_3;
    	$result[4] = $pos_4;

    	return $result;
	}

	/**
	 * 入力データ用SQLを取得する
	 *
     * @return array	入力データ用SQL
	 * @access	public
	 */
	function &_getDataSQL($metadatas, $add_select= "") {
		// 施設情報のitemu_idを取得する		EddyK
		$where_item = PROFILE;
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$select_item_id = null;
		if(count($result) > 0){
			$select_item_id = $result[0]["item_id"];
		}

		$select = "";
		$join = "";
		foreach ($metadatas as $key => $metadata) {
			$alias = "m_content". $metadata['metadata_id'];
			$select .= ", ". $alias. ".content as content". $metadata['metadata_id']. " ";
			$join .= " LEFT JOIN {testee_metadata_content} ". $alias. " ".
						" ON {testee_content}.content_id = ". $alias. ".content_id ".
						" AND ". $alias. ".metadata_id = ". $metadata['metadata_id']. " ";

			if ($metadata['type'] == TESTEE_META_TYPE_FILE || $metadata['type'] == TESTEE_META_TYPE_IMAGE) {
				$select .= ", F".$metadata['metadata_id'].".upload_id as upload_id". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".file_name as file_name". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".physical_file_name as physical_file_name". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".file_password as file_password". $metadata['metadata_id']. " ";
				$select .= ", F".$metadata['metadata_id'].".download_count as download_count". $metadata['metadata_id']. " ";
				$join .= " LEFT JOIN {testee_file} F".$metadata['metadata_id'].
							" ON ". $alias. ".metadata_content_id = F".$metadata['metadata_id'].".metadata_content_id ";
			}
		}

		// SELECT 項目に施設情報({users_items_link}.content) を追加 EddyK
		if($select_item_id){
			$join .= " LEFT JOIN {users_items_link} ".
						" ON {testee_content}.insert_user_id = {users_items_link}.user_id ".
						" AND {users_items_link}.item_id = ". $select_item_id . " ";
		}
		// content_no、施設情報({users_items_link}.content) を追加 EddyK
		$sql = "SELECT {testee}.mail_authority, {testee}.mail_subject,{testee}.mail_body,".
				"{testee}.testee_name,{testee_content}.content_id,".
				"{testee_content}.insert_time,{testee_content}.vote, {testee_content}.vote_count,".
				"{testee_content}.insert_user_id,{testee_content}.insert_user_name,".
				"{testee_content}.update_time,{testee_content}.update_user_id,{testee_content}.update_user_name," .
				"{testee_content}.agree_flag,{testee_content}.temporary_flag ". $select.$add_select.
				", {testee_content}.content_no";

		$sql .= ", {testee_allocation_group}.group_name, {testee_allocation_group}.intervention ";

		if($select_item_id){
			$sql .= ", {users_items_link}.content ";
		}
		$sql .= " FROM {testee} ".
				"INNER JOIN {testee_content} ".
				"ON {testee}.testee_id = {testee_content}.testee_id ";

		// 割付データ
		$sql .= "LEFT JOIN {testee_content_group} ON {testee_content_group}.content_id = {testee_content}.content_id ";
		$sql .= "LEFT JOIN {testee_allocation_group} ON {testee_allocation_group}.allocation_group_id = {testee_content_group}.allocation_group_id ";

		$sql .= $join;


		// -----------------------------------------------------------
		// - 検体管理機能の絞り込み
		// -----------------------------------------------------------

		$session =& $this->_container->getComponent("Session");
		$kentai_search_status = $session->getParameter("kentai_search_status");

		// 検体管理
		if ( !empty( $kentai_search_status ) ) {
			if ( array_key_exists( 1, $kentai_search_status ) && $kentai_search_status[1] == "1" ) {

				$sql .= " INNER JOIN {testee_content_status} s1 ".
						"ON {testee_content}.testee_id = s1.testee_id ".
						"AND {testee_content}.content_id = s1.content_id ".
						"AND s1.status_id = 1 AND s1.content_no = 1 ";
			}
			if ( array_key_exists( 3, $kentai_search_status ) && $kentai_search_status[3] != "" ) {

				$sql .= " INNER JOIN {testee_content_status} s2 ".
						"ON {testee_content}.testee_id = s2.testee_id ".
						"AND {testee_content}.content_id = s2.content_id ".
						"AND s2.status_id = 3 AND s2.content_no = 1 AND s2.content_data = '" . intval( $kentai_search_status[3] ) . "'";
			}
			if ( array_key_exists( 4, $kentai_search_status ) && $kentai_search_status[4] == "1" ) {

				$sql .= " INNER JOIN {testee_content_status} s3 ".
						"ON {testee_content}.testee_id = s3.testee_id ".
						"AND {testee_content}.content_id = s3.content_id ".
						"AND s3.status_id = 4 AND s3.content_no = 1 ";
			}
		}

		// -----------------------------------------------------------
		// - /検体管理機能の絞り込み
		// -----------------------------------------------------------

		return $sql;
	}

	/**
	 * 一覧数取得
	 * @param string $testee_id 汎用データベースID
	 * @param array $metadatas メタデータ項目配列
	 * @param array $where_params 絞込み文字列配列
	 * @param string $wheresql キーワード検索条件WHERE句文字列
	 * @param array $keywordBindValues 検索キーワードデータ配列
	 * @return コンテンツ件数
	 */
	function &getMDBListCount($testee_id, $metadatas, $where_params, $wheresql = '', $keywordBindValues = array(), $target_hospital = null ) {
		// 施設情報がある場合は、同一施設者の登録情報を対象とする		EddyK
		$select_item_id = null;
		if($target_hospital){
			// 施設情報のitemu_idを取得する		EddyK
			$where_item = PROFILE;
			$sql_params = array( $where_item );
			$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
			$result = $this->_db->execute($sql, $sql_params);
			if ($result === false) {
			       	$this->_db->addError();
			       	return 'error';
			}
			if(count($result) > 0){
				$select_item_id = $result[0]["item_id"];
			}
		}

		$sql = "";
		if(!empty($metadatas)){
			$sql = "SELECT count({testee_content}.content_id) list_count";
			$sql_join = "";
			$sql_where = "";

			foreach (array_keys($metadatas) as $i) {
				$key = 'm_content' . $i . '.content';
				if ((!isset($where_params[$key])
						|| !strlen($where_params[$key]))
					&& $wheresql == "") {
					continue;
				}

				$sql_join .= "LEFT JOIN {testee_metadata_content} m_content".$metadatas[$i]['metadata_id'];
				$sql_join .= " ON ({testee_content}.content_id = m_content".$metadatas[$i]['metadata_id'].".content_id";
				$sql_join .= " AND m_content".$metadatas[$i]['metadata_id'].".metadata_id = ". $metadatas[$i]['metadata_id'].") ";
			}


			// -----------------------------------------------------------
			// - 検体管理機能の絞り込み
			// -----------------------------------------------------------

			$session =& $this->_container->getComponent("Session");
			$kentai_search_status = $session->getParameter("kentai_search_status");

			// 検体管理
			if ( !empty( $kentai_search_status ) ) {
				if ( array_key_exists( 1, $kentai_search_status ) && $kentai_search_status[1] == "1" ) {

					$sql_join .= " INNER JOIN {testee_content_status} s1 ".
							"ON {testee_content}.testee_id = s1.testee_id ".
							"AND {testee_content}.content_id = s1.content_id ".
							"AND s1.status_id = 1 AND s1.content_no = 1 ";
				}
				if ( array_key_exists( 3, $kentai_search_status ) && $kentai_search_status[3] != "" ) {

					$sql_join .= " INNER JOIN {testee_content_status} s2 ".
							"ON {testee_content}.testee_id = s2.testee_id ".
							"AND {testee_content}.content_id = s2.content_id ".
							"AND s2.status_id = 3 AND s2.content_no = 1 AND s2.content_data = '" . intval( $kentai_search_status[3] ) . "'";
				}
				if ( array_key_exists( 4, $kentai_search_status ) && $kentai_search_status[4] == "1" ) {

					$sql_join .= " INNER JOIN {testee_content_status} s3 ".
							"ON {testee_content}.testee_id = s3.testee_id ".
							"AND {testee_content}.content_id = s3.content_id ".
							"AND s3.status_id = 4 AND s3.content_no = 1 ";
				}
			}

			// -----------------------------------------------------------
			// - /検体管理機能の絞り込み
			// -----------------------------------------------------------

			list($sql_where, $params) = $this->_getSqlContentWhereStatement($testee_id, $where_params, $keywordBindValues);

			$sql .= " FROM {testee_content} ";
			$sql .= $sql_join;
			$sql .= $this->_getAuthorityFromSQL();
			// 施設情報がある場合、施設情報({users_items_link}.content) を抽出条件に追加 EddyK
			if($select_item_id){
				$sql .= " LEFT JOIN {users_items_link} ".
							" ON {testee_content}.insert_user_id = {users_items_link}.user_id ".
							" AND {users_items_link}.item_id = ". $select_item_id . " ";
			}
			$sql .= $sql_where;
			$sql .= $wheresql;
			$sql .= $this->_getAuthorityWhereSQL($params);
			// 施設情報がある場合、施設情報({users_items_link}.content) を抽出条件に追加 EddyK
			if($select_item_id){
				$sql .= " AND {users_items_link}.content = ? ";
				array_push($params, $target_hospital);
			}
		}
		$result = $this->_db->execute($sql, $params);
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}

		$count = $result[0]['list_count'];
		return $count;
	}

	/**
	 * 一覧取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMDBList($testee_id, $metadatas, $where_params, $order_params, $disp_cnt=0, $begin=0, $target_hospital = null ) {
		$sql = "";
		if(!empty($metadatas)){
			list($sql_where, $params) = $this->_getSqlContentWhereStatement($testee_id, $where_params);

			$sql .= $this->_getDataSQL($metadatas, ',URL.short_url');
			$sql .= $this->_getAuthorityFromSQL();
			$sql .= $this->_getAbbreviateUrlJoinStatement();
			$sql .= $sql_where;
			$sql .= $this->_getAuthorityWhereSQL($params);
			// 施設情報がある場合、施設情報({users_items_link}.content) を抽出条件に追加 EddyK
			if($target_hospital){
				$sql .= " AND {users_items_link}.content = ? ";
				array_push($params, $target_hospital);
			}
			$sql .= $this->_db->getOrderSQL($order_params);
		}

		$result = $this->_db->execute($sql, $params ,$disp_cnt, $begin, true, array($this,"_getMDBListFetchcallback"));
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	function getImageBlockSize($file_name, $display_pos=null) {
		$real_size = array();
		if($display_pos == "1" || $display_pos == "4") {
			$width = TESTEE_IMAGE_LIST_WIDTH;		// 画像サイズ(幅)
			$height = TESTEE_IMAGE_LIST_HEIGHT;	// 画像サイズ(高さ)
		}else {
			$width = TESTEE_IMAGE_DEF_LIST_WIDTH;		// 画像サイズ(幅)
			$height = TESTEE_IMAGE_DEF_LIST_HEIGHT;	// 画像サイズ(高さ)
		}
		$image_size = getimagesize(FILEUPLOADS_DIR."testee/".$file_name);
		if($image_size[0] <= $width) {
			return $image_size;
		}
		$rate_num = (float)($image_size[1]/$image_size[0]);
		$height = intval((float)($width*$rate_num));
		$real_size[0] = $width;
		$real_size[1] = $height;
		return $real_size;
	}

	function &getFileLink($url, $file_name, $physical_file_name, $metadata, $extent_url=null, $insert_user_id=null, $file_password=null) {
		$value = "";

		$session =& $this->_container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");
		$smartphone_flag = $session->getParameter("_smartphone_flag");

		if(!empty($url) && strpos($url, "upload_id=") != false) {
			if($metadata['type'] == TESTEE_META_TYPE_IMAGE) {
				$size = $this->getImageBlockSize($physical_file_name, $metadata['display_pos']);

				if ($mobile_flag == _ON) {
					if($smartphone_flag == _ON) {
						$w = '&amp;w=' . TESTEE_IMAGE_DEF_SMARTPHONE_WIDTH;
					} else {
						$w = '';
					}
					$value = "<a target='_blank' href='" . htmlspecialchars($extent_url.$url."&metadata_id=".$metadata['metadata_id']."&download_flag="._ON, ENT_QUOTES) . $w . "&amp;" . session_name() . "=" . session_id() . "'" . ' data-ajax="false"  data-rel="dialog" ' . "><img src='". htmlspecialchars($extent_url.$url."&metadata_id=".$metadata['metadata_id']."&download_flag="._ON, ENT_QUOTES) . "&amp;w=" . TESTEE_IMAGE_DEF_MOBILE_LIST_WIDTH . "&amp;" . session_name() . "=" . session_id() . "' title='".$file_name."' alt='".$file_name."' /></a>";
				} else{
					$value = "<a href='#' onclick=\"commonCls.showPopupImageFullScale(this); return false;\"><img src='".htmlspecialchars($extent_url.$url."&metadata_id=".$metadata['metadata_id']."&download_flag="._ON, ENT_QUOTES)."' title='".$file_name."' alt='".$file_name."' style='height:".$size[1]."px;width:".$size[0]."px;padding:10px;' /></a>";
				}
			}else if($metadata['type'] == TESTEE_META_TYPE_FILE) {
				$session =& $this->_container->getComponent("Session");
				$user_id = $session->getParameter("_user_id");
				$auth_id = $session->getParameter("_auth_id");
				$pathList = explode("&", $url);
				$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
				$id = $session->getParameter("_id");
				if(!empty($file_password) && $metadata['file_password_flag'] == _ON) {
					$request =& $this->_container->getComponent("Request");
					$block_id = $request->getParameter("block_id");
					$testee_id = $request->getParameter("testee_id");
					$room_id = $request->getParameter("room_id");
					if ($mobile_flag == _ON) {
						$filepwd_nonform = $request->getParameter("filepwd_nonform");
						if (isset($filepwd_nonform) && $filepwd_nonform == _ON) {
							//formに「しない」指定あり。
							//コンテンツ編集画面なので、<form>の必要なし。逆にformにすると、formの入れ子になり画面がくずれる。
							//
							$value = "<a href='". $extent_url . $url . "&amp;" . session_name() . "=" . session_id() . "' >".$file_name."</a>";
						} else {
							//登録した本人、あるいは主担以上の権限者ならfile_passwordをセットして表示する。
							$file_password_val = ($insert_user_id == $user_id || $auth_id >= _AUTH_CHIEF ) ? $file_password : "";

							$filterChain =& $this->_container->getComponent("FilterChain");
							$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
							$btn_val = sprintf($smartyAssign->getLang("_ref"));

							$token =& $this->_container->getComponent("Token");
							$token_name = "_token";		//nccore/TokenExtra.class.phpのcheck()が_token固定に変更されているので合わせた。
							$token_val = $token->getValue();
							$metadataId = $metadata['metadata_id'];
							if($smartphone_flag == _ON) {
								$w = '<input type="hidden" name="w" value="' . TESTEE_IMAGE_DEF_SMARTPHONE_WIDTH . '" />';
							} else {
								$w = '';
							}
							$value =  '<form action=".' . INDEX_FILE_NAME . '" method="get" data-ajax="false">'
									. "<input type='hidden' name='action' value='testee_action_main_filedownload' >"
									. "<input type='hidden' name='upload_id' value='{$upload_id}' >"
									. "<input type='hidden' name='metadata_id' value='{$metadataId}' >"
									. "<input type='hidden' name='download_flag' value='1' >"
									. $w
									. "<input type='hidden' name='". session_name() . "' value='" . session_id() . "' >"
									. $file_name . "<br />"
									. sprintf( TESTEE_MOBILE_FILE_PASSWORD_INPUT, $btn_val )
									. "<input type='password' size='10' maxlength='100' name='password' value='{$file_password_val}' >"
									. "<input type='submit' name='ref' value='{$btn_val}' >"
									. "</form>";
						}
					} else {
						$value = "<a href='#' onclick=\"commonCls.sendPopupView(event,{action:'testee_view_main_filepassword', block_id:'".$block_id."', upload_id:'".$upload_id."', metadata_id:'".$metadata['metadata_id']."', insert_user_id:'".$insert_user_id."', prefix_id_name:'mdb_popup_password'}, {'modal_flag':true});return false;\">".$file_name."</a>";
					}
				} else {
					if ($mobile_flag == _ON) {
						if($smartphone_flag == _ON) {
							$w = '&amp;w=' . TESTEE_IMAGE_DEF_SMARTPHONE_WIDTH;
						} else {
							$w = '';
						}
						$value = "<a target='_blank' href='". $extent_url . "?action=testee_action_main_filedownload&amp;download_flag=1&amp;upload_id=" . $upload_id ."&amp;metadata_id=". $metadata['metadata_id'] . $w . "&amp;" . session_name() . "=" . session_id() . "'" . ' data-ajax="false">' . $file_name. " </a>";
					} else {
						if(!empty($extent_url)) {
							$value = "<a href='".$extent_url."?action=testee_action_main_filedownload&amp;download_flag="._ON."&amp;upload_id=".$upload_id."&amp;metadata_id=".$metadata['metadata_id']."' target='_blank'>".$file_name."</a>";
						}else {
							$value = "<a href='?action=testee_action_main_filedownload&amp;download_flag="._ON."&amp;upload_id=".$upload_id."&amp;metadata_id=".$metadata['metadata_id']."' onclick=\"mdbCls['".$id."'].setDownloadCount('".$upload_id."');\">".$file_name."</a>";
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getMDBListFetchcallback($result) {
		$session =& $this->_container->getComponent("Session");
		$request =& $this->_container->getComponent("Request");
		$metadatas = $request->getParameter("metadatas");

		$data = array();
		while ($row = $result->fetchRow()) {
			$layout = array();
			$items = array();
			foreach ($metadatas as $metadata) {
				if($metadata['list_flag'] == _ON) {
					$layout[$metadata['display_pos']][$metadata['metadata_id']] = $metadata;
				}
				if($metadata['type'] == TESTEE_META_TYPE_IMAGE || $metadata['type'] == TESTEE_META_TYPE_FILE) {
					$items[$metadata['metadata_id']] = $this->getFileLink($row['content'.$metadata['metadata_id']],
																			$row['file_name'.$metadata['metadata_id']],
																			$row['physical_file_name'.$metadata['metadata_id']],
																			$metadata, null, $row['insert_user_id'], $row['file_password'.$metadata['metadata_id']]);
					unset($row['file_name'.$metadata['metadata_id']]);
					unset($row['physical_file_name'.$metadata['metadata_id']]);
				}elseif ($metadata['type'] == TESTEE_META_TYPE_MULTIPLE) {
					$itemArr = explode("|",$row['content'.$metadata['metadata_id']]);
					$multiple = array();
					foreach ($itemArr as $val) {
						$multiple[] = $val;
					}
					$items[$metadata['metadata_id']] = $multiple;
				}else {
					$items[$metadata['metadata_id']] = $row['content'.$metadata['metadata_id']];
				}
				unset($row['content'.$metadata['metadata_id']]);
			}

			foreach($row as $key => $val) {
				$items[$key] = $val;
			}
			$voted = false;
			if($items['vote'] != "") {
				$who_voted = explode(",", $items['vote']);
				$user_id = $session->getParameter("_user_id");
				if(empty($user_id)) {
					$votes = $session->getParameter("testee_votes");
					if(!empty($votes)) {
						if(in_array($items['content_id'], $votes)) {
							$voted = true;
						}
					}
				}else if (in_array($user_id, $who_voted)) {
					$voted = true;
				}
			}
			$items['voted'] = $voted;

			$data['metadata'] = $layout;
			$data['value'][] = $items;
		}
		return $data;
	}

	/**
	 * 汎用ＤＢを取得する
	 *
	 * @param	int photoalbum_id
     * @return 　array
	 * @access	public
	 */
	function &getTestee($testee_id) {
		$result = $this->_db->selectExecute("testee", array("testee_id" => intval($testee_id)));
		if($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * new記号表示期間から対象年月日を取得する
	 *
	 * @param	string	$new_period		new記号表示期間
     * @return string	new記号表示対象年月日(YmdHis)
	 * @access	public
	 */
	function &_getNewPeriodTime($new_period) {
		if (empty($new_period)) {
			$new_period = -1;
		}

		$time = timezone_date();
		$time = mktime(0, 0, 0,
						intval(substr($time, 4, 2)),
						intval(substr($time, 6, 2)) - $new_period,
						intval(substr($time, 0, 4))
						);
		$time = date("YmdHis", $time);

		return $time;
	}

	/**
	 * コンテンツの詳細取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMDBDetail($content_id, $metadatas) {
		$sql = "";

		$params = array(
			"content_id" => $content_id
		);

		if(!empty($metadatas)){
			$sql .= $this->_getDataSQL($metadatas);
			$sql .= $this->_getAuthorityFromSQL();
			$sql .= " WHERE {testee_content}.content_id=? ";
			$sql .= $this->_getAuthorityWhereSQL($params);
		}

		$result = $this->_db->execute($sql, $params ,null, null, true, array($this,"_getMDBDetailFetchcallback"));
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getMDBDetailFetchcallback($result) {
		$session =& $this->_container->getComponent("Session");
		$request =& $this->_container->getComponent("Request");
		$metadatas = $request->getParameter("metadatas");

		$data = array();
		while ($row = $result->fetchRow()) {
			$layout = array();
			$items = array();
			foreach ($metadatas as $metadata) {
				if($metadata['detail_flag'] == _ON) {
					$layout[$metadata['display_pos']][$metadata['metadata_id']] = $metadata;
				}
				if($metadata['type'] == TESTEE_META_TYPE_IMAGE || $metadata['type'] == TESTEE_META_TYPE_FILE) {
					$items[$metadata['metadata_id']] = $this->getFileLink($row['content'.$metadata['metadata_id']],
																			$row['file_name'.$metadata['metadata_id']],
																			$row['physical_file_name'.$metadata['metadata_id']],
																			$metadata, null, $row['insert_user_id'], $row['file_password'.$metadata['metadata_id']]);
					unset($row['file_name'.$metadata['metadata_id']]);
					unset($row['physical_file_name'.$metadata['metadata_id']]);
				}elseif ($metadata['type'] == TESTEE_META_TYPE_MULTIPLE) {
					$itemArr = explode("|",$row['content'.$metadata['metadata_id']]);
					$multiple = array();
					foreach ($itemArr as $val) {
						$multiple[] = $val;
					}
					$items[$metadata['metadata_id']] = $multiple;
				}else {
					$items[$metadata['metadata_id']] = $row['content'.$metadata['metadata_id']];
				}
				unset($row['content'.$metadata['metadata_id']]);
			}

			$voted = false;
			$comment_count = 0;
			$comments = "";
			foreach($row as $key => $val) {
				$items[$key] = $val;
			}
			$items['has_edit_auth'] = $this->_hasEditAuthority($items['insert_user_id']);

			// 削除権限を別扱い by nagahara@opensource-workshop.jp
			$items['has_delete_auth'] = $this->_hasDeleteAuthority($items['insert_user_id']);

			$items['has_confirm_auth'] = $this->_hasConfirmAuthority();

			$comment_count = $this->_db->countExecute("testee_comment", array("content_id" => $items['content_id']));
			$order_params = array(
				"insert_time" => "ASC"
			);
			$comments = $this->_db->selectExecute("testee_comment",array("content_id" => $items['content_id']), $order_params);
			if($comments === false) {
	    		return 'error';
	    	}
	    	foreach($comments as $key => $val) {
	    		$edit_auth = $this->_hasEditCommentAuthority($val['insert_user_id']);
	    		$comments[$key]['edit_auth'] = $edit_auth;
	    	}
	    	$items['comment_count'] = $comment_count;
	    	$items['comments'] = $comments;

	    	if($items['vote'] != "") {
				$who_voted = explode(",", $items['vote']);
				$user_id = $session->getParameter("_user_id");
				if(empty($user_id)) {
					$votes = $session->getParameter("testee_votes");
					if(!empty($votes)) {
						if(in_array($items['content_id'], $votes)) {
							$voted = true;
						}
					}
				}else if (in_array($user_id, $who_voted)) {
					$voted = true;
				}
			}
			$items['voted'] = $voted;

			$data['metadata'] = $layout;
			$data['value'] = $items;
		}
		return $data;
	}

	/**
	 * コンテンツの編集データ取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMdbEditData($content_id, $metadatas) {
		$sql = "";
		if(!empty($metadatas)){
			$sql .= $this->_getDataSQL($metadatas);
			$sql .= " WHERE {testee_content}.content_id=? ";
		}

		$params = array(
			"content_id" => $content_id
		);

		$result = $this->_db->execute($sql, $params ,null, null, true, array($this,"_getMdbEditDataFetchcallback"));
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getMdbEditDataFetchcallback($result) {
		$request =& $this->_container->getComponent("Request");
		$metadatas = $request->getParameter("metadatas");

		$data = array();
		while ($row = $result->fetchRow()) {
			$layout = array();
			$items = array();
			foreach ($metadatas as $metadata) {
				$layout[$metadata['display_pos']][$metadata['metadata_id']] = $metadata;
				if($metadata['type'] == TESTEE_META_TYPE_IMAGE || $metadata['type'] == TESTEE_META_TYPE_FILE) {
					if($metadata['type'] == TESTEE_META_TYPE_FILE && !empty($row['file_password'.$metadata['metadata_id']])) {
						$items[$metadata['metadata_id']."_file_password"] = $row['file_password'.$metadata['metadata_id']];
					}
					$items[$metadata['metadata_id']] = $this->getFileLink($row['content'.$metadata['metadata_id']],
																			$row['file_name'.$metadata['metadata_id']],
																			$row['physical_file_name'.$metadata['metadata_id']],
																			$metadata, null, $row['insert_user_id'], $row['file_password'.$metadata['metadata_id']]);
				} elseif($metadata['type'] == TESTEE_META_TYPE_INSERT_TIME) {
					$items[$metadata['metadata_id']] = $row['insert_time'];
				} elseif($metadata['type'] == TESTEE_META_TYPE_UPDATE_TIME) {
					$items[$metadata['metadata_id']] = $row['update_time'];
				} elseif($metadata['type'] == TESTEE_META_TYPE_MULTIPLE) {
					$itemArr = explode("|",$row['content'.$metadata['metadata_id']]);
					$multiple = array();
					foreach ($itemArr as $val) {
						$multiple[$val] = $val;
					}
					$items[$metadata['metadata_id']] = $multiple;
				} else {
					$items[$metadata['metadata_id']] = $row['content'.$metadata['metadata_id']];
				}
			}

			$data['metadata'] = $layout;
			$data['value'] = $items;
		}

		return $data;
	}

	/**
	 * 一覧取得
	 * @param string $testee_id 汎用データベースID
	 * @param array $metadatas メタデータ項目配列
	 * @param array $where_params 絞込み文字列配列
	 * @param array $order_params ソートデータ配列
	 * @param string $search_sql キーワード検索条件WHERE句文字列
	 * @param string $disp_cnt 表示件数
	 * @param string $begin 対象データ開始行番号
	 * @param array $keywordBindValues 検索キーワードデータ配列
	 * @return コンテンツデータ配列
	 */
	function &getSearchResult($testee_id,
								$metadatas,
								$where_params,
								$order_params,
								$search_sql,
								$disp_cnt = 0,
								$begin = 0,
								$keywordBindValues = array())
	{
		list($sql_where, $params) = $this->_getSqlContentWhereStatement($testee_id, $where_params, $keywordBindValues);

		$sql = $this->_getDataSQL($metadatas, ',URL.short_url');
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= $this->_getAbbreviateUrlJoinStatement();;
		$sql .= $sql_where;
		$sql .= $search_sql;
		$sql .= $this->_getAuthorityWhereSQL($params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result = $this->_db->execute($sql, $params ,$disp_cnt, $begin, true, array($this,"_getSearchResultFetchcallback"));
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getSearchResultFetchcallback($result) {
		$ret = array();

		while ($row = $result->fetchRow()) {
			foreach($row as $key => $val) {
				$metadata_id = substr($key, 7, strlen($key));
				if (is_numeric($metadata_id)) {
					$content[$metadata_id] = $val;
				} else {
					$content[$key] = $val;
				}
			}
			$ret[] = $content;
		}
		return $ret;
	}

	/**
	 * 編集権限を取得する
	 *
	 * @param	array	$insetUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditAuthority($inset_user_id)
	{

		// testee では、以下の編集権限を仕様とする。
		// 変更：config で設定

		$session =& $this->_container->getComponent("Session");

		$user_id = $session->getParameter("_user_id");
		$auth_id = $session->getParameter("_auth_id");

		$role_auth_id = $session->getParameter("_role_auth_id");
		$user_auth_id = $session->getParameter("_user_auth_id");

		// config でシステム管理者権限のみに指定されている場合
		if ( TESTEE_EDIT_SYSTEM_ROLE_AUTH ) {

			// システム管理者チェック
			if ( $role_auth_id == _SYSTEM_ROLE_AUTH_ID ) {
				return true;
			}
			else {
				return false;
			}
		}

		// config で設定されているベース権限でチェック
		if ( $user_auth_id >= TESTEE_EDIT_USER_AUTH_ID ) {
			return true;
		}
		else {
			return false;
		}

		// 以下は元のロジック

		if ($inset_user_id == $user_id || $auth_id >= _AUTH_CHIEF) {
			return true;
		}

		$request =& $this->_container->getComponent("Request");
		$room_id = $request->getParameter("room_id");
		$hierarchy = $session->getParameter("_hierarchy");
		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($inset_user_id, $room_id);
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * 削除権限を取得する
	 *
	 * @param	array	$insetUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasDeleteAuthority($inset_user_id)
	{

		// testee では、以下の編集権限を仕様とする。
		// 削除：config で設定

		$session =& $this->_container->getComponent("Session");

		$user_id = $session->getParameter("_user_id");
		$auth_id = $session->getParameter("_auth_id");

		$role_auth_id = $session->getParameter("_role_auth_id");
		$user_auth_id = $session->getParameter("_user_auth_id");

		// config でシステム管理者権限のみに指定されている場合
		if ( TESTEE_DELETE_SYSTEM_ROLE_AUTH ) {

			// システム管理者チェック
			if ( $role_auth_id == _SYSTEM_ROLE_AUTH_ID ) {
				return true;
			}
			else {
				return false;
			}
		}

		// config で設定されているベース権限でチェック
		if ( $user_auth_id >= TESTEE_DELETE_USER_AUTH_ID ) {
			return true;
		}
		else {
			return false;
		}

		// 以下は元のロジック

		if ($inset_user_id == $user_id || $auth_id >= _AUTH_CHIEF) {
			return true;
		}

		$request =& $this->_container->getComponent("Request");
		$room_id = $request->getParameter("room_id");
		$hierarchy = $session->getParameter("_hierarchy");
		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($inset_user_id, $room_id);
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * コメントの編集権限を取得する
	 *
	 * @param	array	$insetUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditCommentAuthority($inset_user_id)
	{
		$session =& $this->_container->getComponent("Session");

		$user_id = $session->getParameter("_user_id");
		$auth_id = $session->getParameter("_auth_id");
		if ($inset_user_id == $user_id || $auth_id >= _AUTH_CHIEF) {
			return true;
		}

		$request =& $this->_container->getComponent("Request");
		$room_id = $request->getParameter("room_id");
		$hierarchy = $session->getParameter("_hierarchy");
		$authCheck =& $this->_container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($inset_user_id, $room_id);
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}

	/**
	 * 承認権限を取得する
	 *
	 * @param	array	$insetUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasConfirmAuthority()
	{
		$session =& $this->_container->getComponent("Session");
		$_auth_id = $session->getParameter("_auth_id");

		if ($_auth_id >= _AUTH_CHIEF) {
			return true;
		}

	    return false;
	}

	/**
	 * メール送信データを取得する
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMail($content_id, $metadatas) {
		$sql = "";
		if(!empty($metadatas)){
			$sql .= $this->_getDataSQL($metadatas);
			$sql .= " WHERE {testee_content}.content_id=? ";
		}

		$params = array(
			"content_id" => $content_id
		);

		$result = $this->_db->execute($sql, $params);
		if ( $result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result[0];
	}

	/**
	 * タイトル一覧取得
	 * @param array items
	 * @param array order_params
	 * @return array
	 */
	function &getMDBTitleList($testee_id, $metadata_title_id, $order_params) {
		$sql = "";
		$params = array(
			"testee_id" => $testee_id
		);

		$sql = "SELECT {testee_content}.content_id, {testee_content}.vote_count, " .
		$sql .=	" {testee_content}.insert_time, {testee_content}.update_time, ";
		$sql .=	" m_content.content AS title, m_file.file_name ";
		$sql .= " FROM {testee_content} ";
		$sql .= " LEFT JOIN {testee_metadata_content} m_content ";
		$sql .= " ON ({testee_content}.content_id = m_content.content_id";
		$sql .= " AND m_content.metadata_id = ". $metadata_title_id .")";
		$sql .= " LEFT JOIN {testee_file} m_file ";
		$sql .= " ON (m_content.metadata_content_id = m_file.metadata_content_id) ";
		$sql .= $this->_getAuthorityFromSQL();
		$sql .= " WHERE {testee_content}.testee_id=? ";
		$sql .= $this->_getAuthorityWhereSQL($params);
		$sql .= $this->_db->getOrderSQL($order_params);

		$result = $this->_db->execute($sql, $params);
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * コンテンツ番号データを取得する
	 *
     * @return array	コンテンツ番号データ配列
	 * @access	public
	 */
	function &getContentSequence() {
		$request =& $this->_container->getComponent("Request");

		$params = array(
			$request->getParameter("drag_content_id"),
			$request->getParameter("drop_content_id"),
			$request->getParameter("testee_id")
		);

		$sql = "SELECT content_id, display_sequence ".
				"FROM {testee_content} ".
				"WHERE (content_id = ? ".
				"OR content_id = ?) ".
				"AND testee_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false ||
			count($result) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$result[0]["content_id"]] = $result[0]["display_sequence"];
		$sequences[$result[1]["content_id"]] = $result[1]["display_sequence"];

		return $sequences;
	}

	/**
	 * 携帯用ブロックデータを取得 Add by AllCreator
 	 *
 	 * @access  public
 	 */
	function getBlocksForMobile($block_id_arr)
	{
		$sql = "SELECT testee.*, block.block_id, block.visible_item" .
				" FROM {testee} testee" .
				" INNER JOIN {testee_block} block ON (testee.testee_id=block.testee_id)" .
				" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
				" ORDER BY block.insert_time DESC, block.testee_id DESC";

		return $this->_db->execute($sql, null, null, null, true);
	}

	/**
	 * 日付チェック
 	 *
 	 * @access  public
 	 */
	function checkDate($attributes)
	{
		$session =& $this->_container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");

		if ($mobile_flag == _ON) {
			$date = $attributes;
			$replace_of = array('Y', 'm', 'd');
			$replace_by = array($date["year"], $date["month"], $date["day"]);

			$attributes = str_replace($replace_of, $replace_by, _INPUT_DATE_FORMAT);
			if ($attributes == "//") {
				$attributes = "";
			}
		}
		if (empty($attributes)) {
			return "";
		}
		switch (_INPUT_DATE_FORMAT) {
    		case "Y/m/d":
		    	$pattern = "/^([0-9]{4})\/([0-1]?[0-9])\/([0-3]?[0-9])$/";
    			break;
    		case "m/d/Y":
    		case "d/m/Y":
    			$pattern = "/^([0-3]?[0-9])\/([0-1]?[0-9])\/([0-9]{4})$/";
    			break;
    		default:
    			return "";
    	}

		if (!preg_match($pattern, $attributes, $matches)) {
			return "";
		}

		switch (_INPUT_DATE_FORMAT) {
    		case "Y/m/d":
		    	$check = checkdate($matches[2], $matches[3], $matches[1]);
		    	$dateString = $matches[1]. sprintf("%02d", intval($matches[2])). sprintf("%02d", intval($matches[3]));
    			break;
    		case "m/d/Y":
		    	$check = checkdate($matches[1], $matches[2], $matches[3]);
		    	$dateString = $matches[3]. sprintf("%02d", intval($matches[1])). sprintf("%02d", intval($matches[2]));
    			break;
    		case "d/m/Y":
    			$check = checkdate($matches[2], $matches[1], $matches[3]);
    			$dateString = $matches[3]. sprintf("%02d", intval($matches[2])). sprintf("%02d", intval($matches[1]));
    			break;
    	}
    	if (!$check) {
			return "";
		}

		return $dateString;
	}

	/**
	 * 自動番号の取得
 	 *
 	 * @access  public
 	 */
	function getAutoNumber($metadata_id) {
		$params = array(
			"metadata_id" => $metadata_id,
		);
		$number = $this->_db->maxExecute("testee_metadata_content", "content", $params);
		if ($number === false) {
			return 0;
		}
		return sprintf(TESTEE_META_AUTONUM_FORMAT, intval($number)+1);
	}

	/**
	 * コンテンツデータ用WHERE句取得処理
	 *
	 * @param string $testeeId 対象の汎用データベースID
	 * @param array $whereValues 検索条件配列
	 * @param array $keywordBindValues 検索キーワードデータ
	 * @return array 0:コンテンツデータ用WHERE句文字列
	 *               1:バインドパラメータ値配列
	 * @access  public
	 */
	function &_getSqlContentWhereStatement($testeeId, $whereValues, $keywordBindValues = array()) {
		$whereStatement = ' WHERE {testee_content}.testee_id=? ';
		$bindValues = array(
			'testee_id' => $testeeId
		);
		if (empty($whereValues)) {
			$bindValues += $keywordBindValues;

			$returnValue = array(
				$whereStatement,
				$bindValues
			);
			return $returnValue;
		}

		foreach($whereValues as $columnName => $value) {
			if (!strlen($value)) {
				continue;
			}

			$key = 'm_content' . $columnName . '.content';
			if (mb_strlen($value, INTERNAL_CODE) < _MYSQL_FT_MIN_WORD_LEN) {
				$whereStatement .= ' AND ' . $columnName . ' LIKE ? ';
				$bindValues[$key] = '%' . $value . '%';
			} else {
				$whereStatement .= ' AND MATCH(' . $columnName . ') '
									. 'AGAINST (? IN BOOLEAN MODE)';
				$bindValues[$key] = '"' . $value . '"';
			}
		}

		$bindValues += $keywordBindValues;

		$returnValue = array(
			$whereStatement,
			$bindValues
		);
		return $returnValue;
	}

	/**
	 * 短縮URLデータ用JOIN句取得処理
	 *
	 * @return string 短縮URLデータ用JOIN句文字列
	 * @access  public
	 */
	function &_getAbbreviateUrlJoinStatement() {
		$request =& $this->_container->getComponent('Request');
		$moduleId = $request->getParameter('module_id');

		$joinStatement = 'LEFT JOIN {abbreviate_url} URL '
							. "ON URL.module_id = '" . $moduleId . "' "
							. 'AND URL.contents_id = {testee_content}.testee_id '
							. 'AND URL.unique_id = {testee_content}.content_id ';
		return $joinStatement;
	}

/* 追加独自機能での追加fanction　EddyK */
	/**
	 * コンテンツ・レコードの取得
	 */
	function getMDBContent($content_id) {

		$params = array($content_id);

		$sql = "SELECT * ".
				"FROM {testee_content} ".
				"WHERE content_id = ? ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		return $result;
	}
	/**
	 * コンテンツ・レコードの取得
	 */
	function getNID($testee_id, $order_params = null) {

		$params = array($testee_id);

		$sql = "SELECT TC.content_no, TC.insert_time, allocation_group.group_name, allocation_group.intervention ".
				"FROM {testee_content} TC ";

		$sql .= " LEFT JOIN {testee_content_group} content_group ";
		$sql .= " ON (content_group.content_id = TC.content_id) ";
		$sql .= " LEFT JOIN {testee_allocation_group} allocation_group ";
		$sql .= " ON (allocation_group.allocation_group_id = content_group.allocation_group_id) ";

		$sql .= "WHERE TC.testee_id = ? ";
		if($order_params){
			$sql .= $this->_db->getOrderSQL($order_params);
		}
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			print_r($this->_db->ErrorMsg());
			$this->_db->addError();
			return $result;
		}

		return $result;
	}

	/**
	 * 登録番号の取得
	 */
	function getContentNo( $testee_id ) {
		$params = array( $testee_id );

		$sql = "SELECT m.content_no ".
				"FROM {testee} m ".
				"WHERE m.testee_id = ?";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$content_no = $result[0]['content_no'];
		return $content_no;
	}

	/**
	 * メタタイプを指定し、metadata_id、nameを取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getMetadataIDNAME($testee_id, $type)
	{
		$params = array(
			"testee_id" => $testee_id,
			"type" => $type
		);
		$sql = "SELECT TM.metadata_id,  TM.name " .
				"FROM {testee_metadata} TM " .
				"WHERE TM.testee_id = ? AND TM.type = ? " .
				"ORDER BY TM.display_sequence ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * metadata_idより条件式を取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getCondition($metadata_id)
	{
		$params = array(
			"metadata_id" => $metadata_id
		);
		$sql = "SELECT TMC.* " .
				"FROM {testee_metadata_condition} TMC " .
				"WHERE TMC.metadata_id = ? ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}
	/**
	 * metadata_idよりコメントを取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getComment($metadata_id)
	{
		$params = array(
			"metadata_id" => $metadata_id
		);
		$sql = "SELECT TMC.* " .
				"FROM {testee_metadata_comment} TMC " .
				"WHERE TMC.metadata_id = ? ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}
	/**
	 * testee_idよりコメントを取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getCommentList($testee_id)
	{
		$params = array(
			"testee_id" => $testee_id
		);
		$sql = "SELECT TMC.* " .
				"FROM {testee_metadata} TM " .
				"INNER JOIN {testee_metadata_comment} TMC ON TM.metadata_id = TMC.metadata_id " .
				"WHERE TM.testee_id = ? AND TM.type = " . TESTEE_META_TYPE_N_COMMENT;

		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"editCommentList"));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	function editCommentList($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row["metadata_id"]] = $row["comment"];
		}
		return $ret;
	}
	/**
	 * testee_idよりグループメタを取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getGroup($testee_id)
	{
		$params = array(
			"testee_id" => $testee_id
		);
		$sql = "SELECT TM.* " .
				"FROM {testee_metadata} TM " .
				"WHERE TM.testee_id = ? AND TM.type = " . TESTEE_META_TYPE_N_GROUP;
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}
	/**
	 * testee_idより件数設定を取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getCounts($testee_id)
	{
		$params = array(
			"testee_id" => $testee_id
		);
		$sql = "SELECT TC.* " .
				"FROM {testee_counts} TC " .
				"WHERE TC.testee_id = ? ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if (count($result) > 0) {
			return $result[0];
		}
		return null;
	}

	/**
	 * 施設情報の取得	EddyK
	 */
	function getHospital($user_id) {
// 施設情報のitemu_idを取得する
		$where_item = PROFILE;
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$select_item_id = null;
		if(count($result) > 0){
			$select_item_id = $result[0]["item_id"];
		}

// 施設情報を取得する
		$content = null;
		if($select_item_id){
			$params = array($user_id, $select_item_id);
			$sql = "SELECT {users_items_link}.content FROM {users_items_link} ".
					"WHERE user_id = ? AND item_id = ? ";
			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
			       	return 'error';
			}
			if(count($result) > 0) $content = $result[0]["content"];
		}
		return $content;
	}

	/**
	 * TEDC権限の取得	EddyK
	 */
	function getUserTEDC($user_id, $testee_id) {
	// 試験毎権限を取得
		$params = array(
			"testee_id" => intval($testee_id),
			"user_id" => $user_id
		);
		$sql  = "SELECT TEDC.testee_id, TEDC.user_id, TEDC.tedc_authority ";
		$sql .= "FROM {testee_tedcauthority} TEDC ";
		$sql .= "WHERE TEDC.testee_id = ? AND TEDC.user_id = ? ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if(count($result) > 0){
			return $result[0]["tedc_authority"];
		}
	// 試験毎権限がない場合、ベース権限の設定を取得
		// USER_ITEM_TEDCのitemu_idを取得する
		$where_item = "USER_ITEM_TEDC";
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$tedc_item_id = null;
		if(count($result) > 0){
			$tedc_item_id = $result[0]["item_id"];
		} else {
			return null;	// USER_ITEM_TEDC（ベース権限）の設定がない
		}

		$params = array();
		$params["tedc_item_id"] = $tedc_item_id;
		$params["user_id"] = $user_id;
		$sql  = "SELECT U.user_id, U.handle, UIL.content AS item_tedc_authority_id FROM {users} U ";
		$sql .= "LEFT JOIN {users_items_link} UIL ON U.user_id = UIL.user_id AND UIL.item_id = ? ";
		$sql .= "WHERE U.user_id = ? ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if(empty($result[0]["item_tedc_authority_id"])){
			return null;	// USER_ITEM_TEDC（ベース権限）の設定はあるがユーザーへの設定がされていない
		}
		$tedc_code = trim($result[0]["item_tedc_authority_id"], '|');
		$tedc_code = trim($tedc_code, 'USER_ITEM_TEDC_' );
		return $tedc_code;

	}

	/**
	 * testee_idより日付関連チェックの条件を取得する　EddyK
 	 *
 	 * @access  public
 	 */
	function getDateCheck($testee_id, $condition_id = null)
	{
		$params = array(
			"testee_id" => $testee_id
		);
		$sql = "SELECT TDC.*, TM1.name AS date1, TM2.name AS date2 " .
				"FROM {testee_date_condition} TDC " .
				"INNER JOIN {testee_metadata} TM1 ON TDC.date1_id = TM1.metadata_id " .
				"INNER JOIN {testee_metadata} TM2 ON TDC.date2_id = TM2.metadata_id " .
				"WHERE TDC.testee_id = ? ";
		if(!is_null($condition_id)){
			$params["condition_id"] = $condition_id;
			$sql .= " AND TDC.condition_id = ? ";
		}
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 件数設定による全件件数、選択値別件数、及び登録可否を取得
	 *
	 * @param int testee_id
	 * @return array 登録判定用配列
	 * @access public
	 */
	function getOptionCounts($testee_id) {

		// 以下の配列を編集
		$ret_array = array();

		/*
			--- 書式
			[選択肢] => Array ( [limit] => 設定件数, [current] => 実件数, [add_ok] => 追加可否 )

			--- サンプル
			[ALL]      => Array ( [limit] => 20, [current] => 13, [add_ok] => true )
			[食道がん] => Array ( [limit] => 10, [current] => 10, [add_ok] => false )
			[胃がん]   => Array ( [limit] => 10, [current] => 3,  [add_ok] => true )
		*/

	// 対象のDB から、件数設定情報及び選択値、設定件数を取得する
		// 件数設定の既設定の取得
		$counts_data = $this->getCounts($testee_id);
    	if ($counts_data === false) {
    		return 'error';
    	}

		if($counts_data){
			// 全体制限件数
			$setting_all_count = $counts_data["all_counts"];
			// 項目の選択値を取得
			if($counts_data["counts_id"]){
				$result = $this->getMetadataById($counts_data["counts_id"]);
				if ($result === false) {
					return 'error';
				}
				$options = explode("|", $result["select_content"]);
			}
			// 選択値別制限件数
			if($counts_data["option_counts"]){
				$json_option_counts = json_decode($counts_data["option_counts"],true);
				foreach($json_option_counts as $option_count){
					$option_counts[] = $option_count;
				}
			}
		} else {
		// 件数設定情報が無い場合、nullを返す
			return null;
		}

		// 選択肢  例：食道がん|胃がん
		// $select_content = $result[0]['select_content'];

		// -------------------------------------------------
		// - 全件データの件数取得
		// -------------------------------------------------

		$params = array( $testee_id );

		$sql = "SELECT count(*) AS count ".
				"FROM {testee_content} ".
				"WHERE testee_id = ? ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 実データ件数、追加の可否をセット
		$data_all_count = intval( $result[0]['count'] );
		$regist_all_flag = false;
		if ( $setting_all_count > $data_all_count ) {
			$regist_all_flag = true;
		}

		$ret_array["ALL"] = array("limit" => $setting_all_count, "current" => $data_all_count, "add_ok" => $regist_all_flag);

		// -------------------------------------------------
		// - 選択値別件数の取得
		// -------------------------------------------------

		if($counts_data["counts_id"]){
			$loop_count = count($options);
			for ( $i = 0; $i < $loop_count; $i++ ) {

				$limit_check = false;
				// 各割付と設定値
				$option_name          = $options[$i];
				if($option_counts[$i] || !empty($option_counts[$i])){
					$option_setting_count = intval($option_counts[$i]);
					$limit_check = true;		// 選択値別件数のチェックを実施
				} else {
					$option_setting_count = null;
				}

				// 各グループごとの実件数の取得
				$params = array( $counts_data["counts_id"] );
				$sql = "SELECT count(*) AS count ".
						"FROM {testee_metadata_content} ".
						"WHERE metadata_id = ? ".
						  "AND content LIKE '" . $option_name . "%'";

				$result = $this->_db->execute($sql, $params);
				if ($result === false) {
					$this->_db->addError();
					return $result;
				}

				$data_count = intval( $result[0]['count'] );

				$regist_data_flag = false;
				if ($limit_check) {
					if ( $option_setting_count > $data_count ) {
						$regist_data_flag = true;
					}
				} else {
					$regist_data_flag = true;
				}

				$ret_array[$option_name] = array("limit" => $option_setting_count, "current" => $data_count, "add_ok" => $regist_data_flag );
			}
		}

		return $ret_array;
	}

	/**
	 * メタidで指定した選択値別登録件数を取得
	 *
	 * @param int testee_id
	 * @return array 登録判定用配列
	 * @access public
	 */
	function getOptionCounts2($metadata_id) {
		// -------------------------------------------------
		// - 選択値別件数の取得
		// -------------------------------------------------
		$ret_array = array();
		$result = $this->getMetadataById($metadata_id);
		if ($result === false) {
			return 'error';
		}
		$options = explode("|", $result["select_content"]);
		$options_code = explode("|", $result["select_content_code"]);

		for ( $i = 0; $i < count($options); $i++ ) {
			// 各選択肢の実件数の取得
			$params = array($metadata_id);
			$sql = "SELECT count(*) AS count ".
					"FROM {testee_metadata_content} ".
					"WHERE metadata_id = ? ".
					"AND content LIKE '" . $options[$i] . "%'";
			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
				return $result;
			}
			$ret_array[$options[$i]]["code"] = $options_code[$i];
			$ret_array[$options[$i]]["count"] = intval( $result[0]['count'] );
		}

		return $ret_array;
	}

	/**
	 * 年齢計算
	 */
	function calc_age( $birthday, $insertday ) {
		$age = null;
		if($birthday){
			list( $iy, $im, $id ) = explode( '/', $insertday );
			list( $by, $bm, $bd ) = explode( '/', $birthday );
			$age = intval($iy) - intval($by);
			if ( intval($im) * 100 + intval($id) < intval($bm) * 100 + intval($bd) ) $age--;
		}
		return $age;
	}

// TEDC権限設定用Function
	/**
	 * ルームIDより参加者リストを取得
	 *
     * @return array	ルーム参加者リスト
	 * @access	public
	 */
	function getRoomUsers($room_id, $testee_id) {
		// 施設情報のitemu_idを取得する
		$where_item = PROFILE;
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$hospiotal_item_id = null;
		if(count($result) > 0){
			$hospiotal_item_id = $result[0]["item_id"];
		}

		// USER_ITEM_TEDCのitemu_idを取得する
		$where_item = "USER_ITEM_TEDC";
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$tedc_item_id = null;
		if(count($result) > 0){
			$tedc_item_id = $result[0]["item_id"];
		}

		// 対象のルームがデフォルトで全員参加か確認するためページ情報を取得
		$params["page_id"] = intval($room_id);
		$sql  = "SELECT P.room_id, P.default_entry_flag ";
		$sql .= "FROM {pages} P ";
		$sql .= "WHERE P.page_id = ? ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return  'error';
		}

		$params = array();

		if($result[0]["default_entry_flag"] != 1){		// 対象のルームがデフォルトで全員参加か
			$sql  = "SELECT PUL.room_id, PUL.user_id, PUL.role_authority_id , U.handle, TEDC.tedc_authority ";
		} else {
			$sql  = "SELECT ".$room_id." AS room_id, U.user_id, PUL.role_authority_id, U.handle, TEDC.tedc_authority ";
		}

		if($hospiotal_item_id){		// 会員情報に施設情報の項目がある場合
			$sql .= ", UIL.content AS hospital ";
		}
		if($tedc_item_id){			// USER_ITEM_TEDCに施設情報の項目がある場合
			$sql .= ", UIL2.content AS item_tedc_authority_id ";
		}

		if($result[0]["default_entry_flag"] != 1){
			$sql .= "FROM {pages_users_link} PUL ".
					"INNER JOIN {users} U ON PUL.user_id = U.user_id ";
		} else {
			$sql .= "FROM {users} U ".
					"LEFT JOIN {pages_users_link} PUL ON U.user_id = PUL.user_id AND PUL.room_id = ? ";
			$params["room_id1"] = intval($room_id);
		}
		$sql .= "LEFT JOIN {testee_tedcauthority} TEDC ON U.user_id = TEDC.user_id AND TEDC.testee_id = ? ";
		$params["testee_id"] = intval($testee_id);

		if($hospiotal_item_id){		// 会員情報に施設情報の項目がある場合
			$sql .= "LEFT JOIN {users_items_link} UIL ON U.user_id = UIL.user_id AND UIL.item_id = ? ";
			$params["hospiotal_item_id"] = intval($hospiotal_item_id);
		}
		if($tedc_item_id){			// USER_ITEM_TEDCに施設情報の項目がある場合
			$sql .= "LEFT JOIN {users_items_link} UIL2 ON U.user_id = UIL2.user_id AND UIL2.item_id = ? ";
			$params["tedc_item_id"] = intval($tedc_item_id);
		}
		if($result[0]["default_entry_flag"] != 1){
			$sql .= "WHERE PUL.room_id = ? AND PUL.role_authority_id <> 0 ";
			$params["room_id2"] = intval($room_id);
		} else {
			 $sql .= "WHERE PUL.role_authority_id is NULL OR PUL.role_authority_id <> 0 ";
		}
		if($hospiotal_item_id){		// 会員情報に施設情報の項目がある場合
			$sql .= "ORDER BY hospital, role_authority_id";
		} else {
			$sql .= "ORDER BY role_authority_id";
		}

		$result = $this->_db->execute($sql, $params, 0, 0, true, array($this,"_editAuthority"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	function _editAuthority($result) {

		$ret = array();
		$i = 0;
		while ($row = $result->fetchRow()) {
			$ret[$i] = $row;
			if (array_key_exists('hospital', $row)) {
				$ret[$i]["hospital"] = trim( $row["hospital"], '|' );
			} else {
				$ret[$i]["hospital"] = null;
			}
			switch($row["role_authority_id"]){
				case _ROLE_AUTH_ADMIN:
					$ret[$i]["nc_authority_name"] = "管理者";
					break;
				case _ROLE_AUTH_CHIEF:
					$ret[$i]["nc_authority_name"] = "主担";
					break;
				case _ROLE_AUTH_MODERATE:
					$ret[$i]["nc_authority_name"] = "モデレータ";
					break;
				case _ROLE_AUTH_GENERAL:
					$ret[$i]["nc_authority_name"] = "一般";
					break;
				case _ROLE_AUTH_GUEST:
					$ret[$i]["nc_authority_name"] = "ゲスト";
					break;
				case _ROLE_AUTH_CLERK:
					$ret[$i]["nc_authority_name"] = "事務局";
					break;
				case 7:				// NC権限で「7」管理者？
					$ret[$i]["nc_authority_name"] = "管理者";
					break;
				default :			// 会員全員参加ルームでPULが無い場合、一般
					$ret[$i]["nc_authority_name"] = "一般";
					$ret[$i]["role_authority_id"] = _ROLE_AUTH_GENERAL;
					break;
			}
			switch($row["tedc_authority"]){
				case TEDC_AUTHORITY_ADMIN:
					$ret[$i]["tedc_authority_name"] = "管理者";
					break;
				case TEDC_AUTHORITY_DM:
					$ret[$i]["tedc_authority_name"] = "ＤＭ";
					break;
				case TEDC_AUTHORITY_MONITOR:
					$ret[$i]["tedc_authority_name"] = "モニター";
					break;
				case TEDC_AUTHORITY_SECRETARY:
					$ret[$i]["tedc_authority_name"] = "研究事務局";
					break;
				case TEDC_AUTHORITY_READER:
					$ret[$i]["tedc_authority_name"] = "施設研究責任者";
					break;
				case TEDC_AUTHORITY_SCHOLAR:
					$ret[$i]["tedc_authority_name"] = "研究者";
					break;
				case TEDC_AUTHORITY_CRC:
					$ret[$i]["tedc_authority_name"] = "ＣＲＣ";
					break;
				case TEDC_AUTHORITY_ASSISTANT:
					$ret[$i]["tedc_authority_name"] = "作業補助者";
					break;
				case TEDC_AUTHORITY_NOTWORK:
					$ret[$i]["tedc_authority_name"] = "利用不可";
					break;
			}
			if (array_key_exists('item_tedc_authority_id', $row)) {
				$tedc_code = trim( $row["item_tedc_authority_id"], '|' );
				$tedc_code = trim( $tedc_code, 'USER_ITEM_TEDC_' );
				switch($tedc_code){
					case TEDC_AUTHORITY_ADMIN:
						$ret[$i]["item_tedc_authority_name"] = "管理者";
						break;
					case TEDC_AUTHORITY_DM:
						$ret[$i]["item_tedc_authority_name"] = "ＤＭ";
						break;
					case TEDC_AUTHORITY_MONITOR:
						$ret[$i]["item_tedc_authority_name"] = "モニター";
						break;
					case TEDC_AUTHORITY_SECRETARY:
						$ret[$i]["item_tedc_authority_name"] = "研究事務局";
						break;
					case TEDC_AUTHORITY_READER:
						$ret[$i]["item_tedc_authority_name"] = "施設研究責任者";
						break;
					case TEDC_AUTHORITY_SCHOLAR:
						$ret[$i]["item_tedc_authority_name"] = "研究者";
						break;
					case TEDC_AUTHORITY_CRC:
						$ret[$i]["item_tedc_authority_name"] = "ＣＲＣ";
						break;
					case TEDC_AUTHORITY_ASSISTANT:
						$ret[$i]["item_tedc_authority_name"] = "作業補助者";
						break;
					case TEDC_AUTHORITY_NOTWORK:
						$ret[$i]["item_tedc_authority_name"] = "利用不可";
						break;
				}
			} else {
				$ret[$i]["item_tedc_authority_name"] = null;
			}
			$i++;
		}
		return $ret;
	}

	/**
	 * TEDC権限名からコードを取得
	 */
	function getTedcAuthorityCode($tedc_authority_name) {

		if ( $tedc_authority_name == "管理者" ) {
			return TEDC_AUTHORITY_ADMIN;
		}
		if ( $tedc_authority_name == "ＤＭ" || $tedc_authority_name == "DM" ) {
			return TEDC_AUTHORITY_DM;
		}
		if ( $tedc_authority_name == "モニター" ) {
			return TEDC_AUTHORITY_MONITOR;
		}
		if ( $tedc_authority_name == "研究事務局" ) {
			return TEDC_AUTHORITY_SECRETARY;
		}
		if ( $tedc_authority_name == "施設研究責任者" ) {
			return TEDC_AUTHORITY_READER;
		}
		if ( $tedc_authority_name == "研究者" ) {
			return TEDC_AUTHORITY_SCHOLAR;
		}
		if ( $tedc_authority_name == "ＣＲＣ" || $tedc_authority_name == "CRC" ) {
			return TEDC_AUTHORITY_CRC;
		}
		if ( $tedc_authority_name == "作業補助者" ) {
			return TEDC_AUTHORITY_ASSISTANT;
		}
		if ( $tedc_authority_name == "利用不可" ) {
			return TEDC_AUTHORITY_NOTWORK;
		}
		if ( $tedc_authority_name == "" ) {
			return "";
		}
		return false;
	}


	/**
	 * 試験ID、ユーザーIDよりユーザーのTEDC権限を取得
	 *
     * @return array	ユーザーの施設情報・ハンドル名・NC権限・TEDC権限の配列
	 * @access	public
	 */
	function getUserINFO($room_id, $testee_id, $user_id) {
		// 施設情報のitemu_idを取得する
		$where_item = PROFILE;
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$hospiotal_item_id = null;
		if(count($result) > 0){
			$hospiotal_item_id = $result[0]["item_id"];
		}

		// USER_ITEM_TEDCのitemu_idを取得する
		$where_item = "USER_ITEM_TEDC";
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$tedc_item_id = null;
		if(count($result) > 0){
			$tedc_item_id = $result[0]["item_id"];
		}

		$params = array();
		$sql  = "SELECT ".$room_id." AS room_id, U.user_id, PUL.role_authority_id, U.handle, TEDC.tedc_authority ";

		if($hospiotal_item_id){			// 会員情報に施設情報の項目がある場合
			$sql .= ", UIL.content AS hospital ";
		}
		if($tedc_item_id){			// USER_ITEM_TEDCに施設情報の項目がある場合
			$sql .= ", UIL2.content AS item_tedc_authority_id ";
		}
		$sql .= "FROM {users} U ".
				"LEFT JOIN {pages_users_link} PUL ON U.user_id = PUL.user_id AND PUL.room_id = ? ";
		$params["room_id1"] = intval($room_id);
		
		$sql .= "LEFT JOIN {testee_tedcauthority} TEDC ON U.user_id = TEDC.user_id AND TEDC.testee_id = ? ";
		$params["testee_id"] = intval($testee_id);

		if($hospiotal_item_id){			// 会員情報に施設情報の項目がある場合
			$sql .= "LEFT JOIN {users_items_link} UIL ON U.user_id = UIL.user_id AND UIL.item_id = ? ";
			$params["hospiotal_item_id"] = intval($hospiotal_item_id);
		}
		if($tedc_item_id){			// USER_ITEM_TEDCに施設情報の項目がある場合
			$sql .= "LEFT JOIN {users_items_link} UIL2 ON U.user_id = UIL2.user_id AND UIL2.item_id = ? ";
			$params["tedc_item_id"] = intval($tedc_item_id);
		}
		$sql .= "WHERE U.user_id = ? ";
		$params["user_id"] = $user_id;
		$result = $this->_db->execute($sql, $params, 0, 0, true, array($this,"_editAuthority"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	function getTEDC($testee_id, $user_id) {
		$params = array(
			"testee_id" => intval($testee_id),
			"user_id" => $user_id
		);
		$sql  = "SELECT TEDC.testee_id, TEDC.user_id, TEDC.tedc_authority ";
		$sql .= "FROM {testee_tedcauthority} TEDC ";
		$sql .= "WHERE TEDC.testee_id = ? AND TEDC.user_id = ? ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if(count($result) > 0){
			return $result[0];
		} else {
			return null;
		}
	}
// ルーム参加者の施設情報取得
	/**
	 * ルームIDより参加者の施設情報を取得
	 *
     * @return array	ルーム参加者の施設情報リスト
	 * @access	public
	 */
	function getRoomHospital($room_id) {
		// 施設情報のitemu_idを取得する
		$where_item = PROFILE;
		$sql_params = array( $where_item );
		$sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
		$result = $this->_db->execute($sql, $sql_params);
		if ($result === false) {
		       	$this->_db->addError();
		       	return 'error';
		}
		$hospiotal_item_id = null;
		if(count($result) > 0){
			$hospiotal_item_id = $result[0]["item_id"];
		} else {
            return null;    // 施設情報が無い場合nullを返す
        }

		// 対象のルームがデフォルトで全員参加か確認するためページ情報を取得
		$params["page_id"] = intval($room_id);
		$sql  = "SELECT P.room_id, P.default_entry_flag ";
		$sql .= "FROM {pages} P ";
		$sql .= "WHERE P.page_id = ? ";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return  'error';
		}

		$params = array();
		$sql  = "SELECT UIL.content AS hospital, hospital.hospital_code ";

		if($result[0]["default_entry_flag"] != 1){
			$sql .= "FROM {pages_users_link} PUL ".
					"INNER JOIN {users} U ON PUL.user_id = U.user_id ";
		} else {
			$sql .= "FROM {users} U ".
					"LEFT JOIN {pages_users_link} PUL ON U.user_id = PUL.user_id AND PUL.room_id = ? ";
			$params["room_id1"] = intval($room_id);
		}
		$sql .= "LEFT JOIN {users_items_link} UIL ON U.user_id = UIL.user_id AND UIL.item_id = ? ";

		// 施設情報の独立管理対応
		$sql .= "LEFT JOIN {hospitalinfo} hospital ON CONCAT( hospital.hospital_name, '|' ) = UIL.content ";

		$params["hospiotal_item_id"] = intval($hospiotal_item_id);

		if($result[0]["default_entry_flag"] != 1){
			$sql .= "WHERE PUL.room_id = ? AND PUL.role_authority_id <> 0 ";
			$params["room_id2"] = intval($room_id);
		} else {
			 $sql .= "WHERE PUL.role_authority_id is NULL OR PUL.role_authority_id <> 0 ";
		}
		$sql .= "GROUP BY hospital ORDER BY hospital ";

		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	
	function getAllocationContent($testee_id)
	{
		$params = array($testee_id);

		$sql = "SELECT allocation_flag, equal_ratio_flag, force_allocation_flag, group_differences, allocation_probability, allocation_result_flag ".
				"FROM {testee_allocate} ".
				"WHERE testee_id=? AND allocation_flag=1";
		
		$allocations = $this->_db->execute($sql, $params );
		if ($allocations === false) {
			$this->_db->addError();
			
			return $allocations;
		}
		if (count($allocations) === 0){
			return $allocations;
		}
		return $allocations[0];
	}
	
	function getAllocationItem($testee_id)
	{
		$params = array($testee_id);

		//type=4:選択式(択一) , type=30:選択式(ラジオボタン) , type=20:施設名
		$sql = "SELECT tm.metadata_id, tm.name 
				FROM {testee_metadata} tm
				LEFT JOIN {testee_adjustment} ta 
				ON tm.metadata_id = ta.metadata_id 
				WHERE tm.testee_id=? 
				AND ta.metadata_id IS NULL 
				AND tm.type IN(4, 30, 20) 
				ORDER BY tm.display_sequence ";
		
		$metadatas = $this->_db->execute($sql, $params );
		if ($metadatas === false) {
			$this->_db->addError();
			
			return $metadatas;
		}
		
		//test_log($metadatas);
		return $metadatas;
	}
	
	function getSelectedAllocationItem($testee_id)
	{
		$params = array($testee_id);

		$sql = "SELECT ta.desplay_seq, ta.metadata_id, tm.name 
				FROM {testee_adjustment} ta 
				LEFT JOIN {testee_metadata} tm 
				ON ta.metadata_id = tm.metadata_id 
				WHERE tm.testee_id =? 
				ORDER BY ta.desplay_seq ";
				
		$adjustments = $this->_db->execute($sql, $params );
		if ($adjustments === false) {
			$this->_db->addError();
			
			return $adjustments;
		}
		
		//test_log($adjust_metadata_item);
		return $adjustments;
	}
	
	
	function getGroupContent($testee_id)
	{
		$params = array($testee_id);

		$sql = "SELECT tag.allocation_group_id, tag.group_name, tag.intervention ,tag.ratio 
				FROM {testee_allocation_group} tag 
				WHERE tag.testee_id =? 
				ORDER BY tag.allocation_group_id ";
				
		$group_content = $this->_db->execute($sql, $params );
		if ($group_content === false) {
			$this->_db->addError();
			
			return $group_content;
		}
		
		//test_log($adjust_metadata_item);
		return $group_content;
	}

	function getSettinghistory($testee_id)
	{
		$params = array($testee_id);

		$sql = "SELECT tsh.insert_time as set_time, tsh.allocation_result_flag 
				FROM {testee_setting_history} tsh 
				WHERE tsh.testee_id =? 
				ORDER BY tsh.insert_time DESC ";

		$final_history = $this->_db->execute($sql, $params );
		if ($final_history === false) {
			$this->_db->addError();
			
			return $final_history;
		}

		if ( empty( $final_history ) ) {
			return $final_history[0]['set_time'] = "";
		}
		else {
			$final_history[0]['set_time'] = substr($final_history[0]['set_time'], 0, 4).'/'
													.substr($final_history[0]['set_time'], 4, 2).'/'
													.substr($final_history[0]['set_time'], 6, 2);
		}

		//test_log($group_content[0]['set_time']);
		return $final_history[0];
	}

	// ハンドルからユーザID 取得
	function getUserIDbyHandle($handle, $testee_id)
	{
		$params = array( $testee_id, $handle );

		$sql  = "SELECT u.user_id, a.user_id AS tedcauthority_user_id ";
		$sql .= "FROM {users} u ";
		$sql .=   "LEFT JOIN {testee_tedcauthority} a ON u.user_id = a.user_id AND a.testee_id = ? ";
		$sql .= "WHERE u.handle = ?";

		$users = $this->_db->execute($sql, $params );

		if ($users === false) {
			$this->_db->addError();
			return $users;
		}

		if ( empty( $users ) ) {
			return null;
		}

		return $users[0];
	}

	// ルーム内ユーザの選択
	function getRoomMailUser( $room_id, $metadata_id )
	{
		/*
			SELECT l.role_authority_id, u.handle, mu.testee_mail_users_id, il.content AS user_item_hospital, hospital.hospital_code
			FROM epoc_pages_users_link l
			  INNER JOIN epoc_users u ON u.user_id = l.user_id
			  LEFT JOIN epoc_testee_mail_users mu ON mu.user_id = l.user_id
			  LEFT JOIN epoc_users_items_link il ON il.user_id = l.user_id AND il.item_id = ( SELECT item_id FROM epoc_items WHERE item_name = 'USER_ITEM_HOSPITAL' )
			  LEFT JOIN epoc_hospitalinfo hospital ON CONCAT( hospital.hospital_name, '|' ) = il.content
			WHERE l.room_id = 15
			ORDER BY hospital.hospital_code
		*/

		$params = array( $metadata_id, $room_id );

		$sql  = "SELECT l.role_authority_id, u.user_id, u.handle, mu.testee_mail_users_id, il.content AS user_item_hospital, hospital.hospital_code ";
		$sql .= "FROM {pages_users_link} l ";
		$sql .=   "INNER JOIN {users} u ON u.user_id = l.user_id ";
		$sql .=   "LEFT JOIN {testee_mail_users} mu ON mu.user_id = l.user_id AND metadata_id = ? ";
		$sql .=   "LEFT JOIN {users_items_link} il ON il.user_id = l.user_id AND il.item_id = ( SELECT item_id FROM {items} WHERE item_name = 'USER_ITEM_HOSPITAL' ) ";
		$sql .=   "LEFT JOIN {hospitalinfo} hospital ON CONCAT( hospital.hospital_name, '|' ) = il.content ";
		$sql .= "WHERE l.room_id = ? ";
		$sql .= "ORDER BY hospital.hospital_code";

		$users = $this->_db->execute($sql, $params );

		if ($users === false) {
			$this->_db->addError();
			return $users;
		}

		return $users;
	}

	// metadeta に設定されているメール送信ユーザの取得
	function getMetadataMailUser( $metadata_id )
	{
		/*
			SELECT u.handle, mu.testee_mail_users_id, il.content AS user_item_hospital, hospital.hospital_code
			FROM epoc_testee_mail_users mu
			  INNER JOIN epoc_users u ON u.user_id = mu.user_id
			  LEFT JOIN epoc_users_items_link il ON il.user_id = mu.user_id AND il.item_id = ( SELECT item_id FROM epoc_items WHERE item_name = 'USER_ITEM_HOSPITAL' )
			  LEFT JOIN epoc_hospitalinfo hospital ON CONCAT( hospital.hospital_name, '|' ) = il.content
			WHERE mu.metadata_id = 9
			ORDER BY hospital.hospital_code
		*/

		$params = array( $metadata_id );

		$sql  = "SELECT u.handle, u.user_id, mu.testee_mail_users_id, il.content AS user_item_hospital, hospital.hospital_code ";
		$sql .= "FROM {testee_mail_users} mu ";
		$sql .=   "INNER JOIN {users} u ON u.user_id = mu.user_id ";
		$sql .=   "LEFT JOIN {users_items_link} il ON il.user_id = mu.user_id AND il.item_id = ( SELECT item_id FROM {items} WHERE item_name = 'USER_ITEM_HOSPITAL' ) ";
		$sql .=   "LEFT JOIN {hospitalinfo} hospital ON CONCAT( hospital.hospital_name, '|' ) = il.content ";
		$sql .= "WHERE mu.metadata_id = ? ";
		$sql .= "ORDER BY hospital.hospital_code";

		$users = $this->_db->execute($sql, $params );

		if ($users === false) {
			$this->_db->addError();
			return $users;
		}

		return $users;
	}

	// 試験毎にメール送信ユーザが設定されている場合に、取得する。
	function getMailUser4Testee( $testee_id )
	{
		// 試験データ取得
		$testee = $this->getTestee( $testee_id );

		// 試験で「登録時にもこの設定でメールを送信する」の設定がされているか。
		// されていれば、testee テーブルの add_mail_send_metadata_id 
		$add_mail_send_metadata_id = 0;
		if ( !empty( $testee ) && count( $testee ) > 0 ) {
			$add_mail_send_metadata_id = $testee[0]["add_mail_send_metadata_id"];
		}

		// 「登録時にもこの設定でメールを送信する」の設定があれば続き
		if ( !$add_mail_send_metadata_id ) {
			return null;
		}

		// metadeta に設定されているメール送信ユーザの取得
		$users = $this->getMetadataMailUser( $add_mail_send_metadata_id );

		return $users;
	}

	// 進捗データの取得
	function getContentStatus( $content_id, $status_id = null, $content_no = null )
	{
		/*
			SELECT testee_content_status_id, testee_id, content_id, status_id, unique_patient_cd, content_no, content_data
			FROM testee_content_status
			WHERE content_id = 1
			ORDER BY testee_content_status_id DESC
		*/

		$params = array( $content_id );

		$sql  = "SELECT testee_content_status_id, testee_id, content_id, status_id, unique_patient_cd, content_no, content_data, insert_time ";
		$sql .= "FROM {testee_content_status} ";
		$sql .= "WHERE content_id = ? ";

		if ( !empty( $status_id ) ) {
			$params[] = $status_id;
			$sql .= "AND status_id = ? ";
		}

		if ( !empty( $content_no ) ) {
			$params[] = $content_no;
			$sql .= "AND content_no = ? ";
		}

		$sql .= "ORDER BY status_id, content_no DESC";

		$statuses = $this->_db->execute( $sql, $params );

		if ( $statuses === false ) {
			$this->_db->addError();
			return $statuses;
		}

		return $statuses;
	}

	// 進捗データの取得
	function getContentStatusFromContentids( $content_ids )
	{
		/*
			SELECT testee_content_status_id, testee_id, content_id, status_id, unique_patient_cd, content_no, content_data
			FROM testee_content_status
			WHERE content_id in ( 1, 2 )
			ORDER BY testee_content_status_id DESC
		*/

		$content_id_in = implode( ",", $content_ids );

		$sql  = "SELECT testee_content_status_id, testee_id, content_id, status_id, unique_patient_cd, content_no, content_data, insert_time ";
		$sql .= "FROM {testee_content_status} ";
		$sql .= "WHERE content_id in ( " . $content_id_in . " ) ";

		$statuses = $this->_db->execute( $sql );

		if ( $statuses === false ) {
			$this->_db->addError();
			return $statuses;
		}

		// 画面で使いやすいように進捗データをcontent_id をキーにして詰め直す。
		$retuen_statuses = array();
		foreach( $statuses as $status ) {
			$retuen_statuses[$status["content_id"]][$status["status_id"]][$status["content_no"]] = $status["content_data"];
			$retuen_statuses[$status["content_id"]][$status["status_id"]]["insert_time"] = $status["insert_time"];
		}

		return $retuen_statuses;
	}

	// 進捗データの設定権限
	function getStatusAuth( $testee_id )
	{

		$session = $this->_container->getComponent("Session");
		$user_id = $session->getParameter("_user_id");

		/*
			SELECT il.content AS user_item_tedc, a.tedc_authority
			FROM epoc_users_items_link il
			  LEFT JOIN epoc_testee_tedcauthority a ON a.user_id = il.user_id AND a.testee_id = 1
			WHERE il.user_id = "62e5da4b8815ef41ceef2091f9179808e97dd1f4"
			  AND il.item_id = ( SELECT item_id FROM epoc_items WHERE item_name = "USER_ITEM_TEDC" )
		*/

		$params = array( $testee_id, $user_id );

		$sql  = "SELECT il.content AS user_item_tedc, a.tedc_authority ";
		$sql .= "FROM {users_items_link} il ";
		$sql .=   "LEFT JOIN {testee_tedcauthority} a ON a.user_id = il.user_id AND a.testee_id = ? ";
		$sql .= "WHERE il.user_id = ? ";
		$sql .=  "AND il.item_id = ( SELECT item_id FROM epoc_items WHERE item_name = 'USER_ITEM_TEDC' )";

		$status_auth = $this->_db->execute( $sql, $params );

		if ( $status_auth === false ) {
			$this->_db->addError();
			return $status_auth;
		}
		if ( empty( $status_auth ) || count( $status_auth ) < 1 ) {
			return null;
		}

		// 画面で使うTEDC 権限のセット
		$tedc_auth_param = array( "status_1" => false, "status_2" => false, "status_3" => false );

		$tedc_authority = $status_auth[0]["tedc_authority"];
		$user_item_tedc = $status_auth[0]["user_item_tedc"];

		// 試験毎の権限がある場合
		if ( $tedc_authority ) {
			if ( $tedc_authority == "99" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => true );
			}
			else if ( $tedc_authority == "07" ) {
				$tedc_auth_param = array( "status_1" => false, "status_2" => false, "status_3" => true );
			}
			else if ( $tedc_authority == "06" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => true );
			}
			else if ( $tedc_authority == "05" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => true );
			}
			else if ( $tedc_authority == "04" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
			else if ( $tedc_authority == "03" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
			else if ( $tedc_authority == "02" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
			else if ( $tedc_authority == "01" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
		}
		// 試験毎の権限がない場合
		else {
			if ( $user_item_tedc == "USER_ITEM_TEDC_99|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => true );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_07|" ) {
				$tedc_auth_param = array( "status_1" => false, "status_2" => false, "status_3" => true );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_06|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => true );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_05|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => true );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_04|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_03|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_02|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
			else if ( $user_item_tedc == "USER_ITEM_TEDC_01|" ) {
				$tedc_auth_param = array( "status_1" => true, "status_2" => true, "status_3" => false );
			}
		}

		return $tedc_auth_param;
	}

	// コンテンツデータの取得
	function getContent( $content_id )
	{

		$params = array( $content_id );

		$sql  = "SELECT * ";
		$sql .= "FROM {testee_content} ";
		$sql .= "WHERE content_id = ? ";

		$content = $this->_db->execute( $sql, $params );

		if ( $content === false ) {
			$this->_db->addError();
			return $content;
		}

		if ( empty( $content ) || count( $content ) == 0 ) {
			return $content;
		}

		return $content[0];
	}

	// コンテンツデータの施設の取得
	function getContentHospital( $testee_id, $content_id )
	{

		/*
			SELECT *
			FROM epoc_testee_metadata_content
			WHERE content_id = 1 AND metadata_id = (
				SELECT metadata_id
				FROM epoc_testee_metadata
				WHERE testee_id = 1 AND type = 20
			)
		*/


		$params = array( $content_id, $testee_id, TESTEE_META_TYPE_N_HOSPITAL );

		$sql  = "SELECT * ";
		$sql .= "FROM {testee_metadata_content} ";
		$sql .= "WHERE content_id = ? AND metadata_id = ( ";
		$sql .=     "SELECT metadata_id ";
		$sql .=     "FROM {testee_metadata} ";
		$sql .=     "WHERE testee_id = ? AND type = ? ";
		$sql .= ")";

		$content_hospital = $this->_db->execute( $sql, $params );

		if ( $content_hospital === false ) {
			$this->_db->addError();
			return $content_hospital;
		}

		if ( empty( $content_hospital ) || count( $content_hospital ) == 0 ) {
			return $content_hospital;
		}

		return $content_hospital[0]["content"];
	}

}
?>