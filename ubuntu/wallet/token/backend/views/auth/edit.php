<?php

use yii\helpers\Html;

?>

<div class="portlet light bordered">

    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form action="<?=\yii\helpers\Url::toRoute(['edit','role'=>$role->name])?>" method="post" class="form-aaa ">

            <div class="form-group">
                <div>
                    <label>角色名称</label>
                    <span class="help-inline"></span>
                </div>
                <input type="text" class="form-control c-md-2" name="param[name]" value="<?=$role->name?>"/>
                <span class="help-block"></span>
            </div>

            <div class="form-group">
                <div>
                    <label>角色描述</label>
                    <span class="help-inline"></span>
                </div>
                <textarea name="param[description]" class="form-control c-md-4" rows="3"><?=$role->description?></textarea>
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
                            <option
                                    value="<?= $value['id'] ?>"
                                <?php
                                    if ($value['id'] == $role->data){
                                      echo "selected=selected";
                                    }
                                ?>
                            >
                                <?= $value['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <?= Html::submitButton('确定', ['class' => 'btn blue ajax-post','target-form'=>'form-aaa']) ?>
				<?= Html::button('返回', ['class' => 'btn ','onclick'=>"javascript:history.go(-1);"]) ?>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>
