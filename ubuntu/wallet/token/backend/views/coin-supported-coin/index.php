<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午9:59
 */

use backend\assets\coinSupportedCoin\IndexAsset;

IndexAsset::register($this);

?>
<style>
    .layui-table-tips-c {
        padding: 0px;
    }
</style>
<h4>货币列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <button class="layui-btn layui-btn-sm" lay-event="add" id="add">单笔添加</button>
    </div>
</div>
<input id="platform_id" type="hidden" value="<?= $platform_id ?>">
<div class="layui-row">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<script type="text/html" id="operationTpl">
    <a lay-event="delete">
        <button class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon">&#xe640;</i></button>
    </a>
</script>
