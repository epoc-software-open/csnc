var clsDownloadlog = Class.create();
var downloadlogCls = Array();

clsDownloadlog.prototype = {
	initialize: function(id) {
		this.id = id;
	},

	// 条件変更
	changeCondition: function(position) {

		var fromElement = $("pageControlForm" + position + this.id);
		var messageBody = Form.serialize(fromElement);
		messageBody = messageBody + "&now_page=1";

		var option = new Object();
		option["target_el"] = $(this.id);
		commonCls.sendPost(this.id, messageBody, option);
	},

	// ページング
	toPage: function(now_page) {

		var fromElement = $("pageControlForm" + this.id);
		var messageBody = Form.serialize(fromElement);
		messageBody = messageBody + "&now_page=" + now_page;

		var option = new Object();
		option["target_el"] = $(this.id);
		commonCls.sendPost(this.id, messageBody, option);
	},

	// CSVダウンロード
	csvOutput: function( form_el, ym_select ) {
		var href = '';

		href = _nc_base_url + _nc_index_file_name + '?action=downloadlog_view_edit_export';
		href = href + "&ym_select=" + ym_select;
		location.href = href;
	}
}