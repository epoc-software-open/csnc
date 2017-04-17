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
class Testee_Action_Edit_Uploadcsv extends Action
{
    // リクエストパラメータを受け取るため
    var $testee_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	var $session = null;

	// 値をセットするため


    /**
     * csvインポート
     *
     * @access  public
     */
    function execute()
    {

    	$row_data = $this->session->getParameter(array("testee_csv_data", $this->testee_id));
    	$this->session->removeParameter(array("testee_csv_data", $this->testee_id));

    	$metadatas = $this->mdbView->getMetadatas(array("testee_id" => intval($this->testee_id)));
		if($metadatas === false) {
    		return 'error';
    	}

    	$insert_params = array(
			"testee_id" => $this->testee_id,
			"temporary_flag" => _OFF
		);

		$display_sequence = $this->db->maxExecute("testee_content", "display_sequence", array("testee_id" => $this->testee_id));
    	foreach($row_data as $row) {
    		$display_sequence++;
    		$content_id = $this->db->insertExecute("testee_content", array_merge($insert_params, array("display_sequence" => $display_sequence)), true, "content_id");
    		if ($content_id === false) {
				return 'error';
			}
			$count = 0;
			foreach(array_keys($metadatas) as $i) {
				if ($metadatas[$i]['type'] == TESTEE_META_TYPE_FILE 
					|| $metadatas[$i]['type'] == TESTEE_META_TYPE_IMAGE
					|| $metadatas[$i]['type'] == TESTEE_META_TYPE_INSERT_TIME 
					|| $metadatas[$i]['type'] == TESTEE_META_TYPE_UPDATE_TIME) {
					$row[$count] = "";
				} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_DATE || 
						$metadatas[$i]['type'] == TESTEE_META_TYPE_N_DATE || 
						$metadatas[$i]['type'] == TESTEE_META_TYPE_N_BIRTHDAY ) {
					$row[$count] = $this->mdbView->checkDate($row[$count]);
					if (!empty($row[$count])) {
						$row[$count] = timezone_date($row[$count]."000000", true);
					}
				} elseif ($metadatas[$i]['type'] == TESTEE_META_TYPE_AUTONUM) {
					$row[$count] = $this->mdbView->getAutoNumber($metadatas[$i]['metadata_id']);
				}

		    	$params = array(
					"metadata_id" => $metadatas[$i]['metadata_id'],
					"content_id" => $content_id,
					"content" => isset($row[$count]) ? $row[$count]:""
				);
				$metadata_content_id = $this->db->insertExecute("testee_metadata_content", $params, true, "metadata_content_id");
				if ($metadata_content_id === false) {
					return 'error';
				}
				$count++;
	    	}

			//--URL短縮形関連 Start--
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->setAbbreviateUrl($this->testee_id, $content_id);
			if ($result === false) {
				return 'error';
			}
			//--URL短縮形関連 End--
    	}

    	return 'success';
    }

}
?>