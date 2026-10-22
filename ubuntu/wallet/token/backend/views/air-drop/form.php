<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

?>
<style type="text/css">
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md12">
        <form class="layui-table" id="form_data" lay-filter="form_data">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-inline">
                <label class="layui-form-label">币种</label>
                <div class="layui-input-block">
                    <input type="text" name="coin" class="layui-input" readonly="readonly" value="<?= $token ?>"/>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">手机号码</label>
                <div class="layui-input-block">
                    <input type="text" name="mobile" class="layui-input"/>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">总量</label>
                <div class="layui-input-block">
                    <input type="text" name="amount" class="layui-input"/>
                </div>
            </div>
        </form>
    </div>
</div>