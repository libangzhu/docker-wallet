/**
 * 空投类型js
 * @author rlgyzhcn@qq.com
 */

var table = layui.table;
table.render({
    elem: "#table1",
    url: '/admin/investment/type',
    cols: [[
        {field: 'id', title: "ID"},
        {field: 'name', title: "类型"},
        {title: '操作', templet: "#operationTpl"}
    ]],
});
$("#add").click(function () {
    $.get('/admin/investment/type-add', {}, function (str) {
        var index = layer.open({
            type: 1,
            title: '添加类型',
            id: 'addType',
            skin: 'layui-layer-lan',
            area: ['320px', 'auto'],
            content: str,
            btn: ['确认', '取消'],
            btn1: function () {
                var name = $("#type-add input[name='name']").val();
                var _csrf = $("#type-add input[name='_csrf']").val();
                $.post('/admin/investment/type-add', {_csrf: _csrf, name: name}, function (res) {
                    layer.msg(res.msg);
                    if (res.code == 0) {
                        layer.close(index);
                        table.reload('table1', {curr: 1});
                    }
                });
            }
        });
    });
});
table.on('tool(table1)', function (obj) {
    var data = obj.data;
    var event = obj.event;
    if (event == 'delete') {
        var confirm = layer.confirm('删除？', {icon: 3, title: '删除'}, function (index) {
            $.get('/admin/investment/type-delete', {id: data.id}, function (res) {
                layer.close(confirm);
                table.reload('table1', {curr: 1});
            });
        });
    } else if (event == 'update') {
        $.get('/admin/investment/type-update', {id: data.id}, function (res) {
            if (typeof res != 'string' && res.code != 0) {
                layer.msg(res.msg);
            } else {
                var index = layer.open({
                    type: 1,
                    title: '修改',
                    id: 'updateType',
                    skin: 'layui-layer-lan',
                    area: ['320px', 'auto'],
                    content: res,
                    btn: ['确认', '取消'],
                    btn1: function () {
                        var name = $("#type-update input[name='name']").val();
                        var _csrf = $("#type-update input[name='_csrf']").val();
                        $.post('/admin/investment/type-update', {
                            _csrf: _csrf,
                            name: name,
                            id: data.id
                        }, function (res) {
                            layer.msg(res.msg);
                            if (res.code == 0) {
                                layer.close(index);
                                table.reload('table1', {curr: 1});
                            }
                        });
                    }
                });
            }
        })
    }
});