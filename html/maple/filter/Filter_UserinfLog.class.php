<?php

/**
 * ユーザー情報変更ログ出力
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_UserinfLog extends Filter {

    var $_container;
    var $_filterChain;
    var $_request;
    var $_db = null;

    var $usersView = null;

    var $user_id = null;
    var $old_user;
    var $old_db_users_items_link;

    /**
     * コンストラクター
     *
     * @access    public
     */
    function Filter_UserinfLog() {
        parent::Filter();
    }

    /**
     * Viewの処理を実行
     *
     * @access    public
     **/
    function execute() {

	    $this->_container = DIContainerFactory::getContainer();
	    $this->_filterChain = $this->_container->getComponent("FilterChain");
	    $this->_request = $this->_container->getComponent("Request");
	    $this->_session = $this->_container->getComponent("Session");
	    $this->_db = $this->_container->getComponent("DbObject");
	    $this->usersView = $this->_container->getComponent("usersView");
	    $this->mailMain = $this->_container->getComponent("mailMain");

	    // アクション名
	    $action_name = ( array_key_exists( 'action', $_REQUEST ) ) ? $_REQUEST['action'] : '';

// 2020.06.10 change start パスワード再発行に対応

//		//新規かどうかの判断
//	    $this->user_id = ( $this->_request->getParameter("user_id") ) ? $this->_request->getParameter("user_id") : 0;
//		$edit_flag = _ON;
//		if($this->user_id == null || $this->user_id == "0") {
//			$this->user_id = "0";
//			$edit_flag = _OFF;	//新規
//		}
//
//		// 記録の対象を会員情報からの変更のみとする
////		if($action_name == "user_action_admin_regist" || $action_name == "userinf_action_main_init" ) {
//		if($action_name == "userinf_action_main_init" ) {
//			if($edit_flag) {
////test_log($action_name);
//		    	    $this->_prefilter();
//			}
//		}
//
//	    $this->_filterChain->execute();
//
////		if($action_name == "user_action_admin_regist" || $action_name == "userinf_action_main_init" ) {
//		if($action_name == "userinf_action_main_init" ) {
//			if($edit_flag) {
//			        $this->_postfilter();
//			}
//		}

		// アクションによって処理分岐
		if( $action_name == "userinf_action_main_init" )
		{
			// ----- ユーザー管理画面からの変更 --------------------------------
			
			//新規かどうかの判断
		    $this->user_id = ( $this->_request->getParameter("user_id") ) ? $this->_request->getParameter("user_id") : 0;
			if($this->user_id == null || $this->user_id == "0")
			{
				// 新規登録の場合は処理しない
				$this->user_id = "0";
				
				$this->_filterChain->execute();		// 次の処理
			}
			else
			{
				// 変更の場合のみ処理実施
				$this->_prefilter();				// 前処理
				$this->_filterChain->execute();		// 次の処理
				$this->_postfilter($action_name);	// 後処理
				
			}
		}
		else if( $action_name == "login_action_main_forgetpass" )
		{
			// ----- パスワード再発行からの変更 --------------------------------
			// リクエストパラメータ取得（Validatorで設定されているハズ）
			$target_user = $this->_request->getParameter( "target_user" );
			
			if( $target_user == null || empty( $target_user ) === true || count( $target_user ) <= 0 )
			{
				// 何も設定されていない場合、Validatorでエラーがでているからなので、何も処理しない
				$this->_filterChain->execute();		// 次の処理
			}
			else
			{
				// 設定がある場合はValidatorをクリアしているので処理を行う
				$this->user_id = $target_user["user_id"];
				
				$this->_prefilter();				// 前処理
				$this->_filterChain->execute();		// 次の処理
				$this->_postfilter($action_name);	// 後処理
			}
		}
		else
		{
			//----- 上記以外の処理の場合 ---------------------------------------
			
			// 次の処理実施
			$this->_filterChain->execute();
		}

// 2020.06.10 change end パスワード再発行に対応


    }

    /**
     * プレフィルタ
     * @access private
     */
    function _prefilter()
    {
		// 会員データ取得
		$users =& $this->_db->selectExecute("users", array("user_id" => $this->user_id));
		if($users === false) return 'error';
		if(!isset($users[0])) {
			// エラー
			$this->_db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "users"));
			return 'error';
		}
		$this->old_user =& $users[0];
    }
    /**
     * ポストフィルタ
     * @access private
     */
//    function _postfilter()		// 2020.06.10 change
    function _postfilter( $action_name )
    {

		// 変更記録対象項目を取得
	    $check_fields = $this->getAttributes();
		$email = $check_fields["@email"];
		unset($check_fields["@email"]);

		// 会員データ取得
		$users =& $this->_db->selectExecute("users", array("user_id" => $this->user_id));
		if($users === false) return 'error';
		if(!isset($users[0])) {
			// エラー
			$this->_db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "users"));
			return 'error';
		}
		$user =& $users[0];

		$diff_fields = array();

		// 会員データの差異項目を求める
		$diff_fields = array_keys(array_diff_assoc($this->old_user, $user));
		// 記録対象項目かの確認
		$diff_user = array();
        for ($i=0; $i < count($diff_fields); $i++) {
			if(array_key_exists ( $diff_fields[$i] , $check_fields )){
				array_push($diff_user,$diff_fields[$i]);
			}
        }

		if(count($diff_user) > 0) {

			// 会員データの差異項目の新旧データ内容を記録する
		    for ($i=0; $i < count($diff_user); $i++) {

			    $params = array(
			                    "login_id" => $user["login_id"],
			                    "handle" => $user["handle"],
			    );

                // 施設情報を取得
                // 施設情報のitemu_idを取得する
                $where_item = "USER_ITEM_HOSPITAL";
                $sql_params = array( $where_item );
                $sql = "SELECT {items}.item_id from {items} where {items}.item_name = ? ";
                $result = $this->_db->execute($sql, $sql_params);
                if ($result === false) {
                        $this->_db->addError();
                        return false;
                }
                if(count($result) > 0){
                    // 施設情報の登録がある場合
                    $sql_params = array(
                        "item_id" => $result[0]["item_id"],
                        "user_id" => $this->user_id
                    );
                    $sql  = "SELECT UIL.content AS hospital FROM {users} U ";
                    $sql .= "LEFT JOIN {users_items_link} UIL ON U.user_id = UIL.user_id AND UIL.item_id = ? ";
                    $sql .= "WHERE U.user_id = ? ";
                    $result = $this->_db->execute($sql, $sql_params);
                    if ($result === false) {
                        $this->_db->addError();
                        return false;
                    }
                    $params["hospital_name"] = substr($result[0]["hospital"], 0, -1);
                } else {
                    $params["hospital_name"] = null;    // 施設情報が無い場合nullを設定
                }
                
//				$this->sendMail($email, $params);		// 2020.06.10 change 
				$this->sendMail($email, $params, $action_name);
			}
		}
    }

	/**
	 * 変更項目をメールにて通知する
	 * @return	
	 * @access	public
	 **/
//    function sendMail($email, $params)		// 2020.06.10 change 
    function sendMail($email, $params, $action_name)
	{
		// 2020.06.10 change start 
		//$this->mailMain->setSubject("【UserinfLog】会員情報のパスワードが変更されました");
		if( $action_name == "login_action_main_forgetpass" )
		{
			$this->mailMain->setSubject("【UserinfLog】会員情報のパスワードが再発行により変更されました");
		}
		else
		{
			$this->mailMain->setSubject("【UserinfLog】会員情報のパスワードが変更されました");
		}
		// 2020.06.10 change end

		$this->mailMain->setBody("以下の会員のパスワードが変更されました\n".
"---------------------------------------------\n" .
"ログインＩＤ：{X-LOGIN-ID}\n" .
"ハンドル名　：{X-HANDLE}\n" .
"施設名　　　：{X-HOSPITAL-NAME}\n" .
"---------------------------------------------\n \n");
	        $tags = array(
	                    "X-LOGIN-ID" => $params["login_id"],
	                    "X-HANDLE" => $params["handle"],
	                    "X-HOSPITAL-NAME" => $params["hospital_name"],
	        );
		
		$this->mailMain->assign($tags);
		$emails = explode( ',', $email );
		$user = array();
		for ($i=0; $i < count($emails); $i++){
			$user[$i]['email'] = $emails[$i];
		}
		$this->mailMain->setToUsers($user);
		$this->mailMain->send();
		return;
	}
}
?>
