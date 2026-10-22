<?php

use common\core\ActiveForm;

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
