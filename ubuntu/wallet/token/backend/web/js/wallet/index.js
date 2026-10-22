/**
 * @author rlgyzhcn@qq.com
 */
var table = layui.table;
var form = layui.form;
var layer = layui.layer;
form.render();
var tableIns = table.render({
    elem: "#table1",
    url: '/admin/wallet/list',
    limit: 10,
    page: 1,
    loading: true,
    cols: [[
        {field: 'id', title: 'ID'},
        {field: 'name', title: '名称'},
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
        layer.confirm('真的要删除' + data.name + '吗？', {icon: 3, title: '删除'}, function (index) {
            layer.close(index);
            //向服务端发送删除指令
            $.get('/admin/wallet/delete?id=' + obj.data.id, function (data, status) {
                if (data.code == 0) {
                    obj.del(); //删除对应行（tr）的DOM结构
                }
                layer.msg(data.msg);
            });
        });
    } else if (obj.event == 'edit') {
        $.get('/admin/wallet/edit', {id: data.id}, function (str) {
            var editIndex = layer.open({
                type: 1,
                title: '编辑: ' + data.name,
                area: '625px',
                content: str,
                btn: ['保存', '取消'],
                btn1: function () {
                    // console.log();
                    $.post('/admin/wallet/edit', $("#walletEdit").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (rev.code == 0) {
                            table.reload("table1", {
                                where: data.field,
                            });
                            layer.close(editIndex);
                        }
                    });
                }
            });
        });
    }
});