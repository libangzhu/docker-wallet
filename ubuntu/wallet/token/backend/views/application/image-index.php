<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/17
 * Time: 16:27
 */
?>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<h4>所属应用---<?= $applicate->name["zh-CN"] ?></h4>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this">APP应用</li>
            <li>H5应用</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <button class="layui-btn layui-btn-default add_image" image_category="1">添加APP图片</button>
                <table class="layui-table" id="table1" lay-filter="table1"></table>
            </div>
            <div class="layui-tab-item">
                <button class="layui-btn layui-btn-default add_image" image_category="2">添加H5图片</button>
                <div class="layui-inline">
                    <label class="layui-form-label" style="margin-bottom: 0; width: 120px;">显示尺寸</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="h5_image_showtype" lay-filter="type_change" id="myselect">
                            <option value="1" <?php if($applicate->h5_image_showtype ==1): ?> selected <?php endif;?>>mobile (225px * 400px)</option>
                            <option value="2" <?php if($applicate->h5_image_showtype ==2): ?> selected <?php endif;?>>pc (1200px * 630px)</option>
                        </select>
                    </div>
                </div>
                <table class="layui-table" id="table2" lay-filter="table2"></table>
            </div>
        </div>
    </div>
</div>

<div class="layui-row add" style="display: none;padding: 5px;" id="_form">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form" action="javascript:void(0)"  id="form1" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input id="applicate_id"  name="applicate_id" type="hidden" value="<?= $applicate->id ?>">
            <input id="image_category"  name="image_category" type="hidden" value="">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="image1">
                    </div>
                    <input type="hidden" name="image" value="">
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

<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>


<script type="text/javascript">
    var form = layui.form;
    form.render();
    var table = layui.table;
    var applicate_id = $("#applicate_id").val();
    table.render({
        elem: '#table1',
        url: '/admin/application/image-index?id='+applicate_id+"&image_category=1",
        cols: [[
            {
                field: 'id',
                title: '图片ID'
            },
            {
                field: 'image_url',
                title: '图片链接',
                templet: function(d){
                  return "<a href='"+d.image_url+"' target='_blank'>"+d.image_url+"</a>";
                }
            },
            {
                title: '操作',
                templet: '#operationTpl',
                width: 300
            },
        ]],
    });
    table.render({
        elem: '#table2',
        url: '/admin/application/image-index?id='+applicate_id+"&image_category=2",
        cols: [[
            {
                field: 'id',
                title: '图片ID'
            },
            {
                field: 'image_url',
                title: '图片链接',
                templet: function(d){
                    return "<a href='"+d.image_url+"' target='_blank'>"+d.image_url+"</a>";
                }
            },
            {
                title: '操作',
                templet: '#operationTpl',
                width: 300
            },
        ]],
    });
    table.on('tool(table1)', function(obj) {
        var data = obj.data;
        var event = obj.event;
        if (event === 'del') {
            var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
                $.get('/admin/application/delete-image', {id: data.id,applicate_id:applicate_id,image_category:1}, function (rev) {
                    layer.msg(rev.msg);
                    if (0==rev.code) {
                        table.reload('table1');
                    }
                });
            });
        }
    });
    table.on('tool(table2)', function(obj) {
        var data = obj.data;
        var event = obj.event;
        if (event === 'del') {
            var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
                $.get('/admin/application/delete-image', {id: data.id,applicate_id:applicate_id,image_category:2}, function (rev) {
                    layer.msg(rev.msg);
                    if (0==rev.code) {
                        table.reload('table2');
                    }
                });
            });
        }
    });

    $('.add_image').click(function () {
        var image_category = $(this).attr('image_category');
        $('#image_category').val(image_category);
        var index = layer.open({
            title: '添加图片',
            area: '800px',
            type: 1,
            content: $("#_form"),
            btn: ['保存', '取消'],
            success: function() {
                clearForm();
            },
            btn1: function() {
                $.post('/admin/application/add-image', $("#form1").serialize(), function(rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        layer.close(index);
                        $("#_form").css('display', 'none');
                        table.reload('table'+image_category);
                    }
                });
            },
            btn2: function() {
                layer.close(index);
                clearForm();
                $("#_form").css('display', 'none');
            },
            cancel: function() {
                layer.close(index);
                clearForm();
                $("#_form").css('display', 'none');
            }
        });
    })
    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        before: function(input){
           var data = {};
           var image_category = $('#image_category').val();
           data._csrf = $_csrf;
           if(image_category == 1 ){
                data.image_type = 'application' ;
            }else if(image_category == 2){
                data.image_type = 'h5_application';
            }
           this.data=data;
        },
        done: function (res) {
            if(res.code == 0){
                $("input[name='image']").val(res.data.image_id);
                $("#image1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });
    function clearForm(){
        form.val("form1", {
            image: '',
        });
        $("#image1").attr('src', '');
    }

    $("#myselect").change(function () {
        var h5_image_showtype = $(this).val();
        var app_id =  $("input[name='applicate_id']").val();
        $.get('/admin/application/set-h5-image-showtype', {h5_image_showtype:h5_image_showtype,app_id:app_id}, function(rev) {
            layer.msg(rev.msg);
        });
    });

</script>