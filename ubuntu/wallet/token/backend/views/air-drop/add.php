<div style="padding: 5px 20px;">
    <br>
    <form id="addData">
        <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
        <div class="input-group my-group">
            <span class="input-group-addon">打币地址</span>
            <input name="to_address" type="text" class="form-control" lay-verify="required">
        </div>
        <div class="input-group my-group">
            <span class="input-group-addon">打币数量</span>
            <input name="amount" type="text" class="form-control" lay-verify="required">
        </div>
        <div class="input-group my-group">
            <span class="input-group-addon">币种名称</span>
            <input name="coin_name" type="text" class="form-control" lay-verify="required">
        </div>
    </form>
</div>