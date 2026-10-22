<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 16:14:15
 * @authors rlgy <rlgyzhcn@qq.com>
 */

;?>

<div class="layui-col-md8">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<script type="text/html" id="status_tpl">
    {{# if(d.status==1){ }}
    否
    {{# }else{ }}
    是
    {{# } }}
</script>
<script type="text/html" id="toolBarTop">
    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="add">添加</button>
</script>

<script type="text/html" id="toolBar">
    <a class="layui-btn layui-btn-xs layui-btn-normal" href="/admin/coin-withhold/detial?wid={{d.id}}">查看详情</a>
    <button class="layui-btn layui-btn-xs layui-btn-normal" lay-event="del">删除</button>
</script>

<script type="text/javascript">
var table = layui.table;
table.render({
    elem: '#table1',
    page: true,
    limit: 10,
    url: '/admin/coin-withhold/index',
    toolbar: '#toolBarTop',
    cols: [[
        {field: 'id',title: 'ID'},
        {field: 'name',title: '代扣币种'},
        {field: 'status',title: '是否开启全局代扣', templet:'#status_tpl'},
        {field: 'token_name',title: '代扣币种'},
        {field: 'address',title: '代扣币种地址'},
        {field: 'platform',title: '平台'},
        {title: '操作',toolbar: '#toolBar'}
    ]]
});
table.on('toolbar(table1)', function(obj) {
    var event = obj.event;
    if ('add' == event) {
        layer.prompt({
            formType: 0,
            value: '',
            maxlength: 255,
            title: '请输入值'
        }, function(value, index, elem) {
            $.get('/admin/coin-withhold/set', {
                coin: value
            }, function(rev) {
                layer.msg(rev.msg);
                if (rev.code == 0) {
                    layer.close(index);
                    table.reload('table1', {
                        page: {
                            curr: 1
                        }
                    });
                }
            });
        });
    }
});
table.on('tool(table1)', function(obj) {
    var event = obj.event;
    var data = obj.data;
    if (event == 'del') {
        layer.confirm('确定删除吗?', {
            icon: 3,
            title: '删除'
        }, function() {
            $.get('/admin/coin-withhold/del', {
                id: data.id
            }, function(rev) {
                layer.msg(rev.msg);
                if (rev.code == 0) {
                    table.reload('table1', {
                        page: {
                            curr: 1
                        }
                    });
                }
            });
        });
    }
});
</script>
