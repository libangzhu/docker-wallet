<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午3:31
 */
?>

<h4>添加币种</h4>
<?= $this->render('form', ['model' => $model, 'platforms' => $platforms, 'type' => $type]) ?>