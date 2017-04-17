var clsHospitalinfo = Class.create();
var hospitalinfoCls = Array();

clsHospitalinfo.prototype = {
	initialize: function(id) {
		this.id = id;
	},

	// 施設追加
	setHospitalinfo: function() {

		var fromElement = $("hospitalinfo_edit_form" + this.id);
		var messageBody = Form.serialize(fromElement);

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(){
			alert("更新しました。");
		}.bind(this);

		commonCls.sendPost(this.id, messageBody, option);
	},

	// 変更画面
	popupHospitalinfo: function( eventObject, hospitalinfo_id ) {

		var queryString = "action=hospitalinfo_view_main_popup"
			 + "&hospitalinfo_id=" + hospitalinfo_id
			 + "&prefix_id_name=popup_hospitalinfo";
		var option = new Object();
		option["top_el"] = $(this.id);
		option['center_flag'] = true;
		//option["modal_flag"] = true;

		commonCls.sendPopupView(eventObject, queryString, option);
	},

	// 施設変更
	updateHospitalinfo: function() {

		// リフレッシュ用に親画面ID算出
		var parent_id = this.id.replace("_popup_hospitalinfo", "");

		var fromElement = $("hospitalinfo_popup_form" + this.id);
		var messageBody = Form.serialize(fromElement);

		var option = new Object();
		option["target_el"] = $(this.id);
		option["loading_el"] = $(this.id);
		option["callbackfunc"] = function(){
			alert("更新しました。");
			commonCls.removeBlock(this.id);
			commonCls.sendRefresh(parent_id);
		}.bind(this);

		commonCls.sendPost(this.id, messageBody, option);
	},

	// 施設削除
	deleteHospitalinfo: function( hospitalinfo_id ) {

		if (!commonCls.confirm("この施設を削除しますか？")) {
			return false;
		} 

		var messageBody = "action=hospitalinfo_action_main_delete"
			 + "&hospitalinfo_id=" + hospitalinfo_id;

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(){
			alert("削除しました。");
		}.bind(this);

		commonCls.sendPost(this.id, messageBody, option);
	},

	// 検索
	searchHospitalinfo: function() {

		var fromElement = $("hospitalinfo_search_form" + this.id);

		var queryString = "action=hospitalinfo_view_main_init"
			 + "&" + Form.serialize(fromElement);

		commonCls.sendView(this.id, queryString);
	}

}