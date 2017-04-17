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
class Hospitalinfo_Components_View
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
	function Hospitalinfo_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 施設情報データの取得
	 *
	 * @access    public
	 */
	function getHospitalinfo( $hospitalinfo_id = null, $hospital_keyword = null, $hospital_sort = null ) 
	{

		// 施設指定の場合
		$params = array();

		$sql = "SELECT hospitalinfo_id, hospital_name, hospital_kana, hospital_code "
		    .  "FROM {hospitalinfo} "
		    .  "WHERE 1=1 ";

		if ( !empty( $hospitalinfo_id ) ) {
			$sql .= "AND hospitalinfo_id = ? ";
			$params[] = $hospitalinfo_id;
		}

		if ( !empty( $hospital_keyword ) ) {
			$sql .= "AND ( hospital_name LIKE ? || hospital_kana LIKE ? || hospital_code LIKE ? ) ";
			$params[] = "%" . $hospital_keyword . "%";
			$params[] = "%" . $hospital_keyword . "%";
			$params[] = "%" . $hospital_keyword . "%";
		}

		if ( $hospital_sort == 2 ) {
			$sql .= "ORDER BY hospital_name";
		}
		else if ( $hospital_sort == 3 ) {
			$sql .= "ORDER BY hospital_kana";
		}
		else {
			$sql .= "ORDER BY hospital_code";
		}

		$hospitalinfo = $this->_db->execute( $sql, $params );
		//print_r( $sql );
		//print_r( $params );
		//print_r( $this->_db->ErrorMsg() );

		if ( $hospitalinfo === false ) {
			$this->_db->addError();
			return $hospitalinfo;
		}

		return $hospitalinfo;
	}

	/**
	 * 施設情報データの取得(コードから)
	 *
	 * @access    public
	 */
	function getHospitalinfoFromCode( $hospital_code, $hospitalinfo_id = null ) 
	{

		$params = array( $hospital_code );

		$sql = "SELECT hospitalinfo_id, hospital_name, hospital_kana, hospital_code "
		    .  "FROM {hospitalinfo} "
		    .  "WHERE hospital_code = ? ";

		if ( !empty( $hospitalinfo_id ) ) {
			$sql .= "AND hospitalinfo_id != ?";
			$params[] = $hospitalinfo_id;
		}

		$hospitalinfo = $this->_db->execute( $sql, $params );

		if ( $hospitalinfo === false ) {
			$this->_db->addError();
			return $hospitalinfo;
		}

		return $hospitalinfo;
	}

	/**
	 * 施設情報データの取得(施設名から)
	 *
	 * @access    public
	 */
	function getHospitalinfoFromName( $hospital_name, $hospitalinfo_id = null ) 
	{

		$params = array( $hospital_name );

		$sql = "SELECT hospitalinfo_id, hospital_name, hospital_kana, hospital_code "
		    .  "FROM {hospitalinfo} "
		    .  "WHERE hospital_name = ? ";

		if ( !empty( $hospitalinfo_id ) ) {
			$sql .= "AND hospitalinfo_id != ?";
			$params[] = $hospitalinfo_id;
		}

		$hospitalinfo = $this->_db->execute( $sql, $params );

		if ( $hospitalinfo === false ) {
			$this->_db->addError();
			return $hospitalinfo;
		}

		return $hospitalinfo;
	}

	/**
	 * 施設情報データの取得(施設名かな　から)
	 *
	 * @access    public
	 */
	function getHospitalinfoFromKana( $hospital_kana, $hospitalinfo_id = null ) 
	{

		$params = array( $hospital_kana );

		$sql = "SELECT hospitalinfo_id, hospital_name, hospital_kana, hospital_code "
		    .  "FROM {hospitalinfo} "
		    .  "WHERE hospital_kana = ? ";

		if ( !empty( $hospitalinfo_id ) ) {
			$sql .= "AND hospitalinfo_id != ?";
			$params[] = $hospitalinfo_id;
		}

		$hospitalinfo = $this->_db->execute( $sql, $params );

		if ( $hospitalinfo === false ) {
			$this->_db->addError();
			return $hospitalinfo;
		}

		return $hospitalinfo;
	}

	/**
	 * 削除チェック
	 *
	 * @access    public
	 */
	function getHospitalUser( $hospitalinfo_id ) 
	{

		/*
			SELECT user_id
			FROM epoc_users_items_link
			WHERE content = CONCAT( ( SELECT hospital_name FROM epoc_hospitalinfo WHERE hospitalinfo_id = 1 ), '|' )
			  AND item_id = ( SELECT item_id FROM epoc_items WHERE item_name = 'USER_ITEM_HOSPITAL' )
		*/

		$params = array( $hospitalinfo_id );

		// 削除対象の施設名がユーザプロフィールの施設名に設定されていたら、エラーにする。
		$sql = "SELECT user_id "
		    .  "FROM {users_items_link} "
		    .  "WHERE content = CONCAT( ( SELECT hospital_name FROM {hospitalinfo} WHERE hospitalinfo_id = ? ), '|' ) "
		    .    "AND item_id = ( SELECT item_id FROM {items} WHERE item_name = 'USER_ITEM_HOSPITAL' )";

		$hospitalinfo = $this->_db->execute( $sql, $params );

		if ( $hospitalinfo === false ) {
			$this->_db->addError();
			return $hospitalinfo;
		}

		return $hospitalinfo;
	}
}
?>
