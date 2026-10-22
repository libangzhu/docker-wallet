<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

?>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md12">
        <form id="form_data" class="layui-form" lay-filter="form_data">
        <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>" />
        <input type="hidden" name="id" />
        <div class="layui-inline" style="display: none;">
            <label class="layui-form-label" style="width: 110px;">锁仓编号</label>
            <div class="layui-input-block">
                <input name="sid" type="text" class="layui-input" lay-verify="required" readonly="readonly" />
            </div>
        </div>

        <div class="layui-inline">
            <label class="layui-form-label" style="width: 110px;">锁仓比例(%)</label>
            <div class="layui-input-block">
                <input name="lock" type="text" class="layui-input" lay-verify="required" />
            </div>
        </div>

        <div class="layui-inline">
            <label class="layui-form-label" style="width: 110px;">类型</label>
            <div class="layui-input-block">
            <select name="type" class="layui-input">
                <option value="1">按锁仓总量释放</option>
                <option value="2">按锁仓余量释放</option>
            </select>
            </div>
        </div>

        <div class="layui-inline">
            <label class="layui-form-label" style="width: 110px;">释放比例(%)</label>
            <div class="layui-input-block">
            <input name="release" type="text" class="layui-input" lay-verify="required" />
            </div>
        </div>

        <div class="layui-inline">
            <label class="layui-form-label" style="width: 110px;">重复</label>
            <div class="layui-input-block">
            <select name="repeat" class="layui-input" lay-filter="form_data">
                <option value="0">不重复</option>
                <option value="1">每年</option>
                <option value="2" selected="selected">每月</option>
            </select>
            </div>
        </div>

        <div class="layui-inline">
            <label class="layui-form-label" style="width: 110px;">释放时间</label>
            <div class="layui-input-block">
                <input name="release_time" type="text" class="layui-input" lay-verify="required" id="release_time" autocomplete="off" />
            </div>
        </div>

        <div class="layui-inline">
            <label class="layui-form-label" style="width: 110px;">备注</label>
            <div class="layui-input-block">
                <textarea name="notice" class="layui-textarea"></textarea>
            </div>
        </div>
    </form>
    </div>
</div>
