<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row">
    <div class="layui-col-md6">
        <form class="layui-form" method="post" action="">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input name="id" type="hidden" value="<?= $model->id ?>">
            <?php if ($type == 2) : ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <input class="layui-input" name="platform" value="<?= $model->platform ?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">手续费</label>
                    <div class="layui-input-block">
                        <input class="layui-input" name="fee" value="<?= $model->fee ?>">
                    </div>
                </div>
            <?php else : ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">平台</label>
                    <div class="layui-input-block">
                        <select name="platform">
                            <?php foreach ($platforms as $platform): ?>
                                <option value="<?= $platform ?>" <?php if ($model->platform == $platform) {
                                    echo "selected";
                                } ?>><?= $platform ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">最小值</label>
                    <div class="layui-input-block">
                        <input class="layui-input" name="min" value="<?= $model->min ?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">最大值</label>
                    <div class="layui-input-block">
                        <input class="layui-input" name="max" value="<?= $model->max ?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">分档</label>
                    <div class="layui-input-block">
                        <input class="layui-input" name="level" value="<?= $model->level ?>">
                    </div>
                </div>
            <?php endif; ?>
            <div class="layui-form-item">
                <button class="layui-btn">提交</button>
            </div>
        </form>
    </div>
</div>
<script>
    layui.form.render();
</script>