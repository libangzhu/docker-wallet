<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午9:59
 */

use backend\assets\applicationCategory\IndexAsset;

IndexAsset::register($this);

?>
<style>
    .layui-table-tips-c {
        padding: 0px;
    }
</style>
<h4>应用分类列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <a href="/admin/application-category/add">
            <button class="layui-btn layui-btn-default" id="add">添加应用分类</button>
        </a>
    </div>
    <?php
    if (1 == $user_platform_id) {
        ?>
        <form class="layui-form" method="get" action="">
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">钱包</label>
                <div class="layui-input-inline">
                    <select name="platform" id = "platform">
                        <?php foreach ($platforms as $key => $platform): ?>
                            <option value="<?= $platform['id'] ?>"><?= $platform['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="form1">搜索</button>
            </div>
        </form>
        <?php
    } ?>
</div>
<div class="layui-row">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>


<script type="text/html" id="iconTpl">
    <img src="{{d.icon_url}}" style="max-width: 32px; max-height: 32px;"/>
</script>
<!--<script type="text/html" id="operationTpl">-->
<!--    <a lay-event="edit">-->
<!--        <button class="layui-btn layui-btn-sm"><i class="layui-icon">&#xe642;</i></button>-->
<!--    </a>-->
<!--    <a lay-event="delete">-->
<!--        <button class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon">&#xe640;</i></button>-->
<!--    </a>-->
<!--</script>-->

<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    <a class="layui-btn layui-btn-primary layui-btn-xs" href="/admin/application/list?id={{d.id}}">应用列表</a>
    <a class="layui-btn layui-btn-normal layui-btn-xs" href="/admin/application-category/banner-index?id={{d.id}}">banner图管理</a>
</script>
<script type="text/html" id="recommendTpl">
    <input type="checkbox" name="isrecommend" value="{{d.id}}" lay-skin="switch" lay-text="是|否"
           lay-filter="recommendDemo" {{ d.isrecommend== 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="enableTpl">
    <input type="checkbox" name="isenable" value="{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="enableDemo"
           {{ d.enable== 1 ? 'checked' : '' }}>
</script>

