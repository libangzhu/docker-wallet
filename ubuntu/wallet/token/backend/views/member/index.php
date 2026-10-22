<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-12
 * Time: 下午5:52
 */

?>
<h4>用户管理</h4>
<div class="layui-row">
    <form class="layui-form">
        <div class="layui-inline">
            <label class="layui-form-label" style="width: 100px; margin-bottom: 0">注册日期</label>
            <div class="layui-input-inline">
                <input name="register_at" class="layui-input" type="text" id="date1">
            </div>
        </div>
        <div class="layui-inline">
            <div class="layui-input-inline">
                <select name="type">
                    <option value="0">查找单个用户</option>
                    <option value="1">通过推客查找</option>
                </select>
            </div>
        </div>
        <div class="layui-inline">
            <input type="text" name="condition" class="layui-input" placeholder="UID/姓名/手机/邮箱">
        </div>
        <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="submit1">查询</button>
            <button class="layui-btn" type="reset">清除</button>
        </div>
    </form>
</div>
<div class="layui-row">
    <div class="layui-col-md12">
        <table class="layui-table" id="table1"></table>
    </div>
</div>
<script>
    var laydate = layui.laydate;
    var form = layui.form;
    var table = layui.table;
    laydate.render({
        elem: "#date1",
        range: '~',
    });
    form.render();
    table.render({
        elem: "#table1",
        url: '/admin/member/index',
        page: 1,
        cols: [[
            {field: 'uid', title: "UID"},
            {field: 'realname', title: "姓名"},
            {field: 'nickname', title: "昵称"},
            {field: 'phone', title: "手机"},
            {field: 'email', title: "邮箱"},
            {field: 'capital', title: "资产总值"},
            {field: 'register_at', title: "注册时间"},
            {field: 'recommend_id', title: "推客UID"},
            {field: 'recommend_number', title: "推介人数"},
            {field: 'account_address', title: "托管账户地址"},
            {title: "操作", templet: "#operationTpl"},
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
    <a href="/admin/member/edit?id={{d.uid}}">
        <button class="layui-btn layui-btn-sm">编辑</button>
    </a>
</script>