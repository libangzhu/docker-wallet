<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-20
 * Time: 下午2:46
 */

use backend\assets\investmentType\IndexAsset;
/**
 * @var \yii\web\View $this
 */
IndexAsset::register($this);
?>
<h4>空投类型管理</h4>
<div class="layui-row">
    <button class="layui-btn" id="add">添加类型</button>
</div>
<div class="layui-row">
    <div class="layui-col-md6">
        <table class="layui-table" id="table1" lay-filter="table1"></table>
    </div>
</div>

<script type="text/html" id="operationTpl">
    <button class="layui-btn layui-btn-sm" lay-event="update">修改</button>
    <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="delete">删除</button>
</script>
