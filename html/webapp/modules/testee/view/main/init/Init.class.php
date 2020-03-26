<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ一覧画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Testee_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $sort_section = null;
	var $sort_metadata = null;
	var $visible_item = null;
	var $now_page = null;
	var $html_flag = null;

	var $content_id = null;

	// バリデートによりセット
	var $mdb_obj = null;
	var $metadatas = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $session = null;
	var $mobileView = null;
	var $mdbAction = null;		// 参照ログ書き込みのため EddyK

	// 値をセットするため
	var $section_metadatas = null;
	var $testee_id = null;
	var $metadata_exists = true;
	var $exists = true;
	var $sort_metadatas = null;
	var $mdblist = null;
	var $vote_count = null;
	var $block_num = null;

	var $comments = null;		// コメント EddyK
	var $counts_check = null;	// 件数設定による確認結果 EddyK
	var $tedc_authority = null;	// TEDC権限 EddyK
	var $my_hospital = null;	// 自分の施設情報 EddyK
	var $content_no = null;		// 現在のコンテンツ番号 EddyK
	var $contents = null;		// コンテンツ情報（コンテンツの有無確認用） EddyK

	var $intervention = null;   // 割付設定

	var $statuses = null;       // 検体進捗データ

	var $kentai_status = null;  // 検体の進捗管理の絞り込み用の項目

	var $show_allocation_flag = null;	// 割付情報表示フラグ null or 0:非表示 / 1:表示

	//ページ
	var $data_cnt	= 0;
	var $total_page = 0;
	var $next_link = false;
	var $prev_link = false;
	var $disp_begin = 0;
	var $disp_end = 0;
	var $link_array = null;

	var $status_list_show_flag = null;		// 進捗リスト表示フラグ

	/**
	 * コンテンツ一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		// コンテンツの登録可否を判断するリストの取得 EddyK
		$this->counts_check = $this->mdbView->getOptionCounts($this->testee_id);
		// TEDC権限の取得 EddyK
		$this->tedc_authority = $this->mdbView->getUserTEDC($this->session->getParameter("_user_id"), $this->testee_id);
		// 登録者（自分）の施設情報（パイプ付）の取得 EddyK
		$this->my_hospital = $this->mdbView->getHospital($this->session->getParameter("_user_id"));
		// 登録番号の取得
		$this->content_no = $this->mdbView->getContentNo( $this->testee_id );
		// コンテンツの取得
		$this->contents = $this->mdbView->getNID( $this->testee_id );

		// ---------- 一覧データ参照のログ保存 EddyK start. ----------
		$action_keys = array( "testee_id" => $this->testee_id,
		                      "block_id" => $this->block_id );
		$this->mdbAction->addDatabaseViewLog( "SELECT Action=Init", $action_keys );
		// ---------- 一覧データ参照のログ保存 EddyK end. ----------

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
			$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		}

		if(empty($this->metadatas)) {
			$this->metadata_exists = false;
			return 'success';
		}

		$section_params = array(
			"testee_id" => intval($this->testee_id),
			"list_flag" => _ON,
//			"type IN (".TESTEE_META_TYPE_SECTION.",".TESTEE_META_TYPE_MULTIPLE.") " => null 	追加メタタイプ（選択タイプ）EddyK
			"type IN (".TESTEE_META_TYPE_SECTION.",".TESTEE_META_TYPE_MULTIPLE.",".TESTEE_META_TYPE_N_RADIO.",".TESTEE_META_TYPE_N_YESNO.",".TESTEE_META_TYPE_N_APPLICABLE.",".TESTEE_META_TYPE_N_GROUP.",".TESTEE_META_TYPE_N_HOSPITAL.",".TESTEE_META_TYPE_N_SEX.") " => null
		);
		$this->section_metadatas = $this->mdbView->getMetadatas($section_params);
		if($this->section_metadatas === false) {
			return 'error';
		}


		// 施設情報の選択肢の表示条件

		// TEDC権限が研究事務局以上は全て表示対象
		// TEDC権限が施設研究責任者以下はログインユーザーと同一の施設者登録の情報のみ表示対象
		if( $this->tedc_authority <= TEDC_AUTHORITY_READER ) {

			foreach( $this->section_metadatas as $section_metadatas_key => $section_metadatas ) {
				if ( $section_metadatas["type"] == TESTEE_META_TYPE_N_HOSPITAL && array_key_exists( "select_content_array", $section_metadatas ) ) {
					$select_content_array = $section_metadatas["select_content_array"];

					if ( is_array( $select_content_array ) ) {
						foreach( $select_content_array as $select_content_key => $select_content ) {
							if ( $select_content != trim( $this->my_hospital, "|" ) ) {
								unset( $this->section_metadatas[$section_metadatas_key]["select_content_array"][$select_content_key] );
							}
						}
					}
				}
			}
		}


		$sort_params = array(
			"testee_id" => intval($this->testee_id),
			"sort_flag" => _ON,
			"list_flag" => _ON
		);
		$this->sort_metadatas = $this->mdbView->getMetadatas($sort_params);
		if($this->sort_metadatas === false) {
			return 'error';
		}

		$sort_section = $this->session->getParameter(array("testee", $this->block_id, "sort_section"));
		if(!empty($this->sort_section)) {
			$this->session->setParameter(array("testee", $this->block_id, "sort_section"), $this->sort_section);
			// 初回、セッションの $sort_section は空なので、その判定を追加 by nagahara@opensource-workshop.jp
			if(!empty($sort_section) && $sort_section != $this->sort_section) {
				// カテゴリ変更を行った場合、1ページ目を表示する。
				$this->now_page = 1;
			}
		}else if(!empty($sort_section)) {
			$this->sort_section = $sort_section;
		}
		$where_params = array();
		if(!empty($this->sort_section)) {
			foreach($this->sort_section as $key => $val) {
				$where_params["m_content".$key.".content"] = $val;
			}
		}

		$visible_item = $this->session->getParameter(array("testee", $this->block_id, "visible_item"));
		if($this->visible_item != "") {
			if($visible_item != "" && $this->visible_item != $visible_item) {
				$this->now_page = 1;
			}
			$this->session->setParameter(array("testee", $this->block_id, "visible_item"), $this->visible_item);
		}else if($visible_item != ""){
			$this->visible_item = $visible_item;
		}else {
			$this->visible_item = $this->mdb_obj['visible_item'];
		}

		$sort_metadata = $this->session->getParameter(array("testee", $this->block_id, "sort_metadata"));
		if(!empty($this->sort_metadata)) {
			$this->session->setParameter(array("testee", $this->block_id, "sort_metadata"), $this->sort_metadata);
			// 初回、セッションの $sort_metadata は空なので、その判定を追加 by nagahara@opensource-workshop.jp
			if(!empty($sort_metadata) && $sort_metadata != $this->sort_metadata) {
				// 並べ替えを行った場合、1ページ目を表示する。
				$this->now_page = 1;
			}
		}else if(!empty($sort_metadata)) {
			$this->sort_metadata = $sort_metadata;
		}else {
			$this->sort_metadata = $this->mdb_obj['default_sort'];
		}
   		if($this->sort_metadata != TESTEE_DEFAULT_DATE_SORT
   			&& $this->sort_metadata != TESTEE_DEFAULT_DATE_ASC_SORT
			&& $this->sort_metadata != TESTEE_DEFAULT_VOTE_SORT
			&& $this->sort_metadata != TESTEE_DEFAULT_SEQUENCE_SORT
			&& !array_key_exists($this->sort_metadata, $this->sort_metadatas)) {
			$this->sort_metadata = TESTEE_DEFAULT_SEQUENCE_SORT;
		}

		// TEDC権限が研究事務局以上は全て表示対象		EddyK
		// TEDC権限が施設研究責任者以下はログインユーザーと同一の施設者登録の情報のみ表示対象		EddyK
		$target_hospital = null;
		if($this->tedc_authority <= TEDC_AUTHORITY_READER){
			$target_hospital = $this->my_hospital;
		}


		// -----------------------------------------------------------
		// - 検体管理機能の絞り込み
		// -----------------------------------------------------------

		if ( empty( $this->kentai_status ) ) {
			$this->kentai_status = array();
		}

		// セッションから取得
		$kentai_search_status = $this->session->getParameter( "kentai_search_status" );

		// セッションになければ、空の枠を作成
		if ( empty( $kentai_search_status ) ) {
			$kentai_search_status = array();
		}
		if ( !array_key_exists( "1", $kentai_search_status ) ) {
			$kentai_search_status[1] = "";
		}
		if ( !array_key_exists( "3", $kentai_search_status ) ) {
			$kentai_search_status[3] = "";
		}
		if ( !array_key_exists( "4", $kentai_search_status ) ) {
			$kentai_search_status[4] = "";
		}

		// 画面で指定されていたらその内容を使用
		// 資材送付
		if ( array_key_exists( 1, $this->kentai_status ) && strlen( trim( $this->kentai_status[1] ) ) ) {
			if ( $this->kentai_status[1] == "ALL" ) {
				$kentai_search_status[1] = "";
			}
			else {
				$kentai_search_status[1] = $this->kentai_status[1];
			}
		}
		// 確定診断病名
		if ( array_key_exists( 3, $this->kentai_status ) && strlen( trim( $this->kentai_status[3] ) ) ) {
			if ( $this->kentai_status[3] == "ALL" ) {
				$kentai_search_status[3] = "";
			}
			else {
				$kentai_search_status[3] = $this->kentai_status[3];
			}
		}
		// 治療後資料送付
		if ( array_key_exists( 4, $this->kentai_status ) && strlen( trim( $this->kentai_status[4] ) ) ) {
			if ( $this->kentai_status[4] == "ALL" ) {
				$kentai_search_status[4] = "";
			}
			else {
				$kentai_search_status[4] = $this->kentai_status[4];
			}
		}
//print_r($kentai_search_status);

		// セッションに保持
		$this->session->setParameter( "kentai_search_status", $kentai_search_status );

		// 画面
		$this->kentai_status = $kentai_search_status;

		// -----------------------------------------------------------
		// - /検体管理機能の絞り込み
		// -----------------------------------------------------------



//		$mdbcount = $this->mdbView->getMDBListCount($this->testee_id, $this->metadatas, $where_params, null, array());
		$mdbcount = $this->mdbView->getMDBListCount($this->testee_id, $this->metadatas, $where_params, null, array(), $target_hospital);
		if($mdbcount === false) {
			return 'error';
		}

		// ゲスト権限ではデータは見せない EddyK
		if ( $this->session->getParameter("_role_auth_id") == _ROLE_AUTH_GUEST ) {
			$mdbcount = 0;
		}
		// TEDC権限が利用不可（未設定）の場合、見せない EddyK
		if($this->tedc_authority == TEDC_AUTHORITY_NOTWORK || $this->tedc_authority == null){
			$mdbcount = 0;
		}

		if($mdbcount == 0) {
			$this->exists = false;
			return 'success';
		}

		$now_page = $this->session->getParameter(array("testee", $this->block_id, "now_page"));
		if(!empty($this->now_page)) {
			$this->session->setParameter(array("testee", $this->block_id, "now_page"), $this->now_page);
		}else if(!empty($now_page)){
			$this->now_page = $now_page;
		}

		if(!empty($this->visible_item)) {
			$this->setPageInfo($mdbcount, $this->visible_item, $this->now_page);
		}

		if(empty($this->sort_metadata) || $this->sort_metadata == TESTEE_DEFAULT_SEQUENCE_SORT) {
			$order_params = array(
				"{testee_content}.display_sequence" => "ASC",
				"{testee_content}.insert_time" => "DESC"
			);
		}else if($this->sort_metadata == TESTEE_DEFAULT_DATE_SORT) {
			$order_params = array(
				"{testee_content}.insert_time" => "DESC"
			);
		}else if($this->sort_metadata == TESTEE_DEFAULT_DATE_ASC_SORT) {
			$order_params = array(
				"{testee_content}.insert_time" => "ASC"
			);
		}else if($this->sort_metadata == TESTEE_DEFAULT_VOTE_SORT) {
			$order_params = array(
				"{testee_content}.vote_count" => "DESC"
			);
		}else if(isset($this->sort_metadatas[$this->sort_metadata]) && ($this->sort_metadatas[$this->sort_metadata]["type"] == TESTEE_META_TYPE_FILE || $this->sort_metadatas[$this->sort_metadata]["type"] == TESTEE_META_TYPE_IMAGE)) {
			$order_params = array(
				"F".$this->sort_metadata.".file_name" => "ASC",
				"{testee_content}.insert_time" => "DESC"
			);
		}else if (isset($this->sort_metadatas[$this->sort_metadata]) && $this->sort_metadatas[$this->sort_metadata]["type"] == TESTEE_META_TYPE_INSERT_TIME){
			$order_params = array(
				"{testee_content}.insert_time" => "ASC"
			);
		}else if (isset($this->sort_metadatas[$this->sort_metadata]) && $this->sort_metadatas[$this->sort_metadata]["type"] == TESTEE_META_TYPE_UPDATE_TIME){
			$order_params = array(
				"{testee_content}.update_time" => "ASC"
			);
		}else{
			$order_params = array(
				"m_content".$this->sort_metadata.".content" => "ASC",
				"{testee_content}.insert_time" => "DESC"
			);
		}

		// TEDC権限が研究事務局以上は全て表示対象		EddyK
		// TEDC権限が施設研究責任者以下はログインユーザーと同一の施設者登録の情報のみ表示対象		EddyK
//		$this->mdblist = $this->mdbView->getMDBList($this->testee_id, $this->metadatas, $where_params, $order_params, $this->visible_item, $this->disp_begin);
		$this->mdblist = $this->mdbView->getMDBList($this->testee_id, $this->metadatas, $where_params, $order_params, $this->visible_item, $this->disp_begin, $target_hospital);
		if($this->mdblist === false) {
			return 'error';
		}

// コメントの取得　EddyK
		$this->comments = $this->mdbView->getCommentList($this->testee_id);
		if($this->comments === false) {
			return 'error';
		}


//		// 割付設定の取得
		
		// 割付情報の取得
		$result = $this->mdbView->getAllocationContent( $this->testee_id );
		if( $result === false )
		{
			return 'error';
		}
		else if( count( $result ) > 0 && empty( $result[ 'allocation_result_flag' ] ) === false )
		{
			// 割付参照権限を取得する
			$user_id = $this->session->getParameter("_user_id");
			if( empty( $user_id ) === false )
			{
				$result = $this->mdbView->getAllocationViewUser( $this->testee_id, $user_id );
				if( $result === false ) return 'error';
				
				if( count( $result ) > 0 )
				{
					$this->show_allocation_flag = 1;
				}
			}
		}
		


		// 進捗管理機能がon の場合、進捗データを取得する。
		if ( TESTEE_KENTAI_SWITCH ) {
			if( $this->tedc_authority == TEDC_AUTHORITY_ADMIN || $this->tedc_authority == TEDC_AUTHORITY_DM || $this->tedc_authority == TEDC_AUTHORITY_ASSISTANT )
			{
				// 管理者/DM/作業補助者のみ、進捗管理データを表示する
				$this->status_list_show_flag = 1;
			}

			$content_ids = array();
			foreach( $this->mdblist["value"] as $mdblist ) {
				$content_ids[] = $mdblist["content_id"];
			}
			$this->statuses = $this->mdbView->getContentStatusFromContentids( $content_ids );
			//test_log($this->statuses);
		}

		return 'success';
	}

	/**
	 * ページに関する設定を行います
	 *
	 * @param int disp_cnt 1ページ当り表示件数
	 * @param int now_page 現ページ
	 */
	function setPageInfo($data_cnt, $disp_cnt, $now_page = NULL){
		$this->data_cnt = $data_cnt;
		// now page
		$this->now_page = (NULL == $now_page) ? 1 : $now_page;
		// total page
		$this->total_page = ceil($this->data_cnt / $disp_cnt);
		if($this->total_page < $this->now_page) {
			$this->now_page = 1;
		}
		// link array {{
		if(($this->now_page - TESTEE_FRONT_AND_BEHIND_LINK_CNT) > 0){
			$start = $this->now_page - TESTEE_FRONT_AND_BEHIND_LINK_CNT;
		}else{
			$start = 1;
		}
		if(($this->now_page + TESTEE_FRONT_AND_BEHIND_LINK_CNT) >= $this->total_page){
			$end = $this->total_page;
		}else{
			$end = $this->now_page + TESTEE_FRONT_AND_BEHIND_LINK_CNT;
		}
		$i = 0;
		for($i = $start; $i <= $end; $i++){
			$this->link_array[] = $i;
		}
		// next link
		if($disp_cnt < $this->data_cnt){
			if($this->now_page < $this->total_page){
				$this->next_link = TRUE;
			}
		}
		// prev link
		if(1 < $this->now_page){
			$this->prev_link = TRUE;
		}
		// begin disp number
		$this->disp_begin = ($this->now_page - 1) * $disp_cnt;
		// end disp number
		$tmp_cnt = $this->now_page * $disp_cnt;
		$this->disp_end = ($this->data_cnt < $tmp_cnt) ? $this->data_cnt : $tmp_cnt;
	}
}
?>