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
class Testee_Action_Main_Vote extends Action
{
    // リクエストパラメータを受け取るため
    var $content_id = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $session = null;
 
    // 値をセットするため
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
    		"content_id" => intval($this->content_id)
    	);
    	$content = $this->db->selectExecute("testee_content", $params);
    	if($content === false || !isset($content[0])) {
			return "error";
		}
		
		$user_id = $this->session->getParameter("_user_id");
		if (empty($user_id)) {
			$votes = $this->session->getParameter("testee_votes");
			$votes[] = $this->content_id;
			$this->session->setParameter("testee_votes", $votes);
			$user_id = -1;
		}
		
    	$vote = $content[0]['vote'];
    	if (!empty($vote)) {
			$vote_update = $vote.",".$user_id;
		}else{
			$vote_update = $user_id;
		}

		$vote_count = $this->db->maxExecute("testee_content", "vote_count", $params);
    	
		$update_params = array(
			"vote" => $vote_update,
			"vote_count" => $vote_count + 1
		);
		$result = $this->db->updateExecute("testee_content", $update_params,  $params);
		if($result === false) {
			return 'error';
		}

        return 'success';
    }
}
?>
