/**
 * @author rlgyzhcn@qq.com
 */
var table = layui.table;
var form = layui.form;
var upload = layui.upload;

form.render();
table.render({
    elem: "#table1",
    page: 1,
    limit: 10,
    url: '/admin/investment/index',
    cols: [[
        {field: 'uid', title: 'UID'},
        {field: 'nickname', title: '昵称'},
        {field: 'phone', title: '手机'},
        {field: 'coin', title: '币种'},
        {field: 'count', title: '数量'},
        {field: 'type', title: '类型'},
        {field: 'status', title: '状态'},
        {field: 'address', title: '地址'},
        {field: 'create_at', title: '添加时间'},
        {field: 'update_at', title: '处理时间'}
    ]]
});
$('#add1').click(function () {
    //打开弹窗
    $.get('/admin/investment/add-one', {}, function (str) {
        var index = layer.open({
            type: 1,
            title: '添加数据',
            id: 'add-one',
            skin: 'layui-layer-lan',
            area: ['320px', 'auto'],
            content: str,
            btn: ['确认', '取消'],
            btn1: function () {
                var uid = $('#addData input[name="uid"]').val();
                var count = $('#addData input[name="count"]').val();
                var type = $('#addData input[name="type"]').val();
                if (uid !== "" && count !== "") {

                    $.post('/admin/investment/add-one', $("#addData").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (rev.code == 0) {
                            layer.close(index);
                            table.reload("table1", {});
                        }
                    });

                } else {
                    layer.msg("请输入完整！");
                }
                return false;
            }
        });
        layui.form.render();
    });

    return false;
});
$("#add2").click(function () {
    return false;
});
form.on('submit(form1)', function (data) {
    table.reload('table1', {
        where: data.field,
        page: {curr: 1}
    });
    return false;
});
upload.render({
    elem: "#add2",
    url: "/admin/investment/load-file",
    accept: 'file',
    exts: 'csv',
    data: {_csrf: csrf},
    done: function (res) {
        layer.msg(res.msg);
        table.reload('table1', {curr: 1});
    }
});