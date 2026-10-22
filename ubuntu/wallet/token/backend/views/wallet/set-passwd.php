<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 18-12-18
 * Time: 上午10:26
 */
?>
<h4>设置密码</h4>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md3">
        <form class="layui-form" method="post" action="">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-form-item">
                <label class="layui-form-label">原始密码</label>
                <div class="layui-input-block">
                    <input name="old_passwd"  type="password" required class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">设置密码</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="new_passwd" required type="password">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">确认密码</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="confirm_passwd" required type="password">
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn">提交</button>
            </div>
        </form>
    </div>
</div>
<script>
    var form = layui.form;
    form.render();
</script>
