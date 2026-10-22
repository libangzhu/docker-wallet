<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-13
 * Time: 下午5:28
 */

use common\models\pwallet\InvestmentType;

$type = InvestmentType::getList(1, 999, []);
?>
<div style="padding: 5px 20px;">
    <br>
    <form id="addData">
        <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
        <div class="input-group my-group">
            <span class="input-group-addon">UID</span>
            <input name="uid" type="text" class="form-control" lay-verify="required">
        </div>
        <div class="input-group my-group">
            <span class="input-group-addon">数量</span>
            <input name="count" type="text" class="form-control" lay-verify="required">
        </div>
        <div class="input-group my-group">
            <span class="input-group-addon">币种</span>
            <input name="coin" type="text" class="form-control" value="YCC" readonly="readonly">
        </div>
        <div class="input-group my-group">
            <span class="input-group-addon">类型</span>
            <select name="tid" class="my-select select_row" style="border-radius: 0 4px 4px 0">
                <?php if ($type['count'] > 0): ?>
                    <?php foreach ($type['data'] as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </form>
</div>