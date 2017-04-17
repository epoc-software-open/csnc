<?php

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Main_Status extends Action
{
	// リクエストパラメータを受け取るため
	var $testee_id = null;
	var $content_id = null;
	var $status_id = null;
	var $unique_patient_cd = null;
	var $content_data = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $mdbAction = null;
	var $mdbView = null;

	// 値をセットするため

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{

		// 個別対応

		// --- 治療後の資材・伝票の送付で確定診断病名が「がん」以外の場合は、資材・伝票送付日、送付者氏名は空にする。
		if ( $this->status_id == 3 && ( $this->content_data[1] == 2 || $this->content_data[1] == 3 ) ) {
			$this->content_data[2] = "";
			$this->content_data[3] = "";
		}

		// status_id でデータの有無を確認。登録時はメールするための判断材料として。
		$status_check_record = $this->mdbView->getContentStatus( $this->content_id, $this->status_id );

		// 登録後にメールするために、セッションにキーを追加
		$testee_status_mail = array( "content_id" => $this->content_id, "status_id" => $this->status_id, "testee_id" => $this->testee_id );
		if ( empty( $status_check_record ) ) {
			$testee_status_mail["regist_kind"] = "insert";
		}
		else {
			$testee_status_mail["regist_kind"] = "update";
		}

		// [更新]の場合、変更前情報をセッションに登録しておく
		if( !empty( $status_check_record ) )
		{
			$getStatusData = $this->mdbView->getContentStatus( $this->content_id, $this->status_id );
			$testee_status_mail["before_status_data"] = $getStatusData;
		}

		$this->session->setParameter( "testee_status_mail", $testee_status_mail );

		// 進捗フォームの個別データ部分の数だけループする。
		foreach( $this->content_data as $content_no => $content_str ) {
			$this->testee_content_status_update( $this->testee_id, $this->content_id, $this->status_id, $this->unique_patient_cd, $content_no, $content_str );
		}

		return 'success';
	}


	/**
	 * データ追加 or 更新
	 */
	function testee_content_status_update( $testee_id, $content_id, $status_id, $unique_patient_cd, $content_no, $content_data )
	{

		// 施設患者番号がない場合は、空文字にする。
		if ( $unique_patient_cd == null ) {
			$unique_patient_cd = "";
		}

		// status_id でデータの状況を確認
		$statuses = $this->mdbView->getContentStatus( $content_id, $status_id, $content_no );

		// データがある場合は更新、なければ登録
		if ( empty( $statuses ) ) {

			$insert_params = array (
				"testee_id"         => $testee_id,
				"content_id"        => $content_id,
				"status_id"         => $status_id,
				"unique_patient_cd" => $unique_patient_cd,
				"content_no"        => $content_no,
				"content_data"      => $content_data
			);

			$result = $this->db->insertExecute( "testee_content_status", $insert_params, true, "testee_content_status_id" );
			if ( $result === false ) {
				//test_log( $this->db->ErrorMsg() );
				return 'error';
			}
		}
		else {

			$update_params = array (
				"unique_patient_cd" => $unique_patient_cd,
				"content_data"      => $content_data
			);

			$where_params = array( "testee_id" => $testee_id, "content_id" => $content_id, "status_id" => $status_id, "content_no" => $content_no );
			$result = $this->db->updateExecute("testee_content_status", $update_params, $where_params, true);
			if ( $result === false ) {
				//test_log( $this->db->ErrorMsg() );
				return 'error';
			}
		}

		return true;
	}
}
?>
