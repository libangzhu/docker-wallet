<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/1
 * Time: 17:26
 */

use backend\assets\applicateRank\IndexAsset;
IndexAsset::register($this);
?>

<div class="layui-row">
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this">热度榜</li>
            <li>新品榜</li>
            <li>热门搜索</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <button class="layui-btn layui-btn-default" id="add1">添加热度应用</button>
                <table class="layui-table" id="table1" lay-filter="table"></table>
            </div>
            <div class="layui-tab-item">
                <button class="layui-btn layui-btn-default" id="add2">添加新品应用</button>
                <table class="layui-table" id="table2" lay-filter="table"></table>
            </div>
            <div class="layui-tab-item">
                <button class="layui-btn layui-btn-default" id="add3">添加热门搜索</button>
                <table class="layui-table" id="table3" lay-filter="table"></table>
            </div>
        </div>
    </div>

</div>
<!-- 添加应用页面 -->
<div class="layui-row add" style="display: none;padding: 5px;">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form form_add" action="javascript:void(0)" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input type="hidden" name="type" value="">
            <div class="layui-form-item">
                <label class="layui-form-label">应用</label>
                <div class="layui-input-block">
                    <select name="applicate_id" id="applicate_id" lay-search >
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn btn_add_submit" lay-submit lay-filter="formDemo">立即提交</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 添加热门搜索页面 -->
<div class="layui-row add_search" style="display: none;padding: 5px;">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form form_add" action="javascript:void(0)" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-form-item">
                <label class="layui-form-label">名称</label>
                <div class="layui-input-block">
                <input type="text" name="name" required lay-verify="required" placeholder="" autocomplete="off"
                       class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn btn_add_submit" lay-submit lay-filter="formSearch">立即提交</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/html" id="operTpl">
    <a class="btn_delete" lay-event="delete"><i class="layui-icon" style="color: #4b67ff">&#xe640;</i></a>
</script>

<script type="text/html" id="operSearchTpl">
    <a class="btn_delete" lay-event="delete_search"><i class="layui-icon" style="color: #4b67ff">&#xe640;</i></a>
</script>