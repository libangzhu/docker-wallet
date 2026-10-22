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
        <form class="layui-form" method="post" action="" id="application-category-edit">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input name="id" type="hidden" value="<?= $model['id'] ?>">

            <div class="layui-inline">
                <label class="layui-form-label">中文名</label>
                <div class="layui-input-block" style="width: 250px">
                    <input class="layui-input" name="name[zh-CN]" value="<?= $model['name_zh'] ?>">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">英文名</label>
                <div class="layui-input-block" style="width: 250px">
                    <input class="layui-input" name="name[en-US]" value="<?= $model['name_en'] ?>">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">日文名</label>
                <div class="layui-input-block" style="width: 250px">
                    <input class="layui-input" name="name[ja]" value="<?= $model['name_ja'] ?>">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">韩文名</label>
                <div class="layui-input-block" style="width: 250px">
                    <input class="layui-input" name="name[ko]" value="<?= $model['name_ko'] ?>">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-block"  style="width: 250px">
                    <input type="text" name="sort" required lay-verify="required" placeholder=""
                           autocomplete="off" value="<?= $model['sort'] ?>"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <img src="<?= $model['icon_url'] ?>" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="icon1">
                    </div>
                    <input type="hidden" name="icon" value="<?= $model['icon'] ?>">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload1" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">banner</label>
                    <div class="layui-input-block">
                        <img src="<?= $model['banner_image_url'] ?>" style="margin-top: 11px; max-width: 32px; max-height: 32px" id="icon2">
                    </div>
                    <input type="hidden" name="banner" value="<?= $model['banner'] ?>">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload2" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">banner链接</label>
                <div class="layui-input-block">
                    <input type="text" name="banner_url" required lay-verify="required" placeholder=""
                           autocomplete="off" class="layui-input" value="<?= $model['banner_url'] ?>">
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
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf, image_type: 'coin'},
        done: function (res) {
            if(res.code == 0){
                $("input[name='icon']").val(res.data.image_id);
                $("#icon1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });

    uploader.render({
        elem: "#upload2",
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf, image_type: 'banner'},
        done: function (res) {
            if(res.code == 0){
                $("input[name='banner']").val(res.data.image_id);
                $("#icon2").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });
    //form render
    var form = layui.form;
    form.render();
</script>
