<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース編集アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Modify extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;
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
	var $kentai_switch = null;
	
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * 汎用データベース編集アクション
     *
     * @access  public
     */
    function execute()
    {	
    	$params = array(
			"testee_name" => $this->testee_name,
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
		
		$result = $this->db->updateExecute("testee", $params,  array("testee_id"=>$this->testee_id), true);
		if ($result === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
}
?>
