var query=function(id,scroll) {
    "use strict";

    window['query'+id]();
    if (id===2&&scroll===1) {
        setTimeout(function(){
            $('html, body').animate( scrollTo(0, $('#'+'table'+id).height()), 'slow' );
        }, 200);
    }
};

var query1=function() {
    $('#table1').bootstrapTable('refresh');
};

var query2=function() {
    $('#table2').bootstrapTable('refresh');
};

var server_error=function() {
    layer.msg('请求服务器出错！', {anim: 6});
};

var msg_200=function(msg) {
    layer.msg(msg, {time: 800});
};

var msg_no_200=function(msg) {
    layer.msg(msg, {anim: 6});
};