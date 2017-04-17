<?php

/**
 * フッタ設定のデータベース更新共通クラス
 *
 * @package     jp.opensource-workshop
 * @author      nagahara@opensource-workshop.jp
 * @copyright   2012 opensource-workshop.jp
 * @license     Opensource-workshop Commercial License
 * @project     Opensource-workshop add-on module project
 * @access      public
 */
class Footeredit_Components_Action
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
	function Footeredit_Components_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * フッター設定
	 *
	 * @access    public
	 */
	function setFooter( $layout, $edit_type, $address, $tel, $fax, $mail, $link_html, $copyright,
	                    $embed_html,
	                    $chk_logo, $chk_address, $chk_tel, $chk_fax,
	                    $chk_mail, $chk_link, $chk_nc ) 
	{
		// フッター管理用テーブルに保存

		// -----------------------------------------------------------
		// - フッター管理用テーブル(footeredit)
		// -----------------------------------------------------------

		// フッター管理用テーブル(footeredit)にデータがあるか確認
		$sql = "SELECT * "
		    .  "FROM {footeredit}";
		$footer_setting = $this->_db->execute( $sql );

		if ( $footer_setting === false ) {
			$this->_db->addError();
			return $footer_setting;
		}

		// チェックボックスは選択されていない場合は空で来るので、0に変換
		$chk_address = ( empty( $chk_address ) ? 0 : 1 );
		$chk_tel     = ( empty( $chk_tel )     ? 0 : 1 );
		$chk_fax     = ( empty( $chk_fax )     ? 0 : 1 );
		$chk_mail    = ( empty( $chk_mail )    ? 0 : 1 );
		$chk_link    = ( empty( $chk_link )    ? 0 : 1 );
		$chk_nc      = ( empty( $chk_nc )      ? 0 : 1 );

		// データSQL編集
		$params = array(
		    "room_id"       => 0,
		    "layout"        => $layout,
		    "edit_type"     => $edit_type,
		    "address"       => $address,
		    "tel"           => $tel,
		    "fax"           => $fax,
		    "mail"          => $mail,
		    "link_html"     => $link_html,
		    "copyright"     => $copyright,
		    "embed_html"    => $embed_html,
		    "chk_address"   => $chk_address,
		    "chk_tel"       => $chk_tel,
		    "chk_fax"       => $chk_fax,
		    "chk_mail"      => $chk_mail,
		    "chk_link"      => $chk_link,
		    "chk_nc"        => $chk_nc,
		);

		// フッター管理用テーブル(footeredit)にデータがある場合
		if ( count( $footer_setting ) > 0 ) {

			// --- 更新処理
			$where_params = array();
			$result = $this->_db->updateExecute( "footeredit", $params, $where_params, true );
			if($result === false) {
				return "false";
			}

		}
		// フッター管理用テーブル(footeredit)にデータがない場合は追加
		else {

			$result = $this->_db->insertExecute( 'footeredit', $params, true );
			if($result === false) {
				return "false";
			}
		}

		// -----------------------------------------------------------
		// - フッター編集
		// -----------------------------------------------------------

		$footer_str  = '';

		// HTML埋め込み
		if ( $edit_type == 1 ) {

			$footer_str .= $embed_html;
		}
		// 簡易編集
		else {

			$footer_str .= '<hr class="footer_hr">' . "\n";

			$footer_str .= '<div id="footer_area">' . "\n";
				$footer_str .= '<div id="footer_link_box">' . "\n";
				if ( $link_html ) {
					$footer_str .= '            ' . $link_html . "\n";
				}
				$footer_str .= '</div>' . "\n";

				$footer_str .= '<hr id="footer_hr_smp">' . "\n";

				$footer_str .= '<div id="footer_main_box">' . "\n";
				if ( $address ) {
					$footer_str .= '        ' . $address . '<br />' . "\n";
				}
				if ( $tel ) {
					$footer_str .= '        ' . $tel . '<br />' . "\n";
				}
				if ( $fax ) {
					$footer_str .= '        ' . $fax . '<br />' . "\n";
				}
				if ( $mail ) {
					$footer_str .= '        ' . $mail . '<br />' . "\n";
				}
				$footer_str .= '</div>' . "\n";

				if ( $copyright ) {
					$footer_str .= '<table class="footer_copyright">' . "\n";
					$footer_str .= '<tbody>' . "\n";
					$footer_str .= '    <tr>' . "\n";
					$footer_str .= '        <td class="copyright_center">' . "\n";
					$footer_str .= '        ' . $copyright . "\n";
					$footer_str .= '        </td>' . "\n";
					$footer_str .= '    </tr>' . "\n";
					$footer_str .= '</tbody>' . "\n";
					$footer_str .= '</table>';
				}
			$footer_str .= '</div>' . "\n";
		}

		// Powered by NetCommons2
		if ( $chk_nc ) {
			$footer_str .= '<table class="footer_copyright">' . "\n";
			$footer_str .= '<tbody>' . "\n";
			$footer_str .= '    <tr>' . "\n";
			$footer_str .= '        <td class="copyright_body">' . "\n";
			$footer_str .= '        Powered by NetCommons2 <a class="link" href="http://www.netcommons.org/" target="_blank">The NetCommons Project</a>' . "\n";
			//$footer_str .= '        Powered by NetCommons2 The NetCommons Project' . "\n";
			$footer_str .= '        </td>' . "\n";
			$footer_str .= '    </tr>' . "\n";
			$footer_str .= '</tbody>' . "\n";
			$footer_str .= '</table>';
		}

		// -----------------------------------------------------------
		// - congigテーブル(config)
		// -----------------------------------------------------------

		// congigテーブル(config)にデータがあるか確認
		$sql = "SELECT * "
		    .  "FROM {config} "
		    .  "WHERE conf_name = 'meta_footer'";
		$footer_config = $this->_db->execute( $sql );

		if ( $footer_config === false ) {
			$this->_db->addError();
			return $footer_config;
		}

		// congigテーブル(config)にデータがある場合
		if ( count( $footer_config ) > 0 ) {

			// データSQL編集
			$params = array(
				"conf_value" => $footer_str,
			);

			// --- 更新処理
			$where_params = array( 'conf_name' => 'meta_footer' );
			$result = $this->_db->updateExecute( "config", $params, $where_params, true );
			if($result === false) {
				return "false";
			}

		}
		// congigテーブル(config)にデータがない場合は追加
		else {

			// データSQL編集
			$params = array(
			    "conf_modid" => 0,
			    "conf_catid" => 3,
			    "conf_name"  => 'meta_footer',
			    "conf_title" => '',
			    "conf_value" => $footer_str,
			);
			$this->_db->insertExecute( 'config', $params );
		}

	}

	/**
	 * フッター初期値設定
	 *
	 * @access    public
	 */
	function setDefaultFooter()
	{

		// -----------------------------------------------------------
		// - congigテーブル(config)
		// -----------------------------------------------------------

		$footer_str = '<div id="footer_default">Powered by NetCommons2 <a target="_blank" class="link" href="http://www.netcommons.org/">The NetCommons Project</a></div>';

		// congigテーブル(config)にデータがあるか確認
		$sql = "SELECT * "
		    .  "FROM {config} "
		    .  "WHERE conf_name = 'meta_footer'";
		$footer_config = $this->_db->execute( $sql );

		if ( $footer_config === false ) {
			$this->_db->addError();
			return $footer_config;
		}

		// congigテーブル(config)にデータがある場合
		if ( count( $footer_config ) > 0 ) {

			// データSQL編集
			$params = array(
				"conf_value" => $footer_str,
			);

			// --- 更新処理
			$where_params = array( 'conf_name' => 'meta_footer' );
			$result = $this->_db->updateExecute( "config", $params, $where_params, true );
			if($result === false) {
				return "false";
			}

		}
		// congigテーブル(config)にデータがない場合は追加
		else {

			// データSQL編集
			$params = array(
			    "conf_modid" => 0,
			    "conf_catid" => 3,
			    "conf_name"  => 'meta_footer',
			    "conf_title" => '',
			    "conf_value" => $footer_str,
			);
			$this->_db->insertExecute( 'config', $params );
		}

	}
}
?>
