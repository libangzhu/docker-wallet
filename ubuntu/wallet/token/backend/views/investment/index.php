<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-13
 * Time: 下午3:27
 */

use common\models\pwallet\InvestmentType;
use backend\assets\investment\IndexAsset;

$_csrf = Yii::$app->request->getCsrfToken();
$js    = <<<EOF
var csrf='{$_csrf}'
EOF;
/**
 * @var $this \yii\web\View
 */
$this->registerJs($js, \yii\web\View::POS_BEGIN);

IndexAsset::register($this);
$type = InvestmentType::getList(1, 999, []);
?>
<h4>空投管理</h4>
<div class="layui-row">
    <div class="layui-col-md12">
        <form class="layui-form">
            <div class="layui-inline">
                <label class="layui-form-label" style="width: 45px;margin-bottom: 0; padding-left: 0;text-align: left">状态</label>
                <div class="layui-input-inline">
                    <select name="status">
                        <option value="0">全部</option>
                        <option value="1">未处理</option>
                        <option value="2">已处理</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label"
                       style="width: 55px;margin-bottom: 0; padding-left: 10px;text-align: left">类型</label>
                <div class="layui-input-inline">
                    <select name="tid">
                        <option value="0">全部</option>
                        <?php if ($type['count'] > 0): ?>
                            <?php foreach ($type['data'] as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <input name="uid" class="layui-input" placeholder="UID">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <input name="coin" class="layui-input" placeholder="币种">
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-filter="form1" lay-submit>搜索</button>
                <button class="layui-btn" type="reset">重置</button>
                <button class="layui-btn" id="add1">添加数据</button>
                <button class="layui-btn" id="add2">导入文件</button>
            </div>
        </form>
    </div>
    <div class="layui-col-md4">

    </div>
</div>
<div class="layui-row">
    <table class="layui-table" id="table1"></table>
</div>