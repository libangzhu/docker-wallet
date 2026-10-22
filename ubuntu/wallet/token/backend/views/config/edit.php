<?php

use yii\helpers\Html;
use common\core\ActiveForm;
?>

<div class="portlet light bordered">

    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <?php $form = ActiveForm::begin([
            'options'=>[
                'class'=>"form-aaa "
            ]
        ]); ?>

        <?=$form->field($model, 'name')->textInput(['class'=>'form-control c-md-2'])->label('配置标识')->hint('通过<span style="color:#f00;">Yii::$app->params["web"]["配置标识"]</span>访问')?>

        <?=$form->field($model, 'title')->textInput(['class'=>'form-control c-md-2'])->label('配置说明');?>

        <?=$form->field($model, 'type')->selectList(
            Yii::$app->params['config_type'],
            ['class'=>'form-control c-md-1 chosen'])->label('配置类型') ?>

        <?=$form->field($model, 'group')->selectList(
            Yii::$app->params['config_group'],
            ['class'=>'form-control c-md-1 chosen'])->label('分组') ?>

        <?= $form->field($model, 'value')->textarea(['rows'=>1])->label('配置值')->hint('如果为数组或枚举类型，配置格式“项:值”每项之间用换行或逗号隔开') ?>
        
        <?= $form->field($model, 'extra')->textarea(['rows'=>1])->label('选项值')->hint('如果是枚举型(主要是用来做下拉选项的) 需要配置该项') ?>
        
        <?=$form->field($model, 'remark')->textInput()->label('说明文字')?>
        
        <?=$form->field($model, 'sort')->textInput(['class'=>'form-control c-md-1'])->label('排序')->hint('数值越小排序越前')?>
        
        <?= $form->field($model, 'status')->radioList(['1'=>'正常','0'=>'隐藏']) ?>
        
        <div class="form-actions">
            <?= Html::submitButton('确定', ['class' => 'btn blue ajax-post','target-form'=>'form-aaa']) ?>
			<?= Html::button('返回', ['class' => 'btn ','onclick'=>"javascript:history.go(-1);"]) ?>
        </div>
        <?php ActiveForm::end(); ?>
        <!-- END FORM-->
    </div>
</div>

<!-- 定义数据块 -->
<?php $this->beginBlock('test'); ?>
jQuery(document).ready(function() {
    highlight_subnav('config/index'); //子导航高亮
});
<?php $this->endBlock() ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
