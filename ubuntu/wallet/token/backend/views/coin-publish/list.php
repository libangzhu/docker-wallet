<?php
/**
 * Created By Sublime text 3
 *
 * @author rlgzyhcn <rlgyzhcn@qq.com>
 */

use backend\assets\coinPublish\IndexAsset;

IndexAsset::register($this);
?>

<h4>发币管理</h4>
<?php if ($is_show_button): ?>
    <div class="layui-row">
        <button class="layui-btn" id="add">添加币种监控</button>
    </div>
<?php endif; ?>
<div class="layui-row">
    <div class="layui-table" id="table1" lay-filter="table1"></div>
</div>

<script type="text/html" id="toolbar">
    <?php if ($is_show_button): ?>
        <a class="layui-btn layui-btn-xs" lay-event="update">编辑</a>
    <?php endif; ?>
    <a class="layui-btn layui-btn-xs" href="/admin/coin-publish-rule/list?pid={{d.id}}">查看配置</a>
    <?php if ($is_show_button): ?>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    <?php endif; ?>
</script>
<script type="text/html" id="autoCheckTpl">
    <input type="checkbox" name="auto_check" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="autoCheckDemo" {{ d.auto_check == 1 ? 'checked' : '' }}>
</script>