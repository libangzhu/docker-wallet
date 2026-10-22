<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;

?>
<div class="row admin-auth-index">

    <div class="col-md-12">
		<?=Html::a(Yii::$app->params['config_page']['add'] . '<i class="fa fa-plus"></i>',['add'],['class'=>"btn ".Yii::$app->params['config_page']['color_opt']])?>

        <div class="table-container">
            <!--<form class="ids">-->

            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>角色名</th>
                    <th>描述</th>
                    <th>添加时间</th>
                    <th>更新时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>

                <?php $nn=0;?>
                <?php foreach ($roles as $key => $value): ?>
                    <tr class="odd gradeX">
                        <?php $nn++;?>
                        <td><?=$nn?></td>
                        <td style = 'white-space: nowrap;'><?=$value['name']?></td>
                        <td style = 'white-space: nowrap;'><?=$value['description']?></td>
                        <td style = 'white-space: nowrap;'><?=date('Y-m-d H:i',$value['createdAt'])?></td>
                        <td style = 'white-space: nowrap;'><?=date('Y-m-d H:i',$value['updatedAt'])?></td>
                        <td style = 'white-space: nowrap;'>
                            <a href="<?=Url::toRoute(['edit', 'role'=>$key])?>" class="btn btn-xs blue"><i class="fa fa-edit"></i> 编辑</a>
                            <a href="<?=Url::toRoute(['auth', 'role'=>$key])?>" class="btn btn-xs blue"><i class="icon-key"></i> 授权</a>
                            <a href="<?=Url::toRoute(['user', 'role'=>$key])?>" class="btn btn-xs blue"><i class="icon-users"></i> 该组用户</a>
                            <a href="<?=Url::toRoute(['delete', 'role'=>$key])?>" class="btn btn-xs red ajax-get confirm"><i class="fa fa-times"></i></a>
                        </td>
                    </tr>
                <?php endforeach ?>

                </tbody>
            </table>

            <!--</form>-->
        </div>
    </div>


</div>

