<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-13
 * Time: 下午2:05
 */

/**
 * @var $model \common\models\pwallet\Member
 */
?>
<h4>修改用户信息</h4>
<div class="layui-row">
    <div class="layui-col-md8">
        <form class="layui-form" method="post">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input type="hidden" name="uid" value="<?= $model->uid ?>">
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">用户名</label>
                <div class="layui-input-inline">
                    <input name="username" class="layui-input" value="<?= $model->username ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">真实姓名</label>
                <div class="layui-input-inline">
                    <input name="realname" class="layui-input" value="<?= $model->realname ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">昵称</label>
                <div class="layui-input-inline">
                    <input name="nickname" class="layui-input" value="<?= $model->nickname ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">手机</label>
                <div class="layui-input-inline">
                    <input name="phone" class="layui-input" value="<?= $model->phone ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">邮箱</label>
                <div class="layui-input-inline">
                    <input name="email" class="layui-input" value="<?= $model->email ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">资产总值</label>
                <div class="layui-input-inline">
                    <input name="capital" class="layui-input" value="<?= $model->capital ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">注册时间</label>
                <div class="layui-input-inline">
                    <input name="register_at" class="layui-input" value="<?= $model->register_at ?>" id="date1">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">推荐人UID</label>
                <div class="layui-input-inline">
                    <input name="recommend_id" class="layui-input" value="<?= $model->recommend_id ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">推荐人数</label>
                <div class="layui-input-inline">
                    <input name="recommend_number" class="layui-input" value="<?= $model->recommend_number ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 150px;margin-bottom: 0">托管账户地址</label>
                <div class="layui-input-inline">
                    <input name="account_address" class="layui-input" value="<?= $model->account_address ?>">
                </div>
            </div>

            <div class="layui-form-item">
                <button class="layui-btn">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
    var laydate = layui.laydate;
    laydate.render({
        elem: "#date1",
        type: "datetime",
        format: 'yyyy-MM-dd HH:mm:ss'
    });
</script>