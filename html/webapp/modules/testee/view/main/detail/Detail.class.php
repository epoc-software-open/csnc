<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ詳細画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Testee_View_Main_Detail extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $content_id = null;
	var $comment_id = null;
	var $clear_comment = null;
	var $block_id = null;
	var $html_flag = null;
	var $search = null;
	var $print_flag = null;

	// バリデートによりセット
	var $mdb_obj = null;
	var $detail = null;

	// 使用コンポーネントを受け取るため
	var $abbreviateurlView = null; //--URL短縮形関連
	var $mobileView = null;
	var $mdbView = null;
	var $mdbAction = null;		// 参照ログ書き込みのため EddyK
	var $session = null;		// セッションコンポーネント

	// 値をセットするため
	var $short_url = ""; //--URL短縮形関連
	var $comments = null;				// コメント EddyK

	// 進捗の表示文言
	var $status_str = null;

	// 進捗の入力制限
	var $tedc_auth_param = null;

	var $intervention = null;   // 割付設定

	// 検体進捗管理
	//var $input_report_id = null; // 進捗の登録の検体進捗ID(スタブのみ？)
	var $statuses = null;                // 進捗データ
	var $max_status_id = null;           // 最大進捗番号(どこまで入力したか)
	var $last_unique_patient_cd = null;  // 最後の施設患者番号
	var $mod_status_id = null;           // 変更する進捗番号

	//var $mod_report_id = null;   // 進捗の変更の場合に検体進捗IDが画面から渡される。
	//var $report_records = null;  // 進捗の進捗データ

	// 施設患者番号の入力用
	var $unique_patient_cd = null;

	// 施設患者番号の入力用(最初の入力用)
	var $base_unique_patient_cd = null;

	var $status_show_list      = null;	// 進捗の表示リスト
	var $status_list_show_flag = null;	// 進捗リスト表示フラグ

	var $show_allocation_flag = null;		// 割付情報表示フラグ null or 0:非表示 / 1:表示


	/**
	 * コンテンツ詳細画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
//	print_r($this->mdb_obj);
//	test_log($this->detail);

		// ---------- 詳細データ参照のログ保存 EddyK start. ----------
		$action_keys = array( "testee_id" => $this->testee_id,
		                      "content_id" => $this->content_id,
		                      "block_id" => $this->block_id );
		$this->mdbAction->addDatabaseViewLog( "SELECT Action=Detail", $action_keys );
		// ---------- 詳細データ参照のログ保存 EddyK end. ----------
		//--URL短縮形関連
		$this->short_url = $this->abbreviateurlView->getAbbreviateUrl($this->content_id);
		$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		$this->search = intval($this->search);

		// コメントの取得　EddyK
		$this->comments = $this->mdbView->getCommentList($this->testee_id);
		if($this->comments === false) {
			return 'error';
		}


		// 割付のテスト
		//$container =& DIContainerFactory::getContainer();
		//$mdbAction = $container->getComponent("mdbAction");

		//$datas = array ( 1017 => "Ⅲ", 1018 => "高分化" );
		//$mdbAction->setContentAllocation( $this->testee_id, $datas );


//		// 割付設定の取得
		$result = $this->mdbView->getAllocationContent( $this->testee_id );
		if( $result === false )
		{
			return 'error';
		}
		else if( count( $result ) > 0 && empty( $result[ 'allocation_result_flag' ] ) === false )
		{
			// ----- 割付結果詳細情報の表示 --------------------------------------------------------
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
		
		
		// 進捗の取得
		$statuses = $this->mdbView->getContentStatus( $this->content_id );
		//print_r( $statuses );

		// 進捗の組換え[進捗番号:status_id][コンテンツ番号:content_no]
		$this->statuses = array();
		foreach( $statuses as $status ) {

			// 画面で使うための配列
			$this->statuses[$status["status_id"]][$status["content_no"]] = $status;

			// 入力部分がどこかの判定をするため、現在の進捗番号を保持しておく
			if ( $this->max_status_id < $status["status_id"] ) {
				$this->max_status_id = $status["status_id"];
			}

			// 最後の施設患者番号を保持しておく。
			// これは、初期に入力した後、進捗入力時に変更になる可能性があるため。
			if ( $status["unique_patient_cd"] ) {
				$this->last_unique_patient_cd = $status["unique_patient_cd"];
			}
		}
		//print_r( $this->statuses );

		//$this->status_str = "未設定";

		//if ( !empty( $this->statuses ) && count( $this->statuses ) > 0 ) {
		//	if ( $this->statuses[0]["status_id"] == 1 ) {
		//		$this->status_str = "採血";
		//	}
		//	else if ( $this->statuses[0]["status_id"] == 2 ) {
		//		$this->status_str = "提出";
		//	}
		//	else if ( $this->statuses[0]["status_id"] == 3 ) {
		//		$this->status_str = "受け取り";
		//	}
		//}

		// 進捗の権限取得
		$this->tedc_auth_param = $this->mdbView->getStatusAuth( $this->testee_id );
		//print_r($tedc_auth_param);


		// 検体進捗管理

		// 検体進捗データがある。
		// 今の状態(本当はデータのあるなし)、テストは引数で。
		// 各レコードに表示、入力フラグあり。
		// 変更の場合は変更レコードのみ入力で他は表示
		// 変更以外の場合は、次の入力用レコードまで配列を生成

		//$this->report_records = array();

		// 資材・伝票の送付
		//$this->report_records[0] = array( "report_id" => 0, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "a01" => "2016/04/10", "a02" => "テスト　Ａ太郎" );

		// 検体登録
		//$this->report_records[1] = array( "report_id" => 1, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "b01" => "2016/04/11", "b02" => "2016/04/12", "b03" => "2", "b04" => "テスト　Ｂ太郎" );

		// 治療後の資材・伝票の送付
		//$this->report_records[2] = array( "report_id" => 2, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "c01" => "がん", "c02" => "2016/04/13", "c03" => "テスト　Ｃ太郎" );

		// 治療後の検体登録
		//$this->report_records[3] = array( "report_id" => 3, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "d01" => "2016/04/14", "d02" => "2016/04/15", "d03" => "テスト　Ｄ太郎" );

		// 検体払い出し（治療前）
		//$this->report_records[4] = array( "report_id" => 4, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "e01" => "2016/04/16", "e02" => "2", "e03" => "テスト　Ｅ太郎" );

		// 検体払い出し（治療後）
		//$this->report_records[5] = array( "report_id" => 5, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "f01" => "2016/04/17", "f02" => "2", "f03" => "テスト　Ｅ太郎" );

		// マイクロRNA測定データ受領確認
		//$this->report_records[6] = array( "report_id" => 6, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "g01" => "2016/04/18", "g02" => "判別データあり", "g03" => "テスト　Ｇ太郎" );

		// 治療後検体のマイクロRNA測定データ受領確認
		//$this->report_records[7] = array( "report_id" => 7, "input_flag" => false, "content_no" => 1, "patient_no" => "A9987019", "h01" => "2016/04/19", "h02" => "判別データあり", "h03" => "テスト　Ｈ太郎" );

		// スタブ用
		//if ( $this->mod_report_id == null ) {
		//	if ( $this->input_report_id == null ) {
		//		$this->input_report_id == 0;
		//	}
		//	foreach( $this->report_records as $report_key => $report_record ) {
		//		if ( $this->input_report_id == $report_key ) {
		//			$this->report_records[$report_key]["input_flag"] = true;
		//		}
		//		if ( $this->input_report_id < $report_key ) {
		//			unset( $this->report_records[$report_key] );
		//		}
		//	}
		//}
		//else {
		//	foreach( $this->report_records as $report_key => $report_record ) {
		//		if ( $this->mod_report_id == $report_key ) {
		//			$this->report_records[$report_key]["input_flag"] = true;
		//		}
		//	}
		//}

		// print_r($this->report_records);

		// 最初の進捗登録用に施設患者番号がある場合は、個別に保持しておく。
		if ( !empty($this->detail) && array_key_exists( "metadata", $this->detail ) ) {

			$metadatas = $this->detail["metadata"];
			foreach ( $metadatas as $metadata_position ) {
				foreach ( $metadata_position as $metadata ) {
					if ( $metadata["name"] == "施設患者番号" ) {
						$this->base_unique_patient_cd = $this->detail["value"][$metadata["metadata_id"]];
						break 2;
					}
				}
			}
		}
		
		// ユーザーの、試験ベース権限or試験毎権限取得
		$tedc_authority = $this->mdbView->getUserTEDC($this->session->getParameter("_user_id"), $this->testee_id);
		if( $tedc_authority != TEDC_AUTHORITY_ADMIN && $tedc_authority != TEDC_AUTHORITY_DM && $tedc_authority != TEDC_AUTHORITY_ASSISTANT )
		{
			// 管理者/DM/作業補助者 以外の場合は、進捗管理機能非表示の為、進捗リストの表示設定せずに終了
			return 'success';
		}
		
		// 進捗管理機能詳細表示あり
		$this->status_list_show_flag = 1;
		
		// 進捗の表示非表示を設定する
		$this->status_show_list = $this->_getShowStatusList();

		return 'success';
	}
	
	/**
	 * 進捗表示リストの取得
	 *
	 * @access  private
	 */
	private function _getShowStatusList()
	{
		// 全非表示の表示リストを作成する
		$showList = array_fill(1, 8, '0');
		
		// ステータスIDのリストを取得
		$statusList = array_keys( $this->statuses );
		
		// 最後のステータスを取得
		$end_status_id = 0;
		foreach( $statusList as $value )
		{
			if( $end_status_id < intval( $value ) )
			{
				$end_status_id = intval( $value );
			}
		}
		// 最初のステータスから現在の最後のステータス部分まで、"1"(表示)を設定
		for( $i = 1; $i <= $end_status_id; $i++ )
		{
			$showList[ $i ] = 1;
		}
		
		// 変更状態にしておくstatus_idを設定
		if( empty( $this->mod_status_id ) )
		{
			// 変更指示がない場合、最後のstatus_idの次を"変更"にしておく
			if( count( $showList ) <= $end_status_id )
			{
				// 最後まで入力されているので何もしない
			}
			else
			{
				$nextStatusID = $end_status_id + 1;
				$showList[ $nextStatusID ] = 2;
			}
		}
		else
		{
			// 変更指示がある場合は、そのステータスを"変更"にしておく
			$showList[ intval( $this->mod_status_id ) ] = 2;
		}
		
		// [治療後の資材・伝票の送付](status_id=3)、[確定後診断病名](content_no=1)が[がん]以外の場合は、
		// 入力できる進捗が異なる為、表示/非表示の制御を行う
		if( array_key_exists( "3", $this->statuses ) == true )
		{
			if( array_key_exists( "1", $this->statuses["3"] ) == true )
			{
				if( intval( $this->statuses["3"]["1"]["content_data"] ) != 1 )
				{
					// [がん](content_data=1)以外の場合は、以下の進捗は非表示設定
					$showList[4] = 0;	// [治療後の検体登録](status_id=4)
					$showList[6] = 0;	// [検体払い出し(治療後)]
					$showList[8] = 0;	// [治療後検体のマイクロRNA測定データ受領確認]
					
					// 最後の入力がどこか判断し、次の入力場所を判定
					if( count( $showList ) > $end_status_id )
					{
						if( $end_status_id == 3 || $end_status_id == 5 )
						{
							$nextStatusID = $end_status_id + 2;
							$showList[ $nextStatusID ] = 2;
						}
					}
				}
			}
		}
		
		return $showList;
	}
}
?>