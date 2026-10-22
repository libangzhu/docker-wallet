var table = layui.table;
var upload = layui.upload;

table.render({
    elem: "#table1",
    url: '/admin/coin-transfer/list',
    page: 1,
    limit: 10,
    cols: [[
        {field:'id', width:80, title: 'ID', sort: true},
        {field: 'address', title: '地址'},
        {field: 'amount', title: '数量'},
        {field: 'status', title: '状态'},
    ]]
    ,done: function () {
        //$("[data-field='id']").css('display','none');
    }
});

upload.render({
    elem: "#implode",
    url: "/admin/coin-transfer/load-file",
    accept: 'file',
    exts: 'csv',
    done: function (res) {
        if(res.code == 0){
            table.reload('table1', {
                page: {
                    curr: 1
                }
            });
        }else{
            layer.msg(res.msg);
        }


    },
    before: function (obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
        layer.load(); //上传loading
    }
});
$('#transfer').click(function () {
    //var data = obj.data;    //得到所在行所有键值
    $.get('/admin/coin-transfer/batch-transfer', {}, function (rev) {
        layer.msg(rev.msg);
        if (0==rev.code) {
            table.reload('table1', {
                page: {
                    curr: 1
                }
            });
        }
    });
});