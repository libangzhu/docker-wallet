<?php
/**
 * Created By Sublime Text 3
 *
 * @authors rlgy <rlgyzhcn@qq.com>
 * @date    2018-09-04 10:11:49
 */

use  backend\assets\coinPublishRule\IndexAsset;

IndexAsset::register($this);
?>

<div>
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>
<script type="text/html" id="toolbar">
    <a class="layui-btn layui-btn-sm" href="/admin/coin-publish-rule/list?pid={{d.id}}">管理</a>
    <button class="layui-btn layui-btn-sm" lay-event="create">创建锁仓规则</button>
</script>