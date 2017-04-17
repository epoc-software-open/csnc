var clsFooteredit = Class.create();
var footereditCls = Array();

clsFooteredit.prototype = {
	initialize: function(id) {
		this.id = id;
	},

	// 設定内容登録
	setFooter: function() {
		var fromElement = $("footeredit_edit_form" + this.id);
		var messageBody = Form.serialize(fromElement);

		var option = new Object();
		option["target_el"] = $(this.id);
		option["callbackfunc"] = function(){
		    alert("更新しました。");
		}.bind(this);

		commonCls.sendPost(this.id, messageBody, option);
	},

	// 編集モードの切り替え
	changeEditType: function(edit_type) {

		if ( edit_type == 0 ) {
			commonCls.displayVisible($('footeredit_easy')); 
			commonCls.displayNone($('footeredit_html')); 
		}
		else if ( edit_type == 1 ) {
			commonCls.displayNone($('footeredit_easy')); 
			commonCls.displayVisible($('footeredit_html')); 
		}
	},

	// フッタを初期値に戻す
	setFooterDefault: function() {

		var post = {
			"action":"footeredit_action_edit_setdefault"
		};
		var option = new Object();
		option["callbackfunc"] = function(){
		    alert("フッターを初期値に戻しました。");
		}.bind(this);

		commonCls.sendPost(this.id, post, option);
	}
}