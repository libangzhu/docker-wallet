<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 下午2:40
 */
?>

<h4>矿工费</h4>
<div class="layui-row">
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this">币钱包</li>
            <li>托管钱包</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <a href="/admin/miner-fee/add?type=1">
                    <button class="layui-btn">添加矿工费</button>
                </a>
                <div class="layui-row">
                    <table id="table1" class="layui-table"></table>
                </div>

            </div>
            <div class="layui-tab-item">
                <a href="/admin/miner-fee/add?type=2">
                    <button class="layui-btn">添加矿工费</button>
                </a>
                <div class="layui-row">
                    <table class="layui-table" id="table2" lay-filter="table2"></table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    var table = layui.table;
    table.render({
        elem: "#table1",
        cols: [[
            {field: 'id', title: 'ID'},
            {field: 'platform', title: '平台'},
            {field: 'min', title: '最小值'},
            {field: 'max', title: '最大值'},
            {field: 'level', title: '分档'},
            {field: 'create_at', title: '创建时间'},
            {field: 'update_at', title: '更新时间'},
            {field: 'id', title: '操作', templet: "#operatorTpl"}
        ]],
        url: '/admin/miner-fee/cost?type=1'
    });
    table.render({
        elem: "#table2",
        page: true,
        limit: 10,
        cols: [[
            {field: 'id', title: 'ID'},
            {field: 'platform', title: '币种'},
            {field: 'fee', title: '旷工费', edit: 'text'},
            {field: 'create_at', title: '创建时间'},
            {field: 'update_at', title: '更新时间'},
            {field: 'id', title: '操作', templet: "#operatorTpl"}
        ]],
        url: '/admin/miner-fee/cost?type=2'
    });

    $('#update-coin').click(function () {
        $.get('/admin/miner-fee/update-coin', {}, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
                table.reload('table2', {
                    page: {
                        curr: 1
                    }
                });
            }
        });
    });
    //监听单元格编辑
    table.on('edit(table2)', function (obj) {
        var value = obj.value; //得到修改后的值
        var data = obj.data;    //得到所在行所有键值
        $.get('/admin/miner-fee/set-fee', {id: data.id, fee: value}, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
                table.reload('table2', {});
            } else {

            }
        });
    });

</script>
<script type="text/html" id="operatorTpl">
    <a href="/admin/miner-fee/edit?id={{d.id}}&type={{d.type}}">
        <button class="layui-btn layui-btn-sm">编辑</button>
    </a>
    <a href="/admin/miner-fee/del?id={{d.id}}">
        <button class="layui-btn layui-btn-sm layui-btn-danger">删除</button>
    </a>
</script>