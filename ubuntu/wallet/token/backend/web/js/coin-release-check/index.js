/**
 *
 * @authors rlgy (rlgyzhcn@qq.com)
 * @date    2018-09-05 19:33:56
 */

var form = layui.form;
var table = layui.table;
var laydate = layui.laydate;

form.render();
var table_check = table.render({
    elem: '#table_check',
    page: true,
    limit: 10,
    url: '/admin/coin-release-check/check-list',
    cols: [[
        {field: 'id', title: '流水编号'},
        {field: 'mobile', title: '手机号'},
        {field: 'coin', title: '币种'},
        {
            field: 'amount', title: '数量', templet: function (d) {
                return d.amount / 1e8;
            }
        },
        {field: 'to_address', title: '对方地址'},
        {field: 'today', title: '该地址今日提币次数'},
        {field: 'total', title: '该地址总操作数'},
        {field: 'create_time', title: '提交时间'},
        {title: '操作', toolbar: '#toolbar_check'}
    ]]
});
var table_search = table.render({
    elem: '#table_search',
    page: true,
    limit: 10,
    url: '/admin/coin-release-check/search-list',
    cols: [[
        {field: 'id', title: '流水编号'},
        {field: 'mobile', title: '手机号'},
        {field: 'coin', title: '币种'},
        {
            field: 'amount', title: '数量', templet: function (d) {
                return d.amount / 1e8;
            }
        },
        {field: 'to_address', title: '对方地址'},
        {field: 'create_time', title: '提交时间'},
        {field: 'update_time', title: '完成时间'},
        {field: 'status', title: '状态', templet: '#search_status'},
        {field: 'check_first_uid', title: '初审员'},
        {field: 'check_second_uid', title: '复审员'},
    ]]
});
laydate.render({
    elem: '#date_check_start',
});
laydate.render({
    elem: '#date_check_end',
});
laydate.render({
    elem: '#date_search_start',
});
laydate.render({
    elem: '#date_search_end',
});
form.on('submit(submit_check)', function (data) {
    table.reload('table_check', {
        page: {
            curr: 1
        },
        where: data.field
    });
    return false;
});
form.on('submit(submit_search)', function (data) {
    table.reload('table_search', {
        page: {
            curr: 1,
        },
        where: data.field
    });
    return false;
});

//工具栏点击事件
table.on('tool(table_check)', function (obj) {
    var event = obj.event;
    var data = obj.data;
    if (event == 'check_first') {
        var index = layer.msg('审核通过?', {
            title: '审核',
            btn: ['通过', '不通过', '取消'],
            btn1: function (index, layero) {
                $.get('/admin/coin-release-check/check', {id: data.id, status: 2, step: 1}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table_check.reload();
                        layer.close(index);
                    }
                });
            },
            btn2: function (index, layero) {
                $.get('/admin/coin-release-check/check', {id: data.id, status: 3, step: 1}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table_check.reload();
                        layer.close(index);
                    }
                });
            }
        });
    } else if ('check_second' == event) {
        var index = layer.msg('复核通过?', {
            title: '复核',
            btn: ['通过', '不通过', '取消'],
            btn1: function (index, layero) {
                $.get('/admin/coin-release-check/check', {id: data.id, status: 2, step: 2}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table_check.reload();
                        table_search.reload();
                        layer.close(index);
                    }
                });
            },
            btn2: function (index, layero) {
                $.get('/admin/coin-release-check/check', {id: data.id, status: 3, step: 2}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table_check.reload();
                        layer.close(index);
                    }
                });
            }
        });
    } else if ('extract_cancel' == event) {
        var index = layer.msg('撤提该申请?', {
            title: '撤提',
            btn: ['撤提', '取消'],
            btn1: function (index, layero) {
                $.get('/admin/coin-release-check/check', {id: data.id, status: 2, step: 3}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table_check.reload();
                        table_search.reload();
                        layer.close(index);
                    }
                });
            }
        });
    }
});
