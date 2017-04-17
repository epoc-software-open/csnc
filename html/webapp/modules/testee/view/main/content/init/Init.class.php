<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
class Testee_View_Main_Content_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
    var $testee_id = null;
    var $content_id = null;

    // 使用コンポーネントを受け取るため
    var $mdbView = null;
    var $session = null;
 
    // 値をセットするため
    var $metadatas_layout = null;
    //var $session_params = null;

	var $content_insert_time = null;		// データ登録日 表示、生年月日計算用 EddyK
	var $content_insert_user_name = null;	// データ登録ユーザ名。表示用 EddyK
	var $hospital_name_default = null;		// 病院名の初期表示用。初期登録時のみ EddyK

	var $comments = null;				// コメント EddyK
	var $counts_check = null;			// 件数設定による確認結果 EddyK

	var $counts_data = null;			// 既存の件数設定情報

	var $default_birthday = null;		// 今日の50年前

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {

		// データ登録日に初期値として現在を設定	EddyK
		$this->content_insert_time = timezone_date();
		// コンテンツの登録可否を判断するリストの取得 EddyK
		$this->counts_check = $this->mdbView->getOptionCounts($this->testee_id);

    	$params = array(
    		"testee_id" => intval($this->testee_id)
    	);
    	$this->metadatas_layout = $this->mdbView->getLayout($params);
    	if($this->metadatas_layout === false) {
    		return 'error';
    	}
    	
    	$this->session->removeParameter(array("testee_content", $this->block_id));
    	
		if(!empty($this->content_id)) {
	    	$metadatas = $this->mdbView->getMetadatas($params);
	    	if($metadatas === false) {
	    		return 'error';
	    	}
    		$detail = $this->mdbView->getMdbEditData($this->content_id, $metadatas);
	    	if($detail === false) {
	    		return 'error';
	    	}
	    	$this->session->setParameter(array("testee_content", $this->block_id), $detail['value']);

			// データ登録日と登録ユーザを取得。データ登録日は生年月日から年齢算出で使用する。EddyK
			$content = $this->mdbView->getMDBContent($this->content_id);
			$this->content_insert_time = $content[0]['insert_time'];
			$this->content_insert_user_name = $content[0]['insert_user_name'];

    	} else {
			// 初期表示用の施設名取得　EddyK
			$content = $this->mdbView->getHospital($this->session->getParameter("_user_id"));
			if($content === false) {
				return 'error';
			}
			$this->hospital_name_default = str_replace("|", "", $content);

			$this->default_birthday = date('Y/m/d', strtotime("-50 year"));

		}

	// コメントの取得　EddyK
		$this->comments = $this->mdbView->getCommentList($this->testee_id);
		if($this->comments === false) {
			return 'error';
		}
	// 件数設定の既設定の取得	EddyK
		$this->counts_data = $this->mdbView->getCounts($this->testee_id);
    	if ($this->counts_data === false) {
    		return 'error';
    	}

		return 'success';
    }
}
?>
