<?php

/**
 * ダウンロードログのデータ取得関数群
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2015 opensource-workshop.jp
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     Opensource-workshop NetCommons2 add-on module project
 * @access      public
 */
class Downloadlog_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Downloadlog_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * ダウンロードログの月分一覧を取得
	 */
	function getYm() {

		$sql = "SELECT download_ym_local ".
				"FROM {downloadlog} " .
				"GROUP BY download_ym_local ORDER BY download_ym_local DESC";
		$result = $this->_db->execute( $sql );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$log_ym_list = array();

		// 存在月分を編集
		for ( $i = 0; $i < count($result); $i++ ) {
			$log_ym_list[] = array(
				"ym_value" => $result[$i]["download_ym_local"] ,
				"ym_name"  => substr( $result[$i]["download_ym_local"], 0, 4 ) . '年' . 
				              substr( $result[$i]["download_ym_local"], 4, 2 ) . '月'
			);
		}
		return $log_ym_list;
	}

	/**
	 * ダウンロードログの表示対象数を取得
	 */
	function getLogCount( $ym_select ) {

		$sql = "SELECT count(*) logs_count ".
		       "FROM {downloadlog} ";
		$params = array();
		if ( !empty( $ym_select ) ) {
			$sql .= "WHERE download_ym_local = ? ";
			$params[] = $ym_select;
		}

		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result[0]['logs_count'];
	}

	/**
	 * ダウンロードログ一覧を取得する
	 */
	function getLogList( $ym_select, $disp_cnt = null, $begin = null ) {
		$sql = "SELECT dl.upload_id, dl.file_name, dl.module_name, dl.room_name, dl.insert_time, dl.download_user_name, u.upload_id AS uploads_upload_id ".
				"FROM {downloadlog} dl ".
				  "LEFT JOIN {uploads} u ON u.upload_id = dl.upload_id ";
		$params = array();
		if ( !empty( $ym_select ) ) {
			$sql .= " WHERE download_ym_local = ? ";
			$params[] = $ym_select;
		}
		$sql .= " ORDER BY insert_time DESC ";

		if ( empty( $disp_cnt ) ) {
			$result = $this->_db->execute( $sql, $params );
		}
		else {
			$result = $this->_db->execute( $sql, $params, $disp_cnt, $begin, true );
		}
		if ( $result === false ) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return false;
		}

		return $result;
	}

	/**
	 * ページに関する設定を行います
	 *
	 * @param int data_cnt すべての件数
	 * @param int disp_cnt 1ページ当り表示件数
	 * @param int now_page 現ページ
	 */
	function setPageInfo( &$pager, $data_cnt, $disp_cnt, $now_page = NULL )
	{
		$pager['data_cnt']    = 0;
		$pager['total_page']  = 0;
		$pager['next_link']   = FALSE;
		$pager['prev_link']   = FALSE;
		$pager['disp_begin']  = 0;
		$pager['disp_end']    = 0;
		$pager['link_array']  = NULL;

		if(empty($disp_cnt)) {
			return false;
		}

		$pager['data_cnt'] = $data_cnt;
		// now page
		$pager['now_page'] = (NULL == $now_page) ? 1 : $now_page;
		// total page
		$pager['total_page'] = ceil($pager['data_cnt'] / $disp_cnt);
		if($pager['total_page'] < $pager['now_page']) {
			$pager['now_page'] = 1;
		}
		// link array {{
		if(($pager['now_page'] - DOWNLOADLOG_FRONT_AND_BEHIND_LINK_CNT) > 0){
		    $start = $pager['now_page'] - DOWNLOADLOG_FRONT_AND_BEHIND_LINK_CNT;
		}else{
		    $start = 1;
		}
		if(($pager['now_page'] + DOWNLOADLOG_FRONT_AND_BEHIND_LINK_CNT) >= $pager['total_page']){
		    $end = $pager['total_page'];
		}else{
		    $end = $pager['now_page'] + DOWNLOADLOG_FRONT_AND_BEHIND_LINK_CNT;
		}
		$i = 0;
		for($i = $start; $i <= $end; $i++){
		    $pager['link_array'][] = $i;
		}
		// next link
		if($disp_cnt < $pager['data_cnt']){
		    if($pager['now_page'] < $pager['total_page']){
		        $pager['next_link'] = TRUE;
		    }
		}
		// prev link
		if(1 < $pager['now_page']){
		    $pager['prev_link'] = TRUE;
		}
		// begin disp number
		$pager['disp_begin'] = ($pager['now_page'] - 1) * $disp_cnt;
		// end disp number
		$tmp_cnt = $pager['now_page'] * $disp_cnt;
		$pager['disp_end'] = ($pager['data_cnt'] < $tmp_cnt) ? $pager['data_cnt'] : $tmp_cnt;
	}

	/**
	 * ダウンロードログの取得
	 */
	function getDownloadLog( $ym_select ) {

		$sql = "SELECT dl.upload_id, dl.file_name, dl.module_name, dl.room_name, dl.insert_time, dl.download_user_name, u.upload_id AS uploads_upload_id ".
				"FROM {downloadlog} dl ".
				  "LEFT JOIN {uploads} u ON u.upload_id = dl.upload_id ";
		
		$params = array();
		if ( !empty( $ym_select ) ) {
			$sql .= " WHERE download_ym_local = ? ";
			$params[] = $ym_select;
		}
		$sql .= " ORDER BY insert_time DESC ";

		$download_log = $this->_db->execute( $sql, $params );
		return $download_log;
	}
}
?>
