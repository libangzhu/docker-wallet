<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/15
 * Time: 21:01
 */
?>
<style>
    .layui-form-label {
        width: 110px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md6">
        <form class="layui-form" method="post" action="">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="icon1">
                    </div>
                    <input type="hidden" name="icon" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload1" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">H5图标</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="icon2">
                    </div>
                    <input type="hidden" name="h5_icon" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload2" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">中文名</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="name[zh-CN]" value="" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">英文名</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="name[en-US]" value="" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">日文名</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="name[ja]" value="" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">韩文名</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="name[ko]" value="" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">应用排序</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="sort" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                    <select name="type">
                        <option value="1">非原生页面</option>
                        <option value="2">原生页面</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">原生链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="native_url" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">官方链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="official_url" value="" lay-verify="required">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">原生登录链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="native_login_url" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">h5链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="h5_url" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">安卓链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="android_url" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">ios企业版链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="ios_url" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">ios商业版链接</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="app_store_url" value="">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">介绍图</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="icon3">
                    </div>
                    <input type="hidden" name="introduce_image" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload3" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">宣传语</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" name="advertise" value="">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">应用介绍</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="description"></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">显示尺寸</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="show_width" placeholder="宽" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">px</div>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="show_height" placeholder="高" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">px</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">跳转方式</label>
                <div class="layui-input-block">
                    <select name="redirect_type">
                        <option value="1">跳转应用详情</option>
                        <option value="2">直接跳转h5</option>
                    </select>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">H5打开方式（PC版）</label>
                <div class="layui-input-block">
                    <select name="open_type">
                        <option value="0">选择打开方式</option>
                        <option value="1">钱包内打开</option>
                        <option value="2">浏览器打开</option>
                    </select>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">H5打开方式（APP版）</label>
                <div class="layui-input-block">
                    <select name="open_type_app">
                        <option value="0">选择打开方式</option>
                        <option value="1">钱包内打开</option>
                        <option value="2">浏览器打开</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">所属类型</label>
                <div class="layui-input-block">
                    <select name="platform_type">
                        <option value="1">币钱包</option>
                        <option value="2">托管账户</option>
                        <option value="3">通用</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn">提交</button>
            </div>
        </form>
    </div>
</div>
<script>
    //form render
    var form = layui.form;
    form.render();

    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf, image_type: 'coin'},
        done: function (res) {
            if (res.code == 0) {
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
        data: {_csrf: $_csrf, image_type: 'h5_icon'},
        done: function (res) {
            if (res.code == 0) {
                $("input[name='h5_icon']").val(res.data.image_id);
                $("#icon2").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });

    uploader.render({
        elem: "#upload3",
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf, image_type: 'h5_icon'},
        done: function (res) {
            if (res.code == 0) {
                $("input[name='introduce_image']").val(res.data.image_id);
                $("#icon3").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });
</script>