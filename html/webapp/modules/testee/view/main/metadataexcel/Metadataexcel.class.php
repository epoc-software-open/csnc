<?php
include BASE_DIR.'/webapp/components/phpexcel/PHPExcel/IOFactory.php';
include BASE_DIR.'/webapp/components/phpexcel/PHPExcel/Writer/Excel2007.php';

/**
 * メタデータexcel出力
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Testee_View_Main_Metadataexcel extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;

	var $testee_id = null;

	// 使用コンポーネントを受け取るため
	var $mdbView = null;
	var $csvMain = null;
    var $session = null;

    // バリデートによりセット
	var $mdb_obj = null;
	var $metadatas = null;

    // 値をセット
	var $counts_data = null;
	var $lang = null;

    var $group = null;					// グループの選択肢（選択肢・コード値）

	/**
	* [[機能説明]]
	*
	* @access  public
	*/
	function execute() {

		// TEDC権限の取得
		$tedc_authority = $this->mdbView->getUserTEDC($this->session->getParameter("_user_id"), $this->testee_id);

		// 管理者のみ、CSVダウンロードを許可
		if ( $tedc_authority == TEDC_AUTHORITY_ADMIN ) {
		}
		else {
			return "";
		}

        // メモリ無制限
        ini_set('memory_limit', -1);
        // 実行時間無制限
        ini_set('max_execution_time', 0);

		// languageの取得
		$renderer =& SmartyTemplate::getInstance();
		$this->lang  = $renderer->get_template_vars("lang");

		// テンプレートのエクセルファイルの読み込み
		$excel_file = BASE_DIR.'/webapp/modules/testee/files/excel/' . ORIGINAL_TESTEE_METADATADESIGN;
		$objReader = PHPExcel_IOFactory::createReader("Excel2007");
		$objPHPExcel = $objReader->load($excel_file);

		// グループの選択肢配列を作成
		$result = $this->mdbView->getGroup($this->testee_id);
		if ($result === false) {
			return 'error';
		}
		if(count($result) > 0){
			$groups = explode("|", $result[0]["select_content"]);
			$group_codes = explode("|", $result[0]["select_content_code"]);
			for($i = 0; count($group_codes) > $i; $i++){
				$this->group[$group_codes[$i]] = $groups[$i];
			}
		}

		// 「項目名と型」シートを取得
		$objPHPExcel->setActiveSheetIndex(0);
		$nametypeSheet = $objPHPExcel->getActiveSheet();
		// 「選択肢」シートを取得
		$objPHPExcel->setActiveSheetIndex(1);
		$optionsSheet = $objPHPExcel->getActiveSheet();
		// 「条件」シートを取得
		$objPHPExcel->setActiveSheetIndex(2);
		$conditionSheet = $objPHPExcel->getActiveSheet();

	// 件数設定情報の取得
		$this->counts_data = $this->mdbView->getCounts($this->testee_id);

	// 「項目名と型」シートを編集
		$this->writeNameType($nametypeSheet);
	// 「選択肢」シートを編集
		$this->writeOptions($optionsSheet);
	// 「条件」シートを編集
		$this->writeCondition($conditionSheet);

		$excel_name = $this->mdb_obj["testee_name"].".xlsx";		// ファイル名称を編集（テストDB名）

		// シートを保護する
		$nametypeSheet->getProtection()->setSheet( true );
		$optionsSheet->getProtection()->setSheet( true );
		$conditionSheet->getProtection()->setSheet( true );

		// ダウンロード実行
		$this->executeDownload($objPHPExcel, $excel_name);
		exit;
	}

	// 「項目名と型」シートを編集
	function writeNameType(& $objSheet)	{
		// 登録番号・登録日時の編集
		// 各セルの左・右・下罫線（細線）
		$objSheet -> getStyle("A2:H3") -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
		// 各セルの文字縦位置を真中
		$objSheet -> getStyle("A2:H3") -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		// フォントサイズの設定
		$objSheet->getStyle("A2:H3")->getFont()->setSize(9);
		// 行の高さ設定
		$objSheet -> getRowDimension(2)->setRowHeight(18);
		$objSheet -> getRowDimension(3)->setRowHeight(18);

		$objSheet->setCellValue("A2" , "1");			// ブロックNo
		$objSheet->setCellValue("B2" , "USUBJID");		// 変数名
		$objSheet->setCellValue("C2" , "登録番号");		// 項目名
		$objSheet->setCellValue("D2" , "-");			// 属性
		$objSheet->setCellValue("E2" , "必須");

		$objSheet->setCellValue("A3" , "1");			// ブロックNo
		$objSheet->setCellValue("B3" , "REGDTC");		// 変数名
		$objSheet->setCellValue("C3" , "登録日時");		// 項目名
		$objSheet->setCellValue("D3" , "-");			// 属性
		$objSheet->setCellValue("E3" , "必須");

		// メタ設定した項目名と型の編集
		$line = 4;		// 開始位置
		foreach($this->metadatas as $key=>$metadata){
			// 各セルの左・右・下罫線（細線）
			$objSheet -> getStyle("A".$line.":H".$line) -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
			// 各セルの文字縦位置を真中
			$objSheet -> getStyle("A".$line.":H".$line) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			// フォントサイズの設定
			$objSheet->getStyle("A".$line.":H".$line)->getFont()->setSize(9);
			// 行の高さ設定
			$objSheet -> getRowDimension($line)->setRowHeight(18);

			$objSheet->setCellValue("A".$line , $metadata["display_pos"]);				// ブロックNo
			$objSheet->setCellValue("B".$line , $metadata["varb_name"]);				// 変数名
			$objSheet->setCellValue("C".$line , $metadata["name"]);						// 項目名
			$objSheet->setCellValue("D".$line , $this->getTypename($metadata["type"]));	// 属性
			if($metadata["require_flag"]){												// 必須
				$objSheet->setCellValue("E".$line , "必須");
			} else {
				$objSheet->setCellValue("E".$line , "－");
			}
			if(!empty($metadata["group_option"])){
				$objSheet->setCellValue("F".$line , $this->group[$metadata["group_option"]]);	// グループ設定
			}
			if($this->counts_data){																// 件数設定
				if($this->counts_data["counts_id"] == $metadata["metadata_id"]){
					$objSheet->setCellValue("G".$line , "件数設定");
				}
			}
			if($this->mdb_obj["title_metadata_id"] == $metadata["metadata_id"]){		// タイトル
				$objSheet->setCellValue("H".$line , "*");
			}
			$line++;

			// 生年月日の場合、年齢行も作成
			if($metadata["type"] == TESTEE_META_TYPE_N_BIRTHDAY){
				// 各セルの左・右・下罫線（細線）
				$objSheet -> getStyle("A".$line.":H".$line) -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
				// 各セルの文字縦位置を真中
				$objSheet -> getStyle("A".$line.":H".$line) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				// フォントサイズの設定
				$objSheet->getStyle("A".$line.":H".$line)->getFont()->setSize(9);
				// 行の高さ設定
				$objSheet -> getRowDimension($line)->setRowHeight(18);

				$objSheet->setCellValue("A".$line , $metadata["display_pos"]);				// ブロックNo
				$objSheet->setCellValue("B".$line , "AGE");									// 変数名
				$objSheet->setCellValue("C".$line , "年齢");								// 項目名
				$objSheet->setCellValue("D".$line , "-");									// 属性
				$objSheet->setCellValue("E".$line , "-");									// 必須
				$line++;
			}
		}
	}

	//「選択肢」シートを編集
	function writeOptions(& $objSheet)	{
		// 選択値別制限件数
		if($this->counts_data["option_counts"]){
			$json_option_counts = json_decode($this->counts_data["option_counts"],true);
			foreach($json_option_counts as $option_count){
				$option_counts[] = $option_count;
			}
		}

		// 選択肢の編集
		$line = 2;		// 開始位置
		foreach($this->metadatas as $key=>$metadata){
			// 選択項目のみに絞る
			if($metadata["type"] == TESTEE_META_TYPE_SECTION 
				|| $metadata["type"] == TESTEE_META_TYPE_MULTIPLE 
				|| $metadata["type"] == TESTEE_META_TYPE_N_GROUP
				|| $metadata["type"] == TESTEE_META_TYPE_N_HOSPITAL
				|| $metadata["type"] == TESTEE_META_TYPE_N_SEX
				|| $metadata["type"] == TESTEE_META_TYPE_N_RADIO
				|| $metadata["type"] == TESTEE_META_TYPE_N_YESNO
				|| $metadata["type"] == TESTEE_META_TYPE_N_APPLICABLE) {
				$code_array = explode("|", $metadata['select_content_code']);		// 選択値コード値
				$i = 0;
				foreach($metadata["select_content_array"] as $option){
					// 様式の調整
					// 各セルの左・右・下罫線（細線）
					$objSheet -> getStyle("A".$line.":H".$line) -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
					// 各セルの文字縦位置を真中
					$objSheet -> getStyle("A".$line.":H".$line) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					// フォントサイズの設定
					$objSheet->getStyle("A".$line.":H".$line)->getFont()->setSize(9);
					// 行の高さ設定
					$objSheet -> getRowDimension($line)->setRowHeight(18);

					$objSheet->setCellValue("A".$line , $metadata["display_pos"]);				// ブロックNo
					$objSheet->setCellValue("B".$line , $metadata["varb_name"]);				// 変数名
					$objSheet->setCellValue("C".$line , $this->getTypename($metadata["type"]));	// 属性
					if(!empty($metadata["group_option"])){
						$objSheet->setCellValue("D".$line , $this->group[$metadata["group_option"]]);	// グループ設定
					}
					if($this->counts_data){														// 件数設定件数
						if($this->counts_data["counts_id"] == $metadata["metadata_id"]){
							if(!$option_counts[$i]){
								$option_counts[$i] = "-";
							}
							$objSheet->setCellValue("E".$line , $option_counts[$i]."/".$this->counts_data["all_counts"]);
						}
					}
					if(!empty($code_array[$i])){														// 件数設定件数
						$objSheet->setCellValue("F".$line , $code_array[$i]);						// コード値
					}
					$objSheet->setCellValue("G".$line , $option);								// コードラベル
					if($this->mdb_obj["title_metadata_id"] == $metadata["metadata_id"]){		// タイトル
						$objSheet->setCellValue("H".$line , "*");
					}
					$line++;
					$i++;
				}
			}
		}
	}

	// 「条件」シートを編集
	function writeCondition(& $objSheet)	{
		// 登録番号・登録日時の編集
		// 各セルの左・右・下罫線（細線）
		$objSheet -> getStyle("A2:N3") -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
		// 各セルの文字縦位置を真中
		$objSheet -> getStyle("A2:N3") -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		// フォントサイズの設定
		$objSheet->getStyle("A2:N3")->getFont()->setSize(9);
		// 行の高さ設定
		$objSheet -> getRowDimension(2)->setRowHeight(18);
		$objSheet -> getRowDimension(3)->setRowHeight(18);

		$objSheet->setCellValue("A2" , "1");			// ブロックNo
		$objSheet->setCellValue("B2" , "USUBJID");		// 変数名
		$objSheet->setCellValue("C2" , "登録番号");		// 項目名

		$objSheet->setCellValue("A3" , "1");			// ブロックNo
		$objSheet->setCellValue("B3" , "REGDTC");		// 変数名
		$objSheet->setCellValue("C3" , "登録日時");		// 項目名

		// 条件の編集
		$line = 4;		// 開始位置
		foreach($this->metadatas as $key=>$metadata){
			// 条件情報の取得
			$condition = null;
			$result = $this->mdbView->getCondition($metadata["metadata_id"]);
			if(count($result) > 0){
				$condition = $result[0];
			}
			// 様式の調整
			// 各セルの左・右・下罫線（細線）
			$objSheet -> getStyle("A".$line.":N".$line) -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
			// 各セルの文字縦位置を真中
			$objSheet -> getStyle("A".$line.":N".$line) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			// フォントサイズの設定
			$objSheet->getStyle("A".$line.":N".$line)->getFont()->setSize(9);
			// 行の高さ設定
			$objSheet -> getRowDimension($line)->setRowHeight(18);

			$objSheet->setCellValue("A".$line , $metadata["display_pos"]);					// ブロックNo
			$objSheet->setCellValue("B".$line , $metadata["varb_name"]);					// 変数名
			$objSheet->setCellValue("C".$line , $metadata["name"]);							// 項目名
			if(!empty($metadata["group_option"])){
				$objSheet->setCellValue("D".$line , $this->group[$metadata["group_option"]]);	// グループ設定
			}
			// 条件が設定されている場合、条件を編集
			if($condition){
				$objSheet->setCellValue("E".$line , $this->getFugo($condition["cond1_fugo1"]).$condition["cond1_value1"]);		// 条件11
				if($condition["cond1_fugo2"]){
					$objSheet->setCellValue("F".$line , $this->getFugo($condition["cond1_fugo2"]).$condition["cond1_value2"]);	// 条件12
					$objSheet->setCellValue("G".$line , $this->getEW($condition["cond1_ew"]));						// エラー・ワーニング1
					if($condition["cond2_ew"]){
						$objSheet->setCellValue("H".$line , $this->getFugo($condition["cond2_fugo1"]).$condition["cond2_value1"]);	// 条件21
						if($condition["cond2_fugo2"]){
							$objSheet->setCellValue("I".$line , $this->getFugo($condition["cond2_fugo2"]).$condition["cond2_value2"]);	// 条件22
							$objSheet->setCellValue("J".$line , $this->getEW($condition["cond2_ew"]));				// エラー・ワーニング2
						} else {
							$objSheet->setCellValue("I".$line , $this->getEW($condition["cond2_ew"]));				// エラー・ワーニング2
						}
					}
				} else {
					$objSheet->setCellValue("F".$line , $this->getEW($condition["cond1_ew"]));						// エラー・ワーニング1
					if($condition["cond2_ew"]){
						if($condition["cond1_fugo2"]){
							$objSheet->setCellValue("G".$line , $this->getFugo($condition["cond2_fugo1"]).$condition["cond2_value1"]);	// 条件21
							if($condition["cond2_fugo2"]){
								$objSheet->setCellValue("H".$line , $this->getFugo($condition["cond2_fugo2"]).$condition["cond2_value2"]);	// 条件22
								$objSheet->setCellValue("I".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
							} else {
								$objSheet->setCellValue("H".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
							}
						} else {
							$objSheet->setCellValue("G".$line , $this->getFugo($condition["cond2_fugo1"]).$condition["cond2_value1"]);	// 条件21
							if($condition["cond2_fugo2"]){
								$objSheet->setCellValue("H".$line , $this->getFugo($condition["cond2_fugo2"]).$condition["cond2_value2"]);	// 条件22
								$objSheet->setCellValue("I".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
							} else {
								$objSheet->setCellValue("H".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
							}
						}
					}
				}
			}
			// ラジオボタンで正解が登録されている場合、条件を編集
			if($metadata["type"] == TESTEE_META_TYPE_N_RADIO
				|| $metadata["type"] == TESTEE_META_TYPE_N_YESNO
				|| $metadata["type"] == TESTEE_META_TYPE_N_APPLICABLE) {
				if($metadata["correct"]){
					$correct = explode("|", $metadata["correct"]);
					$select_content_code = explode("|", $metadata["select_content_code"]);
					$correct_count = 0;
					if(count($correct) > 0){
						for($i = 0; $i < count($correct); $i++){
							if($correct[$i]){
								$cell = chr(69 + $correct_count);
								$objSheet->setCellValue($cell.$line , $this->lang["mdb_fugo_eq"].$select_content_code[$i]);		// 条件
								$correct_count++;
							}
						}
						$cell = chr(69 + $correct_count);
						$objSheet->setCellValue($cell.$line , $this->lang["mdb_ew_e"]);		// エラー・ワーニング
					}
				}
			}
			$line++;

			// 生年月日の場合、年齢行も作成
			if($metadata["type"] == TESTEE_META_TYPE_N_BIRTHDAY){
				// 各セルの左・右・下罫線（細線）
				$objSheet -> getStyle("A".$line.":N".$line) -> getBorders() -> getAllBorders() -> setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
				// 各セルの文字縦位置を真中
				$objSheet -> getStyle("A".$line.":N".$line) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				// フォントサイズの設定
				$objSheet->getStyle("A".$line.":N".$line)->getFont()->setSize(9);
				// 行の高さ設定
				$objSheet -> getRowDimension($line)->setRowHeight(18);

				$objSheet->setCellValue("A".$line , $metadata["display_pos"]);				// ブロックNo
				$objSheet->setCellValue("B".$line , "AGE");									// 変数名
				$objSheet->setCellValue("C".$line , "年齢");								// 属性
				// 生年月日と同じ内容を編集
				if($condition){
					$objSheet->setCellValue("E".$line , $this->getFugo($condition["cond1_fugo1"]).$condition["cond1_value1"]);		// 条件11
					if($condition["cond1_fugo2"]){
						$objSheet->setCellValue("F".$line , $this->getFugo($condition["cond1_fugo2"]).$condition["cond1_value2"]);	// 条件12
						$objSheet->setCellValue("G".$line , $this->getEW($condition["cond1_ew"]));						// エラー・ワーニング1
						if($condition["cond2_ew"]){
							$objSheet->setCellValue("H".$line , $this->getFugo($condition["cond2_fugo1"]).$condition["cond2_value1"]);	// 条件21
							if($condition["cond2_fugo2"]){
								$objSheet->setCellValue("I".$line , $this->getFugo($condition["cond2_fugo2"]).$condition["cond2_value2"]);	// 条件22
								$objSheet->setCellValue("J".$line , $this->getEW($condition["cond2_ew"]));				// エラー・ワーニング2
							} else {
								$objSheet->setCellValue("I".$line , $this->getEW($condition["cond2_ew"]));				// エラー・ワーニング2
							}
						}
					} else {
						$objSheet->setCellValue("F".$line , $this->getEW($condition["cond1_ew"]));						// エラー・ワーニング1
						if($condition["cond2_ew"]){
							if($condition["cond1_fugo2"]){
								$objSheet->setCellValue("G".$line , $this->getFugo($condition["cond2_fugo1"]).$condition["cond2_value1"]);	// 条件21
								if($condition["cond2_fugo2"]){
									$objSheet->setCellValue("H".$line , $this->getFugo($condition["cond2_fugo2"]).$condition["cond2_value2"]);	// 条件22
									$objSheet->setCellValue("I".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
								} else {
									$objSheet->setCellValue("H".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
								}
							} else {
								$objSheet->setCellValue("G".$line , $this->getFugo($condition["cond2_fugo1"]).$condition["cond2_value1"]);	// 条件21
								if($condition["cond2_fugo2"]){
									$objSheet->setCellValue("H".$line , $this->getFugo($condition["cond2_fugo2"]).$condition["cond2_value2"]);	// 条件22
									$objSheet->setCellValue("I".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
								} else {
									$objSheet->setCellValue("H".$line , $this->getEW($condition["cond2_ew"]));			// エラー・ワーニング2
								}
							}
						}
					}
				}
				$line++;
			}
		}
	}

    /**
     * @param メタタイプ
     */
    function getTypename($in_type) {
		$type = null;
		switch($in_type){
			case TESTEE_META_TYPE_IMAGE :
				$type = $this->lang["mdb_type_image"];			//"画像";
				break;
			case TESTEE_META_TYPE_TEXT :
				$type = $this->lang["mdb_type_text"];			//"テキスト";
				break;
			case TESTEE_META_TYPE_TEXTAREA :
				$type = $this->lang["mdb_type_textarea"];		//"テキストエリア";
				break;
			case TESTEE_META_TYPE_LINK :
				$type = $this->lang["mdb_type_link"];			//"リンク";
				break;
			case TESTEE_META_TYPE_SECTION :
				$type = $this->lang["mdb_type_section"];		//"選択式(択一)";
				break;
			case TESTEE_META_TYPE_FILE :
				$type = $this->lang["mdb_type_file"];			//"ファイル";
				break;
			case TESTEE_META_TYPE_WYSIWYG :
				$type = $this->lang["mdb_type_wysiwyg"];		//"WYSIWYGテキスト";
				break;
			case TESTEE_META_TYPE_AUTONUM :
				$type = $this->lang["mdb_type_autonum"];		//"自動採番";
				break;
			case TESTEE_META_TYPE_MAIL :
				$type = $this->lang["mdb_type_mail"];			//"メール";
				break;
			case TESTEE_META_TYPE_DATE :
				$type = $this->lang["mdb_type_date"];			//"日付";
				break;
			case TESTEE_META_TYPE_INSERT_TIME :
				$type = $this->lang["mdb_type_insert_time"];	//"登録日時";
				break;
			case TESTEE_META_TYPE_UPDATE_TIME :
				$type = $this->lang["mdb_type_update_time"];	//"更新日時";
				break;
			case TESTEE_META_TYPE_MULTIPLE :
				$type = $this->lang["mdb_type_multiple"];		//"選択式(複数)";
				break;
			case TESTEE_META_TYPE_N_RADIO :
				$type = $this->lang["mdb_type_n_radio"];		//"選択式(ラジオボタン)";
				break;
			case TESTEE_META_TYPE_N_YESNO :
				$type = $this->lang["mdb_type_n_yesno"];		//"選択式(はい・いいえ)";
				break;
			case TESTEE_META_TYPE_N_APPLICABLE :
				$type = $this->lang["mdb_type_n_applicable"];	//"選択式(該当せず・該当)";
				break;
			case TESTEE_META_TYPE_N_NUMERIC :
				$type = $this->lang["mdb_type_n_numeric"];		//"チェック付数値";
				break;
			case TESTEE_META_TYPE_N_DATE :
				$type = $this->lang["mdb_type_n_date"];			//"チェック付日付";
				break;
			case TESTEE_META_TYPE_N_COMMENT :
				$type = $this->lang["mdb_type_n_comment"];		//"コメント";
				break;
			case TESTEE_META_TYPE_N_HOSPITAL :
				$type = $this->lang["mdb_type_n_hospital"];		//"施設名";
				break;
			case TESTEE_META_TYPE_N_BIRTHDAY :
				$type = $this->lang["mdb_type_n_birthday"];		//"生年月日";
				break;
			case TESTEE_META_TYPE_N_SEX :
				$type = $this->lang["mdb_type_n_sex"];			//"性別";
				break;
			case TESTEE_META_TYPE_N_STATURE :
				$type = $this->lang["mdb_type_n_stature"];		//"身長";
				break;
			case TESTEE_META_TYPE_N_WEIGHT :
				$type = $this->lang["mdb_type_n_weight"];		//"体重";
				break;
			case TESTEE_META_TYPE_N_CREATININE :
				$type = $this->lang["mdb_type_n_creatinine"];	//"血清クレアチニン値";
				break;
			case TESTEE_META_TYPE_N_BSA :
				$type = $this->lang["mdb_type_n_bsa"];			//"BSA";
				break;
			case TESTEE_META_TYPE_N_CCR :
				$type = $this->lang["mdb_type_n_ccr"];			//"Ccr";
				break;
			case TESTEE_META_TYPE_N_GROUP :
				$type = $this->lang["mdb_type_n_group"];		//"グループ";
				break;
		}
		return $type;
    }

    /**
     * @param 符号
     */
    function getFugo($in_fugo) {
		$fugo = null;
		switch($in_fugo){
			case TESTEE_META_FUGO_GE :
				$fugo = $this->lang["mdb_fugo_ge"];		//"≧";
				break;
			case TESTEE_META_FUGO_G :
				$fugo = $this->lang["mdb_fugo_g"];		//"＞";
				break;
			case TESTEE_META_FUGO_LE :
				$fugo = $this->lang["mdb_fugo_le"];		//"≦";
				break;
			case TESTEE_META_FUGO_L :
				$fugo = $this->lang["mdb_fugo_l"];		//"＜";
				break;
			case TESTEE_META_FUGO_EQ :
				$fugo = $this->lang["mdb_fugo_eq"];		//"＝";
				break;
			case TESTEE_META_FUGO_NE :
				$fugo = $this->lang["mdb_fugo_ne"];		//"≠";
				break;
		}
		return $fugo;
    }

    /**
     * @param エラー・ワーニング
     */
    function getEW($in_ew) {
		$ew = null;
		switch($in_ew){
			case TESTEE_META_EW_E :
				$ew = $this->lang["mdb_ew_e"];		//"エラー";
				break;
			case TESTEE_META_EW_W :
				$ew = $this->lang["mdb_ew_w"];		//"ワーニング";
				break;
		}
		return $ew;
    }

    /**
     * @param $objPHPExcel
     */
    protected function executeDownload(PHPExcel $objPHPExcel, $file_name)
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        //header("Content-Disposition: attachment;filename=" . $file_name);
        header("Content-Disposition: attachment;filename=" . mb_convert_encoding($file_name, _CLIENT_OS_CHARSET, "UTF-8"));
        header("Content-Transfer-Encoding: binary ");
		$writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$writer->save('php://output');
    }
}
?>
