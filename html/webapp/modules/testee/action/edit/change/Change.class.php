<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Action_Edit_Change extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $testee_id = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    function execute()
    {
    	$params = array(
			"block_id"=>intval($this->block_id)
		);

    	$block_obj = $this->db->selectExecute("testee_block", $params);
    	if($block_obj === false) {
    		return 'error';
    	}

    	if(isset($block_obj[0])) {
    		$update_params = array(
    			"testee_id"=>intval($this->testee_id)
    		);
	    	$result = $this->db->updateExecute("testee_block", $update_params, $params, true);
	    	if($result === false) {
	    		return 'error';
	    	}
    	} else {
    		$params = array(
    			"block_id" => $this->block_id,
				"testee_id" => $this->testee_id
			);
	    	$result = $this->db->insertExecute("testee_block", $params, true);
	    	if ($result === false) {
    			return 'error';
    		}
    	}
    	return 'success';
    }
}
?>
