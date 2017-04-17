<?php

/**
 * [[機能説明]]
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Hospitalinfo_Components_Action
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
	function Hospitalinfo_Components_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 施設追加
	 *
	 * @access    public
	 */
	function setHospitalinfo( $hospital_name, $hospital_kana, $hospital_code )
	{

		$params = array( 
		    "hospital_name" => trim( mb_convert_kana( $hospital_name, "s", 'UTF-8' ) ),
		    "hospital_kana" => trim( mb_convert_kana( $hospital_kana, "s", 'UTF-8' ) ),
		    "hospital_code" => trim( mb_convert_kana( $hospital_code, "s", 'UTF-8' ) )
		);

		$result = $this->_db->insertExecute( 'hospitalinfo', $params, true, "hospitalinfo_id" );
    	if($result === false) {
			$this->_db->addError();
			return $result;
    	}
		//test_log($this->_db->ErrorMsg());

		// ユーザプロフィールの施設情報の書き換え
		$this->updateItemsOptions();
	}

	/**
	 * 施設更新
	 *
	 * @access    public
	 */
	function updateHospitalinfo( $hospitalinfo_id, $hospital_name, $hospital_kana, $hospital_code )
	{

		$params = array( 
		    "hospital_name" => $hospital_name,
		    "hospital_kana" => $hospital_kana,
		    "hospital_code" => $hospital_code
		);
		$result = $this->_db->updateExecute("hospitalinfo", $params, array( "hospitalinfo_id" => $hospitalinfo_id ), true);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}

		// ユーザプロフィールの施設情報の書き換え
		$this->updateItemsOptions();
	}

	/**
	 * 施設削除
	 *
	 * @access    public
	 */
	function deleteHospitalinfo( $hospitalinfo_id )
	{

		$result = $this->_db->deleteExecute("hospitalinfo", array( "hospitalinfo_id" => $hospitalinfo_id ) );
		if($result === false) {
			$this->_db->addError();
			return $result;
		}

		// ユーザプロフィールの施設情報の書き換え
		$this->updateItemsOptions();
	}

	/**
	 * ユーザプロフィールの施設情報の書き換え
	 *
	 * @access    public
	 */
	function updateItemsOptions()
	{

		// 施設情報マスタ取得
		$sql = "SELECT hospitalinfo_id, hospital_name, hospital_kana, hospital_code "
		     . "FROM {hospitalinfo} "
		     . "ORDER BY hospital_code";

		$hospitalinfos = $this->_db->execute( $sql, $params );

		if ( $hospitalinfos === false ) {
			$this->_db->addError();
			return $hospitalinfos;
		}

		// 施設情報マスタをユーザプロフィールの形に編集
		$options = array();
		$default_selected = array();
		foreach ( $hospitalinfos as $hospitalinfo ) {
			$options[] = $hospitalinfo["hospital_name"];
			$default_selected[] = "0";
		}

		$option_text = "|" . implode( "|", $options ) . "|";
		$default_selecte_text = "0|" . implode( "|", $default_selected ) . "|";

		// ユーザプロフィール
		$where_params = array( "item_name" => "USER_ITEM_HOSPITAL" );
		$items = $this->_db->selectExecute( "items", $where_params );
		//test_log($items);

		// ユーザプロフィールの存在チェック
		if ( empty( $items ) || count( $items ) < 1 ) {
			return;
		}
		$item_id = $items[0]["item_id"];
		if ( empty( $item_id ) ) {
			return;
		}

		// ユーザプロフィールの更新
		$params = array( 
		    "options" => $option_text,
		    "default_selected" => $default_selecte_text
		);
		$result = $this->_db->updateExecute("items_options", $params, array( "item_id" => $item_id ), true);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
	}
}
?>
