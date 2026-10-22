<?php

use yii\helpers\Html;
?>

<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption font-dark-sunglo">
            <i class="fa fa-shield font-dark-sunglo"></i>
            <span class="caption-subject bold uppercase"> 权限列表</span>（<?= $role?>）
        </div>
    </div>
    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form action="<?= \yii\helpers\Url::toRoute(['auth','role'=>$role])?>" method="post" class="form-aaa ">

            <?php foreach ($node_list as $node): ?>
            <div style="margin-bottom:10px;">
                <div class="form-group" style="margin-bottom:0px;background:#eee;padding:5px;">
                    <div class="mt-checkbox-inline" style="padding:2px 0;">
                        <label class="mt-checkbox mt-checkbox-outline" style="margin-bottom:0px;">
                            <input type="checkbox" name="rules[]" value="<?=$node['url']?>" <?php echo in_array($node['url'],$auth_rules) ?'checked':''; ?> />
                            <span></span>
                            <?=$node['title']?>
                        </label>
                    </div>
                </div>
                <div style="padding:5px;border:1px #eee solid;">
                    <?php if (isset($node['child'])) : ?>
                    <?php foreach ($node['child'] as $child): ?>

                        <div class="form-group" style="margin-bottom:0px;">
                            <div class="mt-checkbox-inline" style="padding:2px 0;">
                                <label class="mt-checkbox mt-checkbox-outline" style="margin-bottom:0px;">
                                    <input type="checkbox" name="rules[]" value="<?php echo $child['url'] ?>" <?php echo in_array($child['url'],$auth_rules) ?'checked':''; ?> />
                                    <span></span>
                                    <?=$child['title']?>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                        <?php if (!empty($child['child'])) : ?>
                            <div class="form-group" style="margin-left:50px;margin-bottom:0px;">
                                <div class="mt-checkbox-inline" style="padding:2px 0;">
                                    <?php foreach ($child['child'] as $op): ?>
                                    <label class="mt-checkbox mt-checkbox-outline" style="margin-bottom:5px;">
                                        <input type="checkbox" name="rules[]" value="<?php echo $op['url'] ?>" <?php echo in_array($op['url'],$auth_rules) ?'checked':''; ?> />
                                        <span></span>
                                        <?=$op['title']?>
                                    </label>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        <?php endif ?>
                        </div>
                    <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>
            <?php endforeach ?>

            <div class="form-actions">
                <?= Html::submitButton('确定', ['class' => 'btn blue ajax-post','target-form'=>'form-aaa']) ?>
				<?= Html::button('返回', ['class' => 'btn ','onclick'=>"javascript:history.go(-1);"]) ?>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>
