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
    url: '/admin/coin-publish-rule/list',
    limit: 10,
    page: 1,
    toolbar: '#toolBarTop',
    cols: [[
        {field: 'sid', title: '锁仓编号'},
        {field: 'amount', title: '锁仓人数'},
        {field: 'release_time', title: '释放时间', templet: "#form_release_time"},
        {
            field: 'lock', title: '锁仓比例', templet: function (d) {
                return d.lock + '%';
            }
        },
        {field: 'type', title: '释放类型', templet: "#form_type"},
        {
            field: 'release', title: '释放比例', templet: function (d) {
                return d.release + '%';
            }
        },
        {field: 'notice', title: '备注'},
        {title: '操作', toolbar: '#toolbar'}
    ]]
});

table.on('tool(table1)', function (obj) {
    var event = obj.event;
    var data = obj.data;
    if ('update' == event) {
        $.get('/admin/coin-publish-rule/set', {id: data.id}, function (rev) {
            var index = layer.open({
                type: 1,
                title: '编辑',
                id: 'update_one',
                skin: 'layer-skin-lan',
                content: rev,
                btn: ['保存', '取消'],
                btn1: function () {
                    $.post('/admin/coin-publish-rule/set', $("#form_data").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (rev.code == 0) {
                            layer.close(index);
                            table.reload("table1", {});
                        }
                    });
                }
            });
            form.render();
            $.get('/admin/coin-publish-rule/list', {id: data.id, page: 1, limit: 1}, function (rev) {
                if (0 == rev.code) {
                    var data = rev.data[0];
                    form.val("form_data", {
                        id: data.id,
                        sid: data.sid,
                        lock: data.lock,
                        type: data.type,
                        release: data.release,
                        repeat: data.repeat,
                        release_time: "",
                        notice: data.notice,
                    });
                    laydate1 = laydate.render({
                        elem: '#release_time',
                        type: 'datetime',
                        format: format[data.repeat],
                        value: data.release_time
                    });
                }
            });
        });
    } else if ('del' == event) {
        layer.confirm('确定删除锁仓规则(' + data.sid + ')', {title: '删除'}, function (index) {
            $.get('/admin/coin-publish-rule/del', {id: data.id}, function (rev) {
                layer.msg(rev.msg);
                if (0 == rev.code) {
                    obj.del();
                    layer.close(index);
                }
            });
        });
    }
});

table.on('toolbar(table1)', function (obj) {
    var checkStatus = table.checkStatus(obj.config.id);
    var event = obj.event;
    if ('add' == event) {
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

                    $.post('/admin/coin-publish-rule/set', $("#form_data").serialize(), function (rev) {
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

$('#search').click(function () {
    var mobile = $('input[name="mobile"]').val();
    $.get('/admin/coin-publish-rule/get-remember-list', {mobile:mobile}, function (rev) {
        if (0 == rev.code) {
            console.log(rev.data);
            var tr_data = '';
            var data = rev.data;
            for(var i=0;i<data.length;i++){
                tr_data += '<tr><td>'+data[i]['rule_sid']+'</td><td>'+data[i]['id']+'</td><td>'+data[i]['mobile']+'</td><td>'+data[i]['coin']+'</td><td>'+
                            data[i]['amount']+'</td><td>'+data[i]['release']+'</td><td>'+data[i]['freeze']+'</td><td>'+
                            data[i]['output']+'</td></tr>';
            }
            var count_html = '<p style="color:#0000ff">共<b style="color:red">'+rev.count+'</b>条资产记录:'+'</p>';
            var html = count_html + '<table class="layui-table">' +
                                         '<thead>' +
                                            '<tr>' +
                                                '<th>锁仓编号</th>' +
                                                '<th>用户id</th>' +
                                                '<th>手机号</th>' +
                                                '<th>币种</th>' +
                                                '<th>总量</th>' +
                                                '<th>活动数量</th>' +
                                                '<th>冻结数量</th>' +
                                                '<th>已提币数量</th>' +
                                            '</tr>' +
                                          '</thead>' +
                                          '<tbody>' +
                                               tr_data +
                                          '</tbody>' +
                                    '</table>';
            layer.open({
                title: '个人资产信息',
                content: html,
                area: ['1000px', '800px'],
                scrollbar:true,
            });

        }
    });
});
