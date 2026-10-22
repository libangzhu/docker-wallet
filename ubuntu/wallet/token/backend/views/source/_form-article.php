<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 上午10:18
 */

/**
 * @var $model backend\models\coin\ArticleForm
 */
?>
<div class="layui-row">
    <div class="layui-col-md6">
        <form class="layui-form" lay-filter="form1" method="post" action="">
            <input type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>" name="_csrf">
            <input type="hidden" value="<?= $model->id ?>" name="id">
            <div class="layui-form-item">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">标题</label>
                <div class="layui-input-block">
                    <input class="layui-input" type="text" value="<?= $model->title ?>" lay-verify="required"
                           name="title">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">外链地址</label>
                <div class="layui-input-block">
                    <input class="layui-input" type="text" value="<?= $model->url ?>" name="url">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">发布</label>
                <div class="layui-input-block">
                    <select name="status">
                        <option value="1">发布</option>
                        <option value="0">暂不发布</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图片</label>
                    <div class="layui-input-block">
                        <img src="<?= $model->image_url ?>" style="margin-top: 11px; max-width: 32px; max-height: 32px" id="icon1">
                    </div>
                    <input type="hidden" name="image_url" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload1" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn" lay-submit>保存</button>
            </div>
        </form>
    </div>
</div>
<script>
    var form = layui.form;
    form.render(null, 'form1');
    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf, image_type: 'banner'},
        done: function (res) {
            if (res.code == 0) {
                $("input[name='image_url']").val(res.data.src);
                $("#icon1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });
</script>