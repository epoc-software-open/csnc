var clsStyleviewer = Class.create();
var styleviewerCls = Array();

clsStyleviewer.prototype = {
    initialize: function(id) {
        this.id = id;
    },

    // スタイル変更
    setStyle: function() {

        var fromElement = $("styleviewer_edit_form" + this.id);
        var messageBody = Form.serialize(fromElement);

        var option = new Object();
        option["target_el"] = $(this.id);
        option["callbackfunc"] = function(){
            location.reload();
        }.bind(this);

        commonCls.sendPost(this.id, messageBody, option);
    }
}