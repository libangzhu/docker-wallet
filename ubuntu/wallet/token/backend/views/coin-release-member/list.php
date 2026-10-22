<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

use backend\assets\coinReleaseMember\IndexAsset;
IndexAsset::register($this);
?>

<div class="layui-row">
    <button class="layui-btn layui-btn-sm" lay-event="implode" id="implode">名单导入</button>
    <button class="layui-btn layui-btn-sm" lay-event="add" id="add">添加人员</button>
</div>
<div class="layui-row">
    <div class="layui-col-md8">
        <table class="layui-table" id="table1" lay-filter="table1"></table>
    </div>
</div>
