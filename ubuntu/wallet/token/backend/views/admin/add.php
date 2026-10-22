<?php

use common\core\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Menu */
/* @var $form ActiveForm */

/* ===========================以下为本页配置信息================================= */
/* 页面基本属性 */
//$this->title = '添加用户';
//$this->params['title_sub'] = '';  // 在\yii\base\View中有$params这个可以在视图模板中共享的参数

/* 渲染其他文件 */
//echo $this->renderFile('@app/views/public/login.php');

?>

<div class="portlet light bordered">
    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <?= $this->render('_form', [
            'model' => $model,
            'role'  => $role,
        ]) ?>
        <!-- END FORM-->
    </div>
</div>
