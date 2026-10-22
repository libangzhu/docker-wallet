<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午3:31
 */
?>
<h4>添加钱包</h4>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md6">
        <form class="layui-form" method="post" action="">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-inline">
                <label class="layui-form-label">简称</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="name"  value="<?= $model->name ?>" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">所属链</label>
                <div class="layui-input-block">
                    <select name="chain_id">
                        <?php foreach ($platform_withhold as $val): ?>
                            <option value="<?= $val['id'] ?>" <?php if ($model->chain_id == $val['id']) {
                                echo "selected";
                            } ?>><?= $val['platform'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">下载地址</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="download_url" value="<?= $model->download_url ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">介绍</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="introduce"><?= $model->introduce?></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn">提交</button>
            </div>
        </form>
    </div>
</div>
<script>
    layui.form.render();
</script>
