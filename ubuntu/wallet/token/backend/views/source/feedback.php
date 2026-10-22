<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-7
 * Time: 下午4:15
 */
?>
<div class="layui-row">
    <div class="layui-col-md5">
        <form class="layui-form">
            <div class="layui-inline" style="display: none;">
                <label class="layui-form-label">类别</label>
                <div class="layui-input-inline">
                    <select name="type">
                        <option value="0">1</option>
                        <option value="1">1</option>
                        <option value="2">1</option>
                        <option value="3">1</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="width: 100px; margin-bottom: 0">联系方式</label>
                <div class="layui-input-inline">
                    <input class="layui-input" type="text" name="connect">
                </div>
            </div>

            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="submit1">查询</button>
            </div>
        </form>
    </div>
    <div class="layui-col-md3">
        <div style="float: right;">
            <a href="/admin/source/feedback?operation=2">
                <button class="layui-btn">导出cvs</button>
            </a>
        </div>
    </div>
</div>
<div class="layui-row">
    <div class="layui-col-md8">
        <table class="layui-table" id="table1"></table>
    </div>
</div>
<script>
    var form = layui.form;
    form.render();
    var table = layui.table;
    table.render({
        elem: "#table1",
        page: 1,
        limit: 10,
        url: '/admin/source/feedback',
        cols: [[
            {field: 'id', title: 'ID'},
            {field: 'content', title: '内容'},
            {field: 'connect', title: '联系方式'},
            {field: 'create_at', title: '反馈时间'},
            {field: 'id', title: '操作', templet: "#operationTpl"}
        ]]
    });
    form.on('submit(submit1)', function (data) {
        table.reload('table1', {
            where: data.field,
            page: {curr: 1},
        });
        return false;
    });
</script>
<script type="text/html" id="operationTpl">
    <a href="/admin/source/feedback?id={{d.id}}&operation=1">
        <button class="layui-btn layui-btn-danger layui-btn-sm">删除</button>
    </a>
</script>