<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 上午9:19
 */
?>
<h4>文章列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <a href="/admin/source/notice-add">
            <button class="layui-btn lauyi-btn-default" id="add">添加公告</button>
        </a>
    </div>
    <div class="layui-col-md11">
        <form class="layui-form" lay-filter="form1" method="get" action="">
            <div class="layui-inline">
                <label class="layui-form-label" style="width: 100px; margin-bottom: 0">公告标题</label>
                <div class="layui-input-block">
                    <input class="layui-input" type="text" value="" name="title" title="公告标题">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="width: 100px; margin-bottom: 0">公告状态</label>
                <div class="layui-input-block">
                    <select name="status" title="公告状态">
                        <option value="all">所有</option>
                        <option value="0">未发布</option>
                        <option value="1">已发布</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="search1">搜索</button>
            </div>
        </form>
    </div>
</div>
<div class="layui-row">
    <table class="layui-table" id="table1"></table>
</div>

<script>
    var form = layui.form;
    form.render(null, 'form1');

    var table = layui.table;
    table.render({
        elem: "#table1",
        cols: [[
            {field: 'id', title: 'ID'},
            {field: 'title', title: '标题'},
            {field: 'content', title: '内容'},
            {field: 'author', title: '作者'},
            {field: 'status', title: '状态', templet: '#statusTpl'},
            {field: 'create_at', title: '创建时间'},
            {field: 'update_at', title: '更新时间'},
            {field: 'id', title: '操作', templet: '#operationTpl'}
        ]],
        page: 1,
        limit: 10,
        url: '/admin/source/notice'
    });
    form.on('submit(search1)', function (data) {
        table.reload("table1", {
            where: data.field,
            page: {curr: 1}
        });
        return false;
    });
</script>
<script type="text/html" id="statusTpl">
    {{# if(d.status==1){ }}
    已发布
    {{# }else{ }}
    未发布
    {{# } }}
</script>
<script type="text/html" id="operationTpl">
    <a href="/admin/source/notice-edit?id={{d.id}}" title="编辑">
        <button class="layui-btn layui-btn-sm"><i class="layui-icon">&#xe642;</i></button>
    </a>
    <a href="/admin/source/notice-del?id={{d.id}}" title="删除">
        <button class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon">&#xe640;</i></button>
    </a>
</script>