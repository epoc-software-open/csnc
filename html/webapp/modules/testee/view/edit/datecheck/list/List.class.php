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
class Testee_View_Edit_Datecheck_List extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;
    
    // バリデートによりセット
	var $mdb_obj = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	
	// 値をセットするため
	var $contents_count = null;			// 登録されているコンテンツ数

	var $count = null;
	var $datechecks = null;
	
    /**
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"testee_id" => intval($this->testee_id)
		);

		$this->datechecks = $this->mdbView->getDateCheck($this->testee_id);
    	if($this->datechecks === false) {
    		return 'error';
    	}
		$this->count = count($this->datechecks);

		// 登録されているコンテンツ数を取得
		$this->contents_count = $this->db->countExecute("testee_content", $params);
        if ($this->contents_count === false) {
        	return 'error';
        }

		return 'success';
    }
}
?>
