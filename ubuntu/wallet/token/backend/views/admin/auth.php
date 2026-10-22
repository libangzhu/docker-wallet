<?php

use yii\helpers\Html;
use common\core\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Menu */
/* @var $form ActiveForm */
/** @var $roles array */
/** @var $group array */

/* ===========================以下为本页配置信息================================= */
/* 页面基本属性 */
//$this->title = '用户组授权';
//$this->params['title_sub'] = '';  // 在\yii\base\View中有$params这个可以在视图模板中共享的参数

/* 渲染其他文件 */
//echo $this->renderFile('@app/views/public/login.php');

?>

<div class="portlet light bordered">
    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form action="<?= \yii\helpers\Url::toRoute(['auth', 'uid' => $model->uid]) ?>" method="post" class="form-aaa ">
            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">

            <div class="form-group">
                后台角色<label style="color: #0d8580">【查看】</label>
                <div class="mt-radio-list">
                    <?php if ($roles) foreach ($roles as $role) : ?>
                        <label class="mt-radio mt-radio-outline">
                            <input type="radio" name="param" value="<?= $role->name ?>" <?php echo in_array($role->name, $group) ? 'checked' : ''; ?> />
                            <span></span>
                            <?= $role->name ?> (<?= $role->description ?>)
                        </label>
                    <?php endforeach ?>
                </div>

                <div class="form-actions">
                    <?= Html::submitButton('确定', ['class' => 'btn blue ajax-post', 'target-form' => 'form-aaa']) ?>&nbsp;&nbsp;&nbsp;
                    <?= Html::button('返回', ['class' => 'btn ', 'onclick' => "javascript:history.go(-1);"]) ?>
                </div>
        </form>
        <!-- END FORM-->
    </div>
</div>

<!-- 定义数据块 -->
<?php $this->beginBlock('test'); ?>
jQuery(document).ready(function() {
    highlight_subnav('admin/index'); //子导航高亮
});
<?php $this->endBlock() ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
