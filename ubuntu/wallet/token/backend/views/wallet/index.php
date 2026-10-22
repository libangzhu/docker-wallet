<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午9:59
 */

use backend\assets\wallet\IndexAsset;

IndexAsset::register($this);

?>
<style>
    .layui-table-tips-c {
        padding: 0px;
    }
</style>
<h4>钱包列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <a href="/admin/wallet/add">
            <button class="layui-btn layui-btn-default" id="add">添加钱包</button>
        </a>
    </div>
    <div class="layui-col-md8">
        <form class="layui-form" method="get" action="">
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">钱包名称</label>
                <div class="layui-input-inline">
                    <input class="layui-input" name="name">
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
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-xs" lay-event="delete">删除</a>
    <a class="layui-btn layui-btn-primary layui-btn-xs" href="/admin/coin-supported-symbol/list?id={{d.id}}">C2C支持货币对</a>
    <a class="layui-btn layui-btn-primary layui-btn-xs" href="/admin/coin-supported-coin/list?id={{d.id}}">支持发行货币种类</a>
</script>
