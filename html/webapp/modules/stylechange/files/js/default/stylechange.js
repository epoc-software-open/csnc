var clsStylechange = Class.create();
var stylechangeCls = Array();

clsStylechange.prototype = {
    initialize: function(id) {
        this.id = id;
    },

    // スタイル変更
    setStyle: function() {

        var fromElement = $("stylechange_edit_form" + this.id);

        // 遷移先の指定
        fromElement.action.value = 'stylechange_action_edit_init';

        var messageBody = Form.serialize(fromElement);

        var option = new Object();
        option["target_el"] = $(this.id);
        option["callbackfunc"] = function(){
            alert("更新しました。");
        }.bind(this);

        commonCls.sendPost(this.id, messageBody, option);
    },

    // 条件変更
    changeStyle: function() {

        var fromElement = $("stylechange_edit_form" + this.id);

        // 遷移先の指定
        fromElement.action.value = 'stylechange_action_view_init';

        var messageBody = Form.serialize(fromElement);

        var option = new Object();
        option["target_el"] = $(this.id);

        commonCls.sendPost(this.id, messageBody, option);
    },

    // 上書きCSS の更新
    setCssText: function() {

        var fromElement = $("stylechange_override_form" + this.id);

        var messageBody = Form.serialize(fromElement);

        var option = new Object();
        option["target_el"] = $(this.id);
        option["callbackfunc"] = function(){
            alert("更新しました。");
        }.bind(this);

        commonCls.sendPost(this.id, messageBody, option);
    }
}