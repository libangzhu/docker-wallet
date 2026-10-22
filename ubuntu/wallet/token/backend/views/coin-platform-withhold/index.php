<?php
use backend\assets\coinPlatformWithhold\IndexAsset;

IndexAsset::register($this);

?>
<style>
    .layui-table-tips-c {
        padding: 0px;
    }
</style>
<h4>平行链列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <a href="/admin/coin-platform-withhold/add">
            <button class="layui-btn layui-btn-default" id="add">添加平行链</button>
        </a>
    </div>
</div>
<div class="layui-row">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-primary layui-btn-xs"  href="/admin/coin-supported-symbol/list?id={{d.id}}" >所支持货币对</a>
</script>
