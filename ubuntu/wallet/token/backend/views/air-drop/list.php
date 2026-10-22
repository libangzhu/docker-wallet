<?php

use backend\assets\airDrop\IndexAsset;
IndexAsset::register($this);
?>

<div class="layui-row">
    <button class="layui-btn layui-btn-sm" lay-event="import" id="import">名单导入</button>
    <button class="layui-btn layui-btn-sm" lay-event="add" id="add">单笔添加</button>
<!--    <button class="layui-btn layui-btn-sm" lay-event="transfer" id="transfer">批量打币</button>-->
</div>
<div class="layui-row">
    <table class="layui-table" id="table1"></table>
</div>
