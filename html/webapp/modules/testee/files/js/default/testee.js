var clsTestee = Class.create();
var mdbCls = Array();

clsTestee.prototype = {
	/**
	 * 初期処理
	 *
	 * @param	id	ID
	 * @return  none
	 **/
	initialize: function(id) {
		this.id = id;
		this.currentMdbId = null;
		this.testee_id = null;
		this.popupLnk = null;
		this.dndMgrMetadata = null;
		this.dndCustomDrag = null;
		this.dndCustomDropzone = null;
		this.calendarFm = null;
		this.calendarTo = null;
		this.textarea = new Object();
		this.calendar = new Object();
		this.errorSeparator = null;
		this.testee_adjustment_id = null;

		this.kentai_a1 = null;
		this.kentai_b1 = null;
		this.kentai_b2 = null;
	},

	initMetadatas: function(el, testee_id) {
		this.testee_id = testee_id;
		commonCls.moveAutoPosition($(this.id).parentNode);
		//項目追加へfocus移動
		commonCls.focus(el);
		// ドラッグ
		this.dndCustomDrag = Class.create();
		this.dndCustomDrag.prototype = Object.extend((new compDraggable), {
			endDrag: function() {
				// 高さ変更
				var drop_params = this.getParams();
		    	var id = drop_params['id'];
		    	var top_drop_event_el = $("mdb_drop_event1"+id);
				var left_drop_event_el = $("mdb_drop_event2"+id);
				var right_drop_event_el = $("mdb_drop_event3"+id);
				var bottom_drop_event_el = $("mdb_drop_event4"+id);
				top_drop_event_el.style.height = "10px";
				left_drop_event_el.style.height = "10px";
				right_drop_event_el.style.height = "10px";
				bottom_drop_event_el.style.height = "10px";
				left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
				right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
				var draggable = this.htmlElement;
       			Element.setStyle(draggable, {opacity:"1"});
			}
		});

		// ドロップ
		this.dndCustomDropzone = Class.create();
		this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
			showHover: function(event) {
				var htmlElement = this.getHTMLElement();
				if ( this._showHover(htmlElement) )
					return;
				if(Element.hasClassName(htmlElement, "mdb_drop_event")) {
					this.showChgSeqHover(event, "top");
				} else {
					this.showChgSeqHover(event);
				}
			},

			hideHover: function(event) {
				this.hideChgSeqHover(event);
			},

			accept: function(draggableObjects) {
				var htmlElement = this.getHTMLElement();
				if(Element.hasClassName(htmlElement, "mdb_drop_event")) {
					//ドロップエレメント変更
					var row_len = htmlElement.previousSibling.rows.length;
					if(row_len != 0) {
						var row_el = htmlElement.previousSibling.rows[row_len - 1];
						this.acceptChgSeq(draggableObjects, row_el, "bottom");
					} else {
						var theGUI = draggableObjects[0].getDroppedGUI();
						if ( Element.getStyle( theGUI, "position" ) == "absolute" )	{
							theGUI.style.position = "static";
							theGUI.style.top = "";
							theGUI.style.left = "";
						}
						var test = htmlElement.previousSibling;
						var tbody = Element.getChildElement(htmlElement.previousSibling);
						if(tbody == null || tbody.tagName.toLowerCase() != "tbody") {
							tbody = htmlElement.previousSibling;
						}
						tbody.appendChild(theGUI);
					}
				} else {
					this.acceptChgSeq(draggableObjects);
				}
			},

			save: function(draggableObjects) {
				if(this.ChgSeqPosition == null) {
					return false;
				}
				var drop_params = this.getParams();
		    	var id = drop_params['id'];
		    	var top_el = $(id);
		    	var pos = this.ChgSeqPosition;
		    	var drop_el = this.getHTMLElement();				// ドロップ対象エレメント
		    	var display_pos = 1;
		    	var drag_el = draggableObjects[0].getHTMLElement();		// ドラッグ対象エレメント
		    	var drag_metadata_id = drag_el.id.replace("mdb_chg_row"+ id + "_","");
		    	var parent_table = null;
		    	if(Element.hasClassName(drop_el, "mdb_drop_event")) {
		    		parent_table = drop_el.previousSibling;
		    		var row_len = drop_el.previousSibling.rows.length;
		    		if(row_len != 0) {
		    			pos = "bottom";
		    			drop_el = drop_el.previousSibling.rows[row_len - 1];
		    			var drop_metadata_id = drop_el.id.replace("mdb_chg_row"+ id + "_","");
		    			if(drop_metadata_id == drag_metadata_id) {
		    				return;
		    			}
		    		} else {
		    			var drop_metadata_id = drag_metadata_id;
		    		}

		    	} else {
		    		parent_table = Element.getParentElement(drop_el, 2);
		    		var drop_metadata_id = drop_el.id.replace("mdb_chg_row"+ id + "_","");
		    	}

		    	if(Element.hasClassName(parent_table, "mdb_drop_pos_1")) {
	    			display_pos = 1;
	    		}else if(Element.hasClassName(parent_table, "mdb_drop_pos_2")) {
	    			display_pos = 2;
	    		}else if(Element.hasClassName(parent_table, "mdb_drop_pos_3")){
	    			display_pos = 3;
	    		}else if(Element.hasClassName(parent_table, "mdb_drop_pos_4")){
	    			display_pos = 4;
	    		}

		    	var chgseq_params = new Object();
		    	chgseq_params["param"] = {"action":"testee_action_edit_metadataseq",
		    										"drag_metadata_id":drag_metadata_id,
													"drop_metadata_id":drop_metadata_id,
													"position":pos,
													"display_pos":display_pos
													};
				chgseq_params["method"] = "post";
				chgseq_params["top_el"] = top_el;
				chgseq_params["loading_el"] = drag_el;

				commonCls.send(chgseq_params);
				return true;
			}
		});

		var edit_top_el = $("mdb_metadata_setting"+ this.id);
		this.dndMgrMetadata = new compDragAndDrop();
		this.dndMgrMetadata.registerDraggableRange(edit_top_el);

		var top_drop_event_el = $("mdb_drop_event1"+this.id);
		var left_drop_event_el = $("mdb_drop_event2"+this.id);
		var right_drop_event_el = $("mdb_drop_event3"+this.id);
		var bottom_drop_event_el = $("mdb_drop_event4"+this.id);
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(top_drop_event_el, {"id":this.id}));
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(left_drop_event_el, {"id":this.id}));
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(right_drop_event_el, {"id":this.id}));
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(bottom_drop_event_el, {"id":this.id}));
		//高さ指定
		left_drop_event_el.style.height = "10px";
		left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
		right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
		bottom_drop_event_el.style.height = "10px";

		var metadata_rowfields = Element.getElementsByClassName(edit_top_el, "mdb_chg_seq");
		metadata_rowfields.each(function(row_el) {
			var top_row_el = Element.getParentElementByClassName(row_el,"mdb_chg_row");
			this.dndMgrMetadata.registerDraggable(new this.dndCustomDrag(top_row_el, row_el, {"id":this.id}));
			this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(top_row_el, {"id":this.id}));
		}.bind(this));
	},
	initCalendar: function() {
		this.calendarFm = new compCalendar(this.id, "mdb_search_date_from" + this.id);
		this.calendarTo = new compCalendar(this.id, "mdb_search_date_to" + this.id);
	},
	initKentaiCalendar: function() {
		this.kentai_a1 = new compCalendar(this.id, "mdb_metadatas_kentai_a1" + this.id);
		this.kentai_b1 = new compCalendar(this.id, "mdb_metadatas_kentai_b1" + this.id);
		this.kentai_b2 = new compCalendar(this.id, "mdb_metadatas_kentai_b2" + this.id);
	},
	editCancel: function() {
		commonCls.sendView(this.id, "testee_view_main_init");
	},
	checkCurrent: function() {
		var currentRow = $("mdb_current_row" + this.currentMdbId + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("mdb_current" + this.currentMdbId + this.id);
		current.checked = true;
	},
	referenceMdb: function(event, mdb_id) {
		var params = new Object();
		params["action"] = "testee_view_main_init";
		params["testee_id"] = mdb_id;
		params["prefix_id_name"] = "popup_mdb_reference" + mdb_id;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	},
	/* 項目追加 */
	initPopupMetadata: function(form_el) {
//		commonCls.focus(form_el.name);		フォーカスを変数名に変更	EddyK
		commonCls.focus(form_el.varb_name);
	},
	showPopupMetadata: function(event, metadata_id) {
		metadata_id = (metadata_id == undefined) ? 0 : metadata_id;
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_edit_metadata_detail",
						"testee_id":this.testee_id,
						"metadata_id":metadata_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	showPopupImport: function(event, testee_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_edit_import_init",
						"testee_id":testee_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
// 件数設定　EddyK
	showPopupCounts: function(event, testee_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_edit_counts_counts",
						"testee_id":testee_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
// 件数設定　EddyK
	setCounts: function() {
		// POSTする画面のシリアライズ
		var fromElement = $("counts_form" + this.id);
		var messageBody = Form.serialize(fromElement);

		// POSTオプションの設定
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
								commonCls.removeBlock(this.id);
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_edit_counts&" + Form.serialize(fromElement), option);

	},

// 選択値を取得 EddyK
	getOptions: function(testee_id) {
		var select_el = document.getElementById("mdb_counts"+ this.id);
		var counts_id = select_el.options[select_el.selectedIndex].value;

		var action_param = new Object();
		action_param = {
			"action":"testee_view_edit_counts_options",
			"counts_id":counts_id,
			"testee_id":testee_id
		};

		var target_el = "mdb_counts_option"+ this.id;

		var target_param = new Object();
		target_param = {
			"target_el":document.getElementById(target_el),
			"loading_el":null
		};

		commonCls.sendView(this.id, action_param, target_param);
	},

// 関連日付条件設定　EddyK
	showPopupDatecheck: function(event, testee_id, condition_id) {
		condition_id = (condition_id == undefined) ? 0 : condition_id;
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_edit_datecheck_detail",
						"testee_id":testee_id,
						"condition_id":condition_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
// 関連日付条件追加　EddyK
	addDatecheck: function(this_el, block_id) {
		var top_el = $(this.id);
		var form_el = $("mdb_addcheckdate_form"+this.id);
		var testee_id = form_el.testee_id.value;
		//パラメータ設定
		var add_params = new Object();
		add_params["method"] = "post";
		add_params["param"] = "action=testee_action_edit_adddatecheck&prefix_id_name=popup&"+ Form.serialize(form_el);
		add_params["top_el"] = top_el;
		add_params["loading_el"] = top_el;
		//add_params["target_el"] = top_el;
		add_params["callbackfunc"] = function(res) {
			//親をリロード
			commonCls.removeBlock(this.id);
			commonCls.sendView("_"+block_id, {'action':'testee_view_edit_datecheck_list','testee_id':testee_id});
		}.bind(this);
		add_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.focus($(id));
		}.bind(this);
		commonCls.send(add_params);
	},
// 関連日付条件の削除　EddyK
	delDatecheck: function(testee_id, condition_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var top_el = $(this.id);
		var del_params = new Object();
		del_params["method"] = "post";
		del_params["param"] = "action=testee_action_edit_deldatecheck&condition_id=" + condition_id + "&testee_id=" + testee_id;
		del_params["top_el"] = top_el;
		del_params["callbackfunc"] = function(res) {
			commonCls.sendView(this.id, {'action':'testee_view_edit_datecheck_list','testee_id':this.testee_id});
		}.bind(this);
		del_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.sendView(this.id, {'action':'testee_view_edit_datecheck_list','testee_id':this.testee_id});
		}.bind(this);
		commonCls.send(del_params);
	},
	/* メタ設定画面でのメタタイプ変更 */
	chgMetadataEditType: function(this_el) {
		var form_el = $("mdb_addmetadata_form"+this.id);
		var metadata_id = form_el.metadata_id.value;	// EddyK  新規か更新かの判断
		// 標準での選択肢欄を非表示にする
/*	EddyK
		var target_el = this_el.nextSibling;
		commonCls.displayNone(target_el);
		commonCls.displayNone(target_el.nextSibling);
*/
		var target_el = $('mdb_option1' + this.id);
		commonCls.displayNone(target_el);

		// はい・いいえタイプの選択肢欄を非表示にする			EddyK
		var option2_el = $('mdb_option2' + this.id);
		commonCls.displayNone(option2_el);
		// 該当せず・該当タイプの選択肢欄を非表示にする			EddyK
		var option3_el = $('mdb_option3' + this.id);
		commonCls.displayNone(option3_el);

		// 条件式・コメント・グループの入力エリアを非表示にする	EddyK
		var cond_el = $('mdb_condition' + this.id);
		commonCls.displayNone(cond_el);
		var comment_el = $('mdb_comment' + this.id);
		commonCls.displayNone(comment_el);
		var group_el = $('mdb_group' + this.id);
		commonCls.displayNone(group_el);

		// ラジオボタンタイプの正解・選択肢コード値欄
		var option = document.getElementById("mdb_metadata_options1" + this.id);
		var options = option.children;			//table
		for (var i = 0; i < options.length; i++) {
			// ラジオボタン（正解）の入力エリアを非表示にする　						EddyK
			var correct_el = options[i].children[0].children[0].children[1];
			commonCls.displayNone(correct_el);
			// ラジオボタン（選択肢コード値）の入力エリアを非表示にする				EddyK
			var select_content_code = options[i].children[0].children[0].children[2];
			commonCls.displayNone(select_content_code);
		}
		// はい・いいえタイプの正解・選択肢コード値欄
		var option = document.getElementById("mdb_metadata_options2" + this.id);
		var options2 = option.children;			//table
		for (var i = 0; i < options2.length; i++) {
			// はい・いいえ（正解）の入力エリアを非表示にする　						EddyK
			var correct_el = options2[i].children[0].children[0].children[1];
			commonCls.displayNone(correct_el);
			// はい・いいえ（選択肢コード値）の入力エリアを非表示にする				EddyK
			var select_content_code = options2[i].children[0].children[0].children[2];
			commonCls.displayNone(select_content_code);
		}
		// 該当せず・該当タイプの正解・選択肢コード値欄
		var option = document.getElementById("mdb_metadata_options3" + this.id);
		var options3 = option.children;			//table
		for (var i = 0; i < options3.length; i++) {
			// 該当せず・該当（正解）の入力エリアを非表示にする　						EddyK
			var correct_el = options3[i].children[0].children[0].children[1];
			commonCls.displayNone(correct_el);
			// 該当せず・該当（選択肢コード値）の入力エリアを非表示にする				EddyK
			var select_content_code = options3[i].children[0].children[0].children[2];
			commonCls.displayNone(select_content_code);
		}

		// ルームに参加しているユーザの施設を取り込むボタンを非表示にする
		var mdb_option_select_room_hospital_el = $('mdb_option_select_room_hospital' + this.id);
		commonCls.displayNone(mdb_option_select_room_hospital_el);

		// 表示単位を非表示にする
		var mdb_view_unit_el = $('mdb_view_unit' + this.id);
		commonCls.displayNone(mdb_view_unit_el);

		// メール設定を非表示にする
		var mdb_mail_send_days = $('mdb_mail_send_days' + this.id);
		commonCls.displayNone(mdb_mail_send_days);
		var mdb_mail_body = $('mdb_mail_body' + this.id);
		commonCls.displayNone(mdb_mail_body);
		var mail_send_target = $('mail_send_target' + this.id);
		commonCls.displayNone(mail_send_target);
		var mdb_add_mail_send = $('mdb_add_mail_send' + this.id);
		commonCls.displayNone(mdb_add_mail_send);
		var mdb_mail_users = $('mdb_mail_users' + this.id);
		commonCls.displayNone(mdb_mail_users);


		var title_flag_el = $('mdb_title_flag' + this.id);
		var title_metadata_el = $('mdb_metadata_title_metadata_flag' + this.id);
		var require_el = $('mdb_metadata_require_flag' + this.id);
		var list_el = $('mdb_metadata_list_flag' + this.id);
		var sort_el = $('mdb_metadata_sort_flag' + this.id);
		var name_el = $('mdb_metadata_name_flag' + this.id);
		var search_el = $('mdb_metadata_search_flag' + this.id);
		var password_el = $('mdb_metadata_file_password_flag' + this.id);
		var count_el = $('mdb_metadata_file_count_flag' + this.id);

		var group_exist = $('mdb_group_exist' + this.id);

		if(title_flag_el.value == "0") {
			title_metadata_el.disabled = false;
			Element.removeClassName(title_metadata_el.parentNode, "disable_lbl");
		}

		require_el.disabled = false;
		if (title_metadata_el.checked) {
			require_el.checked = true;
			require_el.disabled = true;
			Element.addClassName(require_el, "disable_lbl");
		} else {
			Element.removeClassName(require_el.parentNode, "disable_lbl");
		}

		if(list_el.checked) {
			sort_el.disabled = false;
			Element.removeClassName(sort_el.parentNode, "disable_lbl");
		}

		name_el.disabled = false;
		Element.removeClassName(name_el.parentNode, "disable_lbl");
		search_el.disabled = false;
		Element.removeClassName(search_el.parentNode, "disable_lbl");

		this._disableCheckItem(password_el);
		this._disableCheckItem(count_el);

		switch (this_el.value) {
			case "0":		// image
				this._disableCheckItem(name_el);
				this._disableCheckItem(search_el);
				break;
			case "5":		// file
				var password_hidden_el = $('mdb_metadata_file_password_flag_hidden' + this.id);
				if (password_hidden_el.value == "1") {
					password_el.checked = true;
				}
				password_el.disabled = false;
				Element.removeClassName(password_el.parentNode, "disable_lbl");

				var count_hidden_el = $('mdb_metadata_file_count_flag_hidden' + this.id);
				if (count_hidden_el.value == "1") {
					count_el.checked = true;
				}
				count_el.disabled = false;
				Element.removeClassName(count_el.parentNode, "disable_lbl");

				this._disableCheckItem(search_el);
				break;
			case "4":		// section
			case "12":		// multiple
				// 標準の選択肢タイプの選択肢数をセット		EddyK
				form_el.options_len.value = parseInt(form_el.options1_len.value);
				commonCls.displayVisible(target_el);
				break;
			case "20":		// hospital NEW
				var mdb_option_select_room_hospital_el = $('mdb_option_select_room_hospital' + this.id);
				commonCls.displayVisible(mdb_option_select_room_hospital_el);
			case "22":		// sex NEW
			case "28":		// group NEW
				// 標準の選択肢タイプの選択肢数をセット		EddyK
				form_el.options_len.value = parseInt(form_el.options1_len.value);
				commonCls.displayVisible(target_el);
				// 選択肢コード値欄の表示	EddyK
				for (var i = 0; i < options.length; i++) {
					var select_content_code = options[i].children[0].children[0].children[2];
					commonCls.displayVisible(select_content_code);
				}
				break;
			case "30":		// radio NEW
				// 標準の選択肢タイプの選択肢数をセット		EddyK
				form_el.options_len.value = parseInt(form_el.options1_len.value);
				commonCls.displayVisible(target_el);
				// 正解欄の表示				EddyK
				for (var i = 0; i < options.length; i++) {
					var correct_el = options[i].children[0].children[0].children[1];
					commonCls.displayVisible(correct_el);
				}
				// 選択肢コード値欄の表示	EddyK
				for (var i = 0; i < options.length; i++) {
					var select_content_code = options[i].children[0].children[0].children[2];
					commonCls.displayVisible(select_content_code);
				}
				break;
			case "34":		// yesno NEW
				// はい・いいえタイプの選択肢数をセット		EddyK
				form_el.options_len.value = parseInt(form_el.options2_len.value);
				commonCls.displayVisible(option2_el);
				// 正解欄の表示				EddyK
				for (var i = 0; i < options2.length; i++) {
					var correct_el = options2[i].children[0].children[0].children[1];
					commonCls.displayVisible(correct_el);
				}
				// 選択肢コード値欄の表示	EddyK
				for (var i = 0; i < options2.length; i++) {
					var select_content_code = options2[i].children[0].children[0].children[2];
					commonCls.displayVisible(select_content_code);
				}
				break;
			case "35":		// applicable NEW
				// 該当せず・該当タイプの選択肢数をセット		EddyK
				form_el.options_len.value = parseInt(form_el.options3_len.value);
				commonCls.displayVisible(option3_el);
				// 正解欄の表示				EddyK
				for (var i = 0; i < options3.length; i++) {
					var correct_el = options3[i].children[0].children[0].children[1];
					commonCls.displayVisible(correct_el);
				}
				// 選択肢コード値欄の表示	EddyK
				for (var i = 0; i < options3.length; i++) {
					var select_content_code = options3[i].children[0].children[0].children[2];
					commonCls.displayVisible(select_content_code);
				}
				break;
			case "7":		// autonum
				this._disableCheckItem(require_el);
				break;
			case "9":		// date
			case "32":		// date NEW
				commonCls.displayVisible(mdb_mail_send_days);
				commonCls.displayVisible(mdb_mail_body);
				commonCls.displayVisible(mail_send_target);
				commonCls.displayVisible(mdb_add_mail_send);
				//commonCls.displayVisible(mdb_mail_users);
			case "21":		// birth_day NEW
				this._disableCheckItem(search_el);
				break;
			case "10":		// insert_time
			case "11":		// update_time
				this._disableCheckItem(require_el);
				this._disableCheckItem(search_el);
				break;
			case "31":		// numeric NEW
			case "33":		// comment NEW
			case "23":		// stature NEW
			case "24":		// weight NEW
			case "25":		// creatinine NEW
			case "26":		// bsa NEW
			case "27":		// ccr NEW
				this._disableCheckItem(search_el);
				break;
		}
		// 条件設定欄の表示								EddyK
		switch (this_el.value) {
			case "32":		// date NEW
			case "21":		// birth_day NEW
			case "31":		// numeric NEW
			case "23":		// stature NEW
			case "24":		// weight NEW
			case "25":		// creatinine NEW
			case "26":		// bsa NEW
			case "27":		// ccr NEW
				commonCls.displayVisible(cond_el);
				break;
		}
		// group NEW	グループ欄の表示				EddyK
		if(group_exist.value){
			switch (this_el.value) {
				case "20":		// hospital NEW
				case "21":		// birth_day NEW
				case "22":		// sex NEW
				case "23":		// stature NEW
				case "24":		// weight NEW
				case "25":		// creatinine NEW
				case "26":		// bsa NEW
				case "27":		// ccr NEW
				case "28":		// group NEW
				case "33":		// comment NEW
				case "7":		// autonum
				case "10":		// insert_time
				case "11":		// update_time
					break;
				default:
					commonCls.displayVisible(group_el);
					break;
			}
		}
		// comment NEW	コメント欄の表示				EddyK
		if(this_el.value == "33") {
				commonCls.displayVisible(comment_el);
				require_el.checked = false;
				require_el.disabled = true;
		}

		// 単位の表示変更
		switch (this_el.value) {
			case "21":		// birth_day NEW
				$("mdb_unit1"+this.id).innerHTML = "（歳）";
				$("mdb_unit2"+this.id).innerHTML = "（歳）";
				break;
			case "23":		// stature NEW
				$("mdb_unit1"+this.id).innerHTML = "（cm）";
				$("mdb_unit2"+this.id).innerHTML = "（cm）";
				break;
			case "24":		// weight NEW
				$("mdb_unit1"+this.id).innerHTML = "（kg）";
				$("mdb_unit2"+this.id).innerHTML = "（kg）";
				break;
			case "32":		// date NEW
				$("mdb_unit1"+this.id).innerHTML = "（日数）";
				$("mdb_unit2"+this.id).innerHTML = "（日数）";
				break;
			default:
				$("mdb_unit1"+this.id).innerHTML = "";
				$("mdb_unit2"+this.id).innerHTML = "";
				break;
		}

		// 表示単位の表示
		if(this_el.value == "31") {
			commonCls.displayVisible(mdb_view_unit_el);
		}
	},
	_disableCheckItem: function(el) {
		el.checked = false;
		el.disabled = true;
		Element.addClassName(el.parentNode, "disable_lbl");
	},
	/*選択肢追加 */
	addOption: function(this_el) {
		var form_el = $("mdb_addmetadata_form"+this.id);
		var metadata_id = form_el.metadata_id.value;	// EddyK  新規か更新かの判断
		var type = form_el.type.value;					// EddyK  メタタイプを判定
		form_el.options_len.value = parseInt(form_el.options_len.value) + 1;
		// タイプにより選択肢数のカウントアップを行う		EddyK
		switch(type){
			case "34":		// yesno NEW
				form_el.options2_len.value = parseInt(form_el.options2_len.value) + 1;
				break;
			case "35":		// applicable NEW
				form_el.options3_len.value = parseInt(form_el.options3_len.value) + 1;
				break;
			default:	// 標準の選択肢
				form_el.options1_len.value = parseInt(form_el.options1_len.value) + 1;
				break;
		}
		var top_el = $(this.id);
		var addoption_param = new Object();
		addoption_param["param"] = {
						"action":"testee_view_edit_option_add",
						"iteration":parseInt(form_el.options_len.value) - 1,
						"type":type,				// EddyK RADIOタイプを判定
						"prefix_id_name":"popup"
					};
		addoption_param["callbackfunc"] = function(res) {
			var div_parent = document.createElement("DIV");
			div_parent.innerHTML = res;
			// var options_el = $("mdb_metadata_options" + this.id);		EddyK
			// メタタイプにより要素を変更		EddyK
			switch(type){
				case "34":		// yesno NEW
					var options_el = $("mdb_metadata_options2" + this.id);
					break;
				case "35":		// applicable NEW
					var options_el = $("mdb_metadata_options3" + this.id);
					break;
				default:	// 標準の選択肢
					var options_el = $("mdb_metadata_options1" + this.id);
					break;
			}
			options_el.appendChild(Element.getChildElement(div_parent));
			div_parent = null;
			var inputList = options_el.getElementsByTagName("input");
			commonCls.focus(inputList[inputList.length - 1]);
		}.bind(this);
		addoption_param['top_el'] = top_el;
		addoption_param["loading_el"] = this_el;
		commonCls.send(addoption_param);
	},
	delOption: function(this_el, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		this_el.parentNode.removeChild(this_el);
	},
	/* 項目追加 */
	addMetadata: function(this_el, block_id, alert_on) {
		if (alert_on) {
			var confirmMessage = "すでにデータが存在する試験のグループ設定を変更しようとしています。\nコードを変更・削除した場合、項目に設定されているグループ設定がクリアされる場合があります。";
			if (!confirm(confirmMessage)) {
				return false;
			}
		}
		var top_el = $(this.id);
		var form_el = $("mdb_addmetadata_form"+this.id);
		var testee_id = form_el.testee_id.value;
		//パラメータ設定
		var add_params = new Object();
		add_params["method"] = "post";
		add_params["param"] = "action=testee_action_edit_addmetadata&prefix_id_name=popup&"+ Form.serialize(form_el);
		add_params["top_el"] = top_el;
		add_params["loading_el"] = top_el;
		//add_params["target_el"] = top_el;
		add_params["callbackfunc"] = function(res) {
			//親をリロード
			commonCls.removeBlock(this.id);
			commonCls.sendView("_"+block_id, {'action':'testee_view_edit_metadata_list','testee_id':testee_id});
		}.bind(this);
		add_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.focus($(id));
		}.bind(this);
		commonCls.send(add_params);
	},
	/* 項目削除 */
	delMetadata: function(metadata_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var top_el = $(this.id);
		var del_params = new Object();
		del_params["method"] = "post";
		del_params["param"] = "action=testee_action_edit_delmetadata&metadata_id="+ metadata_id;
		del_params["top_el"] = top_el;
		del_params["callbackfunc"] = function(res) {
			commonCls.sendView(this.id, {'action':'testee_view_edit_metadata_list','testee_id':this.testee_id});
		}.bind(this);
		del_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.sendView(this.id, {'action':'testee_view_edit_metadata_list','testee_id':this.testee_id});
		}.bind(this);
		commonCls.send(del_params);
	},
	styleEdit: function(form_el) {
		var top_el = $(this.id);
		var edit_params = new Object();
		edit_params["param"] = "testee_action_edit_style" + "&"+ Form.serialize(form_el);
		edit_params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id,"testee_view_edit_list");
		}.bind(this);
		edit_params["method"] = "post";
		edit_params["loading_el"] = top_el;
		edit_params["top_el"] = top_el;
		edit_params["target_el"] = top_el;
		commonCls.send(edit_params);
	},
	//汎用データベース選択
	changeCurrent: function(mdb_id) {
		var oldCurrentRow = $("mdb_current_row" + this.currentMdbId + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentMdbId = mdb_id;
		var currentRow = $("mdb_current_row" + this.currentMdbId + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"testee_action_edit_change",
			"testee_id":mdb_id
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "testee_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	//汎用データベース削除
	delMdb: function(mdb_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "testee_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_edit_delete&testee_id=" + mdb_id, params);
	},
	delContent: function(mdb_id, content_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		commonCls.sendPost(this.id, "action=testee_action_main_delcontent&content_id=" + content_id + "&testee_id=" + mdb_id, {"target_el":$('mdb_refresh_target'+this.id)});
	},
	vote: function(mdb_id, content_id) {
		var params = new Object();
		params["top_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.sendRefresh(this.id);
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_vote&content_id=" + content_id + "&testee_id=" + mdb_id, params);
	},
	confirmContent: function(mdb_id, content_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.sendPost(this.id, {"action":"testee_action_main_mail"}, {"loading_el":null});
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_confirm&content_id=" + content_id + "&testee_id=" + mdb_id, params);
	},
	//コメント登録
	postComment: function(form_el) {
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.displayVisible($('mdb_comment' + this.id));
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_comment" + "&" + Form.serialize(form_el), params);
	},
	editComment: function(comment_id, mdb_id, content_id) {
		var div_content = $("comment_content_" + comment_id + this.id);
		var textbox = $("mdb_comment_textarea" + this.id);
		textbox.value = div_content.innerHTML.replace(/\n/ig,"").replace(/(<br(?:.|\s|\/)*?>)/ig,"\n").unescapeHTML();
		var hidden_flag = $("comment_post_id"+this.id);
		hidden_flag.value = comment_id;
		textbox.focus();
		textbox.select();
	},
	deleteComment: function(comment_id, mdb_id, content_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.displayVisible($('mdb_comment' + this.id));
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_delcomment&comment_id=" + comment_id + "&content_id=" + content_id + "&testee_id=" + mdb_id, params);
	},
	//登録フォーム追加
	insertMdb: function(form_el) {
		commonCls.sendPost(this.id, "action=testee_action_edit_create" + "&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},
	//汎用データベース編集
	editMdb: function(form_el) {
		commonCls.sendPost(this.id, "action=testee_action_edit_modify" + "&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},
	toPage: function(el, now_page, position) {
		var form_name = "mdb_page_form"+this.id;
		if(position == "bottom") {
			form_name = form_name + "_bottom";
		}
		var form_el = $(form_name);
		var top_el = $(this.id);
		var params = new Object();
		form_el.now_page.value = now_page;
		params["param"] = "action=testee_view_main_init&"+ Form.serialize(form_el);
		params["top_el"] = top_el;
		params["loading_el"] = el;
		params["target_el"] = top_el;
		commonCls.send(params);
	},
	toSearchPage: function(el, now_page) {
		var form_el = $("mdb_search_form"+this.id);
		form_el.now_page.value = now_page;
		commonCls.sendView(this.id, "action=testee_view_main_search_result" + "&" + Form.serialize(form_el));
	},
	changeActivity: function(element, mdb_id, activity) {
		var elements = element.parentNode.childNodes;
		for (var i = 0, length = elements.length; i < length; i++) {
			if (elements[i] == element) {
				Element.addClassName(elements[i], "display-none");
			} else {
				Element.removeClassName(elements[i], "display-none");
			}
		}
		var post = {
			"action":"testee_action_edit_activity",
			"testee_id":mdb_id,
			"active_flag":activity
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "testee_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	wysiwygInit: function(metadata_id) {
		//テキストエリア
		if (typeof this.textarea[metadata_id] == "undefined") {
			this.textarea[metadata_id] = new compTextarea();
			this.textarea[metadata_id].uploadAction = {
				//unique_id   : 0,
				image    : "testee_action_main_upload_image",
				file     : "testee_action_main_upload_init"
			};
			this.textarea[metadata_id].popupPrefix = "mdb_metadatas_" + metadata_id + this.id;
		}
		this.textarea[metadata_id].textareaShow(this.id, "textarea_" + metadata_id + this.id, "full");
	},
	calendarInit: function(metadata_id) {
		//テキストエリア
		if (typeof this.calendar[metadata_id] == "object") {
			this.calendar[metadata_id] = null;
		}
		if (typeof this.calendar[metadata_id] == "undefined" || this.calendar[metadata_id] == null) {
			this.calendar[metadata_id] = new compCalendar(this.id, "mdb_metadatas_" + metadata_id + this.id);
		}
	},
	calendarInit2: function(metadata_id) {
		//テキストエリア
		if (typeof this.calendar[metadata_id] == "object") {
			this.calendar[metadata_id] = null;
		}
		if (typeof this.calendar[metadata_id] == "undefined" || this.calendar[metadata_id] == null) {
			this.calendar[metadata_id] = new compCalendar(this.id, "birthday_" + this.id);
		}
	},
	setWysiwyg: function(metadata_id, form_el) {
		if (typeof this.textarea[metadata_id] != "undefined") {
			form_el["datas[" + metadata_id + "]"].value = this.textarea[metadata_id].getTextArea();
		}
	},
	showPopupEditPreview: function(event, testee_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_edit_metadata_preview",
						"testee_id":testee_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;

		commonCls.sendPopupView(event, param_popup, params);
	},
	contentSubmit: function (form_el, temp_flag, warning_ok, confirm_ok) {
//	データ登録の際のチェック用アクションの起動
		if(temp_flag == 1) {
			form_el.temporary_flag.value = 1;
		}
		if(this.textarea != null) {
			for (var metadata_id in this.textarea) {
				this.setWysiwyg(metadata_id, form_el);
			}
		}

		// POSTの設定
		var postString = "action=testee_action_main_addcontentvalidate"
					+ "&" + Form.serialize(form_el)
					+ "&warning_ok=" + warning_ok
					+ "&confirm_ok=" + confirm_ok;

		// POSTオプションの設定
		var params = new Object();
		params["target_el"] = $(this.id);

		// 正常処理
		params["callbackfunc"] = function(res){
			var msg = "登録が失敗しました。<br>再度試して頂き、エラーが続くようならばシステム管理者へ連絡して下さい。";
			commonCls.alert(msg);
		}.bind(this);

		// エラー、もしくはワーニング処理
		params["callbackfunc_error"] = function(res) {
			// ワーニング処理
			if ( res.indexOf("[warning]") == 0 ) {

				// 続行を確認して、OK なら続行
				var warning_msg = res.replace( "[warning]", "" );
				if( commonCls.confirm( warning_msg ) ) {
					this.contentSubmit( form_el, temp_flag, 1, 0);
				}
			}
			// 確認処理
			else if ( res.indexOf("[confirm]") == 0 ) {
				var confirm_msg1 = "登録してよろしいですか？\n登録後の変更・削除はできません。";
				if( commonCls.confirm( confirm_msg1 ) ) {
					//var confirm_msg2 = "登録してよろしいですか？";
					//if( commonCls.confirm( confirm_msg2 ) ) {
						this.contentSubmitImpl( form_el, temp_flag, 1, 1);
					//}
				}
			}
			// エラー処理
			else {
				commonCls.alert(res);
			}
		}.bind(this);

		commonCls.sendPost(this.id, postString, params);

	},

	contentSubmitImpl: function (form_el, temp_flag, warning_ok, confirm_ok) {
//	データ登録の際の更新用アクションの起動

		if(temp_flag == 1) {
			form_el.temporary_flag.value = 1;
		}
		if(this.textarea != null) {
			for (var metadata_id in this.textarea) {
				this.setWysiwyg(metadata_id, form_el);
			}
		}
		// messageBodyの設定（追加：warning_ok, confirm_okの付与）
		var messageBody = new Object();
		messageBody["action"] = "testee_action_main_addcontent";
		messageBody["warning_ok"] = warning_ok;
		messageBody["confirm_ok"] = confirm_ok;

		// POSTオプションの設定
		var params = new Object();
		params["param"] = messageBody;
		params["top_el"] = $(this.id);
		params["method"] = "post";
		params['form_prefix'] = "mdb_attachment";

		// 正常処理
		params["callbackfunc"] = function(res){
				var content_id = (form_el.content_id) ? form_el.content_id.value : '';
				commonCls.sendView(this.id, {"action":"testee_view_main_init",'content_id':content_id});
				//commonCls.sendPost(this.id, {"action":"testee_action_main_tedclinkresult"}, {"loading_el":null});	// TEDC連携　EddyK
				commonCls.sendPost(this.id, {"action":"testee_action_main_mail"}, {"loading_el":null});


				//commonCls.alert("更新しました。");

				// POSTオプションの設定
				var params_alloc = new Object();
				params_alloc["target_el"] = $(this.id);

				// エラー、もしくはワーニング処理
				params_alloc["callbackfunc_error"] = function(res_alloc) {
					commonCls.alert(res_alloc);
				}.bind(this);

				commonCls.sendPost(this.id, "action=testee_action_main_allocationview", params_alloc);


		}.bind(this);

		// エラー、もしくはワーニング処理
		params["callbackfunc_error"] = function(file_list,res) {
			form_el.temporary_flag.value = 0;
			// エラー時(File)
			if (!res.match(this.errorSeparator)) {
				commonCls.alert(res);
				return;
			}

			var resArray = res.split(this.errorSeparator);
			var element_id = resArray.shift();

			res = resArray.join("\n");
			commonCls.alert(res);
			$(element_id + this.id).focus();

		}.bind(this);

		commonCls.sendAttachment(params);

	},

	searchMdb: function(form_el) {
		commonCls.sendView(this.id, "action=testee_view_main_search_result&" + Form.serialize(form_el));
	},
	importCsv: function (event, this_el, testee_id) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = {'action': "testee_action_edit_uploadcsv", "testee_id": testee_id};
		params["top_el"] = top_el;
		params["method"] = "post";
		params["loading_el"] = top_el;
		params['form_prefix'] = "mdb_import_attachment";
		params["callbackfunc"] = function(res){
			var msg_div = $('mdb_import_success_result'+this.id);
			commonCls.alert(msg_div.innerHTML);
			$('mdb_import'+this.id).value = null;
			commonCls.removeBlock(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			// エラー時(File)
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	changeTitle: function(title_el) {
		var require_el = $("mdb_metadata_require_flag" + this.id);
		var inputtype_el = $("mdb_inputtype" + this.id);
		if (inputtype_el.value == "7" || inputtype_el.value == "10" || inputtype_el.value == "11") {
			require_el.disabled = true;
			Element.addClassName(require_el.parentNode, "disable_lbl");
			return;
		}
		if(title_el.checked) {
			require_el.checked = true;
			require_el.disabled = true;
			Element.addClassName(require_el.parentNode, "disable_lbl");
		}else {
			require_el.disabled = false;
			Element.removeClassName(require_el.parentNode, "disable_lbl");
		}
	},
	setList: function(list_el) {
		var meta_type_el = $('mdb_inputtype' + this.id);
		var meta_type = meta_type_el.options[meta_type_el.selectedIndex];
		var sort_el = $("mdb_metadata_sort_flag" + this.id);
		if(list_el.checked) {
			sort_el.disabled = false;
			Element.removeClassName(sort_el.parentNode, "disable_lbl");
		}else {
			sort_el.checked = false;
			sort_el.disabled = true;
			Element.addClassName(sort_el.parentNode, "disable_lbl");
		}
	},
	showDataSeqPop: function(event, testee_id) {
		var param_popup = new Object();
		param_popup = {
			"action":"testee_view_main_sequence",
			"testee_id":testee_id,
			"prefix_id_name":"testee_sequence_popup"
		};

		var params = new Object();
		params['top_el'] = $(this.id);
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	closeDataSeqPop: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params['top_el'] = top_el;
		params["callbackfunc"] = function(res){
			commonCls.removeBlock(this.id);
			commonCls.sendView(this.id.replace("_testee_sequence_popup",""), {'action':'testee_view_main_init','sort_metadata':'seq'});
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_chgseq&" + Form.serialize(form_el), params);
	},
	changeSequence: function(drag_id, drop_id, position) {
		var post = {
			"action":"testee_action_main_sequence",
			"drag_content_id":drag_id.match(/\d+/)[0],
			"drop_content_id":drop_id.match(/\d+/)[0],
			"position":position
		};

		commonCls.sendPost(this.id, post);
	},
	submitPassword: function(event, form_el) {
		if (event.keyCode == 13) {
			this.submitPasswordAction(form_el);
			return false;
		}
		return true;
	},
	submitPasswordAction: function(form_el) {
		var params = new Object();
		params["callbackfunc"] = function(res){
			var upload_id = form_el.upload_id.value;
			var metadata_id = form_el.metadata_id.value;
			var password = form_el.password.value;
			commonCls.removeBlock(this.id);
			this.downloadFile(upload_id, metadata_id, password);
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_filedownload&" + Form.serialize(form_el), params);
	},
	downloadFile: function(upload_id, metadata_id, password) {
		var str = _nc_base_url + _nc_index_file_name + '?action=testee_action_main_filedownload&download_flag=1&upload_id='+upload_id+'&metadata_id='+metadata_id;
		if(password != "") {
			str += '&password='+password;
		}
		window.location = str;
		this.setDownloadCount(upload_id);
	},
	setDownloadCount: function(upload_id) {
		var block_id = null;
		if(this.id.indexOf("_mdb_popup_password") != -1) {
			block_id = this.id.replace("_mdb_popup_password","");
		}else {
			block_id = this.id;
		}

		var count_el = $("mdb_file_download_count_" + upload_id + block_id);
		if(count_el) {
			var download_count = parseInt(count_el.innerHTML.match(/\d+/)[0]) + 1;
			count_el.innerHTML = count_el.innerHTML.replace(/\d+/, download_count);
		}
	},
	chkAuth: function() {
		var moderate_el = $("mdb_contents_authority3" + this.id);
		if(moderate_el.checked) {
			$(this.id + "_mdb_contents_comment_setting0").disabled = false;
			$(this.id + "_mdb_contents_comment_setting1").disabled = false;
			Element.removeClassName($(this.id + "_mdb_contents_comment_setting_label0"), "disable_lbl");
			Element.removeClassName($(this.id + "_mdb_contents_comment_setting_label1"), "disable_lbl");
		}else {
			commonCls.displayNone($(this.id + '_mdb_contents_comment_setting_detail'));
			$(this.id + "_mdb_contents_comment_setting1").disabled = true;
			$(this.id + "_mdb_contents_comment_setting0").checked = true;
			$(this.id + "_mdb_contents_comment_setting0").disabled = true;
			Element.addClassName($(this.id + "_mdb_contents_comment_setting_label0"), "disable_lbl");
			Element.addClassName($(this.id + "_mdb_contents_comment_setting_label1"), "disable_lbl");
		}
	},

	changeOldUse: function(use) {
		if (use) {
			$("testee_old" + this.id).disabled = false;
		} else {
			$("testee_old" + this.id).disabled = true;
		}
	},

	check_date_format: function(datestr) {
		// 正規表現による書式チェック(9999/99/99)
		if( !datestr.match(/^\d{4}\/\d{2}\/\d{2}$/) ) {
			return false;
		}
		var vYear = datestr.substr(0, 4) - 0;
		var vMonth = datestr.substr(5, 2) - 1; // 月は 0-11で表現される
		var vDay = datestr.substr(8, 2) - 0;

		// 月、日の妥当性チェック
		if( vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31 ) {
			var vDt = new Date(vYear, vMonth, vDay);
			if( isNaN(vDt) ) {
				return false;
			} else if( vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	},

	edit_date_format: function(datestr) {
		var date_item = datestr.split('/');
		if (date_item[1].length == 1) {
			date_item[1] = "0" + date_item[1];
		}
		if (date_item[2].length == 1) {
			date_item[2] = "0" + date_item[2];
		}
		return date_item[0]+"/"+date_item[1]+"/"+date_item[2];
	},

	calcAge_impl: function(input_date,insert_date) {

		// 入力項目の値部分を処理に使う
		var date_str = input_date;
		// 日付のフォーマットチェック
		if ( !this.check_date_format( date_str ) ) {
			// 日付フォーマットにエラーがある場合は、年齢計算せずに戻る。
			// データ登録時は再度フォーマットチェックが行われるため、最終的には正しい値になる。
			return "";
		}

		// 計算当日の設定
		var today=null;

		// 登録日が設定されている(日付のフォーマットチェックOKの)場合
		if ( this.check_date_format( insert_date ) ) {
			insert_date_array = insert_date.split("/");
			today=new Date(insert_date_array[0], parseInt(insert_date_array[1],10)-1, insert_date_array[2] );
		} else {
		// 登録日が設定されていない場合
			today=new Date();
		}

		// 年齢計算
		today=today.getFullYear()*10000+today.getMonth()*100+100+today.getDate()
		birthday=parseInt(date_str.replace(/\//g,''));
		var age = (Math.floor((today-birthday)/10000));

		return age;
	},

	setAge: function(input_date,insert_date,target) {

		// 日付の成形
		input_date.value = this.edit_date_format(input_date.value);

		// 入力チェック(オブジェクトがない ＞ 入力項目の作成漏れ)
		if ( ( input_date == null ) || ( insert_date == null ) ) {
			target.innerHTML = "";
		}
		// 年齢計算
		var age = this.calcAge_impl(input_date.value,insert_date.value);
		target.innerHTML = age;
	},

	calcBSA: function(stature, weight) {		// BSA 計算(引数はHTMLオブジェクト)
		// 入力チェック(オブジェクトがない ＞ 入力項目の作成漏れ)
		if ( ( stature == null ) || ( weight == null ) ) {
			alert( "BSA 計算が行えません。\n理由：体重、もしくは身長の設定がありません。\n\nシステム管理者に連絡してください。" );
			return "";
		}
		// 入力チェック(数値以外の入力)
		if ( ( isNaN( stature.value ) ) || ( isNaN( weight.value ) ) ) {
			alert( "BSA 計算が行えません。\n理由：体重、もしくは身長に数値以外が入力されています。" );
			return "";
		}
		// 入力チェック(空)
		if ( ( stature.value == "" ) || ( weight.value == "" ) ) {
			alert( "BSA 計算が行えません。\n理由：体重、もしくは身長が入力されていません。" );
			return "";
		}
		// 計算
		var ret = this.calcBSA_impl(stature.value, weight.value);
		if (ret == 0) {
			return "";
		}
		return ret;
	},
	calcBSA_impl: function(stature, weight) {		// BSA 計算(引数はオブジェクトではなく値)
		var a1, a_ret;
		with (Math) {
			a1 = (pow(stature,0.725) * pow(weight,0.425))*71.84 /10000;
			a_ret = round(a1 * 1000) / 1000;
		}
		return a_ret;
	},
	calcCcr: function(doc) {		// Ccr 計算(引数はDocumentオブジェクト)
		// 年齢計算用に登録日チェック
		if ( doc.getElementById('content_insert_date_'+ this.id) == null ) {
			alert( "Ccr 計算が行えません。\n理由：登録日時が取得できませんでした。\n\nシステム管理者に連絡してください。" );
			return "";
		}
		var insert_date = doc.getElementById('content_insert_date_'+ this.id).value;
		// 生年月日の有無
		if ( doc.getElementById('birthday_'+ this.id) == null ) {
			alert( "Ccr 計算が行えません。\n理由：生年月日の設定がありません。\n\nシステム管理者に連絡してください。" );
			return "";
		}
		var birth_date = doc.getElementById('birthday_'+ this.id).value;
		// 年齢計算
		var age = this.calcAge_impl( birth_date, insert_date );
		// 年齢チェック( 数値以外 )
		if ( isNaN( age ) ) {
			alert( "Ccr 計算が行えません。\n理由：年齢が算出できませんでした。\n\n生年月日を正しく入力してください。" );
			return "";
		}
		// 年齢チェック( 2歳以下の場合は計算しない )
		if ( ( age == "" ) || ( age < 2 ) ) {
			alert( "Ccr 計算が行えません。\n理由：年齢が算出できなかったか、2歳未満です。" );
			return "";
		}

		// 血清クレアチニン値の有無
		if ( doc.getElementById('creatinine_'+ this.id) == null) {
			alert( "Ccr 計算が行えません。\n理由：血清クレアチニン値の設定がありません。\n\nシステム管理者に連絡してください。" );
			return "";
		}
		// 血清クレアチニン値の数値チェック
		if ( isNaN( doc.getElementById('creatinine_'+ this.id).value ) ) {
			alert( "Ccr 計算が行えません。\n理由：血清クレアチニン値が数値で入力されていません。" );
			return "";
		}
		// 血清クレアチニン値の空チェック
		if ( doc.getElementById('creatinine_'+ this.id).value == "" ) {
			alert( "Ccr 計算が行えません。\n理由：血清クレアチニン値が入力されていません。" );
			return "";
		}
		var creatinine = doc.getElementById('creatinine_'+ this.id).value;

		// 年齢によって、計算式を分ける
		var ret_ccr = 0;
		if ( age < 2) {							// 2歳未満の場合は0を返す
			ret_ccr = 0;
		}
		else if( age >= 2 && age <= 11) {		// Ccr計算式2：Schwartz（小児用：2才以上11才以下に適用）
			// 身長の有無
			if ( doc.getElementById('stature_'+ this.id) == null ) {
				alert( "Ccr 計算が行えません。\n理由：身長の設定がありません。\n\nシステム管理者に連絡してください。" );
				return "";
			}
			// 身長の数値チェック
			if ( isNaN( doc.getElementById('stature_'+ this.id).value ) ) {
				alert( "Ccr 計算が行えません。\n理由：身長が数値で入力されていません。" );
				return "";
			}
			// 身長の空チェック
			if ( doc.getElementById('stature_'+ this.id).value == "" ) {
				alert( "Ccr 計算が行えません。\n理由：身長が入力されていません。" );
				return "";
			}
			var stature = doc.getElementById('stature_'+ this.id).value;

			// Ccr 算出
			ret_ccr = this.calcCcr_impl_2(stature, creatinine);
		}
		// Ccr計算式1：Cockcroft-Gault
		else {
			// 性別の有無
			if ( doc.getElementById('sex_'+ this.id) == null ) {
				alert( "Ccr 計算が行えません。\n理由：性別の設定がありません。\n\nシステム管理者に連絡してください。" );
				return "";
			}
			// 性別の選択チェック
			if ( doc.getElementById('sex_'+ this.id).value == "" ) {
				alert( "Ccr 計算が行えません。\n理由：性別が選択されていません。" );
				return "";
			}
			var sex = doc.getElementById('sex_'+ this.id).value;

			// 体重の有無
			if ( doc.getElementById('weight_'+ this.id) == null ) {
				alert( "Ccr 計算が行えません。\n理由：体重の設定がありません。\n\nシステム管理者に連絡してください。" );
				return "";
			}
			// 体重の数値チェック
			if ( isNaN( doc.getElementById('weight_'+ this.id).value ) ) {
				alert( "Ccr 計算が行えません。\n理由：体重が数値で入力されていません。" );
				return "";
			}
			// 体重の空チェック
			if ( doc.getElementById('weight_'+ this.id).value == "" ) {
				alert( "Ccr 計算が行えません。\n理由：体重が入力されていません。" );
				return "";
			}
			var weight = doc.getElementById('weight_'+ this.id).value;
			// Ccr 算出
			ret_ccr = this.calcCcr_impl_1(sex, age, weight, creatinine);
		}

		// 計算結果
		if (ret_ccr == 0) {
			return "";
		}
		return ret_ccr;
	},
	calcCcr_impl_1: function(sex, age, weight, creatinine) {		// Ccr計算式1：Cockcroft-Gault
		var a_ret;
		if( sex == "男" ) {
			with (Math) {
				a2 = (( 140 - age ) * weight ) / ( 72 * creatinine );
				a_ret =  Math.round( a2 * 10 )/10;
			}
		}
		else{
				with (Math) {
				a2 = ( 0.85 * ( 140 - age ) * weight ) / ( 72 * creatinine );
				a_ret =  Math.round( a2 * 10 )/10;
			}
		}
		return a_ret;
	},
	calcCcr_impl_2: function(stature, creatinine) {					// Ccr計算式2：Schwartz（小児用：2才以上11才以下に適用）
		var a3, a_ret;
		with (Math) {
			a3 = (0.55*stature)/(creatinine*1 + 0.2);
			a_ret = round(a3 * 10 )/10;
		}
		return a_ret;
	},
	// 登録番号の削除処理
	clearContentNo: function(testee_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "testee_view_main_init");
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_main_clearcontentno&testee_id=" + testee_id, params);
	},
// TEDC権限設定用Function
	showAuthorityEdit: function(eventObject, testee_id, user_id) {
		var queryString = "action=testee_view_edit_tedcauthority_edit"
			 + "&testee_id=" + testee_id 
			 + "&user_id=" + user_id 
			 + "&prefix_id_name=popup_editauthority";
		var option = new Object();
		option["top_el"] = $(this.id);
		//option['center_flag'] = true;
		option["modal_flag"] = true;

		commonCls.sendPopupView(eventObject, queryString, option);
	},
	editAuthority: function() {
		// リフレッシュ用に親画面ID算出
		var parent_id = this.id.replace("_popup_editauthority", "");

		// POSTする画面のシリアライズ
		var fromElement = $("tedcauthorityEditForm" + this.id);
		var messageBody = Form.serialize(fromElement);

		// POSTオプションの設定
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
								commonCls.removeBlock(this.id);
								commonCls.sendRefresh(parent_id);
		}.bind(this);
		commonCls.sendPost(this.id, "action=testee_action_edit_tedcauthority&" + Form.serialize(fromElement), option);

	},
	/* ルームに参加しているユーザの施設を取り込む */
	selectRoomHospital: function(event) {
		var param_popup = new Object();
		var params = new Object();
		params["param"] = {
						"action":"testee_view_edit_option_selecthospital",
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = $("mdb_metadata_options1"+this.id);
		commonCls.send(params);
	},
	// 割付設定を使用するかどうかのフラグ送信
	selectAllocation: function() {

		var fromElement = $("testeeForm" + this.id);
		var messageBody = Form.serialize(fromElement);

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("割付設定を更新しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	
	//割付調整因子項目削除
	delAllocationitem: function( metadata_id, testee_id ) {

		var messageBody = "action=testee_action_edit_delallocation"
							+ "&metadata_id=" + metadata_id 
							+ "&testee_id=" + testee_id;

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									//commonCls.alert("削除しました。");
								}.bind(this);
/*		option["callbackfunc_error"] = function(res){
									commonCls.alert(res);
								}.bind(this);
*/
		commonCls.sendPost(this.id, messageBody, option);
	},
	
	//割付調整因子項目追加
	addAllocationitem: function( allocation_flag ) {

		if( allocation_flag == 1 )
		{
			var fromElement = $("addallocationForm" + this.id);
			var messageBody = Form.serialize(fromElement);
		}
		else if( allocation_flag == 2 )
		{
			var fromElement = $("testeeBlockItemForm" + this.id);
			var messageBody = Form.serialize(fromElement);
		}
		else
		{
			var messageBody = "";
		}

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									//commonCls.alert("追加しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	
	//均等比率更新時
	changeEqual: function( equal_ratio_flag, testee_id ) {

		if( equal_ratio_flag == true ){
			equal_ratio_flag = 1;
			}

		var messageBody = "action=testee_action_edit_changeequal"
							+ "&equal_ratio_flag=" + equal_ratio_flag
							+ "&testee_id=" + testee_id;
							
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									if( equal_ratio_flag == true ){
										commonCls.alert("均等比率を有効にしました。");
									}else{
										commonCls.alert("均等比率を無効にしました。");
									}
									
								}.bind(this);
/*		option["callbackfunc_error"] = function(res){
									commonCls.alert(res);
								}.bind(this);*/

		commonCls.sendPost(this.id, messageBody, option);
	},
	
	
	//群の設定追加
	addGroup: function( allocation_flag ) {

		if( allocation_flag == 1 )
		{
			var fromElement = $("groupsettingForm" + this.id);
			var messageBody = Form.serialize(fromElement);
		}
		else if( allocation_flag == 2 )
		{
			var fromElement = $("testeeBlockGroupForm" + this.id);
			var messageBody = Form.serialize(fromElement);
		}
		else
		{
			var messageBody = "";
		}

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("追加しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	
	//群の設定変更
	changeGroup: function( allocation_group_id, group_name, intervention, ratio, testee_id ) {

		var messageBody = "action=testee_action_edit_changegroup"
							+ "&allocation_group_id=" + allocation_group_id
							+ "&group_name=" + group_name
							+ "&intervention=" + intervention
							+ "&ratio=" + ratio
							+ "&testee_id=" + testee_id;
							
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									//commonCls.alert(messageBody);
									commonCls.alert("変更しました。");
								}.bind(this);
		option["callbackfunc_error"] = function(res){
									commonCls.alert(res);
								}.bind(this);

		commonCls.sendPost(this.id, messageBody, option);
	},
	
	//群の設定削除
	delGroup: function( allocation_group_id, testee_id ) {

		var messageBody = "action=testee_action_edit_delgroup"
							+ "&allocation_group_id=" + allocation_group_id
							+ "&testee_id=" + testee_id;
							
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("削除しました。");
								}.bind(this);
/*		option["callbackfunc_error"] = function(res){
									commonCls.alert(res);
								}.bind(this);*/

		commonCls.sendPost(this.id, messageBody, option);
	},
	
/*	//強制割付チェックボックスクリック時
	changeForce: function( force_allocation_flag, testee_id ) {

		if( force_allocation_flag == true ){
			force_allocation_flag = 1;
			}

		var messageBody = "action=testee_action_edit_changeforce"
							+ "&force_allocation_flag=" + force_allocation_flag
							+ "&testee_id=" + testee_id;
							
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									//commonCls.alert(messageBody);
								}.bind(this);
		option["callbackfunc_error"] = function(res){
									commonCls.alert(res);
								}.bind(this);

		commonCls.sendPost(this.id, messageBody, option);
	},
*/	
	//強制割付更新
	selectForce: function(force_allocation_flag, group_differences, allocation_probability, testee_id) {

		if( force_allocation_flag == true ){
			force_allocation_flag = 1;
			}

		var messageBody = "action=testee_action_edit_forceallocation"
							+ "&force_allocation_flag=" + force_allocation_flag
							+ "&group_differences=" + group_differences
							+ "&allocation_probability=" + allocation_probability
							+ "&testee_id=" + testee_id;

		//var fromElement = $("forceallocationForm" + this.id);
		//var messageBody = Form.serialize(fromElement);

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									//commonCls.alert(messageBody);
									commonCls.alert("強制割付を更新しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	
	//出力設定切り替え
	selectAllocationresult: function( allocation_flag ) {

		if( allocation_flag == 1 )
		{
			var fromElement = $("allocationresultForm" + this.id);
			var messageBody = Form.serialize(fromElement);
		}
		else if( allocation_flag == 2 )
		{
			var fromElement = $("testeeBlockOutputForm" + this.id);
			var messageBody = Form.serialize(fromElement);
		}
		else
		{
			var messageBody = "";
		}

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("更新しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	// 試験毎権限のインポート
	imporTedcauthorityCsv: function (testee_id) {

		var top_el = $(this.id);
		var params = new Object();
		params["param"] = {'action': "testee_action_edit_tedcauthorityimport", "testee_id": testee_id, "_token": $('_token'+this.id).value};
		params["top_el"] = top_el;
		params["method"] = "post";
		params["loading_el"] = top_el;
		params['form_prefix'] = "attachment_form";
		params["callbackfunc"] = function(res){
			alert("アップロードしました。");
			commonCls.sendRefresh(this.id);
			//var msg_div = $('mdb_import_success_result'+this.id);
			//commonCls.alert(msg_div.innerHTML);
			//$('mdb_import'+this.id).value = null;
			//commonCls.removeBlock(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			// エラー時(File)
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	// 印刷用に詳細画面のPOPUP
	showPopupDetail: function(event, content_id, testee_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_main_print",
						"content_id":content_id,
						"testee_id":testee_id,
						"prefix_id_name":"popup_print"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		commonCls.sendPopupView(event, param_popup, params);
	},
	// メール設定用に画面のPOPUP
	showMailUser: function(event, metadata_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"testee_view_edit_mail",
						"metadata_id":metadata_id,
						"prefix_id_name":"popup_mailuser"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	// メール設定
	updateMailUser: function(this_el,metadata_id) {

		var fromElement = $("testeeMailForm" + this.id);
//		var messageBody = Form.serialize(fromElement);

		// リフレッシュ用に親画面ID算出
		var parent_id = this.id.replace("_mailuser", "");

		var top_el = $(this.id);
		var add_params = new Object();
		add_params["method"] = "post";
		add_params["top_el"] = top_el;
		add_params["loading_el"] = top_el;
		add_params["param"] = Form.serialize(fromElement);
		add_params["callbackfunc"] = function(res) {
			//親をリロード
			commonCls.removeBlock(this.id);
			commonCls.sendView(parent_id, {'action':'testee_view_edit_metadata_detail','testee_id':this.testee_id,'metadata_id':metadata_id,'prefix_id_name':"popup"});
		}.bind(this);
		commonCls.send(add_params);
	},

	// 進捗保存
	setStatus: function(status_id) {

		if (!confirm("進捗を登録します。よろしいですか？")) {
			return false;
		}

		var fromElement = $("statusForm" + this.id);
		var messageBody = Form.serialize(fromElement);
		var messageBody = messageBody + "&status_id=" + status_id;

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("更新しました。");
									//commonCls.sendPost(this.id, {"action":"testee_action_main_statusmail"}, {"loading_el":null});
									commonCls.sendPost(this.id, {"action":"testee_action_main_statusmail"}, {"loading_el":null});
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},


	// 割付層作成処理
	createAllocationConbination: function()
	{
		var fromElement = $("testeeBlockSettingForm" + this.id);
		var messageBody = Form.serialize(fromElement);
		
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("作成しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	
	// 置換ブロック法用：割付群/割付因子 変更処理
	changeAllocationBlockSetting: function( testee_id )
	{
		// 確認メッセージ
		if( confirm("割付比率の設定と割付層の作成を行いますか？") == false ) return false;
		
		
		var messageBody = "action=testee_view_edit_allocation_list"
							+ "&testee_id=" + testee_id
							+ "&change_block_allocation_flag=1";

		var option = new Object();
		option["target_el"] = $(this.id);

		commonCls.sendView(this.id, messageBody, option);
	},
	
	// 置換ブロック法用：割付比率・ブロック数更新処理
	changeAllocationBlockCount: function()
	{
		// 確認メッセージ
		if( confirm("割付比率と割付層のブロック数を更新しますか？") == false ) return false;
		
		
		var fromElement = $("testeeBlockRatioForm" + this.id);
		var messageBody = Form.serialize(fromElement);
		
		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(response) {
									commonCls.alert("更新しました。");
								}.bind(this);
		commonCls.sendPost(this.id, messageBody, option);
	},
	
// 18.08.01 add start by makino@opensource-workshop.jp
	// 置換ブロック法用：可変ブロック使用する/しない変更処理
	changeVariableBlock: function()
	{
		// フォーム取得
		var fromElement = $("testeeBlockRatioForm" + this.id);
		
		// 可変ブロックエリア表示/非表示切り替え
		if( fromElement.testee_radio_variable_block_use.checked == true )
		{
			commonCls.displayVisible( $("testee_variable_block" + this.id) );
		}
		else
		{
			commonCls.displayNone( $("testee_variable_block" + this.id) );
		}
		
		return;
	},
// 18.08.01 add end by makino@opensource-workshop.jp
	
	
	// 患者情報登録時割付状態チェック処理
	checkAllocation: function( form_el, temp_flag, warning_ok, confirm_ok )
	{
		// POSTの設定
		var postString = "action=testee_action_main_checkallocation" +
						 "&" + Form.serialize(form_el);
		
		// POSTオプションの設定
		var params = new Object();
		params["target_el"] = null;
		
		// 正常処理
		params["callbackfunc"] = function(res)
		{
			this.contentSubmit( form_el, temp_flag, warning_ok, confirm_ok );
		}.bind(this);
		
		// ワーニング処理
		params["callbackfunc_error"] = function(res)
		{
			if( commonCls.confirm( res ) == true )
			{
				this.contentSubmit( form_el, temp_flag, warning_ok, confirm_ok );
			}
		}.bind(this);
		
		// 実施
		commonCls.sendPost(this.id, postString, params);
	},
	
	
// 18.08.23 add start by makino@opensource-workshop.jp
	// 割付シミュレーション画面表示処理
	showAllocSimulate: function(event, testee_id)
	{
		var messageBody = "action=testee_view_edit_allocation_simulate"
							+ "&testee_id=" + testee_id
							+ "&init_flag=1";

		var option = new Object();
		option["target_el"] = $(this.id);

		commonCls.sendView(this.id, messageBody, option);
	},
	
	// 割付シミュレーション画面：症例入力タイプ変更処理
	changeSimuInputType: function()
	{
		// フォーム取得
		var fromElement = $("testeeSimulateSetting" + this.id);
		
		// 可変ブロックエリア表示/非表示切り替え
		if( fromElement.testee_allocsimu_input_type_file.checked == true )
		{
			commonCls.displayVisible( $("testee_allocsimu_input_file" + this.id) );
			commonCls.displayNone( $("testee_allocsimu_input_auto" + this.id) );
			commonCls.displayNone( $("testee_allocsimu_repeat_count" + this.id) );
			if( $("testee_allocsimu_allocitem_file" + this.id) != null )
			{
				commonCls.displayVisible( $("testee_allocsimu_allocitem_file" + this.id) );
				commonCls.displayNone( $("testee_allocsimu_allocitem_auto" + this.id) );
			}
		}
		else
		{
			commonCls.displayNone( $("testee_allocsimu_input_file" + this.id) );
			commonCls.displayVisible( $("testee_allocsimu_input_auto" + this.id) );
			commonCls.displayVisible( $("testee_allocsimu_repeat_count" + this.id) );
			if( $("testee_allocsimu_allocitem_file" + this.id) != null )
			{
				commonCls.displayNone( $("testee_allocsimu_allocitem_file" + this.id) );
				commonCls.displayVisible( $("testee_allocsimu_allocitem_auto" + this.id) );
			}
		}
		
		return;
	},
	
	// 割付シミュレーショ実施処理
	executeSimulation: function(eventObject)
	{
		// 確認メッセージ
		if( confirm("割付シミュレーションを実施しますか？") == false ) return false;
		
		// 処理中ダイアログの設定
		var queryString = "action=testee_view_edit_allocation_uploading"
								+ "&prefix_id_name=popup_uploading";

		var pop_option            = new Object();
		pop_option["top_el"]      = $(this.id);
		pop_option["target_el"]   = $(this.id);
		pop_option["modal_flag"]  = true;			// モーダル(閉じるまで親ウィンドウ操作不可)表示
		pop_option["center_flag"] = true;			// ダイアログを中央に表示

		commonCls.sendPopupView(eventObject, queryString, pop_option);
		
		
		
		var messageBody = new Object();
		messageBody[ "action" ] = "testee_action_edit_allocationsimulate";
		
		var option = new Object();
		option["param"]        = messageBody;
		option["top_el"]       = $(this.id);
		option["target_el"]    = $(this.id);
		option["timeout_flag"] = false;
		option["callbackfunc"] = function(response)
		{
			commonCls.alert("シミュレーション終了しました。");
			
			// 処理中ダイアログが表示されている場合はそれを削除
			commonCls.removeBlock("_popup_uploading"+this.id);
			
		}.bind(this);
		option["callbackfunc_error"] = function(file, response)
		{
			// 戻ってきたメッセージをアラート表示
			commonCls.alert(response);
			
			// [ファイルをアップロード中です]ダイアログが表示されている場合はそれを削除
			commonCls.removeBlock("_popup_uploading"+this.id);
		}.bind(this);
		
		
		// 処理実施
		commonCls.sendAttachment(option);
		
	},
	
	// 割付シミュレーション：症例ファイル削除
	deleteCaseFile: function()
	{
		// 確認メッセージ
		if( confirm("症例ファイルを削除しますか？") == false ) return false;
		
		var fromElement = $("testeeSimulateSetting" + this.id);
		
		// POSTの設定
		var postString = "action=testee_action_edit_delcasefile" +
						 "&testee_id=" + fromElement.testee_id.value;
		
		
		// POSTオプションの設定
		var params = new Object();
		params["target_el"] = null;
		
		// 正常処理
		params["callbackfunc"] = function(res)
		{
			confirm("削除しました。");
			commonCls.sendRefresh(this.id);
		}.bind(this);
		
		// 実施
		commonCls.sendPost(this.id, postString, params);

	}

}