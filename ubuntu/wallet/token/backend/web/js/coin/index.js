/**
 * @author rlgyzhcn@qq.com
 */
var table = layui.table;
var form = layui.form;
var layer = layui.layer;
form.render();
var tableIns = table.render({
    elem: "#table1",
    url: '/admin/coin/index',
    limit: 10,
    page: 1,
    loading: true,
    cols: [[
        {field: 'id', title: 'ID'},
        {field: 'name', title: '名称'},
        {field: 'icon', title: '图标', templet: "#iconTpl"},
        {field: 'official', title: '官网', templet: "#officialTpl"},
        {field: 'paper', title: '白皮书', templet: "#paperTpl"},
        {field: 'platform', title: '平台'},
        {field: 'chain', title: '类型'},
        {
            field: 'exchange',
            title: '上架交易所',
            templet: "#exchangeTpl",
            style: 'cursor: pointer;',
            event: 'getExchange'
        },
        {field: 'price', title: '众筹价格'},
        {field: 'release', title: '发行时间'},
        {field: 'recommend', title: '推介该币种', templet: "#recommendTpl"},
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
    if (obj.event === 'getExchange') {
        var load = layer.load(1);
        $.get('/admin/coin/get-exchange-list-by-id?id=' + obj.data.id, {}, function (str) {
            layer.close(load);
            layer.open({
                type: 1,
                title: '上架交易所列表',
                content: str,
            });
        });
    } else if (obj.event == 'delete') {
        layer.confirm('真的要删除' + data.name + '吗？', {icon: 3, title: '删除'}, function (index) {
            layer.close(index);
            //向服务端发送删除指令
            $.get('/admin/coin/delete?id=' + obj.data.id, function (data, status) {
                if (data.code == 0) {
                    obj.del(); //删除对应行（tr）的DOM结构
                }
                layer.msg(data.msg);
            });
        });
    } else if (obj.event == 'edit') {
        $.get('/admin/coin/edit', {id: data.id}, function (str) {
            var editIndex = layer.open({
                type: 1,
                title: '编辑: ' + data.name,
                area: '625px',
                content: str,
                btn: ['保存', '取消'],
                btn1: function () {
                    // console.log();
                    $.post('/admin/coin/edit', $("#coinEdit").serialize(), function (rev) {
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