<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午9:59
 */

use backend\assets\coin\IndexAsset;

IndexAsset::register($this);

?>
<style>
    .layui-table-tips-c {
        padding: 0px;
    }
</style>
<h4>币种列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <a href="/admin/coin/add">
            <button class="layui-btn layui-btn-default" id="add">添加币种</button>
        </a>
    </div>
    <div class="layui-col-md8">
        <form class="layui-form" method="get" action="">
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">币种名称</label>
                <div class="layui-input-inline">
                    <input class="layui-input" name="name">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">平台</label>
                <div class="layui-input-inline">
                    <select name="platform">
                        <option value="">全部</option>
                        <?php foreach ($platforms as $platform): ?>
                            <option value="<?= $platform ?>"><?= $platform ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">类型</label>
                <div class="layui-input-inline">
                    <select name="chain">
                        <option value="">全部</option>
                        <?php foreach ($chains as $chain): ?>
                            <option value="<?= $chain ?>"><?= $chain ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="margin-bottom: 0; width: 100px;">推介币种</label>
                <div class="layui-input-inline">
                    <select name="recommend">
                        <option value="">全部</option>
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="form1">搜索</button>
            </div>
        </form>
    </div>
</div>
<div class="layui-row">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>


<script type="text/html" id="iconTpl">
    <a href="{{d.icon}}" target="_blank"><img src="{{d.icon}}" style="max-width: 32px; max-height: 32px;"/></a>
</script>
<script type="text/html" id="officialTpl">
    {{# if(d.official){ }}
    {{# layui.each(d.official.split(';'),function(index,item){ }}
    {{# if(item){ }}
    <a href="{{ item }}" class="layui-table-link" target="_blank">官网{{# if(index){ }}{{index}}{{# } }}</a>
    {{# } }}
    {{# }); }}
    {{# }else{ }}
    -
    {{# } }}
</script>
<script type="text/html" id="paperTpl">
    {{# if(d.paper){ }}
    <a href="{{d.paper}}" class="layui-table-link" target="_blank">白皮书</a>
    {{# }else{ }}
    -
    {{# } }}
</script>
<script type="text/html" id="operationTpl">
    <a lay-event="edit">
        <button class="layui-btn layui-btn-sm"><i class="layui-icon">&#xe642;</i></button>
    </a>
    <a lay-event="delete">
        <button class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon">&#xe640;</i></button>
    </a>
</script>
<script type="text/html" id="exchangeTpl">
    {{d.exchange}}家
</script>
<script type="text/html" id="recommendTpl">
    {{# if(d.recommend==0){ }}
    否
    {{# }else if(d.recommend==1){ }}
    首页推荐
    {{# }else{ }}
    次页推荐
    {{# } }}
</script>