<?php

/**
 * 進捗を登録するためのValidator
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_Validator_StatusInsert extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{

		// コンポーネント
		$container = DIContainerFactory::getContainer();
		$request = $container->getComponent("Request");

		// 更新する対象の進捗状況
		$status_id = $request->getParameter("status_id");

		// 各進捗のデータ
		$content_data = $request->getParameter("content_data");

		// 資材・伝票の送付
		if ( $status_id == "1" ) {

			// 施設患者番号
			$unique_patient_cd = $request->getParameter("unique_patient_cd");
			if ( !strlen( trim( $unique_patient_cd ) ) ) {
				return "施設患者番号を入力してください。";
			}

			// 資材・伝票送付日
			$return_msg = $this->date_check( $content_data[1], "資材・伝票送付日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 送付者氏名
			if ( !strlen( trim( $content_data[2] ) ) ) {
				return "送付者氏名を入力してください。";
			}
		}

		// 検体登録
		if ( $status_id == "2" ) {

			// 参加施設での検体採取日
			$return_msg = $this->date_check( $content_data[1], "参加施設での検体採取日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 検体受領日
			$return_msg = $this->date_check( $content_data[2], "検体受領日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 受領した血清検体の本数
			if ( !strlen( trim( $content_data[3] ) ) ) {
				return "受領した血清検体の本数を入力してください。";
			}

			// 受領者氏名
			if ( !strlen( trim( $content_data[4] ) ) ) {
				return "受領者氏名を入力してください。";
			}
		}

		// 治療後の資材・伝票の送付
		if ( $status_id == "3" ) {

			// 施設患者番号
			$unique_patient_cd = $request->getParameter("unique_patient_cd");
			if ( !strlen( trim( $unique_patient_cd ) ) ) {
				return "施設患者番号を入力してください。";
			}

			// 確定診断病名
			if ( !array_key_exists( 1, $content_data ) || !strlen( trim( $content_data[1] ) ) ) {
				return "確定診断病名を選択してください。";
			}

			// 治療後の資材・伝票の送付で確定診断病名が「がん」以外の場合は、資材・伝票送付日、送付者氏名はチェックしない。
			if ( $content_data[1] == 1 ) {

				// 資材・伝票送付日
				$return_msg = $this->date_check( $content_data[2], "資材・伝票送付日" );
				if ( !empty( $return_msg ) ) {
					return $return_msg;
				}

				// 送付者氏名
				if ( !strlen( trim( $content_data[3] ) ) ) {
					return "送付者氏名を入力してください。";
				}
			}
		}

		// 治療後の検体登録
		if ( $status_id == "4" ) {

			// 参加施設での検体採取日
			$return_msg = $this->date_check( $content_data[1], "参加施設での検体採取日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 検体受領日
			$return_msg = $this->date_check( $content_data[2], "検体受領日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 受領した血清検体の本数
			if ( !strlen( trim( $content_data[4] ) ) ) {
				return "受領した血清検体の本数を入力してください。";
			}

			// 送付者氏名
			if ( !strlen( trim( $content_data[3] ) ) ) {
				return "送付者氏名を入力してください。";
			}
		}

		// 検体払い出し（治療前）
		if ( $status_id == "5" ) {

			// 検体を払い出した日
			$return_msg = $this->date_check( $content_data[1], "検体を払い出した日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 払い出した血清検体の本数
			if ( !strlen( trim( $content_data[2] ) ) ) {
				return "払い出した血清検体の本数を入力してください。";
			}

			// 払い出し者氏名
			if ( !strlen( trim( $content_data[3] ) ) ) {
				return "払い出し者氏名を入力してください。";
			}
		}

		// 検体払い出し（治療後）
		if ( $status_id == "6" ) {

			// 検体を払い出した日
			$return_msg = $this->date_check( $content_data[1], "検体を払い出した日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 払い出した血清検体の本数
			if ( !strlen( trim( $content_data[2] ) ) ) {
				return "払い出した血清検体の本数を入力してください。";
			}

			// 払い出し者氏名
			if ( !strlen( trim( $content_data[3] ) ) ) {
				return "払い出し者氏名を入力してください。";
			}
		}

		// マイクロRNA測定データ受領確認
		if ( $status_id == "7" ) {

			// 測定データを受領した日
			$return_msg = $this->date_check( $content_data[1], "測定データを受領した日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 受領したデータの内容
			if ( !array_key_exists( 2, $content_data ) || !strlen( trim( $content_data[2] ) ) ) {
				return "受領したデータの内容を選択してください。";
			}

			// データ確認者
			if ( !strlen( trim( $content_data[3] ) ) ) {
				return "データ確認者を入力してください。";
			}
		}

		// 治療後検体のマイクロRNA測定データ受領確認
		if ( $status_id == "8" ) {

			// 測定データを受領した日
			$return_msg = $this->date_check( $content_data[1], "測定データを受領した日" );
			if ( !empty( $return_msg ) ) {
				return $return_msg;
			}

			// 受領したデータの内容
			if ( !array_key_exists( 2, $content_data ) || !strlen( trim( $content_data[2] ) ) ) {
				return "受領したデータの内容を選択してください。";
			}

			// データ確認者
			if ( !strlen( trim( $content_data[3] ) ) ) {
				return "データ確認者を入力してください。";
			}
		}

		return;
	}

	/**
	 * 日付チェック
	 */
	function date_check( $date_value, $colum_name )
	{
		// 空チェック
		if ( !strlen( trim( $date_value ) ) ) {
			return $colum_name . "を入力してください。";
		}

		if ( mb_strlen( $date_value ) != 10 ) {
			return $colum_name . "をYYYY/MM/DD 形式で入力してください。(桁数エラー)";
		}

		if ( mb_substr_count( $date_value, "/" ) != 2 ) {
			return $colum_name . "をYYYY/MM/DD 形式で入力してください。( / エラー)";
		}

		list( $Y, $m, $d ) = explode( '/', $date_value );
		if ( !checkdate( intval( $m ), intval( $d ), intval( $Y ) ) ) {
			return $colum_name . "をYYYY/MM/DD 形式で入力してください。";
		}

		// 未来日チェック
		$input_ts = mktime( 0, 0, 0, $m, $d, $Y );  // 入力日の00:00:00
		$today_ts = mktime( 0, 0, 0 );              // 当日の00:00:00

		if ( $today_ts < $input_ts ) {
			return $colum_name . "に未来の日付けは登録できません。";
		}

		return;
	}
}
?>
