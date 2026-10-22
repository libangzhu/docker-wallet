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
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">作者</label>
                <div class="layui-input-block">
                    <input class="layui-input" type="text" value="<?= Yii::$app->user->identity->username ?>"
                           lay-verify="required" name="author" readonly="readonly">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">内容</label>
                <div class="layui-input-block">
                    <textarea class="layui-textarea" name="content" lay-verify="required"><?= $model->content ?></textarea>
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
                <button class="layui-btn" lay-submit>保存</button>
            </div>
        </form>
    </div>
</div>
<script>
    var form = layui.form;
    form.render(null, 'form1');
</script>