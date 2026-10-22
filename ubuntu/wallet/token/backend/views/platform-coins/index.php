<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午9:59
 */

use backend\assets\platformCoins\IndexAsset;

IndexAsset::register($this);

?>
<style>
    .layui-table-tips-c {
        padding: 0px;
    }
</style>
<div class="layui-row">
    <div class="layui-col-md8">
        <form class="layui-form" method="get" action="">
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">按地址搜索</label>
                <div class="layui-input-inline">
                    <input class="layui-input" name="coins_address" style="width: 400px;">
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="form1">搜索</button>
            </div>
        </form>
    </div>
</div>
<div class="layui-row">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<script type="text/html" id="operationTpl">
    <a lay-event="edit">
        <button class="layui-btn layui-btn-sm"><i class="layui-icon">&#xe642;</i></button>
    </a>
    <a lay-event="delete">
        <button class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon">&#xe640;</i></button>
    </a>
</script>
