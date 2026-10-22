<?php

use yii\helpers\Html;
use common\core\ActiveForm;

/* @var $this yii\web\View */
/* @var $form common\core\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'options' => [
        'class' => "form-aaa "
    ]
]); ?>

<?= $form->field($model, 'username')->iconTextInput([
    'class'        => 'form-control c-md-2',
    'iconPos'      => 'left',
    'iconClass'    => 'icon-user',
    'placeholder'  => '登录账号',
    'value'        => ' ',
    'autocomplete' => 'off',
])->label(false) ?>
<?= $form->field($model, 'password')->iconTextInput([
    'type'         => 'password',
    'class'        => 'form-control c-md-2',
    'iconPos'      => 'left',
    'iconClass'    => 'icon-lock',
    'value'        => '',
    'placeholder'  => '修改时，密码不变可不填',
    'autocomplete' => 'off',
])->label(false) ?>

<input type="hidden" value="<?= $role ?>" name="role">
<?php $model->status = 1; ?>
<?= $form->field($model, 'status')->radioList(['1' => '正常', '0' => '禁用'])->label('使用状态') ?>

<div class="form-actions">
    <?= Html::submitButton('确定', ['class' => 'btn blue ajax-post', 'target-form' => 'form-aaa']) ?>
    <?= Html::button('返回', ['class' => 'btn ', 'onclick' => "javascript:history.go(-1);"]) ?>
</div>
<?php ActiveForm::end(); ?>

