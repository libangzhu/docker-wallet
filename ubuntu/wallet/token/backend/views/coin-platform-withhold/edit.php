<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md12">
        <form class="layui-form" method="post" action="" id="platformEdit">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input name="id" type="hidden" value="<?= $model->id ?>">
            <div class="layui-inline">
                <label class="layui-form-label">名称</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="platform"  value="<?= $model->platform ?>" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">地址</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="address" value="<?= $model->address ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">私钥</label>
                <div class="layui-input-block" style="width: auto">
                    <input class="layui-input" name="private_key" value="<?= $model->private_key ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">执行器</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="exer" value="<?= $model->exer ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">手续费</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="fee" value="<?= $model->fee ?>">
                </div>
            </div>
        </form>
    </div>
</div>

