<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-11
 * Time: 下午5:45
 */

?>

<style type="text/css">
    .layui-form-label{
        width: 100px;
    }
    .myselect .layui-input{
        height: 25px;
    }
</style>
<div class="layui-row">
<form class="layui-form" lay-filter="form2">
    <div class="layui-inline">
        <div class="layui-input-inline">
            <select class="layui-select" name="platform_id">
                <option value="">选择一个钱包</option>
                <?php foreach($platforms as $key => $value): ?>
                    <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="layui-inline">
        <div class="layui-input-inline">
            <input type="text" name="name" placeholder="币种名称" class="layui-input">
        </div>
    </div>
    <div class="layui-inline">
        <div class="layui-input-inline">
            <select name="platform" class="layui-select">
                <option value="">选择币种平台</option>
                <?php foreach($coin_platforms as $key => $value): ?>
                    <option value="<?= strtoupper($value) ?>"><?= strtoupper($value) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="layui-inline">
        <div class="layui-input-inline">
            <select name="chain" class="layui-select">
                <option value="">选择币种主链</option>
                <?php foreach($chains as $key => $value): ?>
                    <option value="<?= strtoupper($value) ?>"><?= strtoupper($value) ?></option>
                <?php endforeach; ?>
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
        <div class="layui-form-item">
            <label class="layui-form-label">币种名称</label>
            <div class="layui-input-block">
                <input name="name" class="layui-input" type="text">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">币种主链</label>
            <div class="layui-input-block">
                <select name="chain" class="layui-select" lay-ingore>
                    <option value="">选择币种主链</option>
                    <?php foreach($chains as $key => $value): ?>
                        <option value="<?= strtoupper($value) ?>"><?= strtoupper($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
</div>

<script type="text/html" id="toolBar">
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</button>
</script>

<script type="text/html" id="toolBarTop">
    <button class="layui-btn layui-btn-xs" lay-event="recommend_add">添加</button>
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="batch_del">批量删除</button>
    <div class="layui-inline" style="padding-left:100px">
        <button class="layui-btn layui-btn-xs layui-btn-normal" lay-event="copy">复制到</button>
        <div class="layui-input-inline myselect">
            <select class="layui-select" name="copy_platform_id" id="copy_platform_id">
                <option value="">选择一个钱包</option>
                <?php foreach($platforms as $key => $value): ?>
                    <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</script>

<script type="text/javascript">

var table = layui.table;
table.render({
    elem: '#table1',
    toolbar: '#toolBarTop',
    page: true,
    limit: 10,
    url: '/admin/coin/package',
    cols: [[
        {
            type: 'checkbox',
            fixed: 'left'
        },
        {
            field: 'id',
            title: 'ID'
        },
        {
            field: 'name',
            title: '名称'
        },
        {
            field: 'nickname',
            title: '别称'
        },
        {
            field: 'platform',
            title: '平台'
        },
        {
            field: 'chain',
            title: '主链'
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
            success: function(layero,index) {
                var platform_id = $("select[name='platform_id']").val();
                if (platform_id == '') {
                    layer.close(index);
                    layer.msg('请先选择一个钱包');
                    $("#_form").css('display', 'none');
                }
                form.val('form1',{
                    name:'',
                    chain:''
                });
            },
            btn1: function() {
                var data = {
                    name:$("#form1 input[name='name'").val(),
                    chain: $("#form1 select[name='chain']").val(),
                    platform_id: $("select[name='platform_id']").val()
                };
                $.get('/admin/coin/package-add', data, function(rev) {
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
            end: function(){
                $("#_form").css('display', 'none');
            }
        });
    }else if(event == 'copy'){
        var checkStatus = table.checkStatus('table1')
            ,data = checkStatus.data;
         var coin_ids = "";
         $.each(data,function(index,value){
             if(coin_ids){
                 coin_ids +=","+value.id ;
             }else{
                 coin_ids = value.id;
             }
         });
        var platform_id = $("select[name='copy_platform_id']").val();
        $.get('/admin/coin/package-copy', {coin_ids:coin_ids,platform_id:platform_id}, function(rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
                layer.close(index);
                table.reload('table1', {
                    page: {
                        curr: 1
                    }
                });
            }
        });
    }else if(event == 'batch_del'){
        var index = layer.confirm("确认删除?", {
            icon: 3,
            title: '删除'
        }, function() {
            var checkStatus = table.checkStatus('table1')
                ,data = checkStatus.data;
            var coin_ids = "";
            $.each(data,function(index,value){
                if(coin_ids){
                    coin_ids +=","+value.id ;
                }else{
                    coin_ids = value.id;
                }
            });
            var platform_id = $("select[name='platform_id']").val();
            $.get('/admin/coin/package-batch-del', {coin_ids:coin_ids,platform_id:platform_id}, function(rev) {
                layer.msg(rev.msg);
                if (0 == rev.code) {
                    layer.close(index);
                    table.reload('table1', {
                        page: {
                            curr: 1
                        }
                    });
                }
            });
        });
    }
});

table.on('tool(table1)', function(obj) {
    var event = obj.event;
    var data = obj.data;
    if ('del' == event) {
        var index = layer.confirm("确认删除?", {
            icon: 3,
            title: '删除'
        }, function() {
            $.get('/admin/coin/package-del', {
                id: data.id, platform_id: $("select[name='platform_id']").val()
            }, function(rev) {
                layer.msg(rev.msg);
                if (0 == rev.code) {
                    // layer.close(index);
                    table.reload('table1', {
                        page: {
                            curr: 1
                        }
                    });
                }
            });
        });
    }
});
form.on('submit(search)', function (obj) {
    table.reload("table1", {
        where: obj.field,
        page: {curr:1}
    });
    return false;
});
</script>
