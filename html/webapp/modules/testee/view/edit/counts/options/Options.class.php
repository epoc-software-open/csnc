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
class Testee_View_Edit_Counts_Options extends Action
{
    // リクエストパラメータを受け取るため
	var $testee_id = null;
	var $counts_id = null;

    // バリデートによりセット
	var $mdb_obj = null;

	// コンポーネントを受け取るため
	var $mdbView = null;

	// 値をセットするため
	var $options = null;				// 項目の選択値

    /**
     * 汎用データベース編集画面表示
     *
     * @access  public
     */
    function execute()
    {
		if($this->counts_id){
			// 項目の選択値を取得
			$result = $this->mdbView->getOptionCounts2($this->counts_id);
			if ($result === false) {
				return 'error';
			}
			$i = 0;
			foreach($result as $key => $option){
				$this->options[$i]["name"] = $key;
				$this->options[$i]["code"] = $option["code"];
				$this->options[$i]["count"] = $option["count"];
				$i++;
			}
		}

    	return 'success';
    }
}
?>
