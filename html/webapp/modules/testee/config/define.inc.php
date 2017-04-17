<?php

/**
 * 定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
 

// --- データの編集権限

// データを編集できるロール権限をシステム管理者のみにするかどうか。これが優先。(true | false)
define("TESTEE_EDIT_SYSTEM_ROLE_AUTH", true);

// データを編集できるベース権限
define("TESTEE_EDIT_USER_AUTH_ID", _AUTH_ADMIN);

// データを削除できるロール権限をシステム管理者のみにするかどうか。これが優先。(true | false)
define("TESTEE_DELETE_SYSTEM_ROLE_AUTH", false);

// データを削除できるベース権限
define("TESTEE_DELETE_USER_AUTH_ID", _AUTH_ADMIN);

// --- 検体進捗機能

// メール署名
// 定数名の最後の数字はtesteeテーブルのkentai_switchカラムに合わせています。
// 今後、検体進捗のパターンが増えた時のため。
define("TESTEE_KENTAI_MAIL_FOOTER_1", "
問い合わせ先：
研究事務局
");

// リマインドメールの設定日付はリマインドメールバッチ処理の設定ファイル(config.php)に記載

?>