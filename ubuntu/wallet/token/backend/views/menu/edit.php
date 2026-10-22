<?php

use yii\helpers\Html;
use common\core\ActiveForm;
use common\helpers\ArrayHelper;

?>
<div class="row">

    <div class="col-md-2">

		<?php echo $this->render('_tree'); ?>

    </div>

    <div class="col-md-10">

        <div class="portlet-body">

            <div class="portlet-body form">
                <!-- BEGIN FORM-->
				<?php $form = ActiveForm::begin([
					'options'=>[
						'class'=>"form-aaa "
					]
				]); ?>

				<?=$form->field($model, 'title')->textInput(['class'=>'form-control c-md-2'])->label('标题')->hint('栏目的标题')?>

				<?=$form->field($model, 'sort')->textInput(['class'=>'form-control c-md-1'])->label('排序值')->hint('数值越小排序越前')?>

				<?=$form->field($model, 'url')->textInput()->label('链接')->hint('格式：index/index&id=2&type=1')?>

				<?=$form->field($model, 'pid')->selectList(
					ArrayHelper::merge(['0'=>'一级栏目'],ArrayHelper::listDataLevel( \common\models\Menu::find()->asArray()->all(), 'id', 'title','id','pid')),
					['class'=>'form-control select2','widthclass'=>'c-md-2'])->label('上级菜单')->hint('上级菜单描述') ?>

				<?=$form->field($model, 'group')->textInput(['class'=>'form-control c-md-2'])->label('分组')->hint('可不填')?>

				<?=$form->field($model, 'icon')->textInput(['class'=>'form-control c-md-2'])->label('图标')->hint('可不填')?>

				<?= $form->field($model, 'hide')->radioList(['0'=>'可用','1'=>'不可用'])->label('是否可用') ?>

				<?= $form->field($model, 'status')->radioList(['0'=>'显示','1'=>'隐藏'])->label('是否隐藏') ?>

				<?= Html::submitButton('确定', ['class' => 'btn blue ajax-post','target-form'=>'form-aaa']) ?>

				<?= Html::button('返回', ['class' => 'btn ','onclick'=>"javascript:history.go(-1);"]) ?>

				<?php ActiveForm::end(); ?>

                <!-- END FORM-->
            </div>
        </div>
    </div>

</div>


