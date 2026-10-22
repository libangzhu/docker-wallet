<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/17
 * Time: 10:55
 */
?>

<h4>首页推荐列表</h4>

<div class="layui-col-md9">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>
<div class="layui-row add" style="display: none;padding: 5px;" id="_form">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form" action="javascript:void(0)" id="form1" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input type="hidden" name="id" value="">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="icon1">
                    </div>
                    <input type="hidden" name="icon" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload1" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/html" id="typeTpl">
    {{# if(d.type==1){ }}
    应用分类
    {{# } else { }}
    应用
    {{# } }}
</script>
<script type="text/html" id="iconTpl">
    <img src="{{d.icon_url}}" style="max-width: 32px; max-height: 32px;"/>
</script>
<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑图片</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>
<script type="text/javascript">
    var table = layui.table;
    table.render({
        elem: '#table1',
        url: '/admin/applicate-recommend/index',
        cols: [[
            {
                field: 'relate_id',
                title: '应用/分类ID'
            },
            {
                field: 'name',
                title: '名称',
            },
            {
                field: 'coin_name',
                title: '钱包',
            },
            {
                field: 'type',
                title: '类型',
                templet: '#typeTpl'
            },
            {
                field: 'sort',
                title: '排序',
                edit: 'text'
            },
            {
                field: 'icon_url',
                title: '图标',
                templet: '#iconTpl'
            },
            {
                title: '操作',
                templet: '#operationTpl'
            }
        ]],
    });

    var form = layui.form;
    form.render();

    table.on('tool(table1)', function (obj) {
        var event = obj.event;
        var data = obj.data;
        if (event === 'del') {
            var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
                $.get('/admin/applicate-recommend/delete', {id: data.relate_id, type: data.type}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table.reload('table1');
                    }
                });
            });
        } else if (event === 'edit') {
            var index = layer.open({
                title: '编辑图片',
                area: '800px',
                type: 1,
                content: $("#_form"),
                btn: ['保存', '取消'],
                success: function () {
                    form.val("form1", {
                        id: data.id,
                        icon: data.icon,
                    });
                    $("#icon1").attr('src', data.icon_url);
                },
                btn1: function () {
                    $.post('/admin/applicate-recommend/set-icon', $("#form1").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (0 == rev.code) {
                            layer.close(index);
                            $("#_form").css('display', 'none');
                            table.reload('table1');
                        }
                    });
                },
                btn2: function () {
                    layer.close(index);
                    $("#_form").css('display', 'none');
                },
                cancel: function () {
                    layer.close(index);
                    $("#_form").css('display', 'none');
                }
            });
        }
    });
    //监听单元格编辑
    table.on('edit(table1)', function (obj) {
        var value = obj.value; //得到修改后的值
        var data = obj.data;    //得到所在行所有键值
        $.get('/admin/applicate-recommend/set-sort', {
            id: data.relate_id,
            type: data.type,
            sort: value
        }, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
                table.reload('table1', {});
            } else {

            }
        });
    });
    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf, image_type: 'coin'},
        done: function (res) {
            if (res.code == 0) {
                $("input[name='icon']").val(res.data.image_id);
                $("#icon1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });

</script>