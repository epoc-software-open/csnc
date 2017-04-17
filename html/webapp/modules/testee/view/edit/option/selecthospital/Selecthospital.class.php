<?php

/**
 * 項目設定-項目追加-ルームに参加しているユーザの施設を取り込む
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Edit_Option_Selecthospital extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;

	// 使用コンポーネントを受け取るため
	var $mdbView = null;

	// 選択肢
	var $room_hospital = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{

		// ルームに参加しているユーザの施設を取得
		$tmp_room_hospitals = $this->mdbView->getRoomHospital( $this->room_id );

		// ルームに参加しているユーザの施設を@の後ろのコードでソートしなおす
		$this->room_hospital = array();
		foreach ( $tmp_room_hospitals as $tmp_room_hospital ) {

			$tmp_room_hospital["hospital"] = str_replace("|", "", $tmp_room_hospital["hospital"])	;

			$tmp_hospital = explode( "@", $tmp_room_hospital["hospital"] );
			if ( count( $tmp_hospital ) > 1 ) {
				$this->room_hospital[str_replace("|", "", $tmp_hospital[1])] = $tmp_room_hospital;
			}
			else {
				$this->room_hospital[] = $tmp_room_hospital;
			}
		}
		ksort( $this->room_hospital );
		//test_log($this->room_hospital);

		return 'success';
	}
}
?>
