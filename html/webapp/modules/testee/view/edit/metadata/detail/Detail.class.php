<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メタデータ設定(メタデータ編集)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Metadata_detail extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $metadata_id = null;
    var $testee_id = null;

    // 使用コンポーネントを受け取るため
    
    // バリデートによりセット
    var $mdb_obj = null;
    var $metadata = null;

	// 使用コンポーネントを受け取るため
	var $mdbView = null;
	var $db = null;

    // 値をセットするため
	// 新規の場合選択タイプを選ばれた場合のため準備、更新の場合、登録された内容を編集
	// 標準の選択肢用
    var $options1 = null;
    var $options1_len = null;
    var $correct1 = null;				// ラジオボタンの正解	EddyK
    var $select_content1_code = null;	// 選択肢コード値		EddyK

	// はい・いいえ用		EddyK
    var $options2 = null;
    var $options2_len = 0;
    var $correct2 = null;
    var $select_content2_code = null;
	// 該当せず・該当用		EddyK
    var $options3 = null;
    var $options3_len = 0;
    var $correct3 = null;
    var $select_content3_code = null;

    var $group = null;					// グループの選択肢（選択肢・コード値）	EddyK
    var $group_exist = false;			// グループの有無			EddyK

	var $condition = null;				// エラーチェック用条件		EddyK
	var $comment = null;				// コメント					EddyK

	var $contents_count = null;			// 登録されているコンテンツ数	EddyK

	var $alert_on = _OFF;				// アラート表示				EddyK

	// メール送信ユーザ
	var $mail_users = null;

	// testee データベース
	var $testee_obj = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {

    	if($this->metadata_id == 0) {
    		$this->metadata = null;						//初期化
    	}
		else {
			// 条件式
			$result = $this->mdbView->getCondition($this->metadata_id);
			if ($result === false) {
				return 'error';
			}
			if (count($result) > 0) $this->condition = $result[0];
			// コメント
			$result = $this->mdbView->getComment($this->metadata_id);
			if ($result === false) {
				return 'error';
			}
			if (count($result) > 0) $this->comment = $result[0]["comment"];
		}

   		// 新規登録	or 選択式の場合
   		// 選択式を選んだ場合のデータを作成しておく
    	$this->options = array();
    	$this->options_len = 0;
    	if($this->metadata_id == 0 
    		|| ($this->metadata['type'] != TESTEE_META_TYPE_SECTION 
    			&& $this->metadata['type'] != TESTEE_META_TYPE_MULTIPLE 
    			&& $this->metadata['type'] != TESTEE_META_TYPE_N_RADIO 			// 追加メタタイプ
    			&& $this->metadata['type'] != TESTEE_META_TYPE_N_GROUP 			// 追加メタタイプ
    			&& $this->metadata['type'] != TESTEE_META_TYPE_N_HOSPITAL 		// 追加メタタイプ 
    			&& $this->metadata['type'] != TESTEE_META_TYPE_N_SEX)) { 		// 追加メタタイプ
    		for($i=0;$i<TESTEE_DEFAULT_SELECTED_NUM;$i++) {
    			$this->options1[$i] = TESTEE_DEFAULT_OPTIONS.($i+1);		// 選択肢
    			$this->select_content1_code[$i] = $i+1;					// 選択肢コード値	EddyK
    			$this->correct1[$i] = "";								// 正解				EddyK
    		}
    		$this->options1_len = $i;
    	} else {
	    	$this->options1 = explode("|", $this->metadata['select_content']);
	    	$this->options1_len = count($this->options1);
	    	$this->select_content1_code = explode("|", $this->metadata['select_content_code']);	// 選択肢コード値	EddyK
	    	$this->correct1 = explode("|", $this->metadata['correct']);							// 正解				EddyK
	    	$this->options_len = $this->options1_len;
    	}

		// はい・いいえ
    	if($this->metadata_id == 0 
    		|| $this->metadata['type'] != TESTEE_META_TYPE_N_YESNO) {
   			$this->options2[0] = "はい";
   			$this->select_content2_code[0] = "y";
   			$this->correct2[0] = "1";
   			$this->options2[1] = "いいえ";
   			$this->select_content2_code[1] = "n";
   			$this->correct2[1] = "";
    		$this->options2_len = 2;
    	} else {
	    	$this->options2 = explode("|", $this->metadata['select_content']);
	    	$this->options2_len = count($this->options2);
	    	$this->select_content2_code = explode("|", $this->metadata['select_content_code']);	// 選択肢コード値	EddyK
	    	$this->correct2 = explode("|", $this->metadata['correct']);							// 正解				EddyK
	    	$this->options_len = $this->options2_len;
    	}
		// 該当せず・該当
    	if($this->metadata_id == 0 
    		|| $this->metadata['type'] != TESTEE_META_TYPE_N_APPLICABLE) {
   			$this->options3[0] = "該当せず";
   			$this->select_content3_code[0] = "n";
   			$this->correct3[0] = "";
   			$this->options3[1] = "該当";
   			$this->select_content3_code[1] = "y";
   			$this->correct3[1] = "1";
    		$this->options3_len = 2;
    	} else {
	    	$this->options3 = explode("|", $this->metadata['select_content']);
	    	$this->options3_len = count($this->options3);
	    	$this->select_content3_code = explode("|", $this->metadata['select_content_code']);	// 選択肢コード値	EddyK
	    	$this->correct3 = explode("|", $this->metadata['correct']);							// 正解				EddyK
	    	$this->options_len = $this->options3_len;
    	}

		$result = $this->mdbView->getGroup($this->testee_id);
		if ($result === false) {
			return 'error';
		}
		if(count($result) > 0){
			$groups = explode("|", $result[0]["select_content"]);
			$group_codes = explode("|", $result[0]["select_content_code"]);
			for($i = 0; count($groups) > $i; $i++){
				$this->group[$i]["name"] = $groups[$i];
				$this->group[$i]["code"] = $group_codes[$i];
			}

			$this->group_exist = true;
		}
		// 登録されているコンテンツ数を取得
    	$params = array(
			"testee_id" => intval($this->testee_id)
		);
		$this->contents_count = $this->db->countExecute("testee_content", $params);
        if ($this->contents_count === false) {
        	return 'error';
        }
		// アラート表示の設定
		// グループタイプで既に登録データがある場合、更新前にアラートを出力する
    	if($this->metadata['type'] == TESTEE_META_TYPE_N_GROUP && $this->contents_count > 0) {
			$this->alert_on = _ON;
        }

		// メタデータがチェック付日付けの場合に必要になるため、取得しておく。
		$this->mail_users = $this->mdbView->getMetadataMailUser( $this->metadata_id );

		// 登録時メールの設定取得のために、testee テーブル取得
		$this->testee_obj = $this->mdbView->getMdbById( $this->testee_id );

    	return 'success';
    }
}
?>
