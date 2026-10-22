/**
 * @author rlgyzhcn@qq.com
 */
var table = layui.table;
var form = layui.form;
var layer = layui.layer;
form.render();
var platform_id = $("#platform_id").val();
var tableIns = table.render({
    elem: "#table1",
    url: '/admin/coin-supported-symbol/list?id=' + platform_id,
    limit: 10,
    page: 1,
    loading: true,
    cols: [[
        {field: 'id', title: 'ID'},
        {
            field: 'currency',
            title: '交易货币',
            //templet: function (data) {
                //var name = JSON.parse(data.name);
                //console.log(typeof (data.name), name.zh);
                //return name.zh
            //}
        },
        {
            field: 'base_currency',
            title: '基础货币',
            //templet: function (data) {
                //var name = JSON.parse(data.name);
                //console.log(typeof (data.name), name.zh);
                //return name.zh
            //}
        },
        {field: 'create_time', title: '创建时间'},
        {field: 'id', title: '操作', templet: '#operationTpl'}
    ]],
});
form.on('submit(form1)', function (data) {
    table.reload("table1", {
        where: data.field,
        page: {curr: 1},
    });
    return false;
});
//监听单元格事件
table.on('tool(table1)', function (obj) {
    var data = obj.data;
    if (obj.event == 'delete') {
        layer.confirm('真的要删除' + data.coin_name + '吗？', {icon: 3, title: '删除'}, function (index) {
            layer.close(index);
            //向服务端发送删除指令
            $.get('/admin/coin-supported-symbol/delete?id=' + obj.data.id, function (data, status) {
                if (data.code == 0) {
                    obj.del(); //删除对应行（tr）的DOM结构
                }
                layer.msg(data.msg);
            });
        });
    }
});

$('#add').click(function () {
    //打开弹窗
    $.get('/admin/coin-supported-symbol/add?platform_id=' + platform_id, {}, function (str) {
        var index = layer.open({
            type: 1,
            title: '添加数据',
            id: 'add-one',
            skin: 'layui-layer-lan',
            area: ['320px', 'auto'],
            content: str,
            btn: ['确认', '取消'],
            btn1: function () {
                $.post('/admin/coin-supported-symbol/add', $("#addData").serialize(), function (rev) {
                    layer.msg(rev.msg);
                    //if (rev.code == 0) {
                        layer.close(index);
                        table.reload("table1", {});
                    //}
                });
            }
        });
        layui.form.render();
    });

    return false;

});