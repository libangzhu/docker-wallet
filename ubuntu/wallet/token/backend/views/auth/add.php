<?php

use yii\helpers\Html;
use common\core\ActiveForm;
use common\helpers\ArrayHelper;

?>

<div class="portlet light bordered">
    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form action="<?= \yii\helpers\Url::toRoute(['add']) ?>" method="post" class="form-aaa ">

            <div class="form-group">
                <div>
                    <label>角色名</label>
                    <span class="help-inline">（建议使用唯一的英文名或拼音）</span>
                </div>
                <input type="text" class="form-control c-md-2" name="param[name]" value="" placeholder="英文名"/>
                <span class="help-block"></span>
            </div>

            <div class="form-group">
                <div>
                    <label>角色描述</label>
                    <span class="help-inline">（如：客服）</span>
                </div>
                <textarea class="form-control c-md-4" name="param[description]" rows="3"></textarea>
                <span class="help-block"></span>
            </div>

            <div class="form-group">
                <div>
                    <label>所属钱包</label>
                    <span class="help-inline"></span>
                </div>
                <div class="layui-input-inline">
                    <select class="layui-select" name="param[platform_id]" id="param[platform_id]">
                        <option value="">选择一个钱包</option>
                        <?php foreach($platforms as $key => $value): ?>
                            <option value="<?= $value['id'] ?>">
                                <?= $value['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <?= Html::submitButton('确定', ['class' => 'btn blue ajax-post', 'target-form' => 'form-aaa']) ?>
                <?= Html::button('返回', ['class' => 'btn ', 'onclick' => "javascript:history.go(-1);"]) ?>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>
