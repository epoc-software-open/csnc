<?php

/**
 * エクスポート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Testee_View_Main_Export extends Action
{
	// リクエストパラメータをセットするため
	var $testee_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
    var $csvMain = null;
    var $mdbView = null;
    var $actionChain = null;
    var $session = null;

    // バリデートによりセット
	var $mdb_obj = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {

		// TEDC権限の取得
		$tedc_authority = $this->mdbView->getUserTEDC($this->session->getParameter("_user_id"), $this->testee_id);

		// DM と管理者のみ、CSVダウンロードを許可
		if ( $tedc_authority == TEDC_AUTHORITY_DM || $tedc_authority == TEDC_AUTHORITY_ADMIN ) {
		}
		else {
			return "";
		}

		// 割付設定の取得
		$allocation = $this->mdbView->getAllocationContent( $this->testee_id );

    	$metadatas = $this->mdbView->getMetadatas(array("testee_id" => intval($this->testee_id)));
		if($metadatas === false) {
    		return 'error';
    	}

		$order_params = array(
			"display_sequence" => "ASC"
		);
		$data = array();
		$line = 0;
		$data_contents = array();

// 行の最初に登録番号・登録日付を付与
		$data[] = "USUBJID";

		// 割付
		if ( is_array($allocation) && array_key_exists("allocation_result_flag", $allocation) && $allocation["allocation_result_flag"] == 1 ) {
			$data[] = "ARMCD";
			$data[] = "ARM";
		}

		$data[] = "REGDTC";
		$contents = $this->mdbView->getNID(intval($this->testee_id), $order_params);

		if($contents === false) {
			return 'error';
		}
		$i = 0;
		foreach($contents as $content) {
			$data_contents[$i][0] = $content["content_no"];

			// 割付
			if ( is_array($allocation) && array_key_exists("allocation_result_flag", $allocation) && $allocation["allocation_result_flag"] == 1 ) {
				$data_contents[$i][1] = $content["group_name"];
				$data_contents[$i][2] = $content["intervention"];
				$data_contents[$i][3] = timezone_date_format($content["insert_time"], _FULL_DATE_FORMAT);
			}
			else {
				$data_contents[$i][1] = timezone_date_format($content["insert_time"], _FULL_DATE_FORMAT);
			}
			$i++;
		}

		if ( is_array($allocation) && array_key_exists("allocation_result_flag", $allocation) && $allocation["allocation_result_flag"] == 1 ) {
			$line = 4;
		}
		else {
			$line = 2;
		}
		foreach ($metadatas as $metadata) {
			// コメントは出力対象外
			if($metadata['type'] == TESTEE_META_TYPE_N_COMMENT){
				continue;
			}
			$data[] = $metadata['varb_name'];		// メタ名称でなく変数名を項目名に表示
			// 生年月日の場合、後ろに年齢を付与
			if($metadata['type'] == TESTEE_META_TYPE_N_BIRTHDAY){
				$data[] = "AGE";
			}

			$contents = $this->mdbView->getMDBTitleList(intval($this->testee_id), $metadata['metadata_id'], $order_params);
			if($contents === false) {
				return 'error';
			}
			$i = 0;
			foreach($contents as $content) {
				if($content['title'] != ""
					 && ($metadata['type'] == TESTEE_META_TYPE_FILE 
							|| $metadata['type'] == TESTEE_META_TYPE_IMAGE)) {
					if ($content['file_name'] != "") {
						$content['title'] = $content['file_name'];
					} else {
						$content['title'] = "";
					}
				} elseif ($content['title'] != "" && $metadata['type'] == TESTEE_META_TYPE_DATE) {
					$content['title'] = timezone_date_format($content['title'], _DATE_FORMAT);
				} elseif ($content['title'] != "" && $metadata['type'] == TESTEE_META_TYPE_N_DATE) {
					$content['title'] = timezone_date_format($content['title'], _DATE_FORMAT);
				} elseif ($content['title'] != "" && $metadata['type'] == TESTEE_META_TYPE_N_BIRTHDAY) {
					$content['title'] = timezone_date_format($content['title'], _DATE_FORMAT);
				} elseif ($content['insert_time'] != "" && $metadata['type'] == TESTEE_META_TYPE_INSERT_TIME) {
					$content['title'] = timezone_date_format($content['insert_time'], _FULL_DATE_FORMAT);
				} elseif ($content['update_time'] != "" && $metadata['type'] == TESTEE_META_TYPE_UPDATE_TIME) {
					$content['title'] = timezone_date_format($content['update_time'], _FULL_DATE_FORMAT);
				} elseif ($content['title'] != "" && $metadata['type'] == TESTEE_META_TYPE_AUTONUM) {
					$content['title'] = intval($content['title']);
				}
				$data_contents[$i][$line] = $content['title'];
				$i++;
			}

			// 生年月日の場合、後ろに年齢を付与
			if($metadata['type'] == TESTEE_META_TYPE_N_BIRTHDAY){

				$line = $line + 1;
				for ($i = 0; $i < count($contents); $i++){
					// 年齢計算
					$age = 0;

					// 割付
					if ( is_array($allocation) && array_key_exists("allocation_result_flag", $allocation) && $allocation["allocation_result_flag"] == 1 ) {
						$age = $this->mdbView->calc_age($data_contents[$i][$line - 1], $data_contents[$i][3]);
					}
					else {
						$age = $this->mdbView->calc_age($data_contents[$i][$line - 1], $data_contents[$i][1]);
					}

					if($age === 0){
						$age = "0";
					}
					$data_contents[$i][$line] = $age;
				}
			}

			$line++;
		}

		$this->csvMain->add($data);
		foreach($data_contents as $data_content) {
			$this->csvMain->add($data_content);
		}
		$this->csvMain->download($this->mdb_obj['testee_name']);

		exit;
	}
}
?>