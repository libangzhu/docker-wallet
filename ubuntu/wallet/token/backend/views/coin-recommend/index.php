<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-11
 * Time: 下午5:45
 */

;?>

<style type="text/css">
    .layui-form-label{
        width: 100px;
    }
</style>
<div class="layui-row">
<form class="layui-form" lay-filter="form2">
    <div class="layui-inline">
        <div class="layui-input-inline">
            <select class="layui-select" name="platform_id" id="platform_id">
                <option value="">选择一个钱包</option>
                <?php foreach($platforms as $key => $value): ?>
                    <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="layui-inline">
        <div class="layui-input-inline">
            <select name="recommend" class="layui-select">
                <option value="">选择推介类型</option>
                <option value="1">首页推介</option>
                <option value="2">次页推介</option>
            </select>
        </div>
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="search">筛选</button>
        <button class="layui-btn layui-btn-normal" type="reset">重置</button>
    </div>
</form>
</div>
<div class="layui-col-md8">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<div style="display: none; padding: 5px 5px;" id="_form">
    <form class="layui-form" id="form1" lay-filter="form1">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="platform_id_hidden" id = 'platform_id_hidden' value="">
        <div class="layui-form-item">
            <label class="layui-form-label">钱包</label>
            <div class="layui-input-inline">
                <select class="layui-select" name="platform_id" disabled>
                    <option value="">请选择一个钱包</option>
                    <?php foreach($platforms as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">币种名称</label>
            <div class="layui-input-block">
                <input name="coin" class="layui-input" type="text">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">推荐类型</label>
            <div class="layui-input-block">
                <select name="recommend" class="layui-select">
                    <option value="1">首页推介</option>
                    <option value="2">次页推介</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">排序值</label>
            <div class="layui-input-block">
                <input name="sort" class="layui-input" type="text">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">所属类型</label>
            <div class="layui-input-block">
                <select name="type" class="layui-select">
                    <option value="1">币钱包</option>
                    <option value="2">托管钱包</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">所属链</label>
            <div class="layui-input-inline">
                <select class="layui-select" name="chain">
                    <option value="">请选择一个链</option>
                    <?php foreach ($chains as $chain): ?>
                        <option value="<?= $chain ?>"><?= $chain ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
</div>

<script type="text/html" id="platform_id_tpl">
    {{# if(1 == d.platform_id){ }}
    币钱包
    {{# } else if(2 == d.platform_id){ }}
    豆子钱包
    {{# } else if(3 == d.platform_id){ }}
    w钱包
    {{# } }}
</script>

<script type="text/html" id="recommend_tpl">
    {{# if(1 == d.recommend){ }}
    首页推荐
    {{# } else if(2 == d.recommend){ }}
    次页推荐
    {{# } }}
</script>

<script type="text/html" id="type_tpl">
    {{# if(1 == d.type){ }}
    币钱包
    {{# } else if(2 == d.type){ }}
    托管钱包
    {{# } }}
</script>

<script type="text/html" id="toolBar">
    <button class="layui-btn layui-btn-xs layui-btn-normal" lay-event="edit">编辑</button>
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</button>
</script>

<script type="text/html" id="toolBarTop">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="recommend_add">添加</button>
    </div>
</script>

<script type="text/javascript">

var table = layui.table;
table.render({
    elem: '#table1',
    toolbar: '#toolBarTop',
    page: true,
    limit: 10,
    url: '/admin/coin-recommend/index',
    cols: [[
        {
            field: 'id',
            title: 'ID'
        },
        {
            field: 'platform_id',
            title: '钱包',
            templet: '#platform_id_tpl'
        },
        {
            field: 'coin',
            title: '币种名称'
        },
        {
            field: 'recommend',
            title: '推荐类型',
            templet: '#recommend_tpl'
        },
        {
            field: 'type',
            title: '所属类型',
            templet: '#type_tpl'
        },
        {
            field: 'chain',
            title: '所属链',
        },
        {
            field: 'sort',
            title: '排序'
        },
        {
            title: '操作',
            toolbar: '#toolBar'
        },
    ]]
});
var form = layui.form;
form.render();

table.on('toolbar(table1)', function(obj) {
    var event = obj.event;
    if (event == 'recommend_add') {
        var index = layer.open({
            title: '添加推介币种',
            type: 1,
            zindex: 1,
            content: $("#_form"),
            btn: ['保存', '取消'],
            scrollbar: false,
            success: function() {
                form.val("form1", {
                    id: '',
                    platform_id: $('#platform_id').val(),
                    coin: '',
                    sort: '',
                    recommend: '',
                    type:'',
                    chain:'',
                });
            },
            btn1: function() {
                var data = $("#form1").serialize();
                data += "&platform_id="+$('#platform_id').val();
                $.get('/admin/coin-recommend/add', data, function(rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        layer.close(index);
                        $("#_form").css('display', 'none');
                        table.reload('table1', {
                            page: {
                                curr: 1
                            }
                        });
                    }
                });
            },
            btn2: function() {
                $("#_form").css('display', 'none');
            },
            cancel: function () {
                $("#_form").css('display', 'none');
            }
        });
    }
});

table.on('tool(table1)', function(obj) {
    var event = obj.event;
    var data = obj.data;
    if (event == 'edit') {
        var index = layer.open({
            title: '添加推介币种',
            type: 1,
            zindex: 1,
            content: $("#_form"),
            btn: ['保存', '取消'],
            scrollbar: false,
            success: function() {
                form.val("form1", {
                    id: data.id,
                    platform_id: data.platform_id,
                    coin: data.coin,
                    sort: data.sort,
                    recommend: data.recommend,
                    type: data.type,
                    chain: data.chain,
                    platform_id_hidden: data.platform_id
                });
            },
            btn1: function() {
                var data = $("#form1").serialize();
                data += "&platform_id="+$('#platform_id_hidden').val();
                $.get('/admin/coin-recommend/add', data, function(rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        layer.close(index);
                        $("#_form").css('display', 'none');
                        table.reload('table1', {
                            page: {
                                curr: 1
                            }
                        });
                    }
                });
            },
            btn2: function() {
                layer.close(index);
                $("#_form").css('display', 'none');
            },
            cancel: function () {
                $("#_form").css('display', 'none');
            }
        });
    } else if ('del' == event) {
        var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
            $.get('/admin/coin-recommend/del', {id: data.id}, function (rev) {
                layer.msg(rev.msg);
                if (0==rev.code) {
                    // layer.close(index);
                    table.reload('table1',{
                        page:{curr:1}
                    });
                }
            });
        });
    }
});
form.on('submit(search)', function (obj) {
    console.log(obj.field);
    table.reload("table1", {
        where: obj.field,
        page: {curr:1}
    });
    return false;
});
</script>
