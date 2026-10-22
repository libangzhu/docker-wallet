<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 17:59:17
 * @authors rlgy <rlgyzhcn@qq.com>
 */

?>
<style type="text/css">
    .layui-form-label{
        width: 100px;
    }
</style>
<form class="layui-form" id="_form_hidden">
    <input type="hidden" name="wid" value="<?= $wid ?>">
</form>

<div class="layui-col-md8">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<div style="display: none; padding: 5px 5px;" id="_form_add">
    <form class="layui-form" id="form_add" lay-filter="form_add">
        <input type="hidden" name="id" value="">
        <div class="layui-form-item">
            <label class="layui-form-label">Token名称</label>
            <div class="layui-input-block">
                <input name="token" class="layui-input" type="text">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">代扣地址</label>
            <div class="layui-input-block">
                <input name="address" class="layui-input" type="text">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">代扣状态</label>
            <div class="layui-input-block">
                <select name="status" class="layui-select">
                    <option value="1">启用</option>
                    <option value="2">禁用</option>
                </select>
            </div>
        </div>
    </form>
</div>

<script type="text/html" id="toolBarTop">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="add">添加</button>
        <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="set">为所有币种设置代扣地址</button>
    </div>
</script>

<script type="text/html" id="toolBar">
    <button class="layui-btn layui-btn-xs layui-btn-normal" lay-event="edit">编辑</button>
    <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</button>
</script>
<script type="text/html" id="status_tpl">
    {{# if(1 == d.status){ }}
    启用
    {{# } else if(2 == d.status){ }}
    禁用
    {{# } }}
</script>
<script type="text/javascript">
var table = layui.table;
var form = layui.form;
form.render();
table.render({
    elem: '#table1',
    page: true,
    limit: 10,
    url: '/admin/coin-withhold/detial',
    where: {wid: $("#_form_hidden input[name='wid']").val()},
    toolbar: '#toolBarTop',
    cols: [[
        {field: 'id',title: 'ID'},
        {field: 'token',title: 'Token名字'},
        {field: 'status',title: '代扣状态', templet:'#status_tpl'},
        {field: 'address',title: '代扣币种地址'},
        {title: '操作',toolbar: '#toolBar'}
    ]]
});

table.on('toolbar(table1)', function(obj) {
    var event = obj.event;
    if (event == 'add') {
        var index = layer.open({
            type: 1,
            title: '添加代扣token',
            content: $("#_form_add"),
            btn: ['保存', '取消'],
            btn1: function(argument) {
                $.get('/admin/coin-withhold/add-token', $('#form_add').serialize() + '&' + $('#_form_hidden').serialize(), function(rev) {
                    layer.msg(rev.msg);
                    if (rev.code == 0) {
                        layer.close(index);
                        table.reload('table1', {
                            page: {
                                curr: 1
                            }
                        });
                        $('#_form_add').css('display', 'none');
                    }
                });
            },
            btn2: function() {
                $('#_form_add').css('display', 'none');
            }
        });
    } else if (event == 'del') {
        layer.confirm('确认删除?',{icon:3,'title':'删除'}, function() {
            $.get('/admin/coin-withhold/del',{id:obj.data.id},function (rev) {
                // body...
            });
        })
    }
});

table.on('tool(table1)', function(obj) {
    var event = obj.event;
    var data = obj.data;
    if (event == 'edit') {
        var index = layer.open({
            type: 1,
            title: '添加代扣token',
            content: $("#_form_add"),
            btn: ['保存', '取消'],
            success: function() {
                form.val('form_add', data);
            },
            btn1: function(argument) {
                $.get('/admin/coin-withhold/add-token', $('#form_add').serialize() + '&' + $('#_form_hidden').serialize(), function(rev) {
                    layer.msg(rev.msg);
                    if (rev.code == 0) {
                        layer.close(index);
                        table.reload('table1', {
                            page: {
                                curr: 1
                            }
                        });
                        $('#_form_add').css('display', 'none');
                    }
                });
            },
            btn2: function() {
                $('#_form_add').css('display', 'none');
            }
        });
    } else if (event == 'del') {
        layer.confirm('确认删除?', {
            icon: 3,
            'title': '删除'
        }, function() {
            $.get('/admin/coin-withhold/del-token', {
                id: obj.data.id
            }, function(rev) {
                layer.msg(rev.msg);
                if (rev.code == 0) {
                    table.reload('table1', {
                        page: {
                            curr: 1
                        }
                    });
                }
            });
        })
    }
});
</script>