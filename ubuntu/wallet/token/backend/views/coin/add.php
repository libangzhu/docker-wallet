<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午3:31
 */
?>
<h4>添加币种</h4>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md6">
        <form class="layui-form" method="post" action="">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input name="id" type="hidden" value="<?= $model->id ?>">
            <div class="layui-inline">
                <label class="layui-form-label">简称</label>
                <div class="layui-input-block" style="width: 250px">
                    <input class="layui-input" name="name" placeholder="请填写大写字母" value="<?= $model->name ?>"
                           lay-verify="required">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">可选简称</label>
                <div class="layui-input-block"style="width: 250px">
                    <input class="layui-input" name="optional_name" placeholder="请填写大写字母"
                           value="<?= $model->optional_name ?>" lay-verify="required">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">中文名</label>
                <div class="layui-input-block" style="width: 190px">
                    <input class="layui-input" name="nickname[zh-CN]" value="<?= $model->nickname['zh-CN'] ?>">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">英文名</label>
                <div class="layui-input-block" style="width: 190px">
                    <input class="layui-input" name="nickname[en-US]" value="<?= $model->nickname['en-US'] ?>">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">日文名</label>
                <div class="layui-input-block" style="width: 190px">
                    <input class="layui-input" name="nickname[ja]" value="<?= $model->nickname['ja'] ?>">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">韩文名</label>
                <div class="layui-input-block" style="width: 190px">
                    <input class="layui-input" name="nickname[ko]" value="<?= $model->nickname['ko'] ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">币种全称</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="sid" value="<?= $model->sid ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">官网</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="official" value="<?= $model->official ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">白皮书</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="paper" value="<?= $model->paper ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">众筹价格</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="" placeholder="￥" value="<?= $model->price ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">发行时间</label>
                <div class="layui-input-block" style="width: 200px">
                    <input type="text" class="layui-input" id="time1" value="<?= $model->release ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">合约地址</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" name="address" value="<?= $model->address ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">精度</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" name="decimals" value="<?= $model->decimals ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">区块查询</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" value="" name="area_search">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">发行总量</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" value="" name="publish_count">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">流通总量</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" value="" name="circulate_count">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">平台</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="platform" placeholder="请填写小写字母" value="<?= $model->platform ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="chain" placeholder="请填写小写字母" value="<?= $model->chain ?>">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">合约类型</label>
                <div class="layui-input-block">
                    <select name="treaty">
                        <option value="1">token</option>
                        <option value="2">coins</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">中文介绍</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="introduce[zh-CN]"><?= $model->introduce['zh-CN'] ?></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">英文介绍</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="introduce[en-US]"><?= $model->introduce['en-US'] ?></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">日文介绍</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="introduce[ja]"><?= $model->introduce['ja'] ?></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">韩文介绍</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="introduce[ko]"><?= $model->introduce['ko'] ?></textarea>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">图标</label>
                <div class="layui-input-block">
                    <img src="<?= $model->icon ?>" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                         id="icon1">
                </div>
                <input type="hidden" name="icon" value="<?= $model->icon ?>">
            </div>

            <div class="layui-inline" style="margin-left: 50px;">
                <button type="button" class="layui-btn" id="upload1" style="">
                    <i class="layui-icon">&#xe67c;</i>上传新图片
                </button>
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
        url: '/admin/oss/upload',
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
