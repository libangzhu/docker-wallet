<div style="padding: 5px 20px;">
    <br>
    <form id="addData">
        <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
        <div class="input-group my-group">
            <span class="input-group-addon">币种名称</span>
            <input name="coin_name" type="text" class="form-control" lay-verify="required">
        </div>
        <input class="layui-input" name="platform_id" type="hidden" value="<?= $platform_id ?>"
               lay-verify="required">
    </form>
</div>
