<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-20
 * Time: 下午4:27
 */
?>

<div style="padding: 5px;">
    <form id="type-update" class="layui-form">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-inline">
                <input name="name" class="layui-input" value="<?= $model->name ?>">
            </div>
        </div>
    </form>
</div>
