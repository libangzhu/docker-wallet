<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午6:23
 */
?>
<h4>添加应用分类</h4>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md12">
        <form class="layui-form" method="post" action="" id="application-category-edit">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-inline">
                <label class="layui-form-label">名称</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="platform" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">地址</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="address" ">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">私钥</label>
                <div class="layui-input-block" style="width: auto">
                    <input class="layui-input" name="private_key"">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">执行器</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="exer"">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">手续费</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="fee"">
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn">提交</button>
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
        url: '/admin/image/upload',
        data: {_csrf: $_csrf},
        done: function (res) {
            if (res.code == 0) {
                $("input[name='icon']").val(res.data.image_id);
                $("#icon1").attr('src', res.data.image_src);
            }
        },
        error: function (res) {

        }
    });

    uploader.render({
        elem: "#upload2",
        url: '/admin/image/upload',
        data: {_csrf: $_csrf, image_type: 2},
        done: function (res) {
            if (res.code == 0) {
                $("input[name='banner']").val(res.data.image_id);
                $("#icon2").attr('src', res.data.image_src);
            }
        },
        error: function (res) {

        }
    });
    //form render
    var form = layui.form;
    form.render();
</script>
