var table = layui.table;
var upload = layui.upload;

table.render({
    elem: "#table1",
    url: '/admin/air-drop/list',
    page: 1,
    limit: 10,
    cols: [[
        {field:'id', width:100, title: 'ID', sort: true},
        {field: 'to_address', width:400, title: '地址'},
        {field: 'amount', title: '数量'},
        {field: 'coin_name', title: '币种名称'},
        {field: 'txhash', width:400,title: 'hash'},
        {field: 'msg', width:200, title: '返回结果'},
        {field: 'create_time', title: '添加时间'},
        {field: 'update_time', title: '处理时间'}
    ]]
    ,done: function () {
        //$("[data-field='id']").css('display','none');
    }
});

$('#add').click(function () {
    //打开弹窗
    $.get('/admin/air-drop/add', {}, function (str) {
        var index = layer.open({
            type: 1,
            title: '添加数据',
            id: 'add-one',
            skin: 'layui-layer-lan',
            area: ['320px', 'auto'],
            content: str,
            btn: ['确认', '取消'],
            btn1: function () {
                $.post('/admin/air-drop/add', $("#addData").serialize(), function (rev) {
                    layer.msg(rev.msg);
                    if (rev.code == 0) {
                        layer.close(index);
                        table.reload("table1", {});
                    }
                });
            }
        });
        layui.form.render();
    });

    return false;
});

upload.render({
    elem: "#import",
    url: "/admin/air-drop/load-file",
    accept: 'file',
    exts: 'csv',
    done: function (res) {
        if(res.code == 0){
            layer.msg(res.msg);
            table.reload('table1', {curr: 1});
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
    $.get('/admin/air-drop/batch-transfer', {}, function (rev) {
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