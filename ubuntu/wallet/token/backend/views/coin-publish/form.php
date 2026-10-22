<?php
/**
 * Created By Sublime text 3
 * @author rlgzyhcn <rlgyzhcn@qq.com>
 */

/**
 * @var  \commom\models\psources\CoinPublish $model
 * @var  array $roles
 */
?>
<style type="text/css">
    .layui-form-label {
        width: 100px;
        margin-top: 5px;
        margin-left: 20px;
    }
    .layui-input-block{
        margin-left: 170px;
        margin-top: 5px;
    }
</style>
<div style="padding: 5px 20px;">
    <form id="addData" class="layui-form">
        <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <div class="layui-inline">
            <span class="layui-form-label" style="width: 100px;">公司名称</span>
            <div class="layui-input-block">
                <input name="company_name" type="text" class="layui-input" lay-verify="required" value="<?= $model->company_name ?>">
            </div>
        </div>
        <div class="layui-inline">
            <span class="layui-form-label" style="width: 100px;">发行币种</span>
            <div class="layui-input-block">
                <input name="coin_name_zh" type="text" class="layui-input" lay-verify="required" value="<?= $model->coin_name_zh ?>">
            </div>
        </div>
        <div class="layui-inline">
            <span class="layui-form-label" style="width: 100px;">简称</span>
            <div class="layui-input-block">
                <input name="coin_name_en" type="text" class="layui-input" lay-verify="required" value="<?= $model->coin_name_en ?>">
            </div>
        </div>
        <div class="layui-inline">
            <span class="layui-form-label" style="width: 100px;">热钱包地址</span>
            <div class="layui-input-block">
                <input name="address" type="text" class="layui-input" lay-verify="required" value="<?= $model->address ?>">
            </div>
        </div>
        <div class="layui-inline">
            <span class="layui-form-label" style="width: 100px;">企业用户组</span>
            <div class="layui-input-block">
                <select name="owner" class="layui-select" lay-ignore style="width: 182px;">
                    <?php if (is_array($roles)): ?>
                        <?php foreach($roles as $role): ?>
                            <option value="<?= $role ?>" <?php if($role == $model->owner){?> selected <?php }?> > <?= $role ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </form>
</div>