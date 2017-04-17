<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
class Crfif_Components_View
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
	 * グループ設定されているmetadata
	 */
	var $group_metadata = null;

	/**
	 * グループ設定されているmetadataの選択肢
	 */
	var $group_metadata_array = null;

	/**
	 * testee モジュールOnly の設定
	 */
	var $_testee_only = true;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Crfif_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 符号編集
	 * @access	public
	 */
	function getFugo( $fugo_no ) {

		/* 符号
			11：≧
			12：＞
			21：≦
			22：＜
			31：＝
			32：≠
		*/

		$fugo_str = "";

		switch ( $fugo_no ) {
		case 11:
			$fugo_str .= ">=";
			break;
		case 11:
			$fugo_str .= ">";
			break;
		case 21:
			$fugo_str .= "<=";
			break;
		case 22:
			$fugo_str .= "<";
			break;
		}

		return $fugo_str;
	}

	/**
	 * エラー、ワーニングのフラグ変換
	 * @access	public
	 */
	function getEW( $cond_ew ) {

		/* フラグ
			1：e
			2：w
			 ： (空は空)
		*/

		$ew_str = "";

		switch ( $cond_ew ) {
		case 1:
			$ew_str .= "e";
			break;
		case 2:
			$ew_str .= "w";
			break;
		default:
			$ew_str .= "";
			break;
		}

		return $ew_str;
	}

	/**
	 * TEDC権限編集
	 *
	 * @param	tedc_org	NC保持ののTEDC権限
	 * @return	string	数時2桁のTEDC権限 or 空文字
	 * @access	public
	 */
	function edit_ret_tedc( $tedc_org ) {

		if ( strlen( trim( $tedc_org ) ) == 0 ) {
			return '';
		}

		return substr( rtrim( $tedc_org, '|' ), -2 );
	}

	/**
	 * TEDC権限取得
	 *
	 * @return	string	TEDC権限取得(2桁の数字 or 空文字)
	 * @access	public
	 */
	function get_tedc_item_id() {

		$params = array( 'USER_ITEM_TEDC' );

		$sql = "SELECT i.item_id ".
				"FROM {items} i ".
				"WHERE i.item_name = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if ( $result ) {
			return $result[0]['item_id'];
		}
		return null;
	}

	/**
	 * 施設情報ID取得
	 *
     * @return	string	施設情報ID
	 * @access	public
	 */
	function get_belong_item_id() {

		$params = array( 'USER_ITEM_HOSPITAL' );

		$sql = "SELECT i.item_id ".
				"FROM {items} i ".
				"WHERE i.item_name = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if ( $result ) {
			return $result[0]['item_id'];
		}
		return null;
	}

	/**
	 * 施設情報取得
	 *
	 * @param	string	user_id	NCユーザID
     * @return	string	施設情報
	 * @access	public
	 */
	function get_belong_item( $user_id ) {

		$params = array( $user_id, 'USER_ITEM_HOSPITAL' );

		$sql = "SELECT i.item_id, l.content ".
				"FROM {items} i, {users_items_link} l ".
				"WHERE i.item_id = l.item_id ".
				 " AND l.user_id = ? ".
				 " AND i.item_name = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if ( $result ) {
			return rtrim( $result[0]['content'], '|' );
		}
		return null;
	}

	/**
	 * メールアドレスID取得
	 *
     * @return	string	メールアドレスID
	 * @access	public
	 */
	function get_user_email_id() {

		$params = array( 'USER_ITEM_EMAIL' );

		$sql = "SELECT i.item_id ".
				"FROM {items} i ".
				"WHERE i.item_name = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if ( $result ) {
			return $result[0]['item_id'];
		}
		return null;
	}

	/**
	 * メールアドレス取得
	 *
	 * @param	string	user_id	NCユーザID
     * @return	string	メールアドレス
	 * @access	public
	 */
	function get_user_email( $user_id ) {

		$params = array( $user_id, 'USER_ITEM_EMAIL' );

		$sql = "SELECT i.item_id, l.content ".
				"FROM {items} i, {users_items_link} l ".
				"WHERE i.item_id = l.item_id ".
				 " AND l.user_id = ? ".
				 " AND i.item_name = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		if ( $result ) {
			return rtrim( $result[0]['content'], '|' );
		}
		return null;
	}

	/**
	 * type を編集
	 *
	 * @access	public
	 */
	function edit_type( $type ) {

		$ret_type = $type;

		// 施設(20), 性別(22), グループ(28), 選択式(ラジオ(30), はい・いいえ(34), 該当せず・該当(35))
		if ( $type == 20 || $type == 22 || $type == 28 || $type == 30 || $type == 34 || $type == 35 ) {
			$ret_type = 4;
		}

		// 生年月日(21), 数値(31), チェック付日付(32), コメント(33), 身長(23), 体重(24), 血清クレアチニン値(25), BSA(26), Ccr(27)
		if ( $type == 21 || $type == 31 || $type == 32 || $type == 33 || $type == 23 || $type == 24 || $type == 25 || $type == 26 || $type == 27 ) {
			$ret_type = 1;
		}

		return $ret_type;
	}

	/**
	 * metadata_name, metadata_select_content を編集
	 *
	 * @access	public
	 */
	function edit_metadata( $record ) {
//test_log($record);
		// タイトル
		$tmp_name = $record['metadata_name'];

		// 選択肢
		$tmp_select_content = $record['select_content'];

		// 選択肢の配列の再現
		$select_content_code = explode( "|", $record["select_content_code"] );
		$select_content = explode( "|", $record["select_content"] );

		// 選択肢の再現は、臨床試験支援システムで特有のコードがある場合のみ
		$select_content_array = array();
		if ( is_array( $select_content_code ) && is_array( $select_content ) && count( $select_content_code ) == count( $select_content ) ) {
			$select_content_array = array_combine( $select_content_code, $select_content );
		}

		// グループ
		if ( $record["type"] == 28 ) {
			$tmp_name .= "@group";

			$tmp_select_content = "";
			foreach( $select_content_array as $select_content_key => $select_content_str ) {
				$tmp_select_content .= $select_content_str . "@" . $select_content_key . "|";
			}
			$tmp_select_content = rtrim( $tmp_select_content, "|" );
		}

		// 選択式(選択肢(択一), ラジオ, はい・いいえ, 該当せず・該当)
		if ( $record["type"] == 4 || $record["type"] == 30 || $record["type"] == 34 || $record["type"] == 35 ) {

			// 選択肢(択一)でコード値がない場合は、特別なタイプとみなさない。
			if ( $record["type"] == 4 && ( str_replace( "|", "", $record["select_content_code"] ) == "" ) ) {
				$tmp_name .= "";
			}
			else {
				$tmp_name .= "@radio@";
			}

			// グループ設定があるか
			if ( !empty( $record["group_option"] ) ) {
				$tmp_name .= $this->group_metadata_array[$record["group_option"]];
			}

			// 正解の選択肢の配列の再現
			$ok_index = -1;
			$correct_explode = explode( "|", $record["correct"] );
			foreach ( $correct_explode as $correct_index => $correct_item ) {
				if ( $correct_item == "1" ) {
					$ok_index = $correct_index;
					break;
				}
			}

			// 正解の選択肢
			// 選択肢(択一)でコード値がない場合は、特別なタイプとみなさない。
			if ( $record["type"] == 4 && ( str_replace( "|", "", $record["select_content_code"] ) == "" ) ) {
				$tmp_name .= "";
			}
			else {
				$tmp_name .= "@";
			}

			$correct_code_explode = explode( "|", $record["select_content_code"] );
			if ( $ok_index >= 0) {
				$tmp_name .= $correct_code_explode[$ok_index];
			}

			// 選択肢の配列の再現
			$select_content_code = explode( "|", $record["select_content_code"] );
			$select_content = explode( "|", $record["select_content"] );
			$select_content_array = array_combine( $select_content_code, $select_content );

			$tmp_select_content = "";
			foreach( $select_content_array as $select_content_key => $select_content_str ) {
				$tmp_select_content .= $select_content_str . "@" . $select_content_key . "|";
			}
			$tmp_select_content = rtrim( $tmp_select_content, "|" );
		}

		// 数値, チェック付日付
		if ( $record["type"] == 31 || $record["type"] == 32 ) {

			// 数値
			if ( $record["type"] == 31 ) {
				$tmp_name .= "@num";
			}
			// チェック付日付
			else if ( $record["type"] == 32 ) {
				$tmp_name .= "@date";
			}

			// グループ設定があるか
			$tmp_name .= "@";
			if ( !empty( $record["group_option"] ) ) {
				$tmp_name .= $this->group_metadata_array[$record["group_option"]];
			}

			// 条件式1
			$tmp_name .= "@";
			$tmp_name .= $this->getEW( $record["cond1_ew"] );
			$tmp_name .= "@". $this->getFugo( $record["cond1_fugo1"] );
			$tmp_name .= $record["cond1_value1"];
			$tmp_name .= "@". $this->getFugo( $record["cond1_fugo2"] );
			$tmp_name .= $record["cond1_value2"];

			// 条件式2
			$tmp_name .= "@";
			$tmp_name .= $this->getEW( $record["cond2_ew"] );
			$tmp_name .= "@". $this->getFugo( $record["cond2_fugo1"] );
			$tmp_name .= $record["cond2_value1"];
			$tmp_name .= "@". $this->getFugo( $record["cond2_fugo2"] );
			$tmp_name .= $record["cond2_value2"];

			$tmp_name = rtrim( $tmp_name, '@' );
		}

		// 生年月日, 身長, 体重, 血清クレアチニン値, BSA, Ccr
		if ( $record["type"] == 21 || $record["type"] == 23 || $record["type"] == 24 || $record["type"] == 25 || $record["type"] == 26 || $record["type"] == 27 ) {

			// 生年月日
			if ( $record["type"] == 21 ) {
				$tmp_name .= "@birth";
			}
			// 身長
			else if ( $record["type"] == 23 ) {
				//$tmp_name .= "@stature";
				$tmp_name .= "@num";
			}
			// 体重
			else if ( $record["type"] == 24 ) {
				//$tmp_name .= "@weight";
				$tmp_name .= "@num";
			}
			// 血清クレアチニン値
			else if ( $record["type"] == 25 ) {
				//$tmp_name .= "@creatinine";
				$tmp_name .= "@num";
			}
			// BSA
			else if ( $record["type"] == 26 ) {
				//$tmp_name .= "@bsa";
				$tmp_name .= "@num";
			}
			// Ccr
			else if ( $record["type"] == 27 ) {
				//$tmp_name .= "@ccr";
				$tmp_name .= "@num";
			}

			// 条件式1
			$tmp_name .= "@";
			$tmp_name .= $this->getEW( $record["cond1_ew"] );
			$tmp_name .= "@". $this->getFugo( $record["cond1_fugo1"] );
			$tmp_name .= $record["cond1_value1"];
			$tmp_name .= "@". $this->getFugo( $record["cond1_fugo2"] );
			$tmp_name .= $record["cond1_value2"];

			// 条件式2
			$tmp_name .= "@";
			$tmp_name .= $this->getEW( $record["cond2_ew"] );
			$tmp_name .= "@". $this->getFugo( $record["cond2_fugo1"] );
			$tmp_name .= $record["cond2_value1"];
			$tmp_name .= "@". $this->getFugo( $record["cond2_fugo2"] );
			$tmp_name .= $record["cond2_value2"];

			$tmp_name = rtrim( $tmp_name, '@' );
		}

		// 施設
		if ( $record["type"] == 20 ) {

			$tmp_name .= "@hospital";

			// 選択肢の配列の再現
			$select_content_code = explode( "|", $record["select_content_code"] );
			$select_content = explode( "|", $record["select_content"] );
			$select_content_array = array_combine( $select_content_code, $select_content );

			$tmp_select_content = "";
			foreach( $select_content_array as $select_content_key => $select_content_str ) {

				$last_at_pos = strrpos( $select_content_str, "@" );
				if ( $last_at_pos !== false ) {
					$select_content_str = substr( $select_content_str, 0, $last_at_pos );
				}

				$tmp_select_content .= $select_content_str . "@" . $select_content_key . "|";
			}
			$tmp_select_content = rtrim( $tmp_select_content, "|" );
		}

		// コメント
		if ( $record["type"] == 33 ) {

			$tmp_name = $record["comment"] . "@comment";
		}

		// 性別
		if ( $record["type"] == 22 ) {

			$tmp_name .= "@sex";

			// 選択肢の配列の再現
			$select_content_code = explode( "|", $record["select_content_code"] );
			$select_content = explode( "|", $record["select_content"] );
			$select_content_array = array_combine( $select_content_code, $select_content );

			$tmp_select_content = "";
			foreach( $select_content_array as $select_content_key => $select_content_str ) {
				$tmp_select_content .= $select_content_str . "@" . $select_content_key . "|";
			}
			$tmp_select_content = rtrim( $tmp_select_content, "|" );
		}

		// HTML特殊文字の処理
		$tmp_name = str_replace( '>', '&gt;', $tmp_name );
		$tmp_name = str_replace( '<', '&lt;', $tmp_name );

		// 戻り値の編集
		$return = array( $tmp_name, $tmp_select_content );
		return $return;
	}

	/**
	 * metadata_content を編集
	 *
	 * @access	public
	 */
	function edit_metadata_content( $record ) {

		// 入力内容(metadata_content)
		$tmp_metadata_content = $record['content'];

		// 選択肢
		$tmp_select_content = $record['select_content'];

		// 選択肢の配列の再現
		$select_content_code = explode( "|", $record["select_content_code"] );
		$select_content = explode( "|", $record["select_content"] );

		// 選択肢の再現は、臨床試験支援システムで特有のコードがある場合のみ
		$select_content_array = array();
		if ( is_array( $select_content_code ) && is_array( $select_content ) && count( $select_content_code ) == count( $select_content ) ) {
			$select_content_array = array_combine( $select_content, $select_content_code );
		}

		// グループ
		if ( $record["type"] == 28 ) {

			// 値が選択肢にあるか確認
			if ( array_key_exists( $tmp_metadata_content, $select_content_array ) ) {
				// 値が選択肢にある場合、@コード値 を付加する。
				$tmp_metadata_content .= "@" . $select_content_array[ $tmp_metadata_content ];
			}
		}

		// 施設, 選択式(選択式(択一), ラジオ, はい・いいえ, 該当せず・該当)
		if ( $record["type"] == 4 || $record["type"] == 20 || $record["type"] == 30 || $record["type"] == 34 || $record["type"] == 35 ) {

			// 値が選択肢にあるか確認 (選択式(択一)がコードなしも考えられるので、コードの確認も追加した 2016-06-06 )
			if ( array_key_exists( $tmp_metadata_content, $select_content_array ) && $select_content_array[$tmp_metadata_content] ) {
				// 値が選択肢にある場合、@コード値 を付加する。
				$tmp_metadata_content .= "@" . $select_content_array[ $tmp_metadata_content ];
			}
		}

		// チェック付日付, 生年月日
		if ( $record["type"] == 32 || $record["type"] == 21 ) {

			if ( !empty( $tmp_metadata_content ) ) {
				$tmp_metadata_content = timezone_date( $tmp_metadata_content, false, "Y-m-d" );
			}
		}

		// 性別
		if ( $record["type"] == 22 ) {

			// 値が選択肢にあるか確認
			if ( array_key_exists( $tmp_metadata_content, $select_content_array ) ) {
				// 値が選択肢にある場合、@コード値 を付加する。
				$tmp_metadata_content .= "@" . $select_content_array[ $tmp_metadata_content ];
			}
		}

		// HTML特殊文字の処理
		$tmp_metadata_content = str_replace( '>', '&gt;', $tmp_metadata_content );
		$tmp_metadata_content = str_replace( '<', '&lt;', $tmp_metadata_content );

		// 戻り値
		return $tmp_metadata_content;
	}

	/**
	 * 認証チェック
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
     * @return	string	結果XML
	 * @access	public
	 */
	function auth_nc( $loginid, $password ) {

		$params = array( $loginid );

/*
		SELECT u.user_id, u.password, u.role_authority_id, u.handle, a.content
		FROM netcommons2_users u
		  LEFT JOIN
		  (
			SELECT l.content, l.user_id
			FROM netcommons2_users_items_link l
			WHERE l.item_id = (
				SELECT item_id
				FROM netcommons2_items
				WHERE item_name = 'USER_ITEM_TEDC'
			)
		  ) a ON u.user_id = a.user_id
		WHERE u.login_id = 'admin'
*/

		$sql  = "SELECT u.user_id, u.password, u.role_authority_id, u.handle, a.content AS tedc ";
		$sql .= "FROM {users} u ";
		$sql .=   "LEFT JOIN ";
		$sql .=   "( ";
		$sql .=     "SELECT l.content, l.user_id ";
		$sql .=     "FROM {users_items_link} l ";
		$sql .=     "WHERE l.item_id = ( ";
		$sql .=         "SELECT item_id ";
		$sql .=         "FROM {items} ";
		$sql .=         "WHERE item_name = 'USER_ITEM_TEDC' ";
		$sql .=     ") ";
		$sql .=   ") a ON u.user_id = a.user_id ";
		$sql .= "WHERE login_id = ? ";

		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 0;
		$role_authority_id = '';
		$handle = '';
		$belong = '';
		$user_email = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$auth_result       = 1;

			// 権限はTEDC権限を返却するように変更
			// $role_authority_id = $result[0]['role_authority_id'];
			$role_authority_id = $this->edit_ret_tedc( $result[0]['tedc'] );
			$handle            = $result[0]['handle'];

			// 施設情報取得
			$belong = $this->get_belong_item( $result[0]['user_id'] );

			// ユーザメールアドレス
			$user_email = $this->get_user_email( $result[0]['user_id'] );
		}
		else {
			$auth_result = 0;
		}

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<auth>
  <auth_result>{$auth_result}</auth_result>
  <handle>{$handle}</handle>
  <role_authority_id>{$role_authority_id}</role_authority_id>
  <belong>{$belong}</belong>
  <email>{$user_email}</email>
</auth>
EOF;
	}


	/**
	 * 認証＆ユーザデータ連携インターフェース
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
     * @return	string	結果XML
	 * @access	public
	 */
	function get_userlist( $loginid, $password ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
		if ( ( $result[0]['role_authority_id'] == 1 ) || ( $result[0]['role_authority_id'] == 7 ) ) {
			// 1:システム管理者、7:管理者でOK
		}
		else {
			$auth_result   = 0;
			$error_message = '権限エラー';
		}

		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<userlist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</userlist>
EOF;
			exit;
		}


		// ユーザ一覧取得
		$params = array( $this->get_belong_item_id(), $this->get_user_email_id(), $this->get_tedc_item_id() );

		// $sql = "SELECT login_id, password, handle, role_authority_id ".
		// 		"FROM {users} ".
		// 		"ORDER BY insert_time ";

/*
		SELECT u.login_id, u.password, u.handle, u.role_authority_id, belong.content AS belong, email.content AS email, tedc.content AS tedc
		FROM netcommons2_users u
		  LEFT JOIN netcommons2_users_items_link belong ON u.user_id = belong.user_id AND belong.item_id = 24
		  LEFT JOIN netcommons2_users_items_link email ON u.user_id = email.user_id AND email.item_id = 5
		  LEFT JOIN netcommons2_users_items_link tedc ON u.user_id = tedc.user_id AND tedc.item_id = 25
		ORDER BY u.insert_time
*/

		$sql = "SELECT u.login_id, u.password, u.handle, u.role_authority_id, belong.content AS belong, email.content AS email, tedc.content AS tedc ".
				"FROM {users} u ".
				" LEFT JOIN {users_items_link} belong ON u.user_id = belong.user_id AND belong.item_id = ? ".
				" LEFT JOIN {users_items_link} email ON u.user_id = email.user_id AND email.item_id = ? ".
				" LEFT JOIN {users_items_link} tedc ON u.user_id = tedc.user_id AND tedc.item_id = ? ".
				"ORDER BY u.insert_time";

		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<userlist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <users>
EOF;

foreach( $result as $record ) {

	$tmp_belong = rtrim( $record['belong'], '|' );
	$tmp_tedc = $this->edit_ret_tedc( $record['tedc'] );
print <<< EOF
    <user>
      <login_id>{$record['login_id']}</login_id>
      <password>{$record['password']}</password>
      <handle>{$record['handle']}</handle>
      <role_authority_id>{$tmp_tedc}</role_authority_id>
      <belong>{$tmp_belong}</belong>
      <email>{$record['email']}</email>
    </user>
EOF;
}
print <<< EOF
  </users>
</userlist>
EOF;
	}

	/**
	 * 認証＆汎用データベース一覧連携インターフェース
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
     * @return	string	結果XML
	 * @access	public
	 */
	function get_databaselist( $loginid, $password ) {

		// ユーザ確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
//		if ( $result[0]['role_authority_id'] != 1 ) {
//			$auth_result   = 0;
//			$error_message = '権限エラー';
//		}

		// ユーザID
		$user_id = $result[0]['user_id'];


		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<databaselist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</databaselist>
EOF;
			exit;
		}


		// 汎用データベース一覧取得
/*
		SELECT m.epocdatabase_id, m.epocdatabase_name, m.room_id, l.role_authority_id
		FROM netcommons2_epocdatabase m
		  LEFT JOIN netcommons2_pages_users_link l ON m.room_id = l.room_id AND l.user_id = '3d96e39a969e1f98e654080dc4fc5059c90b9326'
		ORDER BY m.epocdatabase_id
*/
/*
		$params = array();
		$sql = "SELECT epocdatabase_id, epocdatabase_name ".
				"FROM {epocdatabase} ".
				"ORDER BY epocdatabase_id";
*/

		// 旧データベース(epocdatabase)の存在確認
/*
		if ( !$this->_testee_only ) {

			$params = array( $user_id );
			$sql =  "SELECT m.epocdatabase_id, m.epocdatabase_name, m.room_id, l.role_authority_id ".
					"FROM {epocdatabase} m ".
					  "LEFT JOIN {pages_users_link} l ON m.room_id = l.room_id AND l.user_id = ? ".
					"ORDER BY m.epocdatabase_id";
			$result = $this->_db->execute( $sql, $params );
			if ($result === false) {
				$this->_db->addError();
				return $result;
			}
*/
print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<databaselist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <databases>
EOF;

/*
foreach( $result as $record ) {
	if ( $record['role_authority_id'] == '2' ) {

print <<< EOF
    <database>
      <multidatabase_id>{$record['epocdatabase_id']}</multidatabase_id>
      <multidatabase_name>{$record['epocdatabase_name']}</multidatabase_name>
    </database>
EOF;

	}
}
*/
//		}

		// 新データベース(testee)

		// 新データベース一覧取得
		/*
		SELECT t.testee_id, t.testee_name, t.room_id
		     , l.role_authority_id
		     , c.all_counts, c.counts_id, c.option_counts
		     , m.select_content, m.select_content_code
		FROM netcommons2_testee t
		  LEFT JOIN netcommons2_pages_users_link l ON t.room_id = l.room_id AND l.user_id = '3d96e39a969e1f98e654080dc4fc5059c90b9326'
		  LEFT JOIN netcommons2_testee_counts c ON c.testee_id = t.testee_id
		  LEFT JOIN netcommons2_testee_metadata m ON m.metadata_id = c.counts_id
		ORDER BY t.testee_id
		*/

		$params = array( $user_id );
		$sql =  "SELECT t.testee_id, t.testee_name, t.room_id ".
				     ", l.role_authority_id ".
				     ", c.all_counts, c.counts_id, c.option_counts ".
				     ", m.select_content, m.select_content_code ".
				"FROM {testee} t ".
				  "LEFT JOIN {pages_users_link} l ON t.room_id = l.room_id AND l.user_id = ? ".
				  "LEFT JOIN {testee_counts} c ON c.testee_id = t.testee_id ".
				  "LEFT JOIN {testee_metadata} m ON m.metadata_id = c.counts_id ".
				"ORDER BY t.testee_id";
		$result = $this->_db->execute( $sql, $params );
		//test_log($result);
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		foreach( $result as $record ) {

			if ( $record['role_authority_id'] == '2' ) {

				// データベース名称
				$database_name = $record["testee_name"];

				// 件数制御がある場合
				if ( !empty( $record["counts_id"] ) ) {

					// 件数制御カラムのmetadata_id
					$database_name .= "@" . $record["counts_id"];

					// 全体の件数制御
					$database_name .= "@" . $record["all_counts"];

					// 選択肢の配列の再現
					$select_content_code = explode( "|", $record["select_content_code"] );
					$select_content = explode( "|", $record["select_content"] );
					$select_content_array = array_combine( $select_content_code, $select_content );
					//test_log($select_content_array);

					// 個別の件数制御の配列の再現
					$counts_option = json_decode( $record["option_counts"], true );
					//test_log($counts_option);

					foreach ( $counts_option as $count_key => $count_option ) {
						$database_name .= "@" . $select_content_array[ $count_key ] . "@" . $count_option;
					}
				}
			}

print <<< EOF

    <database>
      <multidatabase_id>{$record['testee_id']}</multidatabase_id>
      <multidatabase_name>{$database_name}</multidatabase_name>
    </database>
EOF;
		}

print <<< EOF

  </databases>
</databaselist>
EOF;
	}

	/**
	 * testee のグループmetadata を取得
	 * @access	public
	 */
	function getTesteeGroupMetadata( $database_id ) {

		$params = array( $database_id );

		$sql = "SELECT * ".
				"FROM {testee_metadata} ".
				"WHERE testee_id = ? AND type = 28";
		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// testee
		if ( !empty( $result ) && count( $result ) > 0 && !empty( $result[0]["testee_id"] ) ) {

			$this->group_metadata = $result[0];

			// 選択肢の配列の再現
			$select_content_code = explode( "|", $result[0]["select_content_code"] );
			$select_content = explode( "|", $result[0]["select_content"] );
			$this->group_metadata_array = array_combine( $select_content_code, $select_content );

			return $result[0];
		}
		return $result;
	}

	/**
	 * 対象のデータベースがepocdatabase かtestee か判定
	 *
     * @return	boolean true:testee, false:testee以外
	 * @access	public
	 */
	function isTestee( $database_id ) {

		// 対象のデータベースがepocdatabase かtestee か判定
		$params = array( $database_id );

		$sql = "SELECT testee_id ".
				"FROM {testee} ".
				"WHERE testee_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		//test_log( $sql );
		//test_log( $params );
		//test_log( $result );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// testee
		if ( !empty( $result ) && count( $result ) > 0 && !empty( $result[0]["testee_id"] ) ) {
			$this->getTesteeGroupMetadata( $database_id );
			return true;
		}

		// epocdatabase
		return false;

	}

	/**
	 * 認証＆汎用データベース・メタデータ一覧連携インターフェース
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
     * @return	string	結果XML
	 * @access	public
	 */
	function get_metalist( $loginid, $password, $epocdatabase_id ) {

		// 対象のデータベースがepocdatabase かtestee か判定
		$is_testee = $this->isTestee( $epocdatabase_id );
		//test_log( $is_testee );

		if ( $is_testee ) {
			// testee での処理
			$this->_get_metalist_testee_impl( $loginid, $password, $epocdatabase_id );
		}
		else  {
			// epocdatabase での処理
			$this->_get_metalist_epocdatabase_impl( $loginid, $password, $epocdatabase_id );
		}
	}

	/**
	 * 認証＆汎用データベース・メタデータ一覧連携インターフェース(testee)
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	testee_id	データベースID
     * @return	string	結果XML
	 * @access	public
	 */
	function _get_metalist_testee_impl( $loginid, $password, $testee_id ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
//		if ( $result[0]['role_authority_id'] != 1 ) {
//			$auth_result   = 0;
//			$error_message = '権限エラー';
//		}

		// ユーザID
		$user_id = $result[0]['user_id'];

		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metalist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</metalist>
EOF;
			exit;
		}

		// 割付設定の on/off 判定
		$alloc_params = array( $testee_id );
		$alloc_sql  = "SELECT a.allocation_flag ".
					  "FROM {testee_allocate} a ".
					  "WHERE a.testee_id = ?";
		$alloc_result = $this->_db->execute( $alloc_sql, $alloc_params );

		if ($alloc_result === false) {
			$this->_db->addError();
			return $alloc_result;
		}

		$allocation_flag = 0;
		if ( is_array($alloc_result) && count($alloc_result) > 0 && $alloc_result[0]["allocation_flag"] == 1 ) {
			$allocation_flag = 1;
		}


		// 新データベース・メタデータ一覧取得
		$params = array( $user_id, $testee_id );
		/*
			SELECT m.metadata_id, m.name, m.type, m.display_pos, m.select_content, m.select_content_code, m.correct, m.group_option, m.require_flag, m.display_sequence
			     , l.role_authority_id
			     , c.cond1_ew, c.cond1_fugo1, c.cond1_value1, c.cond1_fugo2, c.cond1_value2, c.cond2_ew, c.cond2_fugo1, c.cond2_value1, c.cond2_fugo2, c.cond2_value2
			     , cm.comment
			FROM netcommons2_testee_metadata m
			  LEFT JOIN netcommons2_pages_users_link l ON m.room_id = l.room_id AND l.user_id = '3d96e39a969e1f98e654080dc4fc5059c90b9326'
			  LEFT JOIN netcommons2_testee_metadata_condition c ON c.metadata_id = m.metadata_id
			  LEFT JOIN netcommons2_testee_metadata_comment cm ON cm.metadata_id = m.metadata_id
			WHERE m.testee_id = 1001
			ORDER BY m.display_pos, m.display_sequence
		*/

		$sql  = "SELECT m.metadata_id, m.name AS metadata_name, m.type, m.display_pos, m.select_content, m.select_content_code, m.correct, m.group_option, m.require_flag, m.display_sequence ".
				     ", l.role_authority_id ".
				     ", c.cond1_ew, c.cond1_fugo1, c.cond1_value1, c.cond1_fugo2, c.cond1_value2, c.cond2_ew, c.cond2_fugo1, c.cond2_value1, c.cond2_fugo2, c.cond2_value2 ".
				     ", cm.comment ".
				"FROM {testee_metadata} m ".
				  "LEFT JOIN {pages_users_link} l ON m.room_id = l.room_id AND l.user_id = ? ".
				  "LEFT JOIN {testee_metadata_condition} c ON c.metadata_id = m.metadata_id ".
				  "LEFT JOIN {testee_metadata_comment} cm ON cm.metadata_id = m.metadata_id ".
				"WHERE m.testee_id = ? ".
				"ORDER BY m.display_pos, m.display_sequence";
		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// ここまででエラーの場合
		if ( count( $result ) == 0 ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metalist>
  <result_code>
    <code>0</code>
    <error_message>指定されたデータベースがないか、メタデータがありません。</error_message>
  </result_code>
</metalist>
EOF;
			exit;
		}

		// 権限チェック
		if ( $result[0]['role_authority_id'] != '2' ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>権限エラー</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<metalist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <metas>

EOF;

// 登録者名と登録番号を出力
print <<< EOF
    <meta>
      <metadata_id>0</metadata_id>
      <name>登録者</name>
      <type>1</type>
      <display_pos>1</display_pos>
      <select_content></select_content>
      <require_flag>1</require_flag>
      <display_sequence>0</display_sequence>
    </meta>
    <meta>
      <metadata_id>1</metadata_id>
      <name>登録番号@num</name>
      <type>1</type>
      <display_pos>1</display_pos>
      <select_content></select_content>
      <require_flag>1</require_flag>
      <display_sequence>0</display_sequence>
    </meta>

EOF;

	// 割付データ
	if ( $allocation_flag ) {

print <<< EOF
    <meta>
      <metadata_id>2</metadata_id>
      <name>割付</name>
      <type>1</type>
      <display_pos>1</display_pos>
      <select_content></select_content>
      <require_flag></require_flag>
      <display_sequence>0</display_sequence>
    </meta>
    <meta>
      <metadata_id>3</metadata_id>
      <name>介入</name>
      <type>1</type>
      <display_pos>1</display_pos>
      <select_content></select_content>
      <require_flag></require_flag>
      <display_sequence>0</display_sequence>
    </meta>

EOF;
	}


foreach( $result as $record ) {

	if ( $record['role_authority_id'] == '2' ) {

		// タイトル
		$tmp_name = $record['metadata_name'];

		// 選択肢
		$tmp_select_content = $record['select_content'];

		// metadata 編集
		list( $tmp_name, $tmp_select_content ) = $this->edit_metadata( $record );

		// タイプ
		$tmp_type = $this->edit_type( $record['type'] );


//test_log( $tmp_name );

print <<< EOF
    <meta>
      <metadata_id>{$record['metadata_id']}</metadata_id>
      <name>{$tmp_name}</name>
      <type>{$tmp_type}</type>
      <display_pos>{$record['display_pos']}</display_pos>
      <select_content>{$tmp_select_content}</select_content>
      <require_flag>{$record['require_flag']}</require_flag>
      <display_sequence>{$record['display_sequence']}</display_sequence>
    </meta>

EOF;
	}
}

		// 新データベース・関連日付チェック取得
		/*
			SELECT *
			FROM netcommons2_testee_date_condition c
			WHERE c.testee_id = 1002
			ORDER BY c.condition_id
		*/
		$params = array( $testee_id );
		$sql  = "SELECT * ".
				"FROM {testee_date_condition} c ".
				"WHERE c.testee_id = ? ".
				"ORDER BY c.condition_id";
		$conditions = $this->_db->execute( $sql, $params );
		//test_log($conditions);

		if ($conditions === false) {
			$this->_db->addError();
			return $conditions;
		}

		// 関連日付
		if ( !empty( $conditions ) && count( $conditions ) > 0 && !empty( $conditions[0]["condition_id"] ) ) {

			$index = 0;
			foreach ( $conditions as $condition ) {

				$index++;

				$tmp_name = "関連チェック" . $index . "@check_date";

				// グループ設定があるか
				$tmp_name .= "@";

				// 条件式1
				$tmp_name .= "@";
				$tmp_name .= $this->getEW( $condition["cond1_ew"] );
				$tmp_name .= "@". $this->getFugo( $condition["cond1_fugo1"] );
				$tmp_name .= $condition["cond1_value1"];
				$tmp_name .= "@". $this->getFugo( $condition["cond1_fugo2"] );
				$tmp_name .= $condition["cond1_value2"];
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond1_ew"] ) ) ? $condition["date1_id"] : "";
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond1_ew"] ) ) ? $condition["date2_id"] : "";

				// 条件式2
				$tmp_name .= "@";
				$tmp_name .= $this->getEW( $condition["cond2_ew"] );
				$tmp_name .= "@". $this->getFugo( $condition["cond2_fugo1"] );
				$tmp_name .= $condition["cond2_value1"];
				$tmp_name .= "@". $this->getFugo( $condition["cond2_fugo2"] );
				$tmp_name .= $condition["cond2_value2"];
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond2_ew"] ) ) ? $condition["date1_id"] : "";
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond2_ew"] ) ) ? $condition["date2_id"] : "";

				$tmp_name = rtrim( $tmp_name, '@' );

				// HTML特殊文字の処理
				$tmp_name = str_replace( '>', '&gt;', $tmp_name );
				$tmp_name = str_replace( '<', '&lt;', $tmp_name );

print <<< EOF
    <meta>
      <metadata_id>{$record['metadata_id']}</metadata_id>
      <name>{$tmp_name}</name>
      <type>{$record['type']}</type>
      <display_pos>{$record['display_pos']}</display_pos>
      <select_content>{$tmp_select_content}</select_content>
      <require_flag>{$record['require_flag']}</require_flag>
      <display_sequence>{$record['display_sequence']}</display_sequence>
    </meta>

EOF;
			}
		}

print <<< EOF
  </metas>
</metalist>
EOF;
	}

	/**
	 * 認証＆汎用データベース・メタデータ一覧連携インターフェース(epocdatabase)
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
     * @return	string	結果XML
	 * @access	public
	 */
	function _get_metalist_epocdatabase_impl( $loginid, $password, $epocdatabase_id ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
//		if ( $result[0]['role_authority_id'] != 1 ) {
//			$auth_result   = 0;
//			$error_message = '権限エラー';
//		}

		// ユーザID
		$user_id = $result[0]['user_id'];

		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metalist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</metalist>
EOF;
			exit;
		}


		// 汎用データベース・メタデータ一覧取得
		$params = array( $user_id, $epocdatabase_id );
/*
		SELECT m.metadata_id, m.name, m.type, m.display_pos, m.select_content, m.require_flag, m.display_sequence, l.role_authority_id
		FROM netcommons2_epocdatabase_metadata m
		  LEFT JOIN netcommons2_pages_users_link l ON m.room_id = l.room_id AND l.user_id = '3d96e39a969e1f98e654080dc4fc5059c90b9326'
		WHERE m.epocdatabase_id = 6
		ORDER BY m.display_pos, m.display_sequence
*/

		$sql  = "SELECT m.metadata_id, m.name, m.type, m.display_pos, m.select_content, m.require_flag, m.display_sequence, l.role_authority_id ".
				"FROM {epocdatabase_metadata} m ".
				  "LEFT JOIN {pages_users_link} l ON m.room_id = l.room_id AND l.user_id = ? ".
				"WHERE m.epocdatabase_id = ? ".
				"ORDER BY m.display_pos, m.display_sequence";
		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// ここまででエラーの場合
		if ( count( $result ) == 0 ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metalist>
  <result_code>
    <code>0</code>
    <error_message>指定されたデータベースがないか、メタデータがありません。</error_message>
  </result_code>
</metalist>
EOF;
			exit;
		}

		// 権限チェック
		if ( $result[0]['role_authority_id'] != '2' ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>権限エラー</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<metalist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <metas>
EOF;

foreach( $result as $record ) {
	if ( $record['role_authority_id'] == '2' ) {

	// HTML特殊文字の処理
	$tmp_name = str_replace( '>', '&gt;', $record['name'] );
	$tmp_name = str_replace( '<', '&lt;', $tmp_name );

print <<< EOF
    <meta>
      <metadata_id>{$record['metadata_id']}</metadata_id>
      <name>{$tmp_name}</name>
      <type>{$record['type']}</type>
      <display_pos>{$record['display_pos']}</display_pos>
      <select_content>{$record['select_content']}</select_content>
      <require_flag>{$record['require_flag']}</require_flag>
      <display_sequence>{$record['display_sequence']}</display_sequence>
    </meta>
EOF;
	}
}
print <<< EOF
  </metas>
</metalist>
EOF;
	}

	/**
	 * 認証＆汎用データベース・レコード一覧連携インターフェース
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
     * @return	string	結果XML
	 * @access	public
	 */
	function get_contentlist( $loginid, $password, $epocdatabase_id ) {

		// 対象のデータベースがepocdatabase かtestee か判定
		$is_testee = $this->isTestee( $epocdatabase_id );
		//test_log( $is_testee );

		if ( $is_testee ) {
			// testee での処理
			$this->_get_contentlist_testee_impl( $loginid, $password, $epocdatabase_id );
		}
		else  {
			// epocdatabase での処理
			$this->_get_contentlist_epocdatabase_impl( $loginid, $password, $epocdatabase_id );
		}
	}

	/**
	 * 認証＆汎用データベース・レコード一覧連携インターフェース(testee)
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
     * @return	string	結果XML
	 * @access	public
	 */
	function _get_contentlist_testee_impl( $loginid, $password, $epocdatabase_id ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
//		if ( $result[0]['role_authority_id'] != 1 ) {
//			$auth_result   = 0;
//			$error_message = '権限エラー';
//		}

		// ユーザID
		$user_id = $result[0]['user_id'];


		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<contentlist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</contentlist>
EOF;
			exit;
		}


		// 汎用データベース・権限チェック
		$testee_params = array( $user_id, $epocdatabase_id );

		$testee_sql =  "SELECT t.testee_id, t.room_id, l.role_authority_id ".
		               "FROM {testee} t ".
		                "LEFT JOIN {pages_users_link} l ON t.room_id = l.room_id AND l.user_id = ? ".
		               "WHERE t.testee_id  = ?";
		$testee_result = $this->_db->execute( $testee_sql, $testee_params );

		if ($testee_result === false) {
			$this->_db->addError();
			return $testee_result;
		}

		// 権限チェック
		if ( is_array( $testee_result ) && $testee_result[0]['role_authority_id'] != '2' ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>権限エラー</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}


		// 汎用データベース・レコード一覧取得
		$params = array( $user_id, $epocdatabase_id );

		$sql =  "SELECT c.content_id, c.display_sequence, c.insert_time, c.update_time, c.content_no, l.role_authority_id ".
				"FROM {testee_content} c ".
				  "LEFT JOIN {pages_users_link} l ON c.room_id = l.room_id AND l.user_id = ? ".
				"WHERE c.testee_id  = ? ".
				"ORDER BY c.display_sequence";
		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<contentlist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <contents>
EOF;

foreach( $result as $record ) {
	if ( $record['role_authority_id'] == '2' ) {
print <<< EOF
    <content>
      <content_id>{$record['content_id']}</content_id>
      <display_sequence>{$record['display_sequence']}</display_sequence>
      <insert_time>{$record['insert_time']}</insert_time>
      <update_time>{$record['update_time']}</update_time>
      <content_no>{$record['content_no']}</content_no>
    </content>
EOF;
	}
}
print <<< EOF
  </contents>
</contentlist>
EOF;
	}

	/**
	 * 認証＆汎用データベース・レコード一覧連携インターフェース(epocdatabase)
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
     * @return	string	結果XML
	 * @access	public
	 */
	function _get_contentlist_epocdatabase_impl( $loginid, $password, $epocdatabase_id ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
//		if ( $result[0]['role_authority_id'] != 1 ) {
//			$auth_result   = 0;
//			$error_message = '権限エラー';
//		}

		// ユーザID
		$user_id = $result[0]['user_id'];


		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<contentlist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</contentlist>
EOF;
			exit;
		}


		// 汎用データベース・レコード一覧取得
		$params = array( $user_id, $epocdatabase_id );

		$sql =  "SELECT c.content_id, c.display_sequence, c.insert_time, c.update_time, c.content_no, l.role_authority_id ".
				"FROM {epocdatabase_content} c ".
				  "LEFT JOIN {pages_users_link} l ON c.room_id = l.room_id AND l.user_id = ? ".
				"WHERE c.epocdatabase_id = ? ".
				"ORDER BY c.display_sequence";
		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 権限チェック
		if ( $result[0]['role_authority_id'] != '2' ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>権限エラー</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}


print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<contentlist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <contents>
EOF;

foreach( $result as $record ) {
	if ( $record['role_authority_id'] == '2' ) {
print <<< EOF
    <content>
      <content_id>{$record['content_id']}</content_id>
      <display_sequence>{$record['display_sequence']}</display_sequence>
      <insert_time>{$record['insert_time']}</insert_time>
      <update_time>{$record['update_time']}</update_time>
      <content_no>{$record['content_no']}</content_no>
    </content>
EOF;
	}
}
print <<< EOF
  </contents>
</contentlist>
EOF;
	}

	/**
	 * 認証＆汎用データベース・メタコンテンツ一覧連携インターフェース
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
	 * @param	string	content_id	コンテンツID
	 * @param	string	data	内容が min なら、metacontents の中身を返さない。
     * @return	string	結果XML
	 * @access	public
	 */
	function get_metacontentlist( $loginid, $password, $epocdatabase_id, $content_id, $data = '' ) {

		// 対象のデータベースがepocdatabase かtestee か判定
		$is_testee = $this->isTestee( $epocdatabase_id );
		//test_log( $is_testee );

		if ( $is_testee ) {
			// testee での処理
			$this->_get_metacontentlist_testee_impl( $loginid, $password, $epocdatabase_id, $content_id, $data = '' );
		}
		else  {
			// epocdatabase での処理
			$this->_get_metacontentlist_epocdatabase_impl( $loginid, $password, $epocdatabase_id, $content_id, $data = '' );
		}
	}

	/**
	 * 認証＆汎用データベース・メタコンテンツ一覧連携インターフェース(testee)
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	testee_id	データベースID
	 * @param	string	content_id	コンテンツID
	 * @param	string	data	内容が min なら、metacontents の中身を返さない。
     * @return	string	結果XML
	 * @access	public
	 */
	function _get_metacontentlist_testee_impl( $loginid, $password, $testee_id, $content_id, $data = '' ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
		//if ( $result[0]['role_authority_id'] != 1 ) {
		//	$auth_result   = 0;
		//	$error_message = '権限エラー';
		//}

		// ユーザID
		$user_id = $result[0]['user_id'];


		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}

		// 割付設定の on/off 判定
		$alloc_params = array( $testee_id );
		$alloc_sql  = "SELECT a.allocation_flag ".
					  "FROM {testee_allocate} a ".
					  "WHERE a.testee_id = ?";
		$alloc_result = $this->_db->execute( $alloc_sql, $alloc_params );

		if ($alloc_result === false) {
			$this->_db->addError();
			return $alloc_result;
		}

		$allocation_flag = 0;
		if ( is_array($alloc_result) && count($alloc_result) > 0 && $alloc_result[0]["allocation_flag"] == 1 ) {
			$allocation_flag = 1;
		}


		// 汎用データベース・コンテンツレコードを取得
		$params = array( $user_id, $content_id );

		$sql = "SELECT t.content_no, t.insert_user_id, l.role_authority_id " .
				", {testee_allocation_group}.group_name, {testee_allocation_group}.intervention ".
				"FROM {testee_content} t " .
				  "LEFT JOIN {pages_users_link} l ON t.room_id = l.room_id AND l.user_id = ? ".
				  "LEFT JOIN {testee_content_group} ON {testee_content_group}.content_id = t.content_id ".
				  "LEFT JOIN {testee_allocation_group} ON {testee_allocation_group}.allocation_group_id = {testee_content_group}.allocation_group_id ".
				"WHERE t.content_id = ?";

		$result = $this->_db->execute( $sql, $params );
		//print_r( $this->_db->ErrorMsg() );
		//print_r( $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// レコードなし
		if ( count( $result ) <= 0 ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>1</code>
    <error_message>データなし</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}

		// 権限チェック
		if ( $result[0]['role_authority_id'] != '2' ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>権限エラー</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}


		// 割付結果
		$group_name = $result[0]['group_name'];

		// 割付結果の介入
		$intervention = $result[0]['intervention'];

		// ユーザの施設情報を取得
		$hospital = $this->get_belong_item( $result[0]['insert_user_id'] );

		// データベース内レコードのユニークキー
		$content_no = $result[0]['content_no'];

		// 汎用データベース・メタコンテンツ一覧取得
		$params = array( $content_id, $testee_id );
		//print_r( $params );

		/*
			SELECT mc.metadata_content_id, mc.content, mc.content_id, mc.insert_user_name
			     , m.metadata_id, m.name AS metadata_name, m.type, m.display_pos, m.select_content, m.select_content_code, m.correct, m.group_option, m.require_flag, m.display_sequence
			     , c.cond1_ew, c.cond1_fugo1, c.cond1_value1, c.cond1_fugo2, c.cond1_value2, c.cond2_ew, c.cond2_fugo1, c.cond2_value1, c.cond2_fugo2, c.cond2_value2
			     , cm.comment
			FROM netcommons2_testee_metadata m LEFT JOIN netcommons2_testee_metadata_content mc ON mc.metadata_id = m.metadata_id AND mc.content_id = 5
			  LEFT JOIN netcommons2_testee_metadata_condition c ON c.metadata_id = m.metadata_id
			  LEFT JOIN netcommons2_testee_metadata_comment cm ON cm.metadata_id = m.metadata_id
			WHERE m.testee_id = 1001
			ORDER BY m.display_pos, m.display_sequence
		*/

		$sql =  "SELECT mc.metadata_content_id, mc.content, mc.content_id, mc.insert_user_name ".
				     ", m.metadata_id, m.name AS metadata_name, m.type, m.display_pos, m.select_content, m.select_content_code, m.correct, m.group_option, m.require_flag, m.display_sequence ".
				     ", c.cond1_ew, c.cond1_fugo1, c.cond1_value1, c.cond1_fugo2, c.cond1_value2, c.cond2_ew, c.cond2_fugo1, c.cond2_value1, c.cond2_fugo2, c.cond2_value2 ".
				     ", cm.comment ".
				"FROM {testee_metadata} m LEFT JOIN {testee_metadata_content} mc ON mc.metadata_id = m.metadata_id AND mc.content_id = ? ".
				  "LEFT JOIN {testee_metadata_condition} c ON c.metadata_id = m.metadata_id ".
				  "LEFT JOIN {testee_metadata_comment} cm ON cm.metadata_id = m.metadata_id ".
				"WHERE m.testee_id = ? ".
				"ORDER BY m.display_pos, m.display_sequence";

		$result = $this->_db->execute( $sql, $params );
		//test_log( $result );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		//$params_str = print_r($params,true);

		// 登録者を保持しておく。登録者はもし最初の項目に未入力があった場合、LEFT JOIN でnull の可能性もあるので、ループして探しておく
		// 登録番号は上で取得し、変数に保持しているので、ここでは処理しない。
		$insert_user_name = "";
		foreach( $result as $record ) {
			if ( empty( $insert_user_name ) && $record["insert_user_name"] ) {
				$insert_user_name = $record["insert_user_name"];
			}
		}

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<metacontentlist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <content_header>
    <hospital>{$hospital}</hospital>
    <content_no>{$content_no}</content_no>
  </content_header>
  <metacontents>
    <metacontent>
      <metadata_content_id>0</metadata_content_id>
      <content>{$insert_user_name}</content>
      <metadata_id>0</metadata_id>
      <metadata_name>登録者</metadata_name>
    </metacontent>
    <metacontent>
      <metadata_content_id>1</metadata_content_id>
      <content>{$content_no}</content>
      <metadata_id>1</metadata_id>
      <metadata_name>登録番号</metadata_name>
    </metacontent>

EOF;

	// 割付データ
	if ( $allocation_flag ) {

print <<< EOF
    <metacontent>
      <metadata_content_id>2</metadata_content_id>
      <content>{$group_name}</content>
      <metadata_id>2</metadata_id>
      <metadata_name>割付</metadata_name>
    </metacontent>
    <metacontent>
      <metadata_content_id>3</metadata_content_id>
      <content>{$intervention}</content>
      <metadata_id>3</metadata_id>
      <metadata_name>介入</metadata_name>
    </metacontent>

EOF;
	}

		// data=min の判定
		if ( $data != 'min' ) {

			foreach( $result as $record ) {

				// タイトル
				$tmp_metadata_name = $record['metadata_name'];

				// コメントの場合、タイトルの編集
				if ( $record['type'] == 33 ) {
					$tmp_metadata_name = $record['comment'] . "@comment";
				}

				// 選択肢
				$tmp_select_content = $record['select_content'];

				// metadata 編集
				list( $tmp_metadata_name, $tmp_select_content ) = $this->edit_metadata( $record );

				// 登録したコンテンツ
				$tmp_content = str_replace( '>', '&gt;', $record['content'] );
				$tmp_content = str_replace( '<', '&lt;', $tmp_content );

				// 登録内容の編集
				$tmp_content = $this->edit_metadata_content( $record );


print <<< EOF
    <metacontent>
      <metadata_content_id>{$record['metadata_content_id']}</metadata_content_id>
      <content>{$tmp_content}</content>
      <metadata_id>{$record['metadata_id']}</metadata_id>
      <metadata_name>{$tmp_metadata_name}</metadata_name>
    </metacontent>

EOF;

			// data=min の判定終わり
			}

		}

		// 新データベース・関連日付チェック取得
		/*
			SELECT *
			FROM netcommons2_testee_date_condition c
			WHERE c.testee_id = 1002
			ORDER BY c.condition_id
		*/
		$params = array( $testee_id );
		$sql  = "SELECT * ".
				"FROM {testee_date_condition} c ".
				"WHERE c.testee_id = ? ".
				"ORDER BY c.condition_id";
		$conditions = $this->_db->execute( $sql, $params );
		//test_log($conditions);

		if ($conditions === false) {
			$this->_db->addError();
			return $conditions;
		}

		// 関連日付
		if ( !empty( $conditions ) && count( $conditions ) > 0 && !empty( $conditions[0]["condition_id"] ) ) {

			$index = 0;
			foreach ( $conditions as $condition ) {

				$index++;

				$tmp_name = "関連チェック" . $index . "@check_date";

				// グループ設定があるか
				$tmp_name .= "@";

				// 条件式1
				$tmp_name .= "@";
				$tmp_name .= $this->getEW( $condition["cond1_ew"] );
				$tmp_name .= "@". $this->getFugo( $condition["cond1_fugo1"] );
				$tmp_name .= $condition["cond1_value1"];
				$tmp_name .= "@". $this->getFugo( $condition["cond1_fugo2"] );
				$tmp_name .= $condition["cond1_value2"];
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond1_ew"] ) ) ? $condition["date1_id"] : "";
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond1_ew"] ) ) ? $condition["date2_id"] : "";

				// 条件式2
				$tmp_name .= "@";
				$tmp_name .= $this->getEW( $condition["cond2_ew"] );
				$tmp_name .= "@". $this->getFugo( $condition["cond2_fugo1"] );
				$tmp_name .= $condition["cond2_value1"];
				$tmp_name .= "@". $this->getFugo( $condition["cond2_fugo2"] );
				$tmp_name .= $condition["cond2_value2"];
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond2_ew"] ) ) ? $condition["date1_id"] : "";
				$tmp_name .= "@";
				$tmp_name .= ( !empty( $condition["cond2_ew"] ) ) ? $condition["date2_id"] : "";

				$tmp_name = rtrim( $tmp_name, '@' );

				// HTML特殊文字の処理
				$tmp_name = str_replace( '>', '&gt;', $tmp_name );
				$tmp_name = str_replace( '<', '&lt;', $tmp_name );

print <<< EOF
    <metacontent>
      <metadata_content_id></metadata_content_id>
      <content></content>
      <metadata_id></metadata_id>
      <metadata_name>{$tmp_name}</metadata_name>
    </metacontent>

EOF;
			}
		}

print <<< EOF
  </metacontents>
</metacontentlist>
EOF;
	}

	/**
	 * 認証＆汎用データベース・メタコンテンツ一覧連携インターフェース(epocdatabase)
	 *
	 * @param	string	loginid	ログインID
	 * @param	string	password	パスワード
	 * @param	string	epocdatabase_id	データベースID
	 * @param	string	content_id	コンテンツID
	 * @param	string	data	内容が min なら、metacontents の中身を返さない。
     * @return	string	結果XML
	 * @access	public
	 */
	function _get_metacontentlist_epocdatabase_impl( $loginid, $password, $epocdatabase_id, $content_id, $data = '' ) {

		// 管理者確認
		$params = array( $loginid );

		$sql = "SELECT password, role_authority_id, user_id ".
				"FROM {users} ".
				"WHERE login_id = ? ";
		$result = $this->_db->execute( $sql, $params );
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// 返却変数
		$auth_result = 1;	// 初期値はOK、チェックしてNG を設定
		$role_authority_id = '';
		$error_message = '';

		// パスワード照合
		if ( $result[0]['password'] == $password ) {
			$role_authority_id = $result[0]['role_authority_id'];
		}
		else {
			$auth_result   = 0;
			$error_message = '認証エラー';
		}

		// 管理者チェック
		//if ( $result[0]['role_authority_id'] != 1 ) {
		//	$auth_result   = 0;
		//	$error_message = '権限エラー';
		//}

		// ユーザID
		$user_id = $result[0]['user_id'];


		// ここまででエラーの場合
		if ( $auth_result == false ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>{$error_message}</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}


		// 汎用データベース・コンテンツレコードを取得
		$params = array( $user_id, $content_id );

		$sql = "SELECT m.content_no, m.insert_user_id, l.role_authority_id " .
				"FROM {epocdatabase_content} m " .
				  "LEFT JOIN {pages_users_link} l ON m.room_id = l.room_id AND l.user_id = ? ".
				"WHERE m.content_id = ?";

		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		// レコードなし
		if ( count( $result ) <= 0 ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>1</code>
    <error_message>データなし</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}

		// 権限チェック
		if ( $result[0]['role_authority_id'] != '2' ) {

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<metacontentlist>
  <result_code>
    <code>0</code>
    <error_message>権限エラー</error_message>
  </result_code>
</metacontentlist>
EOF;
			exit;
		}


		// ユーザの施設情報を取得
		$hospital = $this->get_belong_item( $result[0]['insert_user_id'] );

		// データベース内レコードのユニークキー
		$content_no = $result[0]['content_no'];

		// 汎用データベース・メタコンテンツ一覧取得
		$params = array( $content_id, $epocdatabase_id );

		/*
			$sql = "SELECT mc.metadata_content_id, mc.content, mc.metadata_id, m.name AS metadata_name ".
					"FROM {epocdatabase_metadata_content} mc, {epocdatabase_metadata} m ".
					"WHERE mc.metadata_id = m.metadata_id ".
					  "AND mc.content_id = ? ".
					"ORDER BY m.display_pos, m.display_sequence";
		*/

		$sql = "SELECT mc.metadata_content_id, mc.content, m.metadata_id, m.name AS metadata_name ".
				"FROM {epocdatabase_metadata} m LEFT JOIN {epocdatabase_metadata_content} mc ON mc.metadata_id = m.metadata_id AND mc.content_id = ? ".
				"WHERE m.epocdatabase_id = ? ".
				"ORDER BY m.display_pos, m.display_sequence";

		$result = $this->_db->execute( $sql, $params );

		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		//$params_str = print_r($params,true);

print <<< EOF
<?xml version="1.0" encoding="UTF-8"?>

<metacontentlist>
  <result_code>
    <code>1</code>
    <error_message></error_message>
  </result_code>
  <content_header>
    <hospital>{$hospital}</hospital>
    <content_no>{$content_no}</content_no>
  </content_header>
  <metacontents>
EOF;

		// data=min の判定
		if ( $data != 'min' ) {

			foreach( $result as $record ) {

				// HTML特殊文字の処理
				$tmp_metadata_name = str_replace( '>', '&gt;', $record['metadata_name'] );
				$tmp_metadata_name = str_replace( '<', '&lt;', $tmp_metadata_name );

				$tmp_content = str_replace( '>', '&gt;', $record['content'] );
				$tmp_content = str_replace( '<', '&lt;', $tmp_content );

				// 項目が日付型の場合、2012-06-11 の形式で返す 2012-06-11 add by opensource-workshop.jp
				// 日付型の判断は @date と @birth の場合を日付とする。 2012-06-18 add by opensource-workshop.jp
				$date_check  = strpos( $record['metadata_name'], '@date' );
				$birth_check = strpos( $record['metadata_name'], '@birth' );

				if ( $date_check !== false || $birth_check !== false ) {
					if ( strlen( $tmp_content ) == 10 ) {
						$tmp_content = str_replace( '/', '-', $tmp_content );
					}
				}

print <<< EOF
    <metacontent>
      <metadata_content_id>{$record['metadata_content_id']}</metadata_content_id>
      <content>{$tmp_content}</content>
      <metadata_id>{$record['metadata_id']}</metadata_id>
      <metadata_name>{$tmp_metadata_name}</metadata_name>
    </metacontent>
EOF;

			// data=min の判定終わり
			}

		}
print <<< EOF
  </metacontents>
</metacontentlist>
EOF;
	}
}
?>
