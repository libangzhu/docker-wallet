<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-5
 * Time: 上午9:57
 */
?>

<div class="layui-row">
    <div class="layui-col-md8">
        <form class="layui-form">
            <div class="layui-inline">
                <input class="layui-input" placeholder="UID" name="uid">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="submit1">筛选</button>
            </div>
        </form>
    </div>
</div>
<div class="layui-row">
    <div class="layui-col-md8">
        <table id="table1" lay-filter="table1"></table>
    </div>
</div>

<script>
    var table = layui.table;
    var form = layui.form;

    table.render({
        elem: '#table1',
        url: '/admin/member/wallet',
        page: 1,
        cols: [[
            {field: 'uid', title: 'UID'},
            {field: 'public_key', title: '钱包公钥'},
            {title: '操作', toolbar: '#tools', fixed: 'right'}
        ]],
    });
    table.on('tool(table1)', function (obj) {
        var data = obj.data;
        var event = obj.event;
        if (event == 'view_assets') {
            var assets = JSON.parse(data.asset);
            //TODO 资产显示
            var content = '<table class="layui-table" style="margin: 5px; width: 340px">';
            content += '<tr><td>币种</td><td>数量</td></tr>';
            $.each(assets, function (key, val) {
                content += '<tr><td>' + key + '</td>' + '<td>' + val + '</td></tr>';
            });
            content += '</table>';
            var index = layer.open({
                type: 1,
                content: content,
                title: '用户(UID:' + data.uid + ')资产',
                area: '350px',
                resize: false,
            });
        }
    });
    //表单提交
    form.on('submit(submit1)', function (data) {
        table.reload('table1', {
            where: data.field,
            page: {curr: 1},
        });
        return false;
    });
</script>
<script type="text/html" id="tools">
    <a class="layui-btn layui-btn-sm" lay-event="view_assets">查看资产</a>
</script>
