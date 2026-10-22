<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-20
 * Time: 下午3:09
 */


?>
<div style="padding: 5px;">
    <form id="type-add" class="layui-form">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-inline">
                <input name="name" class="layui-input">
            </div>
        </div>
    </form>
</div>
