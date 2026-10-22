<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午6:23
 */
?>

<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md12">
        <form class="layui-form" method="post" action="" id="walletEdit">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input name="id" type="hidden" value="<?= $model->id ?>">
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
        </form>
    </div>
</div>
<script>
    var laydate = layui.laydate;
    laydate.render({
        elem: "#time1"
    });
    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/coin/upload',
        data: {_csrf: $_csrf},
        done: function (res) {
            console.log(res.data.src);
            $("input[name='icon']").val(res.data.src);
            $("#icon1").attr('src', res.data.src);
        },
        error: function (res) {

        }
    });
    //form render
    var form = layui.form;
    form.render();
</script>
