/**
 * Created By Sublime Text 3
 *
 * @authors rlgy (rlgyzhcn@qq.com)
 * @date    2018-09-04 10:24:52
 */

var table = layui.table;
var form = layui.form;
var laydate = layui.laydate;
var format = [
    'yyyy-MM-dd HH:mm:ss',// 不重复
    'MM月dd号 HH:mm:ss', //每年
    'dd号 HH:mm:ss', //每月
];
var laydate1;

table.render({
    elem: '#table1',
    url: '/admin/coin-publish-rule/index',
    page: 1,
    limit: 10,
    cols: [[
        {field: 'id', title: 'ID'},
        {field: 'coin_name_en', title: '币种中文名'},
        {field: 'coin_name_zh', title: '币种英文名'},
        {field: 'address', title: '热钱包地址'},
        {title: '操作', toolbar: '#toolbar'}
    ]]
});
table.on('tool(table1)', function (obj) {
    var event = obj.event;
    var data = obj.data;
    if (event == 'create') {
        $.get('/admin/coin-publish-rule/set', {}, function (rev) {
            var index = layer.open({
                type: 1,
                title: '添加锁仓规则',
                id: 'add_one',
                skin: 'layer-skin-lan',
                content: rev,
                // area: ['500px', 'auto'],
                btn: ['确认', '取消'],
                btn1: function () {
                    $.post('/admin/coin-publish-rule/set', $("#form_data").serialize() + '&pid=' + data.id, function (rev) {
                        layer.msg(rev.msg);
                        if (rev.code == 0) {
                            layer.close(index);
                            table.reload("table1", {});
                        }
                    });

                }
            });
            form.render();
            laydate1 = laydate.render({
                elem: '#release_time',
                type: 'datetime',
                format: 'dd号 HH:mm:ss'
            });
        });
    }
});

form.on('select(form_data)', function (data) {
    var index = $('#form_data select[name="repeat"]').val();
    var clone = $("#release_time").clone();
    var parent = $("#release_time").parent("div");
    $("#release_time").remove();
    clone.removeAttr('lay-key');
    parent.append(clone);
    laydate.render({
        elem: '#release_time',
        type: 'datetime',
        format: format[index]
    });
});