<?php

/**
 * フッタ設定のデータベース取得共通クラス
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access    private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access    private
	 */
	var $_container = null;

	/**
	 * コンストラクター
	 *
	 * @access    public
	 */
	function Footeredit_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 設定内容を取得する。
	 *
	 * @access    public
	 */
	function getFooterData() 
	{

		$sql = "SELECT * "
		    .  "FROM {footeredit} ";

		$footeredit = $this->_db->execute( $sql );
		//print_r( $sql );

		if ( $footeredit === false ) {
			$this->_db->addError();
			return $footeredit;
		}

		if ( !empty( $footeredit ) ) {
			return $footeredit[0];
		}

		return $footeredit;
	}
}
?>
