<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-22
 * Time: 下午1:34
 */

use yii\helpers\Html;
use backend\assets\app\IndexAsset;
IndexAsset::register($this);
?>
<div class="layui-row">
    <form class="layui-form" >
    <div>
        <a class="layui-btn btn-primary btn_add">添加<i class="fa fa-plus"></i></a>
        <div class="layui-inline">
            <div class="layui-input-inline">
                <select class="layui-select" name="platform_id" lay-filter="platform_change">
                    <?php foreach($platforms as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    </form>
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this">Android</li>
            <li>IOS</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <table class="layui-table" id="table1" lay-filter="table"></table>
            </div>
            <div class="layui-tab-item">
                <table class="layui-table" id="table2" lay-filter="table"></table>
            </div>
        </div>
    </div>

</div>
<!-- 添加页面 -->
<div class="layui-row add" style="display: none;padding: 5px;">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form form_add" action="javascript:void(0)" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input type="hidden" name="id" value="">
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                    <select name="type">
                        <option value="1">安卓稳定版</option>
                        <option value="2">安卓公测版</option>
                        <option value="3">安卓内侧版</option>
                        <option value="4">IOS稳定版</option>
                        <option value="5">IOS公测版</option>
                        <option value="6">IOS内侧版</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                    <select name="status">
                        <option value="1">上线</option>
                        <option value="2">推荐升级</option>
                        <option value="3">下线</option>
                        <option value="4">强制更新</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">版本</label>
                <div class="layui-input-block">
                    <input type="text" name="version" required lay-verify="required" placeholder="" autocomplete="off"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label" style="width: 100px;">版本代码</label>
                <div class="layui-input-block">
                    <input type="text" name="version_code" required lay-verify="required" placeholder=""
                           autocomplete="off"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 100px;">下载链接</label>
                <div class="layui-input-block">
                    <input type="text" name="download_url" placeholder="" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 100px;">上传APP</label>
                <div class="layui-input-block">
                    <button type="button" class="layui-btn" id="upload1">
                        <i class="layui-icon">&#xe67c;</i>上传
                    </button>
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label" style="width: 100px;">更新日志</label>
                <div class="layui-input-block">
                    <textarea name="log" placeholder="请输入内容" class="layui-textarea"></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn btn_add_submit" lay-submit lay-filter="formDemo">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/html" id="operTpl">
    <a class="btn_edit" lay-event="edit"><i class="layui-icon">&#xe642;</i></a>
    <a class="btn_detail" lay-event="detail"><i class="layui-icon">&#xe60a;</i></a>
    <a class="btn_delete" lay-event="delete"><i class="layui-icon" style="color: red">&#xe640;</i></a>
</script>